<?php include("readvars.php"); ?>                         <!-- common code to read variables from file STATUS.TXT -->

    <div data-role="header" data-position="fixed" data-fullscreen="true">
        <a href="#sidePanel" class="SquareButton green">
          <span class="gloss"></span>
            <span class="SquareButtonText">Menu</span>
        </a>
        <h1 style="font-size: 22px; padding: .35em 0;">About</h1>
        <a href="#info" onclick="AjaxGet('info.php','info')" data-transition="flip" class="SquareButton green">
          <span class="gloss"></span>
            <span class="SquareButtonText">Info</span>
        </a>
    </div>

    <p data-role="screenSpacer" style="height: 5px;"></p>          <!-- stop first item from getting stuck behind the header
                                                                        also acts as a marker for the scroll to top button.   -->
    <div style="text-align:center">
        <br>
        <form class="floaty box">
            <h2 style="margin-top: 0px; margin-bottom: 0px; text-shadow: none;">Voice Controlled<br>Home Automation.</h2>
        </form>
        <img alt ="logo" src="about.png" style="height: 150px;">
        <form class="floaty box" style="font-weight: bold;">
            <em>Homekit bridge on a budget.<br><br>
                * Voice control your stuff.<br>
                * Monitor your home.<br>
                * Mess with your lights.<br>
                * Ring your alarm bells.<br>
                * Wake your neighbours.<br><br>
                All from hundreds of miles away.</em><br>
        </form>
        <br>
        <form class="floaty box">
            <a target="_blank" href="http://www.oddwires.co.uk">© 2013-18 ODDWIRES.CO.UK</a>
        </form>
        <p>&nbsp </p>
        <p>&nbsp </p>
    </div>
    <p style="height:100vh"></p>
	