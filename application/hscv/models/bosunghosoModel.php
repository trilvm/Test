<?php 
class bosunghosoModel {
	static function Count($parameter){
		global $db;
		$param = Array();
		$sql = "
			SELECT count(*) as CNT FROM 
				".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv 
			where
				hscv.IS_BOSUNG = 1
		";
		$param[] = $parameter['ID_U'];
		try{
			//echo $sql;
		$r = $db->query($sql,$param);
		}catch(Exception $ex){
			echo $ex->__toString();
			return 0;
		}
		return $r->rowCount();
	}
	function SelectAll($parameter,$offset,$limit,$order){
		
		global $db;
		$user = Zend_Registry::get('auth')->getIdentity();
		
		$where = "  ";
		$param = array();
		$param[] = $user->ID_U;
		if($parameter['TENTOCHUCCANHAN']!=""){
			$wheretemp = "";
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('MOTCUA_HOSO'));
			$wheretemp = " match(mc.TENTOCHUCCANHAN) against (? IN BOOLEAN MODE)";
			$order = " match(mc.TENTOCHUCCANHAN) against ('".str_replace("'","''",$parameter['TENTOCHUCCANHAN'])."') DESC";
			$param[] = $parameter['TENTOCHUCCANHAN'];
			$where .= " and (".$wheretemp.")";
		}
		
	//check phuong
		if($parameter['ID_PHUONG']!=0){
			if($parameter['ID_PHUONG']==-1){
				$where .= " and mc.PHUONG is null";
			}else{
				$param[] = $parameter['ID_PHUONG'];
				$where .= " and mc.PHUONG = ?";
			}
		}
	//
	if($parameter['MAHOSO']!=""){
			$wheretemp = "";
		    $wheretemp = " mc.MAHOSO = ? ";
			$param[] = $parameter['MAHOSO'];
			$where .= " and (".$wheretemp.")";
		}
	
	
	$strlimit = "";
	if($limit>0){
		$strlimit = " LIMIT $offset,$limit";
	}

	if($order!=""){
			$order = "ORDER BY $order";
		}else{
			$order = "ORDER BY bs.ID_YEUCAU DESC";
		}

	//$param = Array();
	$sql = "
			SELECT hscv.* , mc.TENTOCHUCCANHAN, mc.MAHOSO,  max(bs.ID_YEUCAU) as ID_YEUCAU FROM 
				".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv 
			inner join ".  QLVBDHCommon::Table("MOTCUA_HOSO") ." mc on hscv.ID_HSCV =  mc.ID_HSCV
			inner join motcua_loai_hoso loaihs on mc.ID_LOAIHOSO = loaihs.ID_LOAIHOSO 
			inner join ( select lv.* from motcua_linhvuc lv inner join motcua_linhvuc_quyen pq on lv.ID_LV_MC = pq.ID_LV_MC   where ID_U = ?) 
			linhvuchs on loaihs.ID_LV_MC = linhvuchs.ID_LV_MC
			inner join ".  QLVBDHCommon::Table("HSCV_PHIEU_YEUCAU_BOSUNG") ." bs on hscv.ID_HSCV = bs.ID_HSCV
			$where
			GROUP BY hscv.ID_HSCV having hscv.IS_BOSUNG = 1
			$order
			$strlimit
		";
		
		//var_dump($param);
		//
		//echo $sql;
		try{
			//echo $sql;
			$r = $db->query($sql,$param);
			$result = $r->fetchAll();
			//var_dump($param);
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
	}
}
