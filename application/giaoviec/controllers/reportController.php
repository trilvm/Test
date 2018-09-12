<?php
require_once "giaoviec/models/giaoviecservice.php";
class Giaoviec_ReportController extends Zend_Controller_Action {
	public function baocaogiaoviecAction(){
		$param = $this->getRequest()->getParams();

		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
		$this->view->title="Thống kê giao nhiệm vụ";
		$token = $giaoviecservice->login($madonvi,md5($password),"");
		$this->view->dataDonVi = json_decode($giaoviecservice->getListDonVi($token))->data;
	}

	public function baocaogiaoviecviewAction(){
		$this->_helper->layout->disableLayout();

		$param = $this->getRequest()->getParams();
		if(!isset($param["fromdate"]) ||$param["fromdate"] =="") {
                    $param["fromdate"] = date('01/m/Y');
                }
                if(!isset($param["todate"]) || $param["todate"] ==""){
                    $param["todate"] = date('t/m/Y');
                }
		$this->view->title = "Quản lý theo dõi, đôn độc thực hiện nhiệm vụ đã giao";
		$this->view->subtitle = "";
                $giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $this->view->dataDonVi = json_decode($giaoviecservice->getListDonVi($token))->data;
                $this->view->fromdate = $param["fromdate"];
                $this->view->todate = $param["todate"];
                $this->view->month = $param["todate"];
		if(isset($param["fromdate"]) && isset($param["todate"]) && $param["fromdate"]!="" && $param["todate"] !=""){
			$param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
                        $param["tungay"] = strtotime($param["fromdate"]);
                        $param["fromdate"] = date('Y-m-d',$param["tungay"]);
                        $param["month"] = date('m',$param["tungay"]);
			$param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
                        $param["todate"] = strtotime($param["todate"]);
                        $param["todate"] = strtotime("+1 day",$param["todate"]);
                        $param["todate"] = date('Y-m-d',$param["todate"]);
                       
			$donvilist = "-1,".implode(",",$param["sel_donvi"]);
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"]))->data;
		}
               $this->view->month = $param["month"];

		$is_in = $param['is_in'];		
                if($is_in){ 
                    if($param["h_isexel"]==1){
                        $this->view->is_in = 0;
                        header("Content-Type: application/vnd.ms-excel; name='excel'");
                        header("Content-Disposition: attachment; filename=baocaotkcongviec.xls;");
                        header("Pragma: no-cache");
                        header("Expires: 0");
                    }else{$this->view->is_in = 1;}                     
                    $this->renderScript("report/baocaogiaoviecin.phtml");
                }
	}
	public function baocaogiaoviecdetailAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->getRequest()->getParams();

		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");

		$param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
		$param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
                $param["todate"] = strtotime($param["todate"]);
                $param["todate"] = strtotime("+1 day",$param["todate"]);
                $param["todate"] = date('Y-m-d',$param["todate"]);
		$donvilist = "-1,".implode(",",$param["sel_donvi"]);
             
		switch($param["REPORTTYPE"]){
			case 1:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail1.phtml");
				break;
			case 2:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
			case 3:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
			case 4:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
			case 5:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
			case 6:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
			case 7:
				$this->view->dataReport = json_decode($giaoviecservice->chiTietGiaoViec($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("report/baocaogiaoviecdetail2.phtml");
				break;
		}
	}
}