<?php
class Alarm
{
	public static function GetData($idu,$type)
    {
    	$sql="";
    	$messages = null;
    	if($type==1){
	    	$sql = sprintf("
	    	SELECT 
	    		m.*
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
	    	//query("UPDATE "+Common::Table("GEN_MESSAGE")+" ";//,Common::Table("GEN_MESSAGE"),array("STATUS"=>1),"ID_U_RECEIVE=".$idu." AND (STATUS is NULL OR STATUS=0)");
	    	$xml="<NOTIFICATIONS>";
	    	while($item = mysql_fetch_assoc($messages)){
	    			$xml.="<NOTIFICATION>";
					$xml.="<MODULE_ELEMENT>".base64_encode("")."</MODULE_ELEMENT>";
					$xml.="<ID_ELEMENT>".base64_encode($item['ID_LCT_P'])."</ID_ELEMENT>";
					$xml.="<TITLE_ELEMENT>".base64_encode("")."</TITLE_ELEMENT>";
					$xml.="<CONTENT_ELEMENT>".base64_encode($item['NOIDUNG'])."</CONTENT_ELEMENT>";
					$xml.="<START_TIME_ELEMENT>".base64_encode(date("H:i:s",strtotime($item['NHACNHO'])))."</START_TIME_ELEMENT>";
					$xml.="<START_DATE_ELEMENT>".base64_encode(date("Y-m-d",strtotime($item['NHACNHO'])))."</START_DATE_ELEMENT>";
					if($item['NHACNHO']==""){
						$xml.="<REMINDER_TIME_ELEMENT>".base64_encode("")."</REMINDER_TIME_ELEMENT>";
					}else{
						$xml.="<REMINDER_TIME_ELEMENT>".base64_encode(date("Y-m-d H:i:s",strtotime($item['NHACNHO'])-$item['BEFORE']*60))."</REMINDER_TIME_ELEMENT>";
					}
					$xml.="<USER_NAME>".base64_encode("")."</USER_NAME>";
					$xml.="<LINK_ELEMENT>".base64_encode("")."</LINK_ELEMENT>";
					$xml.="</NOTIFICATION>";
	    	}
	    	$xml.="</NOTIFICATIONS>";
	        return $xml;
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
	    	//$xml = $sql;
	    	query($sql);
	    	//query("UPDATE "+Common::Table("GEN_MESSAGE")+" ";//,Common::Table("GEN_MESSAGE"),array("STATUS"=>1),"ID_U_RECEIVE=".$idu." AND (STATUS is NULL OR STATUS=0)");
	    	$xml="<NOTIFICATIONS>";
	    	while($item = mysql_fetch_assoc($messages)){
	    			$xml.="<NOTIFICATION>";
					$xml.="<MODULE_ELEMENT>".base64_encode("")."</MODULE_ELEMENT>";
					$xml.="<ID_ELEMENT>".base64_encode($item['ID_MESS'])."</ID_ELEMENT>";
					$xml.="<TITLE_ELEMENT>".base64_encode("")."</TITLE_ELEMENT>";
					$xml.="<CONTENT_ELEMENT>".base64_encode($item['NOIDUNG'])."</CONTENT_ELEMENT>";
					$xml.="<START_TIME_ELEMENT>".base64_encode(date("H:i:s",strtotime($item['DATE_SEND'])))."</START_TIME_ELEMENT>";
					$xml.="<START_DATE_ELEMENT>".base64_encode(date("Y-m-d",strtotime($item['DATE_SEND'])))."</START_DATE_ELEMENT>";
					if($type==1){
						$xml.="<REMINDER_TIME_ELEMENT>".base64_encode(date("Y-m-d H:i:s",strtotime($item['REMINDER'])))."</REMINDER_TIME_ELEMENT>";
					}else{
						$xml.="<REMINDER_TIME_ELEMENT></REMINDER_TIME_ELEMENT>";
					}
					$xml.="<USER_NAME>".base64_encode($item['NAME_U_SEND'])."</USER_NAME>";
					$xml.="<LINK_ELEMENT>".base64_encode($item['LINK'])."</LINK_ELEMENT>";
					$xml.="</NOTIFICATION>";
	    	}
	    	$xml.="</NOTIFICATIONS>";
	        return $xml;
    	}
    }
}