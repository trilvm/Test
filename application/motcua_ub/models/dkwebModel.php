<?php

class dkwebModel {
	function getHosoFromWebsite($params){
		
		$sql = "
			select hs.*,mc_lhs.TENLOAI,mc_lhs.ID_LOAIHSCV,mc_lhs.ID_LOAIHOSO  from gtvt_motcua_hoso_web hs
			left join motcua_loai_hoso mc_lhs on hs.MALOAIHOSO = mc_lhs.CODE
			where ( IS_TIEPNHAN = 0 or IS_TIEPNHAN is NULL )
			ORDER BY hs.ID_MC_HSW DESC
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,$parameters);
			return $qr->fetchAll(); 

		}catch(Exception $ex){
			return array();
		}
	}

	function getDetail($id){
		
		$sql = "
			select distinct hsqm.*, loai.TENLOAI , loai.ID_LOAIHOSO, loai.ID_LV_MC from ". "gtvt_motcua_hoso_web" ." hsqm
			left join motcua_loai_hoso loai on hsqm.MALOAIHOSO = loai.CODE  
			where hsqm.ID_MC_HSW = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id));
			return $qr->fetch(); 
		}catch(Exception $ex){
			return array();
		}
	}

	
	
	function getFileDetail($mahoso){
		$sql = "
			select * from gtvt_motcua_file where MAHOSO = ?
			
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($mahoso));
			return $qr->fetchAll(); 
		}catch(Exception $ex){
			return array();
		}
	}

	function getFileByMaso($maso){
		$sql = "
			select * from gtvt_motcua_file where MASO = ?
			
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($maso));
			return $qr->fetch(); 
		}catch(Exception $ex){
			return array();
		}
	}

	function updateTrangthai($id,$trangthai){
		$sql = "
			update   gtvt_motcua_hoso_web
			set TRANGTHAI=?
			where ID_MC_HSW = ?
			
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($trangthai,$id));
			return $qr->fetch(); 
		}catch(Exception $ex){
			return array();
		}
	}

	function GetMasodkquamangByIDHSCV($id_hscv){
		
		$sql = "
			select mc.MAHOSO  from 
			". QLVBDHCommon::Table("motcua_hoso")." mc
			
			where ID_HSCV = ? and ID_DKQUAMANG > 0
			
		";
		//echo $sql.$id_hscv;
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_hscv));
			$re =  $qr->fetch(); 
			
			return $re["MAHOSO"];
		}catch(Exception $ex){
			return array();
		}
	}

	function delete($id){
		$sql ="
			delete from gtvt_motcua_hoso_web where ID_MC_HSW = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id));
		}catch(Exception $ex){
		
		}
	}
	

	function updateDaTiepnhan($id){
		$sql ="
			update  gtvt_motcua_hoso_web set IS_TIEPNHAN = 1 where ID_MC_HSW = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id));
		}catch(Exception $ex){
		
		}

		$sql ="
			delete from  gtvt_motcua_hoso_web  where ID_MC_HSW = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id));
		}catch(Exception $ex){
		
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

	//gopy

	
	function getGopy($id){
		$sql = "
			select gy.* from  web_motcua_gopy gy
			inner join gtvt_motcua_hoso_web hs on gy.MAHOSO = hs.MAHOSO
			where hs.ID_MC_HSW = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id));
			return $qr->fetchAll(); 
		}catch(Exception $ex){
			return array();
		}
	}
	
	function insertGopy($params){
		
		$param = array();
		$param[] = $params["NOIDUNG"];
		$param[] = $params["NAME_U"];
		$param[] = $params["DATESEND"];
		$param[] = $params["MAHOSO"];
		
		$sql = "
			insert into web_motcua_gopy (NOIDUNG,NAME_U,DATESEND,MAHOSO) 
			values ( 
				?, ?, ? , ?
			)
		";
		//try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,$param);
			//return $qr->fetch(); 
		//}catch(Exception $ex){
			return array();
		//}
	}

	function deleteGopy($params){
		
	}
	

	

	function getDetailSpe($id_hoso, $loai){
	   $dbAdapter = Zend_Db_Table::getDefaultAdapter();
       $sql = "
			select hs.MAHOSO, MALOAIHOSO, loai.LOAIDON from 
			gtvt_motcua_hoso_web hs 
			inner join motcua_loai_hoso loai on hs.MALOAIHOSO = loai.CODE 
			where ID_MC_HSW  = ?

	   ";
		$loaidon = 0;
		
		$mahoso = "";
		
		try{
			
			$stm = $dbAdapter->query($sql,array($id_hoso));
			$re = $stm->fetch();
			$loaidon = (int)$re["LOAIDON"];
			$mahoso = $re["MAHOSO"];
		}catch(Exception $ex){
			return array();
		}
		
		$tble_spe = "";
		switch($loaidon){
			case 1:
				$tble_spe = "web_thicong_ct";
				
				break;
			case 2:
				$tble_spe = "web_phuhieu_tcd";
				$tble_spe_ds = "web_danhsach_tcd";
				break;
			case 3:
				$tble_spe = "web_phuhieu_xhd";
				$tble_spe_ds = "web_danhsach_xhd";
				break;
			case 4:
				$tble_spe = "web_phuhieu_xtx";
				$tble_spe_ds = "web_danhsach_xtx";
				break;
			case 5:
				$tble_spe = "web_phuhieu_xdl";
				$tble_spe_ds = "web_danhsach_xdl";
				break;
			case 6:
				$tble_spe = "web_capphep_dc";
				
				break;
			default:
				return array();
		
		}

		$sql = "
			select tc.* , mc_hs.TENTOCHUCCANHAN from ". $tble_spe ." tc
			inner join gtvt_motcua_hoso_web mc_hs on tc.MAHOSO = tc.MAHOSO
			where  tc.MAHOSO = ?
		";
		//echo $sql . "$id_hoso"; exit;
		$arr_data = array();
		try{
			//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,array($mahoso));
			$arr_data = $stm->fetch();
			$arr_data["LOAIDON"] = $loaidon;
			//return $arr;
		}catch(Exception $ex){
		
		}
		
		$sql = "
			select tc.* from ". $tble_spe_ds ." tc
			
			where MAHOSO  = ?
		";

		$arr_ds = array();
		try{
			//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,array($mahoso));
			$arr_ds = $stm->fetchAll();
			
			//return $arr;
		}catch(Exception $ex){
		
		}
		//echo $sql; echo $mahoso;exit;
		$arr =array();
		$arr["data"] = $arr_data;
		$arr["danhsach"] = $arr_ds;
		//var_dump($arr); exit;
		return $arr;


	}

	function getDetailSpeByMaso($mahoso,$loai=1){
		
	   
	   
	   $dbAdapter = Zend_Db_Table::getDefaultAdapter();
       $sql = "
			select hs.MAHOSO, loai.CODE as MALOAIHOSO, loai.LOAIDON from 
			". QLVBDHCommon::Table("motcua_hoso")." hs 
			inner join motcua_loai_hoso loai on hs.ID_LOAIHOSO = loai.ID_LOAIHOSO 
			where hs.MAHOSO  = ?

	   ";
	    //echo $sql.$mahoso;
	   //$mahoso = $re["MAHOSO"];
		$loaidon = 0;
		
		
		
		try{
			
			$stm = $dbAdapter->query($sql,array($mahoso));
			$re = $stm->fetch();
			
			$loaidon = (int)$re["LOAIDON"];
			
		}catch(Exception $ex){
			return array();
		}
		
		$tble_spe = "";
		switch($loaidon){
			case 1:
				$tble_spe = "web_thicong_ct";
				
				break;
			case 2:
				$tble_spe = "web_phuhieu_tcd";
				$tble_spe_ds = "web_danhsach_tcd";
				break;
			case 3:
				$tble_spe = "web_phuhieu_xhd";
				$tble_spe_ds = "web_danhsach_xhd";
				break;
			case 4:
				$tble_spe = "web_phuhieu_xtx";
				$tble_spe_ds = "web_danhsach_xtx";
				break;
			case 5:
				$tble_spe = "web_phuhieu_xdl";
				$tble_spe_ds = "web_danhsach_xdl";
				break;
			case 6:
				$tble_spe = "web_capphep_dc";
				
				break;
			default:
				return array();
		
		}

		$sql = "
			select tc.* from ". $tble_spe ." tc
			where  MAHOSO = ?
		";
		//echo $sql . "$id_hoso"; exit;
		$arr_data = array();
		try{
			//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,array($mahoso));
			$arr_data = $stm->fetch();
			$arr_data["LOAIDON"] = $loaidon;
			//return $arr;
		}catch(Exception $ex){
		
		}
		
		$sql = "
			select tc.* from ". $tble_spe_ds ." tc
			
			where MAHOSO  = ?
		";

		$arr_ds = array();
		try{
			//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,array($mahoso));
			$arr_ds = $stm->fetchAll();
			
			//return $arr;
		}catch(Exception $ex){
		
		}
		//echo $sql; echo $mahoso;exit;
		$arr =array();
		$arr["data"] = $arr_data;
		$arr["danhsach"] = $arr_ds;
		//var_dump($arr); exit;
		return $arr;

		
		

	}

}