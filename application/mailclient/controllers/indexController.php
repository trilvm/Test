<?php 
require_once('Zend/Controller/Action.php');

class Mailclient_indexController extends  Zend_Controller_Action{
	
	function init(){
		
	}
	
	function indexAction(){
		$this->view->title = "Mail client";
		$this->view->subtitle = "Test connection ...! Oh yeah";
		QLVBDHButton::AddButton("Cập nhật mail","","","UpdateMailClick();");
	}
	
	function testconnectAction(){
		$params = $this->_request->getParams();
		$mail = new Zend_Mail_Storage_Pop3(array('host'     => $params['txt_host'],
                                         'user'     => $params['txt_user'],
                                         'password' => $params['txt_pass'],
                                        	
                                         ));

		echo $mail->countMessages() . " messages found\n";
		foreach ($mail as $message) {
   			 echo "Mail from '{$message->from}': {$message->subject}\n";
		}
		exit;
	}
	
	function configAction(){
		
	}
	
	/**
	 * Danh sach cac thu trong hop inbox
	 *
	 */
	function inboxlistAction(){
		//lay danh sach cac thu den
	}
}
?>