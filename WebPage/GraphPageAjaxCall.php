<html>
<body>
    <script type="text/javascript">
    <?php
/* Code to scan for any number of sensor data files, and merge them into a single JSON file that can be used with FLOT.

   FLOT requires data to be a JSON object with the data element being an array. Example:-
    $JSON_string =
    '{
        "USA":{
            "label":"USA",
            "data":[[1988, 483994], [1989, 479060], [1990, 457648], [1991, 401949], [1992, 424705], [1993, 402375], [1994, 377867], [1995, 357382], [1996, 337946], [1997, 336185], [1998, 328611], [1999, 329421], [2000, 342172], [2001, 344932], [2002, 387303], [2003, 440813], [2004, 480451], [2005, 504638], [2006, 528692]]
            },
        "Russia":{
             "label":"Russia",
            "data":[[1988, 218000], [1989, 203000], [1990, 171000], [1992, 42500], [1993, 37600], [1994, 36600], [1995, 21700], [1996, 19200], [1997, 21300], [1998, 13600], [1999, 14000], [2000, 19100], [2001, 21300], [2002, 23600], [2003, 25100], [2004, 26100], [2005, 31100], [2006, 34700]]
            }
    }'
*/
    $Path = '/var/data/app-sensor/';
    $date = date('d\-m\-y');
    $filter = $Path."*.csv";
    $list = glob($filter);

    $JSON_string = "{\r\n";
    foreach($list as $fileName) {                                         // loop through all files found in data directory...
        $label = str_replace($Path, "", $fileName);                       // strip out the path
        $label = str_replace(".csv", "", $label);                         // strip off file extension

        $JSON_string .= '     "'.$label.'"'.":{\r\n";
        $JSON_string .= '         "label":"'.$label.'"'.",\r\n";
        $JSON_string .= '         "data":[';

        $content = file_get_contents($fileName);                          // Now read the raw ESP8266 data file
        $content = str_replace("\n", "", $content);                       // remove all the new lines
        $content = str_replace("],", "], ", $content);                    // improve readability
        $content = substr($content, 0, -2);                               // remove the last comma
        $content .= "]";                                                  // terminate the data section

        $JSON_string .= $content;
        $JSON_string .= "\r\n     },\n";
    }
    $JSON_string = substr($JSON_string, 0, -2);                           // remove the last comma
    $JSON_string .= "\r\n}\n";
    ?>
    var datasets = <?php echo $JSON_string; ?>
    </script>

    <div id="content">
        <div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="true">
            <a href="#sidePanel" class="SquareButton green">
                <span class="gloss"></span>
                <span class="SquareButtonText">Menu</span>
            </a>
            <h1 style="font-size: 22px; padding: .35em 0;">Temperatures for: <?php echo $date ?></h1>
        </div>

        <div id="chart" style="width: 100%; height: 300px; float:left; background-color: #aac39de8; margin-top: 50px;"></div>
        <p id="choices" style="float:right; width:20%;"><br></p>
        </div>

        <a class="RoundButton red" id="ViewButton1" style="left: 10%; top: 60%; cursor: pointer" ><span class="RoundButtonText2">Week</span></a>
        <a class="RoundButton red" id="ViewButton2" style="left: 35%; top: 60%; cursor: pointer" ><span class="RoundButtonText2">Day</span></a>

        <table><tr>
            <td><button id="ninetynine">1999 by month</button></td>
            <td><button id="whole">Whole period</button></td></tr>
        </table>
  </div>

</body>
</html>
