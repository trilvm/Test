<?php

/**
 * TransitionController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/TransitionModel.php';
require_once 'wf/models/ActivityModel.php';
require_once 'config/wf.php';
require_once 'Common/ValidateInputData.php';

class Wf_TransitionController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated TransitionController::indexAction() default action
	}
	/**
	 * Lưu dữ liệu.
	 * Nếu đã có thì update
	 * Nếu chưa có thì insert
	 */
	public function saveAction(){
		try{
			$this->parameter =  $this->getRequest()->getParams();
			$this->transition = new TransitionModel();
			
			for($i=0;$i<count($this->parameter["ID_T"]);$i++){
				if($this->parameter["HANXULY"][$i]=="")$this->parameter["HANXULY"][$i] = 2;
				if($this->parameter["ID_T"][$i]==0 && $this->parameter["ID_A_BEGIN"][$i]>-1 && $this->parameter["ID_A_END"][$i]>0 && $this->parameter["ID_TP"][$i]>0){
					if($this->parameter["ISFIRST"]==-1){
						$idnew = $this->transition->insert(array("ORDERS"=>$this->parameter["ORDERS"][$i],"ID_A_BEGIN"=>$this->parameter["ID_A_BEGIN"][$i],"ID_A_END"=>$this->parameter["ID_A_END"][$i],"ID_P"=>$this->parameter["idp"],"ID_TP"=>$this->parameter["ID_TP"][$i],"ISFIRST"=>"1","NAME"=>$this->parameter["NAME"][$i],"HANXULY"=>$this->parameter["HANXULY"][$i],"END_AT"=>$this->parameter["END_AT"][$i]));
					}else{
						$idnew = $this->transition->insert(array("ORDERS"=>$this->parameter["ORDERS"][$i],"ID_A_BEGIN"=>$this->parameter["ID_A_BEGIN"][$i],"ID_A_END"=>$this->parameter["ID_A_END"][$i],"ID_P"=>$this->parameter["idp"],"ID_TP"=>$this->parameter["ID_TP"][$i],"ISFIRST"=>"0","NAME"=>$this->parameter["NAME"][$i],"HANXULY"=>$this->parameter["HANXULY"][$i],"END_AT"=>$this->parameter["END_AT"][$i]));
					}
				}else{
					$isfirst = ($this->parameter["ISFIRST"]==$this->parameter["ID_T"][$i]?1:0);
					
					$this->transition->update(array("ORDERS"=>$this->parameter["ORDERS"][$i],"ID_A_BEGIN"=>$this->parameter["ID_A_BEGIN"][$i],"ID_A_END"=>$this->parameter["ID_A_END"][$i],"ID_TP"=>$this->parameter["ID_TP"][$i],"ISFIRST"=>$isfirst,"NAME"=>$this->parameter["NAME"][$i],"HANXULY"=>$this->parameter["HANXULY"][$i],"END_AT"=>$this->parameter["END_AT"][$i]),"ID_T=".$this->parameter["ID_T"][$i]);
				}
			}
			
			$this->transition->update(array("ISLAST"=>0),"ID_P=".(int)$this->parameter["idp"]);
			
			$islast = $this->parameter["ISLAST"];
			foreach($islast as $islastitem){
				if($islastitem==-1){
					$this->transition->update(array("ISLAST"=>1),"ID_T=".$idnew);
				}else{
					$this->transition->update(array("ISLAST"=>1),"ID_T=".(int)$islastitem);
				}
			}

			$this->transition->update(array("BEGINDEADLINE"=>0),"ID_P=".(int)$this->parameter["idp"]);
			
			$begindeadline = $this->parameter["BEGINDEADLINE"];
			foreach($begindeadline as $begindeadlineitem){
				if($begindeadlineitem==-1){
					$this->transition->update(array("BEGINDEADLINE"=>1),"ID_T=".$idnew);
				}else{
					$this->transition->update(array("BEGINDEADLINE"=>1),"ID_T=".(int)$begindeadlineitem);
				}
			}

			$this->transition->update(array("ENDDEADLINE"=>0),"ID_P=".(int)$this->parameter["idp"]);
			
			$enddeadline = $this->parameter["ENDDEADLINE"];
			foreach($enddeadline as $enddeadlineitem){
				if($enddeadlineitem==-1){
					$this->transition->update(array("ENDDEADLINE"=>1),"ID_T=".$idnew);
				}else{
					$this->transition->update(array("ENDDEADLINE"=>1),"ID_T=".(int)$enddeadlineitem);
				}
			}
			
			$multi = $this->parameter['MULTI'];
			$this->transition->update(array("MULTI"=>0),"ID_P=".(int)$this->parameter["idp"]);
			foreach($multi as $multiitem){
				if($multiitem>0){
					$this->transition->update(array("MULTI"=>1),"ID_T=".(int)$multiitem);
				}else{
					$this->transition->update(array("MULTI"=>1),"ID_T=".$idnew);
				}
			}

			$multi = $this->parameter['IS_VAOSO'];
			$this->transition->update(array("IS_VAOSO"=>0),"ID_P=".(int)$this->parameter["idp"]);
			foreach($multi as $multiitem){
				if($multiitem>0){
					$this->transition->update(array("IS_VAOSO"=>1),"ID_T=".(int)$multiitem);
				}else{
					$this->transition->update(array("IS_VAOSO"=>1),"ID_T=".$idnew);
				}
			}
		}catch(Exception $ex){
			echo $ex;exit;
		}
		TransitionModel::deletetrash();
		$this->_redirect("/wf/Workflow/index/idp/".$this->parameter["idp"]);
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->transition = new TransitionModel();
		$this->parameter =  $this->getRequest()->getParams();
		try{
			$this->transition->delete("ID_T IN (".implode(",",$this->parameter["DELT"]).")");
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		TransitionModel::deletetrash();
		$this->_redirect("/wf/Workflow/index/idp/".$this->parameter["idp"]);
	}

	public function indexnewAction(){
		$this->_helper->layout->disableLayout();
		$this->parameter =  $this->getRequest()->getParams();
		$activity = new ActivityModel();
		$transition = new TransitionModel();
		$activity->_id_p = $this->parameter["idp"];
		$transition->_id_p = $this->parameter["idp"];

		$this->view->activity = $activity->SelectAll();
		$this->view->transition = $transition->SelectAll();
		$this->view->id_p = $this->parameter["idp"];
		$this->view->id_c = $this->parameter["idc"];
	}

	public function updateposAction(){
		$this->parameter =  $this->getRequest()->getParams();
		ActivityModel::updatePosition($this->parameter["ida"],$this->parameter["x"],$this->parameter["y"]);
		exit;
	}
}
