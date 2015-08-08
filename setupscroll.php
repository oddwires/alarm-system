<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT    
?>

<!-- Scroller element of the Setup screen                                    -->
<!-- kept separate to ensure AJAX updates load without breaking the scroller -->

     <div class="toolbar"><h1>Setup</h1>
         <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
     </div>
     <p>&nbsp</p>
     <div id="setupscroll" class="scroll">
     <!-- need to put a copy of setupscroll.php in here to handle the screen initialisation -->
        <ul class="plastic" style="margin-top: 0">
           <li class="arrow"><a href="#setup_app" onclick="SetupAppInit()" class="sliderleft">Application</a></li>
           <li class="arrow"><a href="#setup_email" onclick="SetupEmailInit()">Email</a></li>
           <li class="arrow"><a href="#setup1" class="action">Defaults</a></li>
           <li class="arrow"><a href="#themes" onclick="InitCurrentTheme()">Themes <small class="counter">6</small></a></li>
        </ul>
     <!-- Make all setupzone configuration data available on the page just in case we want to drill down and start
     editing the configurations                                                                                   -->
           <input type="hidden" id="SetupLoc" value="<?php echo $location; ?>">
           <input type="hidden" id="SetupDur" value="<?php echo $duration; ?>">
           <input type="hidden" id="SetupEserv" value="<?php echo $EMAIL_server; ?>">
           <input type="hidden" id="SetupEport" value="<?php echo $EMAIL_port; ?>">
           <input type="hidden" id="SetupEsend" value="<?php echo $EMAIL_sender; ?>">
           <input type="hidden" id="SetupEpass" value="<?php echo $EMAIL_password; ?>">
           <p>&nbsp</p>
           <div class="info">
            System information:<br><br>
            <?php echo $hardware.' - '.$memory; ?> memory<br>
            Disk total: <?php echo $disktotal; ?> - used: <?php echo $diskused; ?> - Free space: <?php echo $diskperc; ?><br>
            Local IP: <?php echo $localIP; ?> - Router IP: <?php echo $routerIP; ?>
        </div>
     </div>
     </div>