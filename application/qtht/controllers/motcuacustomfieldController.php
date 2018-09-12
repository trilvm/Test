<?php
/**
 * @name DanhMucSoVanBan
 * @author trunglv
 * @package qtht
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'config/qtht.php';
require_once 'qtht/models/CoQuanModel.php';
require_once 'motcua/models/linhvucmotcuaModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'qtht/models/motcua_custom_fieldModel.php';

class Qtht_motcuacustomfieldController extends Zend_Controller_Action {

	
	var $sovanbanTable; // bang So Van Ban
	
	/**
	 * Ham khoi tao du lieu
	 */
	public function init()
	{
		 $this->model = new SoVanBanModel();
		 $this->view->title="Hồ sơ một cửa - Trường tùy biến";
	}
	/**
	 *  Ham xu ly cho action xem (view index.phtml)
	 */
	public function indexAction()
	{
		$config = Zend_Registry::get('config');
		$page = $this->_request->getParam('page');
		$limit = $this->_request->getParam('limit');
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		
		$filter_object = $this->_request->getParam('filter_object');
		$this->view->filter_object = $filter_object; 
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
		$ID_LV_MC = $this->_request->getParam("ID_LV_MC");
		$this->view->ID_LV_MC = $ID_LV_MC;
		$this->view->data = motcua_custom_fieldModel::getAll($ID_LV_MC);
		//$n_rows = motcua_custom_fieldModel::count($ID_LV_MC);
		QLVBDHButton::EnableAddNew("");
		QLVBDHButton::EnableDelete("");
		//QLVBDHButton::EnableHelp('');
		//$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
		$this->view->limit=$limit;
		$this->view->page=$page;
		//$this->view->subtitle="Danh sách";
		
	}
	
	/**
	 * Ham xu ly cho action thêm  so van ban 
	 */
	public function saveAction()
	{
		$params = $this->_request->getParams();
		$id = $params["ID_MC_CF"];
		
		$is_new_column = $params["is_new_column"];
		if($is_new_column){
			motcua_custom_fieldModel::addNewColumn($params["NAME_COLUMN_NEW"],$params["TYPE"]);
			$params["NAME_COLUMN"] = $params["NAME_COLUMN_NEW"];
		}
		if($id){
			motcua_custom_fieldModel::save($id,$params);
			$this->_redirect('/qtht/motcuacustomfield');
		}
		if($params["SEL_ALL_LV"] == 1 && $params["ID_LV_MC"] > 0){
			
			motcua_custom_fieldModel::saveAllLinhvuc($params);
		}else{
			motcua_custom_fieldModel::save($id,$params);
		}
		$this->_redirect('/qtht/motcuacustomfield');
	}
	
	/**
	 * Ham xu ly cho action xoa so van ban
	 */
	public function deleteAction()
	{
		if($this->_request->isPost())
		{
			
			//Lay id cua so van ban can xoa
			$idarray = $this->_request->getParam('DEL');
			foreach($idarray as $id){
				motcua_custom_fieldModel::delete($id);
			}
			$this->_redirect('/qtht/motcuacustomfield');
			
		}
		else 
		{
			$this->_redirect('/qtht/motcuacustomfield');
		}
		
	}
	
	/**
	 * Ham xu ly cho action cap nhat mot so van ban
	 */
	public function inputAction()
	{
		//var_dump(motcua_custom_field::getColumnMotcuaHoso()); exit;
		$user = Zend_Registry::get('auth')->getIdentity();
		//$department_data = UsersModel::getUserDepId($user->ID_U);
		
		//$this->view->dep_name =  $department_data["NAME"];
		//$this->view->ID_DEP = $department_data[""]
		//Tao phan button
		QLVBDHButton::EnableSave("/qtht/DanhMucChucDanh/save");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		$params = $this->_request->getParams();
		$id = $params["id"];
		if($id){
			$this->view->data = motcua_custom_fieldModel::getById($id);
			$this->view->title = "Cập nhật hồ sơ một cửa - Trường tùy biến";
		}else{
			$this->view->title = "Thêm mới hồ sơ một cửa - Trường tùy biến";		
		}
		if(!$this->view->data) $this->view->data = array(); 
		if($params["ID_LV_MC"]) $this->view->data["ID_LV_MC"] = $params["ID_LV_MC"];
		if($params["ID_MC_CF"]) $this->view->data["ID_MC_CF"] = $params["ID_MC_CF"];
		if($params["NAME_DISPLAY"]) $this->view->data["NAME_DISPLAY"] = $params["NAME_DISPLAY"];
		if($params["NAME_COLUMN"]) $this->view->data["NAME_COLUMN"] = $params["NAME_COLUMN"];
		if($params["LOAIHOSO_CODE"]) $this->view->data["LOAIHOSO_CODE"] = $params["LOAIHOSO_CODE"];
		if($params["NAME_COLUMN_NEW"]) $this->view->data["NAME_COLUMN_NEW"] = $params["NAME_COLUMN_NEW"];
		if($params["TYPE"]) $this->view->data["TYPE"] = $params["TYPE"];
		if($params["IS_REQUIRED"]) $this->view->data["IS_REQUIRED"] = $params["IS_REQUIRED"];
		if($params["IS_BAOCAO"]) $this->view->data["IS_BAOCAO"] = $params["IS_BAOCAO"];
		if($params["IS_TIEPNHAN"]) $this->view->data["IS_TIEPNHAN"] = $params["IS_TIEPNHAN"];
		if($params["IS_KETQUA"]) $this->view->data["IS_KETQUA"] = $params["IS_KETQUA"];
		$this->renderScript("motcuacustomfield/InputData.phtml");	
	}
private function checkInputData($name,$active,$year,$type){
		
		$strurl='/default/error/error?control=danhmucsovanban&mod=qtht&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('text128_re',$name,'ERR11006001').",";
		$strerr .= ValidateInputData::validateInput('boolean',$active,"ERR11006005").",";
		$strerr .= ValidateInputData::validateInput('year',$year,"ERR11006003").",";
		$strerr .= ValidateInputData::validateInput('int_between_no_inclusive=0,4',$type,"ERR11006009").",";
		if(strlen($strerr)!=4){
			$this->_redirect($strurl.$strerr);
		}
		return true;
	}
	
}


