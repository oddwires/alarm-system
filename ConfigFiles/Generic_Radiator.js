// First parameter  = Parm1 = Radiator name
// Second parameter = Parm2 = MAC address
// Third parameter  = Parm3 = Command string to switch radiator on
// Fourth parameter = Parm4 = Command string to switch radiator off
// Fifth parameter =  Parm5 = Maximum temperature
// Sixth parameter =  Parm6 = Minimum temperature

// HomeKit types required
var types = require("./types.js")
var exports = module.exports = {};
var fs = require('fs');
var Accessory = require('../').Accessory;
var Service = require('../').Service;
var Characteristic = require('../').Characteristic;
var uuid = require('../').uuid;
var radiatorNAME = 'Parm1'; //the temperature sensor's name - this is what Siri responds to
var uuidNAME = 'hap-nodejs:accessories:Parm1'; //UUID name

// Generic radiator valve that we'll expose to HomeKit
var Generic_valve = {
        heatOn: false,
      
        setHeatOn: function(on) { 
            console.log("Set Parm1 radiator: %s", on ? "on" : "off");
            if (on) {  Generic_sensor.TargetHeatingCoolingState = 0;
                       fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {       
                       if (err) { return console.log(err); }
                       }); 
            } else {   Generic_sensor.TargetHeatingCoolingState = 1;
                       fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {       
                       if (err) { return console.log(err); }
                       });
            }
        },
}

// HomeKit doesn't support radiators, so this accessory is a modified temperature sensor.
// here's the temperature sensor device that we'll expose to HomeKit...
var Generic_sensor = {
  currentTemperature: 20,
  getTemperature: function() { 
    console.log("Getting the current temperature.");
    return Generic_sensor.currentTemperature;
  },
    incTemperature: function() {
        if (Generic_sensor.currentTemperature >= 25) { step = -1; }
        if (Generic_sensor.currentTemperature <= 20) { step = 1; }
//      if (Generic_sensor.targetTemperature > Generic_sensor.currentTemperature)
        if (Generic_sensor.currentTemperature > 23)
            {   Generic_sensor.TargetHeatingCoolingState = 1;
//              console.log("Greater than 23.");
        }
        else 
           {    Generic_sensor.TargetHeatingCoolingState = 0;
//                   console.log("Less than 23.");
           }
    Generic_sensor.currentTemperature = Generic_sensor.currentTemperature + step;
  },    getTemperature: function() {
        fs.readFile("/var/www/logs/serialized.txt", 'utf-8', function(err, data) {
            if (err) { return console.log(err); }
            var json = JSON.parse(data);                            // convert JSON data to array
//          console.log(json["Parm1"]);                           // Debug
            Generic_sensor.currentTemperature = (json["Parm1"]);
        });
    },
}

// Generate a consistent UUID for our Temperature Sensor Accessory that will remain the same
// even when restarting our server. We use the `uuid.generate` helper function to create
// a deterministic UUID based on an arbitrary "namespace" and the string "temperature-sensor".
var sensorUUID = uuid.generate(uuidNAME);
var sensor = exports.accessory = new Accessory(radiatorNAME, sensorUUID);
sensor.username = "Parm2";
sensor.pincode = "031-45-154";
sensor.displayname = "Parm1";

// set some basic properties (these values are arbitrary and setting them is optional)
sensor
  .getService(Service.AccessoryInformation)
  .setCharacteristic(Characteristic.Manufacturer, "Smartwares")
  .setCharacteristic(Characteristic.Model, "SHS-5300")
  .setCharacteristic(Characteristic.SerialNumber, "4500176458");

// Add the actual TemperatureSensor Service.
// Full list of Services and Characteristics in `lib/gen/HomeKitTypes.js`
sensor
    .addService(Service.Thermostat)
  
sensor
    .getService(Service.Thermostat) 
    .getCharacteristic(Characteristic.TargetHeatingCoolingState)
    .setProps({
//      validValues: [0, 1, 2, 3]         // Off, Heat, Cool & Auto
        validValues: [0, 1, 3]            // Off, Heat & Auto
    })
    .on('set', function(value, callback) { 
        Generic_valve.setHeatOn(value);
        callback();
     });
sensor
    .getService(Service.Thermostat) 
    .getCharacteristic(Characteristic.CurrentHeatingCoolingState)

    .on('get', function(callback) {
            var err = null; // in case there are any problems
            fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
                if (err) { return console.log(err); }
                var lines = data.split('\n');
                for(var i = 0; i < lines.length; i++){
                    if ((lines[i].indexOf("rdtr") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
                        // falls through here when we have found the line for this device in the status file
                        // console.log(lines[i]);                // debug
                        var values = lines[i].split(':');
                        // read the actual status of the accessory from the file
                        if (values[4].indexOf("On") !=-1) { 
                            console.log("Get Parm1 radiator status: On");
                            Generic_valve.heatOn = true;
                            Generic_sensor.CurrentHeatingCoolingState = 1;
                            callback(err, true);
                            return;
                        } else {
                            console.log("Get Parm1 radiator status: Off");
                            Generic_valve.heatOn = false;
                            Generic_sensor.CurrentHeatingCoolingState = 0;
                            callback(err, false);
                            return;
                        }
                    }
                }
            });
   });

sensor
     .getService(Service.Thermostat) 
     .getCharacteristic(Characteristic.TargetTemperature)
     .setProps({
        maxValue: 28,
        minValue: 5
     })
     .on('set', function(value, callback) {
         console.log("Parm1 radiator target temp:",value);
         callback();
         });

  
// cycle the temperature reading
setInterval(function() { 
//Generic_sensor.incTemperature();
  Generic_sensor.getTemperature();
  // update the characteristic value so interested iOS devices can get notified
  sensor
    .getService(Service.Thermostat)
    .setCharacteristic(Characteristic.CurrentTemperature, Generic_sensor.currentTemperature);
}, 3000); 