///////////////////////////////////////////////////////////////////////////////////////////////////
// First parameter  (Accessory name)                         : Parm1
// Second parameter (MAC address)                            : Parm2
///////////////////////////////////////////////////////////////////////////////////////////////////

import { Accessory, AccessoryEventTypes, Categories, Characteristic, CharacteristicEventTypes, CharacteristicSetCallback,
  CharacteristicValue, NodeCallback, Service, uuid, VoidCallback,} from '..';
import fs from 'fs';

class DoorSensorClass {
    name: CharacteristicValue = "Parm1";
    pincode: CharacteristicValue = "031-45-154";
    username: CharacteristicValue = "Parm2";
    manufacturer: CharacteristicValue = "oddwires.co.uk";
    model: CharacteristicValue = "v1.0";
    serialNumber: CharacteristicValue = "12345";
    SensorOpen: CharacteristicValue = true;
    outputLogs = true;

    identify() {
        if(this.outputLogs) console.log("Identify the '%s'", this.name);
    }
}

const DoorSensor = new DoorSensorClass();
var lightUUID = uuid.generate('hap-nodejs:accessories:light' + DoorSensor.name);
var DoorSensorAccessory = exports.accessory = new Accessory(DoorSensor.name as string, lightUUID);

DoorSensorAccessory
  .getService(Service.AccessoryInformation)!
    .setCharacteristic(Characteristic.Manufacturer, DoorSensor.manufacturer)
    .setCharacteristic(Characteristic.Model, DoorSensor.model)
    .setCharacteristic(Characteristic.SerialNumber, DoorSensor.serialNumber);

DoorSensorAccessory.on(AccessoryEventTypes.IDENTIFY, (paired: boolean, callback: VoidCallback) => {
  DoorSensor.identify();
  callback();
});

DoorSensorAccessory
    .addService(Service.ContactSensor,"Parm1")!
    .getCharacteristic(Characteristic.ContactSensorState)!
    .on(CharacteristicEventTypes.GET, (callback: CharacteristicSetCallback) => {
        fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
            if(err) { return console.log(err); }
            var lines = data.split('\n');
            for(var i = 0; i < lines.length; i++){
                if ((lines[i].indexOf("zcon") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
                    var values = lines[i].split(':');
                    if (values[8].toString().trim() === '0') {
                        if(DoorSensor.outputLogs) console.log('Parm1: Open');
                        DoorSensor.SensorOpen = true;
                    } else {
                        if(DoorSensor.outputLogs) console.log('Parm1: Closed');
                        DoorSensor.SensorOpen = false;
                    }
                callback(null, DoorSensor.SensorOpen);
                }
            }
        });
    });