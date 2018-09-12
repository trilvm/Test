<?php

require_once ('Zend/Db/Table/Abstract.php');

class motcua_nhangomModel extends Zend_Db_Table_Abstract {
	function __construct($year){
		$this->_name = 'motcua_nhan_gom_'.$year;
		$arr = array();
		parent::__construct($arr);
	}
	function getTaiLieuByIdHSMC($idHSMC){
		try{
			$se = $this->select()->where('ID_HOSO=?',$idHSMC);
			return  $this->fetchAll($se);
		}catch(Exception $ex){
			return array();
		}
	}
	function getThutucDanhanByHoso($id_hscv){
		$sql = "  select dn.* from ". QLVBDHCommon::Table("motcua_nhan_gom")." dn 
		inner join ". QLVBDHCommon::Table("motcua_hoso")." hs on dn.ID_HOSO = hs.ID_HOSO 
		where hs.ID_HSCV = ? ";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql, array($id_hscv));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
		
}

?>
