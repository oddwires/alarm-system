<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<?php $filename2 = '/var/data/app-sensor/current_values.txt';
    if (file_exists($filename2)) {
        $file = fopen($filename2,'r');
        if (!$file) {                                                        // Sometimes the stoopid file still won't open !
            $found=false;                                                    // ( e.g. permission issues or freaky timing issues )
//            break; 
        }   
        while (!feof($file)) {
            $json=fgets($file);
//            echo $json."<br>";
            $temperatures=(json_decode($json, TRUE));
//            var_dump($temperatures);
//            if (array_key_exists("Bathroom",$temperatures)) { 
//			echo $temperatures["Bathroom"]."<br>"; 
//			$tmp = number_format(floatval($temperatures["Bathroom"]), 1);
//			}
        }
    } ?>

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
                <tr>

<!-- Add the temperature if the data is available...                                                                         -->
    <?php if (array_key_exists($rdtr[$row][2],$temperatures)) {
                echo "<td style=\"padding: 0px; width: 80%; \"><a href=\"#\" id=\"radiator_".$row."\" class=\"ui-btn floaty radiator button ui-shadow\">";
                echo $rdtr[$row][2];
                echo "<span style=\"color: #fff !important; background-color: rgba(0,0,0,0.3); border-style: none; text-shadow: none; position: absolute; right: 10px; top: 12px font-size: large\">&nbsp".number_format(floatval($temperatures[$rdtr[$row][2]]), 1); } 
          else { ?>
<!-- Add the carat if the temperature isn't available...                                                                     -->
    <?php       echo "<td style=\"padding: 0px; width: 80%; \"><a href=\"#\" id=\"radiator_".$row."\"";
                echo "class=\"ui-btn floaty radiator button ui-shadow ui-btn-icon-right ui-icon-carat-r\">".$rdtr[$row][2];
          }
    ?>
        </a></td>
        <td style="text-align: right; width: 50%">
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

<div data-role="footer" data-position="fixed">
    <div data-role="navbar">
        <ul>
            <li><a id="HeatButton" class="SquareButton green" style="margin-top: 4px; left: 2%">
            <span class="gloss"></span>
            <span class="SquareButtonText">Set</span>
            </a></li>
            <li style="margin-left: 24%; width: 50%; text-align: center">
                 <h1 id="HeatStatus" style="font-size: 20px;margin-left: 0px;align-content: center;margin-top: 10px;margin-bottom: 10px;">
                     <?php echo $HEAT_mode ?></h1></li>
            <div class="scroll-top-wrapper" style="bottom: unset; right: 14%">
            <li style=" width: unset">
            <a id="TopButton" href="#" class="SquareButton green" style="margin-top: 4px; left: 79%" onclick="scrollToTop()"> 
                <span class="gloss"></span>
                <span class="SquareButtonText">Top</span>
            </a></li>
            </div>
        </ul>
    </div>
</div><!-- /footer -->

<!-- Heating modes Action sheet dialog -->
<div data-role="popup" id="HeatPopup" style="border: 0; background: #80808075;" data-transition="slideup">
    <div data-role="content" style="padding: 8px;">
        <a href="#" class="ui-popup-button title" data-role="button" data-rel="back">Select the required mode.</a>
        <a href="#" class="ui-popup-button option" onclick="ChangeHeatMode('Heat mode:Heat and Water','Heat and Water')"
                data-role="button" data-rel="back">Heat + Water</a>
        <a href="#" class="ui-popup-button option" onclick="ChangeHeatMode('Heat mode:Water only','Water only')"
                data-role="button" data-rel="back">Water only</a>
        <a href="#" class="ui-popup-button option last" onclick="ChangeHeatMode('Heat mode:Heat off','Heat Off')"
                data-role="button" data-rel="back">Off</a>
        <a href="#" class="ui-popup-button cancel" data-role="button" data-rel="back">Cancel</a>
    </div>
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
