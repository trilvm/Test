<?php
require_once('Zend/Controller/Action.php');
require_once('Report/models/TiepnhanhosomotcuaModel.php');
require_once 'motcua/models/linhvucmotcuaModel.php';
require_once('motcua/models/motcua_hosoModel.php');
class Report_TiepnhanhosomotcuaController extends Zend_Controller_Action{
	function init(){
		$this->view->title = "Báo cáo tiếp nhận Hồ sơ một cửa";
		//$this->view->subtitle = "Tiếp nhận Hồ sơ một cửa";
	}
	
	function indexAction(){
		$ID_LV_MC = $param['ID_LV_MC'];
		$this->view->ID_LV_MC = $ID_LV_MC;
		$this->view->linhvuc = new linhvucmotcuaModel();
		$user = Zend_Registry::get('auth')->getIdentity();
		$this->view->linhvuc = $this->view->linhvuc->SelectAllByUID($user->ID_U);
	}
	
	function reportviewAction(){
		$this->_helper->layout->DisableLayout();
		$params = $this->_request->getParams();				 
		//var_dump($params); exit;
		//echo date("Y-m-d 00:00:00"); exit;	
		$fromdate = $params['fromdate'];		
	    $todate = $params['todate'];
		$sel_tinhtrang = $params['sel_tinhtrang'];
		$sel_lhss = $params['sel_lhs'];		
		$this->view->sel_lhss = $params['sel_lhs'];
		if($params['CHOICEALL_LOAI'] == 1)
			$this->view->sel_lhss = TiepnhanhosomotcuaModel::getIDLOAIByLV($params["ID_LV_MC"]);
		//echo $this->view->sel_lhss; exit;
		//
		$this->view->trangthai = $params['sel_tinhtrang'];
		$this->view->fromdate =  $params['fromdate'];
		$this->view->todate = $params['todate']; 
		$this->view->fromdategt = $params['fromdategt']; 
		$this->view->todategt = $params['todategt'];
		//$this->view->data = TiepnhanhosomotcuaModel::getReportData($fromdate,$todate,$sel_tinhtrang,$sel_lhss);
		//$this->view->data = TiepnhanhosomotcuaModel::getReportDataTiepnhantrongngay($sel_tinhtrang,$sel_lhss);
		
		if($params['baocao_tiepnhan']==1){
			$this->view->data = TiepnhanhosomotcuaModel::getReportDataTiepnhantrongngay($params["ID_LV_MC"],$params["ngaybaocao"]);
			$this->renderScript("tiepnhanhosomotcua/report_tiepnhantrongngay.phtml");
		}
		if($params['baocao_trahoso']==1){
			$this->view->data = TiepnhanhosomotcuaModel::getReportDataTratrongngay($params["ID_LV_MC"],$params["ngaybaocao"]);
			//var_dump($this->view->data);exit;
			$this->renderScript("tiepnhanhosomotcua/report_trahoso.phtml");
		}
		
		if($params['baocao_thuong']==1){
			if($params['CHOICEALL_LOAI'] == 1){
				
				$this->view->data = TiepnhanhosomotcuaModel::reportHosoAll($this->view->trangthai,$fromdate,$todate,$this->view->fromdategt,$this->view->todategt);
				$this->renderScript("tiepnhanhosomotcua/reportall.phtml");
			}
			//$this->view->data = TiepnhanhosomotcuaModel::getReportData($fromdate,$todate,$sel_tinhtrang,$sel_lhss);
		}
		//getReportDataTratrongngay
	   // var_dump($this->view->data);exit;
	}

	function reportviewexcelAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();	
		$this->view->params = $params;
		$config = Zend_Registry::get('config');
		$this->view->config = $config;
		$year = QLVBDHCommon::getYear();
		$this->view->year=$year;
	    if($params['h_isexel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=baocaoHSMC.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		
		$this->_helper->layout->DisableLayout();
		$params = $this->_request->getParams();		
		//var_dump($params); exit;
		//echo date("Y-m-d 00:00:00"); exit;	
		$fromdate = $params['fromdate'];
	    $todate = $params['todate'];
		$sel_tinhtrang = $params['sel_tinhtrang'];
		$sel_lhss = $params['sel_lhs'];
		//var_dump($sel_lhss);
		$this->view->sel_lhss = $params['sel_lhs'];
		if($params['CHOICEALL_LOAI'] == 1)
			$this->view->sel_lhss = TiepnhanhosomotcuaModel::getIDLOAIByLV($params["ID_LV_MC"]);
		//echo $this->view->sel_lhss; exit;
		//
		$this->view->trangthai = $params['sel_tinhtrang'];
		$this->view->fromdate =  $params['fromdate'];
		$this->view->todate = $params['todate']; 
		$this->view->fromdategt = $params['fromdategt']; 
		$this->view->todategt = $params['todategt'];
		$this->view->trangthai= $params['sel_tinhtrang'];
		$this->view->ID_LV_MC = $params["ID_LV_MC"];
		$this->view->UBND=$params['ubnd'];
		$this->view->PHONG=$params['phong'];
		$this->view->PHUONG = -1;
		if(isset($params['phuong'])){
			$this->view->PHUONG = $params['phuong'];
		};
		if($fromdate == "")
			$fromdate = "1/1";
		
		if($todate == "")
			$todate = "31/12";	
	    $time=$params['time'];
	    
		if($time==""){
		$this->view->thu="TỪ NGÀY"." ".$fromdate." "."ĐẾN NGÀY"." ".$todate;
		}else{
			$this->view->thu=$time;}
		//$this->view->data = TiepnhanhosomotcuaModel::getReportData($fromdate,$todate,$sel_tinhtrang,$sel_lhss);
		//$this->view->data = TiepnhanhosomotcuaModel::getReportDataTiepnhantrongngay($sel_tinhtrang,$sel_lhss);
		
		if($params['baocao_tiepnhan']==1){
			$this->view->data = TiepnhanhosomotcuaModel::getReportDataTiepnhantrongngay($params["ID_LV_MC"],$params["ngaybaocao"]);
			$this->renderScript("tiepnhanhosomotcua/report_tiepnhantrongngayexcel.phtml");
		}
		if($params['baocao_trahoso']==1){
			$this->view->data = TiepnhanhosomotcuaModel::getReportDataTratrongngay($params["ID_LV_MC"],$params["ngaybaocao"]);
			//var_dump();
			$this->renderScript("tiepnhanhosomotcua/report_trahosoexcel.phtml");
		}
		
		if($params['baocao_thuong']==1){
			//$this->view->data = TiepnhanhosomotcuaModel::getReportData($fromdate,$todate,$sel_tinhtrang,$sel_lhss);
		}
		
	}
	function reporthosoAction(){

	}
}
?>