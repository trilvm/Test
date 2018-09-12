<?php 
require_once('qtht/models/UsersModel.php');
class xulycongviecModel{
	static function getxulycongviecByUser($id_u){
		
		$year = QLVBDHCommon::getYear();
		$sql = "
		select hscv.`ID_HSCV` , wfl.`ID_U_SEND` ,
wfl.`ID_T`   , wftp.`NAME` as TR_NAME  , wfl.`ID_PL`,wfl.`TRE`
 from
(select ID_HSCV , ID_PI from `hscv_hosocongviec_$year` hscv1 where  hscv1.`ID_LOAIHSCV` =1) hscv
inner join  (SELECT * from `wf_processlogs_$year` where `ID_U_SEND` =?) wfl
on hscv.`ID_PI` = wfl.`ID_PI`
inner join `wf_transitions` wft on wft.`ID_T` = wfl.`ID_T`
inner join `wf_transitionpools` wftp on wftp.`ID_TP` = wft.`ID_TP`
where wfl.`ID_U_RECEIVE`!=0
		";
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$stm = $dbAdapter->query($sql,array($id_u));
		//echo $sql;
		return $stm->fetchAll();
	}
	
	
	
	static function getDetails($id_hscv,$id_pi,$id_u){

		$year = QLVBDHCommon::getYear();
		$sql = "
		select *
 from
(select ID_HSCV , IS_THEODOI,ID_PI from `hscv_hosocongviec_$year` hscv1 where  hscv1.`ID_HSCV`=?) hscv
inner join  (SELECT * from `wf_processlogs_$year` ) wfl
on hscv.`ID_PI` = wfl.`ID_PI`
inner join `wf_transitions` wft on wft.`ID_T` = wfl.`ID_T`
where wfl.`ID_U_RECEIVE`!=0 and
(wfl.`ID_PL` <=
(
select  ID_PL
from `wf_processlogs_$year` wfl_c
inner join `wf_transitions` wft_c on wfl_c.`ID_T` = wft_c.`ID_T`
where wfl_c.`ID_PI` = wfl.`ID_PI` and wft_c.`ISLAST` = 1
) or (
select  ID_PL
from `wf_processlogs_$year` wfl_c
inner join `wf_transitions` wft_c on wfl_c.`ID_T` = wft_c.`ID_T`
where wfl_c.`ID_PI` = wfl.`ID_PI` and wft_c.`ISLAST` = 1
) is NULL)
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$stm = $dbAdapter->query($sql,array($id_hscv));
		$re = $stm->fetchAll();
		$tre_arr = array();
		
		foreach ($re as $item){
			if($item["TRE"] > 0 && $item["ID_U_RECEIVE"] == $id_u)
				array_push($tre_arr,1);
			else 
				array_push($tre_arr,0);
			
		}
		$arr_data = array();
		$arr_data["DONGLUANCHUYEN"] = $re ;
		$arr_data["TRE"] = $tre_arr ;
		return $arr_data;
	}
	
	
	static function getListHscvByuser($id_u,$type,$year,$fromdate,$todate){
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "`NGAY_BD` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		
		if($todate || $todate != ""){
		 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "`NGAY_BD` <= '".$todate."'" ; 
		
		array_push($where_arr,$where_todate);
		}
		
		
		$where ="";
		if(count($where_arr) > 0)
			$where = " and" . implode(' and ',$where_arr)." ";
		
		
		$wheretype = "=?";
		$value_type = "";
		if($type ==1){
			$value_type = "VBD";
		}
		if($type ==2){
			$value_type = "VBSOANTHAO";
		}
		if($type ==3){
			$value_type = "MOTCUA";
		}
	
		$sql ="
		select  pi.`ID_PI` , hscv.`ID_HSCV` , pi.`IS_FINISH`, max(wfl.TRE_CV) as TRE  ,  
		max(wfl.HXL_E) AS HANXULY , max(wfl.DATESEND) as DATESEND,wfl.`ID_U_SEND` ,max(wfl.CXL) as CXL , 
		hscv.IS_THEODOI
		from
`wf_processitems_$year` pi
inner join (select * from `hscv_hosocongviec_$year` ) hscv
on pi.ID_PI = hscv.`ID_PI`
inner join  (SELECT * , ( (TRE is Null) and (ID_U_RECEIVE =? ))  as CXL ,
CASE  
	WHEN (TRE IS NULL) THEN HANXULY
	ELSE 0
END AS HXL_E,
( (TRE > 0) and (`ID_U_RECEIVE` = $id_u )) as TRE_CV
from `wf_processlogs_$year`  
where  `ID_U_RECEIVE` != 0 and(`ID_U_SEND` =?  or `ID_U_RECEIVE` = ?)  ) wfl
on hscv.ID_PI = wfl.`ID_PI`

inner join `wf_processes` wfpr on pi.ID_P = wfpr.ID_P
inner join `wf_classes` wfc on wfpr.ID_C = wfc.ID_C 
where wfc.ALIAS=?
group by hscv.`ID_HSCV`
		
		";
		
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$stm = $dbAdapter->query($sql,array($id_u,$id_u,$id_u,$value_type));
		$re =  $stm->fetchAll();
		
		$arrshscv =  array();
		$arr_dangxuly = array();
		$dem_dangxuly_tre = 0;
		$arr_daxuly = array();
		$dem_daxuly_tre = 0;
		$arr_ketthuc = array();
		$dem_ketthuc_tre = 0;
		foreach ($re as $item){
			//echo "<br/>";
			//var_dump($item);
			if($item["IS_FINISH"] == 1){
				array_push($arr_ketthuc,$item["ID_HSCV"]);	
				if($item["TRE"] > 0 )
					$dem_ketthuc_tre++;
			}elseif ($item["IS_THEODOI"] == 1){
				array_push($arr_ketthuc,$item["ID_HSCV"]);	
				if($item["TRE"] > 0 )
					$dem_ketthuc_tre++;
			}
			else if($item["CXL"] == 1){
				array_push($arr_dangxuly,$item["ID_HSCV"]);	
				if(QLVBDHCommon::getTreHan($item["DATESEND"],$item["HANXULY"]) > 0){
					$dem_dangxuly_tre++;
				}
				
			}
			else if($item["CXL"]==0){
				array_push($arr_daxuly,$item["ID_HSCV"]);
				if($item["TRE"] > 0 )
					$dem_daxuly_tre++;
			}
			
		}
		
		$arrshscv["KETTHUC"] = $arr_ketthuc;
		$arrshscv["DANGXULY"] = $arr_dangxuly;
		$arrshscv["DAXULY"] = $arr_daxuly;
		$arrshscv["DAXULY_TRE"] = $dem_daxuly_tre;
		$arrshscv["DANGXULY_TRE"] = $dem_dangxuly_tre;
		$arrshscv["KETTHUC_TRE"] = $dem_ketthuc_tre;
		return $arrshscv;
	}
	
	
	static function getListHscvBydep($id_pb,$type,$year,$fromdate,$todate){
		
		$data_u = UsersModel::getUsersByDepartment($id_pb);
		//lay mang id nhan vien
		$arr_id = array();
		foreach ($data_u as $u){
			array_push($arr_id,$u["ID_U"]);
		}
		
		if(count($arr_id) > 0 )
			$str_arrid = implode(',',$arr_id);
		else
			return array();
		
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "`NGAY_BD` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		
		if($todate || $todate != ""){
		 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "`NGAY_BD` <= '".$todate."'" ; 
		
		array_push($where_arr,$where_todate);
		}
		
		
		$where ="";
		if(count($where_arr) > 0)
			$where = " and" . implode(' and ',$where_arr)." ";
		
		
			$wheretype = "=?";
		$value_type = "";
		if($type ==1){
			$value_type = "VBD";
		}
		if($type ==2){
			$value_type = "VBSOANTHAO";
		}
		if($type ==3){
			$value_type = "MOTCUA";
		}
	
		$sql ="
		select  pi.`ID_PI` , hscv.`ID_HSCV` , pi.`IS_FINISH`, hscv.IS_THEODOI , max(wfl.TRE_CV) as TRE ,wfl.`ID_U_SEND` 
		,max(wfl.CXL) as CXL, max(wfl.HXL_E) AS HANXULY , max(wfl.DATESEND) as DATESEND  from
`wf_processitems_$year` pi
inner join (select * from `hscv_hosocongviec_$year` ) hscv
on pi.ID_PI = hscv.`ID_PI`
inner join  (SELECT * , ( (TRE is Null) and (ID_U_RECEIVE in ($str_arrid) ))  as CXL ,
CASE  
	WHEN (TRE IS NULL) THEN HANXULY
	ELSE 0
END AS HXL_E,
( (TRE > 0) and  (  ID_U_RECEIVE in ($str_arrid) ) ) as TRE_CV
from `wf_processlogs_$year`  
where  `ID_U_RECEIVE` != 0 and(`ID_U_SEND` in ($str_arrid) or ID_U_RECEIVE in ($str_arrid))  ) wfl
on hscv.ID_PI = wfl.`ID_PI`
inner join `wf_processes` wfpr on pi.ID_P = wfpr.ID_P
inner join `wf_classes` wfc on wfpr.ID_C = wfc.ID_C 
where wfc.ALIAS=?
group by hscv.`ID_HSCV`
		
		";
		//echo $sql;
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$stm = $dbAdapter->query($sql,array($value_type));
		$re =  $stm->fetchAll();
		//echo $type;
		//var_dump($re);
		$arrshscv =  array();
		$arr_dangxuly = array();
		$dem_dangxuly_tre = 0;
		$arr_daxuly = array();
		$dem_daxuly_tre = 0;
		$arr_ketthuc = array();
		$dem_ketthuc_tre = 0;
		foreach ($re as $item){
			//echo "<br/>";
			//var_dump($item);
			
			if($item["IS_THEODOI"] == 1){
				array_push($arr_ketthuc,$item["ID_HSCV"]);
			}
			else if($item["IS_FINISH"] == 1 ){
				array_push($arr_ketthuc,$item["ID_HSCV"]);	
				if($item["TRE"] > 0 )
					$dem_ketthuc_tre++;
			}
			else if($item["CXL"] == 1){
				array_push($arr_dangxuly,$item["ID_HSCV"]);	
				if(QLVBDHCommon::getTreHan($item["DATESEND"],$item["HANXULY"]) > 0){
					$dem_dangxuly_tre++;
				}
				
			}
			else if($item["CXL"]==0){
				array_push($arr_daxuly,$item["ID_HSCV"]);
				if($item["TRE"] > 0 )
					$dem_daxuly_tre++;
			}
			
		}
		
		$arrshscv["KETTHUC"] = $arr_ketthuc;
		$arrshscv["DANGXULY"] = $arr_dangxuly;
		$arrshscv["DAXULY"] = $arr_daxuly;
		$arrshscv["DAXULY_TRE"] = $dem_daxuly_tre;
		$arrshscv["DANGXULY_TRE"] = $dem_dangxuly_tre;
		$arrshscv["KETTHUC_TRE"] = $dem_ketthuc_tre;
		return $arrshscv;
		
	}
	
	static function getNameHSCV($year,$idHSCV,$type){
		/**
		 * xu ly van ban den : 
		*/
		$sql ="";
		switch($type){
			case 1: // xu ly van ban den
				$sql="
		SELECT
				distinct vbd.`ID_VBD` , vbd.`TRICHYEU`  , vbd.`SOKYHIEU`, vbd.`NGAYBANHANH`, fk_v_h.ID_HSCV,
				hscv.`NAME` as TENHS , hscv.`EXTRA` 
				
		FROM
				vbd_vanbanden_$year vbd
				inner join 
				(select * from vbd_fk_vbden_hscvs_$year  where `ID_HSCV` =?)
				fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
				inner join hscv_hosocongviec_$year hscv on hscv.`ID_HSCV`= fk_v_h.`ID_HSCV`
				
		";		
				break;
			case 3: // ho so mot cua
						$sql="
		SELECT
				hsmc.`TRICHYEU` , hsmc.`NHANLAI_NGAY`
		FROM
				motcua_hoso_$year hsmc
				inner join hscv_hosocongviec_$year hscv on hscv.`ID_HSCV` = hsmc.`ID_HSCV`
				where hscv.ID_HSCV=?
		";		
				break;
			case 2: //soan thao van ban
							$sql="
		SELECT
				cvst.`NAME` as TRICHYEU
		FROM
				hscv_congviecsoanthao_$year cvst
				inner join hscv_hosocongviec_$year hscv on hscv.`ID_HSCV` = cvst.`ID_HSCV`
				where hscv.ID_HSCV=?
		";		
				break;
			default:
		}
		
		if($sql != ""){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		try{
			$stm = $dbAdapter->query($sql,array($idHSCV));
			$re = $stm->fetch();
			$str_ten = "";
			if(count($re) != 0){
				switch($type){
					case 1:
							$str_ten = "
							<u>Hồ sơ:</u> <font color=gray ><b>".$re["TENHS"].$idHSCV." - ".$re["EXTRA"]."</b></font><br/>
							<u>Số ký hiệu:</u> <font color=gray ><b>".$re["SOKYHIEU"]."</b></font><br/><u>Ngày ban hành:</u> <font color=gray ><b>".QLVBDHCommon::MysqlDateToVnDate($re["NGAYBANHANH"])."</b></font><br/><u>Trích yếu:</u> <font color=gray ><b>".$re["TRICHYEU"]."</b></font>";
						break;
					case 2:
							$str_ten = "<font color=gray ><b>".$re["TRICHYEU"]."</b></font>";
						break;
					case 3:
							$str_ten = "<u>Tên hồ sơ:</u> <font color=gray ><b>".$re["TRICHYEU"]."</b></font><br/><u>Ngày trả:</u> <font color=gray ><b>".QLVBDHCommon::MysqlDateToVnDate($re["NHANLAI_NGAY"])."</b></font>";
						break;	
				}
				return  $str_ten;
			}
		}catch (Exception $ex ){
			return $ex->__toString();//"Không xác định văn bản";
		}
		}
		return array();
	}
	
	static function getDongLuanChuyenByHscv($arr_dongluanchuyen){
	
	}
	
	static function thongkePhongBan($year,$id_pb,$loai){
		$data_u = UsersModel::getUsersByDepartment($id_pb);
		//lay mang id nhan vien
		$arr_id = array();
		foreach ($data_u as $u){
			array_push($arr_id,$u["ID_U"]);
		}
		
		if(count($arr_id) > 0 ){
			$str_arrid = implode(',',$arr_id);
			//lay db adapter
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "
			select   pi.`IS_TRE` , pi.`IS_FINISH` , wfl.CXL , wfl.TRE_CV ,count(*) as  DEM from
			`wf_processitems_$year` pi
			inner join (select * from `hscv_hosocongviec_$year` where `ID_LOAIHSCV` = ? ) hscv
			on pi.ID_PI = hscv.`ID_PI`
			inner join  (SELECT *  , ( (TRE is Null) and (`ID_U_RECEIVE` in (".$str_arrid.") )) as CXL, ( (TRE > 0) and (`ID_U_RECEIVE` in (".$str_arrid.") )) as TRE_CV from `wf_processlogs_$year`
			where  `ID_U_RECEIVE` != 0 and
			(`ID_U_SEND` in (".$str_arrid.")  or `ID_U_RECEIVE` in (".$str_arrid."))  ) wfl
			on hscv.ID_PI = wfl.`ID_PI`
			group by   pi.`IS_TRE` , pi.`IS_FINISH`,wfl.CXL,wfl.TRE_CV
			
			";
			$stm = $dbAdapter->query($sql,array($loai));
			$rows =  $stm->fetchAll();
			
			//var_dump($rows);
			//tra ve cac thong tin sau
			// -- is_finish (0,1) | is_tre (0,1) | so luong 
			$dangxuly = 0;
			$cv_chua_xu_ly = 0;
			$cv_da_xu_ly =0;
			$cv_da_xu_ly_tre =0;
			$da_ket_thuc = 0;
			$da_ket_thuc_tre = 0;
			
			foreach ($rows as $row) {
				//var_dump($row);
				//echo "<br>";
				if($row["IS_FINISH"] == 1) //cac cong viec da ket thuc
				{
					$da_ket_thuc +=$row["DEM"];
					if($row["IS_TRE"] == 1)
						$da_ket_thuc_tre+=$row["DEM"];
				}
				if($row["IS_FINISH"] == 0) //cac cong viec chua ket thuc
				{
					$dangxuly+=$row["DEM"];
					if($row["CXL"] == 1){
						$cv_chua_xu_ly +=$row["DEM"];
					}
					else{
						$cv_da_xu_ly +=$row["DEM"];
						if($row["TRE_CV"]==1)
						$cv_da_xu_ly_tre +=$row["DEM"];
					}
				}			
			}
			
			$arr_re = array();
			$arr_re["DANGXULY"] = $dangxuly;
			$arr_re["CV_CHUA_XULY"] = $cv_chua_xu_ly;
			$arr_re["CV_DA_XULY"] = $cv_da_xu_ly;
			$arr_re["CV_DA_XULY_TRE"] = $cv_da_xu_ly_tre;
			$arr_re["KETTHUC"] = $da_ket_thuc;
			$arr_re["KETTHUC_TRE"] = $da_ket_thuc_tre;
		}
		
		return $arr_re;
	}
	static function thongkeNhanVien($year,$id_nv,$loai){
		
		
		if($id_nv > 0 ){
			
			//lay db adapter
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "
			select   pi.`IS_TRE` , pi.`IS_FINISH` , wfl.CXL , wfl.TRE_CV ,count(*) as  DEM from
			`wf_processitems_$year` pi
			inner join (select * from `hscv_hosocongviec_$year` where `ID_LOAIHSCV` = ? ) hscv
			on pi.ID_PI = hscv.`ID_PI`
			inner join  (SELECT *  , ( (TRE is Null) and (`ID_U_RECEIVE` = $id_nv )) as CXL, 
			( (TRE > 0) and (`ID_U_RECEIVE` = $id_nv )) as TRE_CV from `wf_processlogs_$year`
			where  `ID_U_RECEIVE` != 0 and
			(`ID_U_SEND` = $id_nv  or `ID_U_RECEIVE`=$id_nv)  ) wfl
			on hscv.ID_PI = wfl.`ID_PI`
			group by   pi.`IS_TRE` , pi.`IS_FINISH`,wfl.CXL,wfl.TRE_CV
			
			";
			$stm = $dbAdapter->query($sql,array($loai));
			$rows =  $stm->fetchAll();
			
			//tra ve cac thong tin sau
			// -- is_finish (0,1) | is_tre (0,1) | so luong 
			$dangxuly = 0;
			$cv_chua_xu_ly = 0;
			$cv_da_xu_ly =0;
			$cv_da_xu_ly_tre =0;
			$da_ket_thuc = 0;
			$da_ket_thuc_tre = 0;
			
			foreach ($rows as $row) {
				if($row["IS_FINISH"] == 1) //cac cong viec da ket thuc
				{
					$da_ket_thuc +=$row["DEM"];
					if($row["IS_TRE"] == 1)
						$da_ket_thuc_tre+=$row["DEM"];
				}
				if($row["IS_FINISH"] == 0) //cac cong viec chua ket thuc
				{
					$dangxuly+=$row["DEM"];
					if($row["CXL"] == 1){
						$cv_chua_xu_ly +=$row["DEM"];
					}
					else{
						$cv_da_xu_ly +=$row["DEM"];
						if($row["TRE_CV"]==1)
						$cv_da_xu_ly_tre +=$row["DEM"];
					}
				}			
			}
			
			$arr_re = array();
			$arr_re["DANGXULY"] = $dangxuly;
			$arr_re["CV_CHUA_XULY"] = $cv_chua_xu_ly;
			$arr_re["CV_DA_XULY"] = $cv_da_xu_ly;
			$arr_re["CV_DA_XULY_TRE"] = $cv_da_xu_ly_tre;
			$arr_re["KETTHUC"] = $da_ket_thuc;
			$arr_re["KETTHUC_TRE"] = $da_ket_thuc_tre;
		}
		
		return $arr_re;
	}
	
	//static function getDetailHscv(){
	static function getLoaiQuitrinhhosocongviec(){
		$sql = "  select * from wf_classes  ";
		try{
			$dbAdapter = Zend_Db_Table::GetDefaultAdapter();
			$qr = $dbAdapter->query($sql);
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
}
?>