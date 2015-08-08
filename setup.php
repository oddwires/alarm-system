<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT    
?>

<div id="setup" class="selectable">
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

    <div id="setup_app" class="selectable">
          <div class="toolbar"><h1>App setup</h1>
                <a class="back2div slideright" onclick="SetupAppSend()" href="#setup">Back</a></div>
                <p>&nbsp</p>
                <p><ul class="plastic">
                       <li><input type="text" placeholder="Installation name" id="location" value="TBD"/></li>
                   </ul>
                <div class="info2">
                     Choose a name for this installation.<br>
                     (Note: for maximum security, do not use your full address or postcode)
                </div></p>
                <p>&nbsp</p>
                <ul class="plastic">
                    <li class="arrow"><a href="#setup_app_duration" id="Duration">Duration
                        <small class="small" id="durtxt">TBD</small></a>
                    </li>
                </ul>
                <div class="info2">
                     Set the alarm duration.<br>
                     This is the period that the alarm will sound before timing out.
                </div>
                <p>&nbsp</p>
          </div>

    <div id="setup_app_duration" class="selectable">
            <div class="toolbar"><h1>Alarm Duration</h1>
               <a href="#settings" class="back">Back</a></div>
            <p>&nbsp</p>
             <ul class="rounded">
               <li><a href="#setup_app" onclick="SetupDuration('15 mins'); "<?php echo ($duration =='15 mins') ? 'class="active"' : ''; ?>>15 minutes</a></li>
               <li><a href="#setup_app" onclick="SetupDuration('9 mins'); "<?php echo ($duration =='9 mins') ? 'class="active"' : ''; ?>>9 minutes</a></li>
               <li><a href="#setup_app" onclick="SetupDuration('5 secs'); "<?php echo ($duration =='5 secs') ? 'class="active"' : ''; ?>>5 seconds (test mode)</a></li>
             </ul>
    </div>

<div id="setup_email" class="selectable">
    <div class="toolbar">
           <h1>email</h1>
           <a class="back slideright" onclick="SetupEmailSend()" href="#setup">Back</a>
    </div>
    <p>&nbsp</p>
    <p><div class="info2">
           Use these settings to connect to your email server.<br>
    </div></p>
    <p><ul class="rounded">
           <li><input type="text" placeholder="email server name" id="SMTP_server" value="TBD"></li>
       </ul></p>
    <p>&nbsp</p>
    <p><ul class="rounded">
           <li><input type="text" placeholder="email server port" id="SMTP_port" value="TBD"></li>
    </ul></p>
    <p>&nbsp</p>
    <p><ul class="rounded">
           <li><input type="text" placeholder="email account" id="email_account" value="TBD"></li>
       </ul></p>
    <p>&nbsp</p>
    <p><ul class="rounded">
           <li><input type="password" placeholder="email account password" id="email_pwd" value="TBD"></li>
       </ul></p>
</div>