<?php if ((isset($_COOKIE['badcount']) && ($_COOKIE['badcount'] < 3) )) { ?>
	
	<div id="failed" class="selectable">
      <div class="toolbar"><h1>Failed logon</h1>
		<a href="#" class="back">Back</a>
      </div>
	  <br><br>
	  <div class="info">
         Username or Password incorrect.<br>
         Your IP address is <?php echo $_SERVER['REMOTE_ADDR'] ?><br>
	  </div>
	  <br>
      <div class="info">
         Your attempt to access this system<br>
         and your IP address have been logged.
	  </div>
</div>
<?php 
 } else { ?>
 
 <div id="failed" class="selectable">
      <div class="toolbar"><h1>Banned</h1></div>
	  <br>
	  <div style="text-align:center">
	  <img src="Skull_and_crossbones.png">
	  </div>
	  <br><br>
      <div class="info">
         Your IP address<br>
		 <?php echo $_SERVER['REMOTE_ADDR'] ?><br>
		 has been banned.<br>
	  </div>
</div>
 
 <?php } ?>
 
