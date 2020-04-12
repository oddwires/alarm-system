<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="true">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;"><?php echo $RCnum; ?> Switches</h1>
                <a href="#PowerEditor" data-transition="pop" class="SquareButton green AddBtn"
                                    data-rel="popup" data-position-to="window">
                  <span class="gloss"></span>
                    <span class="SquareButtonText" id="newSwitch">Add</span>
                </a>
</div>

<p data-role="screenSpacer" style="height: 10px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                     also acts as a marker for the scroll to top button.
Make all task configuration data available on the page just in case we want to drill down and start editing
Note: added a spare entry at the end in case we want to create a new switch                                                    -->

<ul data-role="listview" id="switchList" data-autodividers="true" data-filter="false">
    <input id="switchCount" type="hidden" value="<?php echo $RCnum; ?>">
    <?php for ($row=0; $row<$RCnum; $row++) { ?>
        <li headr="<?php echo $rcon[$row][1]; ?>" id="switch_<?php echo $row; ?>" style="z-index: 2; padding: 0em; border-style: none; background: none;">
        <table class="controlList"><tbody class="floaty_table"
            <tr><td style="padding: 0px; width: 80%; "><a href="#" id="switch_name_<?php echo $row; ?>"
                    class="ui-btn floaty switch button ui-shadow ui-btn-icon-right ui-icon-carat-r"><?php echo $rcon[$row][2]; ?>
            </a></td>
            <td style="text-align: right;">
                <input type="checkbox" class="powerSwitch" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch" data-off-text="OFF" data-on-text="ON"
                        <?php if(strpos($rcon[$row][6],"On")!== false) { echo 'checked=""'; } ?> value=<?php echo $row; ?>>
            </td></tr>
            </tbody></table>
            <input type="hidden" id="switch_type_<?php echo $row; ?>" value=<?php echo $rcon[$row][3]; ?>>
            <input type="hidden" id="switch_group_<?php echo $row; ?>" value=<?php echo $rcon[$row][1]; ?>>
            <input type="hidden" id="switch_address_<?php echo $row; ?>" value=<?php echo $rcon[$row][4]; ?>>
            <input type="hidden" id="switch_channel_<?php echo $row; ?>" value=<?php echo $rcon[$row][5]; ?>>
            <input type="hidden" id="switchAlrmAction_<?php echo $row; ?>" value=<?php echo $rcon[$row][7]; ?>>
            <input type="hidden" id="switch_HK_<?php echo $row; ?>" value=<?php echo $rcon[$row][8]; ?>>
        </li>
     <?php } ?>

<!-- Extra line added for when creating a new switch.                                                             -->
            <li headr="" style="display:none;" id="switch_<?php echo $RCnum; ?>">
            <table class="controlList" border="0">
            <tr><td class="editable" id="switch_name_<?php echo $RCnum; ?>" style="text-align: left; width:80%; ">New switch</td>
            <td style="text-align: right;">
                <input type="checkbox" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch"
                           data-off-text="OFF" data-on-text="ON" value=<?php echo $row; ?>>
                </td></tr></table>
                <input type="hidden" id="switch_type_<?php echo $row; ?>" value="">
                <input type="hidden" id="switch_group_<?php echo $row; ?>" value="New group">
                <input type="hidden" id="switch_address_<?php echo $row; ?>" value="24">
                <input type="hidden" id="switch_channel_<?php echo $row; ?>" value="5">
                <input type="hidden" id="switchAlrmAction_<?php echo $row; ?>" value="None">
                <input type="hidden" id="switch_HK_<?php echo $row; ?>" value="Outlet">
            </li>
<!-- End of extra line                                                                                               -->
</ul>

<!-- put some blank space at the end of the list.                                                                    -->
<p style="height:100vh"></p>
</div>

<!-- Editor popup allows switch parameters to be modified or created.                                                -->
<div data-role="popup" id="PowerEditor" class="floaty"
                   style="top: initial; padding-top: 0; padding-bottom: 0;" data-dismissible="false">
    <div data-role="header">
        <h1 id="headerText">Edit switch 1</h1>
    </div>

<!-- JQuery Mobile does a thing when opening or closing a popup. The focus automatically goes to the first input box on
     the form. When running on an iPhone, this will activate the keypad. A decoy input is used to prevent the keypad jumping
     up and down. Setting the position as fixed effectively hides it. When it receives the focus, it immediately drops it again.                    -->
    <input type="text" readonly="readonly" onfocus="blur();" style="position: fixed;"> 

    <p><table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center; padding: 0px 10px; text-shadow: none"
                border="0">
        <tr><th colspan="3" style="text-align:left; white-space:normal;">Name:</th>
            <td colspan="4"><input type="text" style="margin: 0;" id="switchName"></td></tr>
        <th colspan="3" style="text-align:left;">Room:</th>
            <td colspan="4"><input type="text" id="switchGroup"></td></tr>
            <th colspan="3" style="text-align:left; white-space:normal;">Type:</th><td></td></tr>
            <td colspan="3" style="vertical-align: top; height:112px;" ><fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
                <input name="radio-switchType" onclick="ShowHide2('RC');"id="radio-switchType-a" type="radio" checked="checked" value="Radio">
                <label for="radio-switchType-a" style="padding-left:5px; padding-right:5px; ">Radio</label>
                <input name="radio-switchType" onclick="ShowHide2('WiFi');"id="radio-switchType-b" type="radio" value="WiFi">
                <label for="radio-switchType-b" style="padding-left:5px; padding-right:5px; ">WiFi</label>
        </fieldset></td>
        <td colspan="5" style="text-align: right; display:inline-flex;">

        <table border="0" style="width: 152px;">
        <tr id="Row1Hide"><td style="font-size: small">192.168.1.</td>
            <td><select id="IPAddress" data-mini="true"​>
                <?php for($count=100; $count<=110; $count++) { ?>
                    <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                <?php } ?></td></tr>
        <tr id="Row2Hide"><td style="font-size: small">Address</td>
            <td><select id="switchAddress" data-mini="true"​>
                <?php for($count=0; $count<=31; $count++) { ?>
                   <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                <?php } ?></td>
        <tr id="Row3Hide"><td style="font-size: small">Channel</td>
            <td><select id="switchChannel" data-mini="true"​>
                <?php for($count=0; $count<=6; $count++) { ?>
                   <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                <?php } ?></td>
        </table>

        <tr><th colspan="3" style="text-align:left;">Action on alarm:</th>
            <td colspan="4"><select id="switchAlrmAction" data-mini="true"​>
                    <option value="On">Switch on</option>
                    <option value="Off">Switch off</option>
                    <option value="None">None (do nothing)</option>
                </select></td></tr>
        <tr><th colspan="3" style="text-align:left;">HomeKit type:</th>
            <td colspan="4"><select id="switchHK" data-mini="true"​>
                    <option value="Outlet">Outlet</option>
                    <option value="Light">Light</option>
                    <option value="Fan">Fan</option>
                    <option value="None">None (do not export)</option>
                </select></td></tr>
    </table>
    <a href="#" id="saveSwitch" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Save</span></a>
    <a href="#" id="deleteSwitch"class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Delete</span></a>
    <a href="#"  id="cancelSwitch" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Cancel</span></a>
    </p>
</div>