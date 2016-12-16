<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT 
?>

<!-- Scroller element of the Automation screen                               -->
<!-- kept separate to ensure AJAX updates load without breaking the scroller -->

     <div class="toolbar"><h1>Automation</h1>
          <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
         <a class="button slide" onclick="RconJmp(<?php echo $RCnum ?>)" href="#">Add...</a>
      </div>
      <div id="AutoScroll" class="scroll">
      <!-- need to put a copy of autoscroll.php in here to handle the screen initialisation -->
         <table border="0" style="width:100%;">
         <?php for ($row=0; $row<$RCnum; $row++) { ?>
          <tr><td style="width:95%"><ul class="rounded" style="width:100%; margin-left:0px; margin-top:0px; margin-bottom:0px;">
          <li><a href="#" onclick="Rconedit(<?php echo $row; ?>)" id="hyplnk<?php echo $row; ?>"><?php echo $rcon[$row][1]; ?></a></li>
          <input type="hidden" id="RconAddr<?php echo $row; ?>" value="<?php echo $rcon[$row][2]; ?>">
          <input type="hidden" id="RconChan<?php echo $row; ?>" value="<?php echo $rcon[$row][3]; ?>">
          <input type="hidden" id="RconMode<?php echo $row; ?>" value="<?php echo $rcon[$row][4]; ?>">
          <input type="hidden" id="RconHK<?php echo $row; ?>" value="<?php echo $rcon[$row][6]; ?>">
          </ul></td>
          <td><ul class="rounded" style="margin-top:0px; margin-bottom:0px;"><li>
              <span class="toggle">
              <input type="checkbox" name="RC<?php echo $row; ?>" id="RC<?php echo $row; ?>" onclick="RconSwitch('<?php echo $row; ?>')"
                      <?php if(strpos($rcon[$row][5],"on")!== false) { echo 'checked'; } ?>>
              </span>          
          </li></td>
              </tr>
         <?php } ?>
      </table></div>