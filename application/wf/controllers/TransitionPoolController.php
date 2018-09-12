<?php

/**
 * TransitionPoolController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/TransitionPoolModel.php';
require_once 'wf/models/ClassModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_TransitionPoolController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated TransitionPoolController::indexAction() default action
		
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
		$this->transitionpool = new TransitionPoolModel();
		$this->class = new ClassModel();
		
		//Khởi động các biến cho các model
		$this->transitionpool->_search = $search;
		$this->transitionpool->_id_c = $filter_object;
		
		//Lấy dữ liệu chính
		$rowcount = $this->transitionpool->Count();
		$this->view->data = $this->transitionpool->SelectAll(($page-1)*$limit,$limit,"NAME,ID_C");
		
		//Lấy dữ liệu phụ
		$this->view->class = $this->class->SelectAll(0,0,"");
		
		//Set biến cho view
		$this->view->title = "Danh sách quản lý hành động mẫu";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object = $filter_object;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/wf/TransitionPool/Delete");
		QLVBDHButton::EnableAddNew("/wf/TransitionPool/Input");
	}
	/**
	 * Hiển thị form input dữ liệu
	 */
	public function inputAction(){
		//Lấy parameter
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		$filter_object = $parameter["filter_object"];
		$id = $parameter["id"];
		
		//New các model
		$this->TransitionPool = new TransitionPoolModel();
		$this->class = new ClassModel();
		
		//Lấy dữ liệu
		$this->view->class = $this->class->fetchAll();
		if($id>0){
			$this->view->data = $this->TransitionPool->find($id)->current();
			$this->view->title = "Cập nhật hành động mẫu";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->view->title = "Thêm mới hành động mẫu";
			//$this->view->subtitle = "Thêm mới";
		}
		
		//Set biến cho view
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object = $filter_object;
	
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::EnableBack("/wf/TransitionPool");
		//QLVBDHButton::EnableHelp("");
	}
	/**
	 * Lưu dữ liệu.
	 * Nếu đã có thì update
	 * Nếu chưa có thì insert
	 */
	public function saveAction(){
		$this->transitionpool = new TransitionPoolModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		//Check data
		$this->checkInput($this->view->parameter["NAME"],$this->view->parameter["ALIAS"],$this->view->parameter["LINK"],$this->view->parameter["ID_C"]);
		
		if($this->view->parameter["ID_TP"]>0){
			$this->transitionpool->update(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"LINK"=>$this->view->parameter["LINK"],"ID_C"=>$this->view->parameter["ID_C"]),"ID_TP=".$this->view->parameter["ID_TP"]);
		}else{
			$this->transitionpool->insert(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"LINK"=>$this->view->parameter["LINK"],"ID_C"=>$this->view->parameter["ID_C"]));
		}
		$this->_redirect("/wf/TransitionPool");
	}
	/**
	 * Kiểm tra dữ liệu, nếu ok thì trả về true.
	 */
	public function checkInput($name,$alias,$link,$id_c){
		$strurl='/default/error/error?control=TransitionPool&mod=wf&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('maxlength=50',$name,"ERR11002003").",";
		$strerr .= ValidateInputData::validateInput('req',$name,"ERR11002004").",";
		$strerr .= ValidateInputData::validateInput('req',$alias,"ERR11002005").",";
		$strerr .= ValidateInputData::validateInput('maxlength=20',$alias,"ERR11002006").",";
		$strerr .= ValidateInputData::validateInput('alnum',$alias,"ERR11002007").",";
		$strerr .= ValidateInputData::validateInput('maxlength=128',$link,"ERR11002009").",";
		$strerr .= ValidateInputData::validateInput('req',$link,"ERR11002010").",";
		$strerr .= ValidateInputData::validateInput('req',$id_c,"ERR11002009").",";
		if(strlen($strerr)!=8){
			$this->_redirect($strurl.$strerr);
		}
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->transitionpool = new TransitionPoolModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->transitionpool->delete("ID_TP IN (".implode(",",$this->view->parameter["DEL"]).")");
		}catch(Exception $ex){
			
		}
		$this->_redirect("/wf/TransitionPool");
	}
}
