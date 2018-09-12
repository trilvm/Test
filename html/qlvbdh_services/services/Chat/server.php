<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/Chat.php';
require_once '../../db/common.php';
require_once '../../models/Alarm.php';
$soap = new soap_server ( );
$soap->configureWSDL ( 'ChatService', 'http://php.hoshmand.org/' );
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register ( 'Login', array ('username' => 'xsd:string', 'password' => 'xsd:string' ), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'GetUsers', array (), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'GetDeps', array (), array ('sid' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'GetAlarm', array ('sid' => 'xsd:string' ), array ('c' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'GetMessage', array ('sid' => 'xsd:string' ), array ('c' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->service ( isset ( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : '' );

function Login($username, $password) {
	try {
		return Chat::Login ( $username, $password );
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function GetUsers() {
	try {
		return base64_encode ( Common::SerializeData ( Chat::GetUsers () ) );
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function GetDeps() {
	try {
		return base64_encode ( Common::SerializeData ( Chat::GetDeps () ) );
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function GetAlarm($sid) {
	try {
		$idu = Common::GetID_U ( $sid );
		//return Alarm::GetSerializeDataAlarm($idu,1);
		//return base64_encode(Chat::GetSerializeDataAlarm($idu,1));
		return base64_encode ( Common::SerializeData ( Chat::GetSerializeDataAlarm ( $idu, 1 ) ) );
	} catch ( Exception $ex ) {
		return $ex;
	}
}
function GetMessage($sid) {
	
	try {
		$idu = Common::GetID_U ( $sid );
		return base64_encode ( Common::SerializeData ( Chat::GetSerializeDataAlarm ( $idu, 0 ) ) );
	} catch ( Exception $ex ) {
		return "-1";
	}
}