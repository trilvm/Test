<?php

require_once ('Zend/Db/Table/Abstract.php');

class PhienBanDuThaoModel extends Zend_Db_Table_Abstract {

		function __construct($year){

		$this->_name ='hscv_phienbanduthao'.'_'.$year;
		$arr = array();
		parent::__construct($arr);
	}

	function getListByIdDuthao($idDuthao,$year){
		//$se = $this->select()->where('ID_DUTHAO=?',$idDuthao);
		//return $this->fetchAll($se);
		
		$arrdata = array($idDuthao);
		$query = $this->getDefaultAdapter()->query('
		select * from
  		`hscv_phienbanduthao_'.$year.'` pbdt  inner join (select *  from `hscv_duthao_'.$year.'` where
  		`ID_DUTHAO`=? ) dt on pbdt.`ID_DUTHAO`= dt.`ID_DUTHAO`
		inner join `gen_filedinhkem_'.$year.'` fdk on fdk.`ID_OBJECT`= pbdt.`ID_PB_DUTHAO` and fdk.`TYPE`=2 
		group by pbdt.`ID_PB_DUTHAO` ORDER BY VERSION DESC',$arrdata);
		return $query->fetchAll();
		
	}
	
/*	function getListByIdDuthao1($idPBDuthao,$year){
		//$se = $this->select()->where('ID_DUTHAO=?',$idDuthao);
		//return $this->fetchAll($se);
		
		$arrdata = array($idPBDuthao);
		$query = $this->getDefaultAdapter()->query('
		select * from
  		`gen_filedinhkem_'.$year.'` fdk  inner join (select*  from `hscv_phienbanduthao_'.$year.'` where
  		`ID_PB_DUTHAO`=? ) pb
			where pb.`ID_PB_DUTHAO` = fdk.`ID_OBJECT` and fdk.`TYPE`=2 
		',$arrdata);
		return $query->fetchAll();
		
	}
	function getListByIdDuthao($idHSCV,$year){
		//$se = $this->select()->where('ID_DUTHAO=?',$idDuthao);
		//return $this->fetchAll($se);
		
		$arrdata = array($idHSCV);
		$query = $this->getDefaultAdapter()->query('
         select * from
  		`hscv_phienbanduthao_'.$year.'` pbdt  inner join (select *  from `hscv_duthao_'.$year.'` where
  		`ID_HSCV`=? ) dt on pbdt.`ID_DUTHAO`= dt.`ID_DUTHAO`					
		',$arrdata);
		return $query->fetchAll();
		
	}
	*/
	
	function insertOne($idDuthao,$version,$idu,$comment){
		$data = array(
		'ID_DUTHAO'=>$idDuthao,
		'VERSION'=>$version,
		'ID_U'=>$idu,
                'COMMENT'=>$comment
		);
		return $this->insert($data);
	}
	
	function getDataById($idPBDuthao,$idDuthao){
		if($idPBDuthao >0){
		$se = $this->select()->where('ID_PB_DUTHAO=?',$idPBDuthao);
		return $this->fetchRow($se);
		}else{
			global $db;
		$sql .= " 
		select max(pbdt.VERSION) as VERSION from ".QLVBDHCommon::table("hscv_phienbanduthao")." pbdt where pbdt.ID_DUTHAO=?
		";
		$query =$db->query($sql,$idDuthao);
		$re=$query->fetch();
         return $re['VERSION']; 
		}
	}
	
	function deleteOneById($idPBDuthao){
		$this->delete('ID_PB_DUTHAO='.$idPBDuthao);
	}
	
	function updateVersion($idPBDuthao,$version,$idu,$comment){
		$where = 'ID_PB_DUTHAO='.$idPBDuthao;
		$data = array('VERSION'=>$version,'ID_U'=>$idu,'COMMENT'=>$comment);
		$this->update($data,$where);
		
	}
	
	public static function getFileContent($idhscv){
		global $db;
		$sql .= " 
		SELECT 
			dk.CONTENT,dk.MASO,dk.FILENAME 
		FROM 
			".QLVBDHCommon::table("hscv_duthao")." dt
			inner join ".QLVBDHCommon::table("hscv_phienbanduthao")." pbdt on pbdt.ID_DUTHAO = dt.ID_DUTHAO
			inner join ".QLVBDHCommon::table("gen_filedinhkem")." dk on dk.ID_OBJECT = pbdt.ID_PB_DUTHAO and dk.TYPE=2
		WHERE
			dt.ID_HSCV = ?
		";
		$query =$db->query($sql,$idhscv);
		return $query->fetchAll();
	}
	public static function getFileContentFromVBD($idvbd){
		global $db;
		$sql .= " 
		SELECT 
			dk.CONTENT,dk.MASO,dk.FILENAME 
		FROM 
			".QLVBDHCommon::table("vbd_vanbanden")." vbd
			inner join ".QLVBDHCommon::table("gen_filedinhkem")." dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3
		WHERE
			vbd.ID_VBD = ?
		";
		$query =$db->query($sql,$idvbd);
		return $query->fetchAll();
	}
	public static function getFileContentFromVBDI($idvbdi){
		global $db;
		$sql .= " 
		SELECT 
			dk.CONTENT,dk.MASO,dk.FILENAME 
		FROM 
			".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
			inner join ".QLVBDHCommon::table("gen_filedinhkem")." dk on dk.ID_OBJECT = vbdi.ID_VBDI and dk.TYPE=5
		WHERE
			vbdi.ID_VBDI = ?
		";
		$query =$db->query($sql,$idvbdi);
		return $query->fetchAll();
	}

	public static function gettongphienbanduthao($id_duthao){
		global $db;
		$sql .= " 
		SELECT 
			count(*) as tong
		FROM 
			".QLVBDHCommon::table("hscv_phienbanduthao")." 
			
		WHERE
			ID_DUTHAO = ?
		";
		$query =$db->query($sql,$id_duthao);
		$r= $query->fetch();
		return $r["tong"];
	}
	public static function gettongphienbanduthaoidu($id_u,$id_duthao){
		global $db;
		$sql .= " 
		SELECT 
			count(*) as tong
		FROM 
			".QLVBDHCommon::table("hscv_phienbanduthao")." 
			
		WHERE
			ID_U = ? and ID_DUTHAO = ?
		";
		$query =$db->query($sql,array($id_u,$id_duthao));
		$r= $query->fetch();
		return $r["tong"];
	}

	public static function GetPhienBanMoiNhat($id_duthao,$limit){
		global $db;
		$sql .= " 
		SELECT
			pb.*,file.MASO,file.FILENAME,file.USER
		FROM
			(SELECT 
				ID_PB_DUTHAO,VERSION,ID_U,CHONBANHANH,count(*) as COUNTFILE, IS_XOA,pb.COMMENT
			FROM 
				".QLVBDHCommon::table("HSCV_PHIENBANDUTHAO")." pb
				LEFT JOIN ".QLVBDHCommon::table("GEN_FILEDINHKEM")." file ON pb.ID_PB_DUTHAO = file.ID_OBJECT AND file.TYPE=2
			WHERE
				ID_DUTHAO = ?
			GROUP BY pb.ID_PB_DUTHAO
			ORDER BY VERSION DESC
			LIMIT ".(int)$limit.") pb
			LEFT JOIN ".QLVBDHCommon::table("GEN_FILEDINHKEM")." file ON pb.ID_PB_DUTHAO = file.ID_OBJECT AND file.TYPE=2
		";
	
		$query =$db->query($sql,$id_duthao);
		$r= $query->fetchAll();
		return $r;
	}

	public static function getidphienbanbyidhscv($idhscv){
		global $db;
		$sql .= " 
		SELECT 
			pbdt.*
		FROM 
			".QLVBDHCommon::table("hscv_duthao")." dt
			inner join ".QLVBDHCommon::table("hscv_phienbanduthao")." pbdt on pbdt.ID_DUTHAO = dt.ID_DUTHAO		
		WHERE
			dt.ID_HSCV = ?
		";		
		$query =$db->query($sql,$idhscv);
		return $query->fetchAll();
	}
}

?>
