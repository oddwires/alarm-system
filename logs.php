<div id="logs" class="selectable">
      <div class="toolbar"><h1>Logs</h1>
       <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
      </div>

    <div class="scroll">
      <ul class="plastic" style="margin-top: 0">
          <?php $date[0] = date('d-m-Y');                                         // today
                $date[1] = date('d-m-Y', time() - (1*60*60*24) );                 // yesterday
                $date[2] = date('d-m-Y', time() - (2*60*60*24) );                 // 2 days ago
                $date[3] = date('d-m-Y', time() - (3*60*60*24) );                 // 3 days ago
                $date[4] = date('d-m-Y', time() - (4*60*60*24) );                 // 4 days ago
                $date[5] = date('d-m-Y', time() - (5*60*60*24) );                 // 5 days ago
                $date[6] = date('d-m-Y', time() - (6*60*60*24) );                 // 6 days ago
                $date[7] = date('d-m-Y', time() - (7*60*60*24) );                 // 1 week ago ago
          ?>
         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[0]; ?>')">
               Today<small class="counter"><?php echo $date[0]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[1]; ?>')">
               Yesterday<small class="counter"><?php echo $date[1]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[2]; ?>')">
               2 days ago<small class="counter"><?php echo $date[2]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[3]; ?>')">
               3 days ago<small class="counter"><?php echo $date[3]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[4]; ?>')">
               4 days ago<small class="counter"><?php echo $date[4]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[5]; ?>')">
               5 days ago<small class="counter"><?php echo $date[5]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[6]; ?>')">
               6 days ago<small class="counter"><?php echo $date[6]; ?></small></a></li>

         <li class="arrow"><a href="#" onclick="LogSelect('<?php echo $date[7]; ?>')">
               1 week ago<small class="counter"><?php echo $date[7]; ?></small></a></li>
      </ul>
   </div>
</div>

<div id="logview" class="selectable">
     <div class="toolbar"><h1 id="LogsHead">tbd</h1>
        <a class="back slideright" href="#logs">Back</a>
     </div>

     <div id="LogScroll" class="scroll">
        <p>&nbsp</p>
        <div class="info">
          Retrieving log file.
        </div>
     </div>
</div>
