<?php
/**
 * @author trunglv
 * @version 1.0
 */
require_once ('Zend/Db/Table/Abstract.php');

class ToTrinh {
	var $_id_tt;
	var $_id_hscv;
	var $_nguoinhan;
	var $_nguoitrinh;
	var $_noidung;
	var $_ngaytrinh;
	var $_ngaynhan;
	var $_trangthai;
	var $_hanxuly;
}

class ToTrinhModel extends Zend_Db_Table_Abstract {
	function __construct($year){
		$this->_name = 'hscv_totrinh_'.$year;
		$config = array();
		parent::__construct($config);
	}
	/**
	 * Lay danh sach cac to trinh tuong ung voi ho so cong viec
	 *
	 * @param unknown_type $idHSCV
	 * @return unknown
	 */
	function getListToTrinhByIdHSCV($idHSCV){
		global $db;
		$sql = "
			SELECT
				pl.ID_U_SEND as NGUOITRINH,pl.DATESEND as NGAYTRINH,pl.HANXULY,pl.NOIDUNG
			FROM
				".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
				inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pl.ID_PI = pi.ID_PI
			WHERE
				ID_O = ?
			ORDER BY ID_PL ASC
		";
		//echo $sql;echo $idHSCV;
		$r = $db->query($sql,$idHSCV);
		return $r->fetchAll();
	}
	function getLastToTrinhByIdHSCV($idHSCV){
		global $db;
		$sql = "
			SELECT
				pl.ID_U_SEND as NGUOITRINH,pl.DATESEND as NGAYTRINH,pl.HANXULY,pl.NOIDUNG
			FROM
				".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
				inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pl.ID_PI = pi.ID_PI
			WHERE
				ID_O = ?
			ORDER BY ID_PL DESC
		";
		$r = $db->query($sql,$idHSCV);
		return $r->fetch();
	}
	/**
	 * Them moi mot to trinh
	 *
	 * @param unknown_type $totrinh_obj
	 */
	function insertOne(ToTrinh $totrinh_obj){
		
		$data = array(
			'ID_TT'=>$totrinh_obj->_id_tt,
			'ID_HSCV'=>$totrinh_obj->_id_hscv,
			'NGUOINHAN'=>$totrinh_obj->_nguoinhan,
			'NGUOITRINH'=>$totrinh_obj->_nguoitrinh,
			'NGAYTRINH'=>$totrinh_obj->_ngaytrinh,
			'NGAYNHAN'=>$totrinh_obj->_ngaynhan,
			'TRANGTHAI'=>$totrinh_obj->_trangthai,
			'NOIDUNG'=>$totrinh_obj->_noidung,
			'HANXULY'=>$totrinh_obj->_hanxuly
		);
		$se = $this->insert($data);
	}
	
}

?>
