<?php

require_once 'db/common.php';

if (isset($_REQUEST['download'])) {

    if (Common::ServiceAuthentication($_REQUEST['session']) == 1) {
        require_once 'models/GETFILE.php';
        GetFile::run($_REQUEST['maso']);
    }
} else {
    require_once 'lib/nusoap.php';
    require_once 'config.php';
    require_once 'db/connection.php';
    require_once 'models/Bootstrap.php';
    require_once 'models/Logs.php';
    require_once 'models/Chatv4.php';

    $soap = new soap_server;
    $soap->configureWSDL('DesktopService', 'http://php.hoshmand.org/');
    $soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

    $soap->register('Login',
            array(
                'username' => 'xsd:string',
                'password' => 'xsd:string'
            ),
            array(
                'c' => 'xsd:string'
            ),
            'http://soapinterop.org/'
    );

    $soap->register('Execute',
            array(
                
                'service_code' => 'xsd:string',
                'service_name' => 'xsd:string',
                'parameter' => 'xsd:string'
            ),
            array(
                'c' => 'xsd:string'
            ),
            'http://soapinterop.org/'
    );

    $soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');
}

function Login($username, $password) {
    
    $session = Chat::Login($username, $password);
    LOGS::WriteLog('LOGIN', $username, date('Y-m-d H:i:s'), $session ? 1 : 0, 1, '');
    return $session;
}

function Execute( $service_code, $service_name, $parameter) {
   
    $result = ProcessMessage::ExecuteFunction($service_code, $service_name, $parameter);
    return $result;
    
 /*   $auth = Common::ServiceAuthentication($session);
    Common::setSession($session);
    $sessionInfo = Common::getSession();
    
    LOGS::WriteLog('AUTH', $sessionInfo['MADONVI'], date('Y-m-d H:i:s'), $auth == 1 ? 1 : 0, 1, '');
    if ($auth == 1) {
        $result = ProcessMessage::ExecuteFunction($service_code, $service_name, $parameter);
        //ghi log
        LOGS::WriteLog($service_name==''?$service_code:$service_code.':'.$service_name, $sessionInfo['MADONVI'], date('Y-m-d H:i:s'), $result, 1, '');
        return $result;
    } else {
        return $auth;
    } */
}