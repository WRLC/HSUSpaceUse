<?php
	require_once('./../config.php');

	//get activities and populate activityMap
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$getPeriods = $dbh->prepare('SELECT * FROM survey_period');

    $getPeriods->execute();
	
	$period_result = $getPeriods->fetchAll();

	print json_encode($period_result);