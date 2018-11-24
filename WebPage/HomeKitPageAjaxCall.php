<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;">Homekit</h1>
</div>
<p data-role="screenSpacer" style="height: 100px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                     also acts as a marker for the scroll to top button.   -->

<div data-role="collapsible-set" data-iconpos="right" data-inset="true" style="border-style: none; margin-left: 5px; margin-right: 5px;">
    <div data-role="collapsible" class="animateMe">
        <h3>Homebridge restart</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <div style="float: right; border-top-width: 10px;">
    <a class="RoundButton green not-active" id="SetButton" style="position: relative; float:right; margin: 10px;"
           data-transition="slideup" data-rel="popup" onclick="HomeBridge('reset')" > <span class="RoundButtonText3">Restart</span></a></div>
            Use this button to restart the Homebridge service. This is needed if your Home application cannot contact your accessories, or if Siri reports that she/he/it is unable to contact them.
        </div>
    </div>
    <div data-role="collapsible" class="animateMe info" id="custom-collapsible">
        <h3>Export accesories</h3>
        <div class="info" style="text-align: justify; text-shadow: none; padding: 10px;">
        <div style="float: right; border-top-width: 10px;">
    <a class="RoundButton green not-active" id="SetButton" style="position: relative; float:right; margin: 10px;"
           data-transition="slideup" data-rel="popup" onclick="HomeBridge('export')" > <span class="RoundButtonText3">Export</span></a></div>
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
