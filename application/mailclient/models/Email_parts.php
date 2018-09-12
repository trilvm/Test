<?php
class Email_parts {
	
	var $_content_type;
	var $_name;
	var $_id_em;
	var $_filename;
	var $_is_attactment;
	var $_is_inline;
	var $_id;
	/**
	 * Luu du lieu vao csdl
	 * Tra ve id cua account vua them vao , that bai tra ve 0
	 *
	 */
	

	
	public function savetoDatabase($idobject){
		$dbAdapter= Zend_Db_Table::getDefaultAdapter();
		$sql="";
		if($idobject > 0)//truong hop cap nhat
		{
			$sql = "update  `email_parts` 
			set `CONTENT_TYPE`=? , `NAME`=? , `ID_EM`=? ,`FILENAME`=?,`IS_ATTACTMENT`=?,`IS_INLINE`=?,
			where `ID_MP` =? ";
		
		}else { //truong hop them moi
				$sql =" Insert into `email_parts`  
			(`CONTENT_TYPE` , `NAME` , `ID_EM` , `FILENAME` , `IS_ATTACTMENT`, `IS_INLINE`)
			values (?,?,?,?,?,?)
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
				return $dbAdapter->lastInsertId('email_parts','ID_MP');	
			}
			
		}catch (Exception $ex){
			return 0;
		}
		
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
		select * from `email_parts` where `ID_MP`=?
		";
		try{
			$stm = $dbAdapter->query($sql,array($idobject));
			$data = $stm->fetch();
			//var_dump();
			/**tao doi tuong */
			$object = new Email_parts();
			$object->_content_type = $data["CONTENT_TYPE"];
			$object->_name = $data["NAME"];
			$object->_id_em = $data["ID_EM"];
			$object->_filename = $data["FILENAME"];
			$object->_is_attactment = $data["IS_ATTACTMENT"];
			$object->_id = $data["ID_MP"];
			$object->_is_inline = $data["IS_INLINE"];
			return $object; // tra ve doi tuong email_account
		}catch (Exception $ex){
			return null;
		}
		
		
	}
	
	
	private function objectToArraySquen(){
		$arr = array(
		$this->_content_type,
		$this->_name,
		$this->_id_em,
		$this->_filename,
		$this->_is_attactment,
		$this->_is_inline
		);
		
		return $arr;
		
	}
	
	static public function objectToArray($obj){
		//kiem tra doi tuong co thuoc kieu Email_account
		$class_name = get_class($obj);
	 	if($class_name != "Email_parts")
	 		return null;
		$arr = array(
		"CONTENT_TYPE"=> $obj->_content_type,
		"NAME" => $obj->_name,
		"FILENAME" => $obj->_filename,
		"ID_EM" => $obj->_id_em,
		"IS_ATTACTMENT" => $obj->_is_attactment,
		"IS_INLINE" => $obj->_is_inline
		);
		
		return $arr;
		
	}
	
	 static public function arrayToObject($arr_data){
		$obj = new Email_parts();
		$obj->_content_type = $arr_data["CONTENT_TYPE"];
		$obj->_name = $arr_data["NAME"];
		$obj->_id_em = $arr_data["ID_EM"];
		$obj->_filename = $arr_data["FILENAME"];
		$obj->_is_attactment = $arr_data["IS_ATTACTMENT"];
		$obj->_is_inline = $arr_data["IS_INLINE"];
		$obj->_id = $arr_data["ID_MP"];
		return $obj;
	}
	
	function getContentType(){
		return $this->_content_type;	
	}
	
	function getContent(){
		//doc file
		
		$key  = Crypt_Key::createUserKey();
		
		$crypt = new Crypt_Rijndael($key);
		$content = $crypt->doGetDecryptDataUtf8FromFile($this->_filename);
		//$content = file_get_contents($this->_filename);
		//$content = mb_convert_encoding($content, 'UTF-8','');
		return $content;
	}
	
	function addNewFolderStorage($id_u){
		if(is_null($id_u))
			return 0;
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$dir_storage = $config_mail->mail->default->mailclientstorage_sent.DIRECTORY_SEPARATOR.$id_u;
		//echo $dir_storage;
		if(!file_exists($dir_storage)){
			try{
			mkdir($dir_storage);
			return 1;
			}catch (Exception $ex ){
				return 0;
			}
		}
		return 1;
		
	}

	static function  deletePart($id_part){
		//xoa trong csdl
		$id_part = (int)$id_part;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$part =  Email_parts::getFromDatabase($id_part);
		//xoa file
		
		try{
			unlink($part->_filename);
			$dbAdapter->delete("email_parts","ID_PART=$id_part");
			return 1;
		}catch (Exception $ex){
			return 0;
		}
		

	}
}
?>