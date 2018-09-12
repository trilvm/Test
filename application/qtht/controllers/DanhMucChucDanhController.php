<?php
/**
 * @name DanhMucChucDanhController
 * @author Luu Van Trung
 * @package qtht
 * @version 1.0 
 */

require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/ChucDanhModel.php';
require_once 'qtht/models/fk_chucdanh_empModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'config/qtht.php';

class Qtht_DanhMucChucDanhController extends Zend_Controller_Action {

	var $model ; //doi tuong bang chuc danh
	
	
	/**
	 * Ham khoi tao du lieu cho doi tuong controller
	 */
	public function init()
	{
		$this->model = new ChucDanhModel();
		$this->view->title = "Danh mục chức danh";
	}
	
	/**
	 * Ham xu ly cho action xem danh sach danh sach chuc danh (index) 
	 */
	
	public function indexAction()
	{	
		//$this->view->subtitle="Danh sách";
		$config = Zend_Registry::get('config');
		$page = $this->_request->getParam('page');
		$limit = $this->_request->getParam('limit');
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$this->view->filter_object = $filter_object; 
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
		$this->view->data = $this->model->findByMixed($page,$limit,$search,$filter_object);
		$n_rows = $this->model->count($search,$filter_object);
		QLVBDHButton::EnableAddNew("/qtht/DanhMucChucDanh/input");
		QLVBDHButton::EnableDelete("/qtht/DanhMucChucDanh/");
		//QLVBDHButton::EnableHelp('');
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
		$this->view->limit=$limit;
		$this->view->page=$page;
		
	}

	/**
	 * Ham xu ly cho action them moi va cap nhat 
	 */
	public function saveAction()
	{
		if($this->_request->isPost()){
			$active = $this->_request->getParam("active");
			if(!$active)
				$active = 0;
			$name = $this->_request->getParam("name");
			$code = $this->_request->getParam("code");
			$id = $this->_request->getParam("idChucDanh");
			//Kiem tra du lieu hop le
			$this->checkInputData($name,$active);
			if($id>0)
			{
				//truong hop cap nhat
				$where = 'ID_CD='.$id; 
				try{
					$this->model->update(array('NAME'=>$name,'CODE'=>$code, 'ACTIVE'=>$active),$where);
					
				}catch (Exception $e)
				{
					//Thong bao loi khi khong cap nhat du lieu
					$this->_redirect('/default/error/error?control=DanhMucChucDanh&mod=qtht&id=ERR11002006');
				}
				
				
			}else
			{
			 	//truong hop them moi
				$arr_newdata = array("NAME"=> $name , "CODE" =>$code, "ACTIVE" => $active );
				try{
					$id = $this->model->insert($arr_newdata);
				}catch (Exception $ex) {
					
					//Thong bao loi khi insert
					$this->_redirect('/default/error/error?control=DanhMucChucDanh&mod=qtht&id=ERR11002007');
				}
				
			}
			$fk_chucdanh_empTable = new fk_chucdanh_empModel();
			$arr_emp = $this->_request->getParam("SEL_EMP");
			$fk_chucdanh_empTable->deleteByIdChucDanh($id);
			$fk_chucdanh_empTable->insertOneChucDanhMultiEmp($id,$arr_emp);
			$this->_redirect('/qtht/DanhMucChucDanh');
		}
		else 
		{
			$this->_redirect('/qtht/DanhMucChucDanh');
		}
	}
	
	/**
	 *	Ham xu ly cho action xoa mot mot chuc danh  
	 */
	
	public function deleteAction()
	{
		
	
		if($this->_request->isPost())
		{
			
			//Lay id cua cac chuc danh can xoa
			$idarray = $this->_request->getParam('DEL');
			//thuc hien xoa chuc danh
			$where = 'ID_CD in ('.implode(',',$idarray).')'; 
			try{
				if(!$this->model->delete($where))
				{
					//Loi : Khong the xoa 
					$this->_redirect('/default/error/error?control=DanhMucChucDanh&mod=qtht&id=ERR11002007');
				}
			}catch(Exception $ex)
			{
				//loi xoa du lieu
				$this->_redirect('/default/error/error?control=DanhMucChucDanh&mod=qtht&id=ERR11002007');
			}
			
			//chuyen den trang xem danh sach cac chuc danh
			$this->_redirect('/qtht/DanhMucChucDanh');
		}
		else 
		{
			$this->_redirect('/qtht/DanhMucChucDanh');
		}
		
	
	}
	
	
	/**
	 * Ham xu ly cho action hiện thị form nhap lieu thong tin ve chuc danh
	 */
	public function inputAction()
	{
		if($this->_request->isPost())
		{
			
			
			//Tao phan button
			QLVBDHButton::EnableSave("/qtht/DanhMucChucDanh/save");
			QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
			//Giu cac tham so cua trang danh sach
			$this->view->page = $this->_request->getParam('page');
			$this->view->limit = $this->_request->getParam('limit');
			$this->view->filter_object = $this->_request->getParam('filter_object');
			$this->view->search = $this->_request->getParam("search");	
			//Lay id cua chuc danh can cap nhat
			$idCapNhat = $this->_request->getParam('idChucDanh');
			if($idCapNhat > 0){
				
				//Truong hop cap nhat
				$rowcn = $this->model->find($idCapNhat);
				
				//kiem tra id cua chuc danh can cap nhat co nam trong csdl
				if($rowcn->count() >0 )
				{
					$this->view->namebefore = $rowcn->current()->NAME;
					$this->view->code = $rowcn->current()->CODE;
					$this->view->activeselect = $rowcn->current()->ACTIVE;
					$this->view->id= $idCapNhat;
				}	
				else{
					// Loi khi khong tim thay id cua chuc danh trong co so du lieu
					$this->_redirect('/default/error/error?control=DanhMucChucDanh&mod=qtht&id=ERR11002002');
				}	
				
				$this->view->title = "Cập nhật chức danh";
			}
			else{
				//Truong hop them moi
				$this->view->title = "Thêm mới chức danh";
				
			}
			$table = new fk_chucdanh_empModel();
			$this->view->data = $table->getListEmp($idCapNhat);
			
			//Hien trang nhap lieu 
			$this->renderScript("DanhMucChucDanh/InputData.phtml");
		}
		
	}
	
	private function checkInputData($name,$active){
		
		$strurl='/default/error/error?control=danhmucchucdanh&mod=qtht&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('text128_re',$name,'ERR11002001').",";
		$strerr .= ValidateInputData::validateInput('boolean',$active,"ERR11002005").",";
		if(strlen($strerr)!=2){
			$this->_redirect($strurl.$strerr);
		}
		return true;
	}
	
}

?>
