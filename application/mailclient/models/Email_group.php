<?php
require_once 'mailclient/models/Email_address.php';
class Email_group {
	var $_id_eg;
	var $_name;
	var $_id_u;
	
	function ___contruct(){
	
	}

	function saveToDatabase($idobject){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql="";
		if($idobject > 0)//truong hop cap nhat
		{
			$sql = "update  `email_group` 
			set   `NAME`=? , `ID_U`=? 
			where `ID_EG`=?";
		
		}else { //truong hop them moi
			$sql =" Insert into `email_group`  
			(`NAME` , `ID_U` )
			values (?,?)
			";
		}
		
		//try{
			$arrdata = $this->objectToArraySquen();	
			$stm = $dbAdapter->prepare($sql);
			if($idobject > 0){
				
				array_push($arrdata,$idobject);
				$re = $stm->execute($arrdata);
				return $idobject;
			}
					
			else {
				$re = $stm->execute($arrdata);
				return $dbAdapter->lastInsertId('email_group','ID_EG');	
			}
			
		//}catch (Exception $ex){
			return 0;
		//}
	}

	function getFromDatabase(){
	
	}

	static function deleteGroup($id_eg,$id_u){
		//return $id_eg."ddddd".$id_u; 
		
		if(Email_address::deleteByGroup($id_eg,$id_u)){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			delete from `email_group` where ID_U = ? and ID_EG=?
		";
		//try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_u,$id_eg));
			return 1;
		//}catch(Exception $ex){
			return 0;
		//}
		}
	}
	
	private function objectToArraySquen(){
		return array(
		$this->_name,
		$this->_id_u
		);
	}

	static public function objectToArray($obj){
	
	}
	static public function arrayToObject($arr_data){
	
	}

	static public function getGroupsByIdUser($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from `email_group` where ID_U = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			return $qr->fetchAll();
		}catch (Exception $ex) {
			return array();
		}
	}

	static public function toComboGroupsByIdUser($id_u,$is_group_sy){
		
		$config = Zend_Registry::get('config');
		if(! $is_group_sy )  $is_group_sy = 0;
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from `email_group` where ID_U = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			$re = $qr->fetchAll();
			//echo "<option value='0'>---nhóm---</option>";
			if($is_group_sy == 1 ) echo "<option value='-1'>Nội bộ cơ quan (".$config->sys_info->company.")</option>";
			
			foreach($re as $row){
				echo "<option value='" . $row["ID_EG"]. "'>". $row["NAME"]. "</option>";
			}
		}catch (Exception $ex) {
			echo "<option value='0'>--Chọn nhóm---</option>";
		}
		echo $is_group_sy;
	}

}
