<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT 
?>

<!-- File contains the HTML code to redraw both the Alarm control panel and the zones scroller.    -->
<!-- This allows both the alarm status, and the zone status colours to be updated by one AJAX call -->

    <div class="toolbar"><h1>Alarm</h1>
<!--    <a class="back2div menu" href="#menusheet">< Menu</a> -->
        <a class="back menu" href="#menusheet">Menu</a>
        <a class="button slide" onclick="ZconJmp(<?php echo $ZnNum ?>)" href="#">Add...</a>
    </div>

    <!-- Alarm status panel is contained in a table -->
        <table border="0" style="margin: 0px auto; align: center">
        <tr><td style="width: 15%;">
               <div class="RoundButton" style="font-size: 25px">
                   <a href="#set" class="action" style="text-decoration: none">Set</a>
               </div></td>
            <td><ul class="rounded" style="margin-bottom: 0px; margin-top: 0px; margin-left: 0px; margin-right: 0px; text-align: center">
                    <?php if ($status[0]=="Set") echo '<li>'.$status[1].'</li>';
                          else                   echo '<li style="background-color: #f00;">'.$status[0].'</li>';  ?>
            </ul></td>
            <td style="width: 15%">
               <div class="RoundButton" style="font-size: 25px">
                   <a href="#test" class="action" style="text-decoration: none">Test</a>
            </div></td>
        </tr></table>

    <div id="alarmscroll" class="scroll">
        <ul class="rounded">
        <?php for ($row=0; $row<$ZnNum; $row++) { ?>
            <li class="arrow" <?php echo ($zcon[$row][7]=="true") ? 'style="background-color: #f00;">' : '>'; ?>
                <a href="#" onclick="ZconJmp(<?php echo $row; ?>)" id="AlrmHlnk<?php echo $row; ?>">
                     <?php echo $zcon[$row][2]; ?> <small><?php echo $label[$row]; ?></small></a>
                <!-- Bury all the zone data so it can be accessed from the configuration screeen -->
                <input type="hidden" id="ZoneType<?php echo $row; ?>" value="<?php echo $zcon[$row][1]; ?>">
                <input type="hidden" id="ZoneName<?php echo $row; ?>" value="<?php echo $zcon[$row][2]; ?>">
                <input type="hidden" id="ZoneFset<?php echo $row; ?>" value="<?php echo $zcon[$row][3]; ?>">
                <input type="hidden" id="ZonePset<?php echo $row; ?>" value="<?php echo $zcon[$row][4]; ?>">
                <input type="hidden" id="ZoneChim<?php echo $row; ?>" value="<?php echo $zcon[$row][5]; ?>">
                <input type="hidden" id="ZoneCirc<?php echo $row; ?>" value="<?php echo $zcon[$row][6]; ?>">
                <input type="hidden" id="ZoneTrig<?php echo $row; ?>" value="<?php echo $zcon[$row][7]; ?>">
            </li>
        <?php } ?>
    </div>
</div>
