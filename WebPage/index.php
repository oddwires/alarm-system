<!DOCTYPE html>
<html>
<head>
    <meta name="apple-mobile-web-app-status-bar-style" content="black" >
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

    <title>Automation System</title>

    <link rel="apple-touch-icon" href="/themes/images/apple-touch-icon.png">
    <link rel="stylesheet" href="/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.css">
    <link rel="stylesheet" href="/themes/css/system.css" />
    <link rel="stylesheet" href="/fontawesome/css/fontawesome-all.css">

    <script src="/js/three.js/three.js"></script>
    <script src="/js/jquery-1.11.3/jquery-1.11.3.js"></script>
    <script src="/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.js"></script>
    <script src="/js/flot/jquery.flot.js"></script>
    <script src="/js/flot/jquery.flot.time.js"></script>
    <script src="/js/flot/jquery.flot.resize.js"></script>
    <script src="/js/flot/jquery.flot.axislabels.js"></script>
    <script src="/js/index.js"></script>
</head>

<body>
<!-- Enable one of the following dynamic backgrounds...                                                          -->
    <?php //include("background_1.inc"); ?>                     <!-- Purple snot                                 -->
    <?php //include("background_2.inc"); ?>                     <!-- Slime                                       -->
    <?php //include("background_3.inc"); ?>                     <!-- Chrome                                      -->
    <?php //include("background_4.inc"); ?>                     <!-- Diamonds                                    -->
    <?php //include("background_5.inc"); ?>                     <!-- Bad blood                                   -->
    <?php //include("background_6.inc"); ?>                     <!-- Emerald City                                -->
    <?php //include("background_7.inc"); ?>                     <!-- Nakatomi Plaza ( Welcome to the web-app )   -->
    <?php include("background_8.inc"); ?>                     <!-- Matrix (Code rain)                          -->

<div main>
    <?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

    <div data-role="page" id="login">
        <div data-role="content">
            <form id="check-user" class="floaty logonBox" data-ajax="false" method="POST">
                <fieldset style = "border: 0px; height: 140px;">
                    <div data-role="fieldcontain" style="0px;width: 180px;" >
                        <input type="text" value="" name="uname" id="uname" placeholder="Username" data-clear-btn="true"/>
                        <i class="icon-user icon-large"></i>
                    </div>
                    <div data-role="fieldcontain" style="padding: 0px; width: 180px;" >
                         <input type="password" value="" name="pword" id="pword" placeholder="Password" data-clear-btn="true"/>
                        <i class="icon-user icon-large"></i>
                    </div>
                    <input type="checkbox" id="rememberme" name="rememberme" style="margin: 0px; position: static; float: left" />
                    <div style="margin-left: 25px; font-size: 12px; text-shadow: none; padding-top: 3px; padding-bottom: 10px;">Remember me</div>
                    <button type="submit" class="submit RoundButton green" style = "top: 25px; right: -32px; z-index: 3;">
                       <i class="RoundButtonText1 fa fa-arrow-right" style="width: auto;"></i>
                    </button>
                    <div class = "SemiCircle Filler" style="width: 10px; height: 78px; left: 223px; top: 26px;"></div>
                    <div class = "SemiCircle Right" style="left: 230px; top: 26px; z-index: 1;"></div>
                </fieldset>
            </form>
        </div>
    </div>

    <div data-role="page" id="loginFail">
        <div data-role="content">
                <form id="check-fail" class="floaty logonFail" data-ajax="false" method="POST">
                <fieldset style="height: 205px;">
                <div class="info" id="logonMsg" style=" font-weight: bold; font-size: 12px;">
                    <br>REPLACE ME !<br>
                    <br>Your attempt to access<br>
                    this system and your IP<br>
                    address have been logged.<br>
                    <p><b><?php echo $_SERVER['REMOTE_ADDR'] ?></b></p>
                    <p>This system is monitored.<br>
                    Do not attempt to log in unless<br>
                    you are authorised to do so.<br><br></p>
                </div>
                <button type="submit" class="submit RoundButton green" style = "top: 25px; left: -32px; z-index: 3;">
                     <i class="RoundButtonText1 fa fa-arrow-left" style="width: auto;"></i>
                </button>
                <div class = "SemiCircle Filler" style="width: 10px; height: 80px; left: -1px; top: 25px;"></div>
                <div class = "SemiCircle Left" style="left: -40px; top: 25px; z-index: 1;"></div>
                </fieldset>
            </form>                             
        </div>
    </div>
    <div data-role="page" id="Banned">
        <div data-role="content">
                <br><br>
                <div style="text-align:center">
                     <img src="/themes/images/Skull_and_crossbones.png">
                     <br><br><br><br>
                     <div class="floaty banned" id="logonMsg">
                          <br>Your IP address is <?php echo $_SERVER['REMOTE_ADDR'] ?><br>
                          and it has been banned.<br><br>
                     </div>
                </div>
        </div>
    </div>
    <!-- Place holders for the various PHP page scripts                                            -->
    <div data-role="page" id="security"></div>
    <div data-role="page" id="power"></div>
    <div data-role="page" id="heating"></div>
    <div data-role="page" id="logSelect"></div>
    <div data-role="page" id="logView"></div>
    <div data-role="page" id="graph"></div>
    <div data-role="page" id="schedule"></div>
    <div data-role="page" id="access"></div>
    <div data-role="page" id="settings"></div>
    <div data-role="page" id="about"></div>
    <div data-role="page" id="info"></div>
</div>

<!-- Define menu bars and action sheets... -->
<div data-role="panel" style="width: 10em"; id="sidePanel" class="sidePanel" data-display="overlay">
    <ul data-role="listview">
        <li style="color: #818181; font-size: 20px; font-weight: bold; border-style: none;"><?php echo $location; ?></li>
        <li data-icon="lock"><a href="#security" onclick="AjaxGet('SecurityPageAjaxCall.php','security')" data-rel="close">Security</a></li>
        <li data-icon="false"><a href="#power" onclick="AjaxGet('PowerPageAjaxCall.php','power')" data-rel="close">Power<i class="fa fa-plug"></i></a></li>
        <li data-icon="false"><a href="#heating" onclick="AjaxGet('HeatPageAjaxCall.php','heating')" data-rel="close">Heat<i class="fa fa-thermometer-full"></i></a></li>
        <li data-icon="false"><a href="#logSelect" onclick="AjaxGet('LogsPageAjaxCall.php','logSelect')" data-rel="close">Logs<i class="fa fa-tree"></i></a></li>
        <li data-icon="false"><a href="#graph" onclick="AjaxGet('GraphPageAjaxCall.php','graph')" data-rel="close">Graphs<i class="fas fa-chart-area"></i></a></li>
        <li data-icon="clock"><a href="#schedule" onclick="AjaxGet('TaskPageAjaxCall.php','schedule')" data-rel="close">Tasks</a></li>
        <li data-icon="user"><a href="#access" onclick="AjaxGet('AccessPageAjaxCall.php','access')" data-rel="close">Access</a></li>
        <li data-icon="gear"><a href="#settings" onclick="AjaxGet('SettingsPageAjaxCall.php','settings')" data-rel="close">Settings</a></li>
        <li data-icon="info"><a href="#about" onclick="AjaxGet('AboutPageAjaxCall.php','about')" data-rel="close">About</a></li>
    </ul>
</div>

<!-- Floating 'back to top' button                      -->
<div class="scroll-top-wrapper">
    <a class="RoundButton green" style="line-height: 2.8; text-align: center" <span class="RoundButtonText2">Top</span></a>
</div>

<!-- Alarm modes Action sheet dialog -->
<div data-role="popup" id="AlarmPopup" style="border: 0; background: #80808075;" data-transition="slideup">
    <div data-role="content" style="padding: 8px;">
        <a href="#" class="ui-popup-button title" data-role="button" data-rel="back">Select the required alarm mode.</a>
        <a href="#" class="ui-popup-button option" onclick="ChangeAlarmMode('mode:Day mode','Day mode')"
                data-role="button" data-rel="back">Day mode</a>
        <a href="#" class="ui-popup-button option" onclick="ChangeAlarmMode('mode:Night mode','Night mode')"
                data-role="button" data-rel="back">Night mode</a>
        <a href="#" class="ui-popup-button option last" onclick="ChangeAlarmMode('mode:Standby','Standby')"
                data-role="button" data-rel="back">Standby</a>
        <a href="#" class="ui-popup-button cancel" data-role="button" data-rel="back">Cancel</a>
    </div>
</div>

<!-- Test functions Action sheet dialog -->
<div data-role="popup" id="TestPopup" style="border: 0; background: #80808075;" data-transition="slideup">
    <div data-role="content" style="padding: 8px;">
        <a href="#" class="ui-popup-button title"  data-role="button" data-rel="back">Select the required test.</a>
        <a href="#" class="ui-popup-button option" onclick="SendCommand('test bell')"
            data-role="button" data-rel="back">External siren</a>
        <a href="#" class="ui-popup-button option" onclick="SendCommand('test strobe')"
            data-role="button" data-rel="back">External strobe</a>
        <a href="#" class="ui-popup-button option last" onclick="SendCommand('test sounder')"
            data-role="button" data-rel="back">Internal sounder</a>
        <a href="#" class="ui-popup-button cancel" data-role="button" data-rel="back">Cancel</a>
    </div>
</div>

<!-- Multi-purpose popup dialog, styled as an action sheet, content is customised by JQuery   -->        
<div data-role="popup" id="MultiPopup" style="border: 0; background: #80808075;" data-transition="slideup">
    <div data-role="content" style="padding: 8px;">
        <a href="#" id="MultiMsg" class="ui-popup-button title"  data-role="button" data-rel="back">Select the required test.</a>
        <a href="#" id="MultiBtn" class="ui-popup-button option last" onclick="AjaxSend('readvars.php');"
                      data-role="button" data-rel="back">tbd</a>
        <a href="#" class="ui-popup-button cancel" data-role="button" data-rel="back">Cancel</a>
    </div>
</div>
<!-- End of menu bars and action sheets                         -->

<!-- Hidden variable used to transfer data back to server       -->
    <input type="hidden" id="retval" value="tbd">
</main>
</body>
</html>
