<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../db/common.php';
$soap = new soap_server ( );
$soap->configureWSDL ( 'ChatService', 'http://php.hoshmand.org/' );
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';


$soap->register ( 'Update', array ( 'filename' => 'xsd:string','hash' => 'xsd:string'), array ('exists' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->service ( isset ( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : '' );

function Update($filename,$hash){
	if(file_exists ( PATH_FILE_UPDATE."/".$filename )){
		if(md5_file(PATH_FILE_UPDATE."/".$filename)!=$hash){
			return "1";
		}else{
			return "0";
		}
	}else{
		return "0";
	}
}