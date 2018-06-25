<?php
	require_once('./../config.php');

	$start_date = $_REQUEST['start_date'];
	$end_date = $_REQUEST['end_date'];
	$floor = $_REQUEST['floor'];
	//get activities and populate activityMap
	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$getSurveys = $dbh->prepare('SELECT survey_id, survey_date
								FROM survey_record
								JOIN layout ON survey_record.layout_id = layout.layout_id
								WHERE survey_date > :start_date
								AND survey_date < :end_date
								AND layout.floor = :in_floor');

	$getSurveys->bindParam(':start_date', $start_date, PDO::PARAM_STR);
	$getSurveys->bindParam(':end_date', $end_date, PDO::PARAM_STR);
	$getSurveys->bindParam(':in_floor', $floor, PDO::PARAM_INT);

    $getSurveys->execute();
	
	$get_surveys_result = $getSurveys->fetchAll();

	print json_encode($get_surveys_result);