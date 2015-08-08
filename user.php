<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT    
?>

<div id="userlist" class="selectable">
    <div class="toolbar">
         <h1>Users</h1>
         <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
         <a class="button slide" href="#" onclick="UserConf(<?php echo $USRNum; ?>)">Add...</a>
    </div>
    <ul class="edgetoedge scroll">
         <?php for($i=0; $i<$USRNum; $i++) {  ?>
            <li class="arrow"><a href="#" onclick="UserConf(<?php echo $i; ?>)" id="UserName<?php echo $i; ?>"><?php echo $user[$i][1]; ?></a>
            <!-- Make all user data available on the page just in case we want to drill down and start editing -->
            <input type="hidden" id="UserEmail<?php echo $i; ?>" value="<?php echo $user[$i][2]; ?>"></li>
         <?php } ?>
    </ul>
</div>

<div id="useredit" class="selectable">
     <div class="toolbar"><h1 id="UserConfHead">tbd</h1>
          <a class="back2div slideright" href="#userlist" onclick="UserSend()">Back</a>
          <a class="button slide" href="#" onclick="UserDel()">Delete</a>
     </div>
     <p>&nbsp</p>
     <ul class="plastic">
          <li><input type="text" name="name" placeholder="Username:" id="UserConfName" value="tbd" /></li>
          </ul>
          <ul class="plastic">
               <li><input type="text" name="email" placeholder="Email:" id="UserConfEmail" value="tbd" /></li>
          </ul>
          <ul class="plastic">
              <li class="arrow"><a href="#password">Set password</a></li>
          </ul>
</div>