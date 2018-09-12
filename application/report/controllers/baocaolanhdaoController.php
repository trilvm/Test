<?php
require_once'Zend/Controller/Action.php';
require_once 'report/models/BaocaolanhdaoModel.php';
require_once 'report/models/TiepnhanhosomotcuaModel.php';
class  Report_baocaolanhdaoController extends Zend_Controller_Action{
	function init(){
		$this->view->title="Báo cáo lãnh đạo";
		//$this->view->subtitle="Báo cáo lãnh đạo";
	}
	
	function indexAction(){
		$param = $this->_request->getParams();
		$this->view->todate = $param["todate"]; 
		$this->view->fromdate = $param["fromdate"];
		 
	}
	function indextkeAction(){
		$param = $this->_request->getParams();
		$this->view->todate = $param["todate"]; 
		$this->view->fromdate = $param["fromdate"];
		$this->view->param = $param;
		$fromdate = $param["fromdate"];
		if($fromdate == "")
			$fromdate = "1/1";
		$todate = $param['todate'];
		if($todate == "")
			$todate = "31/12";		
		$this->view->xtodate = $this->view->todate;
		$this->view->xfromdate = $this->view->fromdate;
		$this->view->xtodate = $todate."/".QLVBDHCommon::getYear();
		$this->view->xtodate = implode("-",array_reverse(explode("/",$this->view->xtodate)))." 23:59:59";
		$this->view->xfromdate = $fromdate."/".QLVBDHCommon::getYear();
		$this->view->xfromdate = implode("-",array_reverse(explode("/",$this->view->xfromdate)))." 00:00:00";
		$this->view->title="Thống kê tình hình xử lý hồ sơ";
		//$this->view->subtitle="Tình hình xử lý hồ sơ";
	}
	
	function xuatwebtkeAction(){
		global $auth;
		$user = $auth->getIdentity();
		$config = Zend_Registry::get('config');
		$this->view->config = $config;
		$this->view->user = $user;
		$this->_helper->layout->disableLayout();
		$param = $this->_request->getParams();
		$this->view->todate = $param["todate"]; 
		$this->view->fromdate = $param["fromdate"];
		$this->view->param = $param;
		$fromdate = $param["fromdate"];
		if($fromdate == "")
			$fromdate = "1/1";
		$todate = $param['todate'];
		if($todate == "")
			$todate = "31/12";
		$this->view->todate = $todate;
		$this->view->fromdate = $fromdate;
		$this->view->xtodate = $this->view->todate;
		$this->view->xfromdate = $this->view->fromdate;
		$this->view->xtodate = $todate."/".QLVBDHCommon::getYear();
		$this->view->xtodate = implode("-",array_reverse(explode("/",$this->view->xtodate)))." 00:00:00";
		$this->view->xfromdate = $fromdate."/".QLVBDHCommon::getYear();
		$this->view->xfromdate = implode("-",array_reverse(explode("/",$this->view->xfromdate)))." 23:59:59";
		$this->view->title="Thống kê tình hình xử lý hồ sơ";
		//$this->view->subtitle="Tình hình xử lý hồ sơ";
	}
	function xuatexceltkeAction(){
		$this->_helper->layout->disableLayout();
		
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=thongkephucvulanhdao.xls;");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		global $auth;
		$user = $auth->getIdentity();
		$config = Zend_Registry::get('config');
		$this->view->config = $config;
		$this->view->user = $user;
		$param = $this->_request->getParams();
		$this->view->todate = $param["todate"]; 
		$this->view->fromdate = $param["fromdate"];
		$this->view->param = $param;
		$fromdate = $param["fromdate"];
		if($fromdate == "")
			$fromdate = "1/1";
		$todate = $param['todate'];
		if($todate == "")
			$todate = "31/12";
		$this->view->todate = $todate;
		$this->view->fromdate = $fromdate;
		$this->view->xtodate = $this->view->todate;
		$this->view->xfromdate = $this->view->fromdate;
		$this->view->xtodate = $todate."/".QLVBDHCommon::getYear();
		$this->view->xtodate = implode("-",array_reverse(explode("/",$this->view->xtodate)))." 00:00:00";
		$this->view->xfromdate = $fromdate."/".QLVBDHCommon::getYear();
		$this->view->xfromdate = implode("-",array_reverse(explode("/",$this->view->xfromdate)))." 23:59:59";
		$this->view->title="Thống kê tình hình xử lý hồ sơ";
		//$this->view->subtitle="Tình hình xử lý hồ sơ";
	}
	function reportviewAction(){
		
		//var_dump($this->view->data);
		//$this->view->title="Báo cáo lãnh đạo";
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		$param = $this->_request->getParams();
		$config = Zend_Registry::get("config");
		
		//tham so du lieu
		
		$type = $param['sel_lcv'];//loai cong viec
		if(!$type || $type==0)
			$type = 1;
		$fromdate = $param["fromdate"];
		if($fromdate == "")
			$fromdate = "1/1";
		$todate = $param['todate'];
		if($todate == "")
			$todate = "31/12";		
		$id_pbs = $param["sel_pb"];
		$this->view->type = $type;
		$this->view->todate = $todate;
		$this->view->fromdate = $fromdate;
		$this->view->id_pbs = $id_pbs;
		
		$this->view->title="Báo cáo lãnh đạo từ ngày $fromdate đến ngày $todate";
		
		//tham so phan trang
		$page = $param['page'];
		$limit = $param['limit1'];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$this->view->page = $page;
		$this->view->limit = $limit;
		
		
		
		$count = BaocaolanhdaoModel::getCountReportData($type,$todate,$fromdate,$id_pbs);
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($count,10,$limit,"frm",$page,"/report/baocaolanhdao/reportview");
		$this->view->data = BaocaolanhdaoModel::getReportData($type,$todate,$fromdate,$id_pbs,($page-1)*$limit,$limit);
	}
}


