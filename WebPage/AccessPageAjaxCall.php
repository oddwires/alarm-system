<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#sidePanel" class="SquareButton green">
                  <span class="gloss"></span>
                    <span class="SquareButtonText">Menu</span>
                </a>
                <h1 style="font-size: 22px; padding: .35em 0;" id="accessTitle">Access</h1>
                <a href="#" class="SquareButton green AddDel_Button">
                  <span class="gloss"></span>
                    <span class="SquareButtonText" id="NewUser">Add</span>
                </a>
</div>
    <p data-role="screenSpacer" style="height: 25px"></p>

<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new alarm zone                              -->

<ul data-role="listview" data-inset="true" id="accessList" style="margin-left: 10px; margin-right:10px;">
<input id="UserCount" type="hidden" value="<?php echo $USRNum; ?>"> 
<?php for($i=0; $i<$USRNum; $i++) {  ?>
    <li class="floaty userID" id="account_<?php echo $i; ?>">
            <a href="#" class="floaty userID ui-btn ui-shadow ui-corner-all ui-icon-carat-r ui-btn-icon-right"
                     style="border-radius: 6px; text-shadow: none;"><?php echo $user[$i][1]; ?></a>
    <!-- Make all user data available on the page just in case we want to drill down and start editing -->
    <input type="hidden" id="AccountName_<?php echo $i; ?>" value="<?php echo $user[$i][1]; ?>"></li>
    <input type="hidden" id="AccountEmail_<?php echo $i; ?>" value="<?php echo $user[$i][2]; ?>"></li>
<?php } ?>
    <!-- Extra line added for when creating a new alarm zone.                                                                  -->
    <li class="editable" style="display:none;" id="account_<?php echo $USRNum; ?>"
            <a href="#" class="ui-btn ui-shadow ui-corner-all ui-icon-plus ui-btn-icon-right" style="border-radius: 6px;"><?php echo $user[$i][1]; ?>New logon</a>
    <!-- Make all user data available on the page just in case we want to drill down and start editing -->
    <input type="hidden" id="AccountName_<?php echo $i; ?>" value="New logon"></li>
    <input type="hidden" id="AccountEmail_<?php echo $i; ?>" value=""></li>
    <!-- End of extra line                                                    -->
</ul>
<!-- put some blank space at the end of the list.    -->
<p style="height:100vh"></p></div>

<!-- Editor popup allows switch parameters to be modified or created.                                                -->
<div data-role="popup" id="userEditor" class="floaty"
                   style="top: initial; padding-top: 0; padding-bottom: 0;" data-dismissible="false">
    <div data-role="header">
        <h1 id="accountText">Edit account 1</h1>
    </div>

<!-- JQuery Mobile does a thing when opening or closing a popup. The focus automatically goes to the first input box on
     the form. When running on an iPhone, this will activate the keypad. A decoy input is used to prevent the keypad jumping
     up and down. Setting the position as fixed effectively hides it. When it receives the focus, it immediately drops it again.                                                                                                                 -->
    <input type="text" readonly="readonly" onfocus="blur();" style="position: fixed;"> 

    <p><table style="table-layout: fixed; width:100%; word-wrap:break-word;text-align:center; padding: 0px 10px; text-shadow: none"
                border="0">
        <tr><th colspan="5" style="text-align:left;">Account name</th></tr>
        <tr><td colspan="5" ><input data-wrapper-class="address" type="text" id="AccountName" ></td></tr>
        <tr><th colspan="5" style="text-align:left;">Email</th></tr>
        <tr><td colspan="5"><input data-wrapper-class="address" type="text" id="AccountEmail"
                                placeholder="SomeEmail@SomeAddress.com" ></td></tr>
    </table><br>
    <div id="PWordCollapsible" class="animateMe2" data-role="collapsible" data-collapsed="true" 
                          style="width: 90%; margin: auto;" data-mini="true" data-iconpos="right">
        <legend style="background-color: #2fb173;">Set password</legend>
        <input type="password" id="Pword1" placeholder="New password:"/>
        <input type="password" id="Pword2" placeholder="Confirm password:"/>
    </div><br>
    <a href="#" id="saveUser" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 5px;">
          <span class="gloss"></span><span class="SquareButtonText">&nbspSave&nbsp</span></a>
    <a href="#" id="deleteUser"class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Delete</span></a>
    <a href="#"  id="cancelUser" class="SquareButton green ui-btn ui-corner-all"
                style="position: relative; margin: 4px 10px 0px; padding: 7px 10px;">
          <span class="gloss"></span><span class="SquareButtonText">Cancel</span></a>
    </p>
</div>
