<?php

/**
 * WorkflowController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ProcessModel.php';
require_once 'wf/models/ClassModel.php';
require_once 'wf/models/ActivityModel.php';
require_once 'wf/models/TransitionModel.php';
require_once 'wf/models/TransitionPoolModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_WorkflowController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		global $auth;
		$this->view->user = $auth->getIdentity();
		// TODO Auto-generated WorkflowController::indexAction() default action
		
		$this->view->parameter =  $this->getRequest()->getParams();
		$this->view->idp = $this->view->parameter["idp"];
				
		$this->process = new ProcessModel();
		$this->current = $this->process->find($this->view->idp)->current();
		if($this->current==null){
			$this->temp=$this->process->fetchAll(null,null,1,0);
			$this->current = $this->temp->current();
			$this->view->idp = $this->current->ID_P;
		}
		$this->class = new ClassModel();
		$this->transition = new TransitionModel();
		$this->activity = new ActivityModel();
		$this->transitionpool = new TransitionPoolModel();
		$this->activity->_id_p = $this->view->idp;
		$this->transition->_id_p = $this->view->idp;
		$this->transitionpool->_id_c = $this->current->ID_C;
		$this->view->idc = $this->current->ID_C;
		
		$this->view->process = $this->process->fetchAll(null,"NAME");
		$this->view->class = $this->class->fetchAll(null,"NAME");
		$this->view->activity = $this->activity->SelectAll(0,0,"");
		$this->view->transition = $this->transition->SelectAll(0,0,"");
		$this->view->transitionpool = $this->transitionpool->SelectAll(0,0,"");
		
		//QLVBDHButton::AddButton("Thêm mới quy trình","/wf/process/input","AddNewButton","");
		QLVBDHButton::AddButton("Xoá quy trình","","DeleteButton","DeleteprButtonClick()");
		QLVBDHButton::AddButton("Thêm mới trạng thái","","AddNewButton","AddaButtonClick()");
		QLVBDHButton::AddButton("Xoá trạng thái","","DeleteButton","DeleteaButtonClick()");
		QLVBDHButton::AddButton("Cập nhật SĐLC","","SaveButton","UpdatetrButtonClick()");
		QLVBDHButton::AddButton("Xoá SĐLC","","DeleteButton","DeletetrButtonClick()");

		$this->view->title = "Quy trình";
		//$this->view->subtitle = "";
	}
}
