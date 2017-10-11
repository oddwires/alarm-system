<?php
$time = 1;                                                              // time in seconds to wait for the log file to be updated
$found = false;
$count=0;
$currentevent="";
$lastevent="";
$repeateventcount = 0;
$date = str_replace('/', '-', $_GET["date"]);
$filename = trim('/var/www/logs/'.$date.'.csv');                // watch those pesky white space characters !
//error_log("log file: ".$filename);                            // DEBUG
for($i=0; $i<$time; $i++)
    { if (file_exists($filename)) {
        // Falls through here if we have found it - so read the file ....
        $found = true;
        $file = fopen($filename,'r');
        if (!$file) {                                                    // Sometimes the stoopid file still won't open !
            $found=false;                                                // ( e.g. permission issues or freaky timing issues )
            break; }                                                     // so trap any remaining errors
        while (!feof($file)) {
            $data=fgets($file);
            if (strlen($data)>1) {                                       // last line read is empty, only proceed if we have data..
                $logs[$count]=explode(",", $data);
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][4];   // 'You're only supposed to blow the bloody doors off !'
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][5];   // ( stick the end of the string back together )
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][6];
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][7];
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][8];
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][9];
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][10];
                $logs[$count][3]=$logs[$count][3].' '.$logs[$count][11];

                $lastevent=$currentevent;
                $currentevent=str_replace(" open","",$logs[$count][3]);   // strip out open and closed from string
                $currentevent=str_replace(" closed","",$currentevent);
                if ($lastevent == $currentevent) {
                    $repeateventcount++;
                    if ($repeateventcount > 1) {
                        $count--;
                        $logs[$count][0]=$logs[$count+1][0];
                        $logs[$count][3]=$repeateventcount." more events<br>".$currentevent; }
                    else {
                        $logs[$count][3]="Repeat event<br>".$currentevent; }
                }
                else {
                    $repeateventcount = 0; }
            }
            $count++;
          }
          $count--;                                                      // fiddle factor
          $count--;
      fclose ($file);
      break;          // at this point we have successfully read the data, so break out of 'for' loop.
      }
    else {
        // Falls through here if no file found after specified period
        sleep(1);  // wait one second before trying again
      }
  }
//if ($found == false) { header('Location: /fault.php#fault'); } // Hello Houston...
if ($found == false) { header('Location: /LogFileNotFound.php#notfound'); } // Hello Houston...
?>

<!-- Scroller element of the logs screen                               -->
<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <a href="#sidePanel" data-icon="bars">Menu</a>
    <h1>Log: <?php echo $date ?></h1>
</div>
<p data-role="screenSpacer"></p>
<ul data-role="listview" style="margin-top: 42px;">
     <?php for ($row=0; $row<=$count; $row++) {
           $tmp = '<li style="font-weight: bold;">'.$logs[$row][0].'<span class="ui-li-count" style="top: 30%">'.$logs[$row][1].'@'.$logs[$row][2].'</span><br>'.$logs[$row][3].'</li>';
           echo $tmp; } ?>
</ul>
<!-- put some blank space at the end of the list. Gives Power editor some room to work in for the last items in the list view.   -->
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>