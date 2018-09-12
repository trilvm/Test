<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/HSMC.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('HSMCService', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('Insert',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'masohoso'=>'xsd:string',
		'tentochuccanhan'=>'xsd:string',
		'tenhoso'=>'xsd:string',
		'trangthai'=>'xsd:string',
		'ghichu'=>'xsd:string',
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);
$soap->register('Select',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);
$soap->register('GetInfo',
	array(
		'username'=>'xsd:string',
		'password'=>'xsd:string',
		'in_info'=>'xsd:string'
	),
	array(
		'c' => 'xsd:string'
	),
	'http://soapinterop.org/'
);

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function Select($username,$password){
	//Check user
	if(!Common::Login2($username,$password)){
		return -1;
	}
	
	//Select
	$row = HSMC::Select();
	return Common::SerializeData($row);
}
function Insert($username,$password,$masohoso,$tentochuccanhan,$tenhoso,$trangthai,$ghichu) {
	try{
		
		//Check user
		if(!Common::Login2($username,$password)){
			return -1;
		}
		
		//insert
		$arrmasohoso = Common::Deseriallize($masohoso);
		$arrtentochuccanhan = Common::Deseriallize($tentochuccanhan);
		$arrtenhoso = Common::Deseriallize($tenhoso);
		$arrtrangthai = Common::Deseriallize($trangthai);
		$arrghichu = Common::Deseriallize($ghichu);
		for($i=0;$i<count($arrmasohoso);$i++){
			HSMC::Insert($arrmasohoso[$i],$arrtentochuccanhan[$i],$arrtenhoso[$i],$arrtrangthai[$i],$arrghichu[$i]);
		}
		return 1;
	}catch(Exception $ex){
		return 0;
	}
}
function GetInfo($username,$password,$in_info){
	try{
		
		//Check user
		if(!Common::Login2($username,$password)){
			return -1;
		}
		
		$row = HSMC::GetInfo(strtolower($in_info),strtolower($in_info));
		//return $row;
		return base64_encode(Common::SerializeData($row));

	}catch(Exception $ex){
		return 0;
	}
}
