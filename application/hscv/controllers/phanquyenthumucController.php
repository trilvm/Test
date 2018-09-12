<?php

/**
 * phanquyenthumucController
 * @deprecated 15/10/2009 by hieuvt
 * @author hieuvt
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'hscv/models/phanquyenhscvModel.php';
require_once 'hscv/models/phanquyenthumucModel.php';
require_once 'config/hscv.php';

class hscv_phanquyenthumucController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated phanquyenthumucController::indexAction() default action
		$this->view->parameter =  $this->getRequest()->getParams();
		$this->view->id_thumuc = $this->view->parameter["id"];
		
		// Set Year
        $year = $parameter["year"];
        Zend_Registry::set('year',$year);
        
        $this->ThuMuc = new ThuMucModel();
		$this->ThuMucPQ = $this->ThuMuc->find($this->view->id_thumuc)->current();
		
		$this->view->title = "Quyền truy xuất thư mục - [".$this->ThuMucPQ->NAME."]";
		$this->phanquyenthumuc = new phanquyenthumucModel();
		$this->phanquyenthumuc->_id_thumuc =$this->view->id_thumuc;  
		// kiểm tra user này có được phép phân quyền không
	    if (!$this->phanquyenthumuc->checkPermission(Zend_Registry::get('auth')->getIdentity()->ID_U,null,$this->view->id_thumuc,'phanquyen')) {
//            $this->_redirect('/default/error/error?id=2');
        }
        
		$this->view->Groups = $this->phanquyenthumuc->GetAllGroups();
		$this->view->Users = $this->phanquyenthumuc->GetAllUsers();
		$this->view->Departments = $this->phanquyenthumuc->GetAllDepartments();
				
		QLVBDHButton::EnableSave("");
		QLVBDHButton::EnableBack("/hscv/ThuMuc");
	}
	/**
	 * Lưu phanquyenthumuc
	 */
	public function saveAction(){
        try{
			$this->ThuMuc = new ThuMucModel();
			$this->phanquyenthumuc = new phanquyenthumucModel();
			$this->phanquyenhscv = new phanquyenhscvModel();
			$this->parameter =  $this->getRequest()->getParams();
			$this->id_thumuc = $this->parameter["id"];

			// Set Year
            $year = $this->parameter["year"];
            Zend_Registry::set('year',$year);
        
        	// kiểm tra người dùng này có được phép phân quyền không
		    if (!$this->phanquyenthumuc->checkPermission(Zend_Registry::get('auth')->getIdentity()->ID_U,null,$this->view->id_thumuc,'phanquyen')) {
//              $this->_redirect('/default/error/error?id=2');
            }
			$this->phanquyenthumuc->delete("ID_THUMUC=".(int)$this->id_thumuc);
			for($i=0;$i<count($this->parameter["ID_G_TRUYXUAT"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_G"=>$this->parameter["ID_G_TRUYXUAT"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'truyxuat'));
			}
			for($i=0;$i<count($this->parameter["ID_U_TRUYXUAT"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_U"=>$this->parameter["ID_U_TRUYXUAT"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'truyxuat'));
			}
		    for($i=0;$i<count($this->parameter["ID_DEP_TRUYXUAT"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_DEP"=>$this->parameter["ID_DEP_TRUYXUAT"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'truyxuat'));
			}
		    for($i=0;$i<count($this->parameter["ID_G_TAOMOI"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_G"=>$this->parameter["ID_G_TAOMOI"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'taomoi'));
			}
			for($i=0;$i<count($this->parameter["ID_U_TAOMOI"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_U"=>$this->parameter["ID_U_TAOMOI"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'taomoi'));
			}
		    for($i=0;$i<count($this->parameter["ID_DEP_TAOMOI"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_DEP"=>$this->parameter["ID_DEP_TAOMOI"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'taomoi'));
			}
		    for($i=0;$i<count($this->parameter["ID_G_PHANQUYEN"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_G"=>$this->parameter["ID_G_PHANQUYEN"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'phanquyen'));
			}
			for($i=0;$i<count($this->parameter["ID_U_PHANQUYEN"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_U"=>$this->parameter["ID_U_PHANQUYEN"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'phanquyen'));
			}
		    for($i=0;$i<count($this->parameter["ID_DEP_PHANQUYEN"]);$i++){
				$this->phanquyenthumuc->insert(array("ID_DEP"=>$this->parameter["ID_DEP_PHANQUYEN"][$i],"ID_THUMUC"=>$this->id_thumuc,"QUYEN"=>'phanquyen'));
			}
			// trường hợp thư mục này có thư mục con
			// thực hiện thêm các quyền này đối với các thư mục con của thư mục này
			try{
				// lấy thư mục đang phân quyền
				$id_thumuc = $this->id_thumuc;
	        	// lấy tất cả thư mục con của thư mục này
	        	$AllChild = $this->ThuMuc->GetAllChildren($id_thumuc);
	        	// hợp tất cả các quyền của thư mục cha với các thư mục con của nó
	        	foreach ($AllChild as $Child){
	        		$this->phanquyenthumuc->mergePermission($id_thumuc,$Child['ID_THUMUC']);
	        		// thực hiện merge quyền đối với tất cả các hồ sơ công việc của mỗi thư mục
		        	$AllHscv = $this->phanquyenhscv->GetAllHscvByYear($Child['ID_THUMUC'],2009);
		        	foreach ($AllHscv as $hscv){
		        		$this->phanquyenhscv->mergePermission($Child['ID_THUMUC'],$hscv['ID_HSCV']);
		        	}
	        	}
	        }catch(Exception $ex){
	        }  
			$this->_redirect("/hscv/ThuMuc");
		}catch(Exception $ex){
			$this->_redirect("/default/error");
		}
	}
}
