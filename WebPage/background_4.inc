<!-- Gem soup background.

    This code has been taken from Codepen.io.
    Thanks to Liam Egan for posting it.
    Source code is here...

    https://codepen.io/shubniggurath/pen/vWQmOr

-->

<div id="container" touch-action="none"></div>              <!-- this is where the background goes           -->

<script id="vertexShader" type="x-shader/x-vertex">
    void main() {
        gl_Position = vec4( position, 1.0 );
    }
</script>
<script id="fragmentShader" type="x-shader/x-fragment">
  
  // Liam Egan
  // 2017
  
  
    uniform vec2 u_resolution;
    uniform vec2 u_mouse;
    uniform float u_time;
    uniform vec3 u_colours[ 5 ];
  
    const float multiplier = .5;
    const int octaves = 4;
  
    const float seed2 =   43758.5453123;
    const float seed =    86135.7315468;
    const float seed3 =   13883.2513567;
  
    mat2 rotate2d(float _angle){
        return mat2(cos(_angle),sin(_angle),
                    -sin(_angle),cos(_angle));
    }
  
    float random(float val) {
      return fract(sin(val) * seed);
    }
  
    vec2 random2dFromFloat(float val) {
      return vec2(random(val * seed3), random(val * seed * seed2 / 2.));
    }
  
    vec2 random2(vec2 st){
        st = vec2( dot(st,vec2(127.1,311.7)),
                  dot(st,vec2(269.5,183.3)) );
        return -1.0 + 2.0*fract(sin(st)*43758.5453123);
    }
  
    float random2d(vec2 uv) {
      return fract(
                sin(
                  dot( uv.xy, vec2(12.9898, 78.233) )
                ) * seed);
    }
  
    // Value Noise by Inigo Quilez - iq/2013
    // https://www.shadertoy.com/view/lsf3WH
    float noise(vec2 st) {
        vec2 i = floor(st);
        vec2 f = fract(st);

        vec2 u = f*f*(3.0-2.0*f);

        return mix( mix( dot( random2(i + vec2(0.0,0.0) ), f - vec2(0.0,0.0) ), 
                         dot( random2(i + vec2(1.0,0.0) ), f - vec2(1.0,0.0) ), u.x),
                    mix( dot( random2(i + vec2(0.0,1.0) ), f - vec2(0.0,1.0) ), 
                         dot( random2(i + vec2(1.0,1.0) ), f - vec2(1.0,1.0) ), u.x), u.y);
    }
  
    vec3 plotCircle(vec2 pos, vec2 uv, float size) {
      return vec3(smoothstep(size, size + 0.05, length(uv - pos)));
    }
  
    vec3 render(vec2 uv, vec2 mouse, vec3 colour, float multiplier) {
      
      vec2 grid = floor(uv);
      vec2 gridPos = fract(uv);
      
      float min_dist = 2.;
      vec2 min_point;
      
      float s_min_dist = 2.;
      vec2 s_min_point;
      
      for (int y= -1; y <= 1; y++) {
        for (int x= -1; x <= 1; x++) {
          
          // Neighbor place in the grid
          vec2 neighbor = vec2(float(x),float(y));

          // Random position from current + neighbor place in the grid
          vec2 point = random2(grid + neighbor);
          point = 1.5 * mouse.y * point; // normalise the point;

          // Animate the point
          point = 0.5 + 0.4*sin((u_time / 10.) + 6.2831*point);

          // Vector between the pixel and the point
          vec2 diff = neighbor + point - gridPos;

          // Distance to the point
          float dist = length(diff);

          // Keep the closer distance
          // m_dist = min(min_dist, dist);
          
          if(min_dist > dist) {
            s_min_dist = min_dist;
            s_min_point = min_point;
            min_dist = dist;
            min_point = grid + neighbor;
          } else if(s_min_dist > dist) {
            s_min_dist = dist;
            s_min_point = grid + neighbor;
          }
        }
      }
      
      float old_min_dist = min_dist;
      min_dist = s_min_dist - min_dist;
      
      colour = vec3(1.);
      
      float segment = floor(u_time / 2.);
      float intermix = u_time / 2. - segment;
      float index = mod(floor(random2d(min_point) * multiplier) + segment, multiplier);
      float next_index = mod(floor(random2d(min_point) * multiplier) + segment + 1., multiplier);
      colour = vec3(0.1);
      vec3 next_colour = vec3(0.1);
      if(index == 0.) {
        colour = u_colours[0];
      } else if(index == 1.) {
        colour = u_colours[1];
      } else if(index == 2.) {
        colour = u_colours[2];
      } else if(index == 3.) {
        colour = u_colours[3];
      } else if(index == 4.) {
        colour = u_colours[4];
      }
      if(next_index == 0.) {
        next_colour = u_colours[0];
      } else if(next_index == 1.) {
        next_colour = u_colours[1];
      } else if(next_index == 2.) {
        next_colour = u_colours[2];
      } else if(next_index == 3.) {
        next_colour = u_colours[3];
      } else if(next_index == 4.) {
        next_colour = u_colours[4];
      }
      if(index != next_index) {
        colour = mix(colour, next_colour, intermix);
      }
      // colour *= 0.5;
      // colour -= vec3(min_dist) * -min_dist;
      
      // colour += vec3(sqrt(min_dist)) * sqrt(min_dist);
      
      // colour *= smoothstep(0.0, 0.2, fract(min_dist * 5.)) * 0.5;
      
      colour -= vec3(1.0 - smoothstep(0.0, 0.03, min_dist));
      // colour -= old_min_dist * (0.5 + 0.5*sin(64.0*old_min_dist))*vec3(.1);
      // colour /= old_min_dist / 0.5;
      colour /= s_min_dist / 0.5;
      // colour += vec3(min_dist) / 2.;
      
      return colour;
    }

    void main() {
      vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy);
      
      if(u_resolution.y < u_resolution.x) {
        uv /= u_resolution.y;
      } else {
        uv /= u_resolution.x;
      }
      
      vec2 mouse = (u_mouse / u_resolution) - 0.5;
      mouse.y *= -1.;
      vec3 colour = vec3(0.);
      
      // uv *= 1. + (3. + mouse.x * 15.);
      uv *= 4.;
      
      uv.x += u_time / 5.;
      uv = rotate2d(sin(u_time / 100.)) * uv;
      uv.x += cos(u_time / 150.) * 100.;
      uv.y += cos(u_time / 180.) * 100.;
      
      for (int i=octaves; i > 0; i--) {
      // for (int i= 1; i <= octaves; i++) {
        colour += render(uv * (float(i + 1) * multiplier), mouse, colour, 10.);
        colour /= 2.;
      }
      
      // colour *= sqrt(colour) * 2.;
      
      gl_FragColor = vec4(colour * colour * 3., 1.);
    }
</script>

<script>
let container;
let camera, scene, renderer;
let uniforms;

function init() {
  container = document.getElementById( 'container' );

  camera = new THREE.Camera();
  camera.position.z = 1;

  scene = new THREE.Scene();

  var geometry = new THREE.PlaneBufferGeometry( 2, 2 );

  var colours = [];
  colours.push([138, 79, 125]);
  colours.push([136, 120, 128]);
  colours.push([136, 160, 150]);
  colours.push([187, 171, 139]);
  colours.push([239, 130, 117]);
  for(var i = 0; i < colours.length; i++) {
    var c = colours[i];
    colours[i] = new THREE.Vector3(c[0] / 255, c[1] / 255, c[2] / 255);
  }
  window.colours = colours;
  
  uniforms = {
    u_time: { type: "f", value: 1.0 },
    u_resolution: { type: "v2", value: new THREE.Vector2() },
    u_mouse: { type: "v2", value: new THREE.Vector2() },
    u_colours: { type: "v3v", value: colours }
  };

  var material = new THREE.ShaderMaterial( {
    uniforms: uniforms,
    vertexShader: document.getElementById( 'vertexShader' ).textContent,
    fragmentShader: document.getElementById( 'fragmentShader' ).textContent
  } );

  var mesh = new THREE.Mesh( geometry, material );
  scene.add( mesh );

  renderer = new THREE.WebGLRenderer();
  renderer.setPixelRatio( window.devicePixelRatio );

  container.appendChild( renderer.domElement );

  onWindowResize();
  window.addEventListener( 'resize', onWindowResize, false );

  document.onmousemove = function(e){
    uniforms.u_mouse.value.x = e.pageX
    uniforms.u_mouse.value.y = e.pageY
  }
}

function onWindowResize( event ) {
  renderer.setSize( window.innerWidth, window.innerHeight );
  uniforms.u_resolution.value.x = renderer.domElement.width;
  uniforms.u_resolution.value.y = renderer.domElement.height;
}

function animate() {
  // console.log(uniforms.u_colours);
  requestAnimationFrame( animate );
  render();
}

function render() {
  uniforms.u_time.value += 0.05;
  renderer.render( scene, camera );
}

init();
animate();
</script>