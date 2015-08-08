<?php
include("readvars.php");                         // common code to read variables from file STATUS.TXT 
?>

<!-- Scroller element of the Task screen                               -->
<!-- kept separate to ensure AJAX updates load without breaking the scroller -->

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
