<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT     
?>

<div id="status" class="selectable">
      <div class="toolbar">
       <h1>Status</h1>
       <a class="button flipleft" href="#Home">Logon</a></a>
      </div>
      <table border="0" style="width:100%">
      <tr><td style="width:50%" colspan="2"><h2>Alarm mode:</h2></td>
      <td style="text-align:center">
      <ul class="rounded" style="margin-left:0px; margin-bottom: 0px; margin-top: 5px">
            <?php echo ($status[0] !='Set') ? '<li style="background:#f00;">' : '<li>' ?>
                   <?php echo $status[1]; ?></li></td><td style="width:5%">&nbsp</td></tr>
      </table>
      <div class="scroll">
      <ul class="edgetoedge scroll" style="margin-top: 0">
      <?php for ($row=0; $row<$ZnNum; $row++) { ?>
               <li><?php echo $zcon[$row][2]; ?><small class="counter"
                   <?php echo ($zcon[$row][8]) ? '>Closed' :' style="background: #f00;">Open'; ?></small></li>
      <?php } ?></ul>
    </div>
</div>