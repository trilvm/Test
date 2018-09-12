<?php

/**
 * mainController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/GroupsModel.php';
require_once 'mailclient/models/mail.php';
//require_once 'mailclient/models/EmailEngine/EmailEngineIn.php';
require_once 'mailclient/models/Email_messages.php';
require_once 'mailclient/models/MailClientModel.php';
require_once('mailclient/models/Email_group.php');
require_once('mailclient/models/Email_address.php');
require_once('mailclient/models/Email_account.php');
require_once('mailclient/models/EmailEngine/EmailEngineIn.php');

class Mailclient_mainController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated mainController::indexAction() default action
		global $auth;
		EmailEngineIn::checkMail();
		$user = $auth->getIdentity();
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		
		$idfolder = (int)$parameter["idfolder"];
		//$overlow_quota = (int)$parameter["overlow_quota"];
		//$this->view-> 
		if($idfolder == 0)
			$idfolder = 8;
		$idem = $parameter["ID_EM"];
		/*Tạo đổi tượng*/
		$group = new GroupsModel();
		
		
		$this->view->folder = $folder;
		$this->view->idfolder = $idfolder;
	
		
		$this->view->groups = Email_group::getGroupsByIdUser($user->ID_U);
		
		/*create button*/
		QLVBDHButton::AddButton("Soạn mail","","compose_mail","Compose()");
		QLVBDHButton::AddButton("Nhận mail","","receive_mail","Receive()");
		QLVBDHButton::AddButton("Cấu hình","","config_mail","Config()");
		//QLVBDHButton::EnableHelp("");
		$this->view->title = "Thư điện tử";
		//$this->view->subtitle = "Danh sách";
		$this->view->is_mailmain = 1;
	}

	
	function folderlistAction(){
		$this->_helper->layout->disableLayout();
		$parameter = $this->getRequest()->getParams();
		$idfolder = (int)$parameter["idfolder"];
		if($idfolder == 0)
			$idfolder = 8;
		$folder = array();
		QLVBDHCommon::GetTree(&$folder,"EMAIL_FOLDER","ID_EF","ID_EF_PARENT",1,1);
		$this->view->folder = $folder;
		$this->view->idfolder = $idfolder ;
	}
	
	
	function contactlistAction(){
		global $auth;
		$user = $auth->getIdentity();
		$this->_helper->layout->disableLayout();
		$department = array();
		QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
		$group = new GroupsModel();
		$this->view->department = $department;
		//$this->view->group = $group->SelectAll(0,0,"");
		$this->view->groups = Email_group::getGroupsByIdUser($user->ID_U);
		$this->view->id_u = $user->ID_U;
	}

	function maillistAction(){
		$this->_helper->layout->disableLayout();
		global $auth;
		$user = $auth->getIdentity();
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		
		/*chuẩn bị dữ liệu*/
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;

		$sub = $parameter["sub"];
		$search = $parameter["search"];
		$idfolder = (int)$parameter["idfolder"];
		if($idfolder == 0)
			$idfolder = 8;
		$mailaction = $parameter["mailaction"];
		$idem = $parameter["ID_EM"];
	
		//var_dump();
		/*action*/
		switch($mailaction){
			//chua doc
			case 1:
				mail::Unread($idem,$user->ID_U);
				break;
			case 2:
				mail::Read($idem,$user->ID_U);
				break;
			case 3:
				mail::Mark($idem,$user->ID_U);
				break;
			case 4:
				mail::Unmark($idem,$user->ID_U);
				break;	
			case 5:
				mail::DeleteMessge($idem,$user->ID_U);
				break;	
			case 6:
				mail::SentToTrash($idem,$user->ID_U);
				break;
			
		}

		/*Tạo đổi tượng*/
		$group = new GroupsModel();
		
		/*Lấy dữ liệu*/
		$dataparam = array();
		$dataparam["SEARCH"] = $search;
		$dataparam["IDFOLDER"] = $idfolder;
		$this->view->count = mail::Count($dataparam,0,$limit,"");
		
		$this->view->data = mail::SelectAll($dataparam,($page-1)*$limit,$limit,"");
		//echo "<script>alert(".($page-1)*limit.")</script>"; 
			
		/*Tạo biến cho view*/
		//$department = array();
		//QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
		//$folder = array();
		//QLVBDHCommon::GetTree(&$folder,"EMAIL_FOLDER","ID_EF","ID_EF_PARENT",1,1);
		$this->view->department = $department;
		$this->view->group = $group->SelectAll(0,0,"");
		$this->view->showPage = QLVBDHCommon::Paginator($this->view->count,5,$limit,"frm",$page);
		$this->view->folder = $folder;
		$this->view->idfolder = $idfolder;
		$this->view->sub = $sub;
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		
		$this->view->groups = Email_group::getGroupsByIdUser($user->ID_U);
		
	}

	function viewAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$this->view->is_popup = (int)$params["is_popup"];
		$user = Zend_Registry::get("auth")->getIdentity();
		
		$id_em = $params["id"];
		$this->view->id_em = $id_em;
		
		if($id_em >0){
			try{
				
				
			}catch (Exception $ex){
				
			}
		}
		mail::Read(array($id_em),$user->ID_U);
		//$id_em = $params["id"];
		$this->view->arrparts = Email_messages::getPartsMessageById($id_em);
		$this->view->email_messages = Email_messages::getFromDatabase($id_em);
		$this->view->id = $id_em;
		
		/*var_dump($this->view->arrparts);
		foreach ($this->view->arrparts as $part){
			echo $part->getContent();
		}
		*/
		
		//var_dump($this->view->email_messages);
		//exit;
		
		
	}
	function composeAction(){
		//disable layout
		
		QLVBDHButton::AddButton("Gửi mail","","sent_mail","Compose()");
		QLVBDHButton::AddButton("Lưu nháp","","sent_draft","SentDraft()");
		//QLVBDHButton::EnableHelp("");
		$this->view->title = "soạn mail";
		//$this->view->subtitle = "soạn mail";
		$params = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		
		
/**
*Phan chung cho moi truong hop
*/
		//Lay cac tai khoan mail cua nguoi dung dang su dung
		$this->view->data_mailacc = MailClientModel::getEmailAccountByUser($user->ID_U);
		$this->view->ID_EAC = $this->view->data_mailacc[0]["ID_EAC"];
		//Lay danh sach cac dia chi email trong so dia chi va dia chi email trong co quan
		$addr_ing1 = Email_account::getAllEmailAccountInfoInSystem($txtsearch_contact);
		$addr_ing2 = Email_address::getContactsById_group($this->view->id_eg,$txtsearch_contact,-2);
		$addr_ing3 = Email_address::getContactsNoGroupByUser($user->ID_U);
		$this->view->addr_ing = array_merge($addr_ing1,$addr_ing2,$addr_ing3);
		
		//Lay cac tham so
		$is_reply = $params["IS_REPLY"];
		$is_popup = $params["IS_POPUP"];
		$is_sentspecontact = $params["emailselect"];
		$is_sentspegroup = $params["groupselect"];
		$is_draft= (int)$params["is_draft"];
		
		$this->view->emailselect = "";
		if($is_sentspecontact)
			$this->view->ADDR_TO = $params["emailselect"];
		//truong hop tra loi tu mail da nhan duoc
		else if($is_sentspegroup){
			$email_groups = Email_address::getContactsById_group($is_sentspegroup,$txtsearch_contact,0);
			foreach($email_groups as $em){
				if($this->view->ADDR_TO == "")
					$this->view->ADDR_TO .= '"'.$em["NAME_FRIEND"].'"<'.$em["EMAIL_ADDR_FR"].">";
				else
					$this->view->ADDR_TO .= ';"'.$em["NAME_FRIEND"].'"<'.$em["EMAIL_ADDR_FR"].">";
			}
		}
		else if($is_reply > 0){
			$mess_re = "";
			$id_em_re = $params["ID_MESSAGE_RE"];
			$email_messages_re = Email_messages::getFromDatabase($id_em_re);
			
			if($is_reply == 1)
				$str_to = $email_messages_re->_addr_from;
			if($is_reply == 2)
				$str_to = ($email_messages_re->_addr_from.";".$email_messages_re->_addr_to.";".$email_messages_re->_addr_cc);
			$arrparts_re = Email_messages::getPartsMessageById($id_em_re);
			foreach ($arrparts_re as $part){
				$content_t = strtok($part->_content_type,'/');
				if($content_t == "text" && $part->_is_attactment == 0)
					$mess_re .= $part->getContent();
			}
			
			$str_inline = "";
			foreach ($arrparts_re as $part){
				$content_t = strtok($part->_content_type,'/');
				if($content_t == "image" && $part->_is_inline == 1)
					$str_inline .= "<img src='/mailclient/emailengine/getinline?id_part=$part->_id'> </img>";
			
			}
			
			$this->view->SUBJECT = "Trả lời: ".$email_messages_re->_subject;

			if($this->view->ADDR_TO == ""){
				
				$this->view->ADDR_TO =  $str_to;
			}else{
				$this->view->ADDR_TO += ";" + $str_to;
			}
			if($mess_re != ""){
				$title = nl2br(htmlspecialchars("\n\n\n\n\n"."------------------------"."Ngày ".htmlspecialchars(date("d/m/Y  H:i",strtotime($email_messages_re->_time))). " " . $email_messages_re->_addr_from. "  viết:" ) );
				$this->view->NOIDUNG = $title.$mess_re.$str_inline;
			}
			
		}
		else if($is_popup == 1){ // truong hop popup len trang moi
			$this->view->ID_EAC = $params["ID_EAC"];
			$this->view->ADDR_TO = $params["ADDR_TO"];
			$this->view->ADDR_CC = $params["ADDR_CC"];
			$this->view->ADDR_BCC = $params["ADDR_BCC"];
			$this->view->SUBJECT = $params["SUBJECT"];
			$this->view->NOIDUNG = $params["NOIDUNG"];
				//$this->data_mailacc[0]["ID_EAC"]
		}
		else if($is_draft == 1){
			$id_em_re = $params["ID_MESSAGE_DRAFT"];
			$email_messages_re = Email_messages::getFromDatabase($id_em_re);
			$str_to = $email_messages_re->_addr_to;
			$str_cc = $email_messages_re->_addr_cc;
			$str_bcc = $email_messages_re->_addr_bcc;
			$arrparts_re = Email_messages::getPartsMessageById($id_em_re);
			foreach ($arrparts_re as $part){
				$content_t = strtok($part->_content_type,'/');
				if($content_t == "text" && $part->_is_attactment == 0)
					$mess_re .= $part->getContent();
			}
			
			$str_inline = "";
			foreach ($arrparts_re as $part){
				$content_t = strtok($part->_content_type,'/');
				if($content_t == "image" && $part->_is_inline == 1)
					$str_inline .= "<img src='/mailclient/emailengine/getinline?id_part=$part->_id'> </img>";
			
			}
			
			$this->view->SUBJECT = $email_messages_re->_subject;

			if($this->view->ADDR_TO == ""){
				
				$this->view->ADDR_TO =  $str_to;
			}else{
				$this->view->ADDR_TO += ";" + $str_to;
			}
			
			$this->view->ADDR_CC = $str_cc;
			$this->view->ADDR_BCC = $str_bcc;
			if($mess_re != ""){
				
				$this->view->NOIDUNG = $title.$mess_re.$str_inline;
			}
		}

		$this->view->is_popup = (int)$is_popup;
		
		$this->_helper->layout->disableLayout();
		
		
	}

	function viewcontentmsgAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$id_em = $params["id"];
		$this->view->arrparts = Email_messages::getPartsMessageById($id_em);
	}
	
}
?>

