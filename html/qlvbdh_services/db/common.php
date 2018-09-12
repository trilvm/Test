<?php
class Common
{
	
	 public static function ServiceAuthentication($username, $password) {
        $sid = "0";
        $sql = sprintf("SELECT * FROM WEB_USERS where USERNAME = '%s' and PASSWORD = '%s'",
                        mysql_real_escape_string($username), $password);
        $user = query($sql);
        if (mysql_num_rows($user) == 1) {
            return true;
        }
        return false;
    }
	public static function checkUserAndPass($username,$password)
	{
		
		$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s' ",
			mysql_real_escape_string($username));
		$user = query($sql);
		if(mysql_num_rows($user)<1)
		{
			return 'nonValidateUser';
		}else
		{
			$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s' AND PASSWORD = '%s'",
			mysql_real_escape_string($username),$password);
			$user = query($sql);
		
			if(mysql_num_rows($user)==1)
			{
				return 'okAccount';
			}
			else
			{
				return 'nonValidatePass';
			}
		}
	}
	public static function LoginDesktop($username,$password)
	{
		
		$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s' ",
			mysql_real_escape_string($username));
		$user = query($sql);
		if(mysql_num_rows($user)<1)return "nonValidateUser";
		
		$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s' and PASSWORD = '%s'",
			mysql_real_escape_string($username),$password);
		$user = query($sql);
		if(mysql_num_rows($user)==1){
			$sql = sprintf("SELECT * FROM DES_LOGIN where USER = '%s'",
				mysql_real_escape_string($username));
			$login = query($sql);
			if(mysql_num_rows($login)==1)
			{
				$sql = sprintf("UPDATE DES_LOGIN SET LASTACTIVITY=CURRENT_TIMESTAMP() WHERE USER='%s'",
					mysql_real_escape_string($username));
				query($sql);
			}else{
				$sql = sprintf("INSERT INTO DES_LOGIN(USER) VALUES('%s')",
					mysql_real_escape_string($username));
				query($sql);
			}
			return "okAccount";
		}else{
			return "nonValidatePass";
		}
	}
	public static function CheckUserExist($username){
		$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s'",
			mysql_real_escape_string($username));
		$user = query($sql);
		if(mysql_num_rows($user)==1){
			return 1;
		}else{
			return 0;
		}
	}
    /**
     * @return string
     */
    public static function Login($username,$password)
    {
		$sid = "0";
		$sql = sprintf("SELECT * FROM QTHT_USERS where USERNAME = '%s' and PASSWORD = '%s'",
			mysql_real_escape_string($username),$password);
		$user = query($sql);
		if(mysql_num_rows($user)==1){
			$user = mysql_fetch_assoc($user);
			$sid = md5(rand(1,1000000000).$username.md5(time()));
			$sql = sprintf("SELECT * FROM QTHT_SESSION WHERE ID_U = '%s'",
				$user['ID_U']);
			$session = query($sql);
			if(mysql_num_rows($session)==1){
				$session = mysql_fetch_assoc($session);
				$sql = sprintf("UPDATE QTHT_SESSION SET SESSION='%s',LIMIT_DATE='%s',IP='%s' WHERE ID_U = '%s'",
					$sid,date("Y-m-d H:i:s",time()+86400),$_SERVER['REMOTE_ADDR'],$user['ID_U']);
				query($sql);
			}else{
				$sql = sprintf("INSERT INTO QTHT_SESSION(SESSION,LIMIT_DATE,IP,ID_U) VALUES('%s','%s','%s','%s')",
					$sid,date("Y-m-d H:i:s",time()+86400),$_SERVER['REMOTE_ADDR'],$user['ID_U']);
				query($sql);
			}
		}
		return $sid;
    }
	public static function Login2($username,$password)
    {
		$sid = "0";
		$sql = sprintf("SELECT * FROM WEB_USERS where USERNAME = '%s' and PASSWORD = '%s'",
			mysql_real_escape_string($username),$password);
		$user = query($sql);
		if(mysql_num_rows($user)==1){
			return true;
		}
		return false;
    }
    public static function GetAuth($sid){
        $sql = sprintf("SELECT * FROM QTHT_USERS u inner join QTHT_SESSION s on u.ID_U = s.ID_U WHERE s.SESSION='%s' AND IP='%s'",mysql_real_escape_string($sid),$_SERVER['REMOTE_ADDR']);
        $user = query($sql);
        if(mysql_num_rows($user)==1){
        	$user = mysql_fetch_assoc($user);
        	return $user['ID_U'];
        }
        return 0;
    }
	public static function GetID_U($sid){
        $sql = sprintf("SELECT * FROM QTHT_USERS WHERE USERNAME='%s'",mysql_real_escape_string($sid));
        $user = query($sql);
        if(mysql_num_rows($user)==1){
        	$user = mysql_fetch_assoc($user);
        	return $user['ID_U'];
        }
        return 0;
    }
	public static function getYear(){
   		$d=getdate();
   		$year = $d['year'];
    	return $year;
    }
	public static function Table($tablename){
		return $tablename."_".Common::getYear();
	}
	public static function Deseriallize($string){
		$r = array();
		$r = explode("~",$string);
		for($i=0;$i<count($r);$i++){
			$r[$i] = str_replace("&split;","~",$r[$i]);
			$r[$i] = str_replace("&amp;","&",$r[$i]);
		}
		return $r;
	}
	public static function SerializeString($s){
		$s = str_replace("&","&amp;",$s);
		$s = str_replace("~","&split;",$s);
		return $s;
	}
	public static function SerializeData($object){
		$data = "";
		$fields = mysql_num_fields($object);
		$data .= $fields;
		for($i=0; $i < $fields; $i++) {
		    $fieldname  = mysql_field_name($object, $i);
		    $data .= "~".$fieldname;
		}
		while ($row = mysql_fetch_assoc($object)) {
			for($i=0; $i < $fields; $i++) {
				$fieldname  = mysql_field_name($object, $i);
			    $data .= "~".Common::SerializeString($row[$fieldname]);
			}
		}
		return $data;
	}
	public static function SerializeDataAndUpdateIsGet($object,$table,$idfield){
		$data = "";
		$fields = mysql_num_fields($object);
		$data .= $fields;
		for($i=0; $i < $fields; $i++) {
		    $fieldname  = mysql_field_name($object, $i);
		    $data .= "~".$fieldname;
		}
		while ($row = mysql_fetch_assoc($object)) {
			for($i=0; $i < $fields; $i++) {
				$fieldname  = mysql_field_name($object, $i);
			    $data .= "~".Common::SerializeString($row[$fieldname]);
			}
			query("UPDATE $table SET ISGET=1 WHERE $idfield = ".$row[$idfield]);
		}
		return $data;
	}
        public static function SerializeDataAndDelete($object,$table,$idfield){
		$data = "";
		$fields = mysql_num_fields($object);
		$data .= $fields;
                $arr_id = array();
                $arr_id[]=0;
		for($i=0; $i < $fields; $i++) {
		    $fieldname  = mysql_field_name($object, $i);
		    $data .= "~".$fieldname;
		}
		while ($row = mysql_fetch_assoc($object)) {
			for($i=0; $i < $fields; $i++) {
				$fieldname  = mysql_field_name($object, $i);
			    $data .= "~".Common::SerializeString($row[$fieldname]);
			}
                        $arr_id[]=$row[$idfield];
			//query("DELETE FROM $table  WHERE $idfield = ".$row[$idfield]);
		}
                  query("DELETE FROM $table  WHERE $idfield in (".implode(",",$arr_id).")");
		return $data;
	}

	public static function isAdministrators($username,$password){
		$sid = "0";
		$sql = sprintf("SELECT * FROM qtht_users  u
		inner join fk_users_groups fk on u.ID_U = fk.ID_U
		inner join qtht_groups g on fk.ID_G = g.ID_G 
		where USERNAME = '%s' and PASSWORD = '%s' and g.CODE = 'NQT'  " ,
			mysql_real_escape_string($username),md5(mysql_real_escape_string($password)));
			
		$user = query($sql);
		if(mysql_num_rows($user)==1){
			return true;
		}
		return false;
	}

	public static function SerializeDataXML($object){
		$data = "<DATA>";
		$fields = mysql_num_fields($object);
		//$data .= $fields;
		//for($i=0; $i < $fields; $i++) {
		//    $fieldname  = mysql_field_name($object, $i);
		//    $data .= "~".$fieldname;
		//}
		while ($row = mysql_fetch_assoc($object)) {
			$data.="<ITEM>";
			for($i=0; $i < $fields; $i++) {
				//$fieldname  = mysql_field_name($object, $i);
			    //$data .= "~".Common::SerializeString($row[$fieldname]);
				$fieldname  = mysql_field_name($object, $i);
				$data.="<".$fieldname.">";
				$data.=base64_encode($row[$fieldname]);
				$data.="</".$fieldname.">";
			}
			$data.="</ITEM>";
		}
		$data .= "</DATA>";
		return $data;
	}
	public static function Base64File($filename){
		$result = "";
		$fh = fopen($filename, 'rb'); 

		$cache = ''; 
		$eof = false; 

		while (1) { 

			if (!$eof) { 
				if (!feof($fh)) { 
					$row = fgets($fh, 4096); 
				} else { 
					$row = ''; 
					$eof = true; 
				} 
			} 

			if ($cache !== '') 
				$row = $cache.$row; 
			elseif ($eof) 
				break; 

			$b64 = base64_encode($row); 
			$put = ''; 

			if (strlen($b64) < 76) { 
				if ($eof) { 
					$put = $b64."\n"; 
					$cache = ''; 
				} else { 
					$cache = $row; 
				} 

			} elseif (strlen($b64) > 76) { 
				do { 
					$put .= substr($b64, 0, 76)."\n"; 
					$b64 = substr($b64, 76); 
				} while (strlen($b64) > 76); 

				$cache = base64_decode($b64); 

			} else { 
				if (!$eof && $b64{75} == '=') { 
					$cache = $row; 
				} else { 
					$put = $b64."\n"; 
					$cache = ''; 
				} 
			} 

			if ($put !== '') { 
				$result .= $put;
			} 
		} 
		fclose($fh); 
		return $result; 
	}
	static function DeseriallizeToArray($string)
	{
			$r = array();
			$r = explode("~",$string);
			for($i=0;$i<count($r);$i++){
				$r[$i] = str_replace("&split;","~",$r[$i]);
				$r[$i] = str_replace("&amp;","&",$r[$i]);
			}
			$array_result = array();
			$colnum = $r[0]; //so cot
			$incol = 1;
			$countr = count($r);

			$num_object = (int)((int)$countr/(int)$colnum);

			for($i =1 ; $i < $num_object ; $i ++ ){
				$temp = array();
				for($j = 0; $j < $colnum ; $j++){
					 $temp["".$r[$j+1].""] = $r[$i*$colnum+$j+1];
				}
				$array_result[$i-1] = $temp;


			}
			return $array_result ;

	}
    
	function GetDateTime(){
		return date("d-m-Y");
	}
    
    /**
     * @author Baotq
     * @name tạo nhiêu folder từ một đường dẫn
     * @param <string> $path     
     * @param <integer> $mode CHMOD     
     * @return <boolean>
     */
    function rmkdir($path, $mode = 0755) {
        $path = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $path), "/");
        $e = explode("/", ltrim($path, "/"));
        if (substr($path,0,1) == "/") {
            $e[0] = "/" . $e[0];
        }
        $c = count($e);
        $cp = $e[0];
        for ($i = 1; $i < $c; $i++) {
            if (!is_dir($cp) && !@mkdir($cp, $mode)) {
                return false;
            }
            $cp .= "/" . $e[$i];
        }
        return @mkdir($path, $mode);
    }
}