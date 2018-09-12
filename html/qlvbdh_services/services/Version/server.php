<?php

require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/Phienban.php';
require_once '../../models/Chatv4.php';
require_once '../../db/common.php';
$soap = new soap_server ( );
$soap->configureWSDL ( 'Versions', 'http://php.hoshmand.org/' );
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';
//$soap->register ( 'Login', array ('username' => 'xsd:string', 'password' => 'xsd:string' ), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->register ( 'CungCapLenhCapNhat', array ('username' => 'xsd:string','password' => 'xsd:string' ), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->register ( 'CapNhatTrangThaiCapNhat', array ('username' => 'xsd:string','password' => 'xsd:string', 'prameters' => 'xsd:string' ), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->register ( 'CapNhatTrangThaiThatBai', array ('username' => 'xsd:string','password' => 'xsd:string', 'prameters' => 'xsd:string' ), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->register ( 'ReadConfig',  array('username' => 'xsd:string','password' => 'xsd:string'), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->service ( isset ( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : '' );


function CungCapLenhCapNhat($username, $password) {
	try {
               $bool= Common::Login2( $username, $password );
                if($bool==FALSE) return 0;
		return base64_encode(PhienBan::CungCapLenhCapNhat()) ;
	} catch ( Exception $ex ) {
		return "-1";
	}
}

function CapNhatTrangThaiCapNhat($username, $password,$parameters) {
	try {   
                $bool= Common::Login2($username, $password);
                if($bool==FALSE) return 0;
		return PhienBan::CapNhatTrangThaiCapNhat($parameters);
	} catch ( Exception $ex ) {
		return "-1";
	}
}

function CapNhatTrangThaiThatBai($username, $password,$parameters) {
	try {   
                $bool= Common::Login2($username, $password);
                if($bool==FALSE) return 0;
		return PhienBan::CapNhatTrangThaiThatBai($parameters);
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function ReadConfig($username, $password) {
	try {   
                $bool= Common::Login2($username, $password);
                if($bool==FALSE) return 0;
		return base64_encode(PhienBan::ReadConfig());
	} catch ( Exception $ex ) {
		return "-1";
	}
}

?>
