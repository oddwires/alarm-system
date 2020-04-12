///////////////////////////////////////////////////////////////////////////////////////////////////
// First parameter  (Radiator name)                          : Parm1
// Second parameter (MAC address)                            : Parm2
// Third parameter  (command string to switch accessory on)  : Parm3
// Fourth parameter (command string to switch accessory off) : Parm4
// Fifth parameter  (Maximum temperature)                    : Parm5
// Sixth parameter  (Minimum temperature)                    : Parm6
///////////////////////////////////////////////////////////////////////////////////////////////////

import {Accessory, AccessoryEventTypes, Categories, Characteristic, CharacteristicEventTypes, CharacteristicSetCallback,
CharacteristicValue, NodeCallback, Service, uuid, VoidCallback } from '..';
import fs from 'fs';

class RadiatorControllerClass {
    name: CharacteristicValue = "Parm1";
    displayName: CharacteristicValue = "Parm1";
    pincode: CharacteristicValue = "031-45-154";
    username: CharacteristicValue = "CC:22:3D:B3:CE:00";
    HeatingState: CharacteristicValue = 0;
    TargetHeatingState: CharacteristicValue = 0;
    TargetTemperature: CharacteristicValue = 0;
    DummyCurrentTemp = 25;
    RealCurrentTemp = 25;
    step = 1;

    outputLogs = true;

    SetHeatingOn(status: CharacteristicValue) {
        if(this.outputLogs) console.log("Parm1: Set heating state: %s", status);
        if(this.outputLogs) {               // convert status to format used in the accessory interface...
            if ( status == 0 ) { console.log("Parm1: Set heating state: Off"); }
            if ( status == 1 ) { console.log("Parm1: Set heating state: Heat"); }
            if ( status == 3 ) { console.log("Parm1: Set heating state: Auto"); }
        };
        this.TargetHeatingState = status;
        // following code ensures we only send commands to the radiator valve when a change of state is required
        // ( otherwise they would be beeping all the time )
        if ( this.TargetHeatingState != this.HeatingState ) {
                if (( this.TargetHeatingState == 0 ) && ( this.HeatingState !=0 )) {
                    // 0 indicates 'off'. If it isn't already off, switch it off.
                    this.HeatingState = 0;
                    fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {
                        if(err) { return console.log(err); }
                }); 
                }
                if (( this.TargetHeatingState == 1 ) && ( this.HeatingState !=1 )) {
                    // 1 indicates 'on'. If it isn't already on, switch it on.
                    this.HeatingState = 1;
                    fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {
                        if(err) { return console.log(err); }
                   });
                }
        } // NOTE: HeatingState = 3 indicates 'auto', and is implemented in the DummyTemp / RealTemp function.
    }

// Function to get (real) temperature data from ESP8266 based sensor...
    RealTemp() {
        let that = this;
        fs.readFile("/var/data/app-sensor/current_values.txt", 'utf-8', function(err, data) {
            if (err) { return console.log(err); }
                const json = JSON.parse(data);                      // NOTE: if object isn't found, returns the string 'undefined'
                that.RealCurrentTemp = +json[ 'Parm1' ];            // convert string to numeric
        });
        if ( this.TargetHeatingState == 3 ) {                                                 // Heating state = auto
            if ( this.RealCurrentTemp >= this.TargetTemperature ) {                           // Too hot !
                if ( this.HeatingState !=0 ) {                                                // If radiator isn't already off, switch it off.
                    if(this.outputLogs) console.log("%s: Currently %sC - Too hot. Turning the radiator off.", this.name, this.RealCurrentTemp ); 
                    this.HeatingState = 0;
                    fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {         // send command to alarm service
                        if(err) { return console.log(err); }
                    });
                }
            }
            if ( this.RealCurrentTemp < this.TargetTemperature ) {                            // Too cold !
                if ( this.HeatingState !=1 ) {                                                // If radiator isn't already on, switch it on.
                    if(this.outputLogs) console.log("%s: Currently %sC - Too cold. Turning the radaitor on.", this.name, this.RealCurrentTemp ); 
                    this.HeatingState = 1;
                    fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {         // send command to alarm service
                        if(err) { return console.log(err); }
                    });
                }
            }
        }
        return this.RealCurrentTemp
    }

// Function to create dummy temperature data...
    DummyTemp() {
        if (this.DummyCurrentTemp >= 25) this.step = -1;                                      // Count down
        if (this.DummyCurrentTemp <= 20) this.step = 1;                                       // Count up
        this.DummyCurrentTemp = this.DummyCurrentTemp + this.step;

        if ( this.TargetHeatingState == 3 ) {                                                 // Heating state = auto
            if ( this.DummyCurrentTemp >= this.TargetTemperature ) {                          // Too hot !
                if ( this.HeatingState !=0 ) {                                                // If radiator isn't already off, switch it off.
                    if(this.outputLogs) console.log("%s: Currently %sC - Too hot. Turning the radiator off.", this.name, this.DummyCurrentTemp ); 
                    this.HeatingState = 0;
                    fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {         // send command to alarm service
                        if(err) { return console.log(err); }
                    });
                }
            }
            if ( this.DummyCurrentTemp < this.TargetTemperature ) {                           // Too cold !
                if ( this.HeatingState !=1 ) {                                                // If radiator isn't already on, switch it on.
                    if(this.outputLogs) console.log("%s: Currently %sC - Too cold. Turning the radaitor on.", this.name, this.DummyCurrentTemp ); 
                    this.HeatingState = 1;
                    fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {         // send command to alarm service
                        if(err) { return console.log(err); }
                    });
                }
            }
        return this.DummyCurrentTemp
        }
    }

    SetTargetTemp(value: CharacteristicValue) { this.TargetTemperature = value; }

    Identify() { if(this.outputLogs) console.log("Identify the '%s'", this.name); }
}
const Radiator = new RadiatorControllerClass();
var RadiatorUUID = uuid.generate('hap-nodejs:accessories:Radiator' + Radiator.name);
var RadiatorAccessory = exports.accessory = new Accessory(Radiator.name as string, RadiatorUUID);

RadiatorAccessory
  .getService(Service.AccessoryInformation)!
    .setCharacteristic(Characteristic.Manufacturer, "Smartwares")
    .setCharacteristic(Characteristic.Model, "SHS-5300")
    .setCharacteristic(Characteristic.SerialNumber, "4500176458");

RadiatorAccessory.on(AccessoryEventTypes.IDENTIFY, (paired: boolean, callback: VoidCallback) => {
  Radiator.Identify();
  callback();
});

RadiatorAccessory!
  .addService(Service.Thermostat, Radiator.name)!
  .getCharacteristic(Characteristic.TargetHeatingCoolingState)!
    .setProps({
        validValues: [0, 1, 3]            // Off, Heat & Auto
    })
  .on(CharacteristicEventTypes.SET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
  Radiator.SetHeatingOn(value);
  callback();
  })

  RadiatorAccessory!
  .getService(Service.Thermostat)!
  .getCharacteristic(Characteristic.TargetTemperature)!
  .setProps({
//      maxValue: Parm5,       // If max and min are set too close, the accessory will cause Homebridge to crash.
//      minValue: Parm6        // So hard coding these values as a workaround...
        maxValue: 30,
        minValue: 10
  })
  .on(CharacteristicEventTypes.SET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
  console.log("Parm1: Set target temp:",value);
  Radiator.SetTargetTemp(value);
  callback();
  })
  
//////////////////////  
  RadiatorAccessory!
  .addService(Service.TemperatureSensor)!
  .getCharacteristic(Characteristic.CurrentTemperature)!
  .on(CharacteristicEventTypes.GET, (callback: NodeCallback<CharacteristicValue>) => {
   callback(null, Radiator.RealTemp());
});

// This should be handled through the function in the class, only I can't get the callback to work.
    //  .on(CharacteristicEventTypes.GET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
    //  Radiator.GetHeatingOn(value);
    //  callback();
    //  })
// So using an external function as a workaround...

  RadiatorAccessory!
    .getService(Service.Thermostat)!
    .getCharacteristic(Characteristic.CurrentHeatingCoolingState)!
    .on(CharacteristicEventTypes.GET, (callback: CharacteristicSetCallback) => {
        fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
            if (err) { return console.log(err); }
            var lines = data.split('\n');
            for(var i = 0; i < lines.length; i++){
                if ((lines[i].indexOf("rdtr") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
                    // falls through here when we have found the line for this device in the status file
                    // console.log(lines[i]);                // debug
                    var values = lines[i].split(':');
                    if (values[4].indexOf("On") !=-1) {
                        console.log("Parm1 radiator: Get status: On");
                        Radiator.HeatingState = 1;
                        callback(err, true);
                        return;
                    } else {
                        console.log("Parm1 radiator: Get status: Off");
                        Radiator.HeatingState = 0;
                        callback(err, false);
                        return;
                    }
                }
            }
        });
   });

// Use this section if you don't have a temperature sensor to provide data...
// Cycle the temperature reading...
setInterval(function() { 
    Radiator.DummyTemp();
    RadiatorAccessory
        .getService(Service.Thermostat)!
        .setCharacteristic(Characteristic.CurrentTemperature, Radiator.DummyCurrentTemp);
}, 3000);

// Use this section if you do have a temperature sensor to provide real data...
// cycle the temperature reading...
//setInterval(function() { 
//    Radiator.RealTemp();
//    RadiatorAccessory
//        .getService(Service.Thermostat)!
//        .setCharacteristic(Characteristic.CurrentTemperature, Radiator.RealCurrentTemp);
//}, 3000);