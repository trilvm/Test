<?php

/**
 * CdController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'demo/models/DEMO_CDModel.php';

class Demo_cdController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated CdController::indexAction() default action
	}
	public function listAction(){
		global $auth;
		
		$this->parameter = $this->getRequest()->getParams();
		
		$this->view->type = $this->parameter["type"];
		$this->view->wf_id_a = $this->parameter["wf_id_a"];
		$this->view->wf_id_p = $this->parameter["wf_id_p"];
		
		$this->view->user = $auth->getIdentity();
		
		$this->cd = new DEMO_CDModel();
		$this->view->data  = $this->cd->SelectAllItem($this->view->wf_id_p,$this->view->wf_id_a,$this->view->user->ID_U);
				
		$createlink = WFEngine::GetCreateProcessButton($this->view->wf_id_p,$this->view->wf_id_a,$this->view->user->ID_U);
		if(count($createlink)>0){
			QLVBDHButton::EnableAddNew($createlink["LINK"]."/wf_id_t/".$createlink["ID_T"],QLVBDHButton::$TOP,$createlink["NAME"]);
		}
	}
	public function getrequestAction(){
		global $auth;
		
		$this->view->user = $auth->getIdentity();
		
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"]; 
	}
	public function burnAction(){
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"]; 
		$this->cd = new DEMO_CDModel();
		$this->view->data = $this->cd->find( $this->parameter["id"])->current();
	}
	public function sellAction(){
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"]; 
		$this->cd = new DEMO_CDModel();
		$this->view->data = $this->cd->find( $this->parameter["id"])->current();
	}
	public function burnokAction(){
		global $auth;
		
		$this->user = $auth->getIdentity();
		
		$this->cd = new DEMO_CDModel();
		$this->parameter = $this->getRequest()->getParams();
		$this->data = $this->cd->find( $this->parameter["id"])->current();
		
		$this->cd->getDefaultAdapter()->beginTransaction();
			
		//send next user
		if(WFEngine::SendNextUser($this->data->ID_PI,$this->parameter["wf_nexttransition"],$this->user->ID_U,$this->parameter["wf_nextuser"])){
			$this->cd->getDefaultAdapter()->commit();
		}else{
			$this->cd->getDefaultAdapter()->rollBack();
		}
		$this->_redirect("/demo/cd/list");
	}
	public function sellokAction(){
		global $auth;
		
		$this->user = $auth->getIdentity();
		
		$this->cd = new DEMO_CDModel();
		$this->parameter = $this->getRequest()->getParams();
		$this->data = $this->cd->find( $this->parameter["id"])->current();
		
		$this->cd->getDefaultAdapter()->beginTransaction();
			
		//send next user
		if(WFEngine::SendNextUser($this->data->ID_PI,$this->parameter["wf_nexttransition"],$this->user->ID_U,$this->parameter["wf_nextuser"])){
			$this->cd->getDefaultAdapter()->commit();
		}else{
			$this->cd->getDefaultAdapter()->rollBack();
		}
		$this->_redirect("/demo/cd/list");
	}
	public function saverequestAction(){
		global $auth;
		
		$this->user = $auth->getIdentity();
		
		$this->cd = new DEMO_CDModel();
		$this->parameter = $this->getRequest()->getParams();
		
		$this->cd->getDefaultAdapter()->beginTransaction();
			
		$idcd = $this->cd->insert(array("NAME"=>$this->parameter["NAME"]));
		
		//Create process
		$idpi = WFEngine::CreateProcess("QLCD",$idcd,$this->parameter["NAME"],$this->user->ID_U,$this->parameter["wf_nextuser"]);
		if($idpi==0){
			$this->cd->getDefaultAdapter()->rollBack();
			//$errorController = New ErrorController();
			//$errorController->
			//$errorController->dispatch('errorAction');
		}else{
			$this->cd->update(array("ID_PI"=>$idpi),"ID_DEMO=".$idcd);
			$this->cd->getDefaultAdapter()->commit();
		}
		$this->_redirect("/demo/cd/list");
	}
}
