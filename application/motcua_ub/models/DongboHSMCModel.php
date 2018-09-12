<?php
class DongboHSMCModel {

	function getHosoFromWebsite($params){

		$where = "";
		$arr_where = array();
		$parameters = array();
		if((int)$params["LOAIHSMCID"] > 0 ){
			//$where_item = "hsqm.LOAIHSMCID = ?" 
			$arr_where[] = " hsqm.LOAIHSMCID = ?" ;
			$parameters[] = $params["LOAIHSMCID"] ;
		}

		switch((int)$params["TRANGTHAI"]  ){
			case 0:
				$arr_where[] = " ( hsqm.IS_TIEPNHAN is NULL or hsqm.IS_TIEPNHAN = 0  ) " ;
		
				break;
			case 1:
				$arr_where[] = " ( hsqm.IS_TIEPNHAN = 1  ) " ;
				break;
			case 2:
				
				$arr_where[] = " ( hsqm.IS_TIEPNHAN = 2 ) " ;
				
				break;
			default:
				break;
		}
		
		if(count($arr_where) > 0){
			$where = " where ";
			$where .= implode(" and ", $arr_where);
		}

		
		$sql = "
			select distinct hsqm.*, loai.TENLOAI, loai.ID_LOAIHSCV, loai.ID_LOAIHOSO from ". QLVBDHCommon::Table("services_motcua_dkquamang") ." hsqm
			left join motcua_loai_hoso loai on hsqm.LOAIHSMCID = loai.ID_ONWEBSITE  
			$where
		";
		//try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,$parameters);
			return $qr->fetchAll(); 

		//}catch(Exception $ex){
			return array();
		//}
		

	}

	function getDetail($id_dkquamang){
		$sql = "
			select distinct hsqm.*, loai.TENLOAI , loai.ID_LOAIHOSO, loai.ID_LV_MC from ". QLVBDHCommon::Table("services_motcua_dkquamang") ." hsqm
			left join motcua_loai_hoso loai on hsqm.LOAIHSMCID = loai.ID_ONWEBSITE  
			where hsqm.ID_DKQUAMANG = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_dkquamang));
			return $qr->fetch(); 
		}catch(Exception $ex){
			return array();
		}
	}

	function updateTiepnhan($trangthai,$id_dkquamang){
		$sql = " update  " . QLVBDHCommon::Table("services_motcua_dkquamang") ." set IS_TIEPNHAN = ? where ID_DKQUAMANG = ?" ;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($trangthai,$id_dkquamang));
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}

	function getLoaihsDongbo(){
		$sql = "
			select * from motcua_loai_hoso where ID_ONWEBSITE > 0
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql);
			return $qr->fetchAll(); 
		}catch(Exception $ex){
			return array();
		}
	}


	function updateAfterTiepnhan($id_dkquamang,$params){
		//HOTEN
		//DIACHI
		//EMAIL
		//ISTIEPNHAN
		//ngay tiep nhan
		//ngay hen tra
		//cap nhat trang thai dang xu ly
		//cap nhat nguoi dang xu ly
		$parameters = array();
		$parameters[] = $params["HOTEN"];
		$parameters[] = $params["DIACHI"];
		$parameters[] = $params["EMAIL"];
		$parameters[] = $params["DIENTHOAI"];
		//$parameters[] = $params["MASOBIENNHAN"];
		$parameters[] = $params["NGAYTIEPNHAN"];
		$parameters[] = $params["NGAYHENTRA"];
		$parameters[] = $params["NGUOIDANGXULY"];
		$parameters[] = $id_dkquamang;

		
		
		
		$sql = " update  " . QLVBDHCommon::Table("services_motcua_dkquamang") ." 
		set 
		HOTEN = ?,
		DIACHI = ?,
		EMAIL = ?,
		DIENTHOAI = ?,
		NGAYTIEPNHAN = ? ,
		NGAYHENTRA = ? ,  
		NGUOIDANGXULY = ?,
		TRANGTHAI = 1 ,
		IS_UPDATE = 1,
		IS_TIEPNHAN = 1
		where ID_DKQUAMANG = ?" ;
		//try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute($parameters);
			return 1;
		//}catch(Exception $ex){
			return 0;
		//}
	}

	
	function updateTrangthaiByIdHSCV($id_hscv,$trangthai){

		//kiem tra ho so mot cua co phai la dang ky qua mang khong
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select ID_DKQUAMANG from " . QLVBDHCommon::Table("motcua_hoso") ."  where ID_HSCV = ?  ";
		$id_dkquamang = 0;
		try{
			$qr = $dbAdapter->query($sql,array($id_hscv));
			$re = $qr->fetch();
			$id_dkquamang = $re["ID_DKQUAMANG"];
		}catch(Exception $ex){
		
		}
		//cap nhat trang thai
		//echo $id_dkquamang."ddddddddddd";
		if($id_dkquamang > 0){
			$sql = " update  " . QLVBDHCommon::Table("services_motcua_dkquamang") ." set TRANGTHAI = ? , IS_UPDATE = 1 where ID_DKQUAMANG = ?" ;
			try{
				
				$stm = $dbAdapter->prepare($sql);
				$stm->execute(array($trangthai,$id_dkquamang));
				return 1;
			}catch(Exception $ex){
				return 0;
			}
		}
	}

	
	
	
	function updateTrangthai($id_dkquamang,$trangthai){
		//cap nhat trang thai
		$sql = " update  " . QLVBDHCommon::Table("services_motcua_dkquamang") ." set TRANGTHAI = ? where ID_DKQUAMANG = ?" ;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($trangthai,$id_dkquamang));
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}

	function updateNguoidangxuly(){
		
	}
}