<?php 
class Vb_nguoikyModel {
	
	//ID_U , TEN 
	static function getData($is_get_active){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$user = "QTHT_USERS";
		if(!$is_get_active)
			$is_get_active = 0;
		if($is_get_active > 0){
			$user =" ( SELECT * FROM QTHT_USERS WHERE ACTIVE = 1 ) QTHT_USERS "	;
		}
		$sql = "
		SELECT
     	QTHT_USERS.ID_U,USERNAME,QTHT_DEPARTMENTS.NAME AS DEP_NAME,
     	VB_NGUOIKY.ID_VB_NK,
     	CONCAT(FIRSTNAME , ' ' , LASTNAME) as NAME
			FROM
				$user
			INNER JOIN VB_NGUOIKY ON QTHT_USERS.`ID_U` = VB_NGUOIKY.`ID_U`
			LEFT JOIN
				QTHT_EMPLOYEES
			ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP
			LEFT JOIN 
				QTHT_DEPARTMENTS
			ON QTHT_DEPARTMENTS.ID_DEP = QTHT_EMPLOYEES.ID_DEP
		";
		try{
			$stm = $dbAdapter->query($sql);
			$re = $stm->fetchAll();
			return $re;
		}catch (Exception $ex){
			return array();
		}
	}
	
	static function insert($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		insert into `vb_nguoiky` (ID_U) values (?) 
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_u));
			return $dbAdapter->lastInsertId();
		}catch(Exception $ex){
			return 0;
		}
		return 0;
	}
	
	static function getDataUser($is_themmoi,$id_u_current){
		
		//truong hop them moi : lay danh sach cac user khong nam trong danh sach nguoi ky
		//truong hop cap nhat : lay danh sach cac user khong nam trong danh sach nguoi ky va id_u_current
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//lay danh sach cac id_u la nguoi ky
		$sql = "
		select ID_U from VB_NGUOIKY  
		";
		$arr_id_u = array();
		try{
			$stm = $dbAdapter->query($sql);
			$re = $stm->fetchAll();
			foreach ($re as $item){
				array_push($arr_id_u,$item["ID_U"]);
			}
		}catch(Exception $ex){
			return array();
		}
		
		$where = "";
		if(count($arr_id_u)>0){
			$where = "where ID_U not in ( ".implode(',',$arr_id_u)." ) ";	
		}
		
		$sql = "
		SELECT
     			QTHT_USERS.ID_U,USERNAME,CONCAT(FIRSTNAME , ' ' , LASTNAME) as NAME
			FROM
				(select * from QTHT_USERS $where ) QTHT_USERS
			LEFT JOIN
				QTHT_EMPLOYEES
			ON QTHT_USERS.ID_EMP = QTHT_EMPLOYEES.ID_EMP
		"
		;
		
		$stm = $dbAdapter->query($sql);
		return $stm->fetchAll();
	}
	
	static function delete($id_vb_nk){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			delete from `vb_nguoiky` where `ID_VB_NK`=?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_vb_nk));
			return $dbAdapter->lastInsertId();
		}catch(Exception $ex){
			return 0;
		}
		return 0;
	}
	
}

?>