<?php
require_once ('Zend/Controller/Action.php');
abstract  class  AjaxEngine extends Zend_Controller_Action{
	
	/**
	 * Ham tra ve doan html cho client
	 */
	public function returnResultHTML($controller_action){
		 $controller_action->_helper->layout->disableLayout();
		 $this->echoHTML();
	}
	/**
	 * Ham lay cac tham so duoc post len tu client , va duoc decode 64
	 */
	public function getParam($name){
		return ($this->_request->getParam($name));	
	}
	/**
	 * Ham lay cac du lieu Json duoc truyen tu client
	 */
	public function getParamJson($senddata){
		return Zend_Json::decode(base64_decode($this->_request->getParam($senddata)));
	}
	
	/**
	 * Ham duoc dinh nghia tai cac lop con khi ke thua
	 */
	abstract protected function echoHTML() ;
	
	
}
?>