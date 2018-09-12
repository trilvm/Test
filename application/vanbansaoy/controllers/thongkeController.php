<?php

require_once ('Zend/Controller/Action.php');
require_once 'vanbansaoy/models/tkvanbansaoyModel.php';
class Vanbansaoy_ThongkeController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Thống kê văn bản sao y";
		
	}

	function indexAction(){


	}

	function reportviewAction(){
		$year = QLVBDHCommon::getYear();
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		$config = Zend_Registry::get("config");
		$this->view->config = $config;
		if($fromdate == "") $fromdate = "1/1/$year";
		if($todate == "")	$todate = "31/12/$year";		
		$this->view->data=TkvanbansaoyModel::getReportDataSaoy($fromdate,$todate);                		
		$is_in = $param['is_in'];
		if($is_in){
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("thongke/reportview_in.phtml");
		}

	}
	function reportviewexcelAction(){
		$year = QLVBDHCommon::getYear();
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$config = Zend_Registry::get("config");
		$this->view->config = $config;
		if($param['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=thongkevanbansaoy.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];
		if($fromdate == "") $fromdate = "1/1/$year";
		if($todate == "")	$todate = "31/12/$year";
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$this->view->data=TkvanbansaoyModel::getReportDataSaoy($fromdate,$todate);
	}
}

?>
