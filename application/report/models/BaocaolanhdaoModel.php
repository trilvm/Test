<?php
require_once 'qtht/models/UsersModel.php';
class BaocaolanhdaoModel{
	static function getReportData($type,$todate,$fromdate,$id_pbs,$offset,$limit){
		/*Xu ly tham so*/
		
		$where_arr =  array();
		
		$str_arrid = "";
		if(count($id_pbs) >0){
		if(array_search("0",$id_pbs) == FALSE && $id_pbs[0] != "0"){
			$arr_id = array();
			foreach($id_pbs as $id_pb){
			$data_u = UsersModel::getUsersByDepartment($id_pb);
			//lay mang id nhan vien
			
			foreach ($data_u as $u){
				array_push($arr_id,$u["ID_U"]);
			}
			}
			if(count($arr_id) > 0 )
				$str_arrid = implode(',',$arr_id);
			else
				return array();
				
			}
		
		}
		
		if($str_arrid != "")
			$str_arrid = "in ( $str_arrid ) ";
		
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
		
		
		$where_hscv ="";
		if(count($where_arr) > 0)
			$where_hscv = " and" . implode(' and ',$where_arr)." ";
		
		
		$wheretype = "=$type";
		if($type ==3){
			$wheretype = ">2";
			//$type = 2;
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$limitstr="";
		if($limit>0)
		$limitstr =" LIMIT $offset,$limit ";
		
		$sql = "
		select  rs1.`ID_HSCV` , rs1.`ID_U_RECEIVE`  , rs1.`ID_T` ,
rs2.`NAME_DEP` as NAME_DEP   , NAME_U , rs1.`ID_PL`,rs1.ID_PI, HANXULY, DATESEND,rs1.IS_FINISH,rs1.IS_THEODOI

from
( select hscv.`ID_HSCV` , wfl.`ID_U_RECEIVE`  , DATESEND,hscv.IS_THEODOI,
wfl.`ID_T` ,  wfl.ID_PI   , wfl.`ID_PL`  , HANXULY , wfi.IS_FINISH from
(select ID_HSCV , ID_PI , IS_THEODOI from `".QLVBDHCommon::Table("hscv_hosocongviec")."` hscv1 where  hscv1.`ID_LOAIHSCV`$wheretype".$where_hscv.") hscv
inner join `".QLVBDHCommon::Table("wf_processlogs")."` wfl
on hscv.`ID_PI` = wfl.`ID_PI`
inner join `".QLVBDHCommon::Table("wf_processitems")."` wfi
on wfi.`ID_PI` = wfl.`ID_PI`
where wfl.`ID_U_RECEIVE`!=0 and wfl.`TRE` is NULL and wfl.`ID_U_RECEIVE` $str_arrid
) rs1
inner join
(
 select u.`ID_U` , concat(emp.`FIRSTNAME`, ' ',emp.`LASTNAME`) AS NAME_U , de.`ID_DEP` , de.`NAME` as NAME_DEP from
`qtht_users` u inner join `qtht_employees` emp on emp.`ID_EMP` = u.`ID_EMP`
 inner join `qtht_departments` de on de.`ID_DEP` = emp.`ID_DEP`

)rs2
on rs2.`ID_U` = rs1.ID_U_RECEIVE
".$where_pb." 
group by ID_HSCV ORDER BY ID_HSCV DESC
".$limitstr." 
";
		//echo $sql;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
	
	
	
	static function getCountReportData($type,$todate,$fromdate,$id_pbs){
			
		/*Xu ly tham so*/
		
		$where_arr =  array();
		
		$str_arrid = "";
		if(count($id_pbs) >0){
		if(array_search("0",$id_pbs) == FALSE && $id_pbs[0] != "0"){
			$arr_id = array();
			foreach($id_pbs as $id_pb){
			$data_u = UsersModel::getUsersByDepartment($id_pb);
			//lay mang id nhan vien
			
			foreach ($data_u as $u){
				array_push($arr_id,$u["ID_U"]);
			}
			}
			if(count($arr_id) > 0 )
				$str_arrid = implode(',',$arr_id);
			else
				return array();
				
			}
		
		}
		
		if($str_arrid != "")
			$str_arrid = "in ( $str_arrid ) ";
		
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
		
		
		$where_hscv ="";
		if(count($where_arr) > 0)
			$where_hscv = " and" . implode(' and ',$where_arr)." ";
		
		
		$wheretype = "=$type";
		if($type ==3){
			$wheretype = ">2";
			//$type = 2;
		}	
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select  count(*) as DEM
from
( select hscv.`ID_HSCV` , wfl.`ID_U_RECEIVE`  , DATESEND,
wfl.`ID_T` ,  wfl.ID_PI   , wfl.`ID_PL`  , HANXULY , wfi.IS_FINISH from
(select ID_HSCV , ID_PI from `".QLVBDHCommon::Table("hscv_hosocongviec")."` hscv1 where  hscv1.`ID_LOAIHSCV` $wheretype ".$where_hscv.") hscv
inner join `".QLVBDHCommon::Table("wf_processlogs")."` wfl
on hscv.`ID_PI` = wfl.`ID_PI`
inner join `".QLVBDHCommon::Table("wf_processitems")."` wfi
on wfi.`ID_PI` = wfl.`ID_PI`
where wfl.`ID_U_RECEIVE`!=0 and wfl.`TRE` is NULL and wfl.`ID_U_RECEIVE` $str_arrid
group by ID_HSCV
) rs1

";
		//echo $sql;
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re["DEM"];
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
				distinct vbd.`ID_VBD` , vbd.`TRICHYEU`  , vbd.`SOKYHIEU`, vbd.`NGAYBANHANH`,vbd.`COQUANBANHANH_TEXT` , fk_v_h.ID_HSCV,
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
							
							<u>Số ký hiệu:</u> <font color=gray ><b>".$re["SOKYHIEU"]."</b></font><br/><u>Ngày ban hành:</u> <font color=gray ><b>".QLVBDHCommon::MysqlDateToVnDate($re["NGAYBANHANH"])."</b></font><br/><u>Cơ quan ban hành:</u> <font color=gray ><b>".$re["COQUANBANHANH_TEXT"]."</b></font><br/><u>Trích yếu:</u> <font color=gray ><b>".$re["TRICHYEU"]."</b></font>";
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
			return $ex->cha__toString();//"Không xác định văn bản";
		}
		}
		return array();
	}
}