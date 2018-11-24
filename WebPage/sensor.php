<!DOCTYPE html>
<html>
<body>
<?php
   error_log(print_r($_POST, true));                             // DEBUG
   $SensorName = $_POST['sensor'];
   $SensorReading = $_POST['temperature'];
// $Path = '/var/www/html/logs/';
   $Path = '/var/www/logs/';

// Record the data to log file for graphs...
   $Timestamp = time() * 1000;                                   // Unix timestamp converted to Javascript
   $DateStamp = date('d-m-y');
   $FileName = 'Temperature-'.$SensorName.'-'.$DateStamp.'.csv';
   $data = "[".$Timestamp."],[".$SensorReading."]";
   $myfile = file_put_contents($Path.$FileName, $data.PHP_EOL , FILE_APPEND | LOCK_EX);

// Record data to second file for HomeKit accessories...
   $newTemperature = array($SensorName=>$SensorReading);
   $FileName = $Path."serialized.txt";
   if(file_exists($FileName)){
        $serialized = file_get_contents($FileName);               // read existing data
//      error_log($serialized);                                   // debug
        $temperatures = json_decode($serialized,true);            // convert to associative array
//      var_dump($temperatures);                                  // debug
    } else {
        $temperatures = array();                                  // initialise empty array
    }
   $temperatures = array_replace($temperatures,$newTemperature);  // merge new data
   $serialized = json_encode($temperatures);                      // convert to JSON format
   error_log($FileName);
   $myfile2 = file_put_contents($FileName, $serialized);          // save it
?>

<form action="sensor.php" method="post">
   <p>Temperature: <input type="text" name="temperature" /><br>
      Sensor: <input type="text" name="sensor" value="kitchen" /></p>
   <input type="submit" name="submit" value="Submit" />
</form>
</body>
</html> 


