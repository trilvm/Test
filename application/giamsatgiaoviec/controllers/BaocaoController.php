<?php
require_once 'Zend/Controller/Action.php';
require_once 'giamsatgiaoviec/models/LoaiCongViecGiaoViecModel.php';
require_once 'giamsatgiaoviec/models/BaoCaoModel.php';

class Giamsatgiaoviec_BaoCaoController extends Zend_Controller_Action{
    
    function thongkeAction(){
        $this->view->title = "Thống kê tổng hợp giao việc";
    }
    function thongkeviewAction(){
        global $config;
        $this->view->config = $config;        
        $year = QLVBDHCommon::getYear();
        $this->view->year = $year;
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
	$fromdate = $params['fromdate'];	
        if($fromdate == ""){
            $params['fromdate'] = "1/1";
            $fromdate = $params['fromdate'];
        }
        $todate = $params['todate'];
        if($todate == ""){
            $params['todate'] = "31/12";
            $todate = $params['todate'];
        }
        $this->view->thu="Từ ngày ".$fromdate." đến ngày ".$todate;        
        $this->view->params=$params;
        
        $is_in = $params['is_in'];		
        if($is_in){ 
            if($params["h_isexel"]==1){		
                header("Content-Type: application/vnd.ms-excel; name='excel'");
                header("Content-Disposition: attachment; filename=baocaotkcongviec.xls;");
                header("Pragma: no-cache");
                header("Expires: 0");
            }                     
            $this->renderScript("baocao/thongkeviewin.phtml");
        }        
    }
    
    function baocaochitietAction(){
        $this->view->title = "Báo cáo chi tiết giao việc";       
    }
    
    function baocaochitietviewAction(){
        global $config;
        $this->view->config = $config;
        
        $year = QLVBDHCommon::getYear();
        $this->view->year = $year;
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
	$fromdate = $params['fromdate'];	
        if($fromdate == ""){
            $params['fromdate'] = "1/1";
            $fromdate = $params['fromdate'];
        }
        $todate = $params['todate'];
        if($todate == ""){
            $params['todate'] = "31/12";
            $todate = $params['todate'];
        }
        $this->view->thu="Từ ngày ".$fromdate." đến ngày ".$todate;        
	$this->view->params=$params;
        
        $is_in = $params['is_in'];		
        if($is_in){ 
            if($params["h_isexel"]==1){		
                header("Content-Type: application/vnd.ms-excel; name='excel'");
                header("Content-Disposition: attachment; filename=baocaoctcongviec.xls;");
                header("Pragma: no-cache");
                header("Expires: 0");
            }                     
            $this->renderScript("baocao/baocaochitietviewin.phtml");
        }        
    }
    
    function baocaochitietgiaoviecnoiboAction(){
        $this->view->title = "Báo cáo quản trị việc nội bộ";  
        $loaiCongViecGiaoViecModel = new LoaiCongViecGiaoViecModel();
        $this->view->dataloaicongviec = $loaiCongViecGiaoViecModel->getLoaiCongViecGiao();        
    }
    
    function baocaochitietgiaoviecnoiboviewAction(){
        global $config;
        $this->view->config = $config;
        global $auth;
        $user =$auth->getIdentity();
        $year = QLVBDHCommon::getYear();
        $this->view->year = $year;
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $loaiCongViecGiaoViecModel = new LoaiCongViecGiaoViecModel();
        $this->view->dataloaicongviec = $loaiCongViecGiaoViecModel->getLoaiCongViecGiao(); 
        $this->view->sel_pb=(int)$user->ID_DEP;
	$fromdate = $params['fromdate'];	
        if($fromdate == ""){
            $params['fromdate'] = "1/1";
            $fromdate = $params['fromdate'];
        }
        $todate = $params['todate'];
        if($todate == ""){
            $params['todate'] = "31/12";
            $todate = $params['todate'];
        }
        $this->view->thu="Từ ngày ".$fromdate." đến ngày ".$todate;        
	$this->view->params=$params;
        
        $is_in = $params['is_in'];		
        if($is_in){ 
            if($params["h_isexel"]==1){		
                header("Content-Type: application/vnd.ms-excel; name='excel'");
                header("Content-Disposition: attachment; filename=baocaoctcongviec.xls;");
                header("Pragma: no-cache");
                header("Expires: 0");
            }                     
            $this->renderScript("baocao/baocaochitietgiaoviecnoiboviewin.phtml");
        }        
    }
}
