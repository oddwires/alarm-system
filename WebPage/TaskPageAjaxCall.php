<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT --> 

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;"><?php echo $CRnum; ?> Tasks</h1>
                <a href="#" data-transition="pop" class="SquareButton green AddBtn"
                                    data-rel="popup" data-position-to="window">
                  <span class="gloss"></span>
                    <span class="SquareButtonText" id="newTask">Add</span>
                </a>
</div>

<p data-role="screenSpacer" style="height: 10px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                     also acts as a marker for the scroll to top button.   -->
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new switch                                  -->

<ul data-role="listview" id="taskList" data-autodividers="true" data-filter="false">
<input id="taskCount" type="hidden" value="<?php echo $CRnum; ?>">
<?php // default format for cron job is rather messy, so tidy it up a bit...
      $months=array("January","February","March","April","May","June","July","August","September","October","November","December");
      $days=array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
      for($i=0; $i<$CRnum; $i++) {
            $tmp="";
            if ((strlen($cron[$i][2])==1) && ($cron[$i][2]!="*"))
               { $cron[$i][2] = "0".$cron[$i][2]; }                                     // put in leading zero minutes
            if ((strlen($cron[$i][3])==1) && ($cron[$i][3]!="*"))
               { $cron[$i][3] = "0".$cron[$i][3]; }                                     // put in leading zero hours

            // check for some common types of recurrence...
            if (($cron[$i][5]!="*") && ($cron[$i][4]=="*"))                             // value for month, but not for day of month.
               {  $tmp="During ".$months[$cron[$i][5]-1]."<br>"; }                      // Monthly recurrence (eg During March)

            if (($cron[$i][6]=="0") || ($cron[$i][6]=="1") || ($cron[$i][6]=="2") || ($cron[$i][6]=="3") || ($cron[$i][6]=="4")
                                  || ($cron[$i][6]=="5") || ($cron[$i][6]=="6"))        // day of week has been specified
                { $tmp.="On ".$days[$cron[$i][6]]."s <br>"; }

            if ($cron[$i][6]=="1-5")                                                    // weekday has been specified
                { $tmp.="On Week days<br>"; }

            if ($cron[$i][6]=="6-7")                                                    // weekday has been specified
                { $tmp.="At Weekends<br>"; }

            if (($cron[$i][2]!="*") && ($cron[$i][3]=="*"))                              // value for minutes but not for hours
                { $tmp.="Every hour<br>"; }
            else
                 {  if ($cron[$i][6]=="*")                                               // if a day is specified but no hours...
                        { $tmp.="Every day<br>"; }
                    if ($cron[$i][4]!="*")                                               // recurrence by date
                        { $tmp=$cron[$i][2]." ".$cron[$i][3]." ".$cron[$i][4]." ";       // worst case scenario just show raw data
                          $tmp.=$cron[$i][5]." ".$cron[$i][6]; }
                }
?>
<li class="floaty task" headr="<?php echo $cron[$i][1]; ?>..." id="task_<?php echo $i; ?>" style="margin-top: 5px;">
             <img class="listViewImg" src="/themes/images/schedule.png" style="top: auto;"><h1 style="margin-top: 0;">
             <?php echo $tmp; ?> at <?php echo $cron[$i][3].":".$cron[$i][2]; ?><br>
<?php echo $taskname[$cron[$i][7]]." ".$cron[$i][8]; ?></h1>
    <input type="hidden" id="group_<?php echo $i; ?>" value="<?php echo $cron[$i][1]; ?>">
    <input type="hidden" id="hours_<?php echo $i; ?>" value="<?php echo $cron[$i][3]; ?>">
    <input type="hidden" id="minutes_<?php echo $i; ?>" value="<?php echo $cron[$i][2]; ?>">
    <input type="hidden" id="dom_<?php echo $i; ?>" value="<?php echo $cron[$i][4]; ?>">
    <input type="hidden" id="month_<?php echo $i; ?>" value="<?php echo $cron[$i][5]; ?>">
    <input type="hidden" id="wday_<?php echo $i; ?>" value="<?php echo $cron[$i][6]; ?>">
    <input type="hidden" id="tasknum_<?php echo $i; ?>" value="<?php echo $cron[$i][7]; ?>">
    <input type="hidden" id="status_<?php echo $i; ?>" value="<?php echo $cron[$i][8]; ?>">
    </li>
    <?php } ?>
<!-- Extra line added for when creating a new task.                                                                  -->
<li style="display:none;" class="header editable" headr="" id="task_<?php echo $CRnum; ?>"><img src="/themes/images/schedule.png">
<h1>New task</h1>
<p>tbd</p></a> 
    <input type="hidden" id="group_<?php echo $CRnum; ?>" value="New task">
    <input type="hidden" id="hours_<?php echo $CRnum; ?>" value="2">
    <input type="hidden" id="minutes_<?php echo $CRnum; ?>" value="1">
    <input type="hidden" id="dom_<?php echo $CRnum; ?>" value="*">
    <input type="hidden" id="month_<?php echo $CRnum; ?>" value="*">
    <input type="hidden" id="wday_<?php echo $CRnum; ?>" value="*">
    <input type="hidden" id="tasknum_<?php echo $CRnum; ?>" value="1">
    <input type="hidden" id="status_<?php echo $CRnum; ?>" value="on">
    </li>                                <!-- End of extra line                                                    -->
<!-- put some blank space at the end of the list.                                                                    -->
<p style="height:100vh"></p>
</div>

<!-- Editor popup allows switch parameters to be modified or created.                                                -->
<div data-role="popup" id="taskEditor" class="floaty"
                   style="top: initial; padding-top: 0; padding-bottom: 0;" data-dismissible="false">
    <div data-role="header">
        <h1 id="taskText">Edit task 1</h1>
    </div>

<!-- JQuery Mobile does a thing when opening or closing a popup. The focus automatically goes to the first input box on
     the form. When running on an iPhone, this will activate the keypad. A decoy input is used to prevent the keypad jumping
     up and down. Setting the position as fixed effectively hides it. When it receives the focus, it immediately drops it again.                    -->
    <input type="text" readonly="readonly" onfocus="blur();" style="position: fixed;"> 

    <p><table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center;" border="0">
    <tr><th colspan="3" style="text-align:left;">Group / Heading</td></tr>
    <tr><td colspan="3"><input type="text" id="taskGroup"><tr>
    <th style="text-align:center; width:20%; white-space:normal;">Hours</th><th style="text-align:center; width:20%; white-space:normal;">Mins</th><th style="text-align:center; width:20%; white-space:normal;">Day</th></tr>
    <tr><td><select id="hours_value" data-mini="true"​>
            <?php $count=0;
                  for($count=0; $count<24; $count++) { ?>
                    <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
            <?php } ?><option value"*">*</option></td>
        <td><select id="mins_value" data-mini="true"​>
            <?php $count=0;
                   for($count=0; $count<60; $count++) { ?>
                     <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
            <?php } ?><option value"*">*</option></td>
        <td><select id="dom_value" data-mini="true"​>
             <?php $count=0;
                      for($count=1; $count<=31; $count++) { ?>
                    <option value="<?php echo $count; ?>"><?php echo $count; ?></option>
                <?php } ?><option value"*">*</option></td>
    </tr>
    <tr><th colspan="3" style="text-align:center; width:20%; white-space:normal;">Month</th></tr>
        <tr><td colspan="3"><select id="month_value" data-mini="true"​>
                    <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                    <option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option>
                    <option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option>
                    <option value="12">December</option><option value="*">*</option>
            </select></td>
    </tr>
    <tr><th colspan="3" style="text-align:center; width:20%; white-space:normal;">Day of week</th><tr>
        <td colspan="3"><select id="wday_value" data-mini="true"​>
                    <option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option>
                    <option value="5">Friday</option><option value="6">Saturday</option><option value="7">Sunday</option><option value="*">*</option>
                    <option value="1-5">Weekdays</option><option value="6-7">Weekends</option>
            </select></td>
    </tr></table>
    <table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center;" border="0">
        <tr><th colspan="3" style="text-align:left;">Device:</th>
            <th colspan="2" style="text-align:left;">Action:</th></tr>
            <tr><td colspan="3"><select id="task_value" data-mini="true"​>
                <!-- build the list of security options.                                                       -->
                <optgroup label="Security">
                    <option value="0">Day mode</option>
                    <option value="1">Night mode</option>
                    <option value="2">Standby mode</option>
                    <option value="3">Check IP</option>
                <!-- build list of all configured RC channels. Values start at end of security options ( 4 )   -->
                <optgroup label="Switches">
                    <?php $count=4; for($i=0; $i<$RCnum; $i++) { ?>
                        <option value="<?php echo $count; ?>"><?php echo $rcon[$i][2]; ?></option>
                    <?php $count++; } ?>
                <!-- add list of all configured Radiators. Values start where RC channels finished               -->
                <optgroup label="Radiators">
                    <?php for($i=0; $i<$RDTRnum; $i++) { ?>
                        <option value="<?php echo $count; ?>"><?php echo $rdtr[$i][2]; ?></option>
                    <?php $count++; } ?>
                        </select></td>
            <td colspan="2"><input type="checkbox" data-role="flipswitch" id="switch_value" data-wrapper-class="custom-size-flipswitch" style="text-align:right; font-size: 1em;"></td></tr>
    </td></tr></table>
    <a href="#" id="saveTask" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 9px;">
          <span class="gloss"></span><span class="SquareButtonText">&nbsp;Save&nbsp;</span></a>
    <a href="#" id="deleteTask" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 9px;">
          <span class="gloss"></span><span class="SquareButtonText">Delete</span></a>
    <a href="#" id="cancelTask" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 9px;">
          <span class="gloss"></span><span class="SquareButtonText">Cancel</span></a>
    </p>
</div>