<?php
//Select all areas in a specific layou
session_start();
require_once('./../config.php');

$floor_id =  $_REQUEST['floor_ID'];

$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$select_layout = $dbh->prepare('
	SELECT layout_id
	FROM layout
	WHERE floor_id = :floor_id');

$select_layout->bindParam(':floor_id', $floor_id, PDO::PARAM_INT);

$select_layout->execute();

$result = $select_layout->fetch();

print json_encode($result);