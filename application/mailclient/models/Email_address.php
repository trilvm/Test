<?php
class Email_address {
	var $_id_ead;
	var $_id_u_own;
	var $_email_addr_fr;
	var $_name_friend;
	
	var $_comment;

	function ___contruct(){
	
	}

	function saveToDatabase($idobject){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql="";
		if($idobject > 0)//truong hop cap nhat
		{
			$sql = "update  `email_address` 
			set  `ID_U_OWN`=? , `EMAIL_ADDR_FR`=? , `NAME_FRIEND`=? , `COMMENT`=?
			where `ID_EAD`=?";
		
		}else { //truong hop them moi
			$sql =" Insert into `email_address`  
			(`ID_U_OWN` , `EMAIL_ADDR_FR` , `NAME_FRIEND` ,  `COMMENT`)
			values (?,?,?,?)
			";
		}
		
		try{
			$arrdata = $this->objectToArraySquen();	
			$stm = $dbAdapter->prepare($sql);
			if($idobject > 0){
				
				array_push($arrdata,$idobject);
				$re = $stm->execute($arrdata);
				return $idobject;
			}
					
			else {
				$re = $stm->execute($arrdata);
				return $dbAdapter->lastInsertId('email_address','ID_EAD');	
			}
			
		}catch (Exception $ex){
			return 0;
		}
	}

	static function getFromDatabase($id_object){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$object = new Email_address();
		$sql = "
			select * from `email_address` where `ID_EAD`=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_object));
			$re = $qr->fetch();
			$object->_id_ead = $re["ID_EAD"];
			$object->_id_u_own = $re["ID_U_OWN"];
			$object->_email_addr_fr = $re["EMAIL_ADDR_FR"];
			$object->_name_friend= $re["NAME_FRIEND"];
			
			$object->_comment= $re["COMMENT"];
			return $object;

		}catch(Exception $ex){
			return null;
		}	
	}

	private function objectToArraySquen(){
		return array(
		$this->_id_u_own,
		$this->_email_addr_fr,
		$this->_name_friend,
		
		$this->_comment
		);
	}

	static public function objectToArray($obj){
		
	}
	static public function arrayToObject($param){
		$email_addr = new Email_address();
		$email_addr->_id_u_own = $param["ID_U_OWN"];
		$email_addr->_email_addr_fr = $param["EMAIL_ADDR_FR"];
		$email_addr->_name_friend = $param["NAME_FRIEND"];
		
		$email_addr->_comment = $param["COMMENT"];
		//var_dump($email_addr);exit;
		return $email_addr;
	}

	
	static public function getContactsNoGroupByUser($id_u){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from `email_address` where ID_U_OWN = ? and IS_G = 0
		";
		try{
			$qr = $dbAdapter->query($sql, array($id_u));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	
	static function getAllContactById($id_u){
		/*$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from `email_address` where ID_U_OWN = ? 
		";
		try{
			$qr = $dbAdapter->query($sql, array($id_u));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}*/
	}
	
	static public function getContactsById_group($id_eg,$txtsearch_contact,$where_g){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$find_ct = "";
		$find_g = " `ID_EG`=? ";
		$arr_param = array($id_eg);
		if($where_g == -2){
			$find_g = "";
			$arr_param = array();
		}
		
		if($txtsearch_contact != ""){
			
			$find_ct = " (`EMAIL_ADDR_FR` like ? or `NAME_FRIEND` like ? ) ";
			$arr_param[] = "%$txtsearch_contact%";
			$arr_param[] = "%$txtsearch_contact%";
			
		}
		
		$where = "";
		
		if($find_g != ""){
			$where = " where ". $find_g;
			//echo $where;
			if($find_ct != "")
				$where = $where ." and ". $find_ct;
		}
		else{
			if($find_ct != "")
			$where = " where ".$find_ct;
		}
		$sql = "
			select ed.* from 
			`email_address`  ed
			inner join fk_email_address_group fk on ed.ID_EAD = fk.ID_EAD
			$where
		";
		//echo $sql ; exit;
		try{
			$qr = $dbAdapter->query($sql, $arr_param);
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	
	

	static public function deleteFromDatabase($id_ead,$id_u){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql = "
			delete  from `email_address`  where `ID_EAD`=? and `ID_U_OWN` =?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_ead,$id_u));

			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}

	static public function deleteByGroup($id_eg,$id_u){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql = "
			delete  from `fk_email_address_group`  where `ID_EG`=? 
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_eg));
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}

	static function updateContactToGroups($id_contact , $arr_g){
		$dbAdapter = Zend_DB_Table::getDefaultAdapter();
		
		//xoa du lieu cu
		$sql ="delete from `fk_email_address_group` where `ID_EAD`=?";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_contact));
		}catch(Exception $ex){
			return 0;
		}

		$sql = "insert into `fk_email_address_group` (`ID_EAD`,`ID_EG`) values(?,?)";
		$ln = 0;
		foreach($arr_g as $id_g){
			try{
					$stm = $dbAdapter->prepare($sql);
					$stm->execute(array($id_contact,$id_g));
					$ln = 1;
					
			}catch(Exception $ex){
					
			}
		}
		if($ln==1){
			Email_address::updateIsG($id_contact);
			
		}
		return 1;
	}



	
	static function addContactToGroups($id_contact , $arr_g){
		$dbAdapter = Zend_DB_Table::getDefaultAdapter();
		$sql = "insert into `fk_email_address_group` (`ID_EAD`,`ID_EG`) values(?,?)";
		$ln = 0;
		foreach($arr_g as $id_g){
			try{
					$stm = $dbAdapter->prepare($sql);
					$stm->execute(array($id_contact,$id_g));
					$ln = 1;
					
			}catch(Exception $ex){
					
			}
		}
		if($ln==1){
			Email_address::updateIsG($id_contact);
			
		}
		return 1;
	}


	static function updateIsG($id_ead){
		$dbAdapter = Zend_DB_Table::getDefaultAdapter();
		
		$sql = "update `email_address` set `IS_G` =1 where ID_EAD=?";
		
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_ead));
		}catch(Exception $ex){
		
		}
		

		return 1;
	}

	static function getGroupsByContact($id_ead){
		$dbAdapter = Zend_DB_Table::getDefaultAdapter();
		$sql = "select * from `fk_email_address_group` where `ID_EAD`=? ";
		//try{
			$stm = $dbAdapter->query($sql,array($id_ead));
			return $re = $stm->fetchAll();
			
		//}catch(Exception $ex){
			return array();
		//}
		

		//return 1;

	}
}
