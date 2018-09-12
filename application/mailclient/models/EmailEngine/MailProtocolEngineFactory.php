<?php
class MailProtocolEngineFactory{
	
	static function createIncomingProtocol($protocol_name,$host,$port,$is_ssl){
			
			if(!$is_ssl || $is_ssl=="")
				$ssl = false;
			if($is_ssl == 1)
				$ssl = "SSL";
			if($is_ssl == 2)
				$ssl = "TLS";

			//$tsl = false;
			//if($is_ssl)
			//$tsl = 'SSL';
			//echo $protocol_name;
			switch($protocol_name){
				case 'POP3':
					//echo "sss";
					return new Zend_Mail_Protocol_Pop3($host,$port,$ssl);
				case 'IMAP':
					return new Zend_Mail_Protocol_Imap($host,$port,$ssl);
				default:
					return null;
					break;
			}
	}
	
	static function createOutgoingTransport($protocol_name,$host,$port,$is_ssl){
		$config = array();
		if($is_ssl){
		$config = array(
		'ssl' => 'tls',
                'port' => $port); 
		}else{
			$config = array(
             
			'port' => $port); 
		}
		return new Zend_Mail_Transport_Smtp($host,$config);
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $protocol
	 * @param unknown_type $sll
	 * @param unknown_type $host
	 * @param unknown_type $port
	 * @param unknown_type $user
	 * @param unknown_type $password
	 * @return Zend_Mail_Storage_Pop3
	 */
	
	static function createStorageIncoming($protocol,$ssl,$host,$port,$user,$password){
		//POP3 - IMAP hoac co dung SSL hay khong
		
		$mail_storage = null;
		$ssl_str = "";
		
		$config = 	array(	'host'     => $host,
							'port'     => $port,
							'user'     => $user,
							'password' => $password);

		if(strtoupper($ssl) == 'SSL')
        	$config['ssl'] = 'SSL';
		if(strtoupper($ssl) == 'TLS')
        	$config['ssl'] = 'TLS';
		
		switch($protocol){
			case "POP3":
		$mail_storage = new Zend_Mail_Storage_Pop3($config);
     
        break;

			case "IMAP":
				$mail_storage = new Zend_Mail_Storage_Imap(array('host'     => $host,
                                         		'port'     => $port,
                     	                 		'user'     => $user,
                                         		'password' => $password));
		 if(strtoupper($ssl) == 'SSL')
        	$mail_storage['ssl'] = 'SSL';
		if(strtoupper($ssl) == 'TLS')
        	$mail_storage['ssl'] = 'TLS';
                break;       
		}
        return $mail_storage;

	}
	
}
?>