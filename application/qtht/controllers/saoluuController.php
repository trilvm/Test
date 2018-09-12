<?php
require_once 'qtht/models/saoluuModel.php';
require_once 'nusoap/nusoap.php';
class Qtht_SaoluuController extends Zend_Controller_Action{
    var $db;
    function init(){
        $this->db = new Qtht_SaoluuModel();
    }
    function listAction(){
       
       $params= $this->_getAllParams();
              
       $limit = $params['limit'];
       if($limit == '') $limit = 10;//hiển thị
       
       $offset = $params['limit'];
       if($offset== '') $offset = 0;//giới hạn bắt đầu danh sách
       
       $page = $params['page'];
       if($page== '') $page = 1; //số trang
       
       //tim kiem
       if($params['fromsearch']=='') {
           $fromsearch='';
       }
       else{
       $from = $params['fromsearch'];
       $from = str_replace('/', '-', $from);
       $fromsearch = date('Y-m-d H:i:s',  strtotime($from)); //ngày tìm kiếm từ
       }
       
       if($params['tosearch']=='') {
           $tosearch='';
       }
       else{
            $to = $params['tosearch'];
            $to = str_replace('/', '-', $to);
            $tosearch = date('Y-m-d H:i:s',  strtotime($to)); //ngày tìm kiếm đến
       }
       $checkbox = $params['checkbox'];
       
       if($checkbox!=''){
           $condition.=' and IS_OK = 1 ';
       }
       $condition .= $this->searchFromToDate($fromsearch, $tosearch);
       
       if(!empty($condition)) $page = 1;// trả về trang 1 nến không có điều kiện
       
       $this->view->title = 'Quản lý sao lưu';
       //$this->view->data = $this->db->getData($limit,$page,$condition);
       $this->view->data = $this->db->getData($limit,$page,$condition);
       //echo '<pre>';
       //print_r($this->db->getData($limit,$page,$condition));exit;
       
       $this->view->limit = $limit;
       $this->view->page = $page;
       $this->view->fromsearch = $fromsearch;
       $this->view->tosearch = $tosearch;
       $this->view->checkbox = $checkbox;
       $this->view->showPage = QLVBDHCommon::Paginator($this->db->count($condition),5,$limit,"frm",$page) ;
       $this->view->checkMiss = $this->checkMiss();
       
    }
    
   
    function searchFromToDate($from,$to){
        if($from == '' && $to == '') return '';
        if($from == '' && !empty($to)) return "and BACKUP_DATE < '".date('Y-m-d',strtotime($to))." 23:59:59'";
        if(!empty($from) && $to == '') return "and BACKUP_DATE > '".date('Y-m-d', strtotime($from))." 00:00:00'";
        if($form > $to) return '';
        else return "and BACKUP_DATE BETWEEN '".date('Y-m-d', strtotime($from))." 00:00:00' AND '".date('Y-m-d',strtotime($to))." 23:59:59'";
    }
    
    
    
    function restoreRequest($BACKUP_IDS){
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($username).'~'.base64_encode($BACKUP_IDS);
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'SENDRESTOREREQUEST',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    
    function missAction(){
        //echo 'dsad';exit;
       $params= $this->_getAllParams();
       
       $dismiss = $params['dismiss'];
       
       $limit = $params['limit'];
       if($limit == '') $limit = 10;//hiển thị
       
       $offset = $params['limit'];
       if($offset== '') $offset = 0;//giới hạn bắt đầu danh sách
       
       $page = $params['page'];
       if($page== '') $page = 1; //số trang
       
       //tim kiem
       if($params['fromsearch']=='') {
           $fromsearch='';
       }
       else{
       $from = $params['fromsearch'];
       $from = str_replace('/', '-', $from);
       $fromsearch = date('Y-m-d H:i:s',  strtotime($from)); //ngày tìm kiếm từ
       }
       
       if($params['tosearch']=='') {
           $tosearch='';
       }
       else{
            $to = $params['tosearch'];
            $to = str_replace('/', '-', $to);
            $tosearch = date('Y-m-d H:i:s',  strtotime($to)); //ngày tìm kiếm đến
       }
       
       $condition = $this->searchFromToDateMiss($fromsearch, $tosearch);
       if(!empty($condition)) $page = 1;// trả về trang 1 nếu không có điều kiện
       if(!empty($dismiss)) $this->dismiss($dismiss);//cập nhật dismiss
       $this->view->title = 'Quản lý yêu cầu sao lưu';
       $this->view->data = $this->getDataMiss($limit,$page,$condition);
       $this->view->limit = $limit;
       $this->view->page = $page;
       $this->view->fromsearch = $fromsearch;
       $this->view->tosearch = $tosearch;
       
       $this->view->showPage = QLVBDHCommon::Paginator($this->countMiss($condition),5,$limit,"frm",$page) ;
       
       
    }
    
    function getDataMiss($limit,$page,$condition){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($condition).'~'.base64_encode($page).'~'.base64_encode($limit);
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'ReceiveBackupRequestInfo',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       $result = ServicesCommon::DeseriallizeToArray(base64_decode($result));
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    function countMiss($condition){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($condition);
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'CountBackupRequestInfo',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       
       return base64_decode($result);
       }  catch (Exception $ex){
            return 0;
       }
    }
    function checkMiss(){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = NULL;
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'CheckMissBackupRequest',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       
       return base64_decode($result);
       }  catch (Exception $ex){
            return 0;
       }
    }
    function dismiss($id){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($id);
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'DismissBackupRequest',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    function searchFromToDateMiss($from,$to){
        if($from == '' && $to == '') return '';
        if($from == '' && !empty($to)) return "and REQUEST_DATE < '".date('Y-m-d',strtotime($to))." 23:59:59'";
        if(!empty($from) && $to == '') return "and REQUEST_DATE > '".date('Y-m-d', strtotime($from))." 00:00:00'";
        if($form > $to) return '';
        else return "and REQUEST_DATE BETWEEN '".date('Y-m-d', strtotime($from))." 00:00:00' AND '".date('Y-m-d',strtotime($to))." 23:59:59'";
    }
    
}
