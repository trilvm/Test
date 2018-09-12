<?php


require_once 'Common/AjaxEngine.php';
class Hscv_TestLoadAjaxDivController  extends AjaxEngine  {

	function init(){
		$this->view->title = "Chức năng file đính kèm";
	}
	
	function indexAction(){
		
	}
	
	function loaddivAction(){
		//Goi ham tra ve ket qua doan html cho client
		$this->returnResultHTML($this);
	}
	
	protected function echoHTML(){
		//Lay cac tham so nhan ve
		//var_dump($this->_request->getParam('a'));
	}
}

?>
