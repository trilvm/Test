<?php
require_once('mailclient/models/EmailEngine/EmailEngine.php');
require_once('mailclient/models/Email_account.php');
require_once('mailclient/models/EmailEngine/MailProtocolEngineFactory.php');
require_once('hscv/models/filedinhkemModel.php');
class EmailEngineOut implements EmailEngine {
	protected  $email_account ;
	
	public function __construct($array_attr){
		//$email_account = new Email_account();
		$this->email_account = Email_account::arrayToObject($array_attr);
		
	}
	
	
	public function login(){
		
	}
	
	public function logout(){
		
	}
	
	public function testConnect(){
		
	}
	
	
		
	
	public function sendTestMail(){
	
		//$subject = "Hệ thống kiểm tra tra địa chỉ mail : ".$this->email_account->_name_info."<".$this->email_account->_email_addr.">";
		
		//$this->sendMail($subject,$body,$addr_to,$arr_cc,$arr_bcc,$id_files);
		//$conf = Zend_Registry::get('config');
		$con = Zend_Registry::get('config');
		
		$config = array(
		'auth' => 'login',
		'name'=>$this->email_account->_outgoing_hostname,
		'port'=>$this->email_account->_outgoing_port,
		'username' =>$this->email_account->_username,
		'password' =>$this->email_account->_password
		);
		$ssl_config = "";
		$is_ssl = $this->email_account->_ssl_out;
		
		if(!$is_ssl || $is_ssl=="")
				$ssl_config = '';
		if($is_ssl == 1)
				$ssl_config = "ssl";
		if($is_ssl == 2)
				$ssl_config = "tls";
		
		if($ssl_config !="")
			$config['ssl'] = $ssl_config;
		
		//var_dump($this->email_account);
		$transport = new Zend_Mail_Transport_Smtp($this->email_account->_outgoing_hostname,$config);
		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyText("Kiểm tra gởi mail");
		$mail->setFrom($this->email_account->_email_addr, $this->email_account->_name_info);
		$mail->addTo($this->email_account->_email_addr);
		
		$mail->setSubject($subject);
		
		$mail->send($transport);
		
	}
	/**
	* @return -1: khong goi duoc mail, -2: du lieu dia chi email sai dinh dang , -3 : khong co subject
	**/
	public  function sendMail($subject,$body,$addr_to,$arr_cc,$arr_bcc,$id_files){
		
		if(!$subject || $subject=="")
			return -3;
		if(!$addr_to || $addr_to=="")
			return -2;
		
		$validator = new Zend_Validate_EmailAddress();
		//parse cac dia chi mail
		//$arr_addr_to  = explode(';',$addr_to);
		$arr_addr_cc  = explode(';',$arr_cc);
		$arr_addr_bcc = explode(';',$arr_bcc);
		
		
		$mail = new Zend_Mail('UTF-8');
		
		//set dia chi goi mail
		$mail->setFrom($this->email_account->_email_addr, $this->email_account->_name_info);
		//var_dump($this->email_account); exit;
		$arr_addrto = Mail_Mime::explodeEmailAddress($addr_to);
		
		foreach($arr_addrto as $it_addrto ){
			$mail->addTo($it_addrto["address"],$it_addrto["display"]);
			
		}

		$arr_addrcc = Mail_Mime::explodeEmailAddress($arr_cc);
		
		foreach($arr_addrcc as $it_addrcc ){
			$mail->addCc($it_addrcc["address"],$it_addrcc["display"]);
			
		}
		
		$arr_addrbcc = Mail_Mime::explodeEmailAddress($arr_bcc);
		
		foreach($arr_addrbcc as $it_addrbcc ){
			$mail->addBcc($it_addrbcc["address"],$it_addrbcc["display"]);
			
		}
		
		
		
		$mail->setSubject($subject);
		
		$mail->setBodyHtml($body);
		
		
		
		//dinh kem file
		
		$year = QLVBDHCommon::getYear();
		$fileModel = new filedinhkemModel($year);
		
		//var_dump($id_files); $body; exit;
		foreach ($id_files as $id_file){
			
			$fileObject = $fileModel->getFileByMaso($id_file);
			
			$at = $mail->createAttachment(file_get_contents($fileObject->_pathFile));
			//var_dump($fileObject); exit;
			$at->type        = $fileObject->_mime;
			//$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = $fileObject->_filename;
			//var_dump($fileObject); exit;
		}
		
		//bat dau goi mail
		$config = array(
		'auth' => 'login',
		'name'=>$this->email_account->_outgoing_hostname,
		'port'=>$this->email_account->_outgoing_port,
		'username' =>$this->email_account->_username,
		'password' =>Mail_Auth::decryptPassword($this->email_account->_password)
		);
		$ssl_config = "";
		$is_ssl = $this->email_account->_ssl_out;
		
		if(!$is_ssl || $is_ssl=="")
				$ssl_config = '';
		if($is_ssl == 1)
				$ssl_config = "ssl";
		if($is_ssl == 2)
				$ssl_config = "tls";
		
		if($ssl_config !="")
			$config['ssl'] = $ssl_config;
		try{
			$transport = new Zend_Mail_Transport_Smtp($this->email_account->_outgoing_hostname,$config);
			$mail->send($transport);
			return 1;
		}catch(Exception $ex){
			echo $ex->__toString(); exit;
			return -1;
		}
	}
	
	public  function sendBase64NoEncrypPassMail($subject,$body,$addr_to,$arr_cc,$arr_bcc,$id_files){
		
		if(!$subject || $subject=="")
			return -3;
		if(!$addr_to || $addr_to=="")
			return -2;
		
		$validator = new Zend_Validate_EmailAddress();
		//parse cac dia chi mail
		//$arr_addr_to  = explode(';',$addr_to);
		$arr_addr_cc  = explode(';',$arr_cc);
		$arr_addr_bcc = explode(';',$arr_bcc);
		
		
		$mail = new Zend_Mail('UTF-8');
		
		//set dia chi goi mail
		$mail->setFrom($this->email_account->_email_addr, $this->email_account->_name_info);
		//var_dump($this->email_account); exit;
		$arr_addrto = Mail_Mime::explodeEmailAddress($addr_to);
		
		foreach($arr_addrto as $it_addrto ){
			$mail->addTo($it_addrto["address"],$it_addrto["display"]);
			
		}

		$arr_addrcc = Mail_Mime::explodeEmailAddress($arr_cc);
		
		foreach($arr_addrcc as $it_addrcc ){
			$mail->addCc($it_addrcc["address"],$it_addrcc["display"]);
			
		}
		
		$arr_addrbcc = Mail_Mime::explodeEmailAddress($arr_bcc);
		
		foreach($arr_addrbcc as $it_addrbcc ){
			$mail->addBcc($it_addrbcc["address"],$it_addrbcc["display"]);
			
		}
		
		$mail->setSubject(base64_encode($subject));
		
		$mail->setBodyText(($body));
		
		//$mail->setSubject(($subject));
		
		//$mail->setBodyHtml(($body));
		
		
		//dinh kem file
		
		$year = QLVBDHCommon::getYear();
		$fileModel = new filedinhkemModel($year);
		
		//var_dump($id_files); $body; exit;
		foreach ($id_files as $id_file){
			
			$fileObject = $fileModel->getFileByMaso($id_file);
			
			$at = $mail->createAttachment(file_get_contents($fileObject->_pathFile));
			//var_dump($fileObject); exit;
			$at->type        = $fileObject->_mime;
			//$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = $fileObject->_filename;
			//var_dump($fileObject); exit;
		}
		
		//bat dau goi mail
		$config = array(
		'auth' => 'login',
		'name'=>$this->email_account->_outgoing_hostname,
		'port'=>$this->email_account->_outgoing_port,
		'username' =>$this->email_account->_username,
		'password' =>$this->email_account->_password
		);
		$ssl_config = "";
		$is_ssl = $this->email_account->_ssl_out;
		
		if(!$is_ssl || $is_ssl=="")
				$ssl_config = '';
		if($is_ssl == 1)
				$ssl_config = "ssl";
		if($is_ssl == 2)
				$ssl_config = "tls";
		
		if($ssl_config !="")
			$config['ssl'] = $ssl_config;
		try{
			$transport = new Zend_Mail_Transport_Smtp($this->email_account->_outgoing_hostname,$config);
			$mail->send($transport);
			return 1;
		}catch(Exception $ex){
			echo $ex->__toString(); exit;
			return -1;
		}
	} 


	function addNewFolderStorage($id_u){
		if(is_null($id_u))
			return 0;
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$dir_storage = $config_mail->mail->default->mailclientstorage_sent.DIRECTORY_SEPARATOR.$id_u;
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