<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/Alarm.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('AlarmService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('GetAlarm',array('sid'=>'xsd:string'),array('c' => 'xsd:string'),'http://soapinterop.org/');
$soap->register('GetMessage',array('sid'=>'xsd:string'),array('c' => 'xsd:string'),'http://soapinterop.org/');
$soap->register('Login',array('username' => 'xsd:string','password' => 'xsd:string'),array('sid' => 'xsd:string'),'http://soapinterop.org/');

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function GetAlarm($sid) {
	try{
		$idu = Common::GetAuth($sid);
		return Alarm::GetData($idu,1);
	}catch(Exception $ex){
		return $ex;
	}
}
function GetMessage($sid) {
	try{
		$idu = Common::GetAuth($sid);
		return Alarm::GetData($idu,0);
	}catch(Exception $ex){
		return $ex;
	}
}
function Login($username,$password){
	return Common::Login($username,$password);
}