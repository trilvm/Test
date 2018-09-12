<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../db/common.php';
require_once '../../models/Desktop.php';
$soap = new soap_server;
$soap->configureWSDL('DesktopService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('Execute',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'service_code'=>'xsd:string',
		'service_name'=>'xsd:string',
		'parameter'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function Execute($username,$password,$service_code,$service_name,$parameter){
	
	$username=explode("~",$username);
	$password=explode("~",$password);
	//$auth = Common::ServiceAuthentication($username[0],$password[0]);
	$auth = true;
	if($auth)
	{
		$isLogin=Common::LoginDesktop($username[1],$password[1]);
		if($isLogin=='okAccount'){
			$result = ProcessMessage::ExecuteFunction($service_code,$service_name,$parameter);
		}else{
			return $isLogin;
		}
		
	}
	else
	{
		return '-1';		
	}	
	return $result;
}