<?php
require_once '../../config.php';
require_once '../../db/connection.php';
require_once '../../models/Chatv4.php';
require_once '../../db/common.php';

function VBDmoi($username,$password){
    try {   
               //Chứng thực Auth
                $sessionInfo=Chat::AuthByUsername($username,$password); 
                if($sessionInfo==0) return 0;
		return Chat::VBDmoi($sessionInfo[0]) ;
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function VBDImoi($username,$password){
    try {   
               //Chứng thực Auth
                $sessionInfo=Chat::AuthByUsername($username,$password); 
                if($sessionInfo==0) return 0;
		return Chat::VBDImoi($sessionInfo[0]) ;
	} catch ( Exception $ex ) {
		return "-1";
	}
}
function HSCVmoi($username,$password){
    try {   
		   //Chứng thực Auth
			$sessionInfo=Chat::AuthByUsername($username,$password); 
			if($sessionInfo==0) return 0;
		return Chat::HSCVmoi($sessionInfo[0]) ;
	} catch ( Exception $ex ) {
		return "-1";
	}
}

