<?php
	//gets layout_id's from  layout for a floor
	session_start();
	require_once('./../config.php');

	$floor_ID =  $_REQUEST['floor_ID'];

	$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

	$stmt2 = $dbh->prepare("SELECT layout_id, layout_name 
							FROM layout 
							where floor_id = :floor_id");

	/*statment for after layout is selected*/
	$stmt2->bindParam(':floor_id', $floor_ID, PDO::PARAM_INT);

	$stmt2->execute();

	$floor_result = $stmt2->fetchAll();

	print json_encode($floor_result);