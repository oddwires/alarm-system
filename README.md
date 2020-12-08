<h2>A combined Home automation and Security system with a Homekit Bridge.</h2>
<ul>
<li><a href="https://github.com/arendst/Tasmota" target="_blank">Tasmota devices</a> - no need for the custom hardware. Just configure the Tasmota IP address, and off you go !</li>
<li>Web App interface - control devices from anywhere using your mobile phone.</li>
 <li>Homekit bridge - voice control through Siri.</li>
 <li>433MHz transmitter - cost effective solution to control <a href="https://www.avsl.com/brands/mercury/product/mains-power/remote-switches" target="_blank">Power switches</a> and <a href="https://www.uk-automation.co.uk/smartwares-wireless-thermostatic-radiator-valve-including-remote" target="_blank">Radiator valves</a> anywhere around the house. *</li>
 <li>12 volt alarm system interface - connect to industry standard security sensors and sirens. *</li>
 <li>Remote control of Heating/Hot water through web app GUI and Homekit. *</li>
 </ul>
<p>( * = custom hardware required )</p>
<p>There are some pictures, and interactive sample screens on my web page <a href="http://oddwires.co.uk/alarm/software-ver-3/" target="_blank">here</a>.</p>

<b>Key components:-</b>
<ul>
 <li>Build last tested: 8th December 2020.</li>
 <li>Raspberry Pi 2 with 1GB memory or Raspberry Pi 3 with 1GB memory</li>
 <li>Raspberry Pi OS Lite - Release date: December 2nd 2020</li>
 <li>iPhone SE</li>
 <li>IOS 14.2</li>
</ul>
 
<b>Installation:-</b>

Full installation details are in the <a href="https://github.com/oddwires/alarm-system/wiki/1.2---Installing-the-Alarm-System" target="_blank">Wiki</a>

<b>Latest features:-</b>
<ul>
 <li>Tasmota devices.</li>
 <li>Dynamic backgrounds selectable through the Web App.</li>
 <li>Installer updated to work with latest HAP-NodeJS (TypeScript version).</li>
 <li>Exported Homekit accessory files updated from JavaScript to TypeScript.</li>
 <li>GUI support for central heating system (sorry - only useful to me as more dedicated hardware is required).</li>
 <li>Certificate Authority and full keychain - Root CA certificate created during the install which can be installed to the iPhone providing authentication and encryption and allows the web app to run in full screen.</li>
 </ul>

<b>tbd:-</b>
<ul>
<li>Publish sketch for ESP8266 temperature sensors</li>
<li>readvars.php - test for valid session</li>
</ul>
