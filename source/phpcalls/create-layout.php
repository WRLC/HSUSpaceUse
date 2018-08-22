<?php
//inserts a new layout into the layout record
require_once('./../config.php');
$username = $_REQUEST['username'];
$layout_name = $_REQUEST['layout_name'];
$floor_id = $_REQUEST['floor_id'];
$date_created = date("Y-m-d h:i:sa");

$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);

$get_floor_stmt = $dbh->prepare('
	SELECT floor_num
	FROM floor_images
	WHERE id = :floor_id');

$get_floor_stmt->bindParam(':floor_id', $floor_id, PDO::PARAM_INT);
$get_floor_stmt->execute();
$cur_floor = $get_floor_stmt->fetch();
$cur_floor = array_shift($cur_floor);

$dbh->beginTransaction();
$insert_layout_stmt = $dbh->prepare('
	INSERT INTO layout (layout_name, author, floor, floor_id, date_created)
    VALUES (:layout_name, :username, :floor, :floor_id, :date_created)');


$dbh->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);

$insert_layout_stmt->bindParam(':layout_name', $layout_name, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':username', $username, PDO::PARAM_STR);
$insert_layout_stmt->bindParam(':floor', $cur_floor, PDO::PARAM_INT);
$insert_layout_stmt->bindParam(':floor_id', $floor_id, PDO::PARAM_INT);
$insert_layout_stmt->bindParam(':date_created', $date_created, PDO::PARAM_STR);

$insert_layout_stmt->execute();

$data = array('layout_id' => $dbh->lastInsertId());
$dbh->commit();

print json_encode($data);