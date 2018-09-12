<?php

require_once ('Zend/Controller/Action.php');
require_once 'vbdi/models/BanHanhVanBanModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
class Vbdi_danhsachvanbanbanhanhController extends Zend_Controller_Action {
    function init(){
    	$this->view->title= "Ban hành văn bản";
    }
    
    function indexAction(){
    	$param = $this->_request->getParams();
		$config = Zend_Registry::get('config');
    	$search = $param['search'];
		$NGUOISOAN = $param["NGUOISOAN"];
		$filter_object = $param['filter_object'];
		$this->view->title = "Văn bản chờ ban hành";
    	$this->view->year = QLVBDHCommon::getYear();
    	$this->view->NGUOISOAN = $NGUOISOAN;
		$this->view->filter_object = $filter_object;
		$this->view->search = $search ;
		
		$duthaocount = BanHanhVanBanModel::countDuthao($this->view->year,$search,$filter_object, $NGUOISOAN);
		$page = $param['page'];
		$limit = $param['limit1'];
		if($limit==0 || $limit=="")
			$limit=$config->limit;
		if($page==0 || $page=="")
			$page=1;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($duthaocount,10,$limit,"frm",$page,"/vbdi/danhsachvanbanbanhanh/index");
		$this->view->data = BanHanhVanBanModel::getAllListBanHanhVanBanMixed($this->view->year,$search,$filter_object,($page-1)*$limit,$limit, $NGUOISOAN);
    }
    
    function detailAction(){
    	$this->_helper->layout->disableLayout();
    	$param = $this->_request->getParams();
    	$idDuthao = $param['idDuthao'];
    	$loaiCV  = $param['loaiCV'];
    	$nky = $param['nky'];
		$nsoan = $param['nsoan'];
    	//$this->view->idHSCV = $idHSCV;
    	//$this->view->loaiCV = $loaiCV;
    	//$this->view->nky = $nky;
		//$this->view->nsoan =$nsoan;
		$this->view->loaiCV = $loaiCV;
		$model = new BanHanhVanBanModel();
		$this->view->data =$model->getDetailDuThao(QLVBDHCommon::getYear(),$loaiCV,$idDuthao); 	
    }

	function deleteAction(){
		$this->_helper->layout->disableLayout();
    	
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$vbduthao = new VanBanDuThaoModel($year);
		$idDuthao = $params["id"];
		try{
			$vbduthao->updateTrangthaiByIdDuthao($idDuthao,3/*trang thai*/);
			echo 1;
		}
		catch(Exception $ex){
			echo 0;
		}
		exit;
	}
    
    
}

?>
