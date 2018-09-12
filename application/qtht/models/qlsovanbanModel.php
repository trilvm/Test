<?php

class qlsovanbanModel{
		static function themThongso($type, $code,$des)
		{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			if($code == "YEAR_SYS" ){
				$sql = 'INSERT INTO qtht_config_sovanban(`TYPE`,`CODE`,`DESCRIPTION`,`ON_DB`)
				VALUE (?,?,?,0)';
			}
			else{
				$sql = 'INSERT INTO qtht_config_sovanban(`TYPE`,`CODE`,`DESCRIPTION`)
				VALUE (?,?,?)';
			}
			$arr_value = array($type,$code,$des);
			$stmt = $dbAdapter->prepare($sql,$arr_value);
			$stmt->execute($arr_value);
		}
		
		static function getDataByTypevb($type){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "select * from qtht_config_sovanban where TYPE=?";
			$query = $dbAdapter->query($sql,array($type));
			return $query->fetchAll();
		}
		
		static function updateIs_selected($id,$type){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			
			$sql = 'UPDATE  qtht_config_sovanban
			SET `IS_SELECTED` = 0 WHERE `TYPE` = ?';
			$arr_value = array($type);
			$stmt = $dbAdapter->prepare($sql,$arr_value);
			$stmt->execute($arr_value);
			
			$arr_value = array($id);
			$sql = 'UPDATE  qtht_config_sovanban
			SET `IS_SELECTED` = 1 WHERE `ID_CONFIG_SOVANBAN`=? ';
			//$arr_value = array($id);
			
			$stmt = $dbAdapter->prepare($sql);
			
			$stmt->execute($arr_value);
		}
}
?>