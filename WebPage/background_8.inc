<!-- Matrix effect.

    This code has been taken from Codepen.io.
    Thanks to Laerin for posting it.
    Source code is here...

    https://codepen.io/xgundam05/pen/HlwcC
-->

<canvas id="canvas" width="400" height="300" style="position: fixed"></canvas>

<script>
(function() {
    var lastTime = 0;
    var vendors = ['webkit', 'moz'];
    for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
        window.cancelAnimationFrame =
          window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
    }

    if (!window.requestAnimationFrame)
        window.requestAnimationFrame = function(callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - (currTime - lastTime));
            var id = window.setTimeout(function() { callback(currTime + timeToCall); },
              timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };

    if (!window.cancelAnimationFrame)
        window.cancelAnimationFrame = function(id) {
            clearTimeout(id);
        };
}());

function Matrix(canvas){
    this.intervalId = 0;
    this.canvas = canvas;
    this.canvas.width = window.innerWidth;
    this.canvas.height = window.innerHeight;
    this.ctx = canvas.getContext('2d');
    this.width = canvas.width;
    this.height = canvas.height;
    this.yPositions = [];
    this.ySpeeds = [];
    this.yTimes = [];
    this.lastChars = [];
    
    for(var i = 0; i < this.width / 10; i++){
        this.yPositions[i] = Math.random() * (this.height / 10);
        this.ySpeeds[i] = (Math.random() + 0.3) * 0.5;
        this.yTimes[i] = 0;
        this.lastChars[i] = ' ';
    }
    
    this.ctx.font = '10pt Consolas';
}

Matrix.prototype.Clear = function(){
    var fill = this.ctx.fillStyle;
    this.ctx.fillStyle = '#000000';
    this.ctx.fillRect(0, 0, this.width, this.height);
    this.ctx.fillStyle = fill;
    
    for(var i = 0; i < 20; i++){
        this.Draw();
    }
};

Matrix.prototype.Draw = function(){
    requestAnimationFrame(this.Draw.bind(this));
    
    this.ctx.fillStyle = '#00FF00';
    for(var i = 0; i < this.lastChars.length; i++){
        this.ctx.fillText(this.lastChars[i], i * 10 + 1, this.yPositions[i] * 10);
    }
    
    this.ctx.fillStyle = 'rgba(0, 0, 0, 0.04)';
    this.ctx.fillRect(0, 0, matrix.width, matrix.height);
    
    this.ctx.fillStyle = '#FFFFFF';
    for(var x = 0; x < this.yPositions.length; x++){
        if(this.yTimes[x] > 1){
            var charCode = Math.random() > 0.9 ? Math.random() * 15 + 12688 :
                                                  Math.random() * 93 + 33;
            var char = String.fromCharCode(charCode);
            this.lastChars[x] = char;
            this.ctx.fillText(char, x * 10 + 1, this.yPositions[x] * 10 + 10);
            this.yPositions[x]++;
            if(this.yPositions[x] * 10 > this.height)
                this.yPositions[x] = 0;
            this.yTimes[x] = 0;
        }
        
        this.yTimes[x] += this.ySpeeds[x];
    };
};

Matrix.prototype.Start = function(){
    this.Draw();
};

var matrix = new Matrix(document.getElementById('canvas'));
matrix.Start();
</script>