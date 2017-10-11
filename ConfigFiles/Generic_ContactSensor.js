// First parameter  = Parm1 = Accessory name
// Second parameter = Parm2 = MAC address

var Accessory = require('../').Accessory;
var Service = require('../').Service;
var Characteristic = require('../').Characteristic;
var uuid = require('../').uuid;
var fs = require('fs');

var lastState=0;
// here's a door device that we'll expose to HomeKit
var DOOR = {
    sensor: 0,
    outputLogs: false,                      //output logs
    read: function(){
    var err = null;                         // in case there were any problems
    fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
        if(err) { return console.log(err); }
        var lines = data.split('\n');
        for(var i = 0; i < lines.length; i++){
            if ((lines[i].indexOf("zcon") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
                var svalues = lines[i].split(':');
                if (svalues[8].toString().trim() === '0') {
                    if(this.outputLogs) console.log('Parm1 open')
                    DOOR.open = true;
                } else {
                    if(this.outputLogs) console.log('Parm1 closed')
                    DOOR.open = false;
                }
            }
        }
    });
  },
  identify: function() {
     console.log("Identify the Parm1");
  }
}

// Generate a consistent UUID for our Lock Accessory that will remain the same even when
// restarting our server. We use the `uuid.generate` helper function to create a deterministic
// UUID based on an arbitrary "namespace" and the word "door".
var doorUUID = uuid.generate('hap-nodejs:accessories:Parm1');

// This is the Accessory that we'll return to HAP-NodeJS that represents our door
var door = exports.accessory = new Accessory('Parm1', doorUUID);

// Add properties for publishing (in case we're using Core.js and not BridgedCore.js)
door.username = "Parm2";
door.pincode = "031-45-154";

// set some basic properties (these values are arbitrary and setting them is optional)
door
  .getService(Service.AccessoryInformation)
  .setCharacteristic(Characteristic.Manufacturer, "oddwires.co.uk")
  .setCharacteristic(Characteristic.Model, "v1.0")
  .setCharacteristic(Characteristic.SerialNumber, "A12S345KGB");

// listen for the "identify" event for this Accessory
door.on('identify', function(paired, callback) {
  DOOR.identify();
  callback(); // success
});

door
   .addService(Service.ContactSensor,"Parm1")
   .getCharacteristic(Characteristic.ContactSensorState)
   .on('get', function(callback) {
    DOOR.read();
    // return our current value
    callback(null, DOOR.open);
});

setInterval(function() {  
  DOOR  .read()
  
  // update the characteristic value so interested iOS devices can get notified
   door
    .getService(Service.ContactSensor)
    .getCharacteristic(Characteristic.ContactSensorState, DOOR.open)  
    .setValue(DOOR.open);
}, 1000);