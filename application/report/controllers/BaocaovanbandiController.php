<?php 
require_once ('Zend/Controller/Action.php');
require_once ('report/models/vanbandireportModel.php');
require_once ('report/models/BaocaovanbandireportModel.php');
require_once 'qtht/models/LoaiVanBanModel.php';
class Report_BaocaovanbandiController extends Zend_Controller_Action{
	function init(){
	}
	
	function indexAction(){
		$this->view->title = "Báo cáo";
		$this->view->subtitle = "Văn bản đi";
	}
	
	function reportviewAction(){
                
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		//var_dump($params);exit;
		$id_svb = $params['sel_sovanban'];
		$sel_pb=$params['ID_DEP'];
		$id_u=$params['ID_U'];
		$fromdate = $params['fromdate'];
		$todate = $params['todate'];
		$is_lienThong = (Int)$params['IS_LIENTHONG'];		
		$this->view->ngaybh=$params['ngaybh'];
		$this->view->sodi=$params['sodi'];
		$this->view->sokyhieu=$params['sokyhieu'];
		$this->view->trichyeund=$params['trichyeund'];
		$this->view->soban=$params['soban'];
		$this->view->noinhan=$params['noinhan'];
		$this->view->luuhoso=$params['luuhoso'];
		$this->view->nguoinbl=$params['nguoinbl'];
		$this->view->domat=$params['domat'];
		$this->view->dokhan=$params['dokhan'];
                /*phuongpt*/
		$id_lvbs = $params['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
                $sorttype = $params['SORTTYPE'];
                
		$is_in = $params['is_in'];
		$this->view->data = BaocaovanbandireportModel::getReportData($fromdate,$todate,$id_svb,$sel_pb,$id_u,$is_lienThong, $id_lvbs, $sorttype);
		
                
                if($is_in){
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("BaoCaoVanBanDi/reportview_in.phtml");
		}
	}
	
	function reportviewcvtltAction(){

			$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		//var_dump($params);exit;
		 if($params['h_isexel'] == 1){
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=Sodangkyvanbandi.xls;");
		header("Pragma: no-cache");
		header("Expires: 0");
		 }
		$id_svb = $params['sel_sovanban'];
		$sel_pb=$params['ID_DEP'];
		$id_u=$params['ID_U'];
		$fromdate = $params['fromdate'];
		$todate = $params['todate'];
		$is_lienThong = (Int)$params['IS_LIENTHONG'];
		if($fromdate == "")
				$fromdate = "1/1";			
			if($todate == "")
				$todate = "31/12";
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$this->view->ngaybh=$params['ngaybh'];
		$this->view->sodi=$params['sodi'];
		$this->view->sokyhieu=$params['sokyhieu'];
		$this->view->trichyeund=$params['trichyeund'];
		$this->view->soban=$params['soban'];
		$this->view->noinhan=$params['noinhan'];
		$this->view->luuhoso=$params['luuhoso'];
		$this->view->nguoinbl=$params['nguoinbl'];
		$this->view->domat=$params['domat'];
		$this->view->dokhan=$params['dokhan'];
		$id_lvbs = $params['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
        $sorttype = $params['SORTTYPE'];
		$is_in = $params['is_in'];
		$this->view->data = BaocaovanbandireportModel::getReportData($fromdate,$todate,$id_svb,$sel_pb,$id_u,$is_lienThong, $id_lvbs, $sorttype);
		if($is_in){
			global $config;
			$this->view->config = $config;
			if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
			$this->renderScript("BaoCaoVanBanDi/reportviewcvtlt_in.phtml");
		}
	}

	function reportviewexcelAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		//var_dump($params);exit;
		 if($params['h_isexel'] == 1){
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=baocaovanbandi.xls;");
		header("Pragma: no-cache");
		header("Expires: 0");
		 }
		$id_svb = $params['sel_sovanban'];
		$sel_pb=$params['ID_DEP'];
		$id_u=$params['ID_U'];
		$fromdate = $params['fromdate'];
		$todate = $params['todate'];
		$is_lienThong = (Int)$params['IS_LIENTHONG'];
		if($fromdate == "")
				$fromdate = "1/1";
		if($todate == "")
			$todate = "31/12";
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$this->view->ngaybh=$params['ngaybh'];
		$this->view->sodi=$params['sodi'];
		$this->view->sokyhieu=$params['sokyhieu'];
		$this->view->trichyeund=$params['trichyeund'];
		$this->view->soban=$params['soban'];
		$this->view->noinhan=$params['noinhan'];
		$this->view->luuhoso=$params['luuhoso'];
		$this->view->nguoinbl=$params['nguoinbl'];
		$this->view->domat=$params['domat'];
		$this->view->dokhan=$params['dokhan'];
		$this->view->xuat=(int)$params["xuat"];
		$id_lvbs = $params['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
		$this->view->countcolspan= count($params['ngaybh']) + count($params['sodi'])+count($params['sokyhieu'])+count($params['trichyeund'])+count($params['soban'])+count($params['noinhan'])+ count($params['luuhoso']) +count($params['nguoisoan']) +count($params['domat'])+count($params['dokhan']);

		//$this->view->data = vanbandireportModel::getReportData($fromdate,$todate,$id_svb);
                $sorttype = $params['SORTTYPE'];
		$this->view->data = BaocaovanbandireportModel::getReportData($fromdate,$todate,$id_svb,$sel_pb,$id_u,$is_lienThong, $id_lvbs, $sorttype);
	}
	function reportviewexcelcvtltAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$config = Zend_Registry::get("config");
			$this->view->config = $config;
		//var_dump($params);exit;
		 if($params['h_isexel'] == 1){
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=Sodangkyvanbandi.xls;");
		header("Pragma: no-cache");
		header("Expires: 0");
		 }
		$id_svb = $params['sel_sovanban'];
		$sel_pb=$params['ID_DEP'];
		$id_u=$params['ID_U'];
		$fromdate = $params['fromdate'];
		$todate = $params['todate'];
		$is_lienThong = (Int)$params['IS_LIENTHONG'];
		if($fromdate == "")
				$fromdate = "1/1";			
			if($todate == "")
				$todate = "31/12";
		$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
		$this->view->ngaybh=$params['ngaybh'];
		$this->view->sodi=$params['sodi'];
		$this->view->sokyhieu=$params['sokyhieu'];
		$this->view->trichyeund=$params['trichyeund'];
		$this->view->soban=$params['soban'];
		$this->view->noinhan=$params['noinhan'];
		$this->view->luuhoso=$params['luuhoso'];
		$this->view->nguoinbl=$params['nguoinbl'];
		$this->view->domat=$params['domat'];
		$this->view->dokhan=$params['dokhan'];
		$this->view->xuat=(int)$params["xuat"];
		$this->view->countcolspan= count($params['ngaybh']) + count($params['sodi'])+count($params['sokyhieu'])+count($params['trichyeund'])+count($params['soban'])+count($params['noinhan'])+ count($params['luuhoso']) +count($params['nguoisoan']) +count($params['domat'])+count($params['dokhan']);

		//$this->view->data = vanbandireportModel::getReportData($fromdate,$todate,$id_svb);
		$id_lvbs = $params['sel_lvb'];
		$this->view->id_lvbs = $id_lvbs;
        $sorttype = $params['SORTTYPE'];
		$this->view->data = BaocaovanbandireportModel::getReportData($fromdate,$todate,$id_svb,$sel_pb,$id_u,$is_lienThong, $id_lvbs, $sorttype);
	}
}
?>