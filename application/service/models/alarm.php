<?php
class Alarm
{
    /**
     * @return string
     */
    public static function GetData($user)
    {
    	global $db;
    	$sql = "
    	SELECT 
    		m.*, concat(emp.FIRSTNAME , ' ' , emp.LASTNAME) as NAME_U_SEND 
    	FROM 
    		".QLVBDHCommon::Table("GEN_MESSAGE")." m 
    		inner join QTHT_USERS u on m.ID_U_SEND = u.ID_U
    		inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
    	WHERE 
    		m.ID_U_RECEIVE=? AND (m.STATUS is NULL OR m.STATUS=0)";
    	$r = $db->query($sql,$user['ID_U']);
    	//$db->update(QLVBDHCommon::Table("GEN_MESSAGE"),array("STATUS"=>1),"ID_U_RECEIVE=".$user['ID_U']." AND (STATUS is NULL OR STATUS=0)");
    	$xml="<dsMessage>";
    	$data = $r->fetchAll();
    	foreach($data as $item){
    		$xml.="<tblMessage><ID_MESS>0".$item['ID_MESS']."</ID_MESS>";
		    $xml.="<NOIDUNG>".($item['NOIDUNG']==""?"*":base64_encode($item['NOIDUNG']))."</NOIDUNG>";
		    $xml.="<NAME_U_SEND>".base64_encode($item['NAME_U_SEND'])."</NAME_U_SEND>";
		    $xml.="<ID_U_SEND>0".$item['ID_U_SEND']."</ID_U_SEND>";
		    $xml.="<ID_U_RECEIVE>0".$item['ID_U_RECEIVE']."</ID_U_RECEIVE>";
		    $xml.="<DATE_SEND>".($item['DATE_SEND']==""?"*":$item['DATE_SEND'])."</DATE_SEND>";
		    $xml.="<STATUS>0".$item['STATUS']."</STATUS>";
		    $xml.="<LINK>".($item['LINK']==""?"*":base64_encode($item['LINK']))."</LINK>";
		    $xml.="<START_DATE>".($item['START_DATE']==""?"*":$item['START_DATE'])."</START_DATE>";
		    $xml.="<END_DATE>".($item['END_DATE']==""?"*":$item['END_DATE'])."</END_DATE>";
		    $xml.="<REMINDER>0".$item['REMINDER']."</REMINDER></tblMessage>";
    	}
    	$xml.="</dsMessage>";
        return $xml;
    }
}