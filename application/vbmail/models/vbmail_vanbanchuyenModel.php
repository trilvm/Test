<?php
	class vbmail_vanbanchuyenModel{
	
		public static function getAllByIdVbdi($id_vbdi){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "
				select  * from ".QLVBDHCommon::Table('vbmail_vanbanchuyen')." where `ID_VBDI`=?
				order by ID_VBCHUYEN DESC
			";
			//try{
				$query =  $dbAdapter->query($sql,array($id_vbdi));
				return $query->fetchAll();
			//}catch(Exception $ex){
				return array();
			//}
		}

		public static function insertVbChuyen($arrdata){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "Insert into ".QLVBDHCommon::Table('vb_vanbanchuyen')." 
			(ID_VBDI,CQ_NHAN,EMAIL_NHAN,DACHUYEN,ID_CQ,ID_U)  values( ? , ? , ? , ? , ? , ?)
			";
			$arr_values = array (
				$arrdata["ID_VBDI"]	,
				$arrdata["CQ_NHAN"],
				$arrdata["EMAIL_NHAN"],
				$arrdata["DACHUYEN"],
				$arrdata["ID_CQ"],
				$arrdata["ID_U"]
			);
			try{
				$stm = $dbAdapter->prepare($sql);
				$stm->execute($sql,$arr_values);
				return $dbAdapter->lastInsertId(QLVBDHCommon::Table('vbmail_vanbanchuyen'),'ID_VBCHUYEN');
			}catch (Exception $ex){
				return 1;
			}
		}
	}
?>