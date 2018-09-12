<?php
class Email_account {
	
	var $_email_addr;
	var $_username;
	var $_password;
	var $_active;
	var $_incoming_port;
	var $_outgoing_port;
	var $_incoming_hostname;
	var $_outgoing_hostname;
	var $_incoming_protocol;
	var $_outgoing_protocol;
	var $_name_info;
	var $_ssl_in;
	var $_ssl_out;
	
	/**
	 * Luu du lieu vao csdl
	 * Tra ve id cua account vua them vao , that bai tra ve 0
	 *
	 */
	public function savetoDatabase($idobject){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql="";
		//var_dump($this);exit;
		if($idobject > 0)//truong hop cap nhat
		{
			$sql = "update  `email_account` 
			set `EMAIL_ADDR` = ?, `USERNAME` = ? , `PASSWORD`=?, `ACTIVE`=?,
			`INCOMING_PORT`=? , `OUTGOING_PORT`=? , `INCOMING_HOSTNAME`=?,
			`OUTGOING_HOSTNAME`=? , `INCOMING_PROTOCOL`=?, `OUTGOING_PROTOCOL`=?,
			`NAME_INFO`=?,`SSL_IN`=?,`SSL_OUT`=?
			where `ID_EAC` =? ";
		
		}else { //truong hop them moi
				$sql =" Insert into `email_account`  
			(`EMAIL_ADDR` , `USERNAME` , `PASSWORD` , `ACTIVE` , `INCOMING_PORT`,
			 `OUTGOING_PORT`,`INCOMING_HOSTNAME` ,`OUTGOING_HOSTNAME` , `INCOMING_PROTOCOL`,
			 `OUTGOING_PROTOCOL` , `NAME_INFO`,`SSL_IN`,`SSL_OUT`
			)
			values (?,?,?,?,?,?,?,?,?,?,?,?,?)
			";
		}
		
		
		
		//try{
			$arrdata = $this->objectToArraySquenToSave();	
			$stm = $dbAdapter->prepare($sql);
			if($idobject > 0){
				
				array_push($arrdata,$idobject);
				$re = $stm->execute($arrdata);
				return $idobject;
			}
					
			else {
				$re = $stm->execute($arrdata);
				return $dbAdapter->lastInsertId('email_account','ID_EAC');	
			}
			
		//}catch (Exception $ex){
			return 0;
		//}
		
	}
	/**
	 * Lay doi tuong tu co so du lieu
	 *
	 * @param integer_11 $idobject
	 * @return NULL : neu that bai , doi tuong neu thanh cong
	 */
	
	static public function getFromDatabase($idobject){
		
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql =" 
		select * from `email_account` where `ID_EAC`=?
		";
		try{
			$stm = $dbAdapter->query($sql,array($idobject));
			$data = $stm->fetch();
			/**tao doi tuong */
			$object = new Email_account();
			$object->_active = $data["ACTIVE"];
			$object->_username = $data["USERNAME"];
			$object->_password = $data["PASSWORD"];
			$object->_email_addr = $data["EMAIL_ADDR"];
			$object->_incoming_port = $data["INCOMING_PORT"];
			$object->_outgoing_port = $data["OUTGOING_PORT"];
			$object->_incoming_hostname = $data["INCOMING_HOSTNAME"];
			$object->_outgoing_hostname = $data["OUTGOING_HOSTNAME"];
			$object->_incoming_protocol = $data["INCOMING_PROTOCOL"];
			$object->_outgoing_protocol = $data["OUTGOING_PROTOCOL"];
			$object->_name_info = $data["NAME_INFO"];
			$object->_ssl_in = $data["SSL_IN"];
			$object->_ssl_out = $data["SSL_OUT"];
			
			return $object; // tra ve doi tuong email_account
		}catch (Exception $ex){
			return null;
		}
		
		
	}
	
	static function isAccountOfUser($id_u,$id_eac){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "
				select count(*) as DEM from email_account eac
				inner join fk_user_emailaccounts fk on eac.`ID_EAC` = fk.`ID_E_A`
				where fk.`ID_U`=? and  eac.`ID_EAC`=?
			";
			$qr = $dbAdapter->query($sql,array($id_u,$id_eac));
			$re = $qr->fetch();
			if($re["DEM"]>0)
				return 1;
			else
				return 0;

	}

	private function objectToArraySquenToSave(){
		$arr = array(
		$this->_email_addr,
		$this->_username,
		Mail_Auth::encryptPassword($this->_password),
		$this->_active,
		$this->_incoming_port,
		$this->_outgoing_port,
		$this->_incoming_hostname,
		$this->_outgoing_hostname,
		$this->_incoming_protocol,
		$this->_outgoing_protocol,
		$this->_name_info,
		$this->_ssl_in,
		$this->_ssl_out
		);
		
		return $arr;
		
	}
	
	private function objectToArraySquen(){
		$arr = array(
		$this->_email_addr,
		$this->_username,
		$this->_password,
		$this->_active,
		$this->_incoming_port,
		$this->_outgoing_port,
		$this->_incoming_hostname,
		$this->_outgoing_hostname,
		$this->_incoming_protocol,
		$this->_outgoing_protocol,
		$this->_name_info,
		$this->_ssl_in,
		$this->_ssl_out
		);
		
		return $arr;
		
	}
	
	static public function objectToArray($obj){
		//kiem tra doi tuong co thuoc kieu Email_account
		$class_name = get_class($obj);
	 	if($class_name != "Email_account")
	 		return null;
		$arr = array(
		"EMAIL_ADDR" => $obj->_email_addr,
		"USERNAME" =>$obj->_username,
		"PASSWORD" => $obj->_password,
		"ACTIVE" => $obj->_active,
		"INCOMING_PORT" => $obj->_incoming_port,
		"OUTGOING_PORT" => $obj->_outgoing_port,
		"INCOMING_HOSTNAME" => $obj->_incoming_hostname,
		"OUTGOING_HOSTNAME" => $obj->_outgoing_hostname,
		"INCOMING_PROTOCOL" => $obj->_incoming_protocol,
		"OUTGOING_PROTOCOL" => $obj->_outgoing_protocol,
		"NAME_INFO"=> $obj->_name_info,
		"SSL_IN"=> $obj->_ssl_in,	
		"SSL_OUT"=> $obj->_ssl_out
		);
		
		return $arr;
		
	}
	
	 static public function arrayToObject($arr_data){
		$obj = new Email_account();
		$obj->_email_addr = $arr_data["EMAIL_ADDR"];
		$obj->_username = $arr_data["USERNAME"];
		$obj->_password =  $arr_data["PASSWORD"];
		$obj->_active = $arr_data["ACTIVE"];
		$obj->_incoming_port = $arr_data["INCOMING_PORT"];
		$obj->_outgoing_port = $arr_data["OUTGOING_PORT"];
		$obj->_incoming_hostname = $arr_data["INCOMING_HOSTNAME"];
		$obj->_outgoing_hostname = $arr_data["OUTGOING_HOSTNAME"];
		$obj->_outgoing_protocol = $arr_data["OUTGOING_PROTOCOL"];
		$obj->_incoming_protocol = $arr_data["INCOMING_PROTOCOL"];
		$obj->_name_info = $arr_data["NAME_INFO"];
		$obj->_ssl_in = $arr_data["SSL_IN"];
		$obj->_ssl_out = $arr_data["SSL_OUT"];
		return $obj;
	}

	static public function getAllEmailAccountInfoInSystem($txtsearch_contact){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		
		$find_ct = "";
		$arr_param = array();
		if($txtsearch_contact != ""){
			$find_ct = " where concat(emp.`FIRSTNAME`,' ',emp.`LASTNAME`) like ? or ec.`EMAIL_ADDR` like ? ";
			$arr_param[] = "%$txtsearch_contact%";
			$arr_param[] = "%$txtsearch_contact%";
		}
		
		$sql="
			select -1 as ID_EAD , ec.`EMAIL_ADDR` as EMAIL_ADDR_FR , concat(emp.`FIRSTNAME`,' ',emp.`LASTNAME`) as NAME_FRIEND
			, -1 as ID_EG , dep.`NAME` as COMMENT
			from
			`email_account` ec
			inner join fk_user_emailaccounts fk_e_u on ec.`ID_EAC` = fk_e_u.`ID_E_A` 
			inner join `qtht_users` user on fk_e_u.`ID_U` = user.`ID_U`
			inner join `qtht_employees` emp on user.`ID_EMP`=emp.`ID_EMP`
			inner join `qtht_departments` dep on emp.`ID_DEP` = dep.`ID_DEP` 
			$find_ct
		";
			
		//try{
			$qr = $dbAdapter->query($sql,$arr_param);
			return $qr->fetchAll();
		//}catch(Exception $ex){
			return array();
		//}
	}

	static function deleteAccout($id_eac){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$dbAdapter->delete("email_account","ID_EAC=$id_eac");
			$dbAdapter->delete("fk_user_emailaccounts","ID_EAC=$id_eac");
			return 1;
		}catch (Exception $ex){
			return 0;
		}
	}

	static function updateAccountMain($id_eac,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//Update lai la is_main = 1
		$sql = "update `email_account` set IS_MAIN=1 where ID_EAC=?";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_eac));
			
		}catch (Exception $ex){
			return 0;
		}

		//update lai is_main cua cac dia chi khac nguoi dung
		$sql = "
			update `email_account` set IS_MAIN=0 where ID_EAC != ? and ID_EAC in 
			(
				select ID_E_A from fk_user_emailaccounts where ID_U = ?
			)
		
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_eac,$id_u));
			
		}catch (Exception $ex){
			return 0;
		}
		return 1;
		
	}

	
}
?>