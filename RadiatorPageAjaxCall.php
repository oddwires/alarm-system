<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <table style="width:98%;" border="0" align="center"><tr>
          <td style="width: 25%"><a href="#sidePanel" class="menuButton"data-role="button" data-icon="bars">Menu</a></td>
          <td style="width: 35%"><h1 class="titleLabel ui-title" id="radiatorTitle">Heat</h1></td>
          <td style="width: 40%; text-align: right;">
             <a href="#" data-role="button" data-icon="search" class="searchButton" data-iconpos="notext" style="font-size: 19px;">!</a>
             <a href="#" data-role="button" data-icon="plus" class="AddDel_Button" id="radiatorButton">Add</a></td></tr>
    </table>
</div>
<input class="lineCount" type="hidden" value="<?php echo $RDTRnum; ?>">

<p data-role="screenSpacer" style="height: 10px;"></p>          <!-- stop first item from getting stuck behind the header -->
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new switch                                  -->

<ul data-role="listview" id="radiatorList" data-autodividers="true" data-filter="true">
     <?php for ($row=0; $row<$RDTRnum; $row++) { ?>
        <li headr="<?php echo $rdtr[$row][1]; ?>" id="radiator_<?php echo $row; ?>" style="padding: 0em; background-color:#D4CBAC; border-style: none; ">
        <table class="controlList" border="0">
        <tr><td class="editable" id="radiator_<?php echo $row; ?>" style="text-align: left; width:80%; "><?php echo $rdtr[$row][2]; ?></td>
            <td style="text-align: right;">
                <input type="checkbox" class="rdtrSwitch" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch" 
                           data-off-text="<?php echo $rdtr[$row][6]; ?>" data-on-text="<?php echo $rdtr[$row][5]; ?>"
                           <?php if(strpos($rdtr[$row][4],"On")!== false) { echo 'checked=""'; } ?> value=<?php echo $row; ?>>
                </td></tr></table>
                <input type="hidden" id="radiatorGroup_<?php echo $row; ?>" value="<?php echo $rdtr[$row][1]; ?>">
                <input type="hidden" id="radiatorName_<?php echo $row; ?>" value="<?php echo $rdtr[$row][2]; ?>">
                <input type="hidden" id="radiatorAddress_<?php echo $row; ?>" value="<?php echo $rdtr[$row][3]; ?>">
                <input type="hidden" id="radiatorStatus_<?php echo $row; ?>" value="<?php echo $rdtr[$row][4]; ?>">
                <input type="hidden" id="radiatorHigh_<?php echo $row; ?>" value="<?php echo $rdtr[$row][5]; ?>">
                <input type="hidden" id="radiatorLow_<?php echo $row; ?>" value="<?php echo $rdtr[$row][6]; ?>">
            </li>
     <?php } ?>
<!-- Extra line added for when creating a new switch.                                                                  -->
        <li headr="" id="radiator_<?php echo $RDTRnum; ?>" style="display:none; padding: 0em; background-color:#D4CBAC; border-style: none; ">
        <table class="controlList" border="0">
        <tr><td class="editable" id="radiator_<?php echo $RDTRnum; ?>" style="text-align: left; width:80%; ">New radiator</td>
        <td style="text-align: right;">
            <input type="checkbox" data-role="flipswitch" data-wrapper-class="custom-size-flipswitch"
                       data-off-text="Low" data-on-text="Hi" value=<?php echo $row; ?>>
            </td></tr></table>
            <input type="hidden" id="radiatorGroup_<?php echo $row; ?>" value="New group">
            <input type="hidden" id="radiatorName_<?php echo $row; ?>" value="New radiator">
            <input type="hidden" id="radiatorAddress_<?php echo $row; ?>" value="1">
            <input type="hidden" id="radiatorStatus_<?php echo $row; ?>" value="Off">
            <input type="hidden" id="radiatorHigh_<?php echo $row; ?>" value="Hi">
            <input type="hidden" id="radiatorLow_<?php echo $row; ?>" value="Low">
        </li>
        <!-- End of extra line                                                    -->

<!-- Editor screen. This bit gets around a bit, and will be tagged onto the end of any selected power switch lines   -->
            <li style="display: none; background-color: #D4CBAC" id="radiatorEditor";>
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
            </li>
<!-- End of editor                                   -->
</ul>
<!-- put some blank space at the end of the list. Gives Radiator editor some room to work in for the last items in the list view.   -->
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</div> 