<?php

require_once ('Zend/Controller/Action.php');
require_once 'report/models/XulyvanbandenModel.php';
require_once 'report/models/VanbandenreportModel.php';
require_once 'report/models/Ad_XulyvanbandenModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
class Report_VanBanDenController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Báo cáo";
		$this->view->subtitle = "Báo cáo văn bản đến";
	}
	
	function indexAction(){		
		
	}	
	function reportviewAction(){	
		
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		/*if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=baocaovanbanden.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}*/
		
		//lay id co quan ban hanh
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		$this->view->ngayden=$param['ngayden'];
		$this->view->soden=$param['soden'];
		$this->view->coquanbh=$param['coquanbh'];
		$this->view->trichyeund=$param['trichyeund'];
		$this->view->soban=$param['soban'];
		$this->view->chuyenchoai=$param['chuyenchoai'];
		$this->view->ketqua=$param['ketqua'];		
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$is_lienthong = $param['is_lienthong'];
		$this->view->data = VanbandenreportModel::getReportData($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong);
		$is_in = $param['is_in'];
		if($is_in){
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("vanbanden/reportview_in.phtml");
		}
		
	}

	function reportviewcvtltAction(){
		
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		
		$lvb = new LoaiVanBanModel();
		if((int)$param['loaivb'] >0)
		{
				$tenloai = $lvb->getNameById($param['loaivb']);
		}
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=Sodangkyvanbanden.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		$this->view->ngayden=$param['ngayden'];
		$this->view->soden=$param['soden'];
		$this->view->coquanbh=$param['coquanbh'];
		$this->view->trichyeund=$param['trichyeund'];
		$this->view->soban=$param['soban'];
		$this->view->chuyenchoai=$param['chuyenchoai'];
		$this->view->ketqua=$param['ketqua'];		
		$this->view->idloaivb=$tenloai;		
		if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$is_in = $param['is_in'];
		$is_lienthong = $param['is_lienthong'];
		$this->view->data = VanbandenreportModel::getReportDatacvtlt($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong);
		if($is_in){
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";
			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("vanbanden/reportviewcvtlt_in.phtml");
		}
		
	}
	
	function reportviewexcelAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=baocaovanbanden.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		$this->view->ngayden=$param['ngayden'];
		$this->view->soden=$param['soden'];
		$this->view->coquanbh=$param['coquanbh'];
		$this->view->trichyeund=$param['trichyeund'];
		$this->view->soban=$param['soban'];
		$this->view->chuyenchoai=$param['chuyenchoai'];
		$this->view->ketqua=$param['ketqua'];		
		if($fromdate == "")
				$fromdate = "1/1";
			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$is_lienthong = $param['is_lienthong'];
		$this->view->xuat=(int)$param["xuat"];
		$this->view->data = VanbandenreportModel::getReportData($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong);
	}
	function reportviewexcelcvtltAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		
		$lvb = new LoaiVanBanModel();
		if((int)$param['loaivb'] >0)
		{
				$tenloai = $lvb->getNameById($param['loaivb']);
		}
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=Sodangkyvanbanden.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		$this->view->ngayden=$param['ngayden'];
		$this->view->soden=$param['soden'];
		$this->view->coquanbh=$param['coquanbh'];
		$this->view->trichyeund=$param['trichyeund'];
		$this->view->soban=$param['soban'];
		$this->view->chuyenchoai=$param['chuyenchoai'];
		$this->view->ketqua=$param['ketqua'];		
		$this->view->idloaivb=$tenloai;		
		if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$is_lienthong = $param['is_lienthong'];
		$this->view->xuat=(int)$param["xuat"];
		$this->view->data = VanbandenreportModel::getReportDatacvtlt($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong);
		if($param['is_in'] == 1) {
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("vanbanden/reportviewcvtlt_in.phtml");
		}
	}
}

?>
