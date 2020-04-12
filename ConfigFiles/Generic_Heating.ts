///////////////////////////////////////////////////////////////////////////////////////////////////
// First parameter  (Accessory name)                         : Parm1
// Second parameter (MAC address)                            : Parm2
// Third parameter  (command string to switch accessory on)  : Parm3
// Fourth parameter (command string to switch accessory off) : Parm4
// Fifth parameter  (Address and Channel info)               : Parm5
// Sixth parameter  (Status string)                          : Parm6
///////////////////////////////////////////////////////////////////////////////////////////////////

import { Accessory, AccessoryEventTypes, Categories, Characteristic, CharacteristicEventTypes,
  CharacteristicSetCallback, CharacteristicValue, NodeCallback, Service, uuid, VoidCallback,} from '..';
import fs from 'fs';

class OutletControllerClass {
  name: CharacteristicValue = "Parm1";
  pincode: CharacteristicValue = "031-45-154";
  username: CharacteristicValue = "Parm2";
  manufacturer: CharacteristicValue = "oddwires.co.uk";
  model: CharacteristicValue = "v1.0";
  serialNumber: CharacteristicValue = "Parm5";

  power: CharacteristicValue = false;
  outputLogs = true;

  setPower(status: CharacteristicValue) {
    if(this.outputLogs) console.log("%s: switching outlet %s", this.name, status ? "on" : "off");
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

const OutletController = new OutletControllerClass();
var OutletUUID = uuid.generate('hap-nodejs:accessories:Outlet' + OutletController.name);
var OutletAccessory = exports.accessory = new Accessory(OutletController.name as string, OutletUUID);

// @ts-ignore
//OutletAccessory.username = OutletController.username;
// @ts-ignore
//OutletAccessory.pincode = OutletController.pincode;
// @ts-ignore
//OutletAccessory.category = Categories.Outlet;

OutletAccessory
  .getService(Service.AccessoryInformation)!
    .setCharacteristic(Characteristic.Manufacturer, OutletController.manufacturer)
    .setCharacteristic(Characteristic.Model, OutletController.model)
    .setCharacteristic(Characteristic.SerialNumber, OutletController.serialNumber);

OutletAccessory.on(AccessoryEventTypes.IDENTIFY, (paired: boolean, callback: VoidCallback) => {
  OutletController.identify();
  callback();
});

OutletAccessory
  .addService(Service.Outlet, OutletController.name)
  .getCharacteristic(Characteristic.On)!
  .on(CharacteristicEventTypes.SET, (value: CharacteristicValue, callback: CharacteristicSetCallback) => {
    OutletController.setPower(value);
    callback();
  });

OutletAccessory
  .getService(Service.Outlet)!
  .getCharacteristic(Characteristic.On)!
  .on(CharacteristicEventTypes.GET, (callback: CharacteristicSetCallback) => {
    fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
        if(err) { return console.log(err); }
        var lines = data.split('\n');
        for(var i = 0; i < lines.length; i++){
            if ((lines[i].indexOf("heat") !=-1) && (lines[i].indexOf("mode") !=-1)) {
               var values = lines[i].split(':');
               if (values[2].indexOf("Parm6") !=-1) { 
                    console.log("Boiler: Get status: On");
                    callback(err, true);
                } else {
                    console.log("Boiler: Get status: Off");
                    callback(err, false);
                }
            }
        }
    });
  })