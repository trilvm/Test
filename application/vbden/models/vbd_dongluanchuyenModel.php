<?php

/**
 * vbd_dongluanchuyenModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class vbd_dongluanchuyenModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'vbd_dongluanchuyen_2013';
	/**
	 * construct
	 * @param $year
	 */
	function __construct($year){
		if(isset($year))
			$this->_name ='vbd_dongluanchuyen_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
	function send($idvbd,$arridu,$noidung,$idnguoichuyen,$daxem,$param,$sms,$email,$isphoihop){
		global $db;
		//$this->getDefaultAdapter()->beginTransaction();
		//var_dump($email);exit;
		try{
			if($arridu){
				$r = $db->query("SELECT TRICHYEU FROM ".QLVBDHCommon::Table("VBD_VANBANDEN")." WHERE ID_VBD=?",$idvbd);
				$r = $r->fetch();
				if(is_array($sms) && is_array($email)){
					for($i=0;$i<count($arridu);$i++){					
						if($daxem!=1){
							$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$arridu[$i],"ID_VBD"=>$idvbd,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung,"TRANGTHAI"=>0,"SMS"=>$sms[$i],"EMAIL"=>$email[$i],"ISPHOIHOP"=>$isphoihop));
							QLVBDHCommon::SendMessage(
								$idnguoichuyen,
								$arridu[$i],
								$r["TRICHYEU"],
								"vbden/vbden/list"
							);
						}else{
							$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$arridu[$i],"ID_VBD"=>$idvbd,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung,"TRANGTHAI"=>0,"DA_XEM"=>1,"ISPHOIHOP"=>$isphoihop));
						}
					}
				}else{
					foreach($arridu as $idu){					
						if($daxem!=1){
							$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$idu,"ID_VBD"=>$idvbd,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung,"TRANGTHAI"=>0,"SMS"=>(int)$param["sms_$idu"],"EMAIL"=>(int)$param["email_$idu"],"ISPHOIHOP"=>$isphoihop));
							QLVBDHCommon::SendMessage(
								$idnguoichuyen,
								$idu,
								$r["TRICHYEU"],
								"vbden/vbden/list"
							);
						}else{
							$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$idu,"ID_VBD"=>$idvbd,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung,"TRANGTHAI"=>0,"DA_XEM"=>1,"ISPHOIHOP"=>$isphoihop));
						}
					}
				}
			//$this->getDefaultAdapter()->commit();
			}
		}catch(Exception $ex){
			echo $ex->__toString();
			//$this->getDefaultAdapter()->rollBack();
		}
	}
	function way($idvbd){
		$r = $this->getDefaultAdapter()->query("
			SELECT
				lc.*,
				concat(nc.FIRSTNAME,' ',nc.LASTNAME) as EMPNC,
				concat(nn.FIRSTNAME,' ',nn.LASTNAME) as EMPNN,
				nn.ID_DEP as DEP_NN
			FROM
				$this->_name lc
				inner join QTHT_USERS unc on lc.NGUOICHUYEN = unc.ID_U
				inner join QTHT_USERS unn on lc.NGUOINHAN = unn.ID_U
				inner join QTHT_EMPLOYEES nc on nc.ID_EMP = unc.ID_EMP
				inner join QTHT_EMPLOYEES nn on nn.ID_EMP = unn.ID_EMP
			WHERE
				ID_VBD = ?
			ORDER BY
				ID_DLC DESC
		",array($idvbd));
		return $r->fetchAll();
	}
	
	static function getSoKyHieuVbden($year,$id_vbd){
		$sql ="
		Select SOKYHIEU from 
		`vbd_vanbanden_$year` 
		where `ID_VBD`=?    
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_vbd));
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
	static function getVbdenChuaXemByIdUser($year,$id_u,$offset,$limit){
		$arr_result = array();
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql ="
		Select  lc.`ID_VBD` , 
		lc.`NGUOICHUYEN`
		from 
		`vbd_dongluanchuyen_$year`  lc
		where `NGUOINHAN`=? and `DA_XEM`=0  
		group by ID_VBD $strlimit
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_u));
			$re_ids = $stm->fetchAll();
			
			if(count($re_ids) > 0){
				//co it nhat mot van ban di
				foreach ($re_ids as $id_vbd){
					//Lay trich yeu van ban di
					$sokyhieu = vbd_dongluanchuyenModel::getSoKyHieuVbden($year,$id_vbd["ID_VBD"]);
					if($sokyhieu != ""){
						$arr_item = array();
						$arr_item["ID_VBD"] = $id_vbd["ID_VBD"];
						$arr_item["SOKYHIEU"] = $sokyhieu;
						$arr_item["NGUOICHUYEN"] = $id_vbd["NGUOICHUYEN"];
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
	
	static function getIdVbdenChuaXemByIdUser($year,$id_u){
		$arr_result = array();
		$sql ="
		Select DISTINCT `ID_VBD`
		from 
		`vbd_dongluanchuyen_$year` 
		where `NGUOINHAN`=? and `DA_XEM`=0   
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($id_u));
			$re_ids = $stm->fetchAll();
			if(count($re_ids) > 0){
				//co it nhat mot van ban di
				foreach ($re_ids as $id_vbd){
					array_push($arr_result,$id_vbd["ID_VBD"]);
					
				}
			}else{ //khong co van ban di nao
				
			}
		}catch (Exception $ex){
			//loi co so du lieu
		}
		return $arr_result;
	}
	
	static function updateDaDoc($year,$id_vbd,$id_u){
		$sql ="
		update
		`vbd_dongluanchuyen_$year` 
		set `DA_XEM`=?
		where `NGUOINHAN`=?  and `ID_VBD` =?  
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array(1,$id_u,$id_vbd));
		}catch (Exception $ex){
			//loi co so du lieu
			return 0;
		}
		return 1;
	}

	static function getNguoichuyenbyidhscv($idhscv){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select wf.`ID_U_SEND` from ".QLVBDHCommon::Table("wf_processlogs")."  wf
					inner join ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on wf.`ID_PI`=hscv.`ID_PI`
					where hscv.`ID_HSCV`=? and wf.`ID_U_RECEIVE`=0
							";
		$query = $dbAdapter->query($sql,array($idhscv));
		$re = $query->fetch();		
		return  $re['ID_U_SEND'];
	}
	
}

