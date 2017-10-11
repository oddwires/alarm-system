<!DOCTYPE html>
<html>
<!--<![-->                                                         <!-- Disable data compression over O2           -->
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="apple-mobile-web-app-status-bar-style" content="black" >
        <meta name="apple-mobile-web-app-capable" content="yes">

        <link rel="stylesheet" href="themes/khaki.css" />
        <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
        <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile.structure-1.4.5.css" />
        <script src="https://code.jquery.com/jquery-1.11.1.js"></script>

<!--    Minified versions of the above                                                                              -->
<!--    <link rel="stylesheet" href="themes/khaki.min.css" />
        <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>                     -->

        <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.js"></script>
        <script src="alarm.js" type="text/javascript" charset="utf-8"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div id="main">
    <div data-role="page" id="pageone" >
    <br><br>
        <form class="form-1" id="loginForm" action="/login.php" method="POST" autocomplete="on" data-transition="slide">
            <p class="field" style="padding-bottom: 10px;">
                <input type="text" id="username" name="username" placeholder="Username" data-clear-btn="true">
                <i class="icon-user icon-large"></i>
            </p>
                <p class="field">
                <input type="password" id="pword" name="password" placeholder="Password" data-clear-btn="true">
                <i class="icon-lock icon-large"></i>
            </p>        
            <p class="submit">
                <a href="#" id="submit" value="Submit Button" type="submit"><i class="newi fa fa-arrow-circle-right"></i></a>
            </p>
            <input type="checkbox" id="rememberme" name="rememberme" style="margin: 0px; position: static; float: left">
            <div style="margin-left: 25px; font-size: 12px; padding-top: 5px;">Remember me</div>
        </form>
    </div>

    <div data-role="page" id="pageTwo"></div>      <!-- Place holder for second page.      -->
    <div data-role="page" id="security"></div>     <!-- Place holder for Security page.    -->
    <div data-role="page" id="radiator"></div>     <!-- Place holder for radiator page.    -->
    <div data-role="page" id="schedule"></div>     <!-- Place holder for schedule page.    -->
    <div data-role="page" id="access"></div>       <!-- Place holder for Access page.      -->
    <div data-role="page" id="settings"></div>     <!-- Place holder for settings page.    -->
    <div data-role="page" id="logSelect"></div>    <!-- Place holder for first logs page.  -->
    <div data-role="page" id="logView"></div>      <!-- Place holder for second logs page. -->
    <div data-role="page" id="homekit"></div>      <!-- Place holder for HomeKit page.     -->

    <div data-role="page" id="about">
         <div data-role="header" data-position="fixed" data-fullscreen="true">
            <a href="#sidePanel" data-icon="bars">Menu</a>
            <h1>About</h1>
            <a href="#info" data-transition="flip" class="ui-btn ui-corner-all ui-icon-info ui-btn-icon-left">Info</a>
         </div>
         <br><br>
          <div style="text-align:center">
             <h2>Integrated Home Alarm<br>and Automation System</h2>
             <p><img alt ="logo" src="about.png"><br><br>
               <em>Monitor your home, mess with your lights.<br>
                      Ring your alarm bells & wake your neighbours.<br>
                      All from hundreds of miles away.</em><br><br>
                <a target="_blank" href="http://www.oddwires.co.uk">© 2013-17 ODDWIRES.CO.UK</a>
              </p>
              <p>&nbsp </p>
              <p>&nbsp </p>
           </div>
         </div>                <!-- About screen                            -->

    <div data-role="page" id="info">
         <div data-role="header" data-position="fixed" data-fullscreen="true">
            <a href="#sidePanel" data-icon="bars">Menu</a>
            <h1>Info</h1>
            <a href="#about" data-transition="flip" class="ui-btn ui-corner-all ui-icon-info ui-btn-icon-left">About</a>
        </div>
        <p data-role="screenSpacer">&nbsp</p>
        <p style="text-align:center"><a target="_blank" href="https://jquerymobile.com">jQuery Mobile</a><br><br>
        <em>A Touch-Optimized Web Framework<br>
            jQuery Mobile is a HTML5-based user<br>
            interface system designed to make<br>
            responsive web sites and apps that<br>
            are accessible on all smartphone,<br>
            tablet and desktop devices.</em></p>
                   
        <p style="text-align:center"><a target="_blank" href="http://heirloom.sourceforge.net/mailx.html">Heirloom mailx</a><br><br>
        <em>(formerly known as "nail")<br>
            Provides the functionality of the POSIX<br>
            mailx command with additional support<br>
            for MIME messages, IMAP, POP3, SMTP,<br>
            S/MIME, message threading/sorting,<br>
            scoring, and filtering.</em></p>

        <p style="text-align:center"><a target="_blank" href="https://github.com/samba-team/samba">Samba - Andrew Tridgell</a><br><br>
        <em>Samba is the standard Windows<br>
            interoperability suite of programs<br>
            for Linux and Unix. Providing<br>
            An ftp-like SMB client allowing <br>
            access to Linux resources (disks and
            printers) from the Windows<br>
            operating systems</em></p>
                
        <p style="text-align:center"><a target="_blank" href="https://www.schneier.com/blowfish.html">Blowfish - Bruce Schneier</a><br><br>
        <em>Blowfish is a symmetric block cipher<br>
            that can be used as a drop-in<br>
            replacement for DES or IDEA.<br>
            It takes a variable-length key,<br>
            from 32 bits to 448 bits, making<br>
            it ideal for both domestic and<br>
            exportable use.</em><p>

        <p style="text-align:center"><a target="_blank" href="https://github.com/fail2ban/fail2ban">Fail2Ban</a><br><br>
        <em>Fail2ban scans log files and bans<br>
            IPs that show the malicious signs<br>
            such as too many password failures,<br>
            seeking for exploits, etc.<br>
            Fail2Ban modifies firewall<br>
            rules to reject malicious IP's<br>
            for a specified amount of time.</em></p>

        <p style="text-align:center"><a target="_blank" href="https://github.com/KhaosT/HAP-NodeJS">HAP-NodeJS - Khaos Tian</a><br><br>
        <em>A Node.js implementation of HomeKit<br>
            Accessory Server. This project<br>
            allows creation of HomeKit<br>
            Accessories on Raspberry Pi,<br>
            Intel Edison or any other platform<br>
            that can run Node.js.</em></p>

        <p style="text-align:center"><a target="_blank" href="http://fontawesome.io/">Font Awesome</a><br><br>
        <em>Font Awesome gives you scalable<br>
            vector icons that can instantly be<br>
            customized — size, color, drop shadow<br>
            and anything that can be done<br>
            with the power of CSS.</em></p>

        <p style="text-align:center"><a target="_blank" href="http://www.canstockphoto.co.uk/">Alarm graphics</a><br><br>
        <em>Alarm graphic used under license from canstockphoto.</em>

        </div><br><br>
    </div>              <!-- Credits screen                          -->
</div>
</main>

<!-- Define menu bars and action sheets... -->
<div data-role="panel" id="sidePanel" class="sidePanel" data-display="overlay">
    <ul data-role="listview">
        <li style="color: #818181; font-size: 20px; font-weight: bold; border-style: none;"><?php echo $location; ?></li>
        <li data-icon="lock"><a href="#security" onclick="AjaxGet('SecurityPageAjaxCall.php','security')" data-rel="close">Security</a></li>
        <li data-icon="false"><a href="#pageTwo" onclick="AjaxGet('PowerPageAjaxCall.php','PageTwo')" data-rel="close">Power<i class="fa fa-plug"></i></a></li>
        <li data-icon="false"><a href="#radiator" onclick="AjaxGet('RadiatorPageAjaxCall.php','radiator')" data-rel="close">Heating<i class="fa fa-thermometer-full"></i></a></li>
        <li data-icon="clock"><a href="#schedule" onclick="AjaxGet('SchedulePageAjaxCall.php','schedule')" data-rel="close">Schedule</a></li>
        <li data-icon="user"><a href="#access" onclick="AjaxGet('AccessPageAjaxCall.php','access')" data-rel="close">Access</a></li>
        <li data-icon="gear"><a href="#settings" onclick="AjaxGet('SettingsPageAjaxCall.php','settings')" data-rel="close">Settings</a></li>
        <li data-icon="false"><a href="#logSelect" onclick="AjaxGet('LogsPageAjaxCall.php','logSelect')" data-rel="close">Logs<i class="fa fa-tree"></i></a></li>
        <li data-icon="false"><a href="#homekit" onclick="AjaxGet('HomeKitPageAjaxCall.php','homekit')" data-rel="close">Homekit<i class="fa fa-microphone"></i></a></li>
        <li data-icon="info"><a href="#about" data-rel="close">About</a></li>
    </ul>
</div>

<!-- Floating 'back to top' button for listViews                -->
<div class="scroll-top-wrapper">
    <span class="scroll-top-inner">
        <i class="fa fa-2x fa-arrow-circle-up"></i>
    </span>
</div>

<!-- Multi-purpose popup dialog, styled as an action sheet, content is customised by JQuery   -->        
<div data-role="popup" id="PopupDialog" data-overlay-theme="b" class="ui-corner-all">
    <div data-role="content" class="ui-content">
        <h3><span id="PopUpMsg" style="width:100%; color: #151515";>change me</span><br>&nbsp </h3>
            <a href="#home" id="PopUpBtn" class="whiteButton" data-role="button" onclick="editGo();">change me 2</a>
            <a href="#home" class="whiteButton" data-role="button" onclick="editCancel()" data-rel="back">Cancel</a>
    </div>
</div>

<div data-role="popup" id="DefaultDialog" data-overlay-theme="b" class="ui-corner-all">
    <div data-role="content" class="ui-content">
        <h3><span style="width:100%; color: #151515";>Default settings</span><br>&nbsp</h3>
            <a href="#" class="whiteButton" data-role="button" onclick="Defaults('load user defaults')" data-rel="back">Load user defaults</a>
            <a href="#" class="whiteButton" data-role="button" onclick="Defaults('save user defaults')" data-rel="back">Save user defaults</a>
            <a href="#" class="whiteButton" data-role="button" onclick="Defaults('load factory defaults')" data-rel="back">Load factory defaults</a>
            <a href="#" class="whiteButton" data-role="button" onclick="editCancel()" data-rel="back">Cancel</a>
    </div>
</div>
<!-- End of menu bars and action sheets                         -->

<!-- Hidden variable used to transfer data back to server       -->
    <input type="hidden" id="retval" value="tbd">
</body>
</html> 
