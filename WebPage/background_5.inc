<!-- Bad blood background.

    This code has been taken from Codepen.io.
    Thanks to Liam Egan for posting it.
    Source code is here...

    https://codepen.io/shubniggurath/pen/qxgbLG
-->

<div id="container" touch-action="none"></div>              <!-- this is where the background goes           -->

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
  
  const bool    debug = false;

  vec2 hash2(vec2 p)
  {
    vec2 o = texture2D( u_noise, (p+0.5)/256.0, -100.0 ).xy;
    return o;
  }
  
  // smooth min
  // reference: http://iquilezles.org/www/articles/smin/smin.htm
  float smin(float a, float b, float k) {
      float res = exp(-k*a) + exp(-k*b);
      return -log(res)/k;
  }
  
  void grid(vec2 uv, inout vec3 colour, vec3 gridcolour, vec2 size, float linewidth) {
    vec2 grid = (fract(uv/size+.5)-.5)*size;
    grid = abs(grid);
    float gridlines = smoothstep(linewidth, linewidth + 0.005, min(grid.x, grid.y));
    colour = mix(gridcolour, colour, gridlines);
  }
  
  float voronoi(
          vec2    uv, 
    inout vec2    min_point, 
    inout vec2    s_min_point, 
    inout float   s_min_dist, 
    inout vec3    cell_colour) {
    
    float   dist = 4.;
    float   res = 0.;
            s_min_dist = 4.;
    
    vec2 grid_id = floor(uv);
    vec2 grid_uv = fract(uv);
    
    for(float j = -1.; j < 2.; j++) {
      for(float i = -1.; i < 2.; i++) {
        vec2 offset = vec2(i, j);
        vec2 grid_test_id = grid_id + offset;
        
        // Break out on a 50% chance
        vec2 rand = hash2(grid_test_id + 2000.);
        if(rand.x < 0.5) {
          // continue;
        }
        
        // Th eposition of the point in the offset grid
        rand = hash2(grid_test_id);
        vec2 point_pos = offset + rand - grid_uv;

        // The following adds some random animation to the particles
        rand = hash2(grid_test_id + 1000.);
        rand = 0.4 + 0.3*sin((u_time) + 6.2831*rand);
        point_pos = offset + rand - grid_uv;
        
        // float len = length(point_pos); // the length gives us a more linear (conic) length
        float len = dot(point_pos, point_pos); // The dot gives us a more rounded distance
        // len *= len; // making the ease more pronounced
        
        res += exp( -10.0*len );
        
        if(len < dist) {
          s_min_dist = dist;
          dist = len;
          
          min_point = point_pos;
        } else if(len < s_min_dist) {
          s_min_dist = len;
        }
      }
    }
    
    return -(1.0/10.0)*log( res );
    
    return dist;
  }
  
  float bumpMap(vec2 uv, inout vec2 q, inout vec2 r) {
    
    uv *= 7.;
    uv.x -= u_time * 5.;
    
    vec3 cell_colour;
    vec2 min_point;
    vec2 s_min_point;
    float s_min_dist;
    float _dist = voronoi(uv + vec2(u_time * 2., 0.), min_point, s_min_point, s_min_dist, cell_colour);
    
    float dist = 0.;
    dist = smin(_dist, voronoi(uv * 2. - .5 + vec2(2000.) * _dist / 2000., min_point, s_min_point, s_min_dist, cell_colour), 20.);
    dist = smin(dist, voronoi(uv - .5 + vec2(1000.) * _dist / 500., min_point, s_min_point, s_min_dist, cell_colour), 10.);
    
    q = vec2(_dist, 1. - clamp(_dist, 0., 1.));

    // colour = smoothstep(0., 1., colour);

    return 1. - dist * .6;
  }

  vec4 renderPass(vec2 uv, vec2 uvoffset) {
    vec3 surfacePos = vec3(uv, 0.0);
    vec3 ray = normalize(vec3(uv, 1.));
    vec3 lightPos = vec3(cos(u_time / 2.) * 2., sin(u_time / 2.) * 2., -3.);
    vec3 normal = vec3(0., 0., -1);

    vec2 sampleDistance = vec2(1. / u_resolution.x, 0.);

    vec2 q = vec2(0.,0.);
    vec2 r = vec2(0.,0.);

    float fx = bumpMap(surfacePos.xy-sampleDistance.xy + uvoffset, q, r);
    float fy = bumpMap(surfacePos.xy-sampleDistance.yx + uvoffset, q, r);
    float f = bumpMap(surfacePos.xy + uvoffset, q, r);
    fx = (fx-f)/sampleDistance.x;
    fy = (fy-f)/sampleDistance.x;
    normal = normalize( normal + vec3(fx, fy, 0) * 0.2 );           

    vec3 lightV = lightPos - surfacePos;
    float lightDist = max(length(lightV), 0.001);
    lightV /= lightDist;

    vec3 lightColour = vec3(.8, .8, .5);

    float shininess = 4.1;
    float brightness = 6.;

    float falloff = 0.5;
    float attenuation = 1./(1.0 + lightDist*lightDist*falloff);

    float diffuse = max(dot(normal, lightV), 0.);
    float specular = pow(max(dot( reflect(-lightV, normal), -ray), 0.), 52.) * shininess; 

    vec3 texCol = (vec3(q.y / 2. - q.x / 3. + .6 , q.y / 2.2 + .2, .5) - .5) * brightness;

    vec3 colour = (texCol * (diffuse*vec3(1, .97, .92)*2. + 0.5) + lightColour*specular * f * 2.)*attenuation;
    // colour = texCol;

    return vec4(colour, 1.);
  }

  void main() {
    vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy) / min(u_resolution.y, u_resolution.x);
    
    uv *= 1.;
    
    // colour = vec3(1. - dist);
    vec4 render = renderPass(uv, vec2(1.));
    // vec2 q = vec2(0.,0.);
    // vec2 r = vec2(0.,0.);
    // vec4 render = vec4(bumpMap(uv, q, r));

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
let texture;
loader.setCrossOrigin("anonymous");
loader.load(
  'https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/noise.png',
  function do_something_with_texture(tex) {
    texture = tex;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.minFilter = THREE.LinearFilter;
    init();
    animate();
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