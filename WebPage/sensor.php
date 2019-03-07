<?php
    if (strpos($_SERVER['REMOTE_ADDR'], '192.168.1') === false) {
        // Block access from external IP's
        header('HTTP/1.0 403 Forbidden');
        echo 'Access to this web page is forbidden!';
        exit;
    }
?>

<!DOCTYPE html>
<html>
<body>
<?php
// Routine handles variable number of sensor readings, so single or double sensor devices can use the same script.
// This is achieved by posting sensor data as arrays rather than discrete values.

    $Debug = false;                                                       // used to dump additional data for debugging
    $Timestamp = time() * 1000;                                           // Unix timestamp converted to Javascript
    $DateStamp = date('d-m-y');
    $Path = '/var/www/logs/';
    $FlotDataFile = "flot.txt";                                           // historic data in JSON format.
    $HKDataFile = "accessory.txt";                                        // latest values allowing fast access by HK accessories.
    $NewHKdata = array();                                                 // used to collate new values contained in post

    if($Debug) { 
        if (strpos($_SERVER['REMOTE_ADDR'], '192.168.1') !== false) {
            echo 'local IP address. Access permitted.<br>';
        }
        echo "Remote IP...<br>";
        var_dump($_SERVER['REMOTE_ADDR']);
        echo "<br>POST data...<br>";
        var_dump(print_r($_POST, true));
    }

    if (file_exists($Path.$FlotDataFile)) {
        // if the file exists, read the contents..
        $myfile = fopen($Path.$FlotDataFile, "r+");
        $serialized = fread($myfile,filesize($Path.$FlotDataFile));
        fclose($myfile);
    } else {
        // if the file doesn't exist, initialise the variable cleanly...
        $serialised = "";                                                  
    }
    $jsonOBJ = json_decode($serialized,true);                             // convert to associative array

    if ($Debug) {
        echo "<br>=============================<br>";
        var_dump($jsonOBJ);
    }
   
   // Loop through the input data array
   $count = 0;
   foreach ($_POST['sensor'] as $thisSensor) {
        // Do something with each valid entry ...
        if ($thisSensor != "") {
            $thisValue = $_POST['temperature'][$count];
            $jsonOBJ[$thisSensor][$Timestamp] = $thisValue;               // add new data to Flot data object
            $NewHKdata[$thisSensor] = $thisValue;                         // add data to KomeKit data array
        }
        $count ++;
    }
    if ($Debug) {
        echo "<br>=============================<br>";
        echo "<br>Edited Flot data array...<br>";
        var_dump($jsonOBJ);

        echo "<br>=============================<br>";
        echo "<br>New HK data array...<br>";
        var_dump($NewHKdata);
    }

    // Note: File will only fail to open if the folder permissions are wrong.
    $myfile = fopen($Path.$FlotDataFile, "w+") or die("Unable to open file: $Path$FlotDataFile");
    $serialized = json_encode($jsonOBJ);
    fwrite($myfile, $serialized);                                         // save it
    fclose($myfile);                                                      // close it

   // Record data to second file for HomeKit accessories...
   if(file_exists($Path.$HKDataFile)){
        $serialized = file_get_contents($Path.$HKDataFile);
        $temperatures = json_decode($serialized,true);                    // convert to associative array
    } else {
          $temperatures="";
    }
    $temperatures = array_replace($temperatures,$NewHKdata);              // merge new data
    $serialized = json_encode($temperatures);                             // convert to JSON format
    file_put_contents($Path.$HKDataFile, $serialized);

    if ($Debug) {
        echo "<br>=============================<br>";
        echo "<br>Data read from HK file...<br>";
        var_dump($temperatures);
        echo "<br>=============================<br>";
        echo "<br>Data to be written to HK file...<br>";
        var_dump($temperatures);
        echo "<br>=============================<br><br>";
        echo "JSON encoded data...<br>";
        var_dump($serialized);
        echo "<br>=============================<br><br>";
    }
?>

<form action="sensor.php" method="post">
        <input maxlength="25" name="sensor[]" size="20" type="text" value="Office" /><br>
        <input maxlength="5" name="temperature[]" size="20" type="text" value="21" /><br>
        <input maxlength="25" name="sensor[]" size="20" type="text" value="Kitchen" /><br>
        <input maxlength="5" name="temperature[]" size="20" type="text" value="25" /><br>
    </p> 
   <input type="submit" name="submit" value="Submit" />
</form>
</body>
</html> 


