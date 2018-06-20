<?php
	require_once('./../config.php');

	$period = $_REQUEST['period'];
	$floor = $_REQUEST['floor'];
	//get activities and populate activityMap
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$getSurveys = $dbh->prepare('SELECT survey_id, survey_date
								FROM survey_record
								JOIN layout ON layout.layout_id = survey_record.layout_id 
								WHERE survey_record.survey_period_id = :in_period
								AND layout.floor = :in_floor');

	$getSurveys->bindParam(':in_period', $period, PDO::PARAM_INT);
	$getSurveys->bindParam(':in_floor', $floor, PDO::PARAM_INT);

    $getSurveys->execute();
	
	$get_surveys_result = $getSurveys->fetchAll();

	print json_encode($get_surveys_result);