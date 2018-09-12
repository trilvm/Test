<?php 
require_once 'vbden/models/vbdenModel.php';

class HosoluutheodoiModel {
	static function Count($parameter){
		global $db;
		$param = Array();
		$sql = "
			SELECT count(*) as CNT FROM 
				".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv 
				inner join ".QLVBDHCommon::Table("HSCV_HOSOLUUTHEODOI")." luu on hscv.ID_HSCV = luu.ID_HSCV
			where
				hscv.IS_THEODOI = 1
				and luu.U_OWN = ?
		";
		$param[] = $parameter['ID_U'];
		//echo $parameter['ID_U'];
		try{
			//echo $sql;
			$r = $db->query($sql,$param);
			$cnt = $r->fetch();
			
		}catch(Exception $ex){
			echo $ex->__toString();
			return 0;
		}
		return $cnt["CNT"];
	}
    
    /**
    * Get ID_VBDEN by ID_HSCV
    */
    static function getIdVbdenByIdhscv($id_hscv){
        global $db;
		$param = Array();
		$sql = "SELECT ID_VBDEN FROM ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." where ID_HSCV = ?";
		$param[] = $id_hscv;
		try{
			$cmd = $db->query($sql,$param);
			$rs = $cmd->fetch();
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		return $rs["ID_VBDEN"];
    }
    /**
    * Get ID_VBDI by ID_HSCV
    */
    static function getIdVbdiByIdhscv($id_hscv){
        global $db;
		$param = Array();
		$sql = "SELECT ID_VBDI FROM ".QLVBDHCommon::Table("vbdi_vanbandi")." where ID_HSCV = ?";
		$param[] = $id_hscv;
		try{
			$cmd = $db->query($sql,$param);
			$rs = $cmd->fetch();
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		return $rs["ID_VBDI"];
    }
    
    /**
    * Get ID_VBDI by ID_HSCV
    */
    static function getIdDuthaoByIdhscv($id_hscv){
        global $db;
		$param = Array();
		$sql = "SELECT ID_DUTHAO FROM ".QLVBDHCommon::Table("hscv_duthao")." where ID_HSCV = ?";
		$param[] = $id_hscv;
		try{
			$cmd = $db->query($sql,$param);
			$rs = $cmd->fetch();
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		return $rs["ID_DUTHAO"];
    }
    
	static function SelectAll($parameter,$offset,$limit,$order){
		global $db;
		$param = Array();
		//Check ten cong viec
		if($parameter['NAME']!=""){
			$where .= " and hscv.NAME LIKE ?";
			$param[]="%".$parameter['NAME']."%";
		}
		//Check ngay bd
		if($parameter['NGAY_BD']!=""){
			$ngaybd = $parameter['NGAY_BD']." 00:00:01";
			$where .= " and hscv.NGAY_BD >= ?";
			$param[]=$parameter['NGAY_BD'];
		}
		//Check ngay kt
		if($parameter['NGAY_KT']!=""){
			$ngaykt = $parameter['NGAY_KT']." 23:59:59";
			$where .= " and hscv.NGAY_BD <= ?";
			$param[]=$parameter['NGAY_KT'];
		}
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql = "
			SELECT hscv.*,luu.ID_LUUTD, class1.ALIAS FROM 
			".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv 
			inner join ".QLVBDHCommon::Table("wf_processitems")." wfitem on hscv.ID_PI = wfitem.ID_PI
			inner join ".QLVBDHCommon::Table("HSCV_HOSOLUUTHEODOI")." luu on hscv.ID_HSCV = luu.ID_HSCV
			inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV
				inner join WF_PROCESSES wfp1 on wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class1 on class1.ID_C = wfp1.ID_C
			where
				hscv.IS_THEODOI = 1
				$where
				and luu.U_OWN = ?
			ORDER BY DATE_CREATE DESC
			$strlimit
		";
		$param[] = $parameter['ID_U'];
		try{
			//echo $sql;
			$r = $db->query($sql,$param);
			$result = $r->fetchAll();
			//var_dump($param);
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
	}
	static function getByHSCVId($hscvid){
		global $db;
		
		$sql="
			SELECT * FROM ".QLVBDHCommon::Table("hscv_hosoluutheodoi")." WHERE ID_HSCV=?
		";
		$r = $db->query($sql,$hscvid);
		return $r->fetch();
	}
	static function addNew($year,$id_hscv,$commnent,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//check ton tai
		$luutd = HosoluutheodoiModel::getByHSCVId($id_hscv);
		//var_dump($luutd);exit;
		if($luutd['ID_HSCV']>0){
			$sql="
				UPDATE ".QLVBDHCommon::Table("hscv_hosoluutheodoi")." SET `U_OWN`=?, `ID_HSCV`=?,`COMMENT`=?,`DATE_CREATE`='".date("Y-m-d H:i:s")."' where `ID_HSCV` = ".$luutd['ID_HSCV']."
			";
		}else{
			$sql="
				Insert into ".QLVBDHCommon::Table("hscv_hosoluutheodoi")." (`U_OWN`,`ID_HSCV`,`COMMENT`,`DATE_CREATE`) values (?,?,?,'".date("Y-m-d H:i:s")."') 
			";
		}
		try{
			//echo $sql;exit;
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_u,$id_hscv,$commnent));
			return $dbAdapter->lastInsertId(QLVBDHCommon::Table("hscv_hosoluutheodoi"),'ID_LUUTD');
		}catch(Exception $ex){
			return 0;
		}
	}
	
	static function luuTheodoi($year,$id_hscv,$comment,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//cap nhat co luu theo hoi cua ho so cong viec
		//kiem tra ho so cong viec co ton tai hay khong
		$sql = "
		select count(*) as DEM  from ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv where ID_HSCV=?
		";
		
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetch();
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		$c_hscv = $re["DEM"];
		
		
		if($c_hscv == 0){
			//ho so cong viec khong ton tai
			return -1;
		}
		
		$sql = "
		select IS_THEODOI  from ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv where ID_HSCV=?
		";
		
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetch();
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		
		
		$is_theodoi = $re["IS_THEODOI"];
		
		//if($is_theodoi == 1){
			//ho so cong viec da o trang thai dang theo doi
			//return -2;
		//}
		
		$sql ="
			Update ".QLVBDHCommon::Table("hscv_hosocongviec")." set `IS_THEODOI`=1 where ID_HSCV=?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_hscv));
			$vbd = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbd->findByHscv($id_hscv);
			if($vbden['ID_VBDEN']>0){
				$dbAdapter->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>($vbden['NGAYBATDAU']!=""?QLVBDHCommon::getTreHan($vbden['NGAYBATDAU'],$vbden['HANXULYTOANBO']):"0"),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
			}
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		
		//them moi vao bang ho so luu theo doi
		return HosoluutheodoiModel::addNew($year,$id_hscv,$comment,$id_u);
	}
	
	static function phuchoiluuTheodoi($id_luutd,$idu){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//lay thong tin ve ho so luu theo doi
		$sql ="
			select hs.* from ".QLVBDHCommon::Table("HSCV_HOSOLUUTHEODOI")." hs where ID_LUUTD=? and U_OWN=?
		";
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_luutd,$idu));
			$re = $query->fetch();
			
		}catch (Exception $ex){
			//loi sql 
			return -3;
		}
		if(count($re) == 0){
			//khong tim thay ho so luu theo doi
			return -1;
		}
		if(!($re["ID_HSCV"] > 0)){
			//khong tim thay ho so cong viec tuong ung
			return -2;
		}
		try{
			$dbAdapter->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_THEODOI"=>0),"ID_HSCV=".$re["ID_HSCV"]);
			$vbd = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbd->findByHscv($re["ID_HSCV"]);
			if($vbden['ID_VBDEN']>0){
				$dbAdapter->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>0,"IS_FINISH"=>0),"ID_VBD=".$vbden['ID_VBDEN']);
			}

			
		}catch(Exception $ex){
			//echo $ex;
			return -3;
		}
		
		//xoa ho so luu theo doi trong danh sach
		try{
			$dbAdapter->delete(QLVBDHCommon::Table("HSCV_HOSOLUUTHEODOI"),"ID_LUUTD=".$re["ID_LUUTD"]);
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
	}
        
        function getVBDenLuuTheoDoi($soden,$sokihieu,$idcoquan){
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $sql ="
                    select * from ".QLVBDHCommon::Table("vbd_vanbanden")." where SODEN=? and SOKYHIEU LIKE '%".$sokihieu."%' and ID_CQ=?
            ";
            $query = $dbAdapter->query($sql,array($soden,$idcoquan));
            $re = $query->fetch();
            return $re;
        }
        function getVBDiLuuTheoDoi($soden,$sokihieu,$idcoquan){
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $sql ="
                    select * from ".QLVBDHCommon::Table("vbdi_vanbandi")." where SODI=? and SOKYHIEU LIKE '%".$sokihieu."%' and ID_CQ=?
            ";
            $query = $dbAdapter->query($sql,array($soden,$idcoquan));
            $re = $query->fetch();
            return $re;
        }
        function getVBNgoaiLuuTheoDoi($id_object){
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $sql ="
                    select * from ".QLVBDHCommon::Table("hscv_vblienquan")." vblq 
                    inner join ".QLVBDHCommon::Table("gen_filedinhkem")." dk on dk.ID_OBJECT = vblq.ID_VBLQ
                    where vblq.`ID_VBLQ` =?  AND dk.TYPE=4
            ";
            $query = $dbAdapter->query($sql,array($id_object));
            $re = $query->fetch();
            return $re;
        }
}
