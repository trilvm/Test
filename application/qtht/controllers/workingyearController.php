<?php

/**
 * workingyearController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/workingyearModel.php';

class Qtht_workingyearController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated workingyearController::indexAction() default action
		$this->view->title="Năm làm việc";
		//$this->view->subtitle="Danh sách";
		$workingyear = new workingyearModel();
		$this->view->workingyear = $workingyear->fetchAll();
		QLVBDHButton::EnableAddNew("/qtht/workingyear/Input"); 
	}
	public function inputAction(){
		$param = $this->getRequest()->getParams();
		$this->view->title="Năm làm việc";
		$workingyear = new workingyearModel();
		if(isset($param['id'])){
			$this->view->subtitle="Cập nhật năm làm việc";
			//$this->view->subtitle="Cập nhật";
			$this->view->data = $workingyear->find($param['id'])->current();
		}else{
			$this->view->subtitle="Thêm mới năm làm việc";
			//$this->view->subtitle="Thêm mới";
		}
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		//QLVBDHButton::EnableHelp("");
	}
	public function saveAction(){
		$workingyear = new workingyearModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		try{
			if($this->view->parameter["ID_YEAR"]>0){
				$workingyear->update(array("YEAR"=>$this->view->parameter["YEAR"]),"ID_YEAR=".$this->view->parameter["ID_YEAR"]);
			}else{
				$workingyear->insert(array("YEAR"=>$this->view->parameter["YEAR"]));
			}
		}catch(Exception $ex){
			$this->_redirect("/default/error/error?control=workingyear&mod=qtht&id=ERR11001001");
		}
		$this->_redirect("/qtht/workingyear");
	}
}
