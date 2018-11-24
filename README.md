<h2>Alarm-System</h2>

Key components:-
<ul>
 <li>Build last tested: 24/11/2018.</li>
 <li>Raspberry Pi 2 with 1GB memory or Raspberry Pi 3 with 1GB memory</li>
 <li>RASPBIAN STRETCH LITE - Version: November 2018 - Release date: 2018-11-13</li>
 <li>iPhone SE</li>
 <li>IOS 11.3.1</li>
</ul>
 
<b>Installation:-</b>

<ul>
<li>sudo raspi-config</li>
 <ul><li>Localisation Options | Change Timezone</li>
     <li>Localisation Options | Change Locale ( default is en_GB, you may need to change it )</li>
     <li>Interfacing options | SSH enable</li>
     <li>Hostname</li>
     <li>Finish and Reboot</li>
 </ul>
<li>sudo apt-get update</li>
<li>sudo apt-get -y install git</li>
<li>mkdir Downloads</li>
<li>cd Downloads</li>
<li>git clone git://github.com/oddwires/WorkInProgress.git</li>
<li>cd WorkInProgress</li>
<li>chmod +x install.sh</li>
<li>sudo ./install.sh</li>
</ul>

<b>Added features:-</b>
<ul>
 <li>Silent PostFix install - no more anoying dialog</li>
 <li>Certificate Authority and full keychain - Root CA certificate created during the install which can be installed to the iPhone providing authentication as well as encryption.</li>
 <li>Re-worked GUI to utilise the iPhone GPU to provide a dynamic, fluid background. This eats up your batteries, but I luv it !</li>
 <li>Support for ESP8266 based temperature sensors</li>
</ul>

<b>TBD:-</b>
<ul>
<li>Publish sketch for ESP8266 temperature sensors</li>
<li>Graphs: trap no data error</li>
<li>HTML emails</li>
<li>readvars.php - test for valid session</li>
</ul>
