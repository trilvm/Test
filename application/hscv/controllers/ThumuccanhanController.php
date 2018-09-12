<?
require_once 'hscv/models/ThumuccanhanModel.php';
require_once 'vbden/models/vbdenModel.php';

class Hscv_ThumuccanhanController extends Zend_Controller_Action{

	function init(){
	}

	function indexAction(){
	}

	function inputthumucAction(){
		$param = $this->_request->getParams();
		$user = Zend_Registry::get('auth')->getIdentity();
		$this->view->datathumuc = ThumuccanhanModel::toComboThuMucPrivate();
		$this->view->data = ThumuccanhanModel::getThumucById($param["id"],$user->ID_U);
		$this->view->id = (int)$param["id"];
		$this->view->title = "Thư mục cá nhân";
		if($this->view->id){
			//$this->view->subtitle = "Cập nhật";
			
		}else{
			//$this->view->subtitle = "Thêm mới";
		}
		QLVBDHButton::EnableSave("/hscv/thumuccanhan/savethumuc");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
	}

	function savethumucAction(){
		$param = $this->_request->getParams();
		//var_dump($param);
		$id = (int)$param["id"];
		$id_parent = 1;
		if($param["ID_PARENT"] > 1)
			$id_parent = $param["ID_PARENT"]; 
		ThumuccanhanModel::saveThuMuc($id,$param["NAME"],$id_parent);
		$this->_redirect("/hscv/thumuccanhan/listthumuc");
		exit;
	}

	function deletethumucAction(){
		$param = $this->_request->getParams();
		//var_dump($param);
		$id = (int)$param["id"];
		echo ThumuccanhanModel::deleteThumuc($id); 
		$this->_redirect("/hscv/thumuccanhan/listthumuc");
		exit;
	}

	function listthumucAction(){
		$user = Zend_Registry::get('auth')->getIdentity();
		$data = ThumuccanhanModel::getAllByIdU($user->ID_U);
		$this->view->data = $data;
		$this->view->title = " Danh sách thư mục cá nhân";
		//$this->view->subtitle = "Danh sách thư mục";
		QLVBDHButton::EnableAddNew("/hscv/thumuccanhan/inputthumuc");
		
	}
	function inputluutmcnAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$this->view->ID_HSCV = $param["id"];
		$user = Zend_Registry::get('auth')->getIdentity();
		$this->view->datathumuc = ThumuccanhanModel::toComboThuMucPrivate();
		$this->view->thumuclist = ThumuccanhanModel::getTMCNByHSCV($this->view->ID_HSCV);
		
		//$this->idtm
	}
	function saveluutmcnAction(){
		$param = $this->_request->getParams();
		$id_tmcn =  $param["ID_TMCN"];
		$id_hscv =  $param["ID_HSCV"];
		echo ThumuccanhanModel::insertHscvIntoTMCN($id_hscv,$id_tmcn);
		//var_dump(ThumuccanhanModel::getTMCNByHSCV($id_hscv));
		$this->_redirect("/hscv/thumuccanhan/inputluutmcn/id/$id_hscv");
		exit;
	}
	function deleteluutmcnAction(){
		$param = $this->_request->getParams();
		$id_tmcn =  $param["ID_TMCN"];
		$id_hscv =  $param["ID_HSCV"];
		echo ThumuccanhanModel::removeHscv($id_hscv,$id_tmcn);
		//var_dump(ThumuccanhanModel::getTMCNByHSCV($id_hscv));
		$this->_redirect("/hscv/thumuccanhan/inputluutmcn/id/$id_hscv");
		exit;
		
	}

	function listAction(){
		
		$user = Zend_Registry::get('auth')->getIdentity();
		$param = $this->_request->getParams();
		$config = Zend_Registry::get('config');
		$page = $param['page'];
		$limit = $param['limit1'];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$id_thumuc = $param["id_thumuc"];
		if(($page-1)*$limit==$hscvcount && $hscvcount>0)$page--;
		$hscvcount = ThumuccanhanModel::countListHSCV($id_thumuc);
		//if(($page-1)*$limit==$hscvcount && $hscvcount>0)$page--;
		//$data = ThumuccanhanModel::getAllByIdU($user->ID_U);
		$this->view->thumuc = ThumuccanhanModel::getAllByIdU($user->ID_U);
		$this->view->id_thumuc = $id_thumuc;
		$this->view->data = ThumuccanhanModel::getListHSCV($id_thumuc,($page-1)*$limit,$limit);
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
		
		$this->view->showPage =QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/hscv/thumuccanhan/list/id_thumuc/".$id_thumuc);
		//var_dump($this->view->data);
		$this->view->title = "Thư mục cá nhân";
		//$this->view->subtitle = "Thư mục cá nhân";
		$this->view->page = $page;
		$this->view->limit = $limit;
	}
}