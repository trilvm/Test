<?php

/**
 * ActivityPoolController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ActivityPoolModel.php';
require_once 'wf/models/ClassModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_ActivityPoolController extends Zend_Controller_Action {
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
		$this->activitypool = new ActivityPoolModel();
		$this->class = new ClassModel();
		
		//Khởi động các biến cho các model
		$this->activitypool->_search = $search;
		$this->activitypool->_id_c = $filter_object;
		
		//Lấy dữ liệu chính
		$rowcount = $this->activitypool->Count();
		$this->view->data = $this->activitypool->SelectAll(($page-1)*$limit,$limit,"NAME,ID_C");
		
		//Lấy dữ liệu phụ
		$this->view->class = $this->class->SelectAll(0,0,"");
		
		//Set biến cho view
		$this->view->title = "Danh sách quản lý trạng thái mẫu";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object = $filter_object;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/wf/ActivityPool/Delete");
		QLVBDHButton::EnableAddNew("/wf/ActivityPool/Input");
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
		$this->activitypool = new ActivityPoolModel();
		$this->class = new ClassModel();
		
		//Lấy dữ liệu
		$this->view->class = $this->class->fetchAll();
		if($id>0){
			$this->view->data = $this->activitypool->find($id)->current();
			$this->view->title = " Cập nhật trạng thái mẫu";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->view->title = "Thêm mới trạng thái mẫu";
			//$this->view->subtitle = "Thêm mới";
		}
		
		//Set biến cho view
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object = $filter_object;
	
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
		$this->activitypool = new ActivityPoolModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		//Check data
		$this->checkInput($this->view->parameter["NAME"],$this->view->parameter["ALIAS"],$this->view->parameter["LINK"],$this->view->parameter["ID_C"]);
		try{
			if($this->view->parameter["IP_AP"]>0){
				$this->activitypool->update(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"LINK"=>$this->view->parameter["LINK"],"ID_C"=>$this->view->parameter["ID_C"]),"IP_AP=".$this->view->parameter["IP_AP"]);
			}else{
				$this->activitypool->insert(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"LINK"=>$this->view->parameter["LINK"],"ID_C"=>$this->view->parameter["ID_C"]));
			}
		}catch(Exception $ex){
			$this->_redirect("/default/error/error?control=ActivityPool&mod=wf&id=ERR11001001");
		}
		$this->_redirect("/wf/ActivityPool");
	}
	/**
	 * Kiểm tra dữ liệu, nếu ok thì trả về true.
	 */
	public function checkInput($name,$alias,$link,$id_c){
		$strurl='/default/error/error?control=ActivityPool&mod=wf&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('maxlength=50',$name,"ERR11001003").",";
		$strerr .= ValidateInputData::validateInput('req',$name,"ERR11001004").",";
		$strerr .= ValidateInputData::validateInput('req',$alias,"ERR11001005").",";
		$strerr .= ValidateInputData::validateInput('maxlength=20',$alias,"ERR11001006").",";
		$strerr .= ValidateInputData::validateInput('alnum',$alias,"ERR11001007").",";
		$strerr .= ValidateInputData::validateInput('maxlength=128',$link,"ERR11001009").",";
		$strerr .= ValidateInputData::validateInput('req',$link,"ERR11001010").",";
		$strerr .= ValidateInputData::validateInput('req',$id_c,"ERR11001008").",";
		if(strlen($strerr)!=8){
			$this->_redirect($strurl.$strerr);
		}
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->activitypool = new ActivityPoolModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->activitypool->delete("IP_AP IN (".implode(",",$this->view->parameter["DEL"]).")");
		}catch(Exception $ex){
			$this->_redirect("/default/error/error?control=ActivityPool&mod=wf&id=ERR11001001");
		}
		$this->_redirect("/wf/ActivityPool");
	}
}
