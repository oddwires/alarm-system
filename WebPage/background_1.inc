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
  '/themes/images/noise.png',
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
   // three.js and JQuery don't play nice when changing the iPhone orientation.
   // Introducing a delay allows JQuery mobile to finish resizing the screen before
   // three.js does the same.
   setTimeout(onWindowResize2, 50);
}

function onWindowResize2( event ) {
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
      
      float voronoi(vec2 uv) {
        float dist = 4.;
        
        float   res = 0.;
        
        vec2 grid_id = floor(uv);
        vec2 grid_uv = fract(uv);
        
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
            
            // float len = length(point_pos); // the length gives us a more linear (conic) length
            float len = dot(point_pos, point_pos); // The float gives us a more rounded distance
            // res += exp( -10.0*len );
            
            if(len < dist) {
              dist = len;
            }
          }
        }
        // return -(1.0/10.0)*log( res );
        
        return dist;
      }
      
      float pattern(vec2 uv, float time, inout vec2 q, inout vec2 r) {

        q = vec2( voronoi( uv + vec2(0.0,0.0) ),
                  voronoi( uv + vec2(5.2,1.3) ) );

        r = vec2( voronoi( uv + 2.0*q + vec2(1.7 - time / 2.,9.2) ),
                  voronoi( uv + 2.0*q + vec2(8.3 - time / 2.,2.8) ) );

        return voronoi( uv + 1.5*r );
      }
      
      vec4 colour(float pattern, vec2 distortion1, vec2 distortion2) {
        
        // All I'm doing here is mixing up the colours using the distortion vectors and the derrived pattern
        vec3 col = vec3(pattern + distortion1.x, pattern + distortion2.y / 2. + distortion1.x, pattern + distortion1.x * 2.);
        
        // Try muxing the colours here. Uncomment the following lines, 1-by-1 for some examples
        // col = vec3(col.b, col.r, col.g);
        // col = vec3(col.b * col.r) / col;
        // col *= vec3(dot(distortion1, distortion1) * .5);
        // col = col * (1.5 + sin(gl_FragCoord.x / u_resolution.x * 10.) * .5 + cos(gl_FragCoord.y / u_resolution.y * 10.) * .5);
        // col = 1. - col;
        // col *= sin(col * 2.);
        
        // Ramping up the contrast a bit
        col = col;
        
        return vec4(col, 1.);
      }

      void main() {
        vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy) / min(u_resolution.y, u_resolution.x);
        
        uv *= 5.;
        
        vec2 q = vec2(0.);
        vec2 r = vec2(0.);
        float pattern = pattern(uv, u_time, q, r);
        
        vec4 col = colour(pattern, q, r);

        gl_FragColor = col;
      }
    </script>
