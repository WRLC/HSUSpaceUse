<?php
//query multiple survey id's and return the information

require_once('./../config.php');

$jsondata = json_decode($_POST['to_json'], true);

//setup connection to DB
$dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);

foreach($jsondata as $key => $value){
	
}