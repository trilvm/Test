<?php

/**
 * ActivityPoolController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'motcua_ub/models/linhvucmotcuaModel.php';
require_once 'Common/ValidateInputData.php';

class Motcua_linhvucmotcuaController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ActivityPoolController::indexAction() default action
		
		//Lấy parameter
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		$filter_object = $parameter["filter_object"];
		
		//Tinh chỉnh parameter
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		if($filter_object==0 || $filter_object=="")$filter_object=0;
		
		//New các model
		$this->model = new linhvucmotcuaModel();
		
		//Khởi động các biến cho các model
		$this->model->_search = $search;
		
		//Lấy dữ liệu chính
		$rowcount = $this->model->Count();
		$this->view->data = $this->model->SelectAll(($page-1)*$limit,$limit,"NAME");
		
		//Set biến cho view
		$this->view->title = "Lĩnh vực một cửa";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
		$this->view->model = $this->model;

		//Enable button
		QLVBDHButton::EnableDelete("");
		QLVBDHButton::EnableAddNew("");
	}
	/**
	 * Hiển thị form input dữ liệu
	 */
	public function inputAction(){
		
		//Lấy parameter
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$id = $parameter["id"];
		
		//New các model
		$this->model = new linhvucmotcuaModel();
		
		//Lấy dữ liệu
		if($id>0){
			$this->view->data = $this->model->find($id)->current();
			$this->view->title = "cập nhật Lĩnh vực một cửa";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->view->title = "Thêm mới Lĩnh vực một cửa";
			//$this->view->subtitle = "Thêm mới";
		}
		
		$this->view->uaccess = $this->model->GetAllUser($id);

		//Set biến cho view
		$this->view->limit = $limit;
		$this->view->page = $page;
	
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		//QLVBDHButton::EnableHelp("");
	}
	/**
	 * Lưu dữ liệu.
	 * Nếu đã có thì update
	 * Nếu chưa có thì insert
	 */
	public function saveAction(){
		$this->model = new linhvucmotcuaModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		try{
			$id = $this->view->parameter["ID_LV_MC"];
			if($this->view->parameter["ID_LV_MC"]>0){
				$this->model->update(array("NAME"=>$this->view->parameter["NAME"],"ACTIVE"=>$this->view->parameter["ACTIVE"]),"ID_LV_MC=".$this->view->parameter["ID_LV_MC"]);
			}else{
				$id = $this->model->insert(array("NAME"=>$this->view->parameter["NAME"],"ACTIVE"=>$this->view->parameter["ACTIVE"]));
			}
			//xoa cac quyen tu id
			$this->model->getDefaultAdapter()->delete("MOTCUA_LINHVUC_QUYEN","ID_LV_MC='".$id."'");
			//Them moi lai quyen
			foreach($this->view->parameter["CHECK"] as $idu){
				$this->model->getDefaultAdapter()->insert("MOTCUA_LINHVUC_QUYEN",array("ID_U"=>$idu,"ID_LV_MC"=>$id		));
			}
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		$this->_redirect("/motcua/linhvucmotcua/");
	}

	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->activitypool = new linhvucmotcuaModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->activitypool->delete("ID_LV_MC IN (".implode(",",$this->view->parameter["DEL"]).")");
		}catch(Exception $ex){
			$this->_redirect("/default/error/error?control=ActivityPool&mod=wf&id=ERR11001001");
		}
		$this->_redirect("/motcua/linhvucmotcua/");
	}
}
