<?php 
require_once ('mailclient/models/EmailEngine/EmailEngineOut.php');
require_once('mailclient/models/EmailEngine/MailProtocolEngineFactory.php');
require_once 'hscv/models/filedinhkemModel.php';

class Vb_mailUtils {
	static function getInfoMailVbAccount(){
		//Doc tu file config
		$config_vbmail = new Zend_Config_Ini('../application/config.ini', 'vbmail');
		$config_general = new Zend_Config_Ini('../application/config.ini', 'general');
		$data = array();
		
		$data["USERNAME"] = $config_vbmail->vbmail->username;
		$data["PASSWORD"] = $config_vbmail->vbmail->password;
		$data["EMAIL_ADDR"] = $config_vbmail->vbmail->email;
		$data["INCOMING_PORT"] = $config_vbmail->vbmail->incomingport;
		$data["OUTGOING_PORT"] = $config_vbmail->vbmail->outgoingport;
		$data["INCOMING_HOSTNAME"] = $config_vbmail->vbmail->incominghost;
		$data["OUTGOING_HOSTNAME"] = $config_vbmail->vbmail->outgoinghost;
		$data["INCOMING_PROTOCOL"] = $config_vbmail->vbmail->incomingprotocol; 
		$data["OUTGOING_PROTOCOL"] = $config_vbmail->vbmail->outgoingprotocol;
		$data["NAME_INFO"] = $config_general->sys_info->company;
		$data["SSL_IN"] = $config_vbmail->vbmail->is_in_ssl;
		$data["SSL_OUT"] = $config_vbmail->vbmail->is_out_ssl;
		return $data;
	}

	static function sendMailVb($id_vb,$loaivb,$coquanchuyen,$emal_chuyen){
		//Lay thong tin ve dia chi mail cua he thong 
		$user = Zend_Registry::get('auth')->getIdentity();
		$arr_mailatt = Vb_mailUtils::getInfoMailVbAccount();
		$emailengineOut = new EmailEngineOut($arr_mailatt);
		$arr_data = Vanban_Xuatdulieu::exportInfoFromSystem($id_vb,$loaivb);
		$subject = Vanban_Xuatdulieu::exportToSubject($arr_data["MASOVANBAN"]);
		$body = Vanban_Xuatdulieu::exportToXML($arr_data);
		
		
		$addr_to = $emal_chuyen;
		$cq_nhan = $coquanchuyen;
		$masofiles = Vanban_Xuatdulieu::exportAttachFromSystem($id_vb,$loaivb);
		$arr_datadb = array();
		$arr_datadb["ID_VBDI"] = $id_vb;
		$arr_datadb["CQ_NHAN"] = $cq_nhan;
		$arr_datadb["EMAIL_NHAN"] = $addr_to;
		$arr_datadb["DA_CHUYEN"] = 1;
		$arr_datadb["ID_U"] = $user->ID_U;
		try{
			$emailengineOut->sendBase64NoEncrypPassMail($subject,$body,$addr_to,array(),array(),$masofiles);
			$arr_datadb["DA_CHUYEN"] = 1;
			Vanban_Xuatdulieu::insertIntotDatabase($arr_datadb);
	
		}catch(Exception $ex){
			//insertIntotDatabase()
			
			try{
			$arr_datadb["DA_CHUYEN"] = 0;
			Vanban_Xuatdulieu::insertIntotDatabase($arr_datadb);
			}catch (Exception $ex){
			}
		}
	}

	static function getVbFromMail(){
	
		$data_accmail = Vb_mailUtils::getInfoMailVbAccount();
		//var_dump($data_accmail); exit;
		
		$ssl_config = '';
		$is_ssl = $data_accmail["SSL_IN"];
		if(!$is_ssl || $is_ssl=="" || $is_ssl= 0)
				$ssl_config = '';
		if($is_ssl == 1)
				$ssl_config = "ssl";
		if($is_ssl == 2)
				$ssl_config = "tls";
		
		try{
		$storage_mail = MailProtocolEngineFactory::createStorageIncoming(
														$data_accmail["INCOMING_PROTOCOL"],
														"$ssl_config" ,
														$data_accmail["INCOMING_HOSTNAME"],
														$data_accmail["INCOMING_PORT"],
														$data_accmail["USERNAME"],
														$data_accmail["PASSWORD"]);
		}catch(Exception $ex){
			echo $ex->__toString();
			//return -2; // Khong the cap nhat den server nhan mail
		}
		
		//echo 1;
		
		//var_dump($storage_mail); exit;
		$i = 1;
		foreach ($storage_mail as $message) {
		try{	
				$is_vbmail =Vb_mailUtils::getVbMail($message);
				if($is_vbmail == 1)
					$storage_mail->removeMessage($i);
		}catch(Exception $ex){
		}
			$i++;
		
		}

	}

	/*static function getFileDkFromMail($message){
		if(get_class($message) != 'Zend_Mail_Message')
			return 0;
		$mess_header = $message->getHeaders();
		$user = Zend_Registry::get('auth')->getIdentity();
		foreach (new RecursiveIteratorIterator($message) as $part){
				$header = $part->getHeaders();
				$content_dis = strtok($header["content-disposition"],';');
				$name = strtok(';');
				if( $content_dis == "attachment")
				{
					//luu file dinh kem 
					//$header = $part->getHeaders();
					$name = QLVBDHCommon::get_string_between($name,'"','"');
					$data = $part->getContent();
					$encoding = $header["content-transfer-encoding"];
					$mime = $header["content-type"];
					echo filedinhkemModel::inserDataToFileDb($data,$idObject,0,12,$name,$mime,$encoding);
					//exit;

				}else{
					
				
				}
		}
	}*/
	
	static function getVbMail($message){
		if(get_class($message) != 'Zend_Mail_Message')
			return 0;
		$arr_data_vbnhan = array();
	//Lay message header
		$mess_header = $message->getHeaders();
		//var_dump($mess_header); exit;
	//lay du lieu cua user trong session
		$user = Zend_Registry::get('auth')->getIdentity();
	//Tao doi tuong email_message
		try{
			$_addr_from = Mail_Mime::explodeEmailAddress($message->getHeader('from','string'));
			
			
		}
		catch(Exception $ex){}
		$arr_data_vbnhan["EMAIL_GUI"] =  $_addr_from["address"];
		$subject = $mess_header['subject'];
		
		$arr_header = Vanban_Nhapdulieu::getHeaderMailFromXML(base64_decode($subject));
		if($arr_header["OK_CHECK"] == 0)
			return 0;
		$arr_data_vbnhan["CQ_GUI"] =  $arr_header["COMPANY_SENT"];
		//var_dump($arr_header); 
		$email_message->_content_type = strtok($mess_header['content-type'],';');
		//Kiem tra mail part co phai la attachment hay khong?
		
		$id_files = array();
		if($message->countParts() == 0)
		{
			$header = $message->getHeaders();
			$arr_vb_info = Vanban_Nhapdulieu::getInfoVbFromXML($message->getContent(),$header["content-transfer-encoding"]);
			$arr_data_vbnhan["SOKYHIEU"] =  $arr_vb_info["SOKYHIEU"];
			$arr_data_vbnhan["COQUANBANHANH"] =  $arr_vb_info["COQUANBANHANH"];
			$arr_data_vbnhan["NGAYBANHANH"] =  implode("-",array_reverse(explode("-",$arr_vb_info["NGAYBANHANH"])));;
			$arr_data_vbnhan["TRICHYEU"] =  $arr_vb_info["TRICHYEU"];
			$arr_data_vbnhan["NGUOIKY"] =  $arr_vb_info["NGUOIKY"];
			if($arr_vb_info["OK_CHECK"] == 0)
					return 0;

		}else{
		
			foreach (new RecursiveIteratorIterator($message) as $part){
					
					$header = $part->getHeaders();
					$content_dis = strtok($header["content-disposition"],';');
					$name = strtok(';');
					
					if( $content_dis == "attachment")
					{
						
						//luu file dinh kem 
					
						$name = QLVBDHCommon::get_string_between($name,'"','"');
						$data = $part->getContent();
						$encoding = $header["content-transfer-encoding"];
						$mime = $header["content-type"];
						$id_files[] = filedinhkemModel::inserDataToFileDb($data,$idObject,1,13,$name,$mime,$encoding);
					

					}else{
						
						
						$arr_vb_info = Vanban_Nhapdulieu::getInfoVbFromXML($part->getContent(),$header["content-transfer-encoding"]);
						$arr_data_vbnhan["SOKYHIEU"] =  $arr_vb_info["SOKYHIEU"];
						$arr_data_vbnhan["COQUANBANHANH"] =  $arr_vb_info["COQUANBANHANH"];
						$arr_data_vbnhan["NGAYBANHANH"] =  implode("-",array_reverse(explode("-",$arr_vb_info["NGAYBANHANH"])));;
						$arr_data_vbnhan["TRICHYEU"] =  $arr_vb_info["TRICHYEU"];
						$arr_data_vbnhan["NGUOIKY"] =  $arr_vb_info["NGUOIKY"];
						
						if($arr_vb_info["OK_CHECK"] == 0)
							return 0;
					}
			}
		}
		
		try{
			$id_new =  Vanban_Nhapdulieu::insertIntoDatabase($arr_data_vbnhan);
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			foreach($id_files as $id_file){
				$dbAdapter->update(QLVBDHCommon::Table('gen_filedinhkem'),array("ID_OBJECT"=>$id_new),"ID_DK=$id_file");
			}
		}catch(Exception $ex){
		
		}
		
		//exit;
		
		return 1; 
	}
}
