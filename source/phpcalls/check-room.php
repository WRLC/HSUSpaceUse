<?php
//Checks to see if the room ID already exists within the database.

require_once('./../config.php');

$roomId = $_REQUEST['roomId'];

//setup connection to DB
$dbh = new PDO($dbhost, $dbh_select_user, $dbh_select_pw);

$check_room_stmt = $dbh->prepare('
	SELECT count(area_id)
	FROM area
	WHERE facilities_id = :roomId');

$check_room_stmt->bindParam(':roomId', $roomId, PDO::PARAM_STR);
$check_room_stmt->execute();

if($check_room_stmt->rowCount() > 0){
	$get_area_id = $dbh->prepare('
	SELECT area_id
	FROM area
	WHERE facilities_id = :roomId');

	$get_area_id->bindParam(':roomId', $roomId, PDO::PARAM_STR);
	$get_area_id->execute();
	$result = $get_area_id->fetchAll();
}
else{
	$result = 0;
}

print json_encode($result);