<?php
require_once '../../lib/nusoap.php';
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/Backup.php';
require_once '../../db/common.php';
$soap = new soap_server ( );
$soap->configureWSDL ( 'BackupService', 'http://php.hoshmand.org/' );
$soap->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';


$soap->register ( 'UpdateBackupRequest',
                array ( 'username' => 'xsd:string',
                    'password' => 'xsd:string',
                    'id' => 'xsd:string'),
                array ('status' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'UpdateRestoreRequestResponse',
                array ( 'username' => 'xsd:string',
                    'password' => 'xsd:string',
                    'id' => 'xsd:string'),
                array ('status' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'SendBackup',
                array ( 'username' => 'xsd:string',
                    'password' => 'xsd:string',
                    'id' => 'xsd:string'),
                array ('status' => 'xsd:string' ), 'http://soapinterop.org/' );
$soap->register ( 'ReceiveBackupConfig',
                array ( 'username' => 'xsd:string',
                    'password' => 'xsd:string'),
                array ('status' => 'xsd:string' ), 'http://soapinterop.org/' );

$soap->service ( isset ( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : '' );

function UpdateBackupRequest($username,$password,$id) {
	try {   $bool= Common::Login2( $username, $password );
                if($bool==FALSE) return 0;
		return Backup::UpdateBackupRequest($id);
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function UpdateRestoreRequestResponse($username,$password,$id) {
	try {   $bool= Common::Login2( $username, $password );
                if($bool==FALSE) return 0;
		return Backup::UpdateRestoreRequestResponse($id);
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function SendBackup($username,$password,$id) {
	try {   $bool= Common::Login2( $username, $password );
                if($bool==FALSE) return 0;
		return Backup::SendBackup($id);
	} catch ( Exception $ex ) {
		return "-1";
	}
}

function ReceiveBackupConfig($username,$password) {
	try {   $bool= Common::Login2( $username, $password );
                if($bool==FALSE) return 0;
		return Backup::ReceiveBackupConfig();
	} catch ( Exception $ex ) {
		return "-1";
	}
}

