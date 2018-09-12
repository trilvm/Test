<?php 
require_once('mailclient/models/Email_parts.php');
class Email_messages{
	var $_id_u;
	var $_addr_from;
	var $_addr_to;
	var $_addr_bcc;
	var $_addr_cc;
	var $_subject;
	var $_folder_code;
	var $_is_attactment;
	var $_is_read;
	var $_is_mark;
	var $_time;
	var $_id_sysobject;
	var $_content_type;
	var $_num_part;
	var $_uniqueid_ms;
	public function savetoDatabase($idobject){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql="";
		if($idobject > 0)//truong hop cap nhat
		{
			$sql = "update  `email_messages` 
			set 
			`ID_U`=? , `ADDR_FROM`=? , `ADDR_TO`=? , `ADDR_BCC`=? ,`ADDR_CC`=?,
			`SUBJECT`=?,`FOLDER_CODE`=?,`IS_ATTACTMENT`=?,`IS_READ`=?,
			`IS_MARK`=?,`TIME`=?,`ID_SYSOBJECT`=?,`CONTENT_TYPE`=?,`NUM_PART`=?,`UNIQUEID_MS`=?
			where ID_EM = ?
			";
		
		}else { //truong hop them moi
				$sql =" Insert into `email_messages`  
			(
			`ID_U`, `ADDR_FROM` , `ADDR_TO` , `ADDR_BCC` ,`ADDR_CC`,
			`SUBJECT`,`FOLDER_CODE`,`IS_ATTACTMENT`,`IS_READ`,
			`IS_MARK`,`TIME`,`ID_SYSOBJECT`,`CONTENT_TYPE`,`NUM_PART`,`UNIQUEID_MS`
			)
			values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
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
				
				return  $dbAdapter->lastInsertId('email_messages','ID_EM');	
			}
			
		}catch (Exception $ex){
			return 0;
		}
		
	}
	
	static function getFromDatabase($idobject){
		$key = Crypt_Key::createUserKey();
		$crypt = new Crypt_Rijndael($key);
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql =" 
		select * from `email_messages` where `ID_EM`=?
		";
		try{
			$stm = $dbAdapter->query($sql,array($idobject));
			$data = $stm->fetch();
			//var_dump($data);
			/**tao doi tuong */
			$object = new Email_messages();
			$object->_id_u = $data['ID_U'];
			$object->_addr_from = $data['ADDR_FROM'];
			$object->_addr_to = $data['ADDR_TO'];
			$object->_addr_bcc = $data['ADDR_BCC'];
			$object->_addr_cc = $data['ADDR_CC'];
			$object->_subject = $crypt->doDecryptBlock($data['SUBJECT']);
			$object->_folder_code = $data['FOLDER_CODE'];
			$object->_is_attactment = $data['IS_ATTACTMENT'];
			$object->_is_read = $data['IS_READ'];
			$object->_is_mark = $data['IS_MARK'];
			$object->_time = $data['TIME'];
			$object->_id_sysobject = $data['ID_SYSOBJECT'];
			$object->_content_type = $data['CONTENT_TYPE'];
			$object->_num_part = $data['NUM_PART'];
			$object->_uniqueid_ms = $data['UNIQUEID_MS'];
			return $object; // tra ve doi tuong email_account
		}catch (Exception $ex){
			return null;
		}
	}
	
	
	public function objectToArraySquen(){
		return array(
		$this->_id_u,
		$this->_addr_from,
		$this->_addr_to,
		$this->_addr_bcc,
		$this->_addr_cc,
		$this->_subject,
		$this->_folder_code,
		$this->_is_attactment,
		$this->_is_read,
		$this->_is_mark,
		$this->_time,
		$this->_id_sysobject,
		$this->_content_type,
		$this->_num_parts,
		$this->_uniqueid_ms
		);
	}
	
	static function objectToArray($obj){
		//kiem tra doi tuong co thuoc kieu Email_messages
		$class_name = get_class($obj);
	 	if($class_name != "Email_messages")
	 		return null;
		$arr = array(
			'ID_U' => $this->_id_u,
			'ADDR_FROM' => $this->_addr_from ,
			'ADDR_TO' => $this->_addr_to ,
			'ADDR_BBC'=>$this->_addr_bcc,
			'ADDR_CC' =>$this->_addr_cc,
			'SUBJECT' =>$this->_subject,
			'ID_FOLDER' => $this->_folder_code,
			'IS_ATTACTMENT' => $this->_is_attactment,
			'IS_READ' => $this->_is_read,
			'IS_MARK' => $this->_is_mark,
			'TIME' => $this->_time,
			'ID_SYSOBJECT' => $this->_id_sysobject,
			'CONTENT_TYPE' => $this->_content_type,
			'NUM_PART' => $this->_num_part,
			'UNIQUEID_MS'=>$this->_uniqueid_ms
		);
		
		return $arr;
	}
	
	static function arrayToObject($arr_data){
		$obj = new Email_messages();
		$obj->_id_u = $arr_data['ID_U'];
		$obj->_addr_from = $arr_data['ADDR_FROM'];
		$obj->_addr_to = $arr_data['ADDR_TO'];
		$obj->_addr_bcc = $arr_data['ADDR_BCC'];
		$obj->_addr_cc = $arr_data['ADDR_CC'];
		$obj->_subject = $arr_data['SUBJECT'];
		$obj->_folder_code = $arr_data['FOLDER_CODE'];
		$obj->_is_attactment = $arr_data['IS_ATTACTMENT'];
		$obj->_is_read = $arr_data['IS_READ'];
		$obj->_is_mark = $arr_data['IS_MARK'];
		$obj->_time = $arr_data['TIME'];
		$obj->_id_sysobject = $arr_data['ID_SYSOBJECT'];
		$obj->_content_type = $arr_data['CONTENT_TYPE'];
		$obj->_num_part = $arr_data['NUM_PART'];
		$obj->_uniqueid_ms = $arr_data['NUM_PART'];
		return $obj;
	}
	
	static function getPartsMessageById($id){
		
		
		//tra ve mot mang cac part
		$dbAdaper = Zend_Db_Table::getDefaultAdapter();
		$sql ="
		select * from `email_parts` where `ID_EM`=?
		";
		
		$arr_part = array();
		try{
			$stm = $dbAdaper->query($sql,array($id));
			$data_parts = $stm->fetchAll();
			foreach ($data_parts as $item){
				if(Email_messages::checkUserOwnIdMessage($id)){
					$part = Email_parts::arrayToObject($item);
					array_push($arr_part,$part);
					
				}
			}
			
		}catch(Exception $ex){
			
		}
		return $arr_part;
	}
	
	static function checkUserOwnIdMessage($id_em){
		$user = Zend_Registry::get('auth')->getIdentity();
		//tra ve mot mang cac part
		$dbAdaper = Zend_Db_Table::getDefaultAdapter();
		$sql ="
		select * from `email_messages` where `ID_EM`=?
		";
		
		$arr_part = array();
		try{
			$stm = $dbAdaper->query($sql,array($id_em));
			$data= $stm->fetch();
			
			if($data["ID_U"] == $user->ID_U){
				return 1;			
		}
			
		}catch(Exception $ex){
			return 0;
		}
		
		return 0;
	}

	static function updateAttributes($id_em ,$arr_name_valueAtts ){
		
		$dbAdapter = Zend_Db_Adapter::getDefaultAdapter();
		try{
			$dbAdapter->update("email_messages",$arr_name_valueAtts,"ID_EM=$id_em" );
			return 1;
		}catch (Exception $ex){
			return 0;
		}
			
	}
	
	


	static function sentMessageToDraft($id_em){
			$arr_data = array(
				"FOLDER_CODE"=>"draft"
			);
			Email_messages::updateAttributes($id_em,$arr_data);
	}
	
	static function sentMessageToTrash($id_em){
			$arr_data = array(
				"FOLDER_CODE"=>"trash"
			);
			return Email_messages::updateAttributes($id_em,$arr_data);
	}


	static function deleteMessage($id_em){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			if( Email_messages::checkUserOwnIdMessage($id_em)){
				$parts = Email_messages::getPartsMessageById($id_em);
				//var_dump($parts);exit;
				$re = $dbAdapter->delete("email_messages","ID_EM = $id_em");
				if($re > 0){
					foreach($parts as $part)
						Email_parts::deletePart($part->_id);
				}
			}
	}

	static function checkIsRead($id_em,$status){
		$status = (int)$status;
		if($status > 1) $status = 1;
		$arr_data = array(
				"IS_READ"=>$status
			);
		return Email_messages::updateAttributes($id_em,$arr_data);
	}

	static function checkIsFavorite($id_em,$status){
		$status = (int)$status;
		if($status > 1) $status = 1;
		$arr_data = array(
				"IS_MARK"=>$status
			);
		return Email_messages::updateAttributes($id_em,$arr_data);
	}
	
}
?>