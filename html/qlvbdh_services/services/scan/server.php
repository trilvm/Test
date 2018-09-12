<?php

require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/filedinhkem.php';
require_once '../../db/common.php';
$soap = new soap_server;
$soap->soap_defencoding = 'UTF-8';
$soap->configureWSDL('QLVBDHDesktop', 'http://php.hoshmand.org/');
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

$soap->register('Test', array('a' => 'xsd:string','b' => 'xsd:string'), array('c' => 'xsd:string'), 'http://soapinterop.org/');
$soap->register('upload', 
    array(
        'username' => 'xsd:string',
        'password' => 'xsd:string',
        'year'=>'xsd:string',
        'month'=>'xsd:string',
        'filename'=>'xsd:string',
        'mime'=>'xsd:string',
        'filecontent'=>'xsd:string',
        'maSoFile'=>'xsd:string',
        'idObject'=>'xsd:string'
    ),
    array(
        'd' => 'xsd:string'
    ),
        'http://soapinterop.org/'
    );

$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');

function Test($username,$password) {
	//Check user
	$username = explode("?",$username);
	$username = $username[0];
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
		return -1;
	}    
    $fileObject = new filedinhkem();
    return $fileObject->getMaSo();
}

function upload($username,$password,$year,$month,$filename,$mime,$filecontent,$maSoFile,$idObject){
	$username = explode("?",$username);
	$type=$username[1];
	$username = $username[0];

	if($type==0){
		$type=-1;
	}

    $fileObject = new filedinhkem();
    //$fileObject->writeLog("upload","Calling ... ");
    //$fileObject->writeLog("upload","idObject : ".$idObject);
	if(Common::checkUserAndPass($username,$password)!="okAccount"){
        //$fileObject->writeLog("upload","Login Failed : ".$username."-".$password);
		return -1;
	}
	try{
        $fileObject->folder      = PATH_FILE_UPLOAD."\\".$year."\\".$month;
        $fileObject->idObject    = $idObject;
        $fileObject->maso        = $maSoFile;
        $fileObject->nam         = $year;
        $fileObject->thang       = $month;
        $fileObject->mime        = "application/pdf";
        $fileObject->fileName    = $filename;
        $fileObject->type        = $type;
        $fileObject->content     = $filecontent;
        $fileObject->user        = Common::GetID_U($username);;
        $fileObject->timeUpdate  = date("Y-m-d h:i:s");
        $fileObject->path        = PATH_FILE_UPLOAD."\\".$year."\\".$month;
        if($maSoFile == ""){
            try{
                $fileObject->insert();
            }catch(Exception $ex){
                echo $ex->getMessage();
                //$fileObject->writeLog("upload",$ex->getMessage());
            }
        }
        try{
            $fileObject->upload();
        }catch(Exception $ex){
            //$fileObject->writeLog("upload",$ex->getMessage());
            echo $ex->getMessage();
        }
        return $fileObject->getMaSo();
	}catch(Exception $ex){
		return 0;
	}
}