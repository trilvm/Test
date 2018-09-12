<?php


require_once 'nusoap/nusoap.php';
class Qtht_PhuchoiController extends Zend_Controller_Action{
    
    function listAction(){
        //echo 'dsad';exit;
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
           $condition.=' and RESTORED = 0 ';
       }
       $condition .= $this->searchFromToDate($fromsearch, $tosearch);
       
       if(!empty($condition)) $page = 1;// trả về trang 1 nến không có điều kiện

       $this->view->title = 'Quản lý phục hồi';
       $this->view->data = $this->getData($limit,$page,$condition);
       $this->view->limit = $limit;
       $this->view->page = $page;
       $this->view->fromsearch = $fromsearch;
       $this->view->tosearch = $tosearch;
       //echo $this->count($condition);
       $this->view->checkbox = $checkbox;
       $this->view->showPage = QLVBDHCommon::Paginator($this->count($condition),5,$limit,"frm",$page) ;
       
       QLVBDHButton::AddButton('Thêm yêu cầu mới', '/qtht/phuchoi/add', 'AddNewButton', 'AddNewButtonClick();');
    }
    
    function getData($limit,$page,$condition){
        
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
            'service_name' => 'GetRestoreInfo',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       $result = ServicesCommon::DeseriallizeToArray(base64_decode($result));
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    function count($condition){
        
        
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
            'service_name' => 'CountRestoreInfo',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       
       return base64_decode($result);
       }  catch (Exception $ex){
            return 0;
       }
    }
    
    function searchFromToDate($from,$to){
        if($from == '' && $to == '') return '';
        if($from == '' && !empty($to)) return "and REQUEST_DATE < '".date('Y-m-d',strtotime($to))." 23:59:59'";
        if(!empty($from) && $to == '') return "and REQUEST_DATE > '".date('Y-m-d', strtotime($from))." 00:00:00'";
        if($form > $to) return '';
        else return "and REQUEST_DATE BETWEEN '".date('Y-m-d', strtotime($from))." 00:00:00' AND '".date('Y-m-d',strtotime($to))." 23:59:59'";
    }
    
    function viewphuchoiAction(){
        $params = $this->_getAllParams();
        $config = Zend_Registry::get('config');
        $this->_helper->layout->disableLayout();
        
        $id = $params['id'];
        if(!empty($id)){
            
            $result = $this->findIDs($id);
            //echo '<pre>';
           //print_r($result);
            $this->view->ID= $result[0]['ID'];
            $this->view->REQUEST_DATE=$result[0]['REQUEST_DATE'];
            $this->view->RESTORED=$result[0]['RESTORED'];
            $this->view->RESTORE_DATE=$result[0]['RESTORE_DATE'];
            $this->view->CONTENT=$result[0]['CONTENT'];
            $this->view->SOLUTION=$result[0]['SOLUTION'];
            $this->view->restore_files = $this->findFileName($id);
            $this->view->urlservice = strrev(strchr(strrev($config->service->lienthong->uri),strrev('index.php?')));
            
        }
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));       
            $this->view->session = $session;
       }  catch (Exception $ex){
            return 0;
       }
        
    }
    
    function findIDs($id){
        
        $config = Zend_Registry::get('config');
        
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
            if($session=='0') return 0;
            $parameter = base64_encode($id);
            //return $parameter;
            
            $paramRQ = array(
                'session' => $session,
                'service_code' => 'BACKUP',
                'service_name' => 'SelectRestoreInfoByID',
                'parameter' => $parameter
                );
            
            $result = $client->__call('Execute', $paramRQ);
            //return 1;
           $result = ServicesCommon::DeseriallizeToArray(base64_decode($result));
           return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    
    function findFileName($id){
        
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
            'service_name' => 'SelectRestoreFileByRESTOREID',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       $result = ServicesCommon::DeseriallizeToArray(base64_decode($result));
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    function fileAction(){
        $params = $this->_getAllParams();
        //echo $params['mafile'];exit;
        $config = Zend_Registry::get('config');
        
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($params['mafile']);
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'ATTACH',
            'service_name' => 'downFile',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       
       //$result = ServicesCommon::DeseriallizeToArray(base64_decode($result));
       return $result;
       
       }  catch (Exception $ex){
            return 0;
       }
       exit;
    }
    
    function addAction(){
        $this->view->title = 'Thêm mới yêu cầu phục hồi';
        $params = $this->_getAllParams();
        QLVBDHButton::EnableAddNew($action, 'Gửi');
        
        if($this->getRequest()->getMethod() == 'POST') {
            if($params['content']==''){
                $this->view->error = 'Nội dung bắt buộc không được rỗng';
            }else{
                $file = $_FILES;
                //$name = implode('~', $file['file']['name']);
               $idRestore = $this->sendRestoreRequest($params['content']);
               //print_r($idRestore) ;exit;
                    if($_FILES['file']['name']['0']!=''){
                        $n = count($file['file']['name'])-1;
                        for($i=0; $i<$n;$i++){//lặp tên file
                            $string .= base64_encode($file['file']['name'][$i]).'~'.base64_encode(file_get_contents($file['file']['tmp_name'][$i])).'~';
                        }
                        $string = substr($string,0,-1);
                        
                        //echo base64_decode($this->uploadFile($string));
                        $masoList = explode('~', base64_decode($this->uploadFile($string)));//return mã số file
                        
                       for($i=0;$i<$n;$i++){
                           $fileList .= base64_encode($idRestore).'~'.base64_encode($masoList[$i]).'~'.base64_encode($file['file']['name'][$i]).'~';
                       }
                       
                       $fileList = substr($fileList,0,-1);
                       
                       if($this->attachFile($fileList)){
                           $this->_redirect('qtht/phuchoi/list');
                       }

                    }else{
                        $this->_redirect('qtht/phuchoi/list');
                    }

            }
        }

        
    }
    
    function uploadFile($parameter){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'UploadFile',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    function attachFile($parameter){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'AttachFile',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    
    function sendRestoreRequest($parameter){
        
        $config = Zend_Registry::get('config');
        try{
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $client    = new SoapClient($wsdl);
            $session = $client->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
        if($session=='0') return 0;
        $parameter = base64_encode($parameter).'~'.base64_encode(1);
        //echo $parameter;exit;
        //return $session;
        $paramRQ = array(
            'session' => $session,
            'service_code' => 'BACKUP',
            'service_name' => 'SendRestoreRequest',
            'parameter' => $parameter
            );
        
       $result = $client->__call('Execute', $paramRQ);
       return base64_decode($result);
       //return $result;
       }  catch (Exception $ex){
            return 0;
       }
    }
    
}
