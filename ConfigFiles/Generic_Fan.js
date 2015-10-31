// Description: Accessory Shim to use with homebridge https://github.com/nfarina/homebridge
// Copy this file into the folder: homebridge/accessories

// First parameter  = Parm1 = Accessory name
// Second parameter = Parm2 = Command string to switch accessory on
// Third parameter  = Parm3 = Command string to switch accessory off
// Fourth parameter = Parm4 = Configuration details

var Service = require("hap-nodejs").Service;
var Characteristic = require("hap-nodejs").Characteristic;
var request = require("request");
var fs = require('fs');

module.exports = {
  accessory: Fan
}

'use strict';

function Fan(log, config) {
  this.log = log;
  this.name = config["name"];
  this.Characteristic = {};
  this.currentValue = {};
}

Fan.prototype = {
  getPowerState: function(callback) {
    var that = this;
    var err = null;                         // in case there were any problems
    fs.readFile("/var/www/data/status.txt", 'utf8', function(err, data) {
        if(err) { return console.log(err); }
	    var lines = data.split('\n');
        for(var i = 0; i < lines.length; i++){
		    if ((lines[i].indexOf("rcon") !=-1) && (lines[i].indexOf("Parm1") !=-1)) {
                var svalues = lines[i].split(':');
				if (svalues[5].toString().trim() === 'on') {
					that.currentValue.On = true;
				    that.log("getPowerState: " + that.currentValue.On);
					callback(null, that.currentValue.On);
                } else {
					that.currentValue.On = false;
				    that.log("getPowerState: " + that.currentValue.On);
					callback(null, that.currentValue.On);
                }
			}
		}
	});
  },
  
  setPowerState: function(boolvalue, callback) {
    this.log("setPowerState: " + boolvalue);
//    this.currentValue.On = boolvalue;
    console.log("Turning the Parm1 %s", boolvalue ? "on" : "off");
	if (boolvalue) {
       fs.appendFile("/var/www/data/input.txt", "Parm2", function(err) {		
          if(err) { return console.log(err); }
          console.log("Parm1 on Success");
       }); 
    } else {
       fs.appendFile("/var/www/data/input.txt", "Parm3", function(err) {
          if(err) { return console.log(err); }
          console.log("Parm1 off Success");
   	   });
	}
    callback();
  },

  getFanInUse: null, // N/A
  
  setFanInUse: null, // N/A
      
  identify: function(callback) {
    this.log("Identify requested!");
    callback(); // success
  },
  
  getServices: function() {
    var informationService = new Service.AccessoryInformation();
    informationService
      .setCharacteristic(Characteristic.Manufacturer, "oddwires.co.uk")
      .setCharacteristic(Characteristic.SerialNumber, "Parm4")
      .setCharacteristic(Characteristic.Name, this.name);
        
    var FanService = new Service.Fan();
    this.Characteristic.On = FanService
      .getCharacteristic(Characteristic.On)
      .on('get', this.getPowerState.bind(this))
      .on('set', this.setPowerState.bind(this));
    
    return [informationService, FanService];
  }
};

