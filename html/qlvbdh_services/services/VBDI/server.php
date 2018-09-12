<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../db/common.php';
require_once '../../models/VBDI.php';
$soap = new soap_server;
$soap->configureWSDL('VBDIService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('SelectVBDIByNgayBH',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'ngaybh'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);

$soap->register('SelectVBDI',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'sokyhieu'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);
$soap->register('GetFile',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'sokyhieu'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function SelectVBDIByNgayBH($username,$password,$ngaybh){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = VBDI::SelectVBDIByNgayBH($ngaybh);
	return Common::SerializeDataXML($row);
}
function SelectVBDI($username,$password,$sokyhieu){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = VBDI::SelectVBDI($sokyhieu);
	return Common::SerializeDataXML($row);
}
function GetFile($username,$password,$sokyhieu){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = VBDI::GetFile($sokyhieu);
	return $row;
}