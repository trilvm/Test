<?php

class Common_Sovanban{
	
	static function getColumNameGroup($type){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select `CODE` from `qtht_config_sovanban` where `TYPE`=?  and `ON_DB` = 1
		and IS_SELECTED = 1
		";
		$qr = $dbAdapter->query($sql,array($type));
		$re = $qr->fetchAll();
		$arr = array();
		foreach($re as $it){
			array_push($arr,$it['CODE']);
		}
		return $arr;
	}
	
	static function getCurrentSodi($year,$arr_value){
		//Lay cac cot de group by
		$arr_colum = Common_Sovanban::getColumNameGroup(2);
		$having = " WHERE ID_CQ=".$arr_value['ID_CQ'];
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$having = $having." and ".implode(' and ',$arr_where);
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT MAX(`SODI_IN`) as DEM
			FROM `vbdi_vanbandi_".$year."` 
			".$having."
			
		";
		$qr = $dbAdapter->query($sql);
		$re = $qr->fetch();
		return $re["DEM"];		
	}
	
	static function getCurrentSodiSQL($year,$arr_value){
		//Lay cac cot de group by
		$arr_colum = Common_Sovanban::getColumNameGroup(2);
		$having = " WHERE ID_CQ=".$arr_value['ID_CQ'];
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$having = $having." and ".implode(' and ',$arr_where);
		}
		$sql = "
			SELECT MAX(`SODI_IN`) as DEM
			FROM `vbdi_vanbandi_".$year."` 
			".$having."
			
		";
		return $sql;
	}
	
	static function getCurrentSoden($year,$arr_value){
		//Lay cac cot de group by
		$arr_colum = Common_Sovanban::getColumNameGroup(1);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

		$sql = "
			SELECT MAX(`SODEN_IN`) as DEM
			FROM `vbd_vanbanden_".$year."` 
			".$having."";
		$qr = $dbAdapter->query($sql);
		$re = $qr->fetch();
		
		//get so den tu draft
		$sql = "
			SELECT MAX(`SODEN_IN`) as DEM
			FROM `vbd_vanbanden_draft_".$year."` 
			".$having."";
		$qr = $dbAdapter->query($sql);
		$re1 = $qr->fetch();
		if($re["DEM"]>$re1["DEM"])return $re["DEM"];
		return $re1["DEM"];
		
	}
	static function getCurrentSodenSQL($year,$arr_value){
		//Lay cac cot de group by
		$arr_colum = Common_Sovanban::getColumNameGroup(1);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		$sql = "
			SELECT MAX(`SODEN_IN`) as DEM
			FROM `vbd_vanbanden_".$year."` 
			".$having."";
		return $sql;
	}
	
	static function checkExistsSoden($year,$arr_value,$so_den){
		$arr_colum = Common_Sovanban::getColumNameGroup(1);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$arr_i = "SODEN"."='".$so_den."'";
			array_push($arr_where,$arr_i);
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}else{
			$having = " WHERE SODEN='".$so_den."'";
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_vanbanden_".$year."` 
			".$having."";
		$qr = $dbAdapter->query($sql);

		$re = $qr->fetch();
		if($re["DEM"] >0 )
			return 1;
		return 0;
	}
	
	static function checkExistsSodenSQL($year,$arr_value,$so_den){
		$arr_colum = Common_Sovanban::getColumNameGroup(1);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$arr_i = "SODEN"."='".$so_den."'";
			array_push($arr_where,$arr_i);
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_vanbanden_".$year."` 
			".$having."";
		
		return $sql;
	}
	
	static function checkExistsSodiSQL($year,$arr_value,$so_di){
		$arr_colum = Common_Sovanban::getColumNameGroup(2);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$arr_i = "SODI"."='".$so_di."'";
			array_push($arr_where,$arr_i);

			$arr_i = "ID_CQ"."='".$arr_value["ID_CQ"]."'";
			array_push($arr_where,$arr_i);
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbdi_vanbandi_".$year."` 
			".$having."";
		return $sql;
	}
	static function checkExistsSodi($year,$arr_value,$so_di){
		$arr_colum = Common_Sovanban::getColumNameGroup(2);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."=".$arr_value[$col_item];
					array_push($arr_where,$arr_i);
			}
			$arr_i = "SODI"."='".$so_di."'";
			array_push($arr_where,$arr_i);
			$arr_i = "ID_CQ"."='".$arr_value["ID_CQ"]."'";
			array_push($arr_where,$arr_i);
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbdi_vanbandi_".$year."` 
			".$having."";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$qr = $dbAdapter->query($sql);
		$re = $qr->fetch();
		if($re["DEM"] >0 )
			return 1;
		return 0;
	}
}
?>