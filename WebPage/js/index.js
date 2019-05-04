// Global variables...
    var tmp;
    var userHandler = {
        username : '',
        status : ''
    }
    var chartPeriod = 1;            // default chart period = 1 day
    var startDate = new Date(new Date().setHours(0,0,0,0));                // midnight last night
    var endDate = new Date(new Date().setHours(24,0,0,0));                 // midnight tonight
    var tickSize = 4;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// User ID editor functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $(document).on('click', '#NewUser', function() {
        Num = $('#UserCount').val();
        tmp = parseFloat(Num) + 1;                                              // force maths addition rather than concatenate
        $("#accountText").html('Edit User ID ' + tmp);
        tmp = tmp-1;
        $("#AccountName").val($('#AccountName_' + tmp).val());
        $("#AccountEmail").val($('#AccountEmail_' + tmp).val());
        $('#userEditor').popup('open', { positionTo: origin, transition: "pop" });
    });

    $(document).on('click', '.userID', function(e) {                           // user ID editor
        var lineID = this.id
        var lineNum = lineID.replace('account_', '');                          // extract the user ID number
        tmp = parseFloat(lineNum) + 1;                                         // force maths addition rather than concatenate
        $("#accountText").html('Edit User ID ' + tmp);
        tmp = tmp-1;
        $("#AccountName").val($('#AccountName_' + tmp).val());
        $("#AccountEmail").val($('#AccountEmail_' + tmp).val());
        $('#userEditor').popup('open', { positionTo: origin, transition: "pop" });
    });

    $(document).on('click', '#cancelUser', function() { 
        $('#PWordCollapsible').collapsible( "collapse" );                        // reset button
        $(".ui-collapsible-content", '#PWordCollapsible').slideUp();             // collapse data fields
        $("#userEditor").popup("close");
    });

    $(document).on('click', '#saveUser', function() {
    // create the data string to send to the alarm service before you go go..
        var lineNum = $("#accountText").html();
        lineNum = lineNum.replace('Edit User ID ', '');                        // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                     // convert to integer and bump value down
        if ($('#Pword1').val() != '') {                                        // execute this code if we have a password
            var $tmp1 = $('#Pword1').val();
            var $tmp2 = $('#Pword2').val();
            if ($tmp1 != $tmp2) {
                alert("The passwords do not match.\nPlease try again.");
                $('#Pword1').val('');
                $('#Pword2').val('');
                $('#PWordCollapsible').collapsible( "collapse" );              // reset button
                $(".ui-collapsible-content", '#PWordCollapsible').slideUp();   // collapse data fields
                return false;                                                  // cancel operation
            } else {
                tmp = 'user pwd:'+ (lineNum) + ':';
                tmp += ($('#AccountName').val()) + ':';
                tmp += ($('#AccountEmail').val()) + ':';
                tmp += ($('#Pword1').val());
            }
        } else {                                                                 // execute this code if we don't have a password
            tmp = 'user cfg:'+ (lineNum) + ':';
            tmp += ($('#AccountName').val()) + ':';
            tmp += ($('#AccountEmail').val());
        }
        tmp = tmp.replace(/\ #/g,' \\#');                                    // ' #' (space hash) characters needs to be delimited
                                                                             // change any ' #' to ' \#'
        tmp = tmp.trim()                                                     // ...and get rid of any sneaky white space characters
        console.log(tmp);
        $('#retval').val(tmp);                                               // store data on page
        AjaxSend('AccessPageAjaxCall.php', 'access', 'accessList');          // send the data
        $("#userEditor").popup("close");
    });

    $(document).on('click', '#deleteUser', function() {
        var lineNum = $("#accountText").html();
        lineNum = lineNum.replace('Edit User ID ', '');                        // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                     // convert to integer and bump value down
        tmp = 'user del:'+ (lineNum) + ':';
        console.log(tmp);
        $('#retval').val(tmp);                                                 // store data on page
        AjaxSend('AccessPageAjaxCall.php', 'access', 'accessList');            // send the data
        $("#userEditor").popup("close");
    });

    $(document).on("pageshow", "#access", function(){
        $(".animateMe2 .ui-collapsible-heading-toggle").on("click", function (e) {
            var current = $(this).closest(".ui-collapsible");             
            if (current.hasClass("ui-collapsible-collapsed")) {
                $(".ui-collapsible-content", current).slideDown(300);
            } else {
                $(".ui-collapsible-content", current).slideUp(300);
            }
        });
    });

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Task editor functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $(document).on('click', '#newTask', function() {
        tmp = $('#taskCount').val();
        var lineNum = parseFloat(tmp) + 1;                                // force maths addition rather than concatenate
        $("#taskText").html('Edit task ' + lineNum);
        $("#taskGroup").val('New group');
        $('#hours_value').prop('selectedIndex', 0).change();
        $('#mins_value').prop('selectedIndex', 0).change();
        $('#dom_value').prop('selectedIndex', 31).change();               // default: last option on list
        $('#month_value').prop('selectedIndex', 12).change();
        $('#wday_value').prop('selectedIndex', 7).change();
        $('#task_value').prop('selectedIndex', 2).change();               // default: standby mode
        $('#switch_value').prop('checked', false ).flipswitch('refresh');
        $('#taskEditor').popup('open', { positionTo: origin, transition: "pop" });
    });

    $(document).on('click', '.task', function(e) {               // task editor
        var lineID = this.id
        var lineNum = lineID.replace('task_', '');               // extract the task number
        tmp = parseFloat(lineNum) + 1;                           // force maths addition rather than concatenate
        $("#taskText").html('Edit task ' + tmp);
        $("#taskGroup").val($("#group_" + (lineNum)).val());
        if (isNaN($("#minutes_" + (lineNum)).val())) {
            $('#mins_value').prop('selectedIndex', 60).change(); // Not a number, must be an '*' - force selector to last option in list
        } else {
            $('#mins_value').prop('selectedIndex', ($("#minutes_" + (lineNum)).val())).change();  // Is a number
        }
        if (isNaN($("#hours_" + (lineNum)).val())) {
            $('#hours_value').prop('selectedIndex', 24).change();
        } else {
            $('#hours_value').prop('selectedIndex', ($("#hours_" + (lineNum)).val())).change();
        }
        if (isNaN($("#dom_" + (lineNum)).val())) {
            $('#dom_value').prop('selectedIndex', 31).change();
        } else {
            $('#dom_value').prop('selectedIndex', ($("#dom_" + (lineNum)).val()-1)).change();
        }
        if (isNaN($("#month_" + (lineNum)).val())) {
            $('#month_value').prop('selectedIndex', 12).change();
        } else {
            $('#month_value').prop('selectedIndex', ($("#month_" + (lineNum)).val()-1)).change(); // bump Month number as Jan=0 in selector.
        }
        var tmp = $('#wday_' + (lineNum)).val();
        if (isNaN(tmp)) {
            if (tmp.indexOf("*") >= 0) { $('#wday_value').prop('selectedIndex', 7).change(); }
            if (tmp.indexOf("1-5") >= 0) { $('#wday_value').prop('selectedIndex', 8).change(); }
            if (tmp.indexOf("6-7") >= 0) { $('#wday_value').prop('selectedIndex', 9).change(); }
        } else {
            $('#wday_value').prop('selectedIndex', ($("#wday_" + (lineNum)).val()-1)).change(); // bump weekday number as Mon=0 in selector.
        }
        if ($.isNumeric($("#tasknum_" + (lineNum)).val())) {
            $('#task_value').prop('selectedIndex',($("#tasknum_" + (lineNum)).val())).change()
        }
        if ($("#status_" + (lineNum)).val() == 'On'){ $('#switch_value').prop('checked', true ).flipswitch('refresh'); }   // switch goes on
        else                                        { $('#switch_value').prop('checked', false ).flipswitch('refresh'); }  // switch goes off

        $('#taskEditor').popup('open', { positionTo: origin, transition: "pop" });
        return false;
    });

    $(document).on('click', '#cancelTask', function() { 
        $("#taskEditor").popup("close");
    });

    $(document).on('click', '#saveTask', function() {
        // create the data string to send to the alarm service before you go go..
        var lineNum = $("#taskText").html();
        lineNum = lineNum.replace('Edit task ', '');                            // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                      // convert to integer and bump value down
        tmp = 'edit cron:'+ (lineNum) + ':';
        tmp += ($('#taskGroup').val()) + ':';
        tmp += ($('#mins_value').val()) + ':';
        tmp += ($('#hours_value').val()) + ':';
        tmp += ($('#dom_value').val()) + ':';
        tmp += ($('#month_value').val()) + ':';
        tmp += ($('#wday_value').val()) + ':';                                   // substitute text value from array
        tmp += ($('#task_value').val()) + ':';
        if ($('#switch_value').is(":checked")) { tmp += "On"; }
        else { tmp += "Off"; }
        tmp = tmp.replace(/\ #/g,' \\#');                                        // ' #' (space hash) characters needs to be delimited
                                                                                 // change any ' #' to ' \#'
        tmp = tmp.trim()                                                         // ...and get rid of any sneaky white space characters
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('TaskPageAjaxCall.php', 'schedule', 'taskList');               // send the data
        $("#taskEditor").popup("close");
    });

    $(document).on('click', '#deleteTask', function() {
        var lineNum = $("#taskText").html();
        lineNum = lineNum.replace('Edit task ', '');                            // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                      // convert to integer and bump value down
        tmp = 'delete task:'+ (lineNum) + ':';
        console.log(tmp);
        $('#retval').val(tmp);                                                  // store data on page
        AjaxSend('TaskPageAjaxCall.php', 'schedule', 'taskList');               // send the data
        $("#taskEditor").popup("close");
    });

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Radiator editor functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $(document).on('click', '#newRadiator', function() {
        Num = $('#radiatorCount').val();
        InitRdtrVals(Num);
        $('#RadiatorEditor').popup('open', { positionTo: origin, transition: "pop" });
    });

    $(document).on('click', '#cancelRadiator', function() { 
        setTimeout(function(){
            $("#RadiatorEditor").popup("close");
        },1);
    });

    $(document).on('click', '#deleteRadiator', function() {
        var lineNum = $("#RheaderText").html();
        lineNum = lineNum.replace('Radiator ', '');                             // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                       // convert to integer and bump value down
        tmp = 'rdtr del:'+ (lineNum);
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('HeatPageAjaxCall.php', 'heating', 'heatList');                 // send the data
        $("#RadiatorEditor").popup("close");
    });

    $(document).on('click', '#saveRadiator', function() {
    // create the data string to send to the alarm service before you go go..
        var lineNum = $("#RheaderText").html();
        lineNum = lineNum.replace('Radiator ', '');                              // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                       // convert to integer and bump value down
        // create the data string to send to the alarm service before you go go..
        tmp = 'rdtr cfg:'+ (lineNum) + ':';
        tmp += ($('#rdtrGroup').val()) + ':';
        tmp += ($('#rdtrName').val()) + ':';
        tmp += ($('#rdtrAddress').val()) + ':';
        tmp += ($('#rdtrHi').val()) + ':';
        tmp += ($('#rdtrLow').val()) + ':';
        tmp = tmp.replace(/\ #/g,' \\#');                                        // ' #' (space hash) characters needs to be delimited
                                                                                 // change any ' #' to ' \#'
        tmp = tmp.trim()                                                         // ...and get rid of any sneaky white space characters
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('HeatPageAjaxCall.php', 'heating', 'heatList');                 // send the data
        $("#RadiatorEditor").popup("close");
    });

    $(document).on('click', '.radiator', function() {
        var lineID = this.id
        var lineNum = lineID.replace('radiator_', '');   // extract the radiator number
        InitRdtrVals(lineNum);
        $('#RadiatorEditor').popup('open', { positionTo: origin, transition: "pop" });
        return false;
    });

    function InitRdtrVals(Num){
    // initialise the values for the Radiator editor screen...
        tmp = parseFloat(Num) + 1;                             // force maths addition rather than concatenate
        $('#RheaderText').html('Radiator ' + tmp);
        $('#rdtrGroup').val($('#radiatorGroup_' + (Num)).val());
        $('#rdtrName').val($('#radiatorName_' + (Num)).val());
        $('#rdtrAddress').prop('selectedIndex', ($("#radiatorAddress_" + (Num)).val())).change();  // only ever a number
        tmp = $("#radiatorHigh_" + (Num)).val();                                                   // can be text or number
        $('#rdtrHi').val(tmp).selectmenu('refresh',true);
        tmp = $("#radiatorLow_" + (Num)).val();                                                    // can be text or number
        $('#rdtrLow').val(tmp).selectmenu('refresh',true);
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Switch editor functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $(document).on('click', '#newSwitch', function() {
        Num = $('#switchCount').val();
        InitSwitchVals(Num);
    });

    $(document).on('click', '.switch', function() {
        var lineID = this.id
        var lineNum = lineID.replace('switch_name_', '');   // extract the switch number
        InitSwitchVals(lineNum);
        $('#PowerEditor').popup('open', { positionTo: origin, transition: "pop" });
        return false;
    });

    $(document).on('click', '#cancelSwitch', function() { 
        setTimeout(function(){
            $("#PowerEditor").popup("close");
        },1);
    });

    $(document).on('click', '#deleteSwitch', function() {
        var lineNum = $("#headerText").html();
        lineNum = lineNum.replace('Edit switch ', '');                           // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                       // convert to integer and bump value down
        tmp = 'rcon del:'+ (lineNum);
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('PowerPageAjaxCall.php', 'power', 'switchList');                // send the data
        $("#PowerEditor").popup("close");
    });

    $(document).on('click', '#saveSwitch', function() {
    // create the data string to send to the alarm service before you go go..
        var lineNum = $("#headerText").html();
        lineNum = lineNum.replace('Edit switch ', '');                           // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                       // convert to integer and bump value down
        tmp = 'rcon cfg:'+ (lineNum) + ':';
        tmp += ($('#switchGroup').val()) + ':';
        tmp += ($('#switchName').val()) + ':';
        tmp += ($('#switchAddress').val()) + ':';
        tmp += ($('#switchChannel').val()) + ':';
        tmp += ($('#switchAlrmAction').val()) + ':';
        tmp += $('#switchHK').val();
        tmp = tmp.replace(/\ #/g,' \\#');                                        // ' #' (space hash) characters needs to be delimited
                                                                                 // change any ' #' to ' \#'
        tmp = tmp.trim()                                                         // ...and get rid of any sneaky white space characters
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('PowerPageAjaxCall.php', 'power', 'switchList');                // send the data
        $("#PowerEditor").popup("close");
    });

    function InitSwitchVals(Num){
    // initialise the values for the Power editor screen...
        tmp = parseFloat(Num) + 1;                             // force maths addition rather than concatenate
        $("#headerText").html('Edit switch ' + tmp);
        $("#switchName").val($("#switch_name_" + (Num)).text());
        $("#switchGroup").val($("#switch_group_" + (Num)).val());
        $('#switchAddress').prop('selectedIndex', ($('#switch_address_' + (Num)).val())).change();
        $('#switchChannel').prop('selectedIndex', ($("#switch_channel_" + (Num)).val())).change();
        var tmp = $('#switchAlrmAction_' + (Num)).val();
        if (tmp.indexOf("On") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 0).change(); }
        if (tmp.indexOf("Off") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 1).change(); }
        if (tmp.indexOf("None") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 2).change(); }
        var tmp = $('#switch_HK_' + (Num)).val();
        if (tmp.indexOf("Outlet") >= 0) { $('#switchHK').prop('selectedIndex', 0).change(); }
        if (tmp.indexOf("Light") >= 0) { $('#switchHK').prop('selectedIndex', 1).change(); }
        if (tmp.indexOf("Fan") >= 0) { $('#switchHK').prop('selectedIndex', 2).change(); }
        if (tmp.indexOf("None (do not export)") >= 0) { $('#switchHK').prop('selectedIndex', 3).change(); }
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Security editor functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $(document).on('click', '#newZone', function() {
        Num = $('#zoneCount').val();
        InitZoneVals(Num);
    });

    $(document).on('click', '.zone', function(e) {
        var lineID = this.id
        var lineNum = lineID.replace('zone_', '');   // extract the switch number
        InitZoneVals(lineNum);
        $('#SecurityEditor').popup('open', { positionTo: origin, transition: "pop" });
        return false;
    });

    $(document).on('click', '#cancelZone', function() { 
        setTimeout(function(){
            $("#SecurityEditor").popup("close");
        },1);
    });

    $(document).on('click', '#deleteZone', function() {
        var lineNum = $("#headerText").html();
        lineNum = lineNum.replace('Edit zone ', '');                             // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                       // convert to integer and bump value down
        tmp = 'zcon del:'+ (lineNum);
        console.log(tmp);
        $('#retval').val(tmp);                                                   // store data on page
        AjaxSend('SecurityPageAjaxCall.php', 'security', 'zoneList');            // send the data
            $("#SecurityEditor").popup("close");
    });

    $(document).on('click', '#saveZone', function() {
    // create the data string to send to the alarm service before you go go..
        var lineNum = $("#headerText").html();
        lineNum = lineNum.replace('Edit zone ', '');                                // remove text to leave just the number
        lineNum = parseFloat(lineNum) - 1;                                          // convert to integer and bump value down
    // create the data string to send to the alarm service before you go go..
        tmp = 'zcon cfg:'+ (lineNum) + ':';
        tmp += $('input[name=radio-zoneType]:checked').val().toLowerCase() + ':';   // value of Alarm/Tamper radio button
        tmp += $('#EditZoneName').val() + ':';
        if ($('#checkbox-DM').is(":checked")) { tmp += 'on:'; }
        else { tmp += 'off:'; }
        if ($('#checkbox-NM').is(":checked")) { tmp += 'on:'; }
        else { tmp += 'off:'; }
        if ($('#checkbox-Ch').is(":checked")) { tmp += 'on:'; }
        else { tmp += 'off:'; }
        tmp += $('#EditCircuitNum').val();
        tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                                                                       // change any ' #' to ' \#'
        tmp = tmp.trim()                                               // ...and get rid of any sneaky white space characters
        console.log(tmp);
        $('#retval').val(tmp);
        AjaxSend('SecurityPageAjaxCall.php', 'security', 'zoneList');            // send the data
            $("#SecurityEditor").popup("close");
    });

    function InitZoneVals(Num){
        var fiddleFactor;
    // initialise the values for the editor screen...
        tmp = parseFloat(Num) + 1;                             // force maths addition rather than concatenate
        $("#headerText").html('Edit zone ' + tmp);
        $("#EditZoneName").val($("#ZoneName" + (Num)).val());
        $('#EditCircuitNum').prop('selectedIndex', ($('#ZoneCrct' + (Num)).val())).change();
    // initialise the check boxes...
        if ($('#ZoneDM' + Num).val().toLowerCase() == 'on') { $('#checkbox-DM').prop('checked', true).checkboxradio('refresh'); }
        else { $('#checkbox-DM').prop('checked', false).checkboxradio('refresh'); }
        if ($('#ZoneNM' + Num).val().toLowerCase() == 'on') { $('#checkbox-NM').prop('checked', true).checkboxradio('refresh'); }
        else { $('#checkbox-NM').prop('checked', false).checkboxradio('refresh'); }
        if ($('#ZoneCh' + Num).val().toLowerCase() == 'on') { $('#checkbox-Ch').prop('checked', true).checkboxradio('refresh'); }
        else { $('#checkbox-Ch').prop('checked', false).checkboxradio('refresh'); }
    // initialise the alarm/tamper selector...
        if ($('#ZoneType' + Num).val().toLowerCase() == 'alarm') {
            $('input:radio[name="radio-zoneType"]').filter('[value="Alarm"]').prop("checked",true).checkboxradio("refresh");
            $('input:radio[name="radio-zoneType"]').filter('[value="Tamper"]').prop("checked",false).checkboxradio("refresh");
            $('#HideMe').css({"display":"block"});        // display the alarm options
            $('#SecurityEditor').css({"top":"-25px"});    // force the screen position
        } else {
            $('input:radio[name="radio-zoneType"]').filter('[value="Alarm"]').prop("checked",false).checkboxradio("refresh");
            $('input:radio[name="radio-zoneType"]').filter('[value="Tamper"]').prop("checked",true).checkboxradio("refresh");
            $('#HideMe').css({"display":"none"});         // hide the alarm options
            $('#SecurityEditor').css({"top":"-100px"});   // force the screen position
        }
    }

    function ShowHide(parm,speed) {
        if (parm == "alarm") {
            $('#HideMe').show(speed);
            $('#HideMeLabel').html('Options for this alarm zone:')
        } else {
            $('#HideMe').hide(speed);
            $('#HideMeLabel').html('Tamper zones do not have options.')
        }
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Code to implement the Default buttons...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function Defaults(parm1) {
    console.log(parm1);
    $('#retval').val(parm1);
    AjaxSend('SettingsPageAjaxCall.php', 'settings', 'settingsList');
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Code to implement the Security buttons...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $(document).on('click', '#SetButton', function() {
       if ($(this).closest('.security-button-wrapper').hasClass('show')) {
            $( "#AlarmPopup" ).popup( "open" );
       }
    });

    $(document).on('click', '#TestButton', function() {
       if ($(this).closest('.security-button-wrapper').hasClass('show')) {
            $( "#TestPopup" ).popup( "open" );
        }
    });

    $(document).on('click', '#ResetButton', function() {
       if ($(this).closest('.security-button-wrapper').hasClass('show')) {
            SendCommand('reset');
        }
    });

    function ChangeAlarmMode(command,message){
       $('#retval').val(command);
       AjaxSend('SecurityPageAjaxCall.php','security','');
    }

    function SendCommand(Parm) {
        $('#retval').val(Parm);
        AjaxSend('SecurityPageAjaxCall.php','security','');
    }

    $(document).on("pagebeforeshow", function(){
        var thispage = $.mobile.activePage[0].id;
        if (thispage == "security") {
        // only enable buttons for the Security page
            $('.security-button-wrapper').addClass('show');
        }
        else {
        // disable buttons on all other page
            $('.security-button-wrapper').removeClass('show');
        }
    });
// end of code for Security buttons.

// Code to implement the HomeBridge buttons...
    function HomeBridge(parameter) {
        var kids = $('#settings').find('.ui-collapsible-content');  // find all collapsibles on this page...
        $(kids).slideUp(300);                                       // ... and collapse them.
        tmp = "Confirm " + parameter + " HomeBridge ?";
        $('#MultiMsg').text(tmp);
        $('#MultiBtn').text(parameter);
        $("#MultiPopup").popup("open", { transition: 'slideup' });
        tmp = "HomeBridge:" + parameter;
        $('#retval').val(tmp);                                // set up the value ready to go
                                                              // doesn't matter where we send the data as long as
    }                                                         // it goes through 'readvars' to make it go
// end of code for HomeBridge buttons.

$(document).on("pageshow", "#settings", function(){
    $(".animateMe .ui-collapsible-heading-toggle").on("click", function (e) {
        var current = $(this).closest(".ui-collapsible");
        var parentLine = $(this).closest('\h3');                           // find the parent line
        var lineText = parentLine.text();
        lineText = lineText.replace(' click to expand contents', '');
        lineText = lineText.replace(' click to collapse contents', '');
        if (current.hasClass("ui-collapsible-collapsed")) {
            //collapse all others and then expand this one
            $(".ui-collapsible").not(".ui-collapsible-collapsed").find(".ui-collapsible-heading-toggle").click();
            $(".ui-collapsible-content", current).slideDown(300);
        } else {
            $(".ui-collapsible-content", current).slideUp(300);
                    switch (lineText) {
                        case 'Email':
                            // create the data string to send to the alarm service before you go go..
                            tmp = 'email setup:';
                            tmp += $('#SMTP_server').val() + ':';
                            tmp += $('#SMTP_port').val() + ':';
                            tmp += $('#SMTP_account').val() + ':';
                            tmp += $('#SMTP_passwd').val();
                            $('#MultiMsg').text('Confirm save new email settings ?');
                            $('#MultiBtn').text('Save');
                            $("#MultiPopup").popup("open", { transition: 'slideup' });
                            break;
                        case 'Application':
                            // create the data string to send to the alarm service before you go go..
                            tmp = 'app setup:';
                            tmp += $('#SetupLoc').val() + ':';
                            tmp += $('#SetupDur').val() + ':';
                            $('#MultiMsg').text('Confirm save new application settings ?');
                            $('#MultiBtn').text('Save');
                            $("#MultiPopup").popup("open", { transition: 'slideup' });
                            break;
                    }
                    tmp = tmp.replace(/\ #/g,' \\#');            // ' #' (space hash) characters needs to be delimited before sending
                    console.log(tmp);                            // change any ' #' to ' \#' and get rid of any sneaky white space characters
                    $('#retval').val(tmp);
        }
    });
});
    // Login screen helper scripts...
    // Check for cookies left from previous session. If found, use them to initialise the login dialogue.
    // So if a One Time Access Code has been left on the phone, the 'remember me' button should be set to checked, and the user account
    // input field should be pre-populated.
    $(document).on("pagebeforecreate",function(){
        var otac = readCookie('otac');
        var username = readCookie('username');
        if (otac != null) {
            $("#rememberme").prop("checked", true);             // This is just cosmetic and makes it look like credentials 
            $("#uname").val(username);                          // have been remembered. In reality, no credentials are stored 
            $("#pword").val("************");                    // on the RasPi or iPhone.
        } else { $("#rememberme").prop("checked", false); }
    });
    //JQuery doesn't directly support cookies, so using JavaScript and a bit of sticky tape to retrieve values...
    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // other stuff...

    $(document).on("pageinit", function(){
      // switchChange();
       $('#sidePanel').enhanceWithin().panel();
       $('#AlarmPopup').enhanceWithin().popup();
       $('#TestPopup').enhanceWithin().popup();
       $('#MultiPopup').enhanceWithin().popup();
    });

// Functions to handle the scroll to top button...
$(function(){
    $(document).on( 'scroll', function(){
        var thispage = $.mobile.activePage[0].id;
        if ($(window).scrollTop() > 100) {
            $('.scroll-top-wrapper').addClass('show');
            if (thispage == "security") { $('.security-button-wrapper').removeClass('show'); }
        } else {
            $('.scroll-top-wrapper').removeClass('show');
            if (thispage == "security") { $('.security-button-wrapper').addClass('show'); }
        }
    });
    $('.scroll-top-wrapper').on('click', scrollToTop);
});

function scrollToTop() {
    var thisPage = $.mobile.activePage.attr('id');
    var target = $('#'+thisPage).find("P[data-role='screenSpacer']").prop('scrollHeight');     // get height of current header
    if (thisPage = 'about') { $('html, body').stop().animate({ scrollTop : target - 43 }, 'slow'); }
    else { $('html, body').stop().animate({ scrollTop : target - 24 }, 'slow'); }
}

    $(document).on('change', '.powerSwitch', function(e) {
    // called when power switches change state and we need to send data back to the BASH service.
        var switchNum = $(this).val();
        e.stopPropagation();                     // don't really know why, but some event propagates resulting in the request getting
        e.stopImmediatePropagation();            // sent twice, so need to stop propagation
        tmp = "rcon swtch:" + switchNum + ":";
        if($(this).is (':checked')) { tmp += "On";
                                      console.log("Switch " + switchNum + ": On");  }
        else                        { tmp += "Off";
                                      console.log("Switch " + switchNum + ": Off"); }
        $('#retval').val(tmp);
        AjaxSend('readvars.php');                // send update to the service, without refreshing the screen
    });                                          // doesn't matter where we send it, as long as it goes through

    $(document).on('change', '.rdtrSwitch', function(e) {
    // called when radiator switches change state and we need to send data back to the BASH service.
        var switchNum=$(this).val();
        e.stopPropagation();                     // don't really know why, but some event propagates resulting in the request getting
        e.stopImmediatePropagation();            // sent twice, so need to stop propagation
        tmp = "rdtr swtch:" + switchNum + ":";
        if($(this).is (':checked')) { tmp += "On";
                                      console.log("Radiator " + switchNum + ": On");  }
        else                        { tmp += "Off";
                                      console.log("Radiator " + switchNum + ": Off"); }
        $('#retval').val(tmp);
        AjaxSend('readvars.php');                // send update to the service, without refreshing the screen
    });                                          // doesn't matter where we send it, as long as it goes through
    
$(document).on('click', '.submit', function() { // catch the form's submit event
var tmp;
var activePage = $(':mobile-pagecontainer').pagecontainer('getActivePage');
if(activePage.attr('id') === 'login') {
        userHandler.username = $('#username').val();
        // Send data to server through the Ajax call
        // action is functionality we want to call and outputJSON is our data
        var formData = { "username": $('#uname').val(), "password": $('#pword').val(), "rememberme": $('#rememberme').is(":checked") };
//                      data: {action : 'authorization', formData : $('#check-user').serialize()},
        $.ajax({url: 'auth.php',
            data: formData,
            type: 'POST',
            async: 'true',
            dataType: 'json',
            beforeSend: function() {
                // This callback function will trigger before data is sent
//                          $.mobile.loading('show'); // This will show Ajax spinner
            },
            complete: function() {
                // This callback function will trigger on data sent/received complete
//                          $.mobile.loading('hide'); // This will hide Ajax spinner
            },
            success: function (result) {
                // Check if authorization process was successful
                $tmp = $('#logonMsg').html();
                switch (result.status) {
                    case 'Failed #1':
                        var $res = $tmp.replace("REPLACE ME !", "First failed logon attempt !");
                        $('#logonMsg').html($res);
                            $.mobile.changePage( "#loginFail", {
                                transition: "slide",
                                reverse: false
                            });
                        break;
                    case 'Failed #2':
                        var $res = $tmp.replace("First", "Second");
                        $('#logonMsg').html($res);
                            $.mobile.changePage( "#loginFail", {
                                transition: "slide",
                                reverse: false
                            });
                        break;
                    case 'Failed #3':
                        $.mobile.changePage( "#Banned", {
                            transition: "slideup",
                            reverse: false
                        });
                        break;
                    case 'Success':
                        AjaxGet('PowerPageAjaxCall.php','power');                  // load the first page
                        $('#power').trigger('create');                 // need to manually re-render the page
                            $.mobile.changePage( "#power", {
                                transition: "slide",
                                reverse: false
                            });
                        break;
                    default:
                }
            },
            error: function (result) {
                // This callback function will trigger on unsuccessful action
                alert('Network error has occurred please try again!');
            }
        });
        return false; // cancel original event to prevent form submitting
    }
    if(activePage.attr('id') === 'loginFail') {
            $('#uname').val('');
            $('#pword').val('');              // clear out the data
            $(":mobile-pagecontainer").pagecontainer("change", "#login", { transition: "slide", reverse: true });
            return false; // cancel original event to prevent form submitting
}
});
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajax functions. Three types of Ajax call are required...
//
// 1) Read without write. A PHP file is inserted into the DOM, no message is passed to the alarm service.
// 2) Write to alarm service, wait for a reply, and insert the reply into the DOM. Used when contents of a page have changed. ( Asynchronous write )
// 3) Write to alarm service without waiting for a reply. Used when contents of a page haven't changed much, such as
//      when flicking a switch. (Synchronous write)
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function AjaxGet(fileName,destination){
        $.mobile.loading("show", {
            theme: "b",
            text: "Loading...",
            textVisible: true,
            textonly: false
        });
        /* load panel into a variable */
        $.get(fileName, function (data) {
            // note to self... YEUCH - need to find a better way of doing this...
            // but its working for now, so I'm leaving it alone.
            $('#'+destination).empty();
            $('#'+destination).append(data);
            $('#'+destination).trigger('create');                                    // kick the collapsibles
//            collapsibleSlowScroll();                                                 // and kick the slow scroll too
        }, "html").done(function () {
        $.mobile.loading("hide");
        // find and kick the listview and manually re-trigger the creation of the auto divider info
        $('#'+destination).children("ul").listview({
            autodividers: true,
            autodividersSelector: function (li) {
                var out = li.attr('headr');
                return out;
            }
        }).listview('refresh');
        // and any switches will also be bust by the Ajax call, so re-init them too
//        switchChange();
        scrollToTop();
        });
    }

// Data on the web page has changed, so send the current contents of the retval field.
    function AjaxSend(php_file, tagID, listID) {
        request = $.ajax({
        url: php_file,
        type: "post",
        data: 'retval=' + $('#retval').val(),
        beforeSend: function () {
            if ( tagID != null ) {
            // only show the spinner if we are going to update the page
            // this allows on/off switches to be run asynchronously, improving the on screen performance
                $.mobile.loading('show'); }
            },
    });

    // Callback handler that will be called on success...
    request.done(function (response, textStatus, jqXHR){
        console.log("jQuery Ajax POST success.");
        $.mobile.loading('hide');
        var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');         // get height of current header
        if ( tagID != null) {
           // If a tagID is specified, any returned data is written to this location.
           $('#' + tagID).html(response);
           $('#'+ tagID).trigger('create');                                    // kick the collapsibles
        }
        if ( listID == 'settingsList'){
            // there isn't a listview on this page, but need to kick the collapsible
            $('#SettingsPageAjaxCall').trigger('create');                              // kick the collapsible and menu
        }
        if ( listID == 'accessList'){
            // there isn't a listview on this page, but need to kick the collapsible
            $('#AccessPageAjaxCall').trigger('create');                               // kick the collapsible and menu
            // and re-bind the slow scroll to the 'new' collapsible...
            $(".animateMe2 .ui-collapsible-heading-toggle").on("click", function (e) {
                var current = $(this).closest(".ui-collapsible");             
                if (current.hasClass("ui-collapsible-collapsed")) {
                    $(".ui-collapsible-content", current).slideDown(300);
                } else {
                    $(".ui-collapsible-content", current).slideUp(300);
                }
            });
        }
        if ( listID == 'zoneList'){
            // also need to kick the collapsible
            $('#SecurityPageAjaxCall').trigger('create');                              // kick the collapsible and menu
        }
        else {
            // If a listID is specified, the corresponding listView will be refreshed.
            // The Listview and associated auto-dividers need a right good kicking to get them working again.
            $('#'+tagID).trigger('create');                              // kick the collapsible and menu
            // also have to manually re-trigger the creation of the auto divider info ( duplicate of code already run on pageinit)
            $('#' + listID).listview({
                autodividers: true,
                autodividersSelector: function (li) {
                    var out = li.attr('headr');
                    return out;
                }
            }).listview('refresh');
            if (typeof listID !== 'undefined') {
                // and any switches will also be bust by the Ajax call, so re-init them too
//                if (listID == 'switchList'){ switchChange(); }
//                if (listID == 'radiatorList'){ switchChange(); }
                //now scroll up to top of listview
                scrollToTop();
                }
        return false;
            }
    });

    // Callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        console.error("jQuery Ajax POST fail."+
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Chart functions...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
    $(document).on('click', '#ViewButton1', function() {
        // week view.
        chartPeriod = (24*60*60*1000) * 7;                             //1 week
        tickSize = 24;
        endDate.setTime(Date.now());                                   // Now
        endDate.setHours(24,0,0,0);                                    // midnight tonight
        startDate.setDate(endDate.getDate() - 1);
        startDate.setTime(endDate.getTime() - chartPeriod);
        $(".ui-header .ui-title").text('Temp');
        plotAccordingToChoices();
    });

    $(document).on('click', '#ViewButton2', function() {
        // 24 hour view.
        chartPeriod = (24*60*60*1000) * 1;                             //1 day
        tickSize = 4;
        endDate.setTime(Date.now());                                   // Now
        endDate.setHours(24,0,0,0);                                    // midnight tonight
        startDate.setDate(endDate.getDate() - 1);
        startDate.setTime(endDate.getTime() - chartPeriod);
        tmp = startDate.getDate() + '/' + (startDate.getMonth()+1) + '/' +  startDate.getFullYear();
        $(".ui-header .ui-title").text(tmp);
        plotAccordingToChoices();
    });

    $(document).on('click', '#ViewButton3', back1day);

    $(document).on('click', '#ViewButton4', forward1day);

    $(document).on( "swiperight", '#chart', back1day);
 
    $(document).on( "swipeleft", '#chart', forward1day);

    function back1day() {
        // Back 24 hours.
        chartPeriod = (24*60*60*1000) * 1;                             //1 day
        tickSize = 4;
        endDate.setDate(endDate.getDate() - 1);
        startDate.setTime(endDate.getTime() - chartPeriod);
        tmp = startDate.getDate() + '/' + (startDate.getMonth()+1) + '/' +  startDate.getFullYear();
        $(".ui-header .ui-title").text(tmp);
        plotAccordingToChoices();
    }

    function forward1day() {
        // Forward 24 hours.
        chartPeriod = (24*60*60*1000) * 1;                             //1 day
        tickSize = 4;
        endDate.setDate(endDate.getDate() + 1);
        startDate.setTime(endDate.getTime() - chartPeriod);
        tmp = startDate.getDate() + '/' + (startDate.getMonth()+1) + '/' +  startDate.getFullYear();
        $(".ui-header .ui-title").text(tmp);
        plotAccordingToChoices();
    }
    $(document).on("pageshow", "#graph", function(event,data){
        // insert checkboxes
        var choiceContainer = $("#choices");

        if ( typeof (datasets) == "undefined" ) {
            // no data has been found in the specified period.
            $("#graph").trigger("create");
            tmp = "No temperature data has been found.\n\n" +
                  "At least 3 data points are required\n" +
                  "to render the graph.\n\n" +
                  "Try again later.";
            alert (tmp);
            return;                    // trying to plot no data causes FLOT to lock up, so don't go there.    
        }

        $.each(datasets, function(key, val) {
            choiceContainer.append("<br/><input type='checkbox' name='" + key +
                "' checked='checked' id='id" + key + "'></input>" +
                "<label2 for='id" + key + "'>"
                + val.label + "</label><br>");
        });
        choiceContainer.find("input").click(plotAccordingToChoices);   // bind click handler

        tmp = startDate.getDate() + '/' + (startDate.getMonth()+1) + '/' +  startDate.getFullYear();
        $(".ui-header .ui-title").text(tmp);
        plotAccordingToChoices();                                      // run function on page load
    });

    function plotAccordingToChoices() {
        // hard-code colour indices to prevent them from shifting as sensors are turned on/off
        var i = 0;
        $.each(datasets, function(key, val) {
            val.color = i;
            ++i;
        });

        var choiceContainer = $("#choices");
        var data = [];
        choiceContainer.find("input:checked").each(function () {
            var key = $(this).attr("name");
            if (key && datasets[key]) {
                data.push(datasets[key]);
            }
        });

        $.plot("#chart", data,
            { yaxes: [{ position: 'left',
                        axisLabel: 'Temperature' }],
              xaxes: [{ axisLabel: 'Time'}],
              yaxis: { min: 0 },
              xaxis: { mode: "time",
                       tickSize: [tickSize, "hour"],
                       timezone: "browser", // required to correct for daylight saving in graph.
                       min: (startDate.getTime()),
                       max: (endDate.getTime()),
                       twelveHourClock: false },
              legend: { show: true,
                        position: 'sw' }
        });
    }