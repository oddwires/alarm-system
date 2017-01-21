<?php
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$loginisvalid=FALSE;
$username=$_POST['username'];
$password_entered=$_POST['password'];
$logstring="";
$responsestring;
// Create  random 128 character One Time Access Code
$length = 40;
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$charactersLength = strlen($characters);
$randomString = '';
for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
}

//error_log(print_r($_POST, true));                       // Debug

$file1 = fopen("/var/www/user.txt",'r');
$count = 0;
while (!feof($file1))
{     $data = fgets($file1);
      if ($data<>"") {                                              // skips any blank lines
          $tmp=(explode(':',$data));
	      $saved_hash=$tmp[1];
          $otac=trim($tmp[3]);                                   // current One Time Access Code without whitespace

	      if(($_POST['username'] == $tmp[0]) and (crypt($password_entered, $saved_hash) == $saved_hash)) {
               // User name  and password are correct...
               setcookie('loggedin', 'true',time()+1*24*60*60, '/',FALSE,TRUE);   // wer-hay ! - we're in !!
               setcookie('username', $tmp[0],time()+1*24*60*60, '/',FALSE,TRUE);
			   setcookie('otac',$randomString,time()+1*24*60*60,'/',FALSE,True);
               $loginisvalid=TRUE;
               $responsestring = $username.":".$_SERVER['REMOTE_ADDR'].":logon:".$count.":".$randomString;
               break;                                            // stop the search
            }

           if(isset($_POST['rememberme'])){
//                error_log(print_r($otac, true));
               if($_COOKIE['otac'] == $otac){
                  $loginisvalid=TRUE;
                  $username=trim($tmp[0]);
                  setcookie('loggedin', 'true',time()+1*24*60*60, '/',FALSE,TRUE);   // wer-hay ! - we're in !!
                  setcookie('username', $tmp[0],time()+1*24*60*60, '/',FALSE,TRUE);
                  setcookie('otac',$randomString,time()+1*24*60*60,'/',FALSE,TRUE);
                  $responsestring = $username.":".$_SERVER['REMOTE_ADDR'].":logon:".$count.":".$randomString;
                  break;                                            // stop the search
               }
           }
       }
       $count++;
}
fclose($file1);

if($loginisvalid){
	  setcookie("badcount", "", time() - 3600);                   // reset counter
      header('Location: /auto.php#auto');                         // go to next screen on the existing subnet

}else{                                                            // failed login attempt
      setcookie("username", "", time() - 3600);
      setcookie("loggedin", "", time() - 3600);
      setcookie("otac", "", time() - 3600);
	  if (!isset($_COOKIE['badcount'])) { $counter = 1; }
	  else { $counter = ++$_COOKIE['badcount']; }
	  setcookie("badcount", $counter,time() + 600,'/',FALSE,TRUE);  // 10 minute cookie life
      header('Location: /failed.php');
      $responsestring = $tmp[0].":".$_SERVER['REMOTE_ADDR'].":failed logon";
}
exec("echo $responsestring >>/var/www/data/input.txt");
 ?>