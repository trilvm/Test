<?php
header('Content-Type: text/html; charset=utf-8');
$rootPath = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . '/library');

require_once 'Zend/Loader.php';
require_once 'Common/commonU.php';
require_once 'servicelienthong.php';
Zend_Loader::registerAutoload();
$config = new Zend_Config_Ini('../application/config.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);
$db = Zend_Db::factory($config->db);
try {
    global $db;
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $wsdl      = $configlienthong->service->lienthong->uri;
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $cliente    = new SoapClient($wsdl);
        $session = $cliente->__call('Login',array("madonvi"=>$madonvi,'password'=>$password));
        $param = array(
            'session' => $session,
            'service_code' => 'VPCP',
            'service_name' => 'QLVBDHGETCOQUANTRUCVPCP',
            'parameter' => ''
        );
        $response = $cliente->__call('Execute', $param);
        $datavpcp = base64_decode($response);
        $datavpcps = json_decode($datavpcp);
        $db->query("TRUNCATE TABLE vpcp_agency");
        foreach($datavpcps as $itemvpcp)
        {
            try {
                $db->insert("vpcp_agency",array(
                                                "Code"=> $itemvpcp->Code,
                                                "CenterCode"=> $itemvpcp->CenterCode,
                                                "Link"=> $itemvpcp->Link,
                                                "Deleted"=> $itemvpcp->Deleted,
                                                "Id"=> $itemvpcp->Id,
                                                "ParrentName"=> $itemvpcp->ParrentName,
                                                "Pid"=> $itemvpcp->Pid,
                                                "Name"=> $itemvpcp->Name
                                            ));
            } catch (Exception $ex) {

            }
            
        }
} catch (Exception $e) {
    echo $e;
    echo "ERROR";
}
?>   
