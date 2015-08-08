<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT    
?>

<div id="tasklist" class="selectable">
            <div class="toolbar">
                <h1>Tasks</h1>
                <a class="back2div menu" onclick="ResetMenu()" href="#menusheet">Menu</a>
                <a class="button slide" href="#" onclick="newtask()">New</a>
            </div>
            <div id="TaskScroll" class="scroll">
            <ul class="edgetoedge scroll" style="margin-top: 0">
            <input type="hidden" id="taskcount" value="<?php echo $CRnum; ?>">
               <?php // default format for cron job is rather messy, so tidy it up a bit...
                     $months=array("January","February","March","April","May","June","July","August","September","October","November","December");
                     $days=array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
                     for($i=0; $i<$CRnum; $i++) { 
                       $tmp="";
                       if ((strlen($cron[$i][1])==1) && ($cron[$i][1]!="*"))
                          { $cron[$i][1] = "0".$cron[$i][1]; }                                     // put in leading zero minutes
                       if ((strlen($cron[$i][2])==1) && ($cron[$i][2]!="*"))
                          { $cron[$i][2] = "0".$cron[$i][2]; }                                     // put in leading zero hours

                       // check for some common types of recurrence...
                       if (($cron[$i][4]!="*") && ($cron[$i][3]=="*"))                             // value for month, but not for day of month.
                          {  $tmp="During ".$months[$cron[$i][4]-1]."<br>"; }                      // Monthly recourance (eg During March)

                       if (($cron[$i][5]=="0") || ($cron[$i][5]=="1") || ($cron[$i][5]=="2") || ($cron[$i][5]=="3") || ($cron[$i][5]=="4")
                                              || ($cron[$i][5]=="5") || ($cron[$i][5]=="6"))      // day of week has been specified
                           { $tmp.="On ".$days[$cron[$i][5]]."s <br>"; }

                       if ($cron[$i][5]=="1-5")                                                    // weekday has been specified
                          { $tmp.="On Week days<br>"; }

                       if ($cron[$i][5]=="0,6")                                                    // weekday has been specified
                          { $tmp.="At Week ends<br>"; }

                       if (($cron[$i][1]!="*") && ($cron[$i][2]=="*"))                             // value for minutes but not for hours
                          { $tmp.="Hourly at ".$cron[$i][1]." minutes<br>".$cron[$i][7]; }         // Hourly recourance
                       else
                          {   if ($cron[$i][5]!="*")                                               // if a day is specified but no hours...
                                { $tmp.="at ".$cron[$i][2].":".$cron[$i][1]."<br>".$cron[$i][7]; } // add the task info
                             else                                                                  // day and hours not specified...
                                { $tmp.="Daily at ";                                               // every day
                                  $tmp.=$cron[$i][2].":".$cron[$i][1]."<br>".$cron[$i][7]; }       // add the task info
                          }
                         if ($cron[$i][3]!="*")                                                    // recurrence by date
                          { $tmp=$cron[$i][1]." ".$cron[$i][2]." ".$cron[$i][3]." ";               // worst case scenario just show raw data
                            $tmp.=$cron[$i][4]." ".$cron[$i][5]."<br>".$cron[$i][7]; }
               ?>
           <li><a href="#" onclick="taskedit(<?php echo $i; ?>)"><?php echo $tmp; ?></a>
           <!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
           <!-- Note added a spare entry at the end in case we want to create a new task                                    -->
           <input type="hidden" id="minutes_<?php echo $i; ?>" value="<?php echo $cron[$i][1]; ?>">
           <input type="hidden" id="hours_<?php echo $i; ?>" value="<?php echo $cron[$i][2]; ?>">
           <input type="hidden" id="dom_<?php echo $i; ?>" value="<?php echo $cron[$i][3]; ?>">
           <input type="hidden" id="month_<?php echo $i; ?>" value="<?php echo $cron[$i][4]; ?>">
           <input type="hidden" id="weekday_<?php echo $i; ?>" value="<?php echo $cron[$i][5]; ?>">
           <input type="hidden" id="tasknum_<?php echo $i; ?>" value="<?php echo $cron[$i][6]; ?>">
           <input type="hidden" id="taskname_<?php echo $i; ?>" value="<?php echo $cron[$i][7]; ?>">
           </li>
        <?php } ?>
        </ul></div>
</div>

<!-- Numeric keypad sub menu -->
<div id="numpad">
    <div class="toolbar"><h1 id="NumPadHead">tbd</h1>
           <a href="#" onclick="numpadret()" class="back">Back</a>
    </div>
    <div style="text-align: center;" onmousedown="return false;">
           <input id='numval' value="tbd" size="2" style="font-size: 45pt; background: transparent; text-align:center;"></br>
           <div style="margin-left:10px;">
                 <?php for ($count=0; $count<=9; $count++) { ?>
                          <div class="RoundButton"><a href="#" onclick="numpadupdate('<?php echo $count; ?>')" style="text-decoration: none"><?php echo $count; ?></a></div>
                 <?php } ?>
               <div class="RoundButton">
                    <a href="#" onclick="numpadupdate('*')" style="text-decoration: none">*</a>
               </div>
           </div>
    </div>
</div>

<!-- Task List Edit sub menu -->
<div id="taskedit">
       <div class="toolbar"><h1 id="TaskConfHead">tbd</h1>
              <a class="back2div slideright" onclick="TaskSend()" href="#">Back</a>
              <a class="button slide" onclick="TaskDel()" href="#">Delete</a>
       </div>
       <div class="scroll">
          <ul class="plastic">
               <li class="arrow"><a href="#" onclick="numpadinit('Select hours')">Hours<small class="small" id="edt_hours">tbd</small></a></li>
               <li class="arrow"><a href="#" onclick="numpadinit('Select minutes')">Minutes<small class="small" id="edt_minutes">tbd</small></a></li>
               <li class="arrow"><a href="#" onclick="numpadinit('Select date')">Day of Month<small class="small" id="edt_dom_str">tbd</small></a></li>
        <!-- Text values for the user to read                              -->
               <li class="arrow"><a href="#selectmonth">Month<small class="small" id="edt_month_str">tbd</small></a></li>
               <li class="arrow"><a href="#selectday">Weekday<small class="small" id="edt_wday_str">tbd</small></a></li>
               <li class="arrow"><a href="#selecttask">Task<small class="small" id="edt_task_str">tbd</small></a></li>
          </ul>
        <!-- Numeric values required to pass back to the alarm service     -->
        <input type="hidden" id="edt_month">
        <input type="hidden" id="edt_wday">
        <input type="hidden" id="edt_task">
        </div>
 </div>

<div id="selectday">
      <div class="toolbar"><h1>Select day</h1>
          <a class="back" href="#taskedit">Back</a>
      </div>
      <div class="scroll">
      <p>&nbsp</p>
         <ul class="rounded">
               <li><a href="#taskedit" id="dow_1" onclick="stringret('weekday','1','Monday')">Monday</a></li>
               <li><a href="#taskedit" id="dow_2" onclick="stringret('weekday','2','Tuesday')">Tuesday</a></li>
               <li><a href="#taskedit" id="dow_3" onclick="stringret('weekday','3','Wednesday')">Wednesday</a></li>
               <li><a href="#taskedit" id="dow_4" onclick="stringret('weekday','4','Thursday')">Thursday</a></li>
               <li><a href="#taskedit" id="dow_5" onclick="stringret('weekday','5','Friday')">Friday</a></li>
               <li><a href="#taskedit" id="dow_6" onclick="stringret('weekday','6','Saturday')">Saturday</a></li>
               <li><a href="#taskedit" id="dow_0" onclick="stringret('weekday','0','Sunday')">Sunday</a></li>
               <li><a href="#taskedit" id="dow_7" onclick="stringret('weekday','1-5','Week days')">Week days</a></li>
               <li><a href="#taskedit" id="dow_8" onclick="stringret('weekday','0,6','Week ends')">Week ends</a></li>
               <li><a href="#taskedit" id="dow_9" onclick="stringret('weekday','*','*')">Any day</a></li>
         </ul>
      </div>
</div>

<div id="selectmonth">
      <div class="toolbar"><h1>Select month</h1>
          <a class="back" href="#">Back</a>
      </div>
      <div class="scroll">
      <p>&nbsp</p>
         <ul class="rounded">
               <li><a href="#" id="mth_1" onclick="stringret('month','1','January');">January</a></li>
               <li><a href="#" id="mth_2" onclick="stringret('month','2','February');">February</a></li>
               <li><a href="#" id="mth_3" onclick="stringret('month','3','March');">March</a></li>
               <li><a href="#" id="mth_4" onclick="stringret('month','4','April');">April</a></li>
               <li><a href="#" id="mth_5" onclick="stringret('month','5','May');">May</a></li>
               <li><a href="#" id="mth_6" onclick="stringret('month','6','June');">June</a></li>
               <li><a href="#" id="mth_7" onclick="stringret('month','7','July');">July</a></li>
               <li><a href="#" id="mth_8" onclick="stringret('month','8','August');">August</a></li>
               <li><a href="#" id="mth_9" onclick="stringret('month','9','September');">September</a></li>
               <li><a href="#" id="mth_10" onclick="stringret('month','10','October');">October</a></li>
               <li><a href="#" id="mth_11" onclick="stringret('month','11','November');">November</a></li>
               <li><a href="#" id="mth_12" onclick="stringret('month','12','December');">December</a></li>
               <li><a href="#" id="mth_13" onclick="stringret('month','*','*');">any month</a></li>
         </ul>
      </div>
</div>

<div id="selecttask">
      <div class="toolbar"><h1>Select task</h1>
          <a class="back" href="#taskedit">Back</a>
      </div>
      <div class="scroll">
      <p>&nbsp</p>          
         <ul class="rounded">
           <?php for($i=0; $i<count($taskname); $i++) { ?>
           <li><a href="#" id="task_<?php echo $i; ?>" onclick="stringret('task','<?php echo $i; ?>','<?php echo $taskname[$i] ?>');"><?php echo $taskname[$i] ?></a></li>
           <?php } ?>
         </ul>
      </div>
</div>