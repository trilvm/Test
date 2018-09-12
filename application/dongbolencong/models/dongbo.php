<?php
require_once 'hscv/models/filedinhkemModel.php';

class DongBo{

	function CountListVanBanChuaDongBo($parameter){
		global $db;

		$where = " 1=1 ";
		$param = array();

		if($parameter["SOKYHIEU"]!=""){
			if($parameter["SOKYHIEU_FULL"]==1){
				$where .= " AND vbdi.SOKYHIEU = ?";
				$param[] = $parameter["SOKYHIEU"];
			}else{
				$where .= " AND vbdi.SOKYHIEU LIKE ?";
				$param[] = "%".$parameter["SOKYHIEU"]."%";
			}
		}

		$sql= "
			SELECT
				count(*) as CNT
			FROM
				".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
				LEFT JOIN ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." vbdidb ON vbdi.ID_VBDI = vbdidb.ID_VBDI
			WHERE
				$where AND vbdidb.ID_VBDI IS NULL
			
		";

		$r = $db->query($sql,$param)->fetch();
		return $r["CNT"];
	}

	function GetListVanBanChuaDongBo($parameter,$page,$limit,$order){
		global $db;

		$where = " 1=1 ";
		$param = array();

		if($parameter["SOKYHIEU"]!=""){
			if($parameter["SOKYHIEU_FULL"]==1){
				$where .= " AND vbdi.SOKYHIEU = ?";
				$param[] = $parameter["SOKYHIEU"];
			}else{
				$where .= " AND vbdi.SOKYHIEU LIKE ?";
				$param[] = "%".$parameter["SOKYHIEU"]."%";
			}
		}

		if($limit>0){
			$strlimit = "limit ".($page-1)*$limit.",".$limit;
		}
		if($order==""){
			$order = "vbdi.NGAYBANHANH DESC";
		}

		$sql= "
			SELECT
				vbdi.*
			FROM
				".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
				LEFT JOIN ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." vbdidb ON vbdi.ID_VBDI = vbdidb.ID_VBDI
			WHERE
				$where AND vbdidb.ID_VBDI IS NULL
			
			ORDER BY $order
			$strlimit
		";

		return $db->query($sql,$param)->fetchAll();
	}
	
	function GetListVanBanDaDongBo($parameter,$page,$limit,$order){
		global $db;

		$where = " 1=1 ";
		$param = array();

		if($parameter["SOKYHIEU"]!=""){
			if($parameter["SOKYHIEU_FULL"]==1){
				$where .= " AND vbdi.SOKYHIEU = ?";
				$param[] = $parameter["SOKYHIEU"];
			}else{
				$where .= " AND vbdi.SOKYHIEU LIKE ?";
				$param[] = "%".$parameter["SOKYHIEU"]."%";
			}
		}

		if($limit>0){
			$strlimit = "limit ".($page-1)*$limit.",".$limit;
		}
		if($order==""){
			$order = "vbdi.NGAYBANHANH DESC";
		}
		$sql= "
			SELECT
				vbdi.*,vbdidb.ID_VB_PORTAL
			FROM
				".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
				INNER JOIN ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." vbdidb ON vbdi.ID_VBDI = vbdidb.ID_VBDI
			WHERE
				$where
			ORDER BY $order
			$strlimit
		";

		return $db->query($sql,$param)->fetchAll();
	}

	function CountListVanBanDaDongBo($parameter){
		global $db;

		$where = " 1=1 ";
		$param = array();

		if($parameter["SOKYHIEU"]!=""){
			if($parameter["SOKYHIEU_FULL"]==1){
				$where .= " AND vbdi.SOKYHIEU = ?";
				$param[] = $parameter["SOKYHIEU"];
			}else{
				$where .= " AND vbdi.SOKYHIEU LIKE ?";
				$param[] = "%".$parameter["SOKYHIEU"]."%";
			}
		}

		$sql= "
			SELECT
				count(*) as CNT
			FROM
				".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
				INNER JOIN ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." vbdidb ON vbdi.ID_VBDI = vbdidb.ID_VBDI
			WHERE
				$where
		";

		$r = $db->query($sql,$param)->fetch();
		return $r["CNT"];
	}

	function GetDongBoInfo($vbdi){
		global $db;
		$sql= "
			SELECT
				SOKYHIEU
				,NGAYBANHANH
				,CONCAT(empnk.FIRSTNAME,' ',empnk.LASTNAME) as NGUOIKY
				,TRICHYEU
				,cq.CODE as MACOQUAN
				,cq.NAME as TENCOQUAN
				,lvb.NAME as TENLOAI
				,lvb.KYHIEU as MALOAI
				,cd.CODE as MACHUCDANH
				,cd.NAME as TENCHUCDANH
				,case when lvvb.NAME IS NULL THEN 'Khác' ELSE lvvb.NAME END as TENLINHVUC
				,case when lvvb.ID_LVVB IS NULL THEN 'KHAC' ELSE lvvb.ID_LVVB END as MALINHVUC
			FROM
				".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
				INNER JOIN QTHT_USERS unk ON vbdi.NGUOIKY = unk.ID_U
				INNER JOIN QTHT_EMPLOYEES empnk ON empnk.ID_EMP = unk.ID_EMP
				INNER JOIN VB_LOAIVANBAN lvb ON vbdi.ID_LVB = lvb.ID_LVB
				INNER JOIN VB_COQUAN cq ON vbdi.ID_CQ = cq.ID_CQ
				INNER JOIN QTHT_CHUCDANH cd ON empnk.ID_CD = cd.ID_CD
				LEFT JOIN VB_LINHVUCVANBAN lvvb ON lvvb.ID_LVVB = vbdi.ID_LVVB
			WHERE
				ID_VBDI = ?
		";
		return $db->query($sql,array($vbdi))->fetch();
	}

	function GetFileDongBo($idvbdi){
		
		global $db;

		$sql = "SELECT * FROM ".QLVBDHCommon::table("gen_filedinhkem")." WHERE ID_OBJECT=? AND TYPE=5";
		return $db->query($sql,array($idvbdi))->fetch();
	}

	function GetContentFileBase64($maso){
	
		$date = getdate();
		$year = QLVBDHCommon::getYear();		
		if(!$year)
			$year = $date['year'];		
		$model = new filedinhkemModel($year);
		$file = $model->getFileByMaso($maso);
		return base64_encode(file_get_contents($file->_pathFile));
	}

	function ThuHoi($idvbportal){
	
		global $db;
		$config = new Zend_Config_Ini('../application/config.ini','general',true);
		$wsdl = $config->sys_info->repservice;
		$key = $config->sys_info->keyservice;
		try{
			$client = new SoapClient($wsdl);
			$r = $client->unApprove($idvbportal,$key);
			if($r>0){
				echo json_encode(array("id"=>$r));
				$sql = "DELETE FROM ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." WHERE ID_VB_PORTAL = ?";
				$db->query($sql,array($idvbportal));
			}else{
				echo json_encode(array("id"=>0));
			}
		}catch(Exception $ex){
			echo json_encode(array("msg"=>"ERROR"));
		}
	}

	function DongBo($idvbdi){
		global $auth;
		global $db;
		$user = $auth->getIdentity();

		$config = new Zend_Config_Ini('../application/config.ini','general',true);
		
		$data = DongBo::GetDongBoInfo($idvbdi);

		$wsdl = $config->sys_info->repservice;
		$key = $config->sys_info->keyservice;
		try{
			$client = new SoapClient($wsdl);
			$r = $client->insertVb(
				$data["SOKYHIEU"]
				,$data["NGAYBANHANH"]
				,$data["NGAYBANHANH"]
				,"2099-01-01"
				,$data["NGUOIKY"]
				,$data["TRICHYEU"]
				,$data["TRICHYEU"]
				,$data["MACHUCDANH"]
				,$data["MALINHVUC"]
				,$data["MACOQUAN"]
				,$data["MALOAI"]
				,$data["TENCHUCDANH"]
				,$data["TENLINHVUC"]
				,$data["TENCOQUAN"]
				,$data["TENLOAI"]
				,$key);
			$idvbportal = $r;
			if($r>0){
				$idvb = $r;
				$datafile = DongBo::GetFileDongBo($idvbdi);
				if($datafile["MASO"]!=""){

					$contentfile = DongBo::GetContentFileBase64($datafile["MASO"]);

					$r = $client->uploadFileVB($idvb,$datafile["FILENAME"],$datafile["MIME"],1,$contentfile,$key);
				}
			}
			if($r>0){
				echo json_encode(array("id"=>$r));
				$sql = "INSERT INTO ".QLVBDHCommon::table("vbdi_vanbandi_dongbo")." (ID_VBDI,DATE_REPLICATE,ID_U,ID_VB_PORTAL) VALUES(?,?,?,?)";
				$db->query($sql,array($idvbdi,date("Y-m-d H:i:s"),$user->ID_U,(int)$idvbportal));
			}else{
				echo json_encode(array("id"=>0));
			}
		}catch(Exception $ex){
			echo json_encode(array("msg"=>"ERROR"));
		}
	}
}