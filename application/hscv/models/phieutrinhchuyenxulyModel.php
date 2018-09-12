<?php
/**
 * @author trunglv
 * @version 1.0
 * Xu ly cac du lieu lien quan den chuyen xu ly
 *
 */
 require_once 'auth/models/ResourceUserModel.php';
class phieutrinhchuyenxulyModel {
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
       function selectIdGroupFromIdu($id)
        {   
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $sql="SELECT ID_G FROM fk_users_groups WHERE ID_U=?";
            $r=$dbAdapter->query($sql,$id)->fetchAll();
            return $r[0]['ID_G'] ;
        }
}

?>
