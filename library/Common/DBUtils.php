<?php 
class Common_DBUtils {
	static  public function repairTableBeforeFulltextSearch($table_name){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " REPAIR TABLE ".$table_name." QUICK";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute();
		}catch (Exception  $ex){
			return -1;
		}
		return 1;
	}
}
?>