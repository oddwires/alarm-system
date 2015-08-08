<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT    
?>

<!-- Kept separate to allow AJAX calls to refresh the list 'on the fly' -->

    <div class="toolbar">
         <h1>Users</h1>
         <a class="back2div slideright" href="#menusheet">Back</a>
         <a class="button slide" href="#" onclick="UserConf(<?php echo $USRNum; ?>)">Add...</a>
    </div>
    <ul class="edgetoedge scroll">
         <?php for($i=0; $i<$USRNum; $i++) {  ?>
            <li class="arrow"><a href="#" onclick="UserConf(<?php echo $i; ?>)" id="UserName<?php echo $i; ?>"><?php echo $user[$i][1]; ?></a>
            <!-- Make all user data available on the page just in case we want to drill down and start editing -->
            <input type="hidden" id="UserEmail<?php echo $i; ?>" value="<?php echo $user[$i][2]; ?>"></li>
         <?php } ?>
    </ul>