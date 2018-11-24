<html>
<body>
    <script type="text/javascript">
    <?php
    // Code to scan for any number of temperature data files, and merge them into a single
    // JSON file that can be used with FLOT.
        $date = date('d\-m\-y');
        $filter = "/var/www/logs/Temperature-*".$date."*.csv";
        $list = glob($filter);
        $JSON_string = "{\n";
        foreach($list as $fileName) {                                       // loop through all files found with today's date...
            $roomName = explode("-",$fileName);                             // create array
            $roomName = $roomName[1];                                       // get just the room name
            $JSON_string .= "    \"" . $roomName . "\": {\n";
            $JSON_string .= "        label: \"" . $roomName . "\",\n";
            $JSON_string .= "        data: ";
            $content = file_get_contents($fileName);                         // Now read the raw ESP8266 data file
            $content = str_replace("],[", ",", $content)."]";                // NEED TO CHANGE FORMAT IN ESP8266 CODE !
            $content = "[".str_replace("\n", ", ", $content)."]";
            $content = str_replace(", ]]", "]", $content);
            $JSON_string .= $content;
            $JSON_string .= "\n    },\n";
        }
        $JSON_string .= "}\n";
    // $file = '/var/www/logs/outfile.txt';                                  // DEBUG - write JSON object to text file
    // file_put_contents($file, $JSON_string);
    ?>
    var datasets = <?php echo $JSON_string; ?>
    </script>

    <div id="content">
        <div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="true">
            <a href="#sidePanel" class="SquareButton green">
                <span class="gloss"></span>
                <span class="SquareButtonText">Menu</span>
            </a>
            <h1 style="font-size: 22px; padding: .35em 0;"><?php echo $RCnum; ?>Temperatures for: <?php echo $date ?></h1>
        </div>

        <div id="chart" style="width: 100%; height: 300px; float:left; background-color: #aac39de8; margin-top: 50px;"></div>
        <p id="choices" style="float:right; width:20%;"><br></p>
        </div>

        <table><tr>
            <td><button id="ninetynine">1999 by month</button></td>
            <td><button id="whole">Whole period</button></td></tr>
        </table>
  </div>

</body>
</html>
