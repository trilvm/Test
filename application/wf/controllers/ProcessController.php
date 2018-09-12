<?php

/**
 * ProcessController
 * 
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'wf/models/ProcessModel.php';
require_once 'wf/models/ClassModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'wf/models/TransitionModel.php';
require_once 'wf/models/ActivityModel.php';
require_once 'wf/models/ActivityAccessModel.php';
require_once 'qtht/models/DepartmentsModel.php';

class Wf_ProcessController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ProcessController::indexAction() default action
		$this->process = new ProcessModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		$this->view->page = $this->view->parameter["page"];
		if($this->view->page==0 || $this->view->page=="")$this->view->page=1;
		
		$rowcount = $this->process->Count();
		$this->view->data = $this->process->fetchAll(null,"NAME",10,($this->view->page-1)*10);
		$this->view->title = "Quản lý quy trình";
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,10,"/wf/Process/index",$this->view->page) ;
		
		QLVBDHButton::EnableDelete("/wf/Process/Delete");
		QLVBDHButton::EnableAddNew("/wf/Process/Input");
				
		Zend_Dojo::enableView($this->view);
	}
	/**
	 * Hiển thị form input dữ liệu
	 */
	public function inputAction() {
		$this->process = new ProcessModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		$this->view->isedit = false;
		$id = $this->view->parameter['id'];
		if($id>0){
			if($this->view->parameter['code']=="copy"){
				$this->process->getDefaultAdapter()->beginTransaction();
				try{
					//Lay process hien tai
					$currentprocess = $this->process->find($id)->current();
					
					//Lay activity hien tai
					$this->activity = new ActivityModel();
					$currentactivity = $this->activity->fetchAll("ID_P=".(int)$id);
					$this->activityaccess = new ActivityAccessModel();
					//Lay transition hien tai
					$this->transition = new TransitionModel();
					$currenttransition = $this->transition->fetchAll("ID_P=".(int)$id);
					
					//Copy process
					$idpnew = $this->process->insert(
						array("ID_C"=>$currentprocess->ID_C,
						"NAME"=>$currentprocess->NAME." - sao chép",
						"ALIAS"=>$currentprocess->ALIAS."-NEW",
						"ORDERS"=>$currentprocess->ORDERS)
					);
					//Copy activity
					$arractivity = array();
					foreach($currentactivity as $rowat){
						$arractivity[] = array();
						$arractivity[count($arractivity)-1][0] = $this->activity->insert(
							array(
								"NAME"=>$rowat->NAME,
								"ACTIVE"=>$rowat->ACTIVE,
								"ID_AP"=>$rowat->ID_AP,
								"ID_P"=>$idpnew,
								"POS_X"=>$rowat->POS_X,
								"POS_Y"=>$rowat->POS_Y,
							)
						);
						$arractivity[count($arractivity)-1][1] = $rowat->ID_A;
					}
					
					//Copy transition
					foreach($currenttransition as $rowtr){
						$idabeginnew = "";
						$idaendnew = "";
						foreach($arractivity as $oldactivity){
							if($rowtr->ID_A_BEGIN==$oldactivity[1]){
								$idabeginnew = $oldactivity[0];
							}
							if($rowtr->ID_A_END==$oldactivity[1]){
								$idaendnew = $oldactivity[0];
							}
						}
						$this->transition->insert(
							array(
								"ID_A_BEGIN"=>$idabeginnew,
								"ID_A_END"=>$idaendnew,
								"ID_TP"=>$rowtr->ID_TP,
								"ISFIRST"=>$rowtr->ISFIRST,
								"ORDERS"=>$rowtr->ORDERS,
								"ID_P"=>$idpnew,
								"NAME"=>$rowtr->NAME
							)
						);
						//$arractivity[count($arractivity)-1][1] = $rowat->ID_A;
					}
					//copy activity access
					foreach($arractivity as $oldactivity){
						$oldaccess = $this->activityaccess->fetchAll("ID_A=".$oldactivity[1]);
						foreach($oldaccess as $oldaccessitem){
							$this->activityaccess->insert(array(
							"ID_A"=>$oldactivity[0],
							"ID_U"=>$oldaccessitem->ID_U,
							"ID_G"=>$oldaccessitem->ID_G,
							"ID_DEP"=>$oldaccessitem->ID_DEP,
							));
						}
					}
					$this->process->getDefaultAdapter()->commit();
					$id=$idpnew;
				}catch(Exception $ex){
					echo $ex->__toString();
					$this->process->getDefaultAdapter()->rollBack();
				}
			}
			$this->view->isedit = true;
			$this->view->data = $this->process->find($id)->current();
			$this->loaihscv = new loaihosocongviecModel();
			$this->view->loaihscv = $this->loaihscv->fetchAll("MASOQUYTRINH='".$this->view->data->ALIAS."'")->current();
			$this->view->title = "Cập nhật quy trình";
			//$this->view->subtitle = "Cập nhật";
		}else{
			$this->view->title = "Thêm mới quy trình";
			//$this->view->subtitle = "Thêm mới";
		}
		
		$this->class = new ClassModel();
		$this->view->class = $this->class->fetchAll();
		
		QLVBDHButton::EnableSave("/wf/Process/Save");
		QLVBDHButton::AddButton("Trở lại","/wf/workflow/","BackButton","BackButtonClick()");
		//QLVBDHButton::EnableHelp("");
		
	}
	/**
	 * Lưu dữ liệu.
	 * Nếu đã có thì update
	 * Nếu chưa có thì insert
	 */
	public function saveAction(){
		$this->process = new ProcessModel();
		$this->loaihscv = new loaihosocongviecModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		if($this->view->parameter["ID_P"]>0){
			$this->process->update(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ID_C"=>$this->view->parameter["ID_C"],"ID_DEP"=>$this->view->parameter["ID_DEP"]),"ID_P=".$this->view->parameter["ID_P"]);
			$currentloaihscv = $this->loaihscv->fetchAll("MASOQUYTRINH='".$this->view->parameter["ALIAS"]."'");
			
			if($currentloaihscv->count()>0){
				$this->loaihscv->update(array("MASOQUYTRINH"=>$this->view->parameter["ALIAS"],"NAME"=>$this->view->parameter["NAME"]),"ID_LOAIHSCV=".$this->view->parameter["ID_LOAIHSCV"]);				
			}else{
				$this->loaihscv->insert(
					array("MASOQUYTRINH"=>$this->view->parameter["ALIAS"],
					"NAME"=>$this->view->parameter["NAME"])
				);
			}
		}else{
			$this->process->insert(array("NAME"=>$this->view->parameter["NAME"],"ALIAS"=>$this->view->parameter["ALIAS"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ID_C"=>$this->view->parameter["ID_C"],"ID_DEP"=>$this->view->parameter["ID_DEP"]));
			$this->loaihscv->insert(array("NAME"=>$this->view->parameter["NAME"],"MASOQUYTRINH"=>$this->view->parameter["ALIAS"],"ID_LOAIHSCV"=>$this->view->parameter["ID_LOAIHSCV"]));
		}
		$this->_redirect("/wf/Workflow");
	}
	/**
	 * Xoá dữ liệu
	 */
	public function deleteAction(){
		$this->process = new ProcessModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try{
			$this->process->delete("ID_P IN (".implode(",",$this->view->parameter["DELPR"]).")");
		}catch(Exception $ex){
			
		}
		$this->_redirect("/wf/Workflow");
	}
}
