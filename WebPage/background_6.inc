<!-- Emerald city dreaming background.

    This code has been taken from Codepen.io.
    Thanks to Liam Egan for posting it.
    Source code is here...

    https://codepen.io/shubniggurath/pen/yjKQLp
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
    uniform float u_perspective;
    uniform vec3 u_colours[ 5 ];
  
    // Epsilon value
    const float eps = 0.005;
  
  
    // movement variables
    vec3 movement = vec3(.0);
    vec3 camera_position;

    // Gloable variables for the raymarching algorithm.
    const int maxIterations = 512;
    const int maxIterationsShad = 16;
    const float stepScale = .7;
    const float stopThreshold = 0.001;
  
  #define PI 3.14159265359
  #define TAU 6.28318530718
    
  // float r1 = 0.1 + ((u_mouse.y + 0.5) * .1);
  // float r2 = 0.4 + (u_mouse.x * .2);
  float r1 = 0.3;
  float r2 = 0.9;
  
  // These awesome complex Math functions curtesy of 
  // https://github.com/mkovacs/reim/blob/master/reim.glsl
  vec2 cCis(float r);
  vec2 cLog(vec2 c); // principal value
  vec2 cInv(vec2 c);
  float cArg(vec2 c);
  float cAbs(vec2 c);
  
  vec2 cMul(vec2 a, vec2 b);
  vec2 cDiv(vec2 a, vec2 b);

  vec2 cCis(float r)
  {
    return vec2( cos(r), sin(r) );
  }
  vec2 cExp(vec2 c)
  {
    return exp(c.x) * cCis(c.y);
  }
  vec2 cConj(vec2 c)
  {
    return vec2(c.x, -c.y);
  }
  vec2 cInv(vec2 c)
  {
    return cConj(c) / dot(c, c);
  }
  vec2 cLog(vec2 c)
  {
    return vec2( log( cAbs(c) ), cArg(c) );
  }
  float cArg(vec2 c)
  {
    return atan(c.y, c.x);
  }
  float cAbs(vec2 c)
  {
    return length(c);
  }
  vec2 cMul(vec2 a, vec2 b)
  {
    return vec2(a.x*b.x - a.y*b.y, a.x*b.y + a.y*b.x);
  }
  vec2 cDiv(vec2 a, vec2 b)
  {
    return cMul(a, cInv(b));
  }
  
  
  
  vec2 Droste(vec2 uv) {
    
    // 5. Take the tiled strips back to ordinary space.
    uv = cLog(uv); 
    // 4. Scale and rotate the strips
    float scale = log(r2/r1);
    float angle = atan(scale/(2.0*PI));
    uv = cDiv(uv, cExp(vec2(0,angle))*cos(angle)); 
    // 3. this simulates zooming in the tile
    // uv += u_time;
    // 2. Tile the strips
    uv.x = mod(uv.x,log(r2/r1)); 
    // 1. Take the annulus to a strip
    uv = cExp(uv)*r1;
    
    return uv;
  }
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
  
  // Distance function primitives
  // Reference: http://iquilezles.org/www/articles/distfunctions/distfunctions.htm
  float sdBox( vec3 p, vec3 b )
  {
    vec3 d = abs(p) - b;
    return min(max(d.x,max(d.y,d.z)),0.0) + length(max(d,0.0));
  }
  float udBox( vec3 p, vec3 b )
  {
    return length(max(abs(p)-b,0.0));
  }
  float udRoundBox( vec3 p, vec3 b, float r )
  {
    return length(max(abs(p)-b,0.0))-r;
  }
  float sdSphere( vec3 p, float s )
  {
    return length(p)-s;
  }
  float sdCylinder( vec3 p, vec3 c )
  {
    return length(p.xz-c.xy)-c.z;
  }
  float sdCappedCylinder( vec3 p, vec2 h )
  {
    vec2 d = abs(vec2(length(p.xz),p.y)) - h;
    return min(max(d.x,d.y),0.0) + length(max(d,0.0));
  }
  float sdTorus82( vec3 p, vec2 t )
  {
    vec2 q = vec2(length2(p.xz)-t.x,p.y);
    return length8(q)-t.y;
  }
  float sdPlane( vec3 p)
  {
    return p.y;
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
    
    float dist = length(p - camera_position) * sin(u_time * .2) * .2;
    float c = cos(dist);
    float s = sin(dist);
    
    vec3 _p = floor(p);
    
    p.xy *= mat2(c, -s, s, c);
    
    // p = mod(p, 1.0) - .5;
    p = mod(p, 2.0) - 1.;
    
    // world = sdBox(p, vec3(.46, .2, .46));
    world = sdBox(p, vec3(.96, .5, .96));
    
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
  float rayMarching( vec3 origin, vec3 dir, float start, float end, inout float field, in vec3 drift ) {
    
    float sceneDist = 1e4;
    float rayDepth = start;
    float so = end;
    bool inside = false;
    for ( int i = 0; i < maxIterations; i++ ) {
      sceneDist = world_sdf( origin + dir * rayDepth ); // Distance from the point along the ray to the nearest surface point in the scene.
      
      if((rayDepth >= end) || field >= 1.) {        
        break;
      }

      if (( sceneDist < stopThreshold )) {
        // We haven't hit anything, so increase the depth by a scaled factor of the minimum scene distance.
        float maxsd = abs(max(sceneDist, -1.));
        // float stepsize = abs(maxsd - 1.) * .02;
        // field += maxsd * 0.01;
        float stepsize = abs(maxsd - .6) * .02;
        field += .005 * maxsd;
        rayDepth += stepsize;
        
        if(!inside) {
          so = rayDepth + sceneDist;
          inside = true;
        }
        
        if(( sceneDist + .1 > stopThreshold )) {
          rayDepth += stopThreshold * 4.; // Arbitrary, but we'll see
        }
      } else {
        field *= .99;
        // We haven't hit anything, so increase the depth by a scaled factor of the minimum scene distance.
        rayDepth += sceneDist * stepScale;
      }
    }
    
    return so;
  
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

  // Based on original by IQ - optimized to remove a divide
  float calculateAO(vec3 p, vec3 n)
  {
     const float AO_SAMPLES = 5.0;
     float r = 0.0;
     float w = 1.0;
     for (float i=1.0; i<=AO_SAMPLES; i++)
     {
        float d0 = i * 0.15; // 1.0/AO_SAMPLES
        r += w * (d0 - world_sdf(p + n * d0));
        w *= 0.5;
     }
     return 1.0-clamp(r,0.0,1.0);
  }
  
  vec3 getColour(float rand) {
    vec3 colour = vec3(0.);
    float index = floor(rand * 5.);
    
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
    
    return colour * colour * 1.3;
  }
  
  /**
   * Lighting
   * This stuff is way way better than the model I was using.
   * Courtesy Shane Warne
   * Reference: http://raymarching.com/
   * -------------------------------------
   * */
  
  // Lighting.
  vec3 lighting( vec3 sp, vec3 camPos, int reflectionPass, float dist, float field, vec3 rd) {
    
    vec3 _sp = sp;
    float _dist = length(sp - camera_position) * sin(u_time * .2) * .2;
    float c = cos(_dist);
    float s = sin(_dist);
    
    _sp.xy *= mat2(c, -s, s, c);
    
    // vec3 grid = floor(_sp * .25);
    vec3 grid = floor(_sp * .5);
    vec2 rand = random2(grid.xz);
    
    // Start with black.
    vec3 sceneColor = vec3(0.0);

    vec3 objColor = getColour(rand.x);
    // objColor *= objColor * .5;
    objColor *= 3.;
    // objColour += length(sp.xy);

    // Obtain the surface normal at the scene position "sp."
    vec3 surfNormal = calculate_normal(sp);

    // Lighting.

    // lp - Light position. Keeping it in the vacinity of the camera, but away from the objects in the scene.
    vec3 lp = vec3(0., 0.0, -1.0) + movement;
    // ld - Light direction.
    vec3 ld = lp-sp;
    // lcolor - Light color.
    vec3 lcolor = vec3(.8,1.17,0.92);
    
     // Light falloff (attenuation).
    float len = length( ld ); // Distance from the light to the surface point.
    ld /= len; // Normalizing the light-to-surface, aka light-direction, vector.
    // float lightAtten = min( 1.0 / ( 0.15*len*len ), 1.0 ); // Removed light attenuation for this because I want the fade to white
    
    float sceneLen = length(camPos - sp); // Distance of the camera to the surface point
    float sceneAtten = min( 1.0 / ( 0.005*sceneLen*sceneLen ), 1.0 ); // Keeps things between 0 and 1.   

    // Obtain the reflected vector at the scene position "sp."
    vec3 ref = reflect(-ld, surfNormal);
    
    float ao = 1.0; // Ambient occlusion.
    // ao = calculateAO(sp, surfNormal); // Ambient occlusion.

    float ambient = .5; //The object's ambient property.
    float specularPower = 0.; // The power of the specularity. Higher numbers can give the object a harder, shinier look.
    float diffuse = max( 0.0, dot(surfNormal, ld) ); //The object's diffuse value.
    float specular = max( 0.0, dot( ref, normalize(camPos-sp)) ); //The object's specular value.
    // specular = pow(specular, specularPower); // Ramping up the specular value to the specular power for a bit of shininess.
    	
    // Bringing all the lighting components togethr to color the screen pixel.
    sceneColor += (objColor*(diffuse*1.8+ambient)+specular*.9)*lcolor*.8;
    sceneColor = mix(sceneColor, vec3(0.), 1.-sceneAtten*sceneAtten); // fog
    
    // float shadow = softShadow(sp, lp, .02, 10.);
    // sceneColor *= shadow + .8;
    
    return sceneColor * field;

  }
  
  void camera(vec2 uv, vec3 eye, vec3 at, vec3 up, out vec3 from, out vec3 increment)
  {	
    vec3 cw = normalize(at - eye);
    vec3 cu = normalize(cross(cw, up));
    vec3 cv = normalize(cross(cu,cw));

    float fov = acos(dot(cw, normalize(cu * uv.x)));
    float screenSize = (10.0 / (2.0 * tan(abs(fov) / 2.0)));
    vec3 virtscreen = eye + cw * 2.0 + (cu * uv.x + cv * uv.y) * screenSize;
    from = eye + (cu * uv.x + cv * uv.y) * (0.7 + 0.2 * u_perspective) * screenSize;
    increment = normalize(virtscreen - from);
  }
  
  vec3 camPath(float t) {
    return vec3(sin(t) * 3., 0., t * 20.);
  }

    void main() {
      
      // Setting up our screen coordinates.
      vec2 aspect = vec2(u_resolution.x/u_resolution.y, 1.0); //
      vec2 uv = (2.0*gl_FragCoord.xy/u_resolution.xy - 1.0)*aspect;
      
      // uv = Droste(uv);
      
      // This just gives us a touch of fisheye
      // uv *= 1. + dot(uv, uv) * 0.4;
      
      // movement
      movement = camPath(u_time);
      
      // The sin in here is to make it look like a walk.
      vec3 lookAt = vec3(-0., 0.2, 0.);  // This is the point you look towards, or at, if you prefer.
      camera_position = vec3(0., 0., -1.0); // This is the point you look from, or camera you look at the scene through. Whichever way you wish to look at it.
      vec3 drift = camera_position - vec3(sin(u_time - .1), 0., -1.0);
      
      lookAt += movement;
      lookAt.z += sin(u_time * .5 + 1.3) * 5.5;
      lookAt.x += cos(u_time * .5 + 1.3) * 5.5;
      camera_position += movement;
      
      vec3 forward = normalize(lookAt-camera_position); // Forward vector.
      vec3 right = normalize(vec3(forward.z, 0., -forward.x )); // Right vector... or is it left? Either way, so long as the correct-facing up-vector is produced.
      vec3 up = normalize(cross(forward,right)); // Cross product the two vectors above to get the up vector.
      // vec3 right = normalize(vec3(forward.z, 0., -forward.x )); // Right vector. // CV
      // vec3 up = normalize(cross(forward,right)); // Cross product the two vectors above to get the up vector.

      // FOV - Field of view.
      float FOV = 1.;

      // ro - Ray origin.
      vec3 ro = camera_position;
      // rd - Ray direction.
      vec3 rd = normalize((forward + FOV*uv.x*right + FOV*uv.y*up));
      
      float rot = camPath(u_time - 1.).x * .1;
      rd.yz *= mat2(cos(rot), -sin(rot), sin(rot), cos(rot));
      rot = camPath(u_time + 1.).x * .1;
      rd.xy *= mat2(cos(rot), -sin(rot), sin(rot), cos(rot));
      
      // Ray marching.
      const float clipNear = 0.0;
      const float clipFar = 32.0;
      float field = 0.;
      float dist = rayMarching(ro, rd, clipNear, clipFar, field, drift );
      
      gl_FragColor = vec4(field);
//       if ( dist >= clipFar ) {
//         gl_FragColor = vec4(vec3(1.), 1.0);
//         return;
//       }

//       // sp - Surface position. If we've made it this far, we've hit something.
      vec3 sp = ro + rd*dist;

//       // Light the pixel that corresponds to the surface position. The last entry indicates that it's not a reflection pass
//       // which we're not up to yet.
      vec3 sceneColor = lighting( sp, camera_position, 0, dist, field, rd);
      
      // sceneColor += (1. - clamp(sceneColor, 0., 1.)).g;

//       // Clamping the lit pixel, then put it on the screen.
      gl_FragColor = vec4(sceneColor, 1.0);


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

var colours = [];
colours.push([38, 125, 79]);
colours.push([106, 140, 128]);
colours.push([136, 200, 150]);
colours.push([137, 171, 89]);
colours.push([139, 230, 117]);
for(var i = 0; i < colours.length; i++) {
  var c = colours[i];
  colours[i] = new THREE.Vector3(c[0] / 255, c[1] / 255, c[2] / 255);
}

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
    u_mouse: { type: "v2", value: new THREE.Vector2() },
    u_perspective: { type: 'f', value: 0. },
    u_colours: { type: "v3v", value: colours }
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