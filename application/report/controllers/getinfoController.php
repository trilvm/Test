<?php

require_once 'Zend/Controller/Action.php';
require_once 'report/models/ThongkevanbandiModel.php';
require_once 'qtht/models/CoQuanModel.php';

class Report_getinfoController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->title = "Báo cáo số lượng";
    }
    function reportviewAction(){	
        $this->_helper->layout->disableLayout();
        $param = $this->_request->getParams();
        $fromdate = $param['fromdate'];
        $todate = $param['todate'];		
        $id_CQ = $param['ID_CQ'];
        $is_lienthong = $param['IS_LIENTHONG'];
        if($is_lienthong == NULL){
           $is_lienthong = 0;
        }
        $CQmodel = new CoQuanModel();
        $link = $CQmodel->getLinkById($id_CQ);
        $cntlink = count($link);
        $arrayResponseALL =  array();
        for($i=0;$i<$cntlink;$i++){
            $linkinfo = $link[$i]['LINKINFO'] . '?fromdate=' . $fromdate . '&todate=' . $todate. '&is_lienthong=' . $is_lienthong;
            $response = file_get_contents($linkinfo);
            $arrayResponse =  json_decode($response);
            array_push($arrayResponse,$link[$i]['ID_CQ']);
            array_push($arrayResponseALL,$arrayResponse);
            }
        $this->view->data = $arrayResponseALL;
    }
    function reportviewexcelAction(){
        $this->_helper->layout->disableLayout();
        $param = $this->_request->getParams();
        if($param['h_isexel'] ==1){
            header("Content-Type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: attachment; filename=baocaovanbanden.xls;");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        $fromdate = $param['fromdate'];
        $todate = $param['todate'];		
        $id_CQ = $param['ID_CQ'];
        $is_lienthong = $param['IS_LIENTHONG'];
        $this->view->lienthong = 1;
        if($is_lienthong == NULL){
           $is_lienthong = 0;
           $this->view->lienthong = 0;
        }
        if($fromdate == "")
                $fromdate = "1/1";
        if($todate == "")
                $todate = "31/12";
        $this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
        $CQmodel = new CoQuanModel();
        $link = $CQmodel->getLinkById($id_CQ);
        $cntlink = count($link);
        $arrayResponseALL =  array();
        for($i=0;$i<$cntlink;$i++){
            $linkinfo = $link[$i]['LINKINFO'] . '?fromdate=' . $fromdate . '&todate=' . $todate. '&is_lienthong=' . $is_lienthong;
            $response = file_get_contents($linkinfo);
            $arrayResponse =  json_decode($response);
            array_push($arrayResponse,$link[$i]['ID_CQ']);
            array_push($arrayResponseALL,$arrayResponse);
            }
        $this->view->data = $arrayResponseALL;
	}
}
