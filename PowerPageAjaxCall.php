<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <table style="width:98%;" border="0" align="center"><tr>
          <td style="width: 25%"><a href="#sidePanel" class="menuButton"data-role="button" data-icon="bars">Menu</a></td>
          <td style="width: 35%"><h1 class="titleLabel ui-title" id="powerTitle">Power</h1></td>
          <td style="width: 40%; text-align: right;">
             <a href="#" data-role="button" data-icon="search" class="searchButton" data-iconpos="notext" style="font-size: 19px;">!</a>
             <a href="#" data-role="button" data-icon="plus" class="AddDel_Button" id="powerButton">Add</a></td></tr>
    </table>
</div>
<input class="lineCount" type="hidden" value="<?php echo $RCnum; ?>">

<p data-role="screenSpacer" style="height: 10px;"></p>          <!-- stop first item from getting stuck behind the header -->
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new switch                                  -->

<ul data-role="listview" id="switchList" data-autodividers="true" data-filter="true">
     <?php for ($row=0; $row<$RCnum; $row++) { ?>
        <li headr="<?php echo $rcon[$row][1]; ?>" id="switch_<?php echo $row; ?>" style="padding: 0em; background-color:#D4CBAC; border-style: none; ">
        <table class="controlList" border="0">
        <tr><td class="editable ui-icon-plus" id="switch_name_<?php echo $row; ?>" style="text-align: left; width:80%; "><?php echo $rcon[$row][2]; ?></td>
        <td style="text-align: right;">
            <input type="checkbox" class="powerSwitch" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch" data-off-text="OFF" data-on-text="ON"
                       <?php if(strpos($rcon[$row][5],"On")!== false) { echo 'checked=""'; } ?> value=<?php echo $row; ?>>
            </td></tr></table>
            <input type="hidden" id="switch_group_<?php echo $row; ?>" value=<?php echo $rcon[$row][1]; ?>>
            <input type="hidden" id="switch_address_<?php echo $row; ?>" value=<?php echo $rcon[$row][3]; ?>>
            <input type="hidden" id="switch_channel_<?php echo $row; ?>" value=<?php echo $rcon[$row][4]; ?>>
            <input type="hidden" id="switchAlrmAction_<?php echo $row; ?>" value=<?php echo $rcon[$row][6]; ?>>
            <input type="hidden" id="switch_HK_<?php echo $row; ?>" value=<?php echo $rcon[$row][7]; ?>>
        </li>
     <?php } ?>

<!-- Extra line added for when creating a new switch.                                                             -->
            <li headr="" id="switch_<?php echo $RCnum; ?>" style="display:none; padding: 0em; background-color:#D4CBAC; border-style: none; ">
            <table class="controlList" border="0">
            <tr><td class="editable" id="switch_name_<?php echo $RCnum; ?>" style="text-align: left; width:80%; ">New switch</td>
            <td style="text-align: right;">
                <input type="checkbox" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch"
                           data-off-text="OFF" data-on-text="ON" value=<?php echo $row; ?>>
                </td></tr></table>
                <input type="hidden" id="switch_group_<?php echo $row; ?>" value="New alarm zone#">
                <input type="hidden" id="switch_address_<?php echo $row; ?>" value="24">
                <input type="hidden" id="switch_channel_<?php echo $row; ?>" value="5">
                <input type="hidden" id="switchAlrmAction_<?php echo $row; ?>" value="none">
                <input type="hidden" id="switch_HK_<?php echo $row; ?>" value="switch">
            </li>
<!-- End of extra line                                                                                               -->

<!-- Editor screen. This bit gets around a bit, and will be tagged onto the end of any selected power switch lines   -->
            <li style="display: none; background-color: #D4CBAC" id="switchEditor";>
            <table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center;" border="0">
            <tr><th colspan="5" style="text-align:left;">Group / Heading</td></tr>
            <tr><td colspan="5"><input type="text" id="switchGroup"><tr>
            <th colspan="5" style="text-align:left; width:20%; white-space:normal;">Name</th></tr>
            <tr><td colspan="5"><input type="text" id="switchName">
            <tr><th colspan="2" style="text-align:center; width:20%; white-space:normal;">Address</th><th></th>
                <th colspan="2" style="text-align:center; width:20%; white-space:normal;">Channel</th><tr>
            <tr><td colspan="2" ><select id="switchAddress" data-mini="true"​>
                    <?php for($count=0; $count<=31; $count++) { ?>
                       <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                    <?php } ?></td><td></td>
                <td colspan="2" ><select id="switchChannel" data-mini="true"​>
                    <?php for($count=0; $count<=6; $count++) { ?>
                       <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                    <?php } ?></td><tr>
            <tr><th colspan="5" style="text-align:left;">Action on alarm:</th>
            <tr><td colspan="5"><select id="switchAlrmAction" data-mini="true"​>
                        <option value="On">Switch on</option>
                        <option value="Off">Switch off</option>
                        <option value="None">None (do nothing)</option>
                    </select></td></tr>
            <tr><th colspan="5" style="text-align:left;">HomeKit accessory type:</th>
            <tr><td colspan="5"><select id="switchHK" data-mini="true"​>
                        <option value="Outlet">Outlet</option>
                        <option value="Light">Light</option>
                        <option value="Fan">Fan</option>
                        <option value="None">None (do not export)</option>
                    </select></td></tr>
            </table>
            </li>
<!-- End of editor                                   -->
</ul>
<!-- put some blank space at the end of the list. Gives Power editor some room to work in for the last items in the list view.   -->
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</div>