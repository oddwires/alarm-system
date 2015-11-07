///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// HomeKit functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// logs.php hyperlinks redirect here...
            function HomeKit() {
                tmp = "HK export:";
                $('#retval').val(tmp);
                ajaxrequest('writedata.php','');                                  // doesn't matter where we send the data as long as
				                                                                  // it goes through 'readvars' to make it go
		        tmp = "You will also need to clear the HomeKit database on your phone !"
                alert (tmp);
			}	

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Log functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// logs.php hyperlinks redirect here...
            function LogSelect(date) {
                $('#LogsHead').text("Log " + date);
                var tmp = 'logscroll.php?date=' + date;
                ajaxrequest(tmp,'LogScroll');                                     // synchronous send
                jQT.goTo('#logview', 'slideleft');
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// User functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// user.php and userscroll.php hyperlinks redirect here. So we are either going to edit an existing user, or add a new one.
            function UserConf(number) {
                if ($('#UserName' + (number)).length) {                           // do we have info on this user ?
                    $('#UserConfName').val($('#UserName' + (number)).text());     // Yes - existing user - scrape user name and pass to config screen
                    $('#UserConfEmail').val($('#UserEmail' + (number)).val());    // Yes - existing user - scrape email and pass to config screen
                } else {
                    $('#UserConfName').val("");                                   // No - new user - clear out any previous value from config screen
                    $('#UserConfEmail').val("");                                  // No - new user - clear out any previous value from config screen
                }
                $('#UserConfHead').text("Edit user " + (number));                 // update the title
                jQT.goTo('#useredit', 'slideleft');                               // ...and go to user config screen
                return false;                                                     // returning false cancels the original hyper-link
            }

            // User config has changed, so populate the retval field...
            function UserSend() {
                var tmp = 'user cfg:';
                tmp += ($('#UserConfHead').text().substring(10)) + ':';           // retrieve the user number from the header
                tmp += $('#UserConfName').val() + ':';                            // scrape user name and pass to config screen
                tmp += $('#UserConfEmail').val();                                 // scrape email details and pass to config screen
                console.log(tmp);
                $('#retval').val(tmp);
                ajaxrequest('userscroll.php','userlist');                         // synchronous send
                return false;
            }

            function UserDel() {
                var tmp = 'user del:';
                tmp += ($('#UserConfHead').text().substring(10)) + ':';            // retrieve the user number from the header
                console.log(tmp);
                $('#retval').val(tmp);
                ajaxrequest('userscroll.php','userlist');                          // synchronous send
                jQT.goTo('#userlist', 'slideright');                               // ...and go to user list screen
            }
            function Password() {
                  var $tmp1 = $('#Pwd1').val();
                  var $tmp2 = $('#Pwd2').val();
                  if ($tmp1 != $tmp2) {
                         alert("The passwords do not match.\nPlease try again.");
                         $('#Pwd1').val() = '';
                         $('#Pwd2').val() = '';
                         return false;
                   } else {
                        tmp = 'user pwd:';
                        tmp += ($('#UserConfHead').text().substring(10)) + ':';    // retrieve the user number from the header
                        tmp += $tmp1;
                        $('#retval').val(tmp);
                        ajaxrequest('userscroll.php','userlist');
                        jQT.goTo('#useredit', 'slideright');                       // ...and go to user edit screen
                    }    
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Alarm functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// alarm.php and alarmscroll.php hyper-links redirect here, so we can get a lot jiggy with the titles and parameters
            function ZconJmp(number) {
                $('#AlarmConfHead').text("Edit zone " + (number));                // update the title
                $('#AlarmConfName').val($('#ZoneName' + (number)).val());         // scrape zone name and pass to config screen
                $('#CircuitConfName').val($('#ZoneCirc' + (number)).val());
                if ($('#ZoneType' + number).val() == "alarm") {
                    $('#AlarmZone').prop('checked', true);                        // set radio button values alarm/tamper
                    $('#TamperZone').prop('checked', false);
                    $('#alarmOptions').show();                                    // show alarm options
                } else {
                    $('#AlarmZone').prop('checked', false);
                    $('#TamperZone').prop('checked', true);
                    $('#alarmOptions').hide();                                    // hide alarm options
                }
                if ($('#ZoneFset' + number).val() == "on") {
                    $('#ZoneFS').prop('checked', true);                           // Full set switch
                } else {
                    $('#ZoneFS').prop('checked', false);
                }
                if ($('#ZonePset' + number).val() == "on") {
                    $('#ZonePS').prop('checked', true);                           // Part set switch
                } else {
                    $('#ZonePS').prop('checked', false);
                }
                if ($('#ZoneChim' + number).val() == "on") {
                    $('#ZoneCH').prop('checked', true);                           // Chimes switch
                } else {
                    $('#ZoneCH').prop('checked', false);
                }
                jQT.goTo('#alarmconfig', 'slideleft')                             // ...and go to config screen
                return false;                                                     // returning false cancels the original hyper-link
            }
            function AlarmCfgSend() {
                var tmp2;
                   var tmp = 'zcon cfg:';
                   tmp += $('#AlarmConfHead').text().substring(10) + ':';         // retrieve the zone number from the header
                   tmp +=  $('input[name=ZoneMode]:checked').val() + ':';
                   tmp += $('#AlarmConfName').val() + ':';
                   if ($('#ZoneFS').is(":checked")) { tmp += "on:" }              // add day mode config
                   else { tmp += "off:"; }
                   if ($('#ZonePS').is(":checked")) { tmp += "on:" }              // add night mode config
                   else { tmp += "off:"; }
                   if ($('#ZoneCH').is(":checked")) { tmp += "on:" }              // add chimes config
                   else { tmp += "off:"; }
                   tmp += $('#CircuitConfName').val();
                   tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                                                                                  // change any ' #' to ' \#'
                   console.log(tmp);
                   $('#retval').val(tmp);
                   ajaxrequest('alarmscroll.php','alarm');                        // synchronous send
                   return false;
            }

// changing the zone alarm/tamper setting redirects here
            function showHide(zonetype) {
                if (zonetype == "alarm") {
                    $('#alarmOptions').show(500);
                } else {
                    $('#alarmOptions').hide(500);
                }
            }
            function AlarmMode(NewMode) {
                $('#retval').val(NewMode);
                   ajaxrequest('alarmscroll.php','alarm');                        // synchronous send
                   return false;
            }

            function ZconDel() {
                var num = ($('#AlarmConfHead').text().substring(10));             // retrieve the task number from the header
                var tmp = 'zcon del:' + (num);
                $('#retval').val(tmp);
                ajaxrequest('alarmscroll.php','alarm');                           // synchronous send
                jQT.goTo('#alarm', 'slideright');                                 // ...and go to task edit screen
                return false;
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Automation functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// auto.php and autoscroll.php hyper-links redirect here, so we can get a bit jiggy with the titles and parameters
            function Rconedit(number) {
                $('#RconfHead').text("Edit channel " + number);                  // update the title
                $('#RconName').val($('#hyplnk' + number).text());                // scrape current channel name and pass to config screen
                $('#RconAddr').val($('#RconAddr' + number).val());
                $('#RconChan').val($('#RconChan' + number).val());
				// need to check if this element exists
				// existing switches already have a value
                // new switches wont have a value, so need to create default value manually...
                if ($('#RconMode' + number).length >0 ) {
                    tmp = $('#RconMode' + number).val().toLowerCase();;
				} else { tmp = "none" }
				switch (tmp){
					   case "on":
                        $('#RconAlarmAction').text("Switch on");
                        break;
					   case "off":
                        $('#RconAlarmAction').text("Switch off");
                        break;
					   case "none":
                        $('#RconAlarmAction').text("None");
                        break;					
				}
                $('#RconHK').text($('#RconHK' + number).val());
                tmp=$('#hyplnk' + number).text();
                $('#retval').val(number);                                         // pass channel number to config screen
                jQT.goTo('#autocfg', 'slideleft')                                 // ...and go to config screen
                return false;                                                     // returning false cancels the original hyper-link
            }

// RC On/Off switch has changed, so populate the retval field...
            function RconSwitch(number) {
                tmp = "rcon swtch:" + number + ":"
                if ($('#RC' + number).is(":checked")) { tmp += "on"; }
                else { tmp += "off"; }
                $('#retval').val(tmp);
                ajaxrequest('writedata.php','');                                  // doesn't matter where we send the data as long as
                                                                                  // it goes through 'readvars' to make it go
            }
// RC channel configuration has changed, so populate the retval field...
            function RconSend(number) {
                var tmp = 'rcon cfg:';
                tmp += $('#RconfHead').text().substring(13) + ':';                // retrieve the channel number from the header
                tmp += $('#RconName').val() + ':';
                tmp += $('#RconAddr').val() + ':';
                tmp += $('#RconChan').val() + ':';
				// have to use an else if as the word 'none' also includes the word 'on' - and we only want one match
                if ($('#RconAlarmAction').text().toLowerCase().indexOf("none") >= 0) { tmp += "None::"; }
                else if ($('#RconAlarmAction').text().toLowerCase().indexOf("on") >= 0) { tmp += "On::"; }
                if ($('#RconAlarmAction').text().toLowerCase().indexOf("off") >= 0) { tmp += "Off::"; }
				tmp += $('#RconHK').text();
                tmp = tmp.replace(/\ #/g,' \\#');                                 // ' #' (space hash) characters needs to be delimited 
				                                                                  // change any ' #' to ' \#'
				tmp = tmp.trim()                                                  // ...and get rid of any sneaky white space characters
                $('#retval').val(tmp);
                ajaxrequest('autoscroll.php','auto');                             // synchronous send
	            jQT.goTo('#auto', 'slideright');                                  // ...and go to auto edit screen
            }

            function RconDel() {
                var num = ($('#RconfHead').text().substring(12));                 // retrieve the task number from the header
                var tmp = 'rcon del:' + (num);
                $('#retval').val(tmp);
                ajaxrequest('autoscroll.php','auto');                             // synchronous send
                jQT.goTo('#auto', 'slideright');                                  // ...and go to task edit screen
                return false;
            }

			function HKret(type,value,string) {
                // returns string values from days, months and tasks
                switch (type) {
                    case "accessory":
                        $('#RconHK').text(string);                               // update visible string
                        break;
                    case "action":
                        $('#RconAlarmAction').text(string);                             // update visible string
                        break;
                    }
                jQT.goTo('#autocfg', 'slideright');			
			}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Task functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// task.php and taskscroll.php hyperlinks redirect here
            function taskedit(tasknum) {
                var weekday = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Week days', 'Week ends'];
                var month = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                $('#TaskConfHead').text("Edit task " + (tasknum + 1));            // update the title
                $('#edt_hours').text($('#hours_' + (tasknum)).val());             // scrape values and pass to config screen
                $('#edt_minutes').text($('#minutes_' + (tasknum)).val());

                var tmp = $('#dom_' + (tasknum)).val();
                $('#edt_dom_str').text($('#dom_' + (tasknum)).val());
                var tmp = $('#month_' + (tasknum)).val();
                $('#edt_month').val(tmp);                                         // store numeric in hidden field
                if (tmp == "*") { $('#edt_month_str').text("*");                  // any (*)
                                  $('#mth_13').addClass("active"); }              // highlight current value
                else { $('#edt_month_str').text(month[tmp - 1]);                  // expand month
                       $('#mth_' + tmp).addClass("active"); }                     // highlight current value

                tmp = $('#weekday_' + (tasknum)).val();
                $('#edt_wday').val(tmp);                                          // store numeric in hidden field
                switch (tmp) {
                    case '1-5':
                        $('#edt_wday_str').text("Weekdays");
                        $('#dow_7').addClass("active");
                        break;
                    case '0,6':
                        $('#edt_wday_str').text("Weekends");
                        $('#dow_8').addClass("active");
                        break;
                    case '*':
                        $('#dow_9').addClass("active");
                        break;
                    default:
                        $('#edt_wday_str').text(weekday[tmp]);                    // Expand weekday
                        $('#dow_' + tmp).addClass("active");
                        break;
                }
                tmp = $('#tasknum_' + (tasknum)).val();
                $('#edt_task').val(tmp);                                          // store numeric in hidden field
                $('#edt_task_str').text($('#taskname_' + (tasknum)).val());
                $('#task_' + tmp).addClass("active");                             // highlight current value
                jQT.goTo('#taskedit', 'slideleft');                               // ...and go to task edit screen
                return false;                                                     // returning false cancels the original hyper-link
            }
            function newtask() {
                var tasknum = $('#taskcount').val();
                $('#edt_hours').text("*");                                        // set default values
                $('#hours_' + (tasknum)).val("*");                                // set default values
                $('#edt_minutes').text("*");
                $('#minutes_' + (tasknum)).val("*");                              // set default values
                $('#edt_dom').text("*");
                $('#dom_' + (tasknum)).val("*");                                  // set default values
                $('#edt_month').val("*");
                $('#edt_month_str').text("*");
                $('#month_' + (tasknum)).val("*");                                // set default values
                $('#edt_wday').val("*");
                $('#edt_wday_str').text("*");
                $('#weekday_' + (tasknum)).val("*");                              // set default values
                $('#edt_task').val("1");
                $('#edt_task_str').text("Alarm Standby");
                $('#taskname_' + (tasknum)).val("*");                             // set default
                tasknum++;
                $('#TaskConfHead').text("Edit task " + (tasknum));                // update the title
                $('#taskcount').val(tasknum);
                jQT.goTo('#taskedit', 'slideleft');                               // ...and go to task edit screen
                return false;                                                     // returning false cancels the original hyper-link
            }
// Task has changed, so populate the retval field and send it...
            function TaskSend() {
                var tasknum = ($('#TaskConfHead').text().substring(10)-1);        // retrieve the task number from the header
                // create data string to send to alarm service...
                var tmp = 'edit task:'+ (tasknum) + ':';
                tmp += ($('#edt_minutes').text()) + ':';
                tmp += ($('#edt_hours').text()) + ':';
                tmp += ($('#edt_dom_str').text()) + ':';
                tmp += ($('#edt_month').val()) + ':';
                tmp += ($('#edt_wday').val()) + ':';
                tmp += ($('#edt_task').val());
                console.log(tmp);
                $('#retval').val(tmp);
                ajaxrequest('taskscroll.php','tasklist');                         // synchronous send
                jQT.goTo('#tasklist', 'slideright');                              // ...and go to task edit screen
            }
			
            function TaskDel() {
                var tasknum = ($('#TaskConfHead').text().substring(10)-1);        // retrieve the task number from the header
                var tmp = 'delete task:' + (tasknum);
                $('#retval').val(tmp);
                ajaxrequest('taskscroll.php','tasklist');                         // synchronous send
                jQT.goTo('#tasklist', 'slideright');                              // ...and go to task edit screen
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Numpad functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            function numpadinit(title) {
                // creates a multi purpose numeric keypad...
                switch (title) {
                    case "Select hours":
                        $('#numval').val($('#edt_hours').text());
                        break;
                    case "Select minutes":
                        $('#numval').val($('#edt_minutes').text());
                        break;
                    case "Select date":
                        $('#numval').val($('#edt_dom_str').text());
                        break;
                    case "Select month":
                        $('#numval').val($('#edt_month').text());
                        break;
                }
                $('#NumPadHead').text(title);
                jQT.goTo('#numpad', 'slideleft');                                     // ...and go to task edit screen
                return false;                                                         // returning false cancels the original hyper-link
            }
            function numpadret() {
                // returns value from numeric keypad
                var tasknum = ($('#TaskConfHead').text().substring(10)-1);           // retrieve the task number from the header
                var taskprop = ($('#NumPadHead').text());                            // retrieve the task property from the header
                switch (taskprop){
                    case "Select hours":
                        $('#edt_hours').text($('#numval').val());
                        $('#hours_' + (tasknum)).val($('#numval').val());            // update hidden table
                        break;
                    case "Select minutes":
                        $('#edt_minutes').text($('#numval').val());
                        $('#minutes_' + (tasknum)).val($('#numval').val());          // update hidden table
                        break;
                    case "Select date":
                        $('#edt_dom_str').text($('#numval').val());
                        $('#dom_' + (tasknum)).val($('#numval').val());              // update hidden table
                        break;
                }
            }
            function stringret(type,value,string) {
                // returns string values from days, months and tasks
                var tasknum = ($('#TaskConfHead').text().substring(10)-1);        // retrieve the task number from the header
                switch (type) {
                    case "month":
                        $('#edt_month').val(value);                               // update hidden numeric
                        $('#month_' + (tasknum)).val(value);                      // update hidden table
                        $('#edt_month_str').text(string);                         // update visible string
                        break;
                    case "weekday":
                        $('#edt_wday').val(value);                                // update hidden numeric
                        $('#weekday_' + (tasknum)).val(value);                    // update hidden table
                        $('#edt_wday_str').text(string);                          // update visible string
                        break;
                    case "task":
                        $('#edt_task').val(value);                                // update hidden numeric
                        $('#tasknum_' + (tasknum)).val(value);                    // update hidden table
                        $('#edt_task_str').text(string);                          // update visible string
                        break;
                }
                jQT.goTo('#taskedit', 'slideright');
            }
            function numpadupdate(newval) {
                // updates the numpad screen each time a button is pressed
                if (newval == "*") {
                    tmp = "*";
                } else {
                    tmp = $('#numval').val();                                     // current value
                    if (tmp == "*") { tmp = 0; }
                    tmp = tmp % 10;                                               // shuffle left
                    tmp += newval;
                }
                $('#numval').val(tmp);
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Setup functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function InitCurrentTheme(){
               var tmp=($("link[rel='stylesheet']").attr("href"))
//             alert ("Your stylesheet is: " + tmp);                              // DIAGNOSTIC
               switch (tmp) {
                case "jQTouch/themes/css/jqtouch.css":
                        $('#theme_1').addClass("selected");
                        break;
                case "jQTouch/themes/css/innsbruck.css":
                        $('#theme_2').addClass("selected");
                        break;
                case "jQTouch/themes/css/vanilla.css":
                        $('#theme_3').addClass("selected");
                        break;
                case "jQTouch/themes/css/apple.css":
                        $('#theme_4').addClass("selected");
                        break;
                case "jQTouch/themes/css/khaki.css":
                        $('#theme_5').addClass("selected");
                        break;
                case "jQTouch/themes/css/slate.css":
                        $('#theme_6').addClass("selected");
                        break;
                }
}

// setup.php alarm period hyper-links redirect here
            function SetupAppSend() {
               var tmp = "app setup:";
               tmp += $('#location').val() + ":";
               tmp += $('#durtxt').text();
               $('#retval').val(tmp);
               $('#SetupLoc').val($('#location').val());                         // push the new values back up a level
               $('#SetupDur').val($('#durtxt').text());
               ajaxrequest('writedata.php','');                                  // doesn't matter where we send the data as long as
                                                                                 // it goes through 'readvars' to make it go
               return false;                                                     // returning false cancels the original hyper-link
            }

            function SetupEmailSend() {
               var tmp = "email setup:";
               tmp += $('#SMTP_server').val() + ":";
               tmp += $('#SMTP_port').val() + ":";
               tmp += $('#email_account').val() + ":";
               tmp += $('#email_pwd').val();
               $('#retval').val(tmp);
               $('#SetupEserv').val($('#SMTP_server').val());                    // push the new values back up a level
               $('#SetupEport').val($('#SMTP_port').val());
               $('#SetupEsend').val($('#email_account').val());
               $('#SetupEpass').val($('#email_pwd').val());
               ajaxrequest('writedata.php','');                                  // doesn't matter where we send the data as long as
                                                                                 // it goes through 'readvars' to make it go
               return false;                                                     // returning false cancels the original hyper-link
            }

            function SetupAppInit () {
                 $('#location').val($('#SetupLoc').val());                       // Ensure all data fields are current
                 $('#durtxt').text($('#SetupDur').val());
            }

            function SetupEmailInit () {
                 $('#SMTP_server').val($('#SetupEserv').val());                  // Ensure all data fields are current
                 $('#SMTP_port').val($('#SetupEport').val());
                 $('#email_account').val($('#SetupEsend').val());
                 $('#email_pwd').val($('#SetupEpass').val());
            }

            function Defaults(parm1) {
                $('#retval').val(parm1);
                ajaxrequest('setupscroll.php','setup');                           // this needs to be synchronous - we need all the new 
                                                                                  // config data to be transferred to the web page
                jQT.goTo('#setup', 'slideright');                                 // ...and go to task edit screen
                return false;                                                     // returning false cancels the original hyper-link
            }

            function SetupDuration(parm1) {                                       // passes the duration info back from the selector page
                $('#durtxt').text(parm1);
                jQT.goTo('#setup_app', 'slideright')
            }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Common functions...
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// sends data to a php file, via POST, and displays the received answer in DIV=tagID
            function ajaxrequest(php_file, tagID) {
                var request = null;
                request = get_XmlHttp();
                var the_data = 'retval=' + $('#retval').val();
                request.open("POST", php_file, true);
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.setRequestHeader("X-Requested-With", "XMLHttpRequest");                // TEST
                request.setRequestHeader("pragma", "no-cache");                                // TEST
                request.send(the_data);
                request.onreadystatechange = function () {
                    if (request.readyState == 4 && tagID != '') {
                        // only update the screen if we have specified the div - this allows asynchronous sends.
                        $("#" + tagID).html(request.responseText);
                    }
                }
            }
            // create the XMLHttpRequest object, according to browser
            function get_XmlHttp() {
                // create the variable that will contain the instance of the XMLHttpRequest object (initially with null value)
                var xmlHttp = null;
                if (window.XMLHttpRequest) {     // for Firefox, IE7+, Opera, Safari, ...
                    xmlHttp = new XMLHttpRequest();
                }
                else if (window.ActiveXObject) {  // for Internet Explorer 5 or 6 
                    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                return xmlHttp;
            }
            function getCookie(cname) {
                   var name = cname + "=";
                   var ca = document.cookie.split(';');
                   for (var i = 0; i < ca.length; i++) {
                      var c = ca[i].trim();
                      if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
                   }
                   return "";
            }

// The jQT MenuSheet seems to work OK with swipes - but I'm using a back buttons instead.
// This kinda makes the browser just go back one through its history, so all the jQuery updates and stuff get lost, particularly
// the bit that removes the 'class=active' from the menu links. So the browser just goes back one and leaves the menu options in
// their active (or highlighted) state. So this routine does a bit of manual jiggery pokery and resets all the menu items.
// Also it should be called using the 'back2div' class to force a jump to the menu screen.
function ResetMenu(){
    $('#menu1').removeClass("active");
    $('#menu2').removeClass("active");
    $('#menu3').removeClass("active");
    $('#menu4').removeClass("active");
    $('#menu5').removeClass("active");
    $('#menu6').removeClass("active");
    $('#menu7').removeClass("active");
    $('#menu8').removeClass("active");
    return false;
    }
