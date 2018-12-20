<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;">Settings</h1>
</div>
<p data-role="screenSpacer" style="height: 14px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                     also acts as a marker for the scroll to top button.   -->

<ul data-role="listview" data-inset="true" style="padding: 0em; border-style: none;" data-filter="false">
    <li id="application-header" style="margin-bottom: 10px; font-size: 14px; height: 18px; padding-left: 20px;
                 padding-top: 7px; padding-bottom: 7px;"">Settings:
    </li>
</ul>

<div data-role="collapsible-set" data-iconpos="right" data-inset="true" style="border-style: none; margin-left: 5px; margin-right: 5px;">
    <div data-role="collapsible" class="animateMe">
        <h3>Application</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <table border="0" align="center">
            <tr><td style="width:30%">Installation name</td><td><input type="text" id="SetupLoc" value="<?php echo $location; ?>"></td></tr>
            <tr><td style="width:30%">Alarm duration</td><td><input type="text" id="SetupDur" value="<?php echo $duration; ?>"></td></tr>
        </table></div>
    </div>
    <div data-role="collapsible" class="animateMe info" id="custom-collapsible">
        <h3>Email</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <table border="0" align="center">
            <tr><td style="width:30%">server name</td><td><input type="text" id="SMTP_server" value="<?php echo $EMAIL_server; ?>"></td></tr>
            <tr><td style="width:30%">server port</td><td><input type="text" id="SMTP_port" value="<?php echo $EMAIL_port; ?>"></td></tr>
            <tr><td style="width:30%">account</td><td><input type="text" id="SMTP_account" value="<?php echo $EMAIL_account; ?>"></td></tr>
            <tr><td style="width:30%">password</td><td><input type="password" id="SMTP_passwd" value="********"></td></tr>
        </table></div>
    </div>
    <div data-role="collapsible" class="animateMe info" id="custom-collapsible">
        <h3>Info</h3>
        <div class="info" style="text-align: centre; text-shadow: none; padding: 10px;">
            <?php echo $hardware; ?> with <?php echo $memory; ?> memory<br>
            Disk total available: <?php echo $disktotal; ?><br>
            Disk current usage: <?php echo $diskused; ?> ( <?php echo $diskperc; ?> )<br>
            Local IP: <?php echo $localIP; ?><br>
            Router IP: <?php echo $routerIP; ?><br>
            Up time: <?php echo $uptime; ?>
        </div>
    </div>
    <div>
    <br>
        <a class="ui-btn floaty new button ui-shadow ui-btn-icon-right ui-icon-carat-r"
               style="margin: 0; text-align: left; border-top-width: 0px; text-shadow: none;"
               href="#DefaultDialog" data-transition="slideup" data-rel="popup" data-icon="carat-r">Defaults...</a>
    </div>
</div>

<ul data-role="listview" data-inset="true" style="padding: 0em; border-style: none;" data-filter="false">
    <li id="HomeKit-header" style="margin-bottom: 10px; font-size: 14px; height: 18px; padding-left: 20px;
                 padding-top: 7px; padding-bottom: 7px;"">HomeKit:
    </li>
</ul>

<div data-role="collapsible-set" data-iconpos="right" data-inset="true" style="border-style: none; margin-left: 5px; margin-right: 5px;">
    <div data-role="collapsible" class="animateMe" id="resetCollapsible">
        <h3>Homebridge restart</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <div style="float: right; border-top-width: 10px;">
    <a class="RoundButton green not-active" id="SetButton" style="position: relative; float:right; margin: 10px;"
           data-transition="slideup" data-rel="popup" onclick="HomeBridge('Restart')" > <span class="RoundButtonText3">Restart</span></a></div>
            Use this button to restart the Homebridge service. This is needed if your Home application cannot contact your accessories, or if Siri reports that she/he/it is unable to contact them.
        </div>
    </div>
    <div data-role="collapsible" class="animateMe info" id="exportCollapsible">
        <h3>Export accesories</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <div style="float: right; border-top-width: 10px;">
    <a class="RoundButton green not-active" id="SetButton" style="position: relative; float:right; margin: 10px;"
           data-transition="slideup" data-rel="popup" onclick="HomeBridge('Export')" > <span class="RoundButtonText3">Export</span></a></div>
            Use this button to create KomeKit accessories for all devices configured in this web app.
            When complete, follow the HomeKit reset process (below) to import the accessories into the HomeKit database.
        </div>
    </div>
    <div data-role="collapsible" class="animateMe">
        <h3>Homekit reset process</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <div style="float: right; border-top-width: 10px;">
        </span></a></div>
            <ol style="padding-left: 20px;">
                <li>On your iPhone, open the Home app</li>
                <li>Tap the Home tab</li>
                <li>Tap the arrow in the top left of the screen.</li>
                <li>Scroll to the bottom and tap remove home.</li>
                <li>Tap delete.</li>
            </ol>
            The accessory code is 031 45 154
        </div>
</div>
<!-- put some blank space at the end of the list.    -->
<p style="height:100vh"></p>

<!-- Select defaults Action sheet dialog -->
<div data-role="popup" id="DefaultDialog" style="border: 0; background: #80808075;" data-transition="slideup">
    <div data-role="content" style="padding: 8px;">
        <a href="#" class="ui-popup-button title" data-role="button" data-rel="back">Work day defaults.</a>
        <a href="#" class="ui-popup-button option" onclick="Defaults('load work defaults')"
                      data-role="button" data-rel="back">Load defaults</a>
        <a href="#" class="ui-popup-button option last" onclick="Defaults('save work defaults')"
                      data-role="button" data-rel="back">Save defaults</a>
<br>
        <a href="#" class="ui-popup-button title" data-role="button" data-rel="back">Holiday defaults.</a>
        <a href="#" class="ui-popup-button option" onclick="Defaults('load hols defaults')"
                      data-role="button" data-rel="back">Load defaults</a>
        <a href="#" class="ui-popup-button option last" onclick="Defaults('save hols defaults')"
                      data-role="button" data-rel="back">Save defaults</a>
<!--    <a href="#" class="ui-popup-button option last" onclick="Defaults('load factory defaults')"
                      data-role="button" data-rel="back">Load factory defaults</a> -->
        <a href="#" class="ui-popup-button cancel" data-role="button" data-rel="back">Cancel</a>
</div>
