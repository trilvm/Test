<?php
class motcua_thutuc_bosungModel {

	
	function getByIdYeuCau($id_yeucau){
		$sql = " select * from " . QLVBDHCommon::Table("motcua_thutuc_bosung") ." 
		where ID_YEUCAU = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_yeucau));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	
	function addNew($params){
		$parameters = array();
		$parameters[] = $params["TEN_THUTUC"];
		$parameters[] = $params["NGAYBOSUNG"];
		$parameters[] = $params["ID_YEUCAU"];
		$parameters[] = $params["ID_TAILIEU_NHAN"];
		$sql = " insert into  ". QLVBDHCommon::Table("motcua_thutuc_bosung") . 
		" ( TEN_THUTUC,NGAYBOSUNG,ID_YEUCAU,ID_TAILIEU_NHAN)   values ( ?,?,?,? ) 
		";

		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->prepare($sql);
			$qr->execute($parameters);
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}
	
	function addFromThutucNhan($id_tailieu_nhan,$id_yeucau){
		$sql = " insert into  ". QLVBDHCommon::Table("motcua_thutuc_bosung") . 
		" ( TEN_THUTUC,NGAYBOSUNG,ID_YEUCAU,ID_TAILIEU_NHAN)    ( 
			select TEN_THUTUC, NULL as NGAYBOSUNG , ? as ID_YEUCAU , ID_TAILIEU_NHAN 
			from ". QLVBDHCommon::Table("motcua_nhan_gom") . "  where ID_TAILIEU_NHAN = ?
		) 
		";
		//echo $sql ; exit;
		//try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->prepare($sql);
			$qr->execute(array($id_yeucau,$id_tailieu_nhan));
			return 1;
		//}catch(Exception $ex){
			return 0;
		//}
	}

	function getThutucNhan($id_tailieu_nhan){
		$sql = " select * from " . QLVBDHCommon::Table("motcua_nhan_gom") ." 
		where ID_TAILIEU_NHAN = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_tailieu_nhan));
			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}
	
	function deleteOne(){
		
	}
}