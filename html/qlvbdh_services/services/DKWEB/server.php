<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/DKWEB.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('DKWEB', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('SetHoso',array('data'=>'xsd:string')

						,array('c' => 'xsd:string'),'http://soapinterop.org/');

$soap->register('SetDataSyns',array('tblname'=>'xsd:string','data'=>'xsd:string')

						,array('c' => 'xsd:string'),'http://soapinterop.org/');


$soap->register('getGopy',array()

						,array('c' => 'xsd:string'),'http://soapinterop.org/');

$soap->register('SetFile',array('data'=>'xsd:string')

						,array('c' => 'xsd:string'),'http://soapinterop.org/');


$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function SetHoso($data) {
	try{
		return DKWEB::insertFromWebsite($data);
	}catch(Exception $ex){
		return 0;
	}
}

function SetFile($data) {
	try{
		return DKWEB::insertFileFromWebsite($data);
	}catch(Exception $ex){
		
	}
}


function SetDataSyns($tblname, $data) {
	try{
		//return DKWEB::insertDataSyns($tblname,$data);
		return DKWEB::insertDataSyns($tblname, $data);
		
	}catch(Exception $ex){
		
	}
}


function getGopy() {
	try{
		return DKWEB::getGopy();
	}catch(Exception $ex){
		
	}
}

