<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <table style="width:98%;" border="0" align="center"><tr>
          <td style="width: 25%"><a href="#sidePanel" class="menuButton"data-role="button" data-icon="bars">Menu</a></td>
          <td style="width: 35%"><h1 class="titleLabel ui-title" id="securityTitle">Alarm</h1></td>
          <td style="width: 40%; text-align: right;">
             <a href="#" data-role="button" data-icon="plus" class="AddDel_Button" id="securityButton">Add</a></td></tr>
    </table>
</div>
<p data-role="screenSpacer"><br></p>          <!-- stop first item from getting stuck behind the header -->
<br>
    
<input class="lineCount" type="hidden" value="<?php echo $ZnNum; ?>"> 
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new alarm zone                              -->
    <div class="ui-collapsible-set" data-inset="true" style="margin-left: 5px; margin-right:5px;">
        <div data-role="collapsible" class="animateMe" data-iconpos="right" style="border-radius: 5px;">
            <h3>Alarm mode:
            <?php echo ($status[0]=="Set") ? 
            '<div id="alarmMode" class="info_small" style="width: 110px; padding-top: 10px; padding-bottom: 10px; display:inline-block; margin-left: 30px;">' : 
            '<div id="alarmMode" style="width: 110px; padding-top: 10px; padding-bottom: 10px; background-color: #f00; display:inline-block; margin-left: 30px;">'; ?>
            <b style="margin-left: 10px; margin-right: 10px;"><?php echo $status[1]; ?></b>
        </div></h3>
            <fieldset data-role="controlgroup" data-mini="true" data-type="horizontal" style="text-align: center">
                <legend>Set alarm mode:</legend>
                    <input name="alarm-radio-choice" id="radio-choice-a" type="radio" 
                         <?php if (strtolower($status[1])=="day mode") { ?> checked="checked" <?php } ?> value="Day mode">
                    <label for="radio-choice-a">Day mode</label>
                    <input name="alarm-radio-choice" id="radio-choice-b" type="radio"
                         <?php if (strtolower($status[1])=="night mode") { ?> checked="checked" <?php } ?> value="Night mode">
                    <label for="radio-choice-b">Night mode</label>
                    <input name="alarm-radio-choice" id="radio-choice-c" type="radio"
                         <?php if (strtolower($status[1])=="standby") { ?> checked="checked" <?php } ?> value="Standby">
                    <label for="radio-choice-c">Standby</label>
            </fieldset>
            <div class="info_small">
            <p id="alarm-mode-info" style="text-align: left; height: 80px; margin-left: 10px; margin-right: 10px;">tbd</p>
            </div>
        </div>
        <div data-role="collapsible" class="animateMe" data-iconpos="right" style="border-radius: 5px;">
            <h3><div style="height: 33px; padding-top: 11px;" >Zones:
                Triggered: <?php echo $ZnTrig; ?><div></h3>
            <p>
                <ul data-role="listview" data-inset="true" id="zoneList" style="padding: 0em; background-color:#D4CBAC; border-style: none; ">
                    <?php for ($row=0; $row<$ZnNum; $row++) { ?>
                        <li class="editable" <?php echo ($zcon[$row][7]=="true") ? 'style="background-color: #f00;"' : ';'; ?>
                                id="zone_<?php echo $row; ?>" ><?php echo $zcon[$row][2]; ?>
                        <?php if (strtolower($zcon[$row][3])=='on') { ?> <span class="ui-li-count count-third">D</span> <?php } ?>
                        <?php if (strtolower($zcon[$row][4])=='on') { ?> <span class="ui-li-count count-second">N</span> <?php } ?>
                        <?php if (strtolower($zcon[$row][5])=='on') { ?> <span class="ui-li-count count">C</span> <?php } ?>
                            <!-- Bury all the zone data so it can be accessed from the configuration screen                      -->
                             <input type="hidden" id="ZoneType<?php echo $row; ?>" value="<?php echo $zcon[$row][1]; ?>">
                             <input type="hidden" id="ZoneName<?php echo $row; ?>" value="<?php echo $zcon[$row][2]; ?>">
                             <input type="hidden" id="ZoneDM<?php echo $row; ?>" value="<?php echo $zcon[$row][3]; ?>">
                             <input type="hidden" id="ZoneNM<?php echo $row; ?>" value="<?php echo $zcon[$row][4]; ?>">
                             <input type="hidden" id="ZoneCh<?php echo $row; ?>" value="<?php echo $zcon[$row][5]; ?>">
                             <input type="hidden" id="ZoneCrct<?php echo $row; ?>" value="<?php echo $zcon[$row][6]; ?>">
                             <input type="hidden" id="ZoneTrig<?php echo $row; ?>" value="<?php echo $zcon[$row][7]; ?>">
                        </li>
                    <?php } ?>
                    <!-- Extra line added for when creating a new alarm zone.                                                 -->
                    <li class="editable" style="display:none;" id="zone_<?php echo $row; ?>" >New alarm zone
                        <!--    <li class="arrow" <?php echo ($zcon[$row][7]=="true") ? 'style="background-color: #f00;">' : '>'; ?>      -->
                        <!-- Bury all the zone data so it can be accessed from the configuration screeen -->
                        <input type="hidden" id="ZoneType<?php echo $row; ?>" value="Alarm">
                        <input type="hidden" id="ZoneName<?php echo $row; ?>" value="New alarm zone">
                        <input type="hidden" id="ZoneDM<?php echo $row; ?>" value="On">
                        <input type="hidden" id="ZoneNM<?php echo $row; ?>" value="On">
                        <input type="hidden" id="ZoneCh<?php echo $row; ?>" value="On">
                        <input type="hidden" id="ZoneCrct<?php echo $row; ?>" value="1">
                        <input type="hidden" id="ZoneTrig<?php echo $row; ?>" value="">
                    </li>
                <!-- End of extra line                                                    -->
            </p>
        </div>
        <div data-role="collapsible" class="animateMe" data-iconpos="right" style="border-radius: 5px;">
            <h3><div style="height: 33px; padding-top: 11px;" >System test:
               <div></h3>
            <p><div class="info_small">External siren
            <a href="#" class="whiteButton" onclick="AlarmMode('test bell')" data-inline="true" data-role="button" style="left: 30px; ">Test</a>
            </div><br>
            <div class="info_small">External strobe
            <a href="#" class="whiteButton" onclick="AlarmMode('test strobe')" data-inline="true" data-role="button" style="left: 30px; ">Test</a>
            </div><br>
            <div class="info_small">Internal sounder
            <a href="#" class="whiteButton" onclick="AlarmMode('test sounder')" data-inline="true" data-role="button" style="left: 30px; ">Test</a>
            </div><br>
            </p>
        </div>
    </div>

    <!-- Editor screen. This bit gets around a bit, and will be tagged onto the end of any selected zone lines   -->
    <li style="display: none; background-color: #D4CBAC" id="zoneEditor";>
    <table style="width: 100%" border="0">
        <tr><td style="width: 75%">Name:</td>
            <td>Circuit:</td></tr>
        <tr><td><input data-wrapper-class="address" type="text" id="zoneName" ></td>
            <td><select id="circuitNum" data-mini="true"​>
                <?php for($count=0; $count<=12; $count++) { ?>
                   <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                <?php } ?>
        </select></td></tr>
    </table>
        <fieldset data-role="controlgroup" data-type="horizontal">
            <legend>Type:</legend>
            <input name="radio-zoneType" onclick="showHide('alarm');"id="radio-zoneType-a" type="radio" checked="checked" value="Alarm">
            <label for="radio-zoneType-a" >Alarm</label>
            <input name="radio-zoneType" onclick="showHide('tamper');"id="radio-zoneType-b" type="radio" value="Tamper">
            <label for="radio-zoneType-b">Tamper</label>
        </fieldset>
        <div id="zoneMessage" style="white-space: normal;"">Tamper zones are always active.</div>
        <div id="alarmOptions">                  <!-- Create a hideable div -->
            <fieldset class="ui-corner-all ui-controlgroup ui-controlgroup-vertical" data-role="controlgroup">
            <div class="ui-controlgroup-controls">
                <div class="ui-checkbox"><input class="custom" id="checkbox-DM" type="checkbox"><label class="ui-btn ui-fullsize ui-btn-icon-left ui-corner-top ui-checkbox-on ui-btn-up-c" for="checkbox-DM" data-theme="c" data-icon="checkbox-off" data-mini="false" data-corners="true" data-shadow="false" data-iconshadow="true" data-wrapperEls="span"><span class="ui-btn-inner ui-corner-top"><span class="ui-btn-text">Day mode</span><span class="ui-icon ui-icon-shadow ui-icon-checkbox-on">&nbsp;</span></span></label></div>
                <div class="ui-checkbox"><input class="custom" id="checkbox-NM" type="checkbox"><label class="ui-btn ui-fullsize ui-btn-icon-left ui-checkbox-off ui-btn-up-c" for="checkbox-NM" data-theme="c" data-icon="checkbox-off" data-mini="false" data-corners="true" data-shadow="false" data-iconshadow="true" data-wrapperEls="span"><span class="ui-btn-inner"><span class="ui-btn-text">Night mode</span><span class="ui-icon ui-icon-checkbox-off ui-icon-shadow">&nbsp;</span></span></label></div>
                <div class="ui-checkbox"><input class="custom" id="checkbox-Ch" type="checkbox"><label class="ui-btn ui-btn-up-c ui-fullsize ui-btn-icon-left ui-checkbox-off" for="checkbox-Ch" data-theme="c" data-icon="checkbox-off" data-mini="false" data-corners="true" data-shadow="false" data-iconshadow="true" data-wrapperEls="span"><span class="ui-btn-inner"><span class="ui-btn-text">Chimes</span><span class="ui-icon ui-icon-checkbox-off ui-icon-shadow">&nbsp;</span></span></label></div>
            </div></fieldset>
        </div>
    </li>
<!-- End of editor                                   -->
</ul>
<br><br>

<div class="info_small" style="margin-left: 20px; margin-right: 20px;">Reset
    <a href="#" class="whiteButton" onclick="AlarmMode('reset')" data-inline="true" data-role="button" style="left: 60px;">Reset</a>
</div>

<!-- put some blank space at the end of the list. Gives zone Editor some room to work in for the last items in the list view.   -->
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</div>