<?php
require_once ('Zend/Controller/Action.php');
require_once 'qlcuochop/models/baocaocuochopModel.php';

class qlcuochop_baocaocuochopController extends Zend_Controller_Action {

    public function init(){

        $this->getParams=$this->getRequest()->getParams();
        $this->view->title = "Báo cáo";
    }

    public function indexAction()
    {
        $this->view->subtitle  ="Báo cáo cuộc họp";
    }

    public function reportviewAction(){
        $this->_helper->layout->disableLayout();
        $fromdate = $this->getParams['fromdate'];
        $todate = $this->getParams['todate'];

        $this->view->data = baocaocuochopModel::getReportData($fromdate, $todate);
//        var_dump($this->view->data);exit;
        $is_in = $this->getParams['is_in'];
        if($is_in){
                global $config;
                $config = Zend_Registry::get("config");
                $this->view->config = $config;
                if($fromdate == "")
                        $fromdate = "01/01/".QLVBDHCommon::getYear();
                if($todate == "")
                        $todate = "31/12/".QLVBDHCommon::getYear();
                $this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
                $this->renderScript("baocaocuochop/reportview_in.phtml");
        }
    }

    function reportviewexcelAction() {
        $this->_helper->layout->disableLayout();
        $param = $this->_request->getParams();
        global $config;
        $config = Zend_Registry::get("config");
        $this->view->config = $config;
        if ($param['h_isexel'] == 1) { //echo $param['h_isexel'];exit;
            header("Content-Type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: attachment; filename=baocaocuochop.xls;");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        $fromdate = $param['fromdate'];
        $todate = $param['todate'];
        if ($fromdate == "")
            $fromdate = "01/01/".QLVBDHCommon::getYear();

        if ($todate == "")
            $todate = "31/12/".QLVBDHCommon::getYear();
        $this->view->thu = "TỪ NGÀY" . " " . $fromdate . " " . "ĐẾN NGÀY" . " " . $todate;
        $this->view->data = baocaocuochopModel::getReportData($fromdate, $todate);
    }

    function indextkeAction(){
        $param = $this->_request->getParams();
        //var_dump($param["fromdate"]);exit;
        $this->view->todate = $param["todate"];
        $this->view->fromdate = $param["fromdate"];

        if($param["fromdate"] ==""){
         $this->view->fromdatex = "01/01/".date('Y');
        }else{
                $this->view->fromdatex = $param["fromdate"];
                }
        if($param["todate"] ==""){
            $this->view->todatex = "31/12/".date('Y');
        }else{
                $this->view->todatex = $param["todate"];
                }
        $this->view->param = $param;	//var_dump($param);exit;
        $this->view->id_lhs=$param["sel_lhs"];
        $this->view->hienthi = $param['in'];
        //var_dump($this->view->id_lhs);exit;
        $this->view->title="Thống kê cuộc họp";
        $this->view->subtitle="Tình hình công việc";
    }

    function xuatwebtkeAction(){
        global $auth;
        $user = $auth->getIdentity();
        $config = Zend_Registry::get('config');
        $this->view->config = $config;
        $this->_helper->layout->disableLayout();
        $param = $this->_request->getParams();
        //var_dump($param);exit;
        $this->view->todate = $param["todate"];
        $this->view->fromdate = $param["fromdate"];
        if($param["fromdate"] ==""){
         $this->view->fromdate = "01/01/".date('Y');
        }else{
                $this->view->fromdate = $param["fromdate"];
                }
        if($param["todate"] ==""){
            $this->view->todate = "31/12/".date('Y');
        }else{
                $this->view->todate = $param["todate"];
                }
        
        $this->view->param = $param;		//var_dump($param);exit;
        $this->view->id_pb=$param["sel_pb"];
        $this->view->id_lhs=$param["sel_lhs"];
        $this->view->thu="Từ ngày"." ".$this->view->fromdate." "."đến ngày"." ".$this->view->todate;
        //var_dump($this->view->id_lhs);exit;
        $this->view->title="Thống kê cuộc họp";
        $this->view->subtitle="Tình hình công việc";
    }

    function xuatexceltkeAction() {
        $this->_helper->layout->disableLayout();
        $param = $this->_request->getParams();
        global $config;
        $config = Zend_Registry::get("config");
        $this->view->config = $config;
        if ($param['h_isexel'] == 1) { //echo $param['h_isexel'];exit;
            header("Content-Type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: attachment; filename=thongkecuochop.xls;");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        $fromdate = $param['fromdate'];
        $todate = $param['todate'];
        if ($fromdate == "")
            $fromdate = "01/01/".QLVBDHCommon::getYear();

        if ($todate == "")
            $todate = "31/12/".QLVBDHCommon::getYear();
        $this->view->thu = "TỪ NGÀY" . " " . $fromdate . " " . "ĐẾN NGÀY" . " " . $todate;
        $this->view->data1 = baocaocuochopModel::CountgetReportData($fromdate, $todate, 2);
        $this->view->data2 = baocaocuochopModel::CountgetReportData($fromdate, $todate, 1);
        $this->view->data3 = baocaocuochopModel::CountgetReportData($fromdate, $todate, 3);
        $this->view->data4 = baocaocuochopModel::CountgetReportData($fromdate, $todate, 4);
    }
}
?>
