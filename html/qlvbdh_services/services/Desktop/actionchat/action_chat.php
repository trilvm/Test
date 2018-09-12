<?php

require_once '../../../config.php';
require_once '../../../db/connection.php';
require_once '../../../db/common.php';
require_once '../../../models/Desktop.php';

$option=$_GET["option"];
$id=$_GET["id"];

if($option=='cancelsend')
{
	$sql = sprintf(
				"Update des_message set STATUST_FILE='%s',WHO_CANCEL='%s' where ID_SEND_FILE='%s'"
				,mysql_real_escape_string('cancel')
				,mysql_real_escape_string('ROOT')
				,mysql_real_escape_string($id)
				);
	$rows = query($sql);			
	$sql = sprintf(
				"Delete from des_send_file where ID_SEND_FILE ='%s'",
				 mysql_real_escape_string($id)
				);
	$rows = query($sql);
}else if($option=='cancelget')
{
	$sql = sprintf(
				"Update des_message set STATUST_FILE='%s' where ID_SEND_FILE='%s'"
				,mysql_real_escape_string('cancel')
				,mysql_real_escape_string($id)
				);
	$rows = query($sql);			
	$sql = sprintf(
				"Delete from des_send_file where ID_SEND_FILE ='%s'",
				 mysql_real_escape_string($id)
				);
	$rows = query($sql);
	
}else if($option=='cancelgetgroup')
{
	$param=explode("~",$id);
	$sql = sprintf(
				"Update des_message set STATUST_FILE='cancel' where ID_SEND_FILE='%s' and TOUSER='%s'"
				,mysql_real_escape_string($param[1])
				,mysql_real_escape_string($param[0])
				);
	$rows = query($sql);			
}else if($option=='cancelsendgroup')
{
	$param=explode("~",$id);
	$sql = sprintf(
				"Update des_message set STATUST_FILE='cancel',WHO_CANCEL='%s' where ID_SEND_FILE='%s' and TOUSER='%s'"
				,mysql_real_escape_string('ROOT')
				,mysql_real_escape_string($param[1])
				,mysql_real_escape_string($param[0])
				);
	$rows = query($sql);			
}

//output the response
echo $option.$id;
?>