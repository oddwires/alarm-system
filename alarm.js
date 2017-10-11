///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Generic interface functions.
// These look after navigating and editing the various JQuery Mobile ListView objects.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Global variables...
    var tmp;

    // Need to check for cookies left from previous session. If found, use them to initialise the logon dialogue.
    // So if a One Time Access Code has been left on the phone, the 'remember me' button should be set to checked, and the user account
    // input field should be pre-populated.
    $(document).on("pagebeforecreate",function(){
        var otac = readCookie('otac');
        var username = readCookie('username');
        if (otac != null) {
            $("#rememberme").prop("checked", true);             // This is just cosmetic and makes it look like credentials 
            $("#username").val(username);                       // have been remembered. In reality, no credentials are stored 
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

    $(document).on("change", "input[name=alarm-radio-choice]:radio", function (event, ui) {
//      alert($(this).val());                           // Debug
        initAlarmModeInfo($(this).val());
    });

    $(document).on("pageshow", "#security", function(event,data){
        // first time this page loads, we need to initialise the accompanying text
        var mode = $.trim($("#alarmMode").text());
        initAlarmModeInfo(mode);
    }); 

    function initAlarmModeInfo(alarmMode){
    // this can be called as the alarm mode changes, or as the security page initially loads.
        switch (alarmMode) {
            case 'Standby':
                $('#alarm-mode-info').text('Only tamper circuits will trigger the alarm. The alarm is effectively off.');
                $('#PopUpMsg').text('Confirm set alarm mode to Standby ?');
                break;
            case 'Day mode':
                $('#alarm-mode-info').text('All sensors are active. Provides maximum protection when the house is empty.');
                $('#PopUpMsg').text('Confirm set alarm to Day mode ?');
                break;
            case 'Night mode':
                $('#alarm-mode-info').text('Only peripheral sensors are active. Allows free movement within the house, but entry or exit will trigger the alarm.');
                $('#PopUpMsg').text('Confirm set alarm to Night mode ?');
                break;
        }
    }

    $(document).on("pageinit", function(){
       switchChange();
       $('#sidePanel').enhanceWithin().panel();
       $('#PopupDialog').enhanceWithin().popup();
       $('#DefaultDialog').enhanceWithin().popup();
   });
   
    // Function to add smooth scroll transition to the standard JQuery Mobile collapsible
    $(document).on("pagecreate", function(){
         collapsibleSlowScroll();
    });

$(document).on("pagecreate", "#page1", function(){
    $(".animateMe .ui-collapsible-heading-toggle").on("click", function (e) { 
        var current = $(this).closest(".ui-collapsible");             
        if (current.hasClass("ui-collapsible-collapsed")) {
            //collapse all others and then expand this one
            $(".ui-collapsible").not(".ui-collapsible-collapsed").find(".ui-collapsible-heading-toggle").click();
            $(".ui-collapsible-content", current).slideDown(slow);
        } else {
            $(".ui-collapsible-content", current).slideUp(slow);
        }
    });
});

    $(document).on('click', '#submit', function(){
        var formData = { "username": $('#username').val(), "password": $('#pword').val(), "rememberme": $('#rememberme').is(":checked") },
        request = $.ajax({
            type: "POST",
            url: '/login.php',
            cache: false,
            data: formData,
            beforeSend: function() {
                $.mobile.loading('show'); }
        });

        // Callback handler that will be called on success...
        request.done(function (response, textStatus, jqXHR){
            console.log("jQuery Ajax POST success.");
            $('#pageTwo').html(response);
            var headerText = $("#failedTitle").text();
            $('#pageTwo').trigger('create');                // need to manually re-render the failed.php page when returned
            // find and kick the listview and manually re-trigger the creation of the auto divider info
            $('#switchList').listview({
                autodividers: true,
                autodividersSelector: function (li) {
                    var out = li.attr('headr');
                    return out;
                }
            }).listview('refresh');
            
            // and any switches will also be bust by the Ajax call, so re-init them too
            switchChange();
            $.mobile.loading('hide');
            $.mobile.changePage("#pageTwo", {
                transition: "slide",
                reverse: false
                });
            scrollToTop();
            // align the top of the listview with the bottom of the header...
//            var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');  // get height of current header
//            var firstDiv = $.mobile.activePage.find('#topOfPage');
//            $('html, body').animate({
//                scrollTop: $(firstDiv).offset().top-headerHeight
//            }, '0');
        });
        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error("jQuery Ajax POST fail."+
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });
    });

    function collapsibleSlowScroll(){
        $(".animateMe .ui-collapsible-heading-toggle").on("click", function () {
//          var tmp;
            var parentLine = $(this).closest('\h3');                                  // find the parent line
            var lineText = parentLine.text();
            lineText = lineText.replace(' click to expand contents', '');
            lineText = lineText.replace(' click to collapse contents', '');
            if (lineText.indexOf('Alarm mode') != -1) { lineText = 'Alarm mode'; }
            $('.scroll-top-wrapper').removeClass('show');
            var current = $(this).closest('.ui-collapsible');
            if (current.hasClass("ui-collapsible-collapsed")) {
                //collapse all others and then expand this one
                $(".ui-collapsible").not(".ui-collapsible-collapsed").find(".ui-collapsible-heading-toggle").click();
                $(".ui-collapsible-content", current).slideDown('slow');
            } else {
                $(".ui-collapsible-content", current).slideUp('slow');
                    // customise and show the action sheet...
                    switch (lineText) {
                        case 'Email':
                            // create the data string to send to the alarm service before you go go..
                            tmp = 'email setup:';
                            tmp += $('#SMTP_server').val() + ':';
                            tmp += $('#SMTP_port').val() + ':';
                            tmp += $('#SMTP_account').val() + ':';
                            tmp += $('#SMTP_passwd').val();
                            $('#PopUpMsg').text('Save new email settings ?');
                            $('#PopUpBtn').text('Save');
                            $("#PopupDialog").popup("open", { transition: 'slideup' });
                            break;
                        case 'Application':
                            // create the data string to send to the alarm service before you go go..
                            tmp = 'app setup:';
                            tmp += $('#SetupLoc').val() + ':';
                            tmp += $('#SetupDur').val() + ':';
                            $('#PopUpMsg').text('Save new application settings ?');
                            $('#PopUpBtn').text('Save');
                            $("#PopupDialog").popup("open", { transition: 'slideup' });
                            break;
                        case 'Alarm mode':
                            var newMode = $("input[name=alarm-radio-choice]:radio:checked").val();
                            tmp = 'mode:' + newMode;
                            $('#PopUpMsg').text('Set alarm to ' + newMode + ' ?');
                            $('#PopUpBtn').text('OK');
                            $("#PopupDialog").popup("open", { transition: 'slideup' });
                            break
                    }
                    tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                    console.log(tmp);                                              // change any ' #' to ' \#' and get rid of any sneaky white space characters
                    $('#retval').val(tmp);
            }
        });
    }

    function switchChange(){
     // called when power or radiator switches change state and we need to send data back to the BASH service.
     $(".powerSwitch").change(function(e){
            e.stopPropagation();                 // don't really know why, but some event propagates resulting in the request getting
            e.stopImmediatePropagation();        // sent twice, so need to stop propagation
            var switchNum=$(this).val();
            tmp = "rcon swtch:" + switchNum + ":";
            if($(this).is (':checked')) { tmp += "On";
                                          console.log("Switch " + switchNum + ": On");  }
            else                        { tmp += "Off";
                                          console.log("Switch " + switchNum + ": Off"); }
        $('#retval').val(tmp);
        AjaxSend('readvars.php');                                                        // send update to the service, without refreshing the screen
        });                                                                              // doesn't matter where we send it, as long as it goes through
     $(".rdtrSwitch").change(function(){
            var switchNum=$(this).val();
            tmp = "rdtr swtch:" + switchNum + ":";
            if($(this).is (':checked')) { tmp += "On";
                                          console.log("Radiator " + switchNum + ": On");  }
            else                        { tmp += "Off";
                                          console.log("Radiator " + switchNum + ": Off"); }
        $('#retval').val(tmp);
        AjaxSend('readvars.php');                                                        // send update to the service, without refreshing the screen
        });                                                                              // doesn't matter where we send it, as long as it goes through
    }

    // Function called when search button is tapped...
    $(document).on('click', '.searchButton',function() {
            if ($('[data-role=listview]:visible').prev("form.ui-filterable").css('display') == 'none') {
                $('[data-role=listview]:visible').prev("form.ui-filterable").toggle(true);
            } else {
                $('[data-role=listview]:visible').prev("form.ui-filterable").toggle(false);
            }
    });

    // Function to set the search bar to hidden when loading a new page
    $(document).on("pagebeforeshow",function(){
//      event.stopPropagation();
        var thisPage = $.mobile.activePage.attr('id');
//      $('#'+thisPage).find(".ui-filterable").toggle(false);
        $('[data-role=listview]:visible').prev("form.ui-filterable").toggle(false);
        // set the spacer at the top of the page to equal the size of the header...
        var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');         // get height of current header
//        headerHeight = headerHeight - 12;                                                // fiddle factor to allow for default margins etc.
//      $('#'+thisPage).find("P[data-role='screenSpacer']").css({"color": "red", "border": "2px solid red"});  // DEBUG
        $('#'+thisPage).find("P[data-role='screenSpacer']").height(headerHeight);
     });
    // Function to set the search bar to hidden when loading a new page
    // Same as pevious, but catches any late arriver's - but at this point its a bit clunky.
    $(document).on("pagechange",function(){
//      event.stopPropagation();
        var thisPage = $.mobile.activePage.attr('id');
//      $('#'+thisPage).find(".ui-filterable").toggle(false);
        $('[data-role=listview]:visible').prev("form.ui-filterable").toggle(false);
    });

// Function called when add/del task button is selected...
$(document).on('click', '.AddDel_Button',function() {
        var thisPage = $.mobile.activePage.attr('id');                                   // get current page name
        var countNum = $.mobile.activePage.find('.lineCount').val()                      // get current number of lines
        var slideMode = $(this).text();                                                  // add or del ?
        var lineNum = $.mobile.activePage.contents().find('h1.titleLabel').html()        // get current text from current header
        var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');         // get height of current header
        lineNum = lineNum.replace('Zone ', '');                                          // remove text to leave just the number
        lineNum = lineNum.replace('Switch ', '');                                        // remove text to leave just the number
        lineNum = lineNum.replace('Task ', '');
        lineNum = lineNum.replace('Radiator ', '');
        lineNum = lineNum.replace('Account ', '');
        lineNum = parseInt(lineNum,10);                                                  // convert text to Integer
        countNum = parseInt(countNum,10);                                                // convert text to Integer

        switch (thisPage) {
        case 'security':
            if(slideMode == 'Del') {
                $('#zoneEditor').slideToggle('slow');                                  // collapse the editor
                $(this).text("Add");
                $(this).buttonMarkup({ icon: "plus" });
                $('#securityTitle').text("Alarm");

                // customise and show the action sheet...
                $('.scroll-top-wrapper').removeClass('show');
                $('#PopUpMsg').text('Confirm delete alarm zone ' + (lineNum) + ' ?');
                $('#PopUpBtn').text('Delete');
                $("#PopupDialog").popup("open", { transition: 'slideup' });

                tmp = 'zcon del:'+ (lineNum - 1);
                console.log(tmp);
                $('#retval').val(tmp);                                                   // store data on page
            } else {
                // last 'li' is the alarm zone editor, so the new / hidden 'li' is 2nd last...
                $('#zone_' + countNum).show();
                $('html, body').animate({
                     scrollTop: $('#zone_' + countNum).offset().top-headerHeight         // thanks Ron
                }, 'slow');
            }
            break;
        case 'power':
        case 'pageTwo':
            if(slideMode == 'Del') {
                $('#switchEditor').slideToggle('slow');                                  // collapse the editor
                $(this).text("Add");
                $(this).buttonMarkup({ icon: "plus" });
                $('#powerTitle').text("Power");

                // customise and show the action sheet...
                $('.scroll-top-wrapper').removeClass('show');
                $('#PopUpMsg').text('Confirm delete switch ' + (lineNum) + ' ?');
                $('#PopUpBtn').text('Delete');
                $("#PopupDialog").popup("open", { transition: 'slideup' });

                tmp = 'rcon del:'+ (lineNum - 1);
                console.log(tmp);
                $('#retval').val(tmp);                                                   // store data on page
            } else {
                // last 'li' is the switch editor, so the new / hidden 'li' is 2nd last...
                $('#switch_' + countNum).show();
                $('html, body').animate({
                     scrollTop: $('#switch_' + countNum).offset().top-headerHeight     // thanks Ron
                }, 'slow');
            }
            break;
        case 'radiator':
            if(slideMode == 'Del') {
                $('#radiatorEditor').slideToggle('slow');                                  // collapse the editor
                $(this).text("Add");
                $(this).buttonMarkup({ icon: "plus" });
                $('#radiatorTitle').text("Radiator");

                // customise and show the action sheet...
                $('.scroll-top-wrapper').removeClass('show');
                $('#PopUpMsg').text('Confirm delete radiator ' + (lineNum) + ' ?');
                $('#PopUpBtn').text('Delete');
                $("#PopupDialog").popup("open", { transition: 'slideup' });

                tmp = 'rdtr del:'+ (lineNum - 1);
                console.log(tmp);
                $('#retval').val(tmp);                                                   // store data on page
            } else {
                // last 'li' is the switch editor, so the new / hidden 'li' is 2nd last...
                $('#radiator_' + countNum).show();
                $('html, body').animate({
                     scrollTop: $('#radiator_' + countNum).offset().top-headerHeight                 // thanks Ron
                }, 'slow');
            }
            break;
        case 'schedule':
            if(slideMode == 'Del') {
                $('#taskEditor').slideToggle('slow');                                    // collapse the editor
                $(this).text("Add");
                $(this).buttonMarkup({ icon: "plus" });
                $('#taskTitle').text("Schedule");

                // customise and show the action sheet...
                $('.scroll-top-wrapper').removeClass('show');
                $('#PopUpMsg').text('Confirm delete scheduled task ' + (lineNum) + ' ?');
                $('#PopUpBtn').text('Delete');
                $("#PopupDialog").popup("open", { transition: 'slideup' });

                tmp = 'delete task:'+ (lineNum - 1) + ':';
                console.log(tmp);
                $('#retval').val(tmp);                                                   // store data on page
            } else {
                // last 'li' is the task editor, so the new / hidden 'li' is 2nd last...
                $('#task_' + countNum).show();
                $('html, body').animate({
                     scrollTop: $('#task_' + countNum).offset().top-headerHeight                   // thanks Ron
                }, 'slow');
            }
            break;
        case 'access':
            if(slideMode == 'Del') {
                $('#accessEditor').slideToggle('slow');                                  // collapse the editor
                $(this).text("Add");
                $(this).buttonMarkup({ icon: "plus" });
                $('#accessTitle').text("Access");

                // customise and show the action sheet...
                $('.scroll-top-wrapper').removeClass('show');
                $('#PopUpMsg').text('Confirm delete logon ' + (lineNum + 1) + ' ?');
                $('#PopUpBtn').text('Delete');
                $("#PopupDialog").popup("open", { transition: 'slideup' });

                tmp = 'user del:'+ (lineNum - 1);
                console.log(tmp);
                $('#retval').val(tmp);                                                   // store data on page
            } else {
                // last 'li' is the account editor, so the new / hidden 'li' is 2nd last...
                $('#account_' + countNum).show();
                $('html, body').animate({
                     scrollTop: $('#account_' + countNum).offset().top-headerHeight                   // thanks Ron
                }, 'slow');
            }
            break;
        }
    });

// Function called when editing a switch/task/alarm zone/whatever...
 $(document).on('click', '.editable',function() {
    // Only ever going to have one editor div on the screen to prevent the HTML data becoming huge.
    // Also allow for the fact these elements are added dynamically AFTER the page has loaded.
    // Get the number of the selected switch...
    var lineID = this.id                                                       // save for Ron (later Ron)
    var thisPage = $.mobile.activePage.attr('id');                             // get current page name
    var parentLine = $(this).closest( "li" );                                  // find the parent line
    var $content = parentLine.next();
    var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');         // get height of current header
    var lineNum = lineID.replace('switch_name_', '');
        lineNum = lineNum.replace('task_', '');
        lineNum = lineNum.replace('radiator_', '');
        lineNum = lineNum.replace('zone_', '');
        lineNum = lineNum.replace('account_', '');
        lineNum = parseInt(lineNum,10);
//  var tmp;
    switch (thisPage) {
        case 'security':
            parentLine.after($('#zoneEditor'));                             // move the task editor to after the selected switch
            $content = parentLine.next();
            $content.slideToggle('slow', function () {
                if ($content.is(':visible')) {
                    // execute this code when the zone editor opens...
                    $('#securityTitle').text("Zone " + (lineNum + 1));
                    $('#securityButton').text("Del");
                    $('#securityButton').buttonMarkup({ icon: "minus" });

                    // transfer the zone values into the edit div...
                    if ($('#ZoneType' + lineNum).val().toLowerCase() == 'alarm') {
                           $('input:radio[name="radio-zoneType"]').filter('[value="Alarm"]').attr("checked",true).checkboxradio("refresh");
                           $('input:radio[name="radio-zoneType"]').filter('[value="Tamper"]').attr("checked",false).checkboxradio("refresh"); }
                    else {
                           $('input:radio[name="radio-zoneType"]').filter('[value="Alarm"]').attr("checked",false).checkboxradio("refresh");
                           $('input:radio[name="radio-zoneType"]').filter('[value="Tamper"]').attr("checked",true).checkboxradio("refresh");
                           showHide('Tamper');                     // toggle the data
                    }
                    $('#zoneName').val($('#ZoneName' + lineNum).val());
                    tmp = $("#ZoneCrct" + (lineNum)).val();
                    $('#circuitNum').val(tmp).selectmenu('refresh',true);
                    // Day mode check box...
                    if ($('#ZoneDM' + lineNum).val().toLowerCase() == 'on') { $('#checkbox-DM').prop('checked', true).checkboxradio('refresh'); }
                    else { $('#checkbox-DM').prop('checked', false).checkboxradio('refresh'); }
                    // Night mode check box...
                    if ($('#ZoneNM' + lineNum).val().toLowerCase() == 'on') { $('#checkbox-NM').prop('checked', true).checkboxradio('refresh'); }
                    else { $('#checkbox-NM').prop('checked', false).checkboxradio('refresh'); }
                    // Chimes check box...
                    if ($('#ZoneCh' + lineNum).val().toLowerCase() == 'on') { $('#checkbox-Ch').prop('checked', true).checkboxradio('refresh'); }
                    else { $('#checkbox-Ch').prop('checked', false).checkboxradio('refresh'); }

                    // scroll selected div to top of screen...
                    $('html, body').animate({
                         scrollTop: $('#' + lineID).offset().top-headerHeight                    // thanks Ron
                    }, 'slow');
                } else {
                    // execute this code when the zone editor closes...
                    $('#securityTitle').text("Alarm");
                    $('#securityButton').text("Add");
                    $('#securityButton').buttonMarkup({ icon: "plus" });

                    // customise and show the action sheet...
                    $('.scroll-top-wrapper').removeClass('show');
                    $('#PopUpMsg').text('Save changes to alarm zone ' + (lineNum + 1) + ' ?');
                    $('#PopUpBtn').text('Save');
                    $("#PopupDialog").popup("open", { transition: 'slideup' });

                    // create the data string to send to the alarm service before you go go..
                    tmp = 'zcon cfg:'+ (lineNum) + ':';
                    tmp += $('input[name=radio-zoneType]:checked').val().toLowerCase() + ':';                   // value of Alarm/Tamper radio button
                    tmp += $('#zoneName').val() + ':';
                    if ($('#checkbox-DM').is(":checked")) { tmp += 'on:'; }
                    else { tmp += 'off:'; }
                    if ($('#checkbox-NM').is(":checked")) { tmp += 'on:'; }
                    else { tmp += 'off:'; }
                    if ($('#checkbox-Ch').is(":checked")) { tmp += 'on:'; }
                    else { tmp += 'off:'; }
                    tmp += $('#circuitNum').val();
                    tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                                                                                   // change any ' #' to ' \#'
                    tmp = tmp.trim()                                               // ...and get rid of any sneaky white space characters
                    console.log(tmp);
                    $('#retval').val(tmp);
                }
            });
            break;
        case 'power':
        case 'pageTwo':
            parentLine.after($('#switchEditor'));                             // move the task editor to after the selected switch
            $content = parentLine.next();
            $content.slideToggle('slow', function () {
                if ($content.is(':visible')) {
                    // execute this code when the switch editor opens...
                    $('#powerTitle').text("Switch " + (lineNum + 1));
                    $('#powerButton').text("Del");
                    $('#powerButton').buttonMarkup({ icon: "minus" });

                    // load the switch values into the switch edit div...
                    $('#switchName').val($('#switch_name_' + lineNum).text());
                    $('#switchGroup').val($('#switch_group_' + lineNum).val());
                    $('#switchAddress').prop('selectedIndex', ($('#switch_address_' + (lineNum)).val())).change();
                    $('#switchChannel').prop('selectedIndex', ($("#switch_channel_" + (lineNum)).val())).change();
                    var tmp = $('#switchAlrmAction_' + (lineNum)).val();
                    if (tmp.indexOf("On") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 0).change(); }
                    if (tmp.indexOf("Off") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 1).change(); }
                    if (tmp.indexOf("None") >= 0) { $('#switchAlrmAction').prop('selectedIndex', 2).change(); }
                    var tmp = $('#switch_HK_' + (lineNum)).val();
                    if (tmp.indexOf("Outlet") >= 0) { $('#switchHK').prop('selectedIndex', 0).change(); }
                    if (tmp.indexOf("Light") >= 0) { $('#switchHK').prop('selectedIndex', 1).change(); }
                    if (tmp.indexOf("Fan") >= 0) { $('#switchHK').prop('selectedIndex', 2).change(); }
                    if (tmp.indexOf("None (do not export)") >= 0) { $('#switchHK').prop('selectedIndex', 3).change(); }

                    // scroll selected div to top of screen...
                    $('html, body').animate({
                         scrollTop: $('#' + lineID).offset().top-headerHeight                    // thanks Ron
                    }, 'slow');
                } else {
                    // execute this code when the switch editor closes...
                    $('#powerTitle').text("Power");
                    $('#powerButton').text("Add");
                    $('#powerButton').buttonMarkup({ icon: "plus" });

                    // customise and show the action sheet...
                    $('.scroll-top-wrapper').removeClass('show');
                    $('#PopUpMsg').text('Save changes to switch ' + (lineNum + 1) + ' ?');
                    $('#PopUpBtn').text('Save');
                    $("#PopupDialog").popup("open", { transition: 'slideup' });

                    // create the data string to send to the alarm service before you go go..
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
                }
            });
            break;
        case 'radiator':
            parentLine.after($('#radiatorEditor'));                             // move the task editor to after the selected switch
            $content = parentLine.next();
            $content.slideToggle('slow', function () {
                if ($content.is(':visible')) {
                    // execute this code when the radiator editor opens...
                    $('#radiatorTitle').text("Radiator " + (lineNum + 1));
                    $('#radiatorButton').text("Del");
                    $('#radiatorButton').buttonMarkup({ icon: "minus" });

                    // load the task values into the radiator edit div...
                    $('#rdtrGroup').val($('#radiatorGroup_' + (lineNum)).val());
                    $('#rdtrName').val($('#radiatorName_' + (lineNum)).val());
                    tmp = $('#radiatorAddress_' + (lineNum)).val();
                    $('#rdtrAddress').val(tmp).selectmenu('refresh',true);
                    tmp = $("#radiatorHigh_" + (lineNum)).val();
                    $('#rdtrHi').val(tmp).selectmenu('refresh',true);
                    tmp = $("#radiatorLow_" + (lineNum)).val();
                    $('#rdtrLow').val(tmp).selectmenu('refresh',true);

                    // scroll selected div to top of screen...
                    $('html, body').animate({
                         scrollTop: $('#' + lineID).offset().top-headerHeight                    // thanks Ron
                    }, 'slow');
                } else {
                    // execute this code when the radiator editor closes...
                    $('#radiatorTitle').text("Radiator");
                    $('#radiatorButton').text("Add");
                    $('#radiatorButton').buttonMarkup({ icon: "plus" });

                    // customise and show the action sheet...
                    $('.scroll-top-wrapper').removeClass('show');
                    $('#PopUpMsg').text('Save changes to radiator ' + (lineNum + 1) + ' ?');
                    $('#PopUpBtn').text('Save');
                    $("#PopupDialog").popup("open", { transition: 'slideup' });

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
                }
            });
            break;
        case 'schedule':
            parentLine.after($('#taskEditor'));                             // move the task editor to after the selected switch
            $content = parentLine.next();
            $content.slideToggle('slow', function () {
                if ($content.is(':visible')) {
                    // execute this code when the task editor opens...
                    $('#taskTitle').text("Task " + (lineNum + 1));
                    $('#taskButton').text("Del");
                    $('#taskButton').buttonMarkup({ icon: "minus" });

                    // load the task values into the Cron edit div...
                    $('#group_value').val($('#group_' + (lineNum)).val());
                    if (isNaN($("#minutes_" + (lineNum)).val())) {
                        $('#mins_value').prop('selectedIndex', 60).change();              // Not a number, must be an '*' - force selector to last option in list
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
                        $('#month_value').prop('selectedIndex', ($("#month_" + (lineNum)).val()-1)).change(); // need to correct Month number as Jan=0 in the selector.
                    }
                    var tmp = $('#wday_' + (lineNum)).val();
                    if (isNaN(tmp)) {
                        if (tmp.indexOf("*") >= 0) { $('#wday_value').prop('selectedIndex', 7).change(); }
                        if (tmp.indexOf("1-5") >= 0) { $('#wday_value').prop('selectedIndex', 8).change(); }
                        if (tmp.indexOf("6-7") >= 0) { $('#wday_value').prop('selectedIndex', 9).change(); }
                    } else {
                        $('#wday_value').prop('selectedIndex', ($("#wday_" + (lineNum)).val()-1)).change(); // need to bump weekday number down one as Mon=0 in the selector.
                    }
                    if ($.isNumeric($("#tasknum_" + (lineNum)).val())) {
                        $('#task_value').prop('selectedIndex',($("#tasknum_" + (lineNum)).val())).change()
                    }
                    tmp = $("#status_" + (lineNum)).val();
                    if ($("#status_" + (lineNum)).val() == 'On'){ $('#switch_value').prop('checked', true ).flipswitch('refresh'); }      // switch goes on
                    else                                        { $('#switch_value').prop('checked', false ).flipswitch('refresh'); }     // switch goes off
                    // scroll selected div to top of screen...
                    $('html, body').animate({
                         scrollTop: $('#' + lineID).offset().top-headerHeight                    // thanks Ron
                    }, 'slow');
                } else {
                    // execute this code when the task editor closes...
                    $('#taskTitle').text("Power");
                    $('#taskButton').text("Add");
                    $('#taskButton').buttonMarkup({ icon: "plus" });

                    // customise and show the action sheet...
                    $('.scroll-top-wrapper').removeClass('show');
                    $('#PopUpMsg').text('Save changes to scheduled task ' + (lineNum + 1) + ' ?');
                    $('#PopUpBtn').text('Save');
                    $("#PopupDialog").popup("open", { transition: 'slideup' });

                     // create the data string to send to the alarm service before you go go..
                    tmp = 'edit cron:'+ (lineNum) + ':';
                    tmp += ($('#group_value').val()) + ':';
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
                }
            });
            break;
        case 'access':
            parentLine.after($('#accessEditor'));                             // move the task editor to after the selected switch
            $content = parentLine.next();
            $content.slideToggle('slow', function () {
                if ($content.is(':visible')) {
                    // execute this code when the zone editor opens...
                    $('#accessTitle').text("Account " + (lineNum + 1));
                    $('#accessButton').text("Del");
                    $('#accessButton').buttonMarkup({ icon: "minus" });
                    parentLine.find('a:first').removeClass('ui-icon-plus');
                    parentLine.find('a:first').addClass('ui-icon-minus');

                    // transfer the account details into the edit div...
                    $('#AccountName').val($('#AccountName_' + lineNum).val());
                    $('#AccountEmail').val($('#AccountEmail_' + lineNum).val());
                    $('#AccountNum').val(lineNum);                                  // gonna need this later

                    // scroll selected div to top of screen...
                    $('html, body').animate({
                         scrollTop: $('#' + lineID).offset().top-headerHeight                    // thanks Ron
                    }, 'slow');
                } else {
                    // execute this code when the access editor closes...
                    $('#accessTitle').text("Access");
                    $('#accessButton').text("Add");
                    $('#accessButton').buttonMarkup({ icon: "plus" });
                    parentLine.find('a:first').removeClass('ui-icon-minus');
                    parentLine.find('a:first').addClass('ui-icon-plus');

                    // customise and show the action sheet...
                    $('.scroll-top-wrapper').removeClass('show');
                    tmp = $('#AccountName').val();
//                  $('#PopUpMsg').text('Save changes to account ' + (lineNum + 1) + ' ?');
                    $('#PopUpMsg').text("Save changes to " + tmp + "'s account ?");
                    $('#PopUpBtn').text('Save');
                    $("#PopupDialog").popup("open", { transition: 'slideup' });

                    // create the data string to send to the alarm service before you go go..
                    tmp = 'user cfg:'+ (lineNum) + ':';
                    tmp += $('#AccountName').val() + ':';
                    tmp += $('#AccountEmail').val();
                    tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                                                                                   // change any ' #' to ' \#'
                    tmp = tmp.trim()                                               // ...and get rid of any sneaky white space characters
                    console.log(tmp);
                    $('#retval').val(tmp);
                }
            });
            break;
    }
 });

 // Function to restore the screen if edit has been cancelled...
function editCancel() {
    var firstDiv = "";
    var thisPage = $.mobile.activePage.attr('id');                                   // get current page name
    var countNum = $.mobile.activePage.contents().find('.lineCount').val()           // get current number of lines
    var headerHeight = $('[data-role=header]:visible').prop('scrollHeight');         // get height of current header
    countNum = parseInt(countNum,10);
    switch (thisPage) {
        case 'security':
            scrollToTop();
            firstDiv=$('#zoneList li:nth-child(1)')
            $('#switch_'+(countNum)).hide();                                         // if this was a 'new' switch/task/whatever, we need to re-hide the line.
            break;
        case 'power':
        case 'pageTwo':
            scrollToTop();
            $('#switch_'+(countNum)).hide();                                         // if this was a 'new' switch/task/whatever, we need to re-hide the line.
            break;
        case 'radiator':
            scrollToTop();
            $('#radiator_'+(countNum)).hide();                                         // if this was a 'new' switch/task/whatever, we need to re-hide the line.
            break;
        case 'schedule':
            scrollToTop();
            $('#task_'+(countNum)).hide();
            break;
        case 'access':
            scrollToTop();
            $('#access_'+(countNum)).hide();
            break;
    }
}

// Function to define all required PHP parameters required for an Ajax send
function editGo() {
    var thisPage = $.mobile.activePage.attr('id');                             // get current page name
    switch (thisPage) {
        case 'security':
              AjaxSend('SecurityPageAjaxCall.php', 'security', 'zoneList');
              break;
        case 'power':
        case 'pageTwo':
              AjaxSend('PowerPageAjaxCall.php', 'pageTwo', 'switchList');
              break;
        case 'radiator':
              AjaxSend('RadiatorPageAjaxCall.php', 'radiator', 'radiatorList');
              break;
        case 'schedule':
              AjaxSend('SchedulePageAjaxCall.php', 'schedule', 'taskList');
              break;
        case 'access':
              AjaxSend('AccessPageAjaxCall.php', 'access', 'accessList');
              break;
        case 'settings':
              AjaxSend('SettingsPageAjaxCall.php', 'settings', 'settingsList');
              break;
    }
}

// Functions to handle the scroll to top button...
$(function(){
    $(document).on( 'scroll', function(){
        if ($(window).scrollTop() > 100) {
            $('.scroll-top-wrapper').addClass('show');
        } else {
            $('.scroll-top-wrapper').removeClass('show');
        }
    });
    $('.scroll-top-wrapper').on('click', scrollToTop);
});

function scrollToTop() {
    var thisPage = $.mobile.activePage.attr('id');
    var target = $('#'+thisPage).find("P[data-role='screenSpacer']").prop('scrollHeight');     // get height of current header
    if (thisPage = 'info') { $('html, body').stop().animate({ scrollTop : target - 43 }, 'slow'); }
    else { $('html, body').stop().animate({ scrollTop : target - 24 }, 'slow'); }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajax functions. Three types of Ajax call are required...
//
// 1) Read without write. A PHP file is inserted into the DOM, no message is passed to the alarm service.
// 2) Write to alarm service, wait for a reply, and insert the reply into the DOM. Used when contents of a page have changed. ( Asynchronous write )
// 3) Write to alarm service without waiting for a reply. Used when contents of a page haven't changed much, such as
//      when flicking a switch. (Synchronous write)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
            collapsibleSlowScroll();                                                 // and kick the slow scroll too
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
        switchChange();
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
            collapsibleSlowScroll();                                                   // and kick the slow scroll too
        }
        if ( listID == 'zoneList'){
            // also need to kick the collapsible
            $('#SecurityPageAjaxCall').trigger('create');                              // kick the collapsible and menu
            collapsibleSlowScroll();                                                   // and kick the slow scroll too
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
                if (listID == 'switchList'){ switchChange(); }
                if (listID == 'radiatorList'){ switchChange(); }
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

// changing the zone alarm/tamper setting redirects here
function showHide(zonetype) {
    if (zonetype == "alarm") {
        $('#alarmOptions').show(500);
        $('#zoneMessage').text("Select trigger options for this alarm zone:");
    } else {
        $('#alarmOptions').hide(500);
        $('#zoneMessage').text("Tamper zones are always active in Day mode, Night mode and Standby. They can not be used to trigger chimes.");
    }
}

// Looks after the password validation. If validation is ok, sends new password to alarm service
function PwordCheck() {
      var $tmp1 = $('#Pword1').val();
      var $tmp2 = $('#Pword2').val();
      if ($tmp1 != $tmp2) {
             alert("The passwords do not match.\nPlease try again.");
             $('#Pword1').val('');
             $('#Pword2').val('');
             return false;
       } else {
            tmp = 'user pwd:';
            tmp += ($('#AccountNum').val()) + ':';
            tmp += $tmp1;
            tmp = tmp.replace(/\ #/g,' \\#');                              // ' #' (space hash) characters needs to be delimited before sending
                                                                           // change any ' #' to ' \#'
            tmp = tmp.trim()                                               // ...and get rid of any sneaky white space characters
            console.log(tmp);
            $('#retval').val(tmp);
            AjaxSend('AccessPageAjaxCall.php', 'AccessPageAjaxCall', 'accessList');
        }
}

function Defaults(parm1) {
    console.log(parm1);
    $('#retval').val(parm1);
    editGo();                                                             // want to display spinner, so send request through this routine
}

// Changing the alarm mode redirects here...
function AlarmMode(NewMode) {
    $('#retval').val(NewMode);
       AjaxSend('SecurityPageAjaxCall.php', 'security', 'zoneList');
       return false;
}

    function HomeKit(parameter) {
        tmp = "HomeKit:" + parameter;
        $('#retval').val(tmp);
        AjaxSend('readvars.php');                                         // doesn't matter where we send the data as long as
                                                                          // it goes through 'readvars' to make it go
        tmp = "You also need to clear the HomeKit database on your iPhone !"
        alert (tmp);
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Setup functions...
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function InitCurrentTheme(){
               tmp=($("link[rel='stylesheet']").attr("href"))
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