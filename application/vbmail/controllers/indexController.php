<?php
require_once 'vbmail/models/vbmail_vanbanchuyenModel.php';
require_once 'vbmail/models/vbmail_vanbannhanModel.php';
require_once('vbden/models/vbdenModel.php');
require_once ('vbmail/models/Vb_mailUtils.php');
require_once 'hscv/models/filedinhkemModel.php';

class Vbmail_indexController extends Zend_Controller_Action{
	
	function init(){
		
	}

	function indexAction(){
		$this->view->title = " Danh sách nhận văn bản";
		//$this->view->subtitle ="Danh sách";
		$this->view->vbnhans = vbmail_vanbannhanModel::getAllReceived();
		//Vb_mailUtils::getVbFromMail();
		QLVBDHButton::AddButton("Nhận văn bản","","receive_mail","Receive()");
	}
	
	function listAction(){
		
	}
	
	function sendvbAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		//var_dump($params); exit;
		
		$id_vb  = $params["ID_VBDI"];
		$loaivb = 2;
		$arr_result = array();
		
		if( is_array($params["COQUANCHUYEN"]) && count($params["COQUANCHUYEN"]) > 0){
			
			//$counter = 0;
			for($counter = 0 ; $counter < count($params["COQUANCHUYEN"]) ; $counter++){
				if($params["COQUANCHUYEN"][$counter] != "" && $params["EMAILCHUYEN"][$counter] != "")
					Vb_mailUtils::sendMailVb($id_vb,$loaivb,$params["COQUANCHUYEN"][$counter],$params["EMAILCHUYEN"][$counter]);		
				//$arr_result[] = 1;
			}
		}
		
		//var_dump($arr_result);
		exit;
	}

	function listreceiveAction(){
		$this->_redirect('/vbmail/index/index');
	}
	
	function listsentAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$this->view->idvbdi = $params["idvbdi"];
		$this->view->vbchuyens = vbmail_vanbanchuyenModel::getAllByIdVbdi($params["idvbdi"]);
	
	}

	function receivevbAction(){
		$this->_helper->Layout->disableLayout();
		Vb_mailUtils::getVbFromMail();
		//Vanban_Nhapdulieu::getInfoVbFromXML();
		//Vanban_Nhapdulieu::getHeaderMailFromXML();
		//Vb_mailUtils::getVbFromMail();
		$this->_redirect('/vbmail/index/index');
		exit;
	}
	
	function deletevbnhanAction(){
		$params = $this->_request->getParams();
		try{
			vbmail_vanbannhanModel::deleteVbNhan($params["ID_VBNHAN"]);
		}catch(Exception $ex){
			
		}
		$this->_redirect("/vbmail/index");
		exit;
	}

	function inputconfigAction(){
		$this->view->title = "Nhập thông số cấu hình mail văn bản gửi qua mail";
		//$this->view->subtitle = "Nhập thông số cấu hình mail";
		$config_vbmail = new Zend_Config_Ini('../application/config.ini', 'vbmail');
		QLVBDHButton::AddButton("Lưu","","SaveButton","saveButtonClick();");
		QLVBDHButton::EnableBack("");
		$email_account;
		
		$email_account->_email_addr = $config_vbmail->vbmail->email;
		$email_account->_username = $config_vbmail->vbmail->username;
		$email_account->_password = $config_vbmail->vbmail->password;
		$email_account->_incoming_hostname = $config_vbmail->vbmail->incominghost;
		$email_account->_outgoing_hostname = $config_vbmail->vbmail->outgoinghost;
		$email_account->_incoming_port = $config_vbmail->vbmail->incomingport;
		$email_account->_outgoing_port = $config_vbmail->vbmail->outgoingport;
		$email_account->_outgoing_protocol = $config_vbmail->vbmail->outgoingprotocol; 
		$email_account->_incoming_protocol = $config_vbmail->vbmail->incomingprotocol; 
		$email_account->_ssl_in = $config_vbmail->vbmail->is_in_ssl;
		$email_account->_ssl_out = $config_vbmail->vbmail->is_out_ssl;
		$this->view->email_account = $email_account;
			}
	
	function saveconfigAction(){
		$params = $this->_request->getParams();
		
		//$options = array( 'allowModifications' => true);
		//$config_vbmail = new Zend_Config_Ini('../application/config.ini', 'vbmail',$options);
		//$config_vbmail->vbmail-> = "12344";
		//$config_vbmail__set("vbmail.email","1234");
		//$config_vbmail->merge($config_vbmail);

		//$this->_redirect("/vbmail/index/inputconfig");
	}
}
