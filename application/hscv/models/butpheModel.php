<?php

/**
 * butpheModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class butpheModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'hscv_butphe_2013';

	/**
	 * Ham khoi tao theo nam
	 *
	 * @param unknown_type $year
	 */
	function __construct($year){
		$this->_name ='hscv_butphe'.'_'.$year;
		$arr = array();
		parent::__construct($arr);
	}
	function SelectByIdHSCV($idhscv){
		$r = $this->getDefaultAdapter()->query(
			"
				SELECT
					concat(e.FIRSTNAME,' ',e.LASTNAME) as EMPNAME,
					bp.ID_BP,
					bp.NOIDUNG,
					bp.HANXULY,
					bp.NGAYBP,
					bp.NGUOICHUYEN
				FROM
					".$this->_name." bp
					inner join ".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")." fk on fk.ID_VBDEN = bp.ID_VBD
					inner join QTHT_USERS u on bp.NGUOIKY = u.ID_U
					inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP
				WHERE
					fk.ID_HSCV = ?
			",
			array($idhscv)
		);
		return $r->fetchAll();
	}
	function SelectByIdVBD($idvbd){
		$r = $this->getDefaultAdapter()->query(
			"
				SELECT
					concat(e.FIRSTNAME,' ',e.LASTNAME) as EMPNAME,
					bp.NOIDUNG,
					bp.HANXULY,
					bp.NGAYBP,
					bp.NGUOICHUYEN
				FROM
					".$this->_name." bp
					inner join ".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")." fk on fk.ID_VBDEN = bp.ID_VBD
					inner join QTHT_USERS u on bp.NGUOIKY = u.ID_U
					inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP
				WHERE
					fk.ID_VBDEN = ? 
			",
			array($idvbd)
		);
		return $r->fetchAll();
	}
	
	static function getNguoiButPhe($id)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$sql = "SELECT CONCAT(e.FIRSTNAME, ' ', e.LASTNAME) as NGUOIBUTPHE FROM 
				qtht_users u
				INNER JOIN qtht_employees e ON u.ID_EMP = e.ID_EMP
				WHERE u.ID_U=?";
		$result = $dbAdapter->query($sql, $id)->fetch();
		return $result['NGUOIBUTPHE'];
	}
}
