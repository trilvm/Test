<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/HSCV.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('HSCVService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('SelectHSCV',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function SelectHSCV($username,$password){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = HSCV::SelectHSCV();
	//return $row;
	return base64_encode(Common::SerializeData($row));
}