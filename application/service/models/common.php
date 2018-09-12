<?php
class Common
{
    /**
     * @return string
     */
    public static function Login($username,$password)
    {
        global $db;
        $password = md5($password);
        $sql = "SELECT * FROM QTHT_USERS WHERE USERNAME = ? AND PASSWORD = ?";
        $r = $db->query($sql,array($username,$password));
        if($r->rowCount()==1){
        	
        	$sid = md5(rand(1,1000000000).$username.md5(time()));
        	$user = $r->fetch();
        	//Update table session
        	$sql = "SELECT * FROM QTHT_SESSION WHERE ID_U=?";
        	$r = $db->query($sql,$user['ID_U']);
        	if($r->rowCount()==1){
        		$db->update("QTHT_SESSION",array("SESSION"=>$sid,"LIMIT_DATE"=>date("Y-m-d H:i:s",time()+86400)),"ID_U=".$user['ID_U']);
        	}else{
        		$db->insert("QTHT_SESSION",array("SESSION"=>$sid,"ID_U"=>$user['ID_U'],"LIMIT_DATE"=>date("Y-m-d H:i:s",time()+86400)));
        	}
        	return array($sid,$user['ID_U']);
        }else{
        	return array();
        }
    }
    public static function GetAuth($sid){
    	global $db;
        $sql = "SELECT * FROM QTHT_USERS u inner join QTHT_SESSION s on u.ID_U = s.ID_U WHERE s.SESSION=?";
        $r = $db->query($sql,$sid);
        return $r->fetch();
    }
}