<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <a href="#sidePanel" data-icon="bars">Menu</a>
    <h1>HomeKit</h1>
</div>

<p data-role="screenSpacer"><br></p><br>          <!-- stop first item from getting stuck behind the header -->
<div data-role="collapsible-set" data-iconpos="right" data-inset="true" style="background-color:#D4CBAC; border-style: none; margin-left: 5px; margin-right: 5px;">
    <div data-role="collapsible" class="animateMe">
        <h3>Export</h3>
        <div class="info" style="text-align:center">
            Use this function to create KomeKit accessories for devices you have configured in this web app.<br><br>
            Accessories will be created for...<br>
            * Door sensors<br>
            * Switches<br>
            * Radiators<br><br>
            When the export completes, follow the HomeKit reset process to import the accessories into the HomeKit database.<br>
            <a href="#" data-role="button" onclick="HomeKit('export')" class="ui-btn ui-btn-inline ui-shadow ui-corner-all" role="button">Export</a>
        </div>
    </div>
    <div data-role="collapsible" class="animateMe">
        <h3>Pairing</h3>
        <div class="info" style="text-align:center">
            Use this function to pair your iPhone<br>
            with your HomeKit accessories.<br><br>
            When the pairing completes, follow the HomeKit reset process to import the accessories into the HomeKit database.<br>
            <a href="#" data-role="button" onclick="HomeKit('pair')" class="ui-btn ui-btn-inline ui-shadow ui-corner-all" role="button">Pairing</a>
        </div>
</div>
<br><br>

<div class="info" style="text-align:center">
    <h3>HomeKit reset process</h3>
    <ol style="text-align: left;">
        <li>On your iPhone, open the Home app</li>
        <li>Tap the Home tab</li>
        <li>Tap the arrow in the top left of the screen.</li>
        <li>Scroll to the bottom and tap remove home.</li>
        <li>Tap delete.</li>
    </ol>
    The accessory code is 031 45 154<br><br>
</div>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>