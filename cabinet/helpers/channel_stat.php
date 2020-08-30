<?php
    require_once __DIR__.'/../init.php';
    //-------------------------------------------------------//
    $stat_period    = (isset($_GET['stat_period'])) ? (string)$_GET['stat_period'] : "day";
    $channel_id     = (int)$_GET['channel_id'];
    header("location: /cabinet/home.php?template=channel_stat&view=list&channel_id=".$channel_id."&stat_period=".$stat_period);
    //-------------------------------------------------------//
	
?>