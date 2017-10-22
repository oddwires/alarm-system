<?php
session_start(); 

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$_SESSION["logIn"] = false;
$loginisvalid = FALSE;
if(isset($_POST['username'])) { $username=$_POST['username']; }
$password_entered=$_POST['password'];
$responsestring;
$otac;
// Create  random 128 character One Time Access Code
    $length = 40;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

//error_log(print_r($_POST, true));                       // Debug - DO NOT LEAVE ACTIVE IN LIVE CODE
//error_log(print_r($_SESSION, true));
//error_log('UserName: '.$_POST['username']);
//error_log('Password: '.$_POST['password']);
//error_log('Remember me: '.$_POST['rememberme']);

    $file1 = fopen("/var/www/user.txt",'r');
    $count = 0;
    while (!feof($file1))
    {   $data = fgets($file1);
        if ($data<>"") {                          // skips any blank lines and the email creds
            $tmp=(explode(':',$data));
            $saved_hash=$tmp[1];
            $otac=trim($tmp[3]);                                      // current One Time Access Code without whitespace

            if(($_POST['username'] == $tmp[0]) and (crypt($password_entered, $saved_hash) == $saved_hash)) {
                // User name  and password are correct...
                error_log('Valid login: '.$tmp[0]);
                $_SESSION["logIn"] = true;
                $loginisvalid = true;
                    if($_POST['rememberme'] == 'true'){
                        setcookie('otac',$randomString,time() + 86400,'/');     // write the cookies
                        setcookie('username', $tmp[0],time() + 86400, '/');                 // 86400 = 1 day
                    } else {
                        setcookie('otac', '', time() - 3600);
                        setcookie('username', '', time() - 3600);               // delete the cookies
                    }
                $responsestring = $username.":".$_SERVER['REMOTE_ADDR'].":logon:".$count.":".$randomString;
                error_log(print_r($_SESSION, true));
                break;                                                           // stop the search
            }

                error_log('Checking OTAC for: '.$tmp[0]);
                if($_COOKIE['otac'] == $otac){
                    $username=trim($tmp[0]);
                    if($_POST['rememberme'] == 'true'){
                        setcookie('otac',$randomString,time() + 86400,'/');     // write the cookies
                        setcookie('username', $tmp[0],time() + 86400, '/');     // 86400 = 1 day
                        $_SESSION["logIn"] = true;
                        $loginisvalid = true;
                  } else {
                        setcookie('otac', '', time() - 3600);
                        setcookie('username', '', time() - 3600);               // delete the cookies
                        $_SESSION["logIn"] = false;                             // auto login disabled, so cancel it

                    }
                    $responsestring = $username.":".$_SERVER['REMOTE_ADDR'].":logon:".$count.":".$randomString;
                    error_log('Found OTAC match for: '.$tmp[0]);
                    break;
            }
        }
        $count++;
    }
    fclose($file1);

if($loginisvalid){
    $_SESSION["badcount"] = 0;
    include 'PowerPageAjaxCall.php';                                // I cannot be *rsed typing all this in again

}else{                                                              // failed login attempt
    error_log('failed logon');
    setcookie('username', '', time() - 3600);                       // Clear any cookies
    setcookie('otac', '', time() - 3600);
    if (!isset($_SESSION['badcount'])) { $_SESSION['badcount'] = 1; }
    else { ++$_SESSION['badcount']; }
    header('Location: /failed.php');
    $responsestring = $tmp[0].":".$_SERVER['REMOTE_ADDR'].":failed logon";
}
exec("echo $responsestring >>/var/www/data/input.txt");
?>