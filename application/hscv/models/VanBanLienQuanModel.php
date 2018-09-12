<?php
/**
 * @author trunglv
 * @version 1.0
 */
require_once ('Zend/Db/Table/Abstract.php');
require_once 'vbdi/models/VanBanDiModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';

class VanBanLienQuanModel extends Zend_Db_Table_Abstract {
	/**
	 * Ham khoi tao 
	 *
	 * @param unknown_type $year
	 */
	function __construct($year){
		$this->_name = 'hscv_vblienquan_'.$year;
		$this->_year = $year;
		$arr = array();
		parent::__construct($arr);
	}
	/**
	 * Ham lay danh sach cac van ban lien quan cua ho so cong viec
	 *
	 * @param unknown_type $idHSCV
	 * @param unknown_type $year
	 * @return unknown
	 */
	function getListByIdHSCV($idHSCV,$year){
		/*$sql ="select   vblq.`ID_HSCV` , vblq.`ID_VBLQ` ,vblq.`TYPE`,
			vbdi.`TRICHYEU` , vbdi.`SOKYHIEU` ,vbdi.`ID_VBDI` as IDVB from
					`vbdi_vanbandi_2009` vbdi INNER join `hscv_vblienquan_2009` vblq
			where   vblq.`ID_OBJECT` = vbdi.`ID_VBDI` and vblq.`ID_HSCV`= ?	and vblq.`TYPE` = 1
			
			UNION
			
			select   vblq.`ID_HSCV` , vblq.`ID_VBLQ` ,vblq.`TYPE`,
			vbd.`TRICHYEU` , vbd.`SOKYHIEU` , vbd.`ID_VBD` as MSVB from
					`vbd_vanbanden_2009` vbd INNER join `hscv_vblienquan_2009` vblq
			where   vblq.`ID_OBJECT` = vbd.`ID_VBD` and vblq.`ID_HSCV`= ? and vblq.`TYPE` = 0";*/
		$sql = 'select vblq.`ID_HSCV` , vblq.`ID_VBLQ` , vblq.`NAME` , vblq.`ISSYSTEM` 
		from `hscv_vblienquan_'.$year.'` vblq where vblq.`ID_HSCV` = ?
		';
		$arrdata = array($idHSCV);
		$query = $this->getDefaultAdapter()->query($sql,$arrdata);
		return $query->fetchAll();
		
	}
	/**
	 * Tim kiem van ban theo trich yeu va so ky hieu
	 *
	 * @param unknown_type $idHSCV
	 * @param unknown_type $year
	 * @param unknown_type $type //0 - van ban den , 1 - van ban di
	 * @param unknown_type $trichyeu
	 * @param unknown_type $sokyhieu
	 * @param unknown_type $tieuchi //0-Tim tat ca , 1-Chi tim theo trich yeu , 2-Chi tim theo so hieu van ban 
	 * @return unknown
	 */
	function findListVanBan($idHSCV,$year,$type,$trichyeu,$sokyhieu,$tieuchi){
		
		//$user = Zend_Registry::get('auth')->getIdentity();
		//var_dump($user); exit;
		$hscvModel = new hosocongviecModel();  
		
		$parameter = array(
			"TRICHYEU"=>$trichyeu,
			"SOKYHIEU"=>$sokyhieu
			
		);
		if($trichyeu == "" && $sokyhieu == "")
			return array();
		if(!$type || $type == 0){
			//$hscvcount = $hscvModel->Count_vbd($parameter);
			return $hscvModel->SelectAll_vbd($parameter,0,-1,"NGAYDEN DESC");
		}else{
			//$hscvcount = $hscvModel->Count_vbdi($parameter);
			return $hscvModel->SelectAll_vbdi($parameter,0,-1,"NGAYBANHANH DESC");
		}

		

		
		/*$re = null;
		$false = "0=1";
		$tim_sokyhieu = "vb.`SOKYHIEU` = ?";
		$tim_trichyeu = "(match(vb.`TRICHYEU`) against (? IN BOOLEAN MODE)>0)"; 
		$sql = "";
		$arrdata = array();
		$vbanName = "";
		$id_name = "";
		if(!$type || $type == 0){ // loai van ban den
			$vbanName = "`vbd_vanbanden_".$year."`";
			$id_name = "`ID_VBD`";
		}else{ // loai van ban di
			$id_name = "`ID_VBDI`";
			$vbanName = "`vbdi_vanbandi_".$year."`";
		}
		switch($tieuchi){
			case 0: //tim tat ca
				if($trichyeu == '' && $sokyhieu == '' )
					return null;
				$arrdata = array($sokyhieu,$trichyeu);
				break;
			case 1: //chi tim theo trich yeu 
				if($trichyeu == '')
					return null;
				$tim_sokyhieu = $false;
				$arrdata = array($trichyeu);
				break;
			case 2: // tim theo so ky hieu
				$tim_trichyeu = $false;
				$arrdata = array($sokyhieu);
				break;				
		}
			
		$sql= "select  vb.".$id_name." as IDVB,vb.`TRICHYEU` , vb.`SOKYHIEU` from
		".$vbanName." vb where (".$tim_sokyhieu." or ".$tim_trichyeu.")"
		;
		$query = $this->getDefaultAdapter()->query($sql,$arrdata);
		return  $query->fetchAll();*/
		
	}
	/**
	 * Ham them moi mot van ban lien quan vao database
	 *
	 * @param unknown_type $idHSCV  // ID cua ho so cong viec
	 * @param unknown_type $idObject // ID cua doi tuong van ban
	 * @param unknown_type $type //Loai van ban (0- van ban den , 1 - van ban di )
	 */
	function insertOneOnSys($year,$idHSCV,$idObject,$type){
		/*$arrdata = array(
		'ID_HSCV'=>$idHSCV,
		'ID_OBJECT' =>$idObject,
		'TYPE' =>$type
		);
		$this->insert($arrdata);*/
		$col_nameSo = '';
		$TYPE_DB = 4;
		if($type == 0){ //vanbanden
        //lay thong so cua van ban den
			$sql = 'select * from `vbd_vanbanden_'.$year.'`
			where `ID_VBD` = ?
			';
			$col_nameSo='SODEN';
			$TYPE_DB = 3;
		}else{
			$sql = 'select * from `vbdi_vanbandi_'.$year.'`
			where `ID_VBDI` = ?
			';
			$col_nameSo='SODI';
			$TYPE_DB =5;
		}
		//echo $sql;
		//exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query =  $dbAdapter->query($sql,array($idObject));
		$re = $query->fetch();
		$kyhieu = $re['SOKYHIEU'];
		if($re['NGAYBANHANH'] != "")
		$ngaybanhanh = QLVBDHCommon::MysqlDateToVnDate($re['NGAYBANHANH']);
		$id_cq = $re['ID_CQ'];
		if($type == 0)
			$so = $re["SODEN"];
		else 
			$so = $re["SODI"];
		//lay ten van ban lien quan theo so, ky hieu, ngay ban hanh, co quan ban hanh ,
		$name_vanban= $so."-".$kyhieu."-".$ngaybanhanh."-".$id_cq;
		//Luu ten van ban vao
		$sql_save = 'INSERT INTO `hscv_vblienquan_'.$year.'`(`ID_HSCV`,`NAME`) VALUES (?,?)';
		$stm = $dbAdapter->prepare($sql_save);
		$stm->execute(array($idHSCV,$name_vanban));
		$idvblq = $dbAdapter->lastInsertId();
		//echo 
		filedinhkemModel::copyFile($year,$idObject,$idvblq ,$TYPE_DB,4);
		
	}
	/**
	 * Ham xoa mot van ban lien quan trong database
	 *
	 * @param unknown_type $id_vblq //ID cua van ban lien qua
	 */
	function deleteOne($id_vblq){
		$where = 'ID_VBLQ='.$id_vblq;
		$this->delete($where);
		//xoa cac file dinh kem co lien quan
		filedinhkemModel::deleteFileByObject($this->_year,$id_vblq,4);
	}
	function deleteByHscv($id_hscv){
		$qr = $this->getDefaultAdapter()->query(
		"
			select ID_VBLQ from hscv_vblienquan_$this->_year where ID_HSCV=?
		"
		,array($id_hscv));
		$data_dt = $qr->fetchAll();
		foreach ($data_dt as $item){
			$this->deleteOne($item["ID_VBLQ"]);
		}
	}
}

?>
