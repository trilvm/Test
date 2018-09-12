<?php

/**
 * ActivityAccessController
 * @deprecated 03/10/2009 by nguyennd
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ActivityAccessModel.php';
require_once 'wf/models/ActivityModel.php';
require_once 'wf/models/ProcessModel.php';
require_once 'config/wf.php';

class Wf_ActivityAccessController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ActivityAccessController::indexAction() default action
		$this->view->parameter =  $this->getRequest()->getParams();
		$this->view->ida = $this->view->parameter["ida"];
		$this->view->idp = $this->view->parameter["idp"];
		
		$this->activity = new ActivityModel();
		$this->currentactivity = $this->activity->find($this->view->ida)->current();
		
		$this->process = new ProcessModel();
		$this->currentprocess = $this->process->find($this->view->idp)->current();
		
		$this->view->title = "Quyền truy xuất trạng thái - [".$this->currentprocess->NAME."/".$this->currentactivity->NAME."]";
		$this->activityaccess = new ActivityAccessModel();
		$this->activityaccess->_id_a =$this->view->ida;  
		$this->view->group = $this->activityaccess->GetAllGroup();
		$this->view->user = $this->activityaccess->GetAllUser();
		$this->view->dep = $this->activityaccess->GetAllDep();
		QLVBDHButton::EnableSave("");
		QLVBDHButton::EnableBack("/wf/Workflow/index/idp/".$this->view->idp);
	}
	/**
	 * Lưu activityaccess
	 */
	public function saveAction(){
		try{
			$this->activityaccess = new ActivityAccessModel();
			$this->parameter =  $this->getRequest()->getParams();
			$this->ida = $this->parameter["ida"];
			$this->idp = $this->parameter["idp"];
			$this->activityaccess->delete("ID_A=".(int)$this->ida);
			for($i=0;$i<count($this->parameter["ID_G"]);$i++){
				$this->activityaccess->insert(array("ID_G"=>$this->parameter["ID_G"][$i],"ID_A"=>$this->ida));
			}
			for($i=0;$i<count($this->parameter["ID_U"]);$i++){
				$this->activityaccess->insert(array("ID_U"=>$this->parameter["ID_U"][$i],"ID_A"=>$this->ida));
			}
			for($i=0;$i<count($this->parameter["ID_DEP"]);$i++){
				$this->activityaccess->insert(array("ID_DEP"=>$this->parameter["ID_DEP"][$i],"ID_A"=>$this->ida));
			}
			$this->_redirect("/wf/Workflow/index/idp/".$this->idp);
		}catch(Exception $ex){
			$this->_redirect("/default/error/error/id/ERR11001001/mod/wf");
		}
	}
}
