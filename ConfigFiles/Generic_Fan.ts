///////////////////////////////////////////////////////////////////////////////////////////////////
// First parameter  (Accessory name)                         : Parm1
// Second parameter (MAC address)                            : Parm2
// Third parameter  (command string to switch accessory on)  : Parm3
// Fourth parameter (command string to switch accessory off) : Parm4
// Fifth parameter  (Address and Channel info)               : Parm5
///////////////////////////////////////////////////////////////////////////////////////////////////

import {
  Accessory,
  AccessoryEventTypes,
  Categories,
  Characteristic,
  CharacteristicEventTypes, CharacteristicSetCallback,
  CharacteristicValue,
  NodeCallback,
  Service,
  uuid,
  VoidCallback,
} from '..';
import fs from 'fs';

class FanControllerClass {
  name: CharacteristicValue = "Parm1";
  pincode: CharacteristicValue = "031-45-154";
  username: CharacteristicValue = "Parm2";
  manufacturer: CharacteristicValue = "oddwires.co.uk";
  model: CharacteristicValue = "v1.0";
  serialNumber: CharacteristicValue = "Parm5";

  power: CharacteristicValue = false;
  outputLogs = true;

  setPower(status: CharacteristicValue) {
    if(this.outputLogs) console.log("%s: switching fan %s", this.name, status ? "on" : "off");
    if (status) {
        fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {
           if(err) { return console.log(err); }
           }); 
        } else {
        fs.appendFile("/var/www/data/input.txt", "Parm4", function(err) {
           if(err) { return console.log(err); }
           });
        }
  }

identify() {
    if(this.outputLogs) console.log("Identify the '%s'", this.name);
  }
}

const FanController = new FanControllerClass();
var FanUUID = uuid.generate('hap-nodejs:accessories:Fan' + FanController.name);
var FanAccessory = exports.accessory = new Accessory(FanController.name as string, FanUUID);

// @ts-ignore
FanAccessory.username = FanController.username;
// @ts-ignore
FanAccessory.pincode = FanController.pincode;
// @ts-ignore
FanAccessory.category = Categories.Fan;

FanAccessory
  .getService(Service.AccessoryInformation)!
    .setCharacteristic(Characteristic.Manufacturer, FanController.manufacturer)
    .setCharacteristic(Characteristic.Model, FanController.model)
    .setCharacteristic(Characteristic.SerialNumber, FanController.serialNumber);

FanAccessory.on(AccessoryEventTypes.IDENTIFY, (paired: boolean, callback: VoidCallback) => {
  FanController.identify();
  callback();
});

FanAccessory
  .addService(Service.Fan, FanController.name)
  .getCharacteristic(Characteristic.On)!
  .on(CharacteristicEventTypes.SET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
    FanController.setPower(value);
    callback();
  });

FanAccessory
  .getService(Service.Fan)!
  .getCharacteristic(Characteristic.On)!
  .on(CharacteristicEventTypes.GET, (callback: CharacteristicSetCallback) => {
    fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
        if(err) { return console.log(err); }
        var lines = data.split('\n');
        for(var i = 0; i < lines.length; i++){
            if ((lines[i].indexOf("rcon") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
               var values = lines[i].split(':');
               if (values[6].indexOf("On") !=-1) { 
                    console.log("Parm1: Get status: On");
                    callback(err, true);
                } else {
                    console.log("Parm1: Get status: Off");
                    callback(err, false);
                }
            }
        }
    });
  })