<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/TD.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('TDService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('SelectTD',
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

function SelectTD($username,$password){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = TD::SelectTD();
	//return $row;
	return base64_encode(Common::SerializeData($row));
}