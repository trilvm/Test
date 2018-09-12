<?php
class Chat{
	public static function Login($username,$password){
		$sql = sprintf("
			SELECT * FROM QTHT_USERS WHERE USERNAME = '%s' and PASSWORD = '%s'
			",
			mysql_real_escape_string($username),
			mysql_real_escape_string($password)
		);
		//return $sql;
		$row = query($sql);
		if(mysql_num_rows($row)==1){
			$item = mysql_fetch_assoc($row);
			return $username;
		}else{
			return "-1";
		}
	}
	public static function GetUsers(){
		$sql = sprintf("
			SELECT 
				u.USERNAME,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME, dep.ID_DEP FROM 
			QTHT_USERS u
			INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP=emp.ID_EMP
			INNER JOIN QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			ORDER BY u.USERNAME DESC
			"
		);
		//return $sql;
		$row = query($sql);
		return $row;
	}
	public static function GetDeps(){
		$sql = sprintf("
			SELECT 
				ID_DEP,ID_DEP_PARENT,NAME
			FROM
				QTHT_DEPARTMENTS
			ORDER BY ID_DEP_PARENT,ID_DEP
			"
		);
		//return $sql;
		$row = query($sql);
		return $row;
	}
	public static function GetSerializeDataAlarm($idu,$type){
		
		$sql="";
    	$messages = null;
    	if($type==1){
	    	$sql = sprintf("
	    	SELECT 
	    		m.*,NHACNHO as MAINTIME
	    	FROM 
	    		".Common::Table("LCT2_PERSONAL")." m 
	    	WHERE 
	    		ID_U = '%s' AND IS_GET=0 AND NHACNHO IS NOT NULL",mysql_real_escape_string($idu));
	    	$messages = query($sql);
	    	$sql = sprintf("
	    		UPDATE ".Common::Table("LCT2_PERSONAL")." set IS_GET=1 WHERE ID_U = '%s' AND IS_GET=0 AND NHACNHO IS NOT NULL",
	    		mysql_real_escape_string($idu)
	    	);
	    	//$xml = $sql;
	    	query($sql);
	    	
    		return $messages;
		
		}else{
    		$sql = sprintf("
	    	SELECT 
	    		m.*, concat(emp.FIRSTNAME , ' ' , emp.LASTNAME) as NAME_U_SEND 
	    	FROM 
	    		".Common::Table("GEN_MESSAGE")." m 
	    		inner join QTHT_USERS u on m.ID_U_SEND = u.ID_U
	    		inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
	    	WHERE 
	    		m.ID_U_RECEIVE='%s' AND (m.STATUS is NULL OR m.STATUS=0) AND REMINDER is NULL",mysql_real_escape_string($idu));
    		$messages = query($sql);
	    	$sql = sprintf("
	    		UPDATE ".Common::Table("GEN_MESSAGE")." set STATUS=1 WHERE ID_U_RECEIVE='%s' AND (STATUS is NULL OR STATUS=0) AND REMINDER is NULL",
	    		mysql_real_escape_string($idu)
	    	);
	    	
	    	query($sql);
			return $messages;
	    	
	    	  
    	}
	}
}