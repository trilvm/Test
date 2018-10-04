<?php
require_once ('Zend/Controller/Action.php');
require_once 'report/models/XulyvanbandenModel.php';
require_once 'report/models/VanbandenreportModel.php';
require_once 'report/models/Ad_XulyvanbandenModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
class Giaoviec_BaoCaoGiaoViecController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Báo cáo";
		$this->view->subtitle = "Báo cáo tình hình xử lý nhiệm vụ được giao";
	}
	
	function indexAction(){		
		$this->renderScript("report/baocaogiaoviec.phtml");
	}	
	function reportviewAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
                if($fromdate == "")
				$fromdate = "1/1";
			if($todate == "")
				$todate = "31/12";
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$this->view->data = VanbandenreportModel::getReportDataCongViec($fromdate,$todate,$id_svb,$id_lvbs);
//                var_dump($this->view->data);exit;
		$is_in = $param['is_in'];
		if($is_in){
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("BaoCaoGiaoViec/reportview_in.phtml");
		}
		
	}
	
	function reportviewexcelAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=baocaocongviec.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];		
		if($fromdate == "")
				$fromdate = "1/1";
			
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$id_lvbs = $param['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$id_svb = $param['sel_svb'];
		$this->view->xuat=(int)$param["xuat"];
		$this->view->data = VanbandenreportModel::getReportDataCongViec($fromdate,$todate,$id_svb,$id_lvbs);
	}
}

?>
