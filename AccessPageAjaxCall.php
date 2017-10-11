<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <table style="width:98%;" border="0" align="center"><tr>
          <td style="width: 25%"><a href="#sidePanel" class="menuButton"data-role="button" data-icon="bars">Menu</a></td>
          <td style="width: 35%"><h1 class="titleLabel ui-title" id="accessTitle">Access</h1></td>
          <td style="width: 40%; text-align: right;">
             <a href="#" data-role="button" data-icon="search" class="searchButton" data-iconpos="notext" style="font-size: 19px;">!</a>
             <a href="#" data-role="button" data-icon="plus" class="AddDel_Button" id="accessButton">Add</a></td></tr>
    </table>
</div>
<input class="lineCount" type="hidden" value="<?php echo $USRNum; ?>"> 

<p data-role="screenSpacer"><br></p>          <!-- stop first item from getting stuck behind the header -->
<!-- Make all task configuration data available on the page just in case we want to drill down and start editing -->
<!-- Note added a spare entry at the end in case we want to create a new alarm zone                              -->

<ul data-role="listview" data-inset="true" id="accessList" style="padding: 0em; background-color:#D4CBAC; border-style: none; margin: 5px;">
<?php for($i=0; $i<$USRNum; $i++) {  ?>
    <li class="editable" id="account_<?php echo $i; ?>">
            <a href="#" class="ui-btn ui-shadow ui-corner-all ui-icon-plus ui-btn-icon-right" style="border-radius: 6px;"><?php echo $user[$i][1]; ?></a>
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
    <!-- Editor screen. This bit gets around a bit, and will be tagged onto the end of any selected power switch lines   -->
    <li style="display: none; background-color: #D4CBAC" id="accessEditor";>
    <table style="width: 100%" border="0">
        <tr><th>Name:</td><td><input data-wrapper-class="address" type="text" id="AccountName" ></td></tr>
        <tr><th>Email:</td><td><input data-wrapper-class="address" type="text" id="AccountEmail" placeholder="SomeEmail@SomeAddress.com" ></td></tr>
        <tr><th>Password:</td><td>
            <a href="#PwordPopup" data-rel="popup" class="ui-btn ui-btn-inline ui-mini ui-corner-all" data-transition="slideup" data-position-to="body">Set</a>
        </td></tr>
    </table>
    </li>
<!-- End of editor                                   -->
</ul>
<!-- put some blank space at the end of the list. Gives CronEditor some room to work in for the last items in the list view.   -->
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</div>

<div data-role="popup" id="PwordPopup" class="ui-content" data-overlay-theme="b">
   <form>
    <div style="padding: 10px 20px;">
        <h3 style ="margin-top: 0px;">Enter a new password</h3>
        <input type="password" id="Pword1" placeholder="New password:"/>
        <input type="password" id="Pword2" placeholder="Confirm password:"/>
        <input type="hidden" id="AccountNum"/>
        <a href="#home" id="PopUpBtn2" class="whiteButton" data-role="button" onclick="PwordCheck();">Save</a>
    </div>
    </form>
</div>