<?php
require_once 'qtht/models/Vb_nguoikyModel.php';
class Qtht_DanhmucnguoikyController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Danh mục người ký";
	}
	
	function indexAction(){
		//$this->view->subtitle="Danh sách";	
		QLVBDHButton::EnableAddNew("");
		QLVBDHButton::EnableDelete("");
		$this->view->data = Vb_nguoikyModel::getData();
	}
	
	function inputAction(){
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		$params = $this->_request->getParams();
		$id = $params["id"];
		$id_u = $params["id_u"];
		$this->view->id_u = $id_u;
		$this->view->title="Thêm mới người ký";	
	}
	
	function saveAction(){
		
		if($this->_request->isPost()){
			$params = $this->_request->getParams();
			$id_u = $params["ID_U"];
			
			$id = $params['ID'];
			if(Vb_nguoikyModel::insert($id_u))
			{
				//luu thanh cong
				$this->_redirect("/qtht/danhmucnguoiky/");
			}else{
				$this->_redirect("/qtht/danhmucnguoiky/input");
			}
			
		}
		exit;
	}
	
	function deleteAction(){
		$params = $this->_request->getParams();
		$id_nks = $params["DEL"];
		foreach ($id_nks as $id_nk){
			Vb_nguoikyModel::delete($id_nk);
		}
		$this->_redirect('/qtht/Danhmucnguoiky/');
		exit;
	}
}
?>
