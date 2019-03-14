<!-- Lattice background.

    This code has been taken from Codepen.io.
    Thanks to Liam Egan for posting it.
    Source code is here...

    https://codepen.io/shubniggurath/pen/ERjXqy
-->

<div id="container" touch-action="none"></div>              <!-- this is where the background goes           -->

<script id="vertexShader" type="x-shader/x-vertex">
    void main() {
        gl_Position = vec4( position, 1.0 );
    }
</script>
<script id="fragmentShader" type="x-shader/x-fragment">
    uniform vec2 u_resolution;
    uniform float u_time;
    uniform vec2 u_mouse;
  
    #define reflections true
  
    const int octaves = 2;
    const float seed = 43758.5453123;
    const float seed2 = 73156.8473192;
    // Epsilon value
    const float eps = 0.005;
  
    const vec3 ambientLight = 0.99 * vec3(1.0, 1.0, 1.0);
    const vec3 light1Pos = vec3(10., 5.0, -25.0);
    const vec3 light1Intensity = vec3(0.35);
    const vec3 light2Pos = vec3(-20., -25.0, 85.0);
    const vec3 light2Intensity = vec3(0.2);
  
    // movement variables
    vec3 movement = vec3(.0);

    // Gloable variables for the raymarching algorithm.
    const int maxIterations = 256;
    const int maxIterationsShad = 16;
    const float stepScale = .7;
    const float stopThreshold = 0.001;
  
    // Holy fuck balls, fresnel!
    const float bias = .5;
    const float scale = 6.;
    const float power = 8.1;
  
  
  mat4 rotationMatrix(vec3 axis, float angle)
  {
      axis = normalize(axis);
      float s = sin(angle);
      float c = cos(angle);
      float oc = 1.0 - c;

      return mat4(oc * axis.x * axis.x + c,           oc * axis.x * axis.y - axis.z * s,  oc * axis.z * axis.x + axis.y * s,  0.0,
                  oc * axis.x * axis.y + axis.z * s,  oc * axis.y * axis.y + c,           oc * axis.y * axis.z - axis.x * s,  0.0,
                  oc * axis.z * axis.x - axis.y * s,  oc * axis.y * axis.z + axis.x * s,  oc * axis.z * axis.z + c,           0.0,
                  0.0,                                0.0,                                0.0,                                1.0);
  }
  
  
  float length2( vec2 p )
  {
    return sqrt( p.x*p.x + p.y*p.y );
  }

  float length6( vec2 p )
  {
    p = p*p*p; p = p*p;
    return pow( p.x + p.y, 1.0/6.0 );
  }

  float length8( vec2 p )
  {
    p = p*p; p = p*p; p = p*p;
    return pow( p.x + p.y, 1.0/8.0 );
  }
  
  // smooth min
  // reference: http://iquilezles.org/www/articles/smin/smin.htm
  float smin(float a, float b, float k) {
      float res = exp(-k*a) + exp(-k*b);
      return -log(res)/k;
  }
  
  vec3 random3( vec3 p ) {
      return fract(sin(vec3(dot(p,vec3(127.1,311.7,319.8)),dot(p,vec3(269.5,183.3, 415.2)),dot(p,vec3(362.9,201.5,134.7))))*43758.5453);
  }
  vec2 random2( vec2 p ) {
      return fract(sin(vec2(dot(p,vec2(127.1,311.7)),dot(p,vec2(269.5,183.3))))*43758.5453);
  }
  
  // The world!
  float world_sdf(in vec3 p) {
    float world = 10.;
    
    p.x += sin(p.z) * .5;
    p.y += cos(p.z) * .5;
    p.z += cos(p.x * .2) * 4.2;
    // p.z += length(p.xy) * .5;
    
    p = mod(p, 3.) - 1.5;
    
    // float amt = sin(u_time) * 40. + 50.;
    float amt = 60.;
    
    world = smin(length8(p.xy) - .3, length8(p.zy) - .3, amt);
    
    world = smin(world, length8(p.xz) - .3, amt);
    
    // world = min(length6(p.xy) - .05, length6(p.zy) - .05);
    // world = min(world, length6(p.xz) - .05);
    
    // world = sdSphere(p, .5);
    
    return world;
  }
  
  // Fuck yeah, normals!
  vec3 calculate_normal(in vec3 p)
  {
    const vec3 small_step = vec3(0.0001, 0.0, 0.0);
    
    float gradient_x = world_sdf(vec3(p.x + eps, p.y, p.z)) - world_sdf(vec3(p.x - eps, p.y, p.z));
    float gradient_y = world_sdf(vec3(p.x, p.y + eps, p.z)) - world_sdf(vec3(p.x, p.y - eps, p.z));
    float gradient_z = world_sdf(vec3(p.x, p.y, p.z  + eps)) - world_sdf(vec3(p.x, p.y, p.z - eps));
    
    vec3 normal = vec3(gradient_x, gradient_y, gradient_z);

    return normalize(normal);
  }

  // Raymarching.
  float rayMarching( vec3 origin, vec3 dir, float start, float end, inout float field ) {
    
    float sceneDist = 1e4;
    float rayDepth = start;
    for ( int i = 0; i < maxIterations; i++ ) {
      sceneDist = world_sdf( origin + dir * rayDepth ); // Distance from the point along the ray to the nearest surface point in the scene.

      if (( sceneDist < stopThreshold ) || (rayDepth >= end)) {        
        break;
      }
      // We haven't hit anything, so increase the depth by a scaled factor of the minimum scene distance.
      rayDepth += sceneDist * stepScale;
    }
  
    if ( sceneDist >= stopThreshold ) rayDepth = end;
    else rayDepth += sceneDist;
      
    // We've used up our maximum iterations. Return the maximum distance.
    return rayDepth;
  }
  

  // Shadows
  // Reference at: http://www.iquilezles.org/www/articles/rmshadows/rmshadows.htm
  float softShadow(vec3 ro, vec3 lightPos, float start, float k){
    
      vec3 rd = lightPos - ro;
      float end = length(rd);

      float shade = 1.0;

      float dist = start;
      float stepDist = start;

      for (int i=0; i<maxIterationsShad; i++){
          float h = world_sdf(ro + rd*dist);
          shade = min(shade, k*h/dist);
          
          dist += min(h, stepDist*2.); // The best of both worlds... I think. 
          
          if (h<0.001 || dist > end) break; 
      }

      return min(max(shade, 0.) + 0.3, 1.0); 
  }
  
  /**
   * Lighting
   * This stuff is way way better than the model I was using.
   * Courtesy Shane Warne
   * Reference: http://raymarching.com/
   * -------------------------------------
   * */
  
  // Lighting.
  vec3 lighting( vec3 sp, vec3 camPos, int reflectionPass, float dist, float field, vec3 rd, inout vec3 surfNormal, inout float shade) {
    
    // Start with black.
    vec3 sceneColor = vec3(0.0);

    vec3 objColor = vec3(.85);
    if(reflections) {
      vec3 objColor = vec3(.7);
    }

    // Obtain the surface normal at the scene position "sp."
    surfNormal = calculate_normal(sp);

    // Lighting.

    // lp - Light position. Keeping it in the vacinity of the camera, but away from the objects in the scene.
    vec3 lp = vec3(1., -1.0, 1.0) + movement;
    // ld - Light direction.
    vec3 ld = lp-sp;
    // lcolor - Light color.
    vec3 lcolor = vec3(0.92, 0.92, 0.94) * .5;
    
     // Light falloff (attenuation).
    float len = length( ld ); // Distance from the light to the surface point.
    ld /= len; // Normalizing the light-to-surface, aka light-direction, vector.
    // float lightAtten = min( 1.0 / ( 0.15*len*len ), 1.0 ); // Removed light attenuation for this because I want the fade to white
    
    float sceneLen = length(camPos - sp); // Distance of the camera to the surface point
    float sceneAtten = min( 1.0 / ( 0.015*sceneLen*sceneLen ), 1.0 ); // Keeps things between 0 and 1.   

    // Obtain the reflected vector at the scene position "sp."
    vec3 ref = reflect(-ld, surfNormal);
    
    float ao = 1.0; // Ambient occlusion.

    float ambient = .3; //The object's ambient property.
    float specularPower = 20.; // The power of the specularity. Higher numbers can give the object a harder, shinier look.
    float diffuse = max( 0.0, dot(surfNormal, ld) ); //The object's diffuse value.
    float specular = max( 0.0, dot( ref, normalize(camPos-sp)) ); //The object's specular value.
    specular = pow(specular, specularPower); // Ramping up the specular value to the specular power for a bit of shininess.
    
    // Holy fuck balls, fresnel!
    // specular = max(0.0, min(1.0, bias + scale * (1.0 + length(camPos-sp * surfNormal)) * power));
    shade = bias + (scale * pow(1.0 + dot(normalize(sp-camPos), surfNormal), power));
    	
    // Bringing all the lighting components togethr to color the screen pixel.
    // sceneColor = objColor * (diffuse*0.8+ambient) + specular*0.5 * shade;
    sceneColor = (objColor*(diffuse*0.8+ambient)*(shade+.3)+specular*0.5)*lcolor*1.3;
    
    // sceneColor = vec3(shade);
    
    if(reflectionPass == 0) {
      float shadow = softShadow(sp, lp, .01, 2.);
      sceneColor *= shadow + .4;
    }
    sceneColor = mix(sceneColor, vec3(1.), 1.-sceneAtten*sceneAtten); // fog
    
    return sceneColor;

  }
  
  vec3 camPath(float delta) {
    return vec3(0., 0., delta * 5.);
    return vec3(clamp(cos(delta), -2., 2.) * 2., clamp(sin(delta), -2., 2.) * 2., delta * 5.);
  }

    void main() {
      
      // Setting up our screen coordinates.
      vec2 aspect = vec2(u_resolution.x/u_resolution.y, 1.0); //
      vec2 uv = (2.0*gl_FragCoord.xy/u_resolution.xy - 1.0)*aspect;
      
      // This just gives us a touch of fisheye
      // uv *= 1. + dot(uv, uv) * 0.4;
      
      // movement
      movement = camPath(u_time);
      
      // The sin in here is to make it look like a walk.
      vec3 lookAt = vec3(0., 0., 1.0) + camPath(u_time + 1.) * vec3(2., 2., 1.);  // This is the point you look towards, or at, if you prefer.
      vec3 camera_position = vec3(0., 0., -1.0); // This is the point you look from, or camera you look at the scene through. Whichever way you wish to look at it.
      
      lookAt += movement;
      // lookAt.z += sin(u_time / 10.) * .5;
      // lookAt.x += cos(u_time / 10.) * .5;
      camera_position += movement;
      
      vec3 forward = normalize(lookAt-camera_position); // Forward vector.
      vec3 right = normalize(vec3(forward.z, 0., -forward.x )); // Right vector... or is it left? Either way, so long as the correct-facing up-vector is produced.
      vec3 up = normalize(cross(forward,right)); // Cross product the two vectors above to get the up vector.

      // FOV - Field of view.
      float FOV = 0.6;

      // ro - Ray origin.
      vec3 ro = camera_position; 
      // rd - Ray direction.
      vec3 rd = normalize(forward + FOV*uv.x*right + FOV*uv.y*up);
      
      float c = cos(u_time * .5);
      float s = sin(u_time * .5);
      
      rd.xy *= mat2(c, -s, s, c);
      rd.xz *= mat2(c, -s, s, c);
      c = cos(u_time * .2 + 2.);
      s = sin(u_time * .2 + 2.);
      rd.yz *= mat2(c, -s, s, c);
      
      // Ray marching.
      const float clipNear = 0.0;
      const float clipFar = 16.0;
      float field = 0.;
      float dist = rayMarching(ro, rd, clipNear, clipFar, field );
      if ( dist >= clipFar ) {
        gl_FragColor = vec4(vec3(1.), 1.0);
        return;
      }

      // sp - Surface position. If we've made it this far, we've hit something.
      vec3 sp = ro + rd*dist;

      // Light the pixel that corresponds to the surface position. The last entry indicates that it's not a reflection pass
      // which we're not up to yet.
      vec3 surfNormal;
      float shade;
      vec3 sceneColor = lighting( sp, camera_position, 0, dist, field, rd, surfNormal, shade);
      
      if(reflections) {
        ro = sp;
        rd = reflect(rd, surfNormal);
        dist = rayMarching(ro, rd, stopThreshold*5., clipFar, field );
        if ( dist < clipFar ) {
          sp = ro + rd*dist;
          sceneColor += lighting( sp, camera_position, 1, dist, field, rd, surfNormal, shade) * .2;
        } else {
          sceneColor += vec3(.2);
        }
      }

      // Clamping the lit pixel, then put it on the screen.
      gl_FragColor = vec4(clamp(sceneColor, 0.0, 1.0), 1.0);


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
  // renderer.setPixelRatio( window.devicePixelRatio );

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
  uniforms.u_resolution.value.x = window.innerWidth;
  uniforms.u_resolution.value.y = window.innerHeight;
}


function animate(delta) {
  requestAnimationFrame( animate );
  render(delta);
}






let capturer = new CCapture( { 
  verbose: true, 
  framerate: 30,
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