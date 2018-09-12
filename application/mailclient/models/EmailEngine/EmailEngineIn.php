<?php
require_once('mailclient/models/EmailEngine/EmailEngine.php');
require_once('mailclient/models/Email_account.php');
require_once('mailclient/models/EmailEngine/MailProtocolEngineFactory.php');
require_once('mailclient/models/Email_messages.php');
require_once('mailclient/models/Email_parts.php');

class EmailEngineIn implements EmailEngine {
	protected  $email_account ;
	public static $str;
	public function __construct($array_attr){
		//$email_account = new Email_account();
		$this->email_account = Email_account::arrayToObject($array_attr);
		
	}
	
	
	public function login(){
		
		
		$is_ssl = false;
		
		try{
		$protocol =  MailProtocolEngineFactory::createIncomingProtocol(
		$this->email_account->_incoming_protocol,
		$this->email_account->_incoming_hostname,
		$this->email_account->_incoming_port,
		(string)$this->email_account->_ssl_in
		);
		}catch (Exception $ex){
			echo $ex->__toString();
		}
		
		return $protocol->login($this->email_account->_username,$this->email_account->_password);
	}
	
	public function logout(){
		
	}
	
	public function testConnect(){
		
	}
	
	public static function checkMail(){
		
		$user = Zend_Registry::get('auth')->getIdentity();
		$email_accounts = MailClientModel::getEmailAccountByUser($user->ID_U);
		foreach ($email_accounts as $arr_data) {
			$emailenginein = new EmailEngineIn($arr_data);
			try{
				$emailenginein->receiveAllNewMail();
				
			}catch(Exception $ex){
				
			}
			
		}
		return 1;
	}
	/**
	*Lay du lieu cua mail part
	**/
	

	public function receivePartMail($part,$id_em){
		if(get_class($part) != 'Zend_Mail_Part')
			return 0;
		
		$is_att = 1;
		$header = $part->getHeaders();
		$data = $part->getContent();
		
		$is_att = 0;
		$is_inline = 0;
		$content_type = strtok($header['content-type'],';');
		
		$name = strtok(';');

		
		if($str_cont_dis = strtok($header["content-disposition"],';')=="attachment"){
			$is_att = 1;
			$name = strtok(';');
			$name = QLVBDHCommon::get_string_between($name,'"','"');
		}else if ($str_cont_dis == "inline") {
			$is_inline = 1;
			$name = strtok(';');
			$name = QLVBDHCommon::get_string_between($name,'"','"');
		}
		
		
		
		$email_part = new Email_parts();
		//lay content_type
		$email_part->_content_type = $content_type; 
		//lay ten file
		$email_part->_name = $name;
		//co phai dinh kem hay khong
		$email_part->_is_attactment = $is_att;
		$email_part->_id_em = $id_em;
		$email_part->_is_inline = $is_inline;
		//luu xuong file
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$user = Zend_Registry::get('auth')->getIdentity();
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$email_part->_filename ="";
		//Luu email part xuong database
		$id_new = $email_part->savetoDatabase(0);
		
		if($id_new > 0){
			$this->addNewFolderStorage($user->ID_U);
			$date = getdate();
			$nam = $date["year"];
			$thang = $date['mon'];
			$path_year = $config_mail->mail->default->mailclientstorage_inbox.DIRECTORY_SEPARATOR.$user->ID_U.DIRECTORY_SEPARATOR.$nam;
			
			if(!file_exists($path_year)){
				try{
					mkdir($path_year);
				}catch (Exception $ex ){
					
				}
			}
			
			$path_month = $path_year.DIRECTORY_SEPARATOR.$thang;
			if(!file_exists($path_month)){
				try{
					mkdir($path_month);
				}catch (Exception $ex ){
					
				}
			}

			$filename =  $path_month.DIRECTORY_SEPARATOR.$id_new;	
			$key  = Crypt_Key::createUserKey();
			$crypt = new Crypt_Rijndael($key);
			if($crypt->doEncryptDataUtf8ToFile($data,$header["content-transfer-encoding"],$filename)){
				$dbAdapter->update('email_parts',array("FILENAME"=>$filename),"ID_MP=$id_new");
			}else{
				//do nothing
			}
		}	
		return 1;
	}
	
	
	public function receiveContentWithoutPart($id_em,$data,$content_transfer_encoding,$content_type){
		
		$content_type = strtok($content_type,';');
		$name = strtok(';');
		$email_part = new Email_parts();
		//lay content_type
		$email_part->_content_type = $content_type; 
		//lay ten file
		$email_part->_name = $name;
		//co phai dinh kem hay khong
		$email_part->_is_attactment = 0;
		$email_part->_id_em = $id_em;
		$email_part->_is_inline = 1;
		//luu xuong file
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$user = Zend_Registry::get('auth')->getIdentity();
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//$dbAdapter->beginTransaction();
		
		$email_part->_filename ="";
		
		$id_new = $email_part->savetoDatabase(0);
		//echo $id_new;
		
		
		if($id_new > 0){
			$this->addNewFolderStorage($user->ID_U);
			
			$date = getdate();
			$nam = $date["year"];
			$thang = $date['mon'];
			$path_year = $config_mail->mail->default->mailclientstorage_inbox.DIRECTORY_SEPARATOR.$user->ID_U.DIRECTORY_SEPARATOR.$nam;
			
			if(!file_exists($path_year)){
				try{
					mkdir($path_year);
				}catch (Exception $ex ){
					
				}
			}
			
			$path_month = $path_year.DIRECTORY_SEPARATOR.$thang;
			if(!file_exists($path_month)){
				try{
					mkdir($path_month);
				}catch (Exception $ex ){
					
				}
			}
			$filename =  $path_month.DIRECTORY_SEPARATOR.$id_new;	
			
			$key  = Crypt_Key::createUserKey();
			$crypt = new Crypt_Rijndael($key);
			
			if($crypt->doEncryptDataUtf8ToFile($data,$content_transfer_encoding,$filename)){
				$dbAdapter->update('email_parts',array("FILENAME"=>$filename),"ID_MP=$id_new");
				return 1;
			}else{
				return 0;
			}
			
		}	
	}
	
	
	public function receiveMail($message){
		if(get_class($message) != 'Zend_Mail_Message')
			return 0;
	
	//var_dump($message); exit;
	//Lay message header
		$mess_header = $message->getHeaders();
	//lay du lieu cua user trong session
		$user = Zend_Registry::get('auth')->getIdentity();
	//Tao doi tuong email_message
		$email_message = new Email_messages();
	//Lay cac dia chi mail from,to,cc	
		$email_message->_id_u = $user->ID_U;
		try{
			$email_message->_addr_from = implode(";",Mail_Mime::parseEmailAddress($message->getHeader('from','string')));
		}
		catch(Exception $ex){}
	
		try{
			$addr_to = ereg_replace(",",";",$message->getHeader('to','string'));
			$email_message->_addr_to = implode(";",Mail_Mime::parseEmailAddress($addr_to));
		}
		catch(Exception $ex){}
		
		
		try{
			$addr_cc = ereg_replace(",",";",$message->getHeader('cc','string'));
			$email_message->_addr_cc = implode(";",Mail_Mime::parseEmailAddress($addr_cc));
		}
		catch(Exception $ex){}
		
	//Lay tieu de cua mail
		$key  = Crypt_Key::createUserKey();
		$crypt = new Crypt_Rijndael($key);
		$subject = $mess_header['subject'];
		$email_message->_subject = $crypt->doEncryptBlock(Mail_Mime::decode_mimeheader($subject));
		
		$email_message->_content_type = strtok($mess_header['content-type'],';');
		$email_message->_folder_code = 'Inbox';
		$email_message->_is_attactment = 0;
	
	//Kiem tra mail part co phai la attachment hay khong?
		foreach (new RecursiveIteratorIterator($message) as $part){
				$header = $part->getHeaders();
				$content_dis = strtok($header["content-disposition"],';');
				
				if( $content_dis == "attachment")
				{
					$email_message->_is_attactment = 1;
					break;
				}	
		}
	//dien cac thong tin mac dinh	
		$email_message->_is_mark = 0;
		$email_message->_is_read = 0;
		$time = strtotime($mess_header["date"]);
		$email_message->_time = date('Y-m-d H:i:s',$time);
	//Luu thong tin mail_message xuong database
		$id_new = $email_message->savetoDatabase(0);
		
		if( $id_new>0){ //Neu luu thanh cong
			$id_temp = $id_new;
			$is_html = 0;
	//kiem tra thu mail message co danh html khong
			foreach (new RecursiveIteratorIterator($message) as $part){
				$header = $part->getHeaders();
				$content_type = strtok($header["content-type"],';');
				
				if($content_type == "text/html")
				{
					$is_html = 1;
					
				}
				
			}
		//Lay tung part cua message 	
			foreach (new RecursiveIteratorIterator($message) as $part){
				$header = $part->getHeaders();
				$content_dis = "";
				if($header["content-disposition"])
					$content_dis = strtok($header["content-disposition"],';');
				$content_type = strtok($header["content-type"],';');
				if( $content_dis =="attachment"){
					$this->receivePartMail($part,$id_temp);
				}else{	
					if(!($content_type == "text/plain" && $is_html == 1))
						$this->receivePartMail($part,$id_temp);
				}		 
				
			}
		//Lay message khong co part
			if($message->countParts() == 0){
				
				$content_transfer_encoding = $mess_header["content-transfer-encoding"];
				$content_type = $mess_header["content-type"];
				
				$data =  $message->getContent();
				$this->receiveContentWithoutPart($id_new,$data,$content_transfer_encoding,$content_type);
			}

		}
		
		return 1; 
	}
	
	

	public function receiveAllNewMail(){
		
		//lay tat ca cac mail cua nguoi dung
		//check quota
		if(Mail_Quota::checkQuotaUser() == 0){
			return -1; // da het quota
		}
		$is_ssl = $this->email_account->_ssl_in;
		$ssl_config = '';
		if(!$is_ssl || $is_ssl=="")
				$ssl_config = '';
		if($is_ssl == 1)
				$ssl_config = "ssl";
		if($is_ssl == 2)
				$ssl_config = "tls";
		
		//var_dump($this->email_account);
		//echo Mail_Auth::decryptPassword($this->email_account->_password);
		//exit;
		try{
		$storage_mail = MailProtocolEngineFactory::createStorageIncoming(
														$this->email_account->_incoming_protocol,
														"$ssl_config" ,
														$this->email_account->_incoming_hostname,
														$this->email_account->_incoming_port,
														$this->email_account->_username,
														Mail_Auth::decryptPassword($this->email_account->_password));
		}catch(Exception $ex){
			return -2; // Khong the cap nhat den server nhan mail
		}
		//doc tat ca cac mail hien co tren mail server
		//$arr_part= Email_messages::getPartsMessageById(62);
		//var_dump($arr_part);
		$i = 1;
		foreach ($storage_mail as $message) {
    		//var_dump($message); exit;
			
			//var_dump($storage_mail);exit;
			//echo getUniqueId
			//echo $storage_mail->getUniqueId($i); //removeMessage($i);
    		//var_dump($storage_mail->getCapabilities());
			//echo $id_em;
			//var_dump($message->getFlags());
    		//if ($message->hasFlag(Zend_Mail_Storage::FLAG_SEEN)) {
				//continue;
			//}

			//if ($message->hasFlag(Zend_Mail_Storage::FLAG_RECENT)){
				//echo 1;
			try{	
				$id_em = $this->receiveMail($message);
				$storage_mail->removeMessage($i);
			}catch(Exception $ex){
		
			}//}

			$i++;
			//if($i == 10){
			//	return ;
			//}
			//exit;
		}
		//echo $storage_mail->countMessages();
		//exit;
		return 1;
		

	}
	private function addNewFolderStorage($id_u){
		if(is_null($id_u))
			return 0;
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$dir_storage = $config_mail->mail->default->mailclientstorage_inbox.DIRECTORY_SEPARATOR.$id_u;
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
	
	
	
}
?>