<?php
    include("readvars.php");                         // common code to read variables from file STATUS.TXT
?>
<br><br>
<div data-role="header" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
    <a href="#sidePanel" data-icon="bars">Menu</a>
    <h1>Logs</h1>
</div>
<p data-role="screenSpacer"></p>          <!-- stop first item from getting stuck behind the header -->

<?php $date[0] = date('d/m/Y');                                       // today
    $date[1] = date('d/m/Y', time() - (1*60*60*24) );                 // yesterday
    $date[2] = date('d/m/Y', time() - (2*60*60*24) );                 // 2 days ago
    $date[3] = date('d/m/Y', time() - (3*60*60*24) );                 // 3 days ago
    $date[4] = date('d/m/Y', time() - (4*60*60*24) );                 // 4 days ago
    $date[5] = date('d/m/Y', time() - (5*60*60*24) );                 // 5 days ago
    $date[6] = date('d/m/Y', time() - (6*60*60*24) );                 // 6 days ago
    $date[7] = date('d/m/Y', time() - (7*60*60*24) );                 // 1 week ago ago
?>
<ul data-role="listview" data-inset="true" style="margin-left: 5px; margin-right:5px;">
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[0]; ?>','logView')" data-rel="close">Today
            <span class="ui-li-count"><?php echo $date[0]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[1]; ?>','logView')" data-rel="close">Yesterday
            <span class="ui-li-count"><?php echo $date[1]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[2]; ?>','logView')" data-rel="close">2 days ago
            <span class="ui-li-count"><?php echo $date[2]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[3]; ?>','logView')" data-rel="close">3 days ago
            <span class="ui-li-count"><?php echo $date[3]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[4]; ?>','logView')" data-rel="close">4 days ago
            <span class="ui-li-count"><?php echo $date[4]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[5]; ?>','logView')" data-rel="close">5 days ago
            <span class="ui-li-count"><?php echo $date[5]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[6]; ?>','logView')" data-rel="close">6 days ago
            <span class="ui-li-count"><?php echo $date[6]; ?></a></li>
     <li><a href="#logView" onclick="AjaxGet('logscroll.php?<?php echo "date=".$date[7]; ?>','logView')" data-rel="close">1 week ago
            <span class="ui-li-count"><?php echo $date[7]; ?></a></li>
  </ul>
</div>


