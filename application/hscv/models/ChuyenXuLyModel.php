<?php
/**
 * @author trunglv
 * @version 1.0
 * Xu ly cac du lieu lien quan den chuyen xu ly
 *
 */
 require_once 'auth/models/ResourceUserModel.php';
class ChuyenXuLyModel {
	static function getObjectByIdHSCV($idHSCV,$type,$year){
		
		$db = Zend_Db_Table::getDefaultAdapter();
		//$db->beginTransaction();
		/*$sql = "select vbd.`SOKYHIEU` as MS ,  vbd.`TRICHYEU` as TEN from `vbd_vanbanden_".$year."`vbd
		where vbd.`ID_HSCV`=".$idHSCV."
		union
		select vbdi.`SOKYHIEU` as MS ,vbdi.`TRICHYEU` as TEN from `vbdi_vanbandi_".$year."` vbdi
		where vbdi.`ID_HSCV`=".$idHSCV."
		union
		select '' as MS, cvst.`NAME` as TEN from `hscv_congviecsoanthao_".$year."` cvst
		where cvst.`ID_HSCV`=".$idHSCV."
		union
		select hsmc.`MAHOSO` as MS , hsmc.`TRICHYEU` as TEN  from `motcua_hoso_".$year."` hsmc
		where hsmc.`ID_HSCV`=".$idHSCV;*/
		
		$sql = "";
		if($type>=3)$type=3;
		switch ($type){
			case 1 : // Xu ly van ban den
				$sql = "select vbd.`SOKYHIEU` as MS ,  vbd.`TRICHYEU` as TEN , vbd.`MASOVANBAN` as MASO from `vbd_vanbanden_".$year."`vbd
				inner join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fk on fk.id_vbden = vbd.id_vbd
		where fk.`ID_HSCV`=".$idHSCV;
				break;
			case 2 : // cong viec soan thao ho so mot cua
				$sql ="select '' as MS, cvst.`NAME` as TEN from `hscv_congviecsoanthao_".$year."` cvst
		where cvst.`ID_HSCV`=".$idHSCV;
				break;
			case 3 : // ho so mot cua
				$sql = "select loaihsmc.`TENLOAI` , hsmc.`ID_LOAIHOSO`,  hsmc.`MAHOSO` as MS , hsmc.`TRICHYEU` as TEN  
				from `motcua_hoso_".$year."` hsmc inner join `motcua_loai_hoso` loaihsmc
		where loaihsmc.`ID_LOAIHOSO` = hsmc.`ID_LOAIHOSO` and hsmc.`ID_HSCV`=".$idHSCV;
				break;
			default:
				break;
		}
		//echo $sql;
		$query = $db->query($sql,array());
		return $re = $query->fetch();
	}

	static function  updatePCMTraHSMC($id){
		$ngay_tra = date('Y-m-d ')." 00:00:00";
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$sql = " update ". QLVBDHCommon::Table(motcua_hoso) . "  set PCMTRA_NGAY = ? where ID_HSCV =?";
			//echo $sql
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($ngay_tra,$id));
		}catch( Exception $ex ){
		
		}

	}

	static function isAlowaHSMC($ID_U){
		$actid = ResourceUserModel::getActionByUrl("motcua","motcua","trahoso");
		
		if(ResourceUserModel::isAcionAlowedByIDU($ID_U,$actid[0])){
			return 1;
		}
		return 0; 

	}

	static function updateKhongxulyMotcua($idhscv){
		$sql = "
			update ". QLVBDHCommon::Table("motcua_hoso")." 
			set KHONGXULY = 1
			where ID_HSCV = ?
		";

		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($idhscv));
		}catch(Exception $ex){
		
		}
	}
	static function updateThuhoiKhongxulyMotcua($idhscv){
		$sql = "
			update ". QLVBDHCommon::Table("motcua_hoso")." 
			set KHONGXULY = 0
			where ID_HSCV = ?
		";

		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($idhscv));
		}catch(Exception $ex){
		
		}
	}

	static function getUserByGroup($id_t,$group_code){
		$sql = "
		
			SELECT
				UG.ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U=U.ID_U
				INNER JOIN QTHT_GROUPS G ON UG.ID_G = G.ID_G
			WHERE
				TR.ID_T=? AND G.CODE = ?
			
			
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_t,$group_code));
			return $qr->fetchAll();
		}catch(Exception $ex){
		}
	}

	static function getUserByChucdanh($id_t,$cd_code){
		$sql = "
		
			SELECT
				 U.ID_U ,UG.ID_G, DEP.ID_DEP,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U=U.ID_U
				INNER JOIN QTHT_GROUPS G ON UG.ID_G = G.ID_G
				INNER JOIN FK_CHUCDANH_EMP FK_CD_EMP ON E.ID_EMP =  FK_CD_EMP.ID_EMP
				INNER JOIN QTHT_CHUCDANH CD ON FK_CD_EMP.ID_CD = CD.ID_CD
			WHERE
				TR.ID_T=? AND CD.CODE = ?
			
			GROUP BY U.ID_U
		";
		//echo $sql; exit;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_t,$cd_code));
			return $qr->fetchAll();
		}catch(Exception $ex){
		}
	}
}

?>
