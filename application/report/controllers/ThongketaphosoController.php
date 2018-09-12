<?php

require_once ('Zend/Controller/Action.php');
require_once 'report/models/ThongketaphosoModel.php';
class Report_ThongketaphosoController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Thống kê tập hồ sơ";
		//$this->view->subtitle = "Thống kê tập hồ sơ";
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
		if($param['bienvanban']==1){
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$this->view->datavb=ThongketaphosoModel::Vanbantaphoso($param['id_taphoso']);
		$this->view->datadinhkem=ThongketaphosoModel::dinhkemtaphoso($param['id_taphoso']);
		$this->renderScript("Thongketaphoso/reportview_vanban.phtml");
		}		
		$this->view->data=ThongketaphosoModel::getReportDataHoso($fromdate,$todate);
		$this->view->datathumuc=ThongketaphosoModel::selectthucmuc();
		$is_in = $param['is_in'];
		if($is_in){	
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("Thongketaphoso/reportview_in.phtml");
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
			header("Content-Disposition: attachment; filename=thongketaphoso.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$fromdate = $param['fromdate'];
		$todate = $param['todate'];				
		if($fromdate == "") $fromdate = "1/1/$year";
		if($todate == "")	$todate = "31/12/$year";
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;		
		$this->view->data=ThongketaphosoModel::getReportDataHoso($fromdate,$todate);
		$this->view->datathumuc=ThongketaphosoModel::selectthucmuc();		
	}
}

?>
