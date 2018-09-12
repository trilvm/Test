<?php

/**
 * ActivityController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ActivityModel.php';
require_once 'wf/models/ProcessModel.php';
require_once 'wf/models/ActivityPoolModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_ActivityController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ActivityController::indexAction() default action

		$this->view->parameter =  $this->getRequest()->getParams();
		$this->activity = new ActivityModel();
		$this->activity->_id_p = $this->view->parameter["idp"];
		$this->view->page = $this->view->parameter["page"];
		if($this->view->page==0 || $this->view->page=="")$this->view->page=1;
		
		$rowcount = $this->activity->Count();
		$this->view->data = $this->activity->fetchAll("ID_P='".$this->activity->_id_p."'","NAME",10,($this->view->page-1)*10);
		$this->view->title = "Quản lý trạng thái";
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,10,"/wf/Process/index",$this->view->page) ;
		
		$this->process = new ProcessModel();
		$this->view->process = $this->process->fetchAll();
				
		$this->view->title = "Danh sách trạng thái";
		QLVBDHButton::EnableDelete("/wf/Activity/Delete");
		QLVBDHButton::EnableAddNew("/wf/Activity/Input");
	}
	/**
	 * Hiển thị form nhập liệu
	 */
	public function inputAction() {
		//Lấy parameter
		$parameter = $this->getRequest()->getParams();
		$id = $parameter["id"];
		$idp = $parameter["idp"];
		
		//New các model
		$this->activity = new ActivityModel();
		$this->process = new ProcessModel();
		$this->currentprocess = $this->process->find($idp)->current();
		$this->activitypool = new ActivityPoolModel();
		$this->activitypool->_id_c = $this->currentprocess->ID_C;
		$this->view->activitypool = $this->activitypool->SelectAll(0,0,"");
		if($id>0){
			$this->view->data = $this->activity->find($id)->current();
			$this->view->title = "Cập nhật trạng thái";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->view->title = "Thêm mới trạng thái";
			//$this->view->subtitle = "Thêm mới";
		}
		
		$this->view->idp = $idp;
		
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
		$this->activity = new ActivityModel();
		$this->view->parameter =  $this->getRequest()->getParams();

		//Check data
		$this->checkInput($this->view->parameter["NAME"],$this->view->parameter["ID_AP"]);
		try{
			if($this->view->parameter["ID_A"]>0){
				$this->activity->update(array("NAME"=>$this->view->parameter["NAME"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ID_AP"=>$this->view->parameter["ID_AP"]),"ID_A=".$this->view->parameter["ID_A"]);
			}else{
				$this->activity->insert(array("NAME"=>$this->view->parameter["NAME"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ID_AP"=>$this->view->parameter["ID_AP"],"ID_P"=>$this->view->parameter["idp"]));
			}
		}catch(Exception $ex){
			echo $ex->__toString();exit;
			$this->_redirect("/default/error/error?control=Activity&mod=wf&id=ERR11006001");
		}
		$this->_redirect("/wf/Workflow/index/idp/".$this->view->parameter["idp"]);
	}
	/**
	 * Kiểm tra dữ liệu, nếu ok thì trả về true.
	 */
	public function checkInput($name,$id_ap){
		$strurl='/default/error/error?control=Activity&mod=wf&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('maxlength=50',$name,"ERR11006003").",";
		$strerr .= ValidateInputData::validateInput('req',$name,"ERR11006004").",";
		$strerr .= ValidateInputData::validateInput('req',$id_ap,"ERR11006008").",";
		if(strlen($strerr)!=3){
			$this->_redirect($strurl.$strerr);
		}
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->activity = new ActivityModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->activity->delete("ID_A IN (".implode(",",$this->view->parameter["DELA"]).")");
		}catch(Exception $ex){
			$this->_redirect("/default/error/error?control=Activity&mod=wf&id=ERR11006001");
		}
		$this->_redirect("/wf/Workflow/index/idp/".$this->view->parameter["idp"]);
	}
}
