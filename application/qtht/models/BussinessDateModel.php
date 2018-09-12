<?php

/**
 * BussinessDateModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class BussinessDateModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'bussinessdate';
	static function IsNonWorkingDate($ngay){
		global $db;
		$nonewkd = new Zend_Session_Namespace('nonewkd');
		$iswkd = new Zend_Session_Namespace('iswkd');
		if(!isset($nonewkd->data)){
			$sql = "
				SELECT * FROM GEN_NONWORKINGDATES
			";
			$r = $db->query($sql);
			$nonewkd->data = $r->fetchAll();
		}
		if(!isset($iswkd->data)){
			//echo "SELECT * FROM GEN_WORKINGDATES";
			$sql = "
				SELECT * FROM GEN_WORKINGDATES
			";
			$r = $db->query($sql);
			$iswkd->data = $r->fetchAll();
		}
		//echo "�";
		//var_dump($iswkd->data);
		foreach($iswkd->data as $item){
			if($item['BEGINDAY']==$ngay['mday'] && $item['BEGINMONTH'] == $ngay['mon'] && $item['BEGINYEAR'] == $ngay['year']) return false;
		}
		foreach($nonewkd->data as $item){
			//echo ($ngay['mday']+$ngay['mon']*31) ."-". ($item['BDAY']+$item['BMON']*31).";";
			if($item['ISCOMMON']==1 && $item['WDAY']==$ngay['wday']){
				return true;
			}else if($item['ISMONTH']==1 && 
			($ngay['mday']+$ngay['mon']*31 >= $item['BDAY']+$item['BMON']*31) && 
			($ngay['mday']+$ngay['mon']*31 <= $item['EDAY']+$item['EMON']*31)){
				return true;
			}
		}
		return false;
	}
	static function IsNonWorkingWDate($ngay){
		global $db;
		$sql = "
			SELECT * FROM GEN_NONWORKINGDATES WHERE	ISCOMMON=1 AND WDAY = ?
		";
		$r = $db->query($sql,array($ngay['wday']));
		if($r->rowCount()>0)return true;
		return false;
	}
	static function insertnonworking($ngay){
		global $db;
		$db->insert("GEN_NONWORKINGDATES",array("BDAY"=>$ngay['mday'],"BMON"=>$ngay['mon'],"EDAY"=>$ngay['mday'],"EMON"=>$ngay['mon'],"ISMONTH"=>1));
		BussinessDateModel::updateSession();
	}
	static function deletenonworking($ngay){
		global $db;
		$db->delete("GEN_NONWORKINGDATES","ISMONTH=1
			AND ".($ngay['mday']+$ngay['mon']*31)." >= (BDAY+BMON*31) AND ".($ngay['mday']+$ngay['mon']*31)." <= (EDAY+EMON*31)");
		BussinessDateModel::updateSession();
	}
	static function insertnonworkingwday($wday){
		global $db;
		$db->insert("GEN_NONWORKINGDATES",array("WDAY"=>$wday,"ISCOMMON"=>1));
		BussinessDateModel::updateSession();
	}
	static function deletenonworkingwday($wday){
		global $db;
		$db->delete("GEN_NONWORKINGDATES","ISCOMMON=1 AND WDAY = ".$wday);
		BussinessDateModel::updateSession();
	}
	static function updateSession(){
		global $db;
		$nonewkd = new Zend_Session_Namespace('nonewkd');
		//if(!isset($nonewkd->data)){
			$sql = "
				SELECT * FROM GEN_NONWORKINGDATES
			";
			$r = $db->query($sql);
			$nonewkd->data = $r->fetchAll();
		//}
		$iswkd = new Zend_Session_Namespace('iswkd');
		$sql = "
				SELECT * FROM GEN_WORKINGDATES
			";
			$r = $db->query($sql);
			$iswkd->data = $r->fetchAll();
	}
}
