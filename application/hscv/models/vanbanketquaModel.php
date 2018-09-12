<?php 
class vanbanketquaModel{
	
	function add($params){
	
		$parameter = array();
		if($params["NGAYBANHANH"]){
			$parameter[] = implode("-",array_reverse(explode("/",$params["NGAYBANHANH"])));
		}else{
			$parameter[] = NULL;
		}
		
		$parameter[] = $params["SOKYHIEU"];
		$parameter[] = $params["NGUOIKY_TEXT"];
		$parameter[] = $params["ID_HSCV"];

		$sql = " insert into " . QLVBDHCommon::Table("motcua_ketqua") . "
		( NGAYBANHANH , SOKYHIEU , NGUOIKY, ID_HSCV)
		values ( ?, ?, ? , ?)
		";

		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute($parameter);

		}catch(Exception $ex){
		
		}
	}

	function delete($id){
		$sql = "delete from " . QLVBDHCommon::Table("motcua_ketqua"). " 
		where ID_KQ_MC = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id));

		}catch(Exception $ex){
		
		}
	}
	
}

?>