<?php
require_once 'Zend/Controller/Action.php';

class Giamsatgiaoviec_GiamSatGiaoViecController extends Zend_Controller_Action{
    
    function getdanhsachdonvinoibottAction(){
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $config = Zend_Registry::get('config');
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
            $param = array(
                    'session'  =>  $session,
                    'service_code' => 'TRAODOIGSCV',
                    'service_name' => 'DANHSACHNOIBOLIENTHONG',
                    'parameter' => base64_encode($params['donvi'])
            );
            $dataReturn = $cliente->__call('Execute', $param);            
            $data =  ServicesCommon::DeseriallizeToArray(base64_decode($dataReturn));
            echo json_encode($data);            
	}catch (Exception $ex){
            echo $ex;
        }
        exit;
    }
}

