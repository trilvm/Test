<?php 
require_once('Zend/Controller/Action.php');
require_once('qtht/models/UsersModel.php');
require_once('mailclient/models/MailClientModel.php');
require_once('mailclient/models/EmailEngine/NetworkUtils.php');
require_once('mailclient/models/EmailEngine/EmailEngineIn.php');
require_once('mailclient/models/EmailEngine/EmailEngineOut.php');

require_once('mailclient/models/Email_group.php');
require_once('mailclient/models/Email_address.php');
require_once('mailclient/models/Email_account.php');
require_once('hscv/models/filedinhkemModel.php');

class Mailclient_emailengineController extends  Zend_Controller_Action{

	function init(){
			
	}
		
	function configinfoAction(){
		$this->view->title = "Cấu hình mail";
		//$this->view->subtitle = "Thông tin cấu hình mail";
		QLVBDHButton::AddButton("Xóa","","DeleteButton","DeleteButtonClick();");
		QLVBDHButton::AddButton("Danh sách thư","","BackButton","BackButtonClick();");
		QLVBDHButton::EnableAddNew();
		
		$id_del_acs = $this->_request->getParam('del_config');
		if($id_del_acs){
			foreach ($id_del_acs as $id_del_ac)
				Email_account::deleteAccout($id_del_ac);
		}
		
		
		$id_u = Zend_Registry::get('auth')->getIdentity()->ID_U;
		$this->view->data = MailClientModel::getEmailAccountByUser($id_u);
		//var_dump($this->view->data);
	}
	
	function inputconfigAction(){
		$this->view->title = "Nhập thông tin cấu hình";
		//$this->view->subtitle = "Nhập thông số cấu hình mail";
		
		QLVBDHButton::AddButton("Lưu","","SaveButton","saveButtonClick();");
		QLVBDHButton::EnableBack("");
		
		$params = $this->_request->getParams();
		$id_ea = $params['id_ea'];
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$this->view->email_account = new Email_account();
		
			$this->view->id_eac = $id_ea;
			if($id_ea >0){
				
				//truong hop cap nhat
				if(Email_account::isAccountOfUser($user->ID_U,$id_ea) == 1){
				$this->view->email_account = Email_account::getFromDatabase($id_ea);
				$this->view->email_account->_password = Mail_Auth::decryptPassword($this->view->email_account->_password);
				if(is_null($this->view->email_account)){
					$this->view->email_account = new Email_account();
				}
				}
			}else{
				$name_u = UsersModel::getEmloyeeNameByIdUser(Zend_Registry::get('auth')->getIdentity()->ID_U);
				
				$this->view->email_account->_name_info = $name_u;
				$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
				$this->view->email_account->_incoming_port = $config_mail->mail->default->incoming_port;
				$this->view->email_account->_outgoing_port = $config_mail->mail->default->outgoing_port;
				$this->view->email_account->_outgoing_hostname = $config_mail->mail->default->outgoing_server;
				$this->view->email_account->_incoming_hostname = $config_mail->mail->default->incoming_server;
				$this->view->email_account->_incoming_port = $config_mail->mail->default->incoming_port;
				$this->view->email_account->_outgoing_port = $config_mail->mail->default->outgoing_port;
				$this->view->email_account->_outgoing_protocol = $config_mail->mail->default->outgoing_protocol;
				$this->view->email_account->_incoming_protocol = $config_mail->mail->default->incoming_protocol;
				$this->view->email_account->_ssl_in = $config_mail->mail->default->ssl_in;
				$this->view->email_account->_ssl_out = $config_mail->mail->default->ssl_out;
				
			}
			
		
	}
	
	function saveconfigAction(){
		$params = $this->_request->getParams();
		//var_dump($params); exit;
		$id_eac = $params["ID_EAC"];
		//$params["PASSWORD"] = Mail_Auth::encryptPassword($params["PASSWORD"]);
		if(MailClientModel::saveconfig($id_eac,$params) != 0)
			$this->_redirect("/mailclient/emailengine/configinfo");
		else 
			$this->_redirect("/mailclient/emailengine/inputconfig/not_saved_true");
		
		exit;
	}
	
	function updateinboxMail(){
		
	}
	
	/**
	 * ham kiem tra mail_accounts
	 *
	 */
	function testconnectAction(){
		$params = $this->_request->getParams();
		
		//var_dump($params);
		
		switch($params["test_id"]){
			case 1: // test server nhan mail
				
				/*	$ip = gethostbyname($params["value"]);
					if(ip2long($ip) == -1 || ($ip == gethostbyaddr($ip) && preg_match("/.*\.[a-zA-Z]{2,3}$/",$params["value"]) == 0) ) {
						echo 0;
					}
					else {
					    try{	  
						$re = NetworkUtils::ping($ip,10000);
						if($re == -1)
							echo 0;
						else 
							echo 1;
						}catch(Exception $ex){
							echo 0;
							
						}
						
					}*/
				echo 1;
					
				exit;
			break;
			case 2: // test server gui mail
				/*$ip = gethostbyname($params["value"]);
					
					if(ip2long($ip) == -1 || $ip ==$params["value"]) {
					echo 0;
						}
					else {

						try{	  
						$re = NetworkUtils::ping($ip,10000);
						if($re == -1)
							echo 0;
						else 
							echo 1;
						}catch(Exception $ex){
							echo 0;
							
					}
						
					}*/
				echo 1;
					
				exit;
			break;
			case 3: // test dang nhap den server nhan mail
			
			//var_dump($params) ;exit;
			$emailenginein = new EmailEngineIn($params);
			try{
				$emailenginein->login();
				echo 1;
			}
			catch(Exception $ex){
				echo $ex->__toString();
			}
			exit;
			break;
			case 4: // test gui mail thu
			try{
				$emailengineout = new EmailEngineOut($params);
				$emailengineout->sendTestMail();
				echo 1;
			}catch(Exception $ex){
				echo $ex->__toString();
				echo 0;
			}
			
			exit;
			break;
			default:
				echo 0;
				exit;
				break;
		}
		
		exit;
	}
	
	function checkmailAction(){
		//$params = $this->_request->getParams();
		$this->_redirect("/mailclient/main/index/idfolder/8");
		//EmailEngineIn::checkMail();
		/*$user = Zend_Registry::get('auth')->getIdentity();
		$email_accounts = MailClientModel::getEmailAccountByUser($user->ID_U);
		foreach ($email_accounts as $arr_data) {
			$emailenginein = new EmailEngineIn($arr_data);
			try{
				$emailenginein->receiveAllNewMail();
				
			}catch(Exception $ex){
				//$this->_redirect("/mailclient/main/index/idfolder/8/");
			}
			
		}
		*/
		$this->_redirect("/mailclient/main/index/idfolder/8");
		exit;
	}
	
	function sendmailAction(){
		
		
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$user = Zend_Registry::get('auth')->getIdentity();
		
		$params  = $this->_request->getParams();
		//var_dump($params); exit;
		
		$addr_to = $params["ADDR_TO"];
		$addr_cc = $params["ADDR_CC"];
		$addr_bcc = $params["ADDR_BCC"];
		$subject = $params["SUBJECT"];
		$id_files = $params["idFile"];
		$is_draft = (int)$params["is_draft"];
		if($is_draft > 1) $is_draft =1;
		
		
		$params["ID_U"] = $user->ID_U;
		$params["FOLDER_CODE"] = "SENT";
		if($is_draft == 1)	$params["FOLDER_CODE"] = "draft";
		$params["IS_ATTACTMENT"] = 0;
		$params["IS_READ"] = 1;
		$params["IS_MARK"] = 1;
		$params["TIME"] = date('Y-m-d H:i:s');
		
		
		$emai_part_text =  "";


		$email_mgs = new Email_messages();
		$is_eac = $params["ID_EAC"];
		$noidung = $params['NOIDUNG'];
		$email_account = Email_account::getFromDatabase($is_eac);	
		$email_out = new EmailEngineOut(Email_account::objectToArray($email_account));
		try{
			if(Mail_Quota::checkQuotaUser() == 0){
				$this->_redirect("/mailclient/main/index/idfolder/8"); // da het quota
			}
			if($is_draft == 0)
				$re = $email_out->sendMail($subject,$noidung,$addr_to,$addr_cc,$addr_bcc,$id_files);
			
			
			if($re > 0 || $is_draft ==1){
			if($is_draft ==1)
				$params["FOLDER_CODE"] = "DRAFT";
			//echo $re."djkjdkjdk"; exit;
			//them moi email_messages outbox
			$key = Crypt_Key::createUserKey();
			$cript = new Crypt_Rijndael($key);
			$params["SUBJECT"] = $cript->doEncryptBlock($params["SUBJECT"]);
			$new_email_ms = Email_messages::arrayToObject($params);
			
			
			$id_new_em = $new_email_ms->saveToDatabase(0);
			
			//them moi cac email part
			
				
			$arr_email_sent_part = array(
				'NAME'=>'charset=UTF-8',
				'ID_EM'=>$id_new_em,
				'IS_ATTACTMENT'=>0,
				'CONTENT_TYPE'=>'text/html',
			);
			

			$email_parts = Email_parts::arrayToObject($arr_email_sent_part);
			$id_emp = $email_parts->saveToDatabase(0);
			
			//echo $noidung;
			//var_dump($pả);
			//echo $id_emp ;exit;;
			//$filename =  $config_mail->mail->default->mailclientstorage_sent.DIRECTORY_SEPARATOR.$user->ID_U.DIRECTORY_SEPARATOR.$id_emp;
			$email_parts->addNewFolderStorage($user->ID_U);
			$date = getdate();
			$nam = $date["year"];
			$path_year = $config_mail->mail->default->mailclientstorage_sent.DIRECTORY_SEPARATOR.$user->ID_U.DIRECTORY_SEPARATOR.$nam;
			if(!file_exists($path_year)){
				try{
					mkdir($path_year);
				}catch (Exception $ex ){
					
				}
			}
			$thang = $date['mon'];
			$path_month = $path_year.DIRECTORY_SEPARATOR.$thang;
			if(!file_exists($path_month)){
				try{
					mkdir($path_month);
				}catch (Exception $ex ){
					
				}
			}
			$key  = Crypt_Key::createUserKey();
			$crypt = new Crypt_Rijndael($key);
			if($id_emp >0){
				
			
				$filename =  $path_month.DIRECTORY_SEPARATOR.$id_emp;
				
				
			
				if($crypt->doEncryptDataUtf8ToFile($noidung,'none',$filename)){
				
				//if(file_put_contents($filename,$noidung)){
					$dbAdapter->update('email_parts',
					array("FILENAME"=>$filename),"ID_MP=$id_emp");
				
				}
			}
			
			//luu file dinh kem
			$year = QLVBDHCommon::getYear();
			$fileModel = new filedinhkemModel($year);
			
			$id_lc_attach = 0;
			foreach ($id_files as $id_file){
				$fileObject = $fileModel->getFileByMaso($id_file);
				$arr_email_sent_part = array(
					'NAME'=>$fileObject->_filename,
					'ID_EM'=>$id_new_em,
					'IS_ATTACTMENT'=>1,
					'CONTENT_TYPE'=>$fileObject->_mime,
					'FILENAME'=>$fileObject->_pathFile
				);
				$email_parts = Email_parts::arrayToObject($arr_email_sent_part);
				$id_emp = $email_parts->saveToDatabase(0);
				
				if($id_emp >0){
					$filename =  $path_month.DIRECTORY_SEPARATOR.$id_emp;
					$dbAdapter->update('email_parts',array("FILENAME"=>$filename),"ID_MP=$id_emp");
					$crypt->doEncryptDataUtf8ToFile(file_get_contents($fileObject->_pathFile),'none',$filename);
					//rename($fileObject->_pathFile,$filename);
					$fileModel->deleteFileByMaso($id_file);
				}
				$id_lc_attach = 1;
				//remane();
				
			}
			if($id_lc_attach == 1){
				$dbAdapter->update('email_messages',array("IS_ATTACTMENT"=>1),"ID_EM=$id_new_em");
			}

			$script = "
			<script>
			var o_divpro = window.parent.document.getElementById('id_progress');
			var o_is_succcend = window.parent.document.getElementById('is_succcend');
			o_divpro.style.display = '';
			is_draft = window.parent.document.frm.is_draft.value;
			if(is_draft == 0)
				o_is_succcend.innerHTML = 'Đang gởi mail ...';
			else
				o_is_succcend.innerHTML = 'Đang lưu nháp ... ';
			
			if(is_draft == 0){
								alert('Đã gửi mail thành công');
							}
							else
								alert('Đã lưu nháp');
							
							o_divpro.style.display = 'none';
							//window.parent.close();
							window.parent.CloseOnChildFrame();
			
			</script>
			";
			echo $script; exit;
			
			//luu file
			
			//echo 1;
			}else{
				$script = "
				<script>
				var o_divpro = window.parent.document.getElementById('id_progress');
				var o_is_succcend = window.parent.document.getElementById('is_succcend');
				o_divpro.style.display = '';
				o_is_succcend.innerHTML = 'Đang gởi mail';
				
				is_draft = window.parent.document.frm.is_draft.value;
				if(is_draft == 0)
									o_is_succcend.innerHTML = 'Không thể gởi mail được';
				else
									o_is_succcend.innerHTML = 'Lưu nháp không thành công';
				
				</script>
				";
				echo $script; exit;
				}

		}catch(Exception $ex){
			
			$script = "
			<script>
			var o_divpro = window.parent.document.getElementById('id_progress');
			var o_is_succcend = window.parent.document.getElementById('is_succcend');
			o_divpro.style.display = '';
			o_is_succcend.innerHTML = 'Đang gởi mail';
			
			is_draft = window.parent.document.frm.is_draft.value;
			if(is_draft == 0)
								o_is_succcend.innerHTML = 'Không thể gởi mail được';
			else
								o_is_succcend.innerHTML = 'Lưu nháp không thành công';
			
			</script>
			";
			echo $script; exit;
		}
		
		exit;
		
	}

	function getattachmentAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$id_part = $params['id_part'];
		$email_part = Email_parts::getFromDatabase($id_part);
		/* tao header*/
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$email_part->_content_type ); 
		header( "Content-Disposition: attachment; filename=".$email_part->_name ); 
		header( "Content-Description: mail attactment output" );
		/*Lay noi dung file*/
		echo $email_part->getContent(); 
		exit;
	}

	function getinlineAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$id_part = $params['id_part'];
		$email_part = Email_parts::getFromDatabase($id_part);
		/* tao header*/
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$email_part->_content_type ); 
		header( "Content-Disposition: inline; filename=".$email_part->_name ); 
		//header( "Content-Description: mail attactment output" );
		/*Lay noi dung file*/
		echo $email_part->getContent(); 
		exit;
	}
	
	function inputcontactAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		$this->view->id_u = $user->ID_U;
		$id_ead = (int)$params["id_ead"];
		$this->view->id_ead = $id_ead;
		$this->view->object = Email_address::getFromDatabase($id_ead);
		
		//$this->view->groups_old = Email_address::getGroupsByContact($id_ead);
		$arr_g = array();
		$groups = Email_group::getGroupsByIdUser($user->ID_U);
		$groups_old = Email_address::getGroupsByContact($id_ead);
		foreach($groups as $group){
			$ln = 0;
			foreach ($groups_old as $old){
				if($old["ID_EG"] == $group["ID_EG"])
					$ln = 1;
			}
			$it = array();
			$it["ID_EG"] =  $group["ID_EG"];
			$it["NAME"] =  $group["NAME"];
			$it["IS_OLD"] =  $ln;
			$arr_g[] = $it;
		}
		$this->view->groups_old = Email_address::getGroupsByContact($id_ead);
		$this->view->groups = $arr_g;
	}

	function savecontactAction(){
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		$params["ID_U_OWN"] =$user->ID_U;
		$email_addr = Email_address::arrayToObject($params);
		//echo $params["id_ead"]; exit;
		//var_dump($params); exit;
		$id_new = 0;
		if(($id_new = $email_addr->saveToDatabase($params["id_ead"])) > 0){
			//echo $id_new ; exit;
			Email_address::updateContactToGroups($id_new,$params["ID_EG"]);
			echo 1;
		}else
			echo 0;
		exit;
	}

	function inputgroupAction(){
		
	}

	function savegroupAction(){
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		//var_dump($user);
		//exit;
		$id_object = (int)$params["id_eg"];
		$email_group = new Email_group();
		$email_group->_name = $params[name_g];
		$email_group->_id_u = $user->ID_U;
		if($email_group->saveToDatabase($id_object) > 0)
			echo 1;
		else
			echo 0;
		exit;
	}

	function deletegroupAction(){
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		//echo $user->ID_U; exit;
		//echo (int)$params['id_eg'] ; exit;
		if((int)$params['id_eg'] == 0) { echo 0; exit; }
		echo Email_group::deleteGroup((int)$params['id_eg'],$user->ID_U);
		exit;
	}

	
	function findcontactAction(){
		
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		
		$id_eg = $params["ID_EG"];
		if(!id_eg)
			$id_eg = 0;	
		$txtsearch_contact = $params["txtsearch_contact"];
		if(!$txtsearch_contact) $txtsearch_contact = "";
		$this->view->id_u = $user->ID_U;
		$this->view->id_eg = $id_eg;
		$this->view->txtsearch_contact = $txtsearch_contact;
		
		$this->view->addr_to = $params["addr_to"];
		$this->view->addr_cc = $params["addr_cc"];
		$this->view->addr_bcc = $params["addr_bcc"];
		
		if($this->view->id_eg == -1)
		$this->view->addr_ing = Email_account::getAllEmailAccountInfoInSystem($txtsearch_contact);
		else if($this->view->id_eg > 0)
		$this->view->addr_ing = Email_address::getContactsById_group($this->view->id_eg,$txtsearch_contact,0);
		else if ($this->view->id_eg == 0){
			if($txtsearch_contact != ""){
				$addr_ing1 = Email_account::getAllEmailAccountInfoInSystem($txtsearch_contact);
				
				$addr_ing2 = Email_address::getContactsById_group($this->view->id_eg,$txtsearch_contact,-2);
				$this->view->addr_ing = array_merge($addr_ing1,$addr_ing2);
			}
		}
		
	}
	
	function deletecontactAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		echo Email_address::deleteFromDatabase($params["id_ead"],$user->ID_U);
		exit;
	}


	function emailmessageutilAction(){
		/*$params = $this->_request->getParams();
		$mailaction = $params["mailaction"];
		switch($mailaction){
			case : 
			case :

			case :
		}*/
	}
	
	function updatemainaccountAction(){
		$params = $this->_request->getParams();
		$id_eac = $params["id_eac"]; 
		$user = Zend_Registry::get('auth')->getIdentity();
		Email_account::updateAccountMain($id_eac,$user->ID_U);
		$this->_redirect("/mailclient/emailengine/configinfo/");
	}

}
