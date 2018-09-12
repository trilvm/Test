<?php

require_once 'Zend/Controller/Action.php';
require_once 'report/models/ThongkevanbandiModel.php';

class Report_ThongkevanbandiController extends Zend_Controller_Action {

    public function indextkAction() {
        $this->view->title = "Thống kê văn bản đi";
        $params = $this->_request->getParams();
        $this->view->year=QLVBDHCommon::getYear();
        $this->view->fromdate = $params["fromdate"];
        $this->view->todate = $params["todate"];
        $this->view->sel_pb = $params["sel_pb"];
    }

    public function indextkviewAction() {
        
        global $auth;
        $config = Zend_Registry::get('config');
        $this->view->config = $config;
        $this->view->user = $auth->getIdentity();
        $this->_helper->layout->disableLayout();
        $this->view->title = "Thống kê văn bản đi"; 
        $params = $this->_request->getParams();
        $this->view->param = $params;
        $fromdate = $params["fromdate"];
        $todate = $params["todate"];    
        $year=$params["year"];
        if ($fromdate == ""){
            $fromdate = "1/1";
        }
        if ($todate == ""){
            $todate = "31/12";
        }
        if((int)$year<2013 || (int)$year>QLVBDHCommon::getYear()){
            $year=QLVBDHCommon::getYear();
        }
        $this->view->fromdate = $fromdate;
        $this->view->todate = $todate;
        $this->view->year = $year;
        $this->view->sel_pb = $params["sel_pb"];
        $this->view->sel_svb = $params["sel_svb"];
        $this->view->sel_lvb = $params["sel_lvb"];
        $this->view->thongke = $params["thongke"];
        $this->view->sel_dep_canhan = $params["sel_dep_canhan"];
        $this->view->sel_canhan = $params["sel_canhan"];
        $this->view->thu = "Từ ngày " . $fromdate . "/" .$year. " đến ngày " . $todate.'/'.$year;
        if((int)$params["thongke"]==0){
            $this->view->data = ThongkevanbandiModel::getPhongBan($params["sel_pb"]);
        }else{
            $this->view->data = ThongkevanbandiModel::getCaNhan($params["sel_dep_canhan"],$params["sel_canhan"]);
        }        
        $is_in = $params['is_in'];
        if ($is_in) {
            $this->renderScript("thongkevanbandi/indextkviewin.phtml");
        }
    }

    public function indextkviewexcelAction() {
        
        global $auth;
        $config = Zend_Registry::get('config');
        $this->view->config = $config;
        $this->view->user = $auth->getIdentity();
        $this->_helper->layout->disableLayout();
        $this->view->title = "Thống kê văn bản đi"; 
        $params = $this->_request->getParams();
        $this->view->param = $params;
        $fromdate = $params["fromdate"];
        $todate = $params["todate"];    
        $year=$params["year"];
        if ($fromdate == ""){
            $fromdate = "1/1";
        }
        if ($todate == ""){
            $todate = "31/12";
        }
        if((int)$year<2013 || (int)$year>QLVBDHCommon::getYear()){
            $year=QLVBDHCommon::getYear();
        }
        $this->view->fromdate = $fromdate;
        $this->view->todate = $todate;
        $this->view->year = $year;
        $this->view->sel_pb = $params["sel_pb"];
        $this->view->sel_svb = $params["sel_svb"];
        $this->view->sel_lvb = $params["sel_lvb"];
        $this->view->thongke = $params["thongke"];
        $this->view->sel_dep_canhan = $params["sel_dep_canhan"];
        $this->view->sel_canhan = $params["sel_canhan"];
        $this->view->thu = "Từ ngày " . $fromdate . "/" .$year. " đến ngày " . $todate.'/'.$year;
        if((int)$params["thongke"]==0){
            $this->view->data = ThongkevanbandiModel::getPhongBan($params["sel_pb"]);
        }else{
            $this->view->data = ThongkevanbandiModel::getCaNhan($params["sel_dep_canhan"],$params["sel_canhan"]);
        }       
        
        if ($this->view->param["h_isexel"] == 1) {
            header("Content-Type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: attachment; filename=thongkevanbandi.xls;");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
    }
}
