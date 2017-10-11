<?php
    include("readvars.php");                         // common code to read variables from file STATUS.TXT
?>
<br><br>
<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false" id="ftmch">
    <a href="#sidePanel" data-icon="bars">Menu</a>
    <h1>Settings</h1>
</div>

<div data-role="collapsible-set" id="settingsList" data-iconpos="right" data-inset="true" style="background-color:#D4CBAC; border-style: none; margin-left: 5px; margin-right: 5px;">
    <div data-role="collapsible" class="animateMe">
        <h3>Application</h3>
        <table border="0" align="center">
            <tr><td style="width:30%">Installation name</td><td><input type="text" id="SetupLoc" value="<?php echo $location; ?>"></td></tr>
            <tr><td style="width:30%">Alarm duration</td><td><input type="text" id="SetupDur" value="<?php echo $duration; ?>"></td></tr>
        </table>
    </div>
    <div data-role="collapsible" class="animateMe">
        <h3>Email</h3>
        <table border="0" align="center">
            <tr><td style="width:30%">server name</td><td><input type="text" id="SMTP_server" value="<?php echo $EMAIL_server; ?>"></td></tr>
            <tr><td style="width:30%">server port</td><td><input type="text" id="SMTP_port" value="<?php echo $EMAIL_port; ?>"></td></tr>
            <tr><td style="width:30%">account</td><td><input type="text" id="SMTP_account" value="<?php echo $EMAIL_account; ?>"></td></tr>
            <tr><td style="width:30%">password</td><td><input type="password" id="SMTP_passwd" value="********"></td></tr>
        </table>
    </div>
    <div>
        <a class="ui-btn ui-shadow" style="margin: 0; text-align: left; border-top-width: 0px;" href="#DefaultDialog" data-transition="slideup" data-rel="popup">Defaults...</a>
    </div>
<!--            <div data-role="collapsible" class="animateMe">
        <h3>Theme<span class="ui-li-count" style="right: 35px !important;">1</span></h3>
        <ul data-role="listview" data-inset="false">
            <li>Green</li>
        </ul>
    </div> -->
</div>

<br><br>
            
<div class="info" style="text-align:center">
    <h3>System information:</h3>
    <?php echo $hardware; ?> with <?php echo $memory; ?> memory<br>
    Disk total available: <?php echo $disktotal; ?><br>
    Disk current usage: <?php echo $diskused; ?> ( <?php echo $diskperc; ?> )<br>
    Local IP: <?php echo $localIP; ?><br>
    Router IP: <?php echo $routerIP; ?><br>
    Up time: <?php echo $uptime; ?><br>
    &nbsp<br>
</div>