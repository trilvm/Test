<?php

/**
 * phanquyenhscvController
 * @deprecated 15/10/2009 by hieuvt
 * @author hieuvt
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/phanquyenhscvModel.php';
require_once 'hscv/models/phanquyenthumucModel.php';
require_once 'config/hscv.php';

class hscv_phanquyenhscvController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated phanquyenhscvController::indexAction() default action
		$this->view->parameter =  $this->getRequest()->getParams();
		$this->view->id_hscv = $this->view->parameter["id"];
		
		// Set Year
        $year = $parameter["year"];
        Zend_Registry::set('year',$year);
        
		// Set Year
        $year = $parameter["year"];
        Zend_Registry::set('year',$year);
        
        $this->hscv = new hosocongviecModel();
		$this->hscvPQ = $this->hscv->find($this->view->id_hscv)->current();
		
		$this->view->title = "Quyền truy xuất hồ sơ công việc - [".$this->hscvPQ->NAME."]";
		$this->phanquyenhscv = new phanquyenhscvModel(2009);
		$this->phanquyenhscv->_id_hscv =$this->view->id_hscv;  
		
		$this->view->Groups = $this->phanquyenhscv->GetAllGroups();
		$this->view->Users = $this->phanquyenhscv->GetAllUsers();
		$this->view->Departments = $this->phanquyenhscv->GetAllDepartments();
				
		QLVBDHButton::EnableSave("");
		QLVBDHButton::EnableBack("/hscv/ThuMuc");
	}
	/**
	 * Lưu phanquyenhscv
	 */
	public function saveAction(){
		try{
			$this->parameter =  $this->getRequest()->getParams();
			
			// Set Year
            // $year = $this->parameter["year"];
            // Zend_Registry::set('year',$year);
            
            $this->phanquyenhscv = new phanquyenhscvModel(2009);			
			$this->id_hscv = $this->parameter["id"];
        
			$this->phanquyenhscv->delete("ID_HSCV=".(int)$this->id_hscv);
			for($i=0;$i<count($this->parameter["ID_G_TRUYXUAT"]);$i++){
				$this->phanquyenhscv->insert(array("ID_G"=>$this->parameter["ID_G_TRUYXUAT"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'truyxuat'));
			}
			for($i=0;$i<count($this->parameter["ID_U_TRUYXUAT"]);$i++){
				$this->phanquyenhscv->insert(array("ID_U"=>$this->parameter["ID_U_TRUYXUAT"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'truyxuat'));
			}
		    for($i=0;$i<count($this->parameter["ID_DEP_TRUYXUAT"]);$i++){
				$this->phanquyenhscv->insert(array("ID_DEP"=>$this->parameter["ID_DEP_TRUYXUAT"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'truyxuat'));
			}
		    for($i=0;$i<count($this->parameter["ID_G_CHINHSUA"]);$i++){
				$this->phanquyenhscv->insert(array("ID_G"=>$this->parameter["ID_G_CHINHSUA"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'chinhsua'));
			}
			for($i=0;$i<count($this->parameter["ID_U_CHINHSUA"]);$i++){
				$this->phanquyenhscv->insert(array("ID_U"=>$this->parameter["ID_U_CHINHSUA"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'chinhsua'));
			}
		    for($i=0;$i<count($this->parameter["ID_DEP_CHINHSUA"]);$i++){
				$this->phanquyenhscv->insert(array("ID_DEP"=>$this->parameter["ID_DEP_CHINHSUA"][$i],"ID_HSCV"=>$this->id_hscv,"QUYEN"=>'chinhsua'));
			}
			$this->_redirect("/hscv/ThuMuc");
		}catch(Exception $ex){
			$this->_redirect("/default/error");
		}
	}
}
