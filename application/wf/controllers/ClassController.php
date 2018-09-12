<?php

/**
 * ClassController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ClassModel.php';
require_once 'wf/models/TransitionPoolModel.php';
require_once 'wf/models/ActivityPoolModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_ClassController extends Zend_Controller_Action {
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
		
		//Tinh chỉnh parameter
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		
		//New các model
		$this->class = new ClassModel();
		
		//Khởi động các biến cho các model
		$this->class->_search = $search;
		
		//Lấy dữ liệu chính
		$rowcount = $this->class->Count();
		$this->view->data = $this->class->SelectAll(($page-1)*$limit,$limit,"NAME");
		
		//Set biến cho view
		$this->view->title = "Danh sách quản lý đối tượng";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/wf/Class/Delete");
		QLVBDHButton::EnableAddNew("/wf/Class/Input");
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
		$id = $parameter["id"];
		
		//New các model
		$this->class = new ClassModel();
		$this->activitypool = new ActivityPoolModel();
		$this->transitionpool = new TransitionPoolModel();
		
		//Lấy dữ liệu
		if($id>0){
			$this->view->data = $this->class->find($id)->current();
			$this->activitypool->_id_c = $this->view->data->ID_C;
			$this->transitionpool->_id_c = $this->view->data->ID_C;
			$this->view->title = "Cập nhật đối tượng";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->activitypool->_id_c = 0;
			$this->transitionpool->_id_c = 0;
			$this->view->title = "Thêm mới đối tượng";
			//$this->view->subtitle = "Thêm mới";
		}
		
		//Lấy dữ liệu phụ
		$this->view->activity = $this->activitypool->SelectCheckedByClass();
		$this->view->transition = $this->transitionpool->SelectCheckedByClass();
		
		//Set biến cho view
		$this->view->limit = $limit;
		$this->view->search = $search;
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
		//Tạo các đối tượng
		$this->Class = new ClassModel();
		$this->activitypool = new ActivityPoolModel();
		$this->transitionpool = new TransitionPoolModel();
		
		$this->view->parameter =  $this->getRequest()->getParams();
		$id_c = $this->view->parameter["ID_C"];
		$id_ap = $this->view->parameter["DELAP"];
		$id_tp = $this->view->parameter["DELTP"];
		
		//Check data
		$this->checkInput($this->view->parameter["NAME"],$this->view->parameter["ALIAS"]);
		try{
			$this->Class->getDefaultAdapter()->beginTransaction();
			if($this->view->parameter["ID_C"]>0){
				$this->Class->update(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"]),"ID_C=".$this->view->parameter["ID_C"]);
				$this->activitypool->_id_c = $id_c;
				$this->transitionpool->_id_c = $id_c;
			}else{
				$id_c = $this->Class->insert(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"]));
				$this->activitypool->_id_c = $id_c;
				$this->transitionpool->_id_c = $id_c;
			}
			/*Update Activity*/
			$this->activitypool->update(array("ID_C"=>null),"ID_C=".$id_c);
			for($i=0;$i<count($id_ap);$i++){
				$this->activitypool->update(array("ID_C"=>$id_c),"IP_AP=".$id_ap[$i]);
			}
			/*Update Transition*/
			$this->transitionpool->update(array("ID_C"=>null),"ID_C=".$id_c);
			for($i=0;$i<count($id_tp);$i++){
				$this->transitionpool->update(array("ID_C"=>$id_c),"ID_TP=".$id_tp[$i]);
			}
			$this->Class->getDefaultAdapter()->commit();
		}catch(Exception $ex){
			$this->Class->getDefaultAdapter()->rollBack();
			$this->_redirect('/default/error/error?control=Class&mod=wf&id=ERR11003001');
		}
		$this->_redirect("/wf/Class");
	}
	/**
	 * Kiểm tra dữ liệu, nếu ok thì trả về true.
	 */
	public function checkInput($name,$alias){
		$strurl='/default/error/error?control=TransitionPool&mod=wf&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('maxlength=50',$name,"ERR11002003").",";
		$strerr .= ValidateInputData::validateInput('req',$name,"ERR11002004").",";
		$strerr .= ValidateInputData::validateInput('req',$alias,"ERR11002005").",";
		$strerr .= ValidateInputData::validateInput('maxlength=20',$alias,"ERR11002006").",";
		$strerr .= ValidateInputData::validateInput('alnum',$alias,"ERR11002007").",";
		if(strlen($strerr)!=5){
			$this->_redirect($strurl.$strerr);
		}
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->Class = new ClassModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->Class->delete("ID_C IN (".implode(",",$this->view->parameter["DEL"]).")");
		}catch(Exception $ex){
			$this->_redirect('/default/error/error?control=Class&mod=wf&id=ERR11003001');
		}
		$this->_redirect("/wf/Class");
	}
}
