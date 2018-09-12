<?php

/**
 * vbd_dongluanchuyenModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class vbdi_dongluanchuyenModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'vbdi_dongluanchuyen_2013';
	/**
	 * construct
	 * @param $year
	 */
	function __construct($year){
		if(isset($year))
			$this->_name ='vbdi_dongluanchuyen_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
	function send($idvbi,$arridu,$noidung,$idnguoichuyen){
		global $db;
		$this->getDefaultAdapter()->beginTransaction();
		try{
			$r = $db->query("SELECT TRICHYEU FROM ".QLVBDHCommon::Table("VBDI_VANBANDI")." WHERE ID_VBDI=?",$idvbi);
			$r = $r->fetch();
			foreach($arridu as $idu){
				$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$idu,"ID_VBDI"=>$idvbi,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung));

				QLVBDHCommon::SendMessage(
					$idnguoichuyen,
					$idu,
					$r["TRICHYEU"],
					"vbdi/banhanh/list"
				);
			}
			$this->getDefaultAdapter()->commit();
		}catch(Exception $ex){
			echo $ex->__toString();
			$this->getDefaultAdapter()->rollBack();
		}
	}
	static function way($year,$idvbi){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$r = $dbAdapter->query("
			SELECT
				lc.*,
				concat(nc.FIRSTNAME,' ',nc.LASTNAME) as EMPNC,
				concat(nn.FIRSTNAME,' ',nn.LASTNAME) as EMPNN,
				nn.ID_DEP as DEP_NN
			FROM
				vbdi_dongluanchuyen_$year lc
				inner join QTHT_USERS unc on lc.NGUOICHUYEN = unc.ID_U
				inner join QTHT_USERS unn on lc.NGUOINHAN = unn.ID_U
				inner join QTHT_EMPLOYEES nc on nc.ID_EMP = unc.ID_EMP
				inner join QTHT_EMPLOYEES nn on nn.ID_EMP = unn.ID_EMP
			WHERE
				ID_VBDI = ?
			ORDER BY
				ID_VBDI_DLC DESC
		",array($idvbi));
		return $r->fetchAll();
	}
	/**
	 * findall NGUOINHAN
	 *
	 * @return array
	 */
	function findAllNguoiNhan($id)
	{
		$r = $this->getDefaultAdapter()->query("
		SELECT
			".$this->_name.".*,U.ID_U AS ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NGUOINHAN
		FROM				
			$this->_name
		INNER JOIN QTHT_USERS U ON U.ID_U = ".$this->_name.".NGUOINHAN
		INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP=U.ID_EMP
		WHERE ".$this->_name.".ID_VBDI = ?",array($id));		
		return $r->fetchAll();
	}
	
	static function getSoKyHieuVbdi($year,$id_vbdi){
		$sql ="
		Select SOKYHIEU from 
		`vbdi_vanbandi_$year` 
		where `ID_VBDI`=?    
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_vbdi));
			$re = $stm->fetch();
			if(count($re) > 0){
				return $re["SOKYHIEU"];
			}else{ //khong co van ban di nao
				return "";
			}
		}catch (Exception $ex){
			//loi co so du lieu
			return "";
		}
	}
	static function getVbdiChuaXemByIdUser($year,$id_u,$offset,$limit){
		$arr_result = array();
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql ="
		Select DISTINCT `ID_VBDI` , 
		`NGUOICHUYEN`
		from 
		`vbdi_dongluanchuyen_$year` 
		where `NGUOINHAN`=? and `DA_XEM`=0 $strlimit
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_u));
			$re_ids = $stm->fetchAll();
			if(count($re_ids) > 0){
				//co it nhat mot van ban di
				foreach ($re_ids as $id_vbdi){
					//Lay trich yeu van ban di
					$sokyhieu = vbdi_dongluanchuyenModel::getSoKyHieuVbdi($year,$id_vbdi["ID_VBDI"]);
					if($sokyhieu != ""){
						$arr_item = array();
						$arr_item["ID_VBDI"] = $id_vbdi["ID_VBDI"];
						$arr_item["SOKYHIEU"] = $sokyhieu;
						$arr_item["NGUOICHUYEN"] = $id_vbdi["NGUOICHUYEN"];
						array_push($arr_result,$arr_item);
					}
				}
			}else{ //khong co van ban di nao
				
			}
		}catch (Exception $ex){
			//loi co so du lieu
		}
		return $arr_result;
	}
	
	static function getIdVbdiChuaXemByIdUser($year,$id_u){
		$arr_result = array();
		$sql ="
		Select DISTINCT `ID_VBDI` , 
		`NGUOICHUYEN`
		from 
		`vbdi_dongluanchuyen_$year` 
		where `NGUOINHAN`=?  and `DA_XEM` =?  
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_u,0));
			$re_ids = $stm->fetchAll();
			if(count($re_ids) > 0){
				//co it nhat mot van ban di
				foreach ($re_ids as $id_vbdi){
					array_push($arr_result,$id_vbdi["ID_VBDI"]);
				}
			}else{ //khong co van ban di nao
				
			}
		}catch (Exception $ex){
			//loi co so du lieu
		}
		return $arr_result;
	}
	static function updateDaDoc($year,$id_vbdi,$id_u){
		$sql ="
		update
		`vbdi_dongluanchuyen_$year` 
		set `DA_XEM`=?
		where `NGUOINHAN`=?  and `ID_VBDI` =?  
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array(1,$id_u,$id_vbdi));
		}catch (Exception $ex){
			//loi co so du lieu
			return 0;
		}
		return 1;
	}
	
}

