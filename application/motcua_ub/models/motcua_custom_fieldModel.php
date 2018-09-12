<?php
class motcua_custom_fieldModel{

	function getAll($ID_LV_MC){
		$where = "";
		$params = array();
		if($ID_LV_MC){
			$where = " where ID_LV_MC = ? ";
			$params[] = $ID_LV_MC;
		}
		$sql = " select cu.* , loaihs.TENLOAI from motcua_custom_field cu 
		inner join motcua_loai_hoso loaihs on cu.LOAIHOSO_CODE = loaihs.CODE 
		$where;
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$qr = $dbAdapter->query($sql,$params);
		return $qr->fetchAll();
	}
	
	function delete($id){
		
		$sql = " delete from motcua_custom_field where ID_MC_CF =?  ";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm =  $dbAdapter->prepare($sql);
			$stm->execute(array($id));
		}catch(Exception $ex){
		
		}
	}
	function count($ID_LV_MC){
		$where = "";
		$params = array();
		if($ID_LV_MC){
			$where = " where ID_LV_MC = ? ";
			$params[] = $ID_LV_MC;
		}
		$sql = " select count(*) as DEM  from motcua_custom_field cu
		inner join motcua_loai_hoso loaihs on cu.LOAIHOSO_CODE = loaihs.CODE
		$where;
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$qr = $dbAdapter->query($sql,$params);
		$re = $qr->fetch();
		return $re["DEM"];
	}

	function getById($id){
		$sql = " select cu.*  from motcua_custom_field cu where ID_MC_CF = ?
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$qr = $dbAdapter->query($sql,array($id));
		return $qr->fetch();
	}

	static function save($id,$params){
		$sql = "";
		$parameters = array();
		$parameters[] = $params["LOAIHOSO_CODE"];
		$parameters[] = $params["NAME_DISPLAY"];
		$parameters[] = $params["NAME_COLUMN"];
		$parameters[] = $params["ACTIVE"];
		$parameters[] = $params["IS_TIEPNHAN"];
		$parameters[] = $params["IS_KETQUA"];
		$parameters[] = $params["IS_BAOCAO"];
		$parameters[] = $params["TYPE"];
		$parameters[] = $params["IS_REQUIRED"];
		if($id >0){
			$sql = " update motcua_custom_field set LOAIHOSO_CODE=? , NAME_DISPLAY =? , NAME_COLUMN = ? , ACTIVE = ? , IS_TIEPNHAN = ?, IS_KETQUA = ?,
			IS_BAOCAO = ? , TYPE = ?,IS_REQUIRED = ?
			where ID_MC_CF=?";
			$parameters[] = $id;
			try{
				$dbAdapter = Zend_Db_Table::getDefaultAdapter();
				$stm =  $dbAdapter->prepare($sql);
				$stm->execute($parameters);
			}catch(Exception $ex){
			
			}
		}else{
			foreach($params["LOAIHOSO_CODE"] as $itemloai){
				$sql = " insert into motcua_custom_field (LOAIHOSO_CODE,NAME_DISPLAY,NAME_COLUMN,ACTIVE,IS_TIEPNHAN,IS_KETQUA,IS_BAOCAO,TYPE,IS_REQUIRED )  values (?,?,?,?,?,?,?,?,?) ";
				try{
					$parameters[0] = $itemloai;
					$dbAdapter = Zend_Db_Table::getDefaultAdapter();
					$stm =  $dbAdapter->prepare($sql);
					$stm->execute($parameters);
				}catch(Exception $ex){
				
				}
			}
		}


	}
	
	function getCodeLoaihosoByLinhvuc($id_lv){
		
		$where = "";
		$params = array();
		if($id_lv > 0){
			$where = " where ID_LV_MC = ? ";
			$params[] = $id_lv;
		}
		$sql = " select CODE from motcua_loai_hoso $where " ;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,$params);
			$data = $stm->fetchAll();
			return $data;
		}catch(Exception $ex){
			
		}
	}

	static function saveAllLinhvuc($params){
		$sql = "";
		$parameters = array();
		$parameters[] = "";
		$parameters[] = $params["NAME_DISPLAY"];
		$parameters[] = $params["NAME_COLUMN"];
		$parameters[] = $params["ACTIVE"];
		$parameters[] = $params["IS_TIEPNHAN"];
		$parameters[] = $params["IS_KETQUA"];
		$parameters[] = $params["IS_BAOCAO"];
		$parameters[] = $params["TYPE"];
		$parameters[] = $params["IS_REQUIRED"];
		$arr_loaihscode = motcua_custom_fieldModel::getCodeLoaihosoByLinhvuc($params["ID_LV_MC"]);
		
		$sql = " insert into motcua_custom_field (LOAIHOSO_CODE,NAME_DISPLAY,NAME_COLUMN,ACTIVE,IS_TIEPNHAN,IS_KETQUA,IS_BAOCAO,TYPE,IS_REQUIRED )  values (?,?,?,?,?,?,?,?,?) ";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			foreach($arr_loaihscode as $loaihscode){
				$parameters[0] = $loaihscode["CODE"];
				$stm =  $dbAdapter->prepare($sql);
				$stm->execute($parameters);
			}
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}
	

	function getColumnMotcuaHoso(){
		$sql = " Describe ". QLVBDHCommon::Table("motcua_hoso") ;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql);
			$data = $stm->fetchAll();
			foreach($data as $item){
				echo "<option value=".$item["Field"]." >".$item["Field"]."</option>";
			}
		}catch(Exception $ex){
			
		}
	}

	function toComboLoaihosoByLinhvuc($id_lv){
		
		$where = "";
		$params = array();
		if($id_lv > 0){
			$where = " where ID_LV_MC = ? ";
			$params[] = $id_lv;
		}
		$sql = " select * from motcua_loai_hoso $where " ;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,$params);
			$data = $stm->fetchAll();
			foreach($data as $item){
				echo "<option value=".$item["CODE"]." >".$item["TENLOAI"]."</option>";
			}
		}catch(Exception $ex){
			
		}
	}

	function addNewColumn($name,$type){
		$type_str = "";
		switch($type){
			case "VARCHAR":
				$type_str = " varchar(1024) ";
				break;
			case "INTEGER":
				$type_str = " integer(11) " ;
				break;	
			case "DOUBLE":
				$type_str = " double(11,4) " ;
				break;
			case "DATE":
				$type_str = " date " ;
				break;
			default:
				$type_str = " varchar(20) ";
				break;
		}
		$sql = " ALTER TABLE ". QLVBDHCommon::Table("motcua_hoso"). " ADD $name $type_str ";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute();
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}
}