<script id="vertexShader" type="x-shader/x-vertex">
    void main() {
        gl_Position = vec4( position, 1.0 );
    }
</script>
<script id="fragmentShader" type="x-shader/x-fragment">
  uniform vec2 u_resolution;
  uniform vec2 u_mouse;
  uniform float u_time;
  uniform sampler2D u_noise;
  uniform sampler2D u_environment;
  
  vec2 movement;
  float scale = 5.;

  vec2 hash2(vec2 p)
  {
    vec2 o = texture2D( u_noise, (p+0.5)/256.0, -100.0 ).xy;
    return o;
  }
  
  void grid(vec2 uv, inout vec3 colour, vec3 gridcolour, vec2 size, float linewidth) {
    vec2 grid = (fract(uv/size+.5)-.5)*size;
    grid = abs(grid);
    float gridlines = smoothstep(linewidth, linewidth + 0.005, min(grid.x, grid.y));
    colour = mix(gridcolour, colour, gridlines);
  }
  
  float voronoi(vec2 uv, inout vec2 n_point, inout vec2 s_n_point, inout float s_dist) {
    
    float dist = 4.;
    s_dist = 4.;
    float s_result = 0.;
    
    vec2 grid_id = floor(uv);
    vec2 grid_uv = fract(uv);
    
    float exponent = clamp(abs(u_mouse.y * 50.), 7., 50.);
    float result = 0.;
    
    for(float j = -1.; j < 2.; j++) {
      for(float i = -1.; i < 2.; i++) {
        vec2 offset = vec2(i, j);
        vec2 grid_test_id = grid_id + offset;
        vec2 rand = hash2(grid_test_id);
        vec2 point_pos = offset + rand - grid_uv;

        // The following adds some random animation to the particles
        rand = hash2(grid_test_id + 1000.);
        rand = 0.5 + 0.4*sin((u_time) + 6.2831*rand);
        point_pos = offset + rand - grid_uv;
        
        // float len = length(point_pos); // the length gives us a more euclidian (conic) length
        float len = dot(point_pos, point_pos); // The float gives us a more rounded distance
        // float len = abs(point_pos.x)+abs(point_pos.y); // manhatten distance
        result += exp( -exponent*len ); // To soften the effect, use this. You'll also need to return the log result, commented out below
        
        if(len < dist) {
          s_dist = dist;
          dist = len;
          s_n_point = n_point;
          n_point = point_pos;
        } else if (len < s_dist) {
          s_dist = len;
          s_n_point = point_pos;
        }
      }
    }
    
    return -(1.0/exponent)*log( result );
    
    return dist;
  }
//   Naive environment mapping. Pass the reflected vector and pull back the texture position for that ray.
  vec3 envMap(vec3 rd, vec3 sn){

      rd.xy -= u_time*.2; // This just sort of compensates for the camera movement
      // rd.xy -= movement;
      rd /= scale; // scale the whole thing down a but from the scaled UVs
    
      vec3 col = texture2D(u_environment, rd.xy - .5, 100.).rgb;
      col *= normalize(col);
      // col *= vec3(1., 1., 1.2);
      // vec3 col = vec3(hash2(rd.xy).y * .5 + .5);

      return col;

  }

  
  float bumpMap(vec2 uv, inout vec2 q, inout vec2 r, inout float s_dist) {
    
    float vor = voronoi(uv, q, r, s_dist);
    
    // return dot(r, r);
    // return s_dist * vor;
    
    return abs(vor - 1.) * ((u_mouse.x + .5) * 3. + 1.);
  }

  vec4 renderPass(vec2 uv, vec2 uvoffset) {
    vec3 surfacePos = vec3(uv, 0.0);
    vec3 ray = normalize(vec3(uv - movement, 1.));
    // vec3 lightPos = vec3(cos(u_time / 2.) * 2., sin(u_time / 2.) * 2., -3.);
    vec3 lightPos = vec3(0., 0., -3.) + vec3(movement, 0.);
    vec3 normal = vec3(0., 0., -1);

    vec2 sampleDistance = vec2(1. / u_resolution.x, 0.);

    vec2 q = vec2(0.,0.);
    vec2 r = vec2(0.,0.);
    float s_dist = 4.;

    float fx = bumpMap(surfacePos.xy-sampleDistance.xy + uvoffset, q, r, s_dist);
    float fy = bumpMap(surfacePos.xy-sampleDistance.yx + uvoffset, q, r, s_dist);
    s_dist = 4.;
    float f = bumpMap(surfacePos.xy + uvoffset, q, r, s_dist);
    fx = (fx-f)/sampleDistance.x;
    fy = (fy-f)/sampleDistance.x;
    normal = normalize( normal + vec3(fx, fy, 0) * 0.2 );           

    vec3 lightV = lightPos - surfacePos;
    float lightDist = max(length(lightV), 0.001);
    lightV /= lightDist;

    vec3 lightColour = vec3(.8, .8, 1.);

    float shininess = 1.;
    float brightness = 4.;
    float roughness = 2.;

    float falloff = 0.6;
    float attenuation = 1./(1.0 + lightDist*lightDist*falloff);

    float diffuse = max(dot(normal, lightV), 0.);
    float specular = pow(max(dot( reflect(-lightV, normal), -ray), 0.), 50.) * shininess;
    // specular *= sqrt(roughness);
    
    vec2 _q = abs(normalize(q));
    
    // vec3 tex = texture2D(u_environment, (reflect(vec3(uv, -1.), normal)).xy ).rgb;
    vec3 reflect_ray = reflect(vec3(uv - movement, 1.), normal * 1.);
    // The reflect ray is the ray wwe use to determine the reflection.
    // We use the UV less the movement (to account for "environment") to the surface normal
    vec3 tex = envMap(reflect_ray, normal); // Fake environment mapping.

    // vec3 texCol = (vec3(s_dist, f, .5)) * brightness;
    vec3 texCol = tex * brightness;

    vec3 colour = (texCol * (diffuse*vec3(1, .97, .92)*2. + 0.5) + lightColour*specular * f * 2.)*attenuation*1.5;
    // colour += texCol * .2;

    return vec4(colour, 1.);
  }

  void main() {
    vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy) / min(u_resolution.y, u_resolution.x);
    
    uv *= scale;
    movement = vec2(u_time, 0.);
    uv += movement;
    
    // vec4 render = renderPass(uv, vec2(cos(u_time), sin(u_time)));
    vec4 render = renderPass(uv, vec2(0.));
    
    
    
    // grid(uv, colour, vec3(1.), vec2(1.), .005);

    gl_FragColor = render;
  }
</script>

<script>
/*
Most of the stuff in here is just bootstrapping. Essentially it's just
setting ThreeJS up so that it renders a flat surface upon which to draw 
the shader. The only thing to see here really is the uniforms sent to 
the shader. Apart from that all of the magic happens in the HTML view
under the fragment shader.
*/

let container;
let camera, scene, renderer;
let uniforms;

let loader=new THREE.TextureLoader();
let texture, environment;
loader.setCrossOrigin("anonymous");
loader.load(
  'https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/noise.png',
  function texture_load(tex) {
    texture = tex;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.minFilter = THREE.LinearFilter;
    loader.load( 
      'https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/env_lat-lon.png',
      function environment_load(tex) {
        environment = tex;
        environment.wrapS = THREE.RepeatWrapping;
        environment.wrapT = THREE.RepeatWrapping;
        environment.minFilter = THREE.LinearFilter;
        init();
        animate();
      }
    );
  }
);

function init() {
  container = document.getElementById( 'container' );

  camera = new THREE.Camera();
  camera.position.z = 1;

  scene = new THREE.Scene();

  var geometry = new THREE.PlaneBufferGeometry( 2, 2 );

  uniforms = {
    u_time: { type: "f", value: 1.0 },
    u_resolution: { type: "v2", value: new THREE.Vector2() },
    u_noise: { type: "t", value: texture },
    u_environment: { type: "t", value: environment },
    u_mouse: { type: "v2", value: new THREE.Vector2() }
  };

  var material = new THREE.ShaderMaterial( {
    uniforms: uniforms,
    vertexShader: document.getElementById( 'vertexShader' ).textContent,
    fragmentShader: document.getElementById( 'fragmentShader' ).textContent
  } );
  material.extensions.derivatives = true;

  var mesh = new THREE.Mesh( geometry, material );
  scene.add( mesh );

  renderer = new THREE.WebGLRenderer();
  renderer.setPixelRatio( 1 );

  container.appendChild( renderer.domElement );

  onWindowResize();
  window.addEventListener( 'resize', onWindowResize, false );

  document.addEventListener('pointermove', (e)=> {
    let ratio = window.innerHeight / window.innerWidth;
    uniforms.u_mouse.value.x = (e.pageX - window.innerWidth / 2) / window.innerWidth / ratio;
    uniforms.u_mouse.value.y = (e.pageY - window.innerHeight / 2) / window.innerHeight * -1;
    
    e.preventDefault();
  });
}

function onWindowResize( event ) {
  renderer.setSize( window.innerWidth, window.innerHeight );
  uniforms.u_resolution.value.x = renderer.domElement.width;
  uniforms.u_resolution.value.y = renderer.domElement.height;
}

function animate() {
  requestAnimationFrame( animate );
  render();
}

function render() {
  uniforms.u_time.value += 0.01;
  renderer.render( scene, camera );
}
</script>