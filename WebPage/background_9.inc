<!-- Ripples in time background.

    This code has been taken from Codepen.io.
    Thanks to Liam Egan for posting it.
    Source code is here...

    https://codepen.io/shubniggurath/pen/XPxBEa
-->

<div id="container" touch-action="none"></div>              <!-- this is where the background goes           -->

<script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/982762/ccapture.js"></script>

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
  renderer.setPixelRatio( window.devicePixelRatio );

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

function animate(delta) {
  requestAnimationFrame( animate );
  render(delta);
}

let capturer = new CCapture( { 
  verbose: true, 
  framerate: 60,
  // motionBlurFrames: 4,
  quality: 90,
  format: 'webm',
  workersPath: 'js/'
 } );
let capturing = false;

isCapturing = function(val) {
  if(val === false && window.capturing === true) {
    capturer.stop();
    capturer.save();
  } else if(val === true && window.capturing === false) {
    capturer.start();
  }
  capturing = val;
}
toggleCapture = function() {
  isCapturing(!capturing);
}

window.addEventListener('keyup', function(e) { if(e.keyCode == 68) toggleCapture(); });

let then = 0;
function render(delta) {
  
  uniforms.u_time.value = delta * 0.0005;
  renderer.render( scene, camera );
  
  if(capturing) {
    capturer.capture( renderer.domElement );
  }
}

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/88/three.min.js"></script>
<script id="vertexShader" type="x-shader/x-vertex">
    void main() {
        gl_Position = vec4( position, 1.0 );
    }
</script>
<script id="fragmentShader" type="x-shader/x-fragment">
    uniform vec2 u_resolution;
    uniform vec2 u_mouse;
    uniform float u_time;
    uniform vec3 u_colours[ 5 ];
  
    const float multiplier = 1.2;
  
    const float zoomSpeed = 4.;
    const int layers = 12;
  
    const int octaves = 1;
    const float seed = 43758.5453123;
    const float seed2 = 73156.8473192;
  
    float random(float val) {
      return fract(sin(val) * seed);
    }
  
    vec2 random2(vec2 st, float seed){
        st = vec2( dot(st,vec2(127.1,311.7)),
                  dot(st,vec2(269.5,183.3)) );
        return -1.0 + 2.0*fract(sin(st)*seed);
    }
  
    mat2 rotate2d(float _angle){
        return mat2(cos(_angle),sin(_angle),
                    -sin(_angle),cos(_angle));
    }
  
    // Value Noise by Inigo Quilez - iq/2013
    // https://www.shadertoy.com/view/lsf3WH
    float noise(vec2 st, float seed) {
        vec2 i = floor(st);
        vec2 f = fract(st);

        vec2 u = f*f*(3.0-2.0*f);

        return mix( mix( dot( random2(i + vec2(0.0,0.0), seed ), f - vec2(0.0,0.0) ), 
                         dot( random2(i + vec2(1.0,0.0), seed ), f - vec2(1.0,0.0) ), u.x),
                    mix( dot( random2(i + vec2(0.0,1.0), seed ), f - vec2(0.0,1.0) ), 
                         dot( random2(i + vec2(1.0,1.0), seed ), f - vec2(1.0,1.0) ), u.x), u.y);
    }
  
    float fbm(in vec2 st, float seed) {
      float value = 0.0;
      float amp = 0.5;
      vec2 shift = vec2(100);
      // Rotate to reduce axial bias
      mat2 rot = mat2(cos(1.5), sin(1.5), -sin(1.5), cos(1.50));
      for (int i = 0; i < octaves; ++i) {
        value += amp * abs(noise(st, seed));
        st = rot * st * 2.0 + shift;
        amp *= 0.5;
      }
      return value;
    }
  
    vec3 renderNoise(vec2 uv) {
      float r = fbm(uv, seed);
      return vec3(r * r * 10.);
    }
  
    vec3 renderRipples(vec2 uv, float multiplier, inout vec2 id) {
      vec2 _uv = uv;
      id = floor(uv);
      vec2 rand2 = random2(id, seed);
      // _uv.y += u_time * 1. * mod(id.x, 2.) - 1.;
      // uv = fract(_uv) - .5;
      uv = mod(uv, 1.) - .5;
      
      float len = length(uv);
      
      float field = len+0.05*(u_time*5.);
      // field = mod(field, 1.);
// 
      // float ripple = smoothstep(0., 0.5 - multiplier, sin(field*80.0 * length(rand2))) + smoothstep(0.5 + multiplier, 0., sin(field*80.0 * length(rand2)));
      float ripple = smoothstep(0.99, -.5, sin(field*80.0 * length(rand2)*length(rand2)));
      ripple *= smoothstep(0.4,.8,clamp(1. - len * 1.2,0.0,1.0));
      
      return vec3(ripple*ripple*ripple*2.);
    }
  
    // The render function is where we render the pattern to be added to the layer
    vec3 render(vec2 uv, float multiplier, inout vec2 id) {
      vec3 n = renderNoise(uv*.5);
      n *= n*8.;
      return renderRipples(uv, multiplier, id)*(.1+n);

    }
  
    vec3 renderLayer(int layer, int layers, vec2 uv, inout float opacity, inout vec2 id) {
      // Scale
      // Generating a scale value between zero and 1 based on a mod of u_time
      // A frequency of 10 dixided by the layer index (10 / layers * layer)
      float scale = mod((u_time + zoomSpeed / float(layers) * float(layer)) / zoomSpeed, -1.);
      uv *= 8.; // The initial scale. Increasing this makes the cells smaller and the "speed" apepar faster
      uv *= (1. + random(float(layer)));
      uv *= scale; // then modifying the overall scale by the generated amount
      // uv += .5*float(layer);
      uv = rotate2d(u_time / 10.) * uv; // rotarting
      uv += vec2(1.5) * float(layer) * random(float(layer+10)); // ofsetting the UV by an arbitrary amount to make the layer appear different
      
      // id = random2(floor(uv), seed);

      // render
      vec3 pass = render(uv * multiplier, multiplier, id); // render the pass

       // this is the opacity of the layer fading in from the "bottom"
      opacity = clamp(1. + scale * 1.1, 0., 1.);
      float _opacity = opacity;

      // This is the opacity of the layer fading out at the top (we want this minimal, hence the smoothstep)
      float endOpacity = 1.;
      endOpacity = smoothstep(0., 0.05, scale * -1.);
      opacity += endOpacity;

      return clamp(pass * _opacity * endOpacity, 0., 1.);
    }
  
  // smooth min
  // reference: http://iquilezles.org/www/articles/smin/smin.htm
  float smin(float a, float b, float k) {
      float res = exp(-k*a) + exp(-k*b);
      return -log(res)/k;
  }

    void main() {
        vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy);

        if(u_resolution.y < u_resolution.x) {
          uv /= u_resolution.y;
        } else {
          uv /= u_resolution.x;
        }

        // uv.x += sin(u_time) * .5;

        vec3 colour = vec3(sin(u_time+uv.y)*0.2, cos(u_time+uv.x)*0.2, cos(u_time*.3+uv.x-uv.y)*0.3)*.5+.1;

        float opacity = 1.;
        float opacity_sum = 1.;

        for(int i = 1; i <= layers; i++) {
          vec2 id;
          vec3 layer = renderLayer(i, layers, uv, opacity, id);
          float t = u_time * .1 + (1. + length(id));
          // vec3 cellcolour = vec3(sin(t)*0.2, cos(t)*0.2, cos(t*.3)*0.3)*.5+.5;
          vec3 cellcolour = colour*(1. + length(id))*.5+.2;
          colour = mix(colour, cellcolour, layer);
          opacity_sum += opacity;
        }

        gl_FragColor = vec4(colour,1.0);
      
      gl_FragColor.rgb = vec3(
        min(gl_FragColor.r, gl_FragColor.b*4.),
        min(gl_FragColor.g, gl_FragColor.r*4.),
        min(gl_FragColor.b, gl_FragColor.g*4.));
    }
</script>
