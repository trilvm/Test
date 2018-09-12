<?php 
require_once 'qtht/models/SoVanBanModel.php';
require_once 'nusoap/nusoap.php';

class vanbandireportModel{
	static function getReportData($fromdate,$todate,$id_svb,$sel_pb,$id_u,$is_lienThong, $id_lvbs, $sorttype){
		
	    $where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "vbdi.`NGAYBANHANH` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "vbdi.`NGAYBANHANH` <= '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		if($is_lienThong > 0)
		{ 
			$where_lienthong = "vbdi.`MASOLIENTHONG` > 0"; 
			array_push($where_arr,$where_lienthong);
		}
		if($id_svb > 0)
		{
			$where_svb = "vbdi.`ID_SVB`= '".$id_svb."'" ; 
			array_push($where_arr,$where_svb);
		} else {
			$idSvb_arr = array();
			$svbs = SoVanBanModel::selectWithDep(1);
			foreach($svbs as $svb)
			{
				$idSvb_arr[] = $svb['ID_SVB'];
			}
			$where_svb = "vbdi.`ID_SVB` IN( ".implode(', ', $idSvb_arr).")" ; 
			array_push($where_arr,$where_svb);
		}
		if($id_lvbs > 0)
		{
			if(array_search("0",$id_lvbs) == FALSE && $id_lvbs != "0"){
				$where_lvb = "vbdi.`ID_LVB` = ".$id_lvbs; 
				array_push($where_arr,$where_lvb);	
			}
		}
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";		 
		//var_dump($where);exit;			
         $sql = "Select vbdi.*,gen_dk.FILENAME as TEP_DINHKEM,vbden.NGAYHETHAN,vbden.SOKYHIEU as SOKYHIEU_VBD from ".QLVBDHCommon::Table("vbdi_vanbandi")." vbdi 
         		left join `".QLVBDHCommon::Table("vbd_vanbanden")."` vbden on vbdi.ID_HSCV=vbden.ID_HSCV
                left join QTHT_USERS unyc on vbdi.NGUOISOAN=unyc.ID_U 
                left join QTHT_EMPLOYEES empnyc on unyc.ID_EMP = empnyc.ID_EMP
                left join `".QLVBDHCommon::Table("gen_filedinhkem")."` gen_dk on gen_dk.ID_OBJECT=vbdi.ID_VBDI 
                ".$where." GROUP BY vbdi.ID_VBDI ORDER BY SODI_IN, NGUOIKY ". $sorttype;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		//echo $sql;exit;
		$re = $query->fetchAll();
		return $re;
	}

	static function getNguoinhanFromVbandi($id_vbdi){
		$sql = "select NGUOINHAN from `".QLVBDHCommon::Table("vbdi_dongluanchuyen")."` where ID_VBDI = ?
			";
	        //  echo $sql;
	       //  echo $id_hscv;
		$name_u = "";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($id_vbdi));
		$re = $query->fetchAll();
		$stt = 1;
		foreach ($re as $row){
			//if($stt == 2){
				$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOINHAN"])."<br/>".$name_u ;
				//break;	
			//}
			$stt ++;
		}
		return $name_u;
    }
        static function getNguoinhanFromVbandi2($id_vbdi){
	
	$sql = "select NGUOINHAN from `".QLVBDHCommon::Table("vbdi_dongluanchuyen")."` where ID_VBDI = ?
		";
        //  echo $sql;
       //  echo $id_hscv;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbdi));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){
		//if($stt == 2){
			$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOINHAN"])."<br/>".$name_u ;
			//break;	
		//}
		$stt ++;
	}
	return $name_u;
        }
        static function getNguoiguiFromVbandi($id_vbdi){
	
	$sql = "select * from `".QLVBDHCommon::Table("vbdi_dongluanchuyen")."` where ID_VBDI = ?
		";
        //  echo $sql;
       //  echo $id_hscv;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbdi));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){
		//if($stt == 2){
			$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOICHUYEN"])."<br/>" ;
			//break;	
		//}
		$stt ++;
	}
	return $name_u;
        }
        static function getNguoiguiFromVbandi2($id_vbdi){
	
	$sql = "select * from `".QLVBDHCommon::Table("vbdi_dongluanchuyen")."` where ID_VBDI = ?
		";
        //  echo $sql;
       //  echo $id_hscv;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbdi));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){
		//if($stt == 2){
			$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOICHUYEN"])."<br/>" ;
			//break;	
		//}
		$stt ++;
	}
	return $name_u;
        }
        static function getDonViNoiBoFromVbandi($id_vbdi){
	
	$sql = "select * from `".QLVBDHCommon::Table("vbdi_luanchuyennoibo")."` where ID_VBDI = ?
		";
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbdi));
	$re = $query->fetchAll();
      
        //exit;
	foreach ($re as $row){
			$name_u = UsersModel::getnamecqnb($row["CQNHAN"])."<br/>".$name_u ;
	}
        
	return $name_u;
        }
        static function getIdempFromVBDI($id_vbdi){
	
	$sql = "select * from `".QLVBDHCommon::Table("vbdi_dongluanchuyen")."` where ID_VBDI = ?
		";
         //echo $sql;
       //  echo $id_hscv;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbdi));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){
		//if($stt == 2){
			$name_u = UsersModel::getIdEmpById($row["NGUOINHAN"])."<br/>" ;
			//break;	
		//}
		$stt ++;
	}
	return $name_u;
        }
        static function getIdDepFromIDemp($id_emp){
	
			$sql = "select ID_DEP from `qtht_employees` where ID_EMP = ?
				";

		          //echo $sql;
		       //  echo $id_hscv;
			$name_u = "";
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$query = $dbAdapter->query($sql,array($id_emp));
			$re = $query->fetchAll();
			$stt = 1;
			foreach ($re as $row){
				//if($stt == 2){
					$name_u = UsersModel::getNamePBByIdDep($row["ID_DEP"]);
					//break;	
				//}
				$stt ++;
			}
			return $name_u;
        }
        static function getIdHscvVBDFromIDHscvVBDi($id_hscv){
	
			$sql = "select ID_VBDEN from `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` where ID_HSCV = ?
				";

		          //echo $sql;
		       //  echo $id_hscv;
			$name_u = "";
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetchAll();
			$stt = 1;
			foreach ($re as $row){
				//if($stt == 2){
					$name_u = UsersModel::getSkhVBD($row["ID_VBDEN"]);
					//break;	
				//}
				$stt ++;
			}
			return $name_u;
        }
        static function getNhhfromIdHscv($id_hscv){
	
			$sql = "select ID_VBDEN from `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` where ID_HSCV = ?
				";

		          //echo $sql;
		       //  echo $id_hscv;
			$name_u = "";
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetchAll();
			$stt = 1;
			foreach ($re as $row){
				//if($stt == 2){
					$name_u = UsersModel::getNhhVBD($row["ID_VBDEN"]);
					//break;	
				//}
				$stt ++;
			}
			return $name_u;
        }

        
        // Lấy thông tin văn bản đi liên thông
        public static function LoginService() {
            global $config;
            $client = new SoapClient($config->service->lienthong->uri);
            return $client->__call('Login', array(
                'madonvi' => $config->service->lienthong->username,
                'password' => $config->service->lienthong->password));
        }
        
        public static function DongLuanChuyenLienThong($masovanban,$session) {

        global $config;
        try{
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'LUANCHUYEN',
                'service_name' => '',
                'parameter' => base64_encode($masovanban)
            );

            $dlc = $client->__call('Execute', $params);
            echo $dlc;exit;
			return ServicesCommon::DeseriallizeToArray(base64_decode($dlc));

        } catch (Exception $e) {
            return;
        }
    }

}
?>