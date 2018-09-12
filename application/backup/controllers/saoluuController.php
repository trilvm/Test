<?php
require_once 'backup/models/saoluuModel.php';
class Backup_SaoluuController extends Zend_Controller_Action{
    var $db;
    function init(){
         $this->db = new Backup_SaoluuModel();
         $this->view->title = 'Quản lý sao lưu, phục hồi';
         
    }
    function listAction(){
        
       $params= $this->_getAllParams();
       if($params['post']=='ok'){
       }
       
       $limit = $params['limit'];
       $page = $params['page'];
       $search = $params['search'];
       
      //if($limit==0 ||$limit =='') $limit = 5;
       $this->view->data = $this->db->getAll();
       QLVBDHButton::EnableRestore("/qtht/users/add");
       $config = Zend_Registry::get('config');
       $this->view->limit = '0k';
       echo '<pre>';
       print_r($config);
      
    }
    function restoreRequest(){
        $config = Zend_Registry::get('config');
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
        }catch (Exception $ex){
            
        }
        
    }
}
