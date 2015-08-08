<?php
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

// Takes the retval string from the page and writes it to a file on the server
if (isset($_POST['retval']))
{     exec("rm -f /var/www/data/status.txt");                 // remove old data
      $tmp = $_COOKIE['username'].":".$_SERVER['REMOTE_ADDR'].":".$_POST['retval'];
      exec("echo $tmp >>/var/www/data/input.txt");            // Pass data to the BASH shell script
}
?>