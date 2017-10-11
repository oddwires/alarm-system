// First parameter  = Parm1 = Accessory name
// Second parameter = Parm2 = MAC address
// Third parameter  = Parm3 = Command string to switch accessory on
// Fourth parameter = Parm4 = Command string to switch accessory off
// Fifth parameter =  Parm5 = Address and Channel info

var Accessory = require('../').Accessory;
var Service = require('../').Service;
var Characteristic = require('../').Characteristic;
var uuid = require('../').uuid;
var fs = require('fs');

// here's a GENERIC outlet device that we'll expose to HomeKit
var GENERIC_OUTLET01 = {
  powerOn: false,
  setPowerOn: function(on) { 
    console.log("Turning the Parm1 outlet %s", on ? "on" : "off");
    if (on) {
            fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {       
               if(err) { return console.log(err); }
               console.log("Parm1 outlet on Success");
               }); 
            } else {
            fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {       
               if(err) { return console.log(err); }
               console.log("Parm1 outlet off Success");
               });
            }
  },
}

// Generate a consistent UUID for our light Accessory that will remain the same even when
// restarting our server. We use the `uuid.generate` helper function to create a deterministic
// UUID based on an arbitrary "namespace" and the word "light".
var outletUUID01 = uuid.generate('hap-nodejs:accessories:Parm1');

// This is the Accessory that we'll return to HAP-NodeJS that represents our GENERIC light.
var outlet01 = exports.accessory = new Accessory('Parm1', outletUUID01);

// Add properties for publishing (in case we're using Core.js and not BridgedCore.js)
outlet01.username = "Parm2";
outlet01.pincode = "031-45-154";
outlet01.displayName = "Parm1";

// set some basic properties (these values are arbitrary and setting them is optional)
outlet01
  .getService(Service.AccessoryInformation)
  .setCharacteristic(Characteristic.Manufacturer, "oddwires.co.uk")
  .setCharacteristic(Characteristic.Model, "Ver 1.0")
  .setCharacteristic(Characteristic.SerialNumber, "Parm5");

// Add the actual outlet Service and listen for change events from iOS.
// We can see the complete list of Services and Characteristics in `lib/gen/HomeKitTypes.js`
outlet01
  .addService(Service.Outlet, "Parm1") // services exposed to the user should have "names" like "GENERIC Light" for us
  .getCharacteristic(Characteristic.On)
  .on('set', function(value, callback) {
    GENERIC_OUTLET01.setPowerOn(value);
    callback(); // Our GENERIC Light is synchronous - this value has been successfully set
  });

// We want to intercept requests for our current power state so we can query the hardware itself instead of
// allowing HAP-NodeJS to return the cached Characteristic.value.
outlet01
  .getService(Service.Outlet)
  .getCharacteristic(Characteristic.On)
  .on('get', function(callback) {
    // this event is emitted when you ask Siri directly whether your outlet is on or not. you might query
    // the outlet hardware itself to find this out, then call the callback. But if you take longer than a
    // few seconds to respond, Siri will give up.
   var err = null; // in case there were any problems
    fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
        if(err) { return console.log(err); }
        var lines = data.split('\n');
        for(var i = 0; i < lines.length; i++){
            if ((lines[i].indexOf("rcon") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
            // falls through here when we have found the line for this device in the status file
            // console.log(lines[i]);
               var values = lines[i].split(':');
               // read the actual status of the accessory from the file
               console.log(values[5]);
               if (values[5].indexOf("on") !=-1) { 
                    console.log("Current status: on");
                    GENERIC_OUTLET01.powerOn = true;
                    callback(err, true);
                } else {
                    console.log("Current status: off");
                    GENERIC_OUTLET01.powerOn = false;
                    callback(err, false);
                }
            }
        }
    });
 });