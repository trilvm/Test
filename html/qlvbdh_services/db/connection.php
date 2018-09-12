<?php
$link = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD) or die('Could not connect: ' . mysql_error());
mysql_select_db(DB_DATABASE) or die('Could not select database');
$debugsql = true;
function query($sql){
	global $debugsql;
	if($debugsql){
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/qlvbdh_services/Log.txt", $sql."\n", FILE_APPEND | LOCK_EX);
	}
	return mysql_query($sql);
}