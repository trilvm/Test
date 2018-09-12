<?php 
class vbmail_vanbannhanModel {
	static function getAllReceived(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from ".QLVBDHCommon::Table('vbmail_vanbannhan')." 
			where ID_VBD  is  NULL  
		";
		try{
			$qr = $dbAdapter->query($sql);
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}

	static function deleteVbNhan($id_vbnhan){
		if(!$id_vbnhan)
			return 0;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "delete from ".QLVBDHCommon::Table('vbmail_vanbannhan')."
			where ID_VBNHAN = ?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_vbnhan));
		}catch(Exception $ex){
			return 0;
		}
	}

	static function getById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from ".QLVBDHCommon::Table('vbmail_vanbannhan')." 
			where ID_VBNHAN = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id));
			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}
}
?>