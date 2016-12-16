<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT     
?>

<div id="heating" class="selectable">
      <div class="toolbar"><h1>Radiators</h1>
        <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
        <a class="button slide" onclick="RDTREdit(<?php echo $RDTRnum ?>)" href="#">Add...</a>
      </div>
      <div id="HeatingScroll" class="scroll">
      <!-- need to put a copy of HeatingScroll.php in here to handle the screen initialisation -->
         <table border="0" style="width:100%;">
         <?php for ($row=0; $row<$RDTRnum; $row++) { ?>
          <tr><td style="width:95%"><ul class="rounded" style="width:100%; margin-left:0px; margin-top:0px; margin-bottom:0px;">
          <li><a href="#" onclick="RDTREdit(<?php echo $row; ?>)" id="hyplnk<?php echo $row; ?>"><?php echo $rdtr[$row][1]; ?></a></li>
<!--      <input type="text" id="RDTRName<?php echo $row; ?>" value="<?php echo $rdtr[$row][1]; ?>">
          <input type="text" id="RDTRAddr<?php echo $row; ?>" value="<?php echo $rdtr[$row][2]; ?>">
          <input type="text" id="RDTRStatus<?php echo $row; ?>" value="<?php echo $rdtr[$row][3]; ?>">
          <input type="text" id="RDTRHi<?php echo $row; ?>" value="<?php echo $rdtr[$row][4]; ?>">
          <input type="text" id="RDTRLo<?php echo $row; ?>" value="<?php echo $rdtr[$row][5]; ?>">   -->
          <input type="hidden" id="RDTRName<?php echo $row; ?>" value="<?php echo $rdtr[$row][1]; ?>">
          <input type="hidden" id="RDTRAddr<?php echo $row; ?>" value="<?php echo $rdtr[$row][2]; ?>">
          <input type="hidden" id="RDTRStatus<?php echo $row; ?>" value="<?php echo $rdtr[$row][3]; ?>">
          <input type="hidden" id="RDTRHi<?php echo $row; ?>" value="<?php echo $rdtr[$row][4]; ?>">
          <input type="hidden" id="RDTRLo<?php echo $row; ?>" value="<?php echo $rdtr[$row][5]; ?>"> 
          </ul></td>
          <td><ul class="rounded" style="margin-top:0px; margin-bottom:0px;"><li>
              <span class="toggle">
              <input type="checkbox" name="RDTR<?php echo $row; ?>" id="RDTR<?php echo $row; ?>" onclick="RdtrSwitch('<?php echo $row; ?>')"
                      <?php if(strpos($rdtr[$row][3],"on")!== false) { echo 'checked'; } ?>>
              </span>          
          </li></td>
              </tr>
         <?php } ?>
      </table></div>
</div>

<div id="RdtrCfg" class="selectable">
     <div class="toolbar"><h1 id="RdtrHead">tbd</h1>
          <a class="back2div slideright" onclick="RdtrSend()" href="#">Back</a>		  
          <a class="button slide" onclick="RdtrDel()" href="#">Delete</a>
      </div>
      <p>&nbsp</p>
    Name:
      <ul class="rounded">
          <li><input type="text" id ="RdtrNameCfg"  value=tbd /></li>
      </ul>
    Address:
      <ul class="rounded">
          <li><input type="text" id ="RdtrAddrCfg"  value=tbd /></li>
      </ul>
    Low temp:
      <ul class="rounded">
          <li><input type="text" id ="RdtrLoCfg"  value=tbd /></li>
      </ul>
    High temp:
      <ul class="rounded">
          <li><input type="text" id ="RdtrHiCfg"  value=tbd /></li>
      </ul>
</div>