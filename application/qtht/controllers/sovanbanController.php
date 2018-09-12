<?php
require_once ('Zend/Controller/Action.php');
require_once ('qtht/models/qlsovanbanModel.php');
class Qtht_SovanbanController extends Zend_Controller_Action{
	function init(){
		$this->view->title = "Quản lý số văn bản ";
	}
	
	function indexAction(){
		QLVBDHButton::AddButton("Lưu","","SaveButton","luuButtonClick();");
		QLVBDHButton::AddButton("Trang chủ","","BackButton","trangchuButtonClick();");
		$this->view->dataVBden = qlsovanbanModel::getDataByTypevb(1);
		$this->view->dataVBdi = qlsovanbanModel::getDataByTypevb(2);
	}
	
	function inputAction(){
		QLVBDHButton::EnableSave("/qtht/DanhMucMaSo/save");
	}
	
	function saveAction(){
		$params = $this->_request->getParams();
		//var_dump($params);
		$des = $params["des"];
		$sel_vbtype = $params["sel_vbtype"];
		$sel_coltype = $params["sel_coltype"];
		qlsovanbanModel::themThongso($sel_vbtype,$sel_coltype,$des);
		$this->_redirect("/qtht/sovanban/index");
		exit;
	}
	
	function updatecolnameAction(){
		$params = $this->_request->getParams();
		//var_dump($params);
		$id = $params['choice_attr'];
                $id2 = $params['choice_attr2'];
		$type = $params['type'];
                $type2 = $params['type2'];
		//foreach($ids as $id){
			qlsovanbanModel::updateIs_selected($id,$type);
                        qlsovanbanModel::updateIs_selected($id2,$type2);
		//}
		
		$this->_redirect("/qtht/sovanban/index");
		
	}
}
?>
