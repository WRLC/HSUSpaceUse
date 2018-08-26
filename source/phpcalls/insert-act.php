<?php
//insert the areas in the area_in_layout table
require_once('./../config.php');

$actName = $_REQUEST['actName'];
$furnOrWb = $_REQUEST['furnOrWb'];

 //setup connection to DB
 $dbh = new PDO($dbhost, $dbh_insert_user, $dbh_insert_pw);
 $dbh->beginTransaction();
 $insert_act_stmt = $dbh->prepare('INSERT INTO activity (activity_description, wb_activity) 
                                     VALUES (:actName, :furnOrWb)');
 $insert_act_stmt->bindParam(':actName', $actName, PDO::PARAM_STR);
 $insert_act_stmt->bindParam(':furnOrWb', $furnOrWb, PDO::PARAM_INT);
 $insert_act_stmt->execute();

 $dbh->commit();

 print json_encode($insert_act_stmt->rowCount());