<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;">Security
                <img id="AlarmIndicator" alt ="Indicator" src="/themes/images/alarm.gif"
                       style = "position: absolute; width: 320px;left: 0; top: 0; visibility: 
                         <?php echo ($status[0]=="Set") ? 'hidden' : 'visible'; ?>">
                </h1>
                <a href="#SecurityEditor" data-transition="pop" class="SquareButton green AddBtn"
                                    data-rel="popup" data-position-to="window">
                  <span class="gloss"></span>
                    <span class="SquareButtonText" id="newZone">Add</span>
                </a>
</div>
<p data-role="screenSpacer" style="height: 30px;"></p>          <!-- stop first item from getting stuck behind the header -->

<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new alarm zone                              -->
    <p data-role="screenSpacer" style="height: 82px"></p>
        <p>
            <ul data-role="listview" data-inset="true" id="zoneList" style="padding: 0em; border-style: none;" data-filter="false">
                <input id="zoneCount" type="hidden" value="<?php echo $ZnNum; ?>"> 

                <li class="ui-li-divider ui-bar-inherit" style="margin-bottom: 10px; z-index: 2;"">
                      <?php echo $ZnNum; ?> zones:
                      <span class="ui-li-count count infoBox" >Triggered: <?php echo $ZnTrig; ?></span></li>
                <?php for ($row=0; $row<$ZnNum; $row++) { ?>
                    <a href="#"
                        <?php $tmp='class="floaty GreenStripe zone"';                                        // default
                              if (((strtolower($zcon[$row][3])=='on') && ( strtolower($status[1])=='day mode' )) ||
                                  ((strtolower($zcon[$row][4])=='on') && ( strtolower($status[1])=='night mode' )) ||
                                  ((strtolower($zcon[$row][1])=='tamper')))
                                    { $tmp='class="floaty AmberStripe zone"'; }                              // enabled
                              if (strtolower($zcon[$row][7])=='true') {$tmp='class="floaty RedStripe zone"';}  // triggered
                              echo $tmp;
                        ?>
                        data-role="button" data-icon="carat-r" data-iconpos="right" id="zone_<?php echo $row; ?>"><?php echo $zcon[$row][2]; ?>
                        <?php if (strtolower($zcon[$row][3])=='on') { ?> <span class="ui-li-count count-first">D</span> <?php } ?>
                        <?php if (strtolower($zcon[$row][4])=='on') { ?> <span class="ui-li-count count-second">N</span> <?php } ?>
                        <?php if (strtolower($zcon[$row][5])=='on') { ?> <span class="ui-li-count count-third">C</span> <?php } ?>
                    <span class="ui-li-count circuitstate">
                        <?php if (strtolower($zcon[$row][8])==0) { echo 'Open'; }
                              else                               { echo 'Closed'; }
                        ?>
                    </span>
                    </a>
                    <!-- Bury all the zone data so it can be accessed from the configuration screen                      -->
                     <input type="hidden" id="ZoneType<?php echo $row; ?>" value="<?php echo $zcon[$row][1]; ?>">
                     <input type="hidden" id="ZoneName<?php echo $row; ?>" value="<?php echo $zcon[$row][2]; ?>">
                     <input type="hidden" id="ZoneDM<?php echo $row; ?>" value="<?php echo $zcon[$row][3]; ?>">
                     <input type="hidden" id="ZoneNM<?php echo $row; ?>" value="<?php echo $zcon[$row][4]; ?>">
                     <input type="hidden" id="ZoneCh<?php echo $row; ?>" value="<?php echo $zcon[$row][5]; ?>">
                     <input type="hidden" id="ZoneCrct<?php echo $row; ?>" value="<?php echo $zcon[$row][6]; ?>">
                     <input type="hidden" id="ZoneTrig<?php echo $row; ?>" value="<?php echo $zcon[$row][7]; ?>">
                <?php } ?>
                <!-- Extra line added for when creating a new alarm zone.                                                 -->
                <li class="editable" style="display:none;" id="zone_<?php echo $row; ?>" >
                    <!-- Bury all the zone data so it can be accessed from the configuration screeen -->
                    <input type="hidden" id="ZoneType<?php echo $row; ?>" value="Alarm">
                    <input type="hidden" id="ZoneName<?php echo $row; ?>" value="New zone">
                    <input type="hidden" id="ZoneDM<?php echo $row; ?>" value="On">
                    <input type="hidden" id="ZoneNM<?php echo $row; ?>" value="On">
                    <input type="hidden" id="ZoneCh<?php echo $row; ?>" value="On">
                    <input type="hidden" id="ZoneCrct<?php echo $row; ?>" value="1">
                    <input type="hidden" id="ZoneTrig<?php echo $row; ?>" value="">
                </li>
                <!-- End of extra line                                                    -->
                </ul>
        </p>
</ul>
<br><br>

<!-- Floating alarm buttons                      -->
<div class="security-button-wrapper show" style="position: fixed; top: 46px; width: 100%">
    <ul data-role="listview" data-inset="true" style="padding: 0em; border-style: none;" data-filter="false">
        <li id="alarm-header" style="margin-bottom: 10px; font-size: 14px; height: 18px; padding-left: 20px;
                 padding-top: 7px; padding-bottom: 7px;"">Alarm:
                 <span class="ui-li-count count infoBox" id="alarm-mode-info" style="width: 75px;"><?php echo $status[1]; ?></span></li>
    </ul>
<!-- NOTE: need to add 'style="cursor pointer"' to convert a tap on an iphone into a click...   -->
    <a class="RoundButton red" id="SetButton" style="left: 10%; top: 40px; cursor: pointer" ><span class="RoundButtonText2">Set</span></a>
    <a class="RoundButton blue" id="TestButton" style="left: 40%; top: 40px; cursor: pointer"><span class="RoundButtonText2">Test</span></a>
    <a class="RoundButton green" id="ResetButton" style="left: 70%; top: 40px; cursor: pointer"><span class="RoundButtonText2">Reset</span></a>
</div>

<!-- put some blank space at the end of the list.    -->
<p style="height:100vh"></p>
</div>

<!-- Editor popup allows switch parameters to be modified or created.                                                -->
<div data-role="popup" id="SecurityEditor" class="floaty"
                   style="padding-top: 0; padding-bottom: 0;" data-dismissible="false">
    <div data-role="header">
        <h1 id="headerText">Edit zone 1</h1>
    </div>

<!-- JQuery Mobile does a thing when opening or closing a popup. The focus automatically goes to the first input box on
     the form. When running on an iPhone, this will activate the keypad. A decoy input is used to prevent the keypad jumping
     up and down. Setting the position as fixed effectively hides it. When it receives the focus, it immediately drops it again.                                                                                                                  -->
    <input type="text" readonly="readonly" onfocus="blur();" style="position: fixed;"> 

    <p><table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center; padding: 0px 10px; text-shadow: none"
                border="0">
           <tr><th colspan="5" style="text-align:left; white-space:normal;">Zone name:</th></tr>
           <tr><td colspan="5"><input type="text" style="margin: 0;" id="EditZoneName"></td></tr>
           <tr><th colspan="2" style="text-align:left; white-space:normal;">Circuit:</th><th colspan="3" style="text-align:left">Type:</th></tr>
           <tr><td colspan="2"><select id="EditCircuitNum" data-mini="true"​>
                   <?php for($count=0; $count<=12; $count++) { ?>
                       <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                   <?php } ?>
            </select></td>
            <td colspan="3"><fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
                <input name="radio-zoneType" onclick="ShowHide('alarm','1000');"id="radio-zoneType-a" type="radio" checked="checked" value="Alarm">
                <label for="radio-zoneType-a">Alarm</label>
                <input name="radio-zoneType" onclick="ShowHide('tamper','1000');"id="radio-zoneType-b" type="radio" value="Tamper">
                <label for="radio-zoneType-b">Tamper</label>
            </fieldset></td></tr>

            <tr><td colspan="5">
            <div id="alarmOptions">
                <label id="HideMeLabel" style="text-align: left;">Options for this alarm zone:</label>
                <div data-role="fieldcontain" id="HideMe">
                    <fieldset data-role="controlgroup">
                        <input type="checkbox" name="checkbox-1a" id="checkbox-DM" class="custom" />
                        <label style="background: transparent;" for="checkbox-DM">Day mode</label>
                        <input type="checkbox" name="checkbox-2a" id="checkbox-NM" class="custom" />
                        <label style="background: transparent;" for="checkbox-NM">Night mode</label>
                        <input type="checkbox" name="checkbox-3a" id="checkbox-Ch" class="custom" />
                        <label style="background: transparent;" for="checkbox-Ch">Chimes</label>
                    </fieldset>
                </div>
            </div></td></tr>
    </table>

    <a href="#" id="saveZone" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Save</span></a>
    <a href="#" id="deleteZone" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Delete</span></a>
    <a href="#" id="cancelZone" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Cancel</span></a>
    </p>

</div>
