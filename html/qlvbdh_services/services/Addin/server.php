<?php

require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/addin.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->configureWSDL('QLVBDHDesktop', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';


$soap->register('Test', array('a' => 'xsd:string','b' => 'xsd:string'), array('c' => 'xsd:string'), 'http://soapinterop.org/');
$soap->register('CheckPermissionOfDuThao', 
array(
	'username' => 'xsd:string'
	,'password' => 'xsd:string'
	,'lastidobject'=>'xsd:string'
	,'nam'=>'xsd:string'
)
, array(
	'e' => 'xsd:string'
)
, 'http://soapinterop.org/');

$soap->register('InsertPhienBanDuThao', 
array(
	'username' => 'xsd:string'
	,'password' => 'xsd:string'
	,'idduthao'=>'xsd:string'
	,'filename'=>'xsd:string'
	,'realfilename'=>'xsd:string'
	,'nam'=>'xsd:string'
	,'thang'=>'xsd:string'
)
, array(
	'e' => 'xsd:string'
)
, 'http://soapinterop.org/');

$soap->register('CreateDuThao', 
array(
	'username' => 'xsd:string'
	,'password' => 'xsd:string'
	,'idhscv'=>'xsd:string'
	,'trichyeu'=>'xsd:string'
	,'idloaivb'=>'xsd:string'
	,'filename'=>'xsd:string'
	,'realfilename'=>'xsd:string'
	,'nam'=>'xsd:string'
	,'thang'=>'xsd:string'
)
, array(
	'e' => 'xsd:string'
)
, 'http://soapinterop.org/');

$soap->register('UploadFile', array('username' => 'xsd:string','password' => 'xsd:string','year'=>'xsd:string','month'=>'xsd:string','filename'=>'xsd:string','filecontent'=>'xsd:string','isfirst'=>'xsd:string'), array('d' => 'xsd:string'), 'http://soapinterop.org/');
$soap->register('GetListHSCVAndDuThao', array('a' => 'xsd:string','b' => 'xsd:string'), array('c' => 'xsd:string'), 'http://soapinterop.org/');

$soap->register('CreateFileAfterDS', array(
'username' => 'xsd:string'
,'password' => 'xsd:string'
,'idobject' => 'xsd:string'
,'maso' => 'xsd:string'
,'realfilename' => 'xsd:string'
,'nam' => 'xsd:string'
,'thang' => 'xsd:string'
), array('c' => 'xsd:string'), 'http://soapinterop.org/');

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function Test($username,$password) {
	//Check user
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
    return $username.$password;
}
function GetListHSCVAndDuThao($username,$password){
	//Check user
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	return 1;
}
function CheckPermissionOfDuThao($username,$password,$lastidobject,$nam){
	//Check user
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	return Addin::CheckPermissionOfDuThao($lastidobject,$nam,$username);
}
function UploadFile($username,$password,$year,$month,$filename,$filecontent,$isfirst){
	//Check user
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	try{
		$path = PATH_FILE_UPLOAD."\\".$year."\\".$month;
		$file = $path.'\\'.$filename;
		if($isfirst=="1"){
			mkdir($path,777,true);
			unlink($file);
		}
		$data = base64_decode($filecontent);
		file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
		return 1;
	}catch(Exception $ex){
		return 0;
	}
}
function InsertPhienBanDuThao($username,$password,$idduthao,$filename,$realfilename,$nam,$thang){
	//Check user
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	$idu = Common::GetID_U($username);
	return Addin::InsertPhienBanDuThao($idduthao,$idu,$filename,base64_decode($realfilename),$nam,$thang);
}

function CreateDuThao($username,$password,$idhscv,$trichyeu,$idloaivb,$filename,$realfilename,$nam,$thang){
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	$idu = Common::GetID_U($username);
	return Addin::CreateDuThao($idu,$idhscv,base64_decode($trichyeu),$idloaivb,$filename,base64_decode($realfilename),$nam,$thang);
}

function CreateFileAfterDS($username,$password,$idobject,$maso,$realfilename,$nam,$thang){
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}
	$idu = Common::GetID_U($username);
	return Addin::CreateFileAfterDS($idu,$idobject,$maso,base64_decode($realfilename),$nam,$thang);
}