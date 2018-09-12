<?php

require_once ('Zend/Db/Table/Abstract.php');

class capcoquanModel extends Zend_Db_Table_Abstract {

	var $_name = "vb_capcoquan";
	
	
	public static function toComboName($arraySelected){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME`,`ID_CAPCOQUAN` from `vb_capcoquan` ";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();

		foreach ($re as $row){
			echo "<option ".$arraySelected[$row['ID_CAPCOQUAN']]." value=".$row['ID_CAPCOQUAN'].">".$row['NAME']."</option>";
		}
	}
	
	public function getAllCapCQ(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME`,`ID_CAPCOQUAN` from `vb_capcoquan` ";
		$query = $dbAdapter->query($sql);
		return $query->fetchAll();
	}
	
}

?>
