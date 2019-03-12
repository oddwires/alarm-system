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
    $Path = '/var/data/app-sensor/';
    $HKDataFile = "current_values.txt";                                   // just the latest values - allows fast access by HK accessories.
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

    if($Debug) {
        echo "<br>Timestamp: ".$Timestamp."<br>";
    }

// Loop through the input data array
   $count = 0;
   foreach ($_POST['sensor'] as $thisSensor) {
        // Do something with each valid entry ...
        if ($thisSensor != "") {
            $thisValue = $_POST['temperature'][$count];

            // Record the data to log file for graphs...
            $FileName = $thisSensor.'.csv';
            $data = "[".$Timestamp.", ".$thisValue."],";
            $myfile = file_put_contents($Path.$FileName, $data.PHP_EOL , FILE_APPEND | LOCK_EX);

            // Record the data to log file for HomeKit...
            $NewHKdata[$thisSensor] = $thisValue;                         // add data to KomeKit data array
        }
        $count ++;
    }

   // Write data to second file for HomeKit accessories...
   if(file_exists($Path.$HKDataFile)){
        $serialized = file_get_contents($Path.$HKDataFile);
        $temperatures = json_decode($serialized,true);                    // convert to associative array
    } else {
          $temperatures = array();
    }

    if ($Debug) {
        echo "<br>=============================<br>";
        echo "<br>Data read from HK file...<br>";
        var_dump($temperatures);
        echo "<br>=============================<br>";
        echo "<br>Data to be merged into existing HK file...<br>";
        var_dump($NewHKdata);
        echo "<br>=============================<br><br>";
    }

    $temperatures = array_replace($temperatures,$NewHKdata);              // merge new data

    if ($Debug) {
        echo "<br>=============================<br>";
        echo "<br>Data following array merge...<br>";
        var_dump($temperatures);
        echo "<br>=============================<br><br>";
    }

    $serialized = json_encode($temperatures);                             // convert to JSON format
    file_put_contents($Path.$HKDataFile, $serialized);

    if ($Debug) {
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


