<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;"><?php echo $RDTRnum; ?> Radiators</h1>
                <a href="#" class="SquareButton green AddDel_Button">
                  <span class="gloss"></span>
                    <span class="SquareButtonText" id="newRadiator">Add</span>
                </a>
</div>

<p data-role="screenSpacer" style="height: 10px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                     also acts as a marker for the scroll to top button.   -->
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new switch                                  -->

<ul data-role="listview" id="heatList" data-autodividers="true" data-filter="false">
    <input id="radiatorCount" type="hidden" value="<?php echo $RDTRnum; ?>">
    <?php for ($row=0; $row<$RDTRnum; $row++) { ?>
        <li headr="<?php echo $rdtr[$row][1]; ?>" id="radiator_<?php echo $row; ?>" style="z-index: 2; padding: 0em; border-style: none; background: none;">
            <table class="controlList">
                <tr><td style="padding: 0px; width: 80%; "><a href="#" id="radiator_<?php echo $row; ?>"
                        class="ui-btn floaty radiator button ui-shadow ui-btn-icon-right ui-icon-carat-r"><?php echo $rdtr[$row][2]; ?>
                </a></td>
                <td style="text-align: right;">
                    <input type="checkbox" class="rdtrSwitch" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch" 
                               data-off-text="<?php echo $rdtr[$row][6]; ?>" data-on-text="<?php echo $rdtr[$row][5]; ?>"
                               <?php if(strpos($rdtr[$row][4],"On")!== false) { echo 'checked=""'; } ?> value=<?php echo $row; ?>>
                </td></tr>
            </table>
            <input type="hidden" id="radiatorGroup_<?php echo $row; ?>" value="<?php echo $rdtr[$row][1]; ?>">
            <input type="hidden" id="radiatorName_<?php echo $row; ?>" value="<?php echo $rdtr[$row][2]; ?>">
            <input type="hidden" id="radiatorAddress_<?php echo $row; ?>" value="<?php echo $rdtr[$row][3]; ?>">
            <input type="hidden" id="radiatorStatus_<?php echo $row; ?>" value="<?php echo $rdtr[$row][4]; ?>">
            <input type="hidden" id="radiatorHigh_<?php echo $row; ?>" value="<?php echo $rdtr[$row][5]; ?>">
            <input type="hidden" id="radiatorLow_<?php echo $row; ?>" value="<?php echo $rdtr[$row][6]; ?>">
        </li>
     <?php } ?>

<!-- Extra line added for when creating a new radiator.                                                             -->
            <li headr="" id="radiator_<?php echo $row; ?>" style="display:none; padding: 0em; background-color:#D4CBAC; border-style: none; ">
            <table class="controlList" border="0">
            <tr><td class="editable" id="radiator_<?php echo $RDTRnum; ?>" style="text-align: left; width:80%; "></td>
            <td style="text-align: right;">
                <input type="checkbox" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch"
                           data-off-text="LOW" data-on-text="Hi" value=<?php echo $row; ?>>
                </td></tr></table>
                <input type="hidden" id="radiatorGroup_<?php echo $row; ?>"  value="New group">
                <input type="hidden" id="radiatorName_<?php echo $row; ?>" value="New radiator">
                <input type="hidden" id="radiatorAddress_<?php echo $row; ?>" value="0">
                <input type="hidden" id="radiatorStatus_<?php echo $row; ?>" value="Hi">
                <input type="hidden" id="radiatorHigh_<?php echo $row; ?>" value="Hi">
                <input type="hidden" id="radiatorLow_<?php echo $row; ?>" value="Low">
            </li>
<!-- End of extra line                                                                                               -->
</ul>

<!-- put some blank space at the end of the list.    -->
<p style="height:100vh"></p>
</div>

<!-- Editor popup allows switch parameters to be modified or created.                                                -->
<div data-role="popup" id="RadiatorEditor" class="floaty"
                   style="top: initial; padding-top: 0; padding-bottom: 0;" data-dismissible="false">
    <div data-role="header">
        <h1 id="RheaderText">Edit radiator 1</h1>
    </div>

<!-- JQuery Mobile does a thing when opening or closing a popup. The focus automatically goes to the first input box on
     the form. When running on an iPhone, this will activate the keypad. A decoy input is used to prevent the keypad jumping
     up and down. Setting the position as fixed effectively hides it. When it receives the focus, it immediately drops it again.                    -->
    <input type="text" readonly="readonly" onfocus="blur();" style="position: fixed;"> 

    <p>
    <table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center;" border="0">
    <tr><th colspan="3" style="text-align:left;">Group / Heading</td></tr>
    <tr><td colspan="3"><input type="text" id="rdtrGroup"><tr>
    <th colspan="3" style="text-align:left; width:20%; white-space:normal;">Name</th></tr>
    <tr><td colspan="3"><input type="text" id="rdtrName">
    <tr><th style="text-align:center; width:20%; white-space:normal;">Address</th>
        <th style="text-align:center; width:20%; white-space:normal;">High</th>
        <th style="text-align:center; width:20%; white-space:normal;">Low</th><tr>
    <tr><td ><select id="rdtrAddress" data-mini="true"​>
            <?php for($count=0; $count<=8; $count++) { ?>
               <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
            <?php } ?></td>
        <td ><select id="rdtrHi" data-mini="true"​>
               <option value="Hi">Hi</option>
            <?php for($count=5; $count<=26; $count++) { ?>
               <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
            <?php } ?></td>
        <td ><select id="rdtrLow" data-mini="true"​>
               <option value="Low">Low</option>
            <?php for($count=5; $count<=26; $count++) { ?>
               <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
            <?php } ?></td><tr>
    </table>
    <a href="#" id="saveRadiator" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Save</span></a>
    <a href="#" id="deleteRadiator"class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Delete</span></a>
    <a href="#"  id="cancelRadiator" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Cancel</span></a>
    </p>
</div>
