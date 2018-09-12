<?php
/**
 * ThuMucController
 * 
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/ThuMucModel.php';

class Hscv_ThuMucController extends Zend_Controller_Action {
	public function indexAction(){
		$this->view->title = "Quản lý thư mục lưu trữ";
		//$this->view->subtitle = "Danh sách";
		$thumuc = Array();
		QLVBDHCommon::GetTreeWithWhere(&$thumuc,"HSCV_THUMUC","ID_THUMUC","ID_THUMUC_CHA",1,1,"(ISCOQUAN=1 OR ID_THUMUC_CHA IS NULL)");
		$this->view->data = $thumuc;
		//Enable button
		QLVBDHButton::EnableDelete("/hscv/thumuc/Delete");
		QLVBDHButton::EnableAddNew("/hscv/thumuc/Input/ISCOQUAN/1");
	}
	public function indexhscvAction(){
		$this->view->title = "Quản lý thư mục HSCV";
		//$this->view->subtitle = "Danh sách";
		$thumuc = Array();
		QLVBDHCommon::GetTreeWithWhere(&$thumuc,"HSCV_THUMUC","ID_THUMUC","ID_THUMUC_CHA",1,1,"(ISCOQUAN=0 OR ID_THUMUC_CHA IS NULL)");
		$this->view->data = $thumuc;
		//Enable button
		QLVBDHButton::EnableDelete("/hscv/thumuc/Delete");
		QLVBDHButton::EnableAddNew("/hscv/thumuc/Input/ISCOQUAN/0");
	}
	public function inputAction(){
		
		//Lấy parameter
		$parameter = $this->getRequest()->getParams();
		$id = $parameter["id"];
		
		//New các model
		$this->thumuc = new ThuMucModel();
		$this->thumuc->_id = $id;
		if($parameter["ISCOQUAN"]==""){
			$this->view->ISCOQUAN=1;
		}else{
			$this->view->ISCOQUAN=0;
		}		
		//Lấy dữ liệu
		$thumuc = Array();
		QLVBDHCommon::GetTreeNoChildWithWhere(&$thumuc,"HSCV_THUMUC","ID_THUMUC","ID_THUMUC_CHA",1,1,$id,"(ISCOQUAN = ".$this->view->ISCOQUAN." OR ID_THUMUC_CHA IS NULL)");
		$this->view->thumuc = $thumuc;
		
		if($id>0){
			$this->view->data = $this->thumuc->find($id)->current();
			if($this->view->ISCOQUAN==1){
				$this->view->title = "Quản lý thư mục lưu trữ";
			}else{
				$this->view->title = "Quản lý thư mục HSCV";
			}
			//$this->view->subtitle = "Cập nhật";
		}else{
			if($this->view->ISCOQUAN==1){
				$this->view->title = "Quản lý thư mục lưu trữ";
			}else{
				$this->view->title = "Quản lý thư mục HSCV";
			}
			//$this->view->subtitle = "Thêm mới";
		}
		
		$this->view->group = $this->thumuc->GetAllGroup();
		$this->view->dep = $this->thumuc->GetAllDep();
		$this->view->user = $this->thumuc->GetAllUser();
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		//QLVBDHButton::EnableHelp("");


	}
	public function saveAction(){
		$this->thumuc = new ThuMucModel();
		$parameter =  $this->getRequest()->getParams();
		
		//Check data
		try{
			if($parameter["ID_THUMUC"]>0){
				$this->thumuc->_id = $parameter["ID_THUMUC"];
				$this->thumuc->update(array("NAME"=>$parameter["NAME"],"ID_THUMUC_CHA"=>$parameter["ID_THUMUC_CHA"]),"ID_THUMUC=".$parameter["ID_THUMUC"]);
			}else{
				$this->thumuc->_id = $this->thumuc->insert(array("NAME"=>$parameter["NAME"],"ID_THUMUC_CHA"=>$parameter["ID_THUMUC_CHA"],"ISCOQUAN"=>$parameter["ISCOQUAN"]));
			}
			$this->thumuc->UpdateGroup($parameter["SEL_GROUP_XEM"],$parameter["SEL_GROUP_THEMMOI"],$parameter["SEL_GROUP_PHANQUYEN"]);
			$this->thumuc->UpdateDep($parameter["SEL_DEP_XEM"],$parameter["SEL_DEP_THEMMOI"],$parameter["SEL_DEP_PHANQUYEN"]);
			$this->thumuc->UpdateUser($parameter["SEL_USER_XEM"],$parameter["SEL_USER_THEMMOI"],$parameter["SEL_USER_PHANQUYEN"]);
		}catch(Exception $ex){
			echo $ex->__toString();exit;
			$this->_redirect("/default/error/error?control=ThuMuc&mod=hscv&id=ERR11001001");
		}
		if($parameter["ISCOQUAN"]==1){
			$this->_redirect("/hscv/thumuc");
		}else{
			$this->_redirect("/hscv/thumuc/indexhscv");
		}
	}
	public function deleteAction(){
		$thumuc = new ThuMucModel();
		$param =  $this->getRequest()->getParams();
		$thumuc->delete("ID_THUMUC in (".implode(",",$param["DEL"]).")");
		$this->_redirect("/hscv/thumuc");
	}
}