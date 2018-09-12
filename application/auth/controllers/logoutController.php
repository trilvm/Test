<?php

require_once ('Zend/Controller/Action.php');

class Auth_logoutController extends Zend_Controller_Action {
	
	function indexAction()
	{
		//Xoa session hien tai
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::namespaceUnset('menu');
		//chuyen den trang login
		$this->_redirect('/auth/login');
	}
}

?>
