<?php

require_once ('Zend/Db/Table/Abstract.php');

class CoQuanModel extends Zend_Db_Table_Abstract {

	var $_name = "vb_coquan";
	
	public static function returnCapCoQuan($cap)
	{
		switch ($cap)
		{
			case 0:
				return 'TW';
				break;
			case 1:
				return 'Tỉnh Thành';
				break;
			case 2:
				return 'Quận/Huyện';
				break;
			case 3:
				return 'Phường/Xã';
				break;
			default:
				return 'Không biêt cấp';
				break;
	}
	}
	
	public static function toComboName($type){
		
		if(is_null($type))
			$type = 1;
		$is_sys = 0;
		if($type == 2)
			$is_sys = 1;
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `CODE`,`NAME`,`ID_CQ` from `vb_coquan` 
		where `ISSYSTEMCQ` = ?
		";
		$query = $dbAdapter->query($sql,array($is_sys));
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['CODE']."' value=".$row['ID_CQ'].">".$row['NAME']."</option>";
		}
	}
	
	//lay ten co quan theo id
	public static function getNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME` from `vb_coquan` 
		where `ID_CQ` = ?
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetch();
		return $re["NAME"];
	}
	/**
	 * tao combox co quan ban hanh tuy theo loi van ban
	 *
	 */
	
	public static function getData($type){
		
		if(is_null($type))
			$type = 1;
		$is_sys = 0;
		if($type == 2)
			$is_sys = 1;
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "select `CODE`,`NAME`,`ID_CQ` from `vb_coquan` 
		where `ISSYSTEMCQ` = ?
		";
		try{
			$query = $dbAdapter->query($sql,array($is_sys));
			return  $query->fetchAll();
		}catch (Exception $ex){
			return array();	
		}
	}
static function toComboName1(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select * from `vb_coquan`" ;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['CODE']."' value=".$row['ID_CQ'].">".$row['NAME']."</option>";
		}
	}

    public function getAllCoQuan(){
        $rs = array();
        $r = $this->getDefaultAdapter()->query("
			SELECT
     			CODE
			FROM
				vb_coquan where CODE <> '' ");
		while ($row = $r->fetch()) {
            $rs[] = $row['CODE'];
        }
        return $rs;
        
    }
    public function getAllCoQuanName(){
        $rs = array();
        $r = $this->getDefaultAdapter()->query("
			SELECT
				ID_CQ,
				ISSYSTEMCQ,
				CAPCQ,
     			NAME
			FROM
				vb_coquan ORDER BY NAME");
		return $r->fetchAll();
        
    }
        public static function  getNameLienThongByCode($code){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME` from `vb_coquan` 
		where `LIENTHONG` = 1 AND CODE = ?
		";
		$query = $dbAdapter->query($sql,array($code));
		$re = $query->fetch();
		return $re["NAME"];
	}
}

?>
