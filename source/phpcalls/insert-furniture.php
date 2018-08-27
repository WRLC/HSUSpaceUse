<?php
//pairs furniture with a layout and inserts that data into the furniture table
require_once('./../config.php');

$jsondata = json_decode($_POST['to_json'], true);
$layout_id = $_REQUEST['layout_id'];
$area_id;

//setup connection to DB
$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);

//json data holds the furnMap, iterate through all pieces of furniture
foreach($jsondata as $key => $value){
	//get basic furniture data
	$dbh->beginTransaction();
	$x = $value['x'];
	$y = $value['y'];
	$degreeOffset = $value['degreeOffset'];
	$ftype = $value['ftype'];
	$inArea = $value['inArea'];

	$checkArea = (int)$inArea;

	 	
	if($checkArea == 0){

		$roomID = $value['roomId'];
		$roomName = $value['roomName'];

		$insert_room_stmt = $dbh->prepare('
			INSERT INTO room (facilities_id, name)
			VALUES (:roomID, :roomName)');

		$insert_room_stmt->bindParam(':roomID', $roomID, PDO::PARAM_STR);
		$insert_room_stmt->bindParam(':roomName', $roomName, PDO::PARAM_STR);
		$insert_room_stmt->execute();

		$insert_area_stmt = $dbh->prepare('
			INSERT INTO area (name, facilities_id)
			VALUES (:roomName, :roomID)');

		$insert_area_stmt->bindParam(':roomID', $roomID, PDO::PARAM_STR);
		$insert_area_stmt->bindParam(':roomName', $roomName, PDO::PARAM_STR);
		$insert_area_stmt->execute();

		//get the ID of the inserted room/area

		$area_id = $dbh->lastInsertId();


		$inArea = $area_id;

		$insert_arealayout_stmt = $dbh->prepare('
			INSERT INTO area_in_layout (area_id, layout_id)
			VALUES (:areaID, :layoutID)');

		$insert_arealayout_stmt->bindParam(':areaID', $area_id, PDO::PARAM_INT);
		$insert_arealayout_stmt->bindParam(':layoutID', $layout_id, PDO::PARAM_INT);
		$insert_arealayout_stmt->execute();
	}
	
	
	$insert_furn_stmt = $dbh->prepare('INSERT INTO furniture 
	(x_location, y_location, degree_offset, layout_id, furniture_type, default_seat_type, in_area) 
	VALUES (:x, :y, :degreeOffset, :layout_id, :ftype, :default_seat_type, :inArea)');
	$insert_furn_stmt->bindParam(':x', $x, PDO::PARAM_INT);
	$insert_furn_stmt->bindParam(':y', $y, PDO::PARAM_INT);
	$insert_furn_stmt->bindParam(':degreeOffset', $degreeOffset, PDO::PARAM_INT);
	$insert_furn_stmt->bindParam(':layout_id', $layout_id, PDO::PARAM_INT);
	$insert_furn_stmt->bindParam(':ftype', $ftype, PDO::PARAM_INT);

	//currently, DB holds a default seat for objects, but we aren't tracking what they are.
	//32 is for a chair, later when library tracks we can replace this with chair data.
	$defSeat = 32;
	$insert_furn_stmt->bindParam(':default_seat_type', $defSeat, PDO::PARAM_INT);
	$insert_furn_stmt->bindParam(':inArea', $inArea, PDO::PARAM_INT);
	
	$insert_furn_stmt->execute();
	$dbh->commit();
	
}
print json_encode($insert_furn_stmt->rowCount());