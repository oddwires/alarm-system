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
var radiatorNAME = 'Parm1 radiator'; //the temperature sensor's name - this is what Siri responds to
var uuidNAME = 'hap-nodejs:accessories:Parm1'; //UUID name

// Generic radiator device that we'll expose to HomeKit
var GENERIC_radiator = {
  heatOn: false,
  setHeatOn: function(on) { 
    console.log("Turning the Parm1 radiator %s", on ? "on" : "off");
	if (on) {
		TEMP_SENSOR.TargetHeatingCoolingState = 0;
            fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {		
               if(err) { return console.log(err); }
               console.log("Parm1 radiator on Success");
               }); 
        	} else {
		TEMP_SENSOR.TargetHeatingCoolingState = 1;
            fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {		
               if(err) { return console.log(err); }
               console.log("Parm1 radiator off Success");
   			   });
			}
  },
}

// HomeKit doesn't support radiators, so this accessory is a modified temperature sensor.
// here's the temperature sensor device that we'll expose to HomeKit...
var TEMP_SENSOR = {
  currentTemperature: 20,
  getTemperature: function() { 
    console.log("Getting the current temperature.");
//    TEMP_SENSOR.currentTemperature = Math.round(Math.random() * 100);
    return TEMP_SENSOR.currentTemperature;
  },
    randomizeTemperature: function() {
//  console.log("Creating random value for current temperature.");
//  TEMP_SENSOR.currentTemperature = Math.round(Math.random() * 100);
    TEMP_SENSOR.currentTemperature = Math.round((15 + Math.random() * 10));
  },
    incTemperature: function() {
		if (TEMP_SENSOR.currentTemperature >= 25) { step = -1; }
		if (TEMP_SENSOR.currentTemperature <= 20) { step = 1; }
		
//      if (TEMP_SENSOR.targetTemperature > TEMP_SENSOR.currentTemperature)
        if (TEMP_SENSOR.currentTemperature > 23)
	        {	TEMP_SENSOR.TargetHeatingCoolingState = 1;
//              console.log("Greater than 23.");
		}
	    else 
	       {	TEMP_SENSOR.TargetHeatingCoolingState = 0;
//                   console.log("Less than 23.");
           }
//		if (TEMP_SENSOR.currentTemperature >= 23 ) {
//			GENERIC_radiator.setHeatOn(false);
//		} else {
//			GENERIC_radiator.setHeatOn(true);
//		}
//		console.log(step);
    TEMP_SENSOR.currentTemperature = TEMP_SENSOR.currentTemperature + step;
  }
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
  .setCharacteristic(Characteristic.Manufacturer, "smartwares")
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
//              validValues: [0, 1, 2, 3]         // Off, Heat, Cool & Auto
                validValues: [0, 1, 3]            // Off, Heat & Auto
		 })
     .on('set', function(value, callback) { 
    GENERIC_radiator.setHeatOn(value);
    callback();
     }); 

sensor
     .getService(Service.Thermostat) 
	 .getCharacteristic(Characteristic.TargetTemperature)
	 .setProps({
		maxValue: 25,
		minValue: 0              // can't seem to bring value up, will only go down :(
     })
     .on('set', function(value, callback) {
		 console.log("Parm1 radiator target temp:",value);
		 callback();
		 });

  
// cycle the temperature reading
setInterval(function() { 
  TEMP_SENSOR.incTemperature();
  // update the characteristic value so interested iOS devices can get notified
  sensor
    .getService(Service.Thermostat)
    .setCharacteristic(Characteristic.CurrentTemperature, TEMP_SENSOR.currentTemperature);
}, 3000); 