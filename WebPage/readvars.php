<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['login'])){ //if login in session is not set
//    header("Location: /index.php#pageone");
}

if (isset($_POST['retval']))
{   exec("rm -f /var/www/data/status.txt");                          // remove old data
    $tmp = $_COOKIE['username'].":".$_SERVER['REMOTE_ADDR'].":";
    if (substr($_POST['retval'],0,8) != "user pwd") { 
        // most of the time we just pass the data to the BASH shell script as clear text...    
        $tmp = $tmp.$_POST['retval'];
        } else {
        // but if we are sending a password...
        $input = (explode(':',$_POST['retval']));                    // split the data string, to get just the password
        // So we aren't going to pass the password, we are going to pass a hash of the password.
        // This means passwords aren't stored on the system, so remain safe even if the system
        // has been compromised.

        // Original PHP code by Chirp Internet: www.chirp.com.au
        // Please acknowledge use of this code by including this header.

        // Sorry Chirp, but I've had to really mangle your code to make it work with mine...
        $rounds = 7;
        // Create a random 22 character salt...
        $salt = "";
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) { $salt .= $salt_chars[array_rand($salt_chars)]; }
        // Create the hash using BlowFish encryption and the random salt...
        $hash = crypt($input[4], sprintf('$2y$%02d$', $rounds).$salt);
        $hash = str_replace("\$","\\$",$hash);                         // need to escape '$' to prevent BASH from interpreting it.
        // re-assemble the string using the hashed password
        $tmp = $tmp.$input[0].":".$input[1].":".$input[2].":".$input[3].":".$hash ;
        }
    exec("echo $tmp >>/var/www/data/input.txt");                      // Pass data to the BASH shell script
}
     
// Global variables
if (isset($_COOKIE['username'])) { $username=$_COOKIE["username"]; };
$user_ip=$_SERVER['REMOTE_ADDR'];
$RCnum=0; $ZnNum=0; $ZnTrig=0; $CRnum=0; $USRNum=0; $RDTRnum=0;
$time = 4;                                                              // time in seconds to wait for the status file to be created.
$found = false; $filename = '/var/www/data/status.txt';

// Define first four tasks. Additional tasks for on and off are added to this array later for each RC channel defined.
$taskname=array("Day mode","Night mode","Standby mode","Check IP", "Heat and water", "Water only", "Heat off" );

// Start reading the data file....
for($i=0; $i<$time; $i++)
  { if (file_exists($filename))
      { // Falls through here if we have found it - so read the file ....
        $found = true;
        $file = fopen($filename,'r');
        if (!$file) {                                                        // Sometimes the stoopid file still won't open !
            $found=false;                                                    // ( e.g. permission issues or freaky timing issues )
            break; }                                                         // so trap any remaining errors
        while (!feof($file))
          { $data=fgets($file);
              if (substr($data,0,13) == "alarm:status:") { $status[0]=substr($data, 13, -1); }
              if (substr($data,0,11) == "alarm:mode:") { $status[1]=substr($data, 11, -1); }
              if (substr($data,0,15) == "alarm:duration:") { $duration=substr($data, 15, -1); }
              if (substr($data,0,15) == "setup:location:") { $location=substr($data, 15, -1); }
              if (substr($data,0,15) == "setup:routerIP:") { $routerIP=substr($data, 15, -1); }
              if (substr($data,0,14) == "setup:localIP:") { $localIP=substr($data, 14, -1); }
              if (substr($data,0,15) == "setup:hardware:") { $hardware=substr($data, 15, -1); }
              if (substr($data,0,13) == "setup:memory:") { $memory=substr($data, 13, -1); }
              if (substr($data,0,16) == "setup:disktotal:") { $disktotal=substr($data, 16, -1); }
              if (substr($data,0,15) == "setup:diskused:") { $diskused=substr($data, 15, -1); }
              if (substr($data,0,12) == "setup:disk%:") { $diskperc=substr($data, 12, -1); }
              if (substr($data,0,13) == "setup:uptime:") { $uptime=substr($data, 13, -1); }
              if (substr($data,0,13) == "email:server:") { $EMAIL_server=substr($data, 13, -1); }
              if (substr($data,0,11) == "email:port:") { $EMAIL_port=substr($data, 11, -1); }
              if (substr($data,0,13) == "email:sender:") { $EMAIL_account=substr($data, 13, -1); }
              if (substr($data,0,10) == "heat:mode:") { $HEAT_mode=substr($data, 10, -1); }
              if (substr($data,0,5) == "zcon:") {
                   $zcon[$ZnNum]=(explode(':',$data));
                   if ($zcon[$ZnNum][7] == "true") { $ZnTrig++; }            // number of triggered zones
                   $ZnNum++; 
              }
              if (substr($data,0,5) == "rcon:") {
                   $rcon[$RCnum]=(explode(':',$data));
                   $taskname[]=$rcon[$RCnum][2];                             // add additional task for this RC channel
                   $RCnum++; }
              if (substr($data,0,5) == "rdtr:") {
                   $tmp=strlen($data)-1;
                   $data=substr($data,0,$tmp);                               // remove white space at end of line
                   $rdtr[$RDTRnum]=(explode(':',$data));
                   $taskname[]=$rdtr[$RDTRnum][2]." radiator";               // add additional task for this radiator
                   $RDTRnum++; }
              if (substr($data,0,5) == "cron:") {
                   $tmp=strlen($data)-1;
                   $data=substr($data,0,$tmp);                               // remove white space at end of line
                   $cron[$CRnum]=(explode(':',$data));
                   $CRnum++; }
              if (substr($data,0,5) == "user:") {
                   $user[$USRNum]=(explode(':',$data));
                   $USRNum++; }
          }
      fclose ($file);
      break;                                                                 // at this point we have read all the data, so break out of 'for' loop.
      }
    else
      {                                                                      // Falls through here if no file found after specified period
        sleep(1);                                                            // wait one second before trying again
      }
  }
if ($found == false) { header('Location: /fault.php#fault'); }               // Hello Euston...we have a problem !
?>