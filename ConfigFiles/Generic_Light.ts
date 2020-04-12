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

class LightControllerClass {
    name: CharacteristicValue = "Parm1";
    pincode: CharacteristicValue = "031-45-154";
    username: CharacteristicValue = "Parm2";
    manufacturer: CharacteristicValue = "oddwires.co.uk";
    model: CharacteristicValue = "v1.0";
    serialNumber: CharacteristicValue = "Parm5";
    power: CharacteristicValue = false;
    outputLogs = true;

    SetPower(status: CharacteristicValue) {
        if(this.outputLogs) console.log("%s: switching light %s", this.name, status ? "on" : "off");
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

    GetPower(status: CharacteristicValue) {                                             // ( this function not currently used )
        if(this.outputLogs) console.log("Get the '%s' status.", this.name);
    }

    identify() {
        if(this.outputLogs) console.log("Identify the '%s'", this.name);
    }
}

const LightController = new LightControllerClass();
var lightUUID = uuid.generate('hap-nodejs:accessories:light' + LightController.name);
var lightAccessory = exports.accessory = new Accessory(LightController.name as string, lightUUID);

lightAccessory
  .getService(Service.AccessoryInformation)!
    .setCharacteristic(Characteristic.Manufacturer, LightController.manufacturer)
    .setCharacteristic(Characteristic.Model, LightController.model)
    .setCharacteristic(Characteristic.SerialNumber, LightController.serialNumber);

lightAccessory.on(AccessoryEventTypes.IDENTIFY, (paired: boolean, callback: VoidCallback) => {
  LightController.identify();
  callback();
});

lightAccessory
  .addService(Service.Lightbulb, LightController.name)
  .getCharacteristic(Characteristic.On)!
  .on(CharacteristicEventTypes.SET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
    LightController.SetPower(value);
    callback();
  });

  lightAccessory
  .getService(Service.Lightbulb)!
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
 