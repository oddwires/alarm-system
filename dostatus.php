<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT     
?>

<div id="status" class="selectable">
      <div class="toolbar">
       <h1>Status</h1>
       <a class="button flipleft" href="#Home">Logon</a></a>
      </div><br>
	  
      <!-- Using most of the info class, but overwriting a few key variables...    -->
	  <fieldset class=info style="border-radius: 25px; -webkit-margin-start: 8px; -webkit-margin-end: 8px; border: 3px solid; text-align: left; padding:0px; -webkit-padding-after:0px">
			 
          <table border="0" style="margin: 0px auto; width: 100%;">
          <tr><td style="width:40%" colspan="2"><h1>Alarm:</h1></td>
		  <td><ul class="rounded">
			  <?php echo ($status[0] !='Set') ? '<li style="background:#f00;">' : '<li>' ?>
                   <?php echo $status[1]; ?></li></td>
		  </tr></table>
-      </fieldset>	  
	  
      <div class="scroll">
	  <ul class="rounded">
      <?php for ($row=0; $row<$ZnNum; $row++) { ?>
               <li><?php echo $zcon[$row][2]; ?><small class="counter"
                   <?php echo ($zcon[$row][8]) ? '>Closed' :' style="background: #f00;">Open'; ?></small></li>
      <?php } ?></ul>
    </div>
</div>