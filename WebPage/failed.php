<?php   session_start();
        if ($_SESSION['badcount'] < 3) { ?>
<!--
            <div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <a href="#pageone" data-transition="slide" data-direction="reverse" data-icon="arrow-l">Back</a>
                <h1 class="titleLabel">Failed logon #<?php echo $_SESSION['badcount']; ?></h1>
            </div>
-->
            <br><br>
            
            <form class="form-1" id="loginForm" action="/login.php" method="POST" data-transition="slide">
                <div class="info" style=" font-weight: bold; font-size: 12px;"><p>
                    <?php if ($_SESSION['badcount'] == "1") {
                        echo "First failed log on!<br>";
                    }?>
                    <?php if ($_SESSION['badcount'] == "2") {
                        echo "Second failed log on!<br>";
                    }?></p>
                    Your attempt to access<br>
                    this system and your IP<br>
                    address have been logged.<br>
                    <p><b><?php echo $_SERVER['REMOTE_ADDR'] ?></b></p>
                    <p>This system is monitored.<br>
                    Do not attempt to log in unless<br>
                    you are authorised to do so.<br><br></p>
                </div>
            <p class="submit" style="right: 185px;">
                <a href="#" id="submit" value="Submit Button" type="submit"><i class="newi fa fa-arrow-circle-left"></i></a>
                <a href="#pageone" data-transition="slide" data-direction="reverse"><i class="newi fa fa-arrow-circle-left"></i></a>
            </p>
        </form>

<?php  } else { ?>
            <div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
                <h1 class="titleLabel">Banned</h1>
            </div>
            <br><br><br><br><br>
            <div style="text-align:center">
                <img src=".\themes\images\Skull_and_crossbones.png">
            </div>
            <br><br><br>
            <div class="info">
                <br>Your IP address is <?php echo $_SERVER['REMOTE_ADDR'] ?><br>
                and it has been banned.<br><br>
            </div>
<?php  } ?>
