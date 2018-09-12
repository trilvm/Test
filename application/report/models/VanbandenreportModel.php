<?php 
class VanbandenreportModel{
	static function getReportData($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong){
		$user = Zend_Registry::get('auth')->getIdentity();
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "vbd.`NGAYDEN` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "vbd.`NGAYDEN` <= '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		if($id_svb > 0)
		{
			$where_svb = " vb_svb.ID_SVB= '".$id_svb."'" ; 
			array_push($where_arr,$where_svb);
		}else{
//			if($user->DEPLEADER != 1){
//				$where_svb = " vb_svb.ID_DEP= '".$user->ID_DEP."'" ; 
//				array_push($where_arr,$where_svb);
//			}
                        $idSvb_arr = array();
			$svbs = SoVanBanModel::selectWithDep(0);
			foreach($svbs as $svb)
			{
				$idSvb_arr[] = $svb['ID_SVB'];
			}
			$where_svb = "vbd.`ID_SVB` IN( ".implode(', ', $idSvb_arr).")" ; 
			array_push($where_arr,$where_svb);
		}
		if($id_lvbs > 0)
		{
			if(array_search("0",$id_lvbs) == FALSE && $id_lvbs != "0"){
				$where_lvb = "vbd.`ID_LVB` = ".$id_lvbs; 
				array_push($where_arr,$where_lvb);	
			}
		}
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";
		
		//$year = QLVBDHCommon::getYear();
		//$sql = "Select * from vbd_vanbanden_$year 
		//$where
		//";
		$where_kxl = "";
		if($where == ""){
			$where_kxl = "where `IS_KHONGXULY` =1";
		}
		else{
			$where_kxl = $where." and `IS_KHONGXULY` =1";
		}
		
		if ($is_lienthong == 1) {
            $where .= ' and IS_LIENTHONG=1';
        }
		$sql = " select temp.`ID_VBD`,temp.ID_HSCV,temp.MASOVANBAN,temp.SD,temp.ID_CQ,temp.SODEN_IN,
					 temp.ID_LVB,temp.TRICHYEU,temp.GHICHU, temp.NGAYDEN,temp.SOKYHIEU,temp.NGUOITAO,temp.NGAYBANHANH, temp.NGAYHETHAN,
					 temp.SOBAN,temp.ID_SVB,temp.IS_KHONGXULY,temp.COQUANBANHANH_TEXT,temp.KQ_DEXUAT,temp.IS_LIENTHONG,temp.HANXULYTOANBO,temp.IS_FINISH,temp.TRE, 
					 GROUP_CONCAT(DISTINCT wfl.`ID_U_RECEIVE` ORDER BY wfl.`ID_PL` DESC SEPARATOR ',') as NAME_U,
					 GROUP_CONCAT(DISTINCT ph.`ID_U` ORDER BY ph.`ID_PHOIHOP` DESC SEPARATOR ',') as NAME_PH,
					 GROUP_CONCAT(DISTINCT dlc.`NGUOINHAN` ORDER BY dlc.`ID_DLC` DESC SEPARATOR ',') as NAME_DB,					
					 GROUP_CONCAT(DISTINCT concat(dk.`MASO`, '~', vbdi.SOKYHIEU, '~', dk.FILENAME) ORDER BY dk.`ID_DK` DESC SEPARATOR ',') as FILE_MASO,
					 vbdi.`TRICHYEU` as TRICHYEU_FILE,dk.`NAM`,temp.SODEN , vbdi.SOKYHIEU as SOKYHIEU_VBDI , hs.IS_THEODOI as THEODOI , hsltd.U_OWN as NGUOILUUKETTHUC, gen_dk.`FILENAME` as TEP_DINHKEM
					  from
					 ( select vbd.`ID_VBD` ,vbd.HANXULYTOANBO,vbd.TRE,vbd.IS_FINISH, hs.`ID_HSCV` , vbd.`MASOVANBAN`, vbd.NGAYHETHAN , vbd.`SODEN` as SD,
					 vbd.`SODEN`, vbd.`SODEN_IN`, vbd.`ID_CQ`,vbd.`ID_LVB` ,vbd.`TRICHYEU` , vbd.GHICHU,
					 vbd.`NGAYDEN` , vbd.`SOKYHIEU`, vbd.`NGUOITAO`,vbd.`NGAYBANHANH`, vbd.`SOBAN`,
					 vbd.ID_SVB,vbd.`IS_KHONGXULY` , vbd.`COQUANBANHANH_TEXT` , hsltd.COMMENT as
					 KQ_DEXUAT,vbd.IS_LIENTHONG,
					 vb_svb.ID_DEP,hs.`ID_PI` from  `".QLVBDHCommon::Table("vbd_vanbanden")."` vbd
					 left join vb_sovanban vb_svb on vbd.ID_SVB = vb_svb.ID_SVB
					 left join  `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` fk_vbd_hs on fk_vbd_hs.ID_VBDEN = vbd.ID_VBD
					 left join  `".QLVBDHCommon::Table("hscv_hosocongviec")."`hs on hs.`ID_HSCV` = fk_vbd_hs.`ID_HSCV` 
					 left join  `".QLVBDHCommon::Table("hscv_hosoluutheodoi")."`hsltd on hs.ID_HSCV = hsltd.ID_HSCV

					 $where) temp
					 left join  `".QLVBDHCommon::Table("hscv_phoihop")."` ph on ph.`ID_HSCV`= temp.ID_HSCV
					 left join  `".QLVBDHCommon::Table("vbd_dongluanchuyen")."` dlc on dlc.`ID_VBD`= temp.ID_VBD
					 left join  `".QLVBDHCommon::Table("wf_processlogs")."` wfl on temp.`ID_PI` = wfl.`ID_PI`				
					 left join `".QLVBDHCommon::Table("vbdi_vanbandi")."` vbdi on vbdi.`ID_HSCV` = temp.ID_HSCV
					  left join `".QLVBDHCommon::Table("hscv_hosocongviec")."` hs on hs.`ID_HSCV` = temp.ID_HSCV
					  left join  `".QLVBDHCommon::Table("hscv_hosoluutheodoi")."`hsltd on hsltd.ID_HSCV = temp.ID_HSCV
					 left join `".QLVBDHCommon::Table("gen_filedinhkem")."`dk on dk.`ID_OBJECT`=vbdi.`ID_VBDI` and dk.`TYPE` =5
					 left join  `".QLVBDHCommon::Table("gen_filedinhkem")."`gen_dk on gen_dk.ID_OBJECT = temp.ID_VBD
					 
					 group by temp.`ID_VBD` order by temp.SODEN_IN ASC
					";
//echo $sql; exit;
	//order by vbdse.`SD` DESC , dk.`ID_PL`	
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}

static function getReportDatacvtlt($fromdate,$todate,$id_svb,$id_lvbs,$is_lienthong){
      
		$user = Zend_Registry::get('auth')->getIdentity();
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "`NGAYDEN` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "`NGAYDEN` <= '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		if($id_svb > 0)
		{     
			$where_svb = " vb_svb.ID_SVB= '".$id_svb."'" ; 
			array_push($where_arr,$where_svb);
		}else{
			if($user->DEPLEADER != 1){
				$where_svb = " vb_svb.ID_DEP= '".$user->ID_DEP."'" ; 
				array_push($where_arr,$where_svb);
			}
		}
		if($id_lvbs > 0)
		{
			if(array_search("0",$id_lvbs) == FALSE && $id_lvbs != "0"){
				$where_lvb = "vbd.`ID_LVB` = ".$id_lvbs; 
				array_push($where_arr,$where_lvb);	
			}
		}
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";
		
		//$year = QLVBDHCommon::getYear();
		//$sql = "Select * from vbd_vanbanden_$year 
		//$where
		//";
		$where_kxl = "";
		if($where == ""){
			$where_kxl = "where `IS_KHONGXULY` =1";
		}
		else{
			$where_kxl = $where." and `IS_KHONGXULY` =1";
		}
		if ($is_lienthong == 1) {
            $where .= ' and IS_LIENTHONG=1';
        }
		$sql = "
		 
	(select vbd.`ID_VBD` , GROUP_CONCAT(hs.`ID_HSCV`) as ID_HSCV , vbd.`MASOVANBAN` , vbd.`SODEN` as SD, vbd.`SODEN`, vbd.`SODEN_IN`, 
		vbd.`ID_CQ`,vbd.`ID_LVB` ,vbd.`TRICHYEU` ,vbd.GHICHU, vbd.`NGAYDEN` , vbd.`SOKYHIEU`,vbd.`NGUOITAO`,vbd.`NGAYBANHANH`,
		vbd.`SOBAN`,vbd.ID_SVB,vbd.`IS_KHONGXULY` , vbd.`COQUANBANHANH_TEXT` , hsltd.COMMENT as KQ_DEXUAT,
		GROUP_CONCAT(wfl.`ID_U_RECEIVE`) as ID_U_WLF,GROUP_CONCAT(dlc.`NGUOINHAN`) as ID_U_DLC,GROUP_CONCAT(hscv_ph.`ID_U`) as ID_U_PH, CONCAT(empkynhan.FIRSTNAME,' ',empkynhan.LASTNAME) as NAMEKYNHAN
from `".QLVBDHCommon::Table("vbd_vanbanden")."` vbd
inner join vb_sovanban vb_svb on vbd.ID_SVB = vb_svb.ID_SVB
inner join `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` fk_vbd_hs on fk_vbd_hs.ID_VBDEN = vbd.ID_VBD
inner join `".QLVBDHCommon::Table("hscv_hosocongviec")."` hs on hs.`ID_HSCV` = fk_vbd_hs.`ID_HSCV`
left join `".QLVBDHCommon::Table("hscv_hosoluutheodoi")."` hsltd on hs.ID_HSCV = hsltd.ID_HSCV
left join  `".QLVBDHCommon::Table("wf_processlogs")."` wfl on hs.ID_PI = wfl.ID_PI
left join `".QLVBDHCommon::Table("vbd_dongluanchuyen")."`  dlc on dlc.`ID_VBD` = vbd.`ID_VBD`
left join `".QLVBDHCommon::Table("hscv_phoihop")."`  hscv_ph on hs.ID_HSCV = hscv_ph.`ID_HSCV`
left join qtht_users ukynhan on ukynhan.ID_U = vbd.ID_U_TRINHLDVP
left join qtht_employees empkynhan on ukynhan.ID_EMP = empkynhan.ID_EMP
".$where."
group by vbd.`ID_VBD`
)
union (
	
	select vbd.`ID_VBD` , NULL as ID_HSCV , vbd.`MASOVANBAN` , vbd.SODEN as `SD`, vbd.`SODEN`, vbd.`SODEN_IN`,vbd.`ID_LVB`,
		vbd.`ID_CQ`,vbd.`TRICHYEU` ,vbd.GHICHU,vbd.`NGAYDEN` , vbd.`SOKYHIEU`,vbd.`NGUOITAO`, vbd.`NGAYBANHANH`,vbd.SOBAN,vbd.ID_SVB, vbd.`IS_KHONGXULY`, vbd.`COQUANBANHANH_TEXT` , NULL as KQ_DEXUAT
		,null,null,null,'' from
		 ".QLVBDHCommon::Table("vbd_vanbanden")." vbd
		 inner join vb_sovanban vb_svb on vbd.ID_SVB = vb_svb.ID_SVB
		 $where_kxl	
		 
	
)

order by SODEN_IN  
		";
	//order by vbdse.`SD` DESC , dk.`ID_PL`	
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}

static function getNguoiXulyFromVban($id_u){

	$sql = "select DISTINCT(u.ID_U),concat(emp.`FIRSTNAME`, ' ', emp.`LASTNAME`) as NAME_U,de.`NAME` as NAME_DEP,de.ISLEADER as ISLEADER
from `qtht_users` u
	 inner join `qtht_employees` emp on emp.`ID_EMP` = u.`ID_EMP`
	 inner join `qtht_departments` de on de.`ID_DEP` = emp.`ID_DEP`
where u.id_u in (".str_replace(",,",",", trim(implode(",",$id_u),", ")).")
		";
   //echo $sql;return;
  //echo $sql;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql);
	$re = $query->fetchAll();
	$stt = 1;
	$name = array();
	$namedep = array();
	foreach ($re as $row){
		//if($stt == 2){
			//$name_u = $row["NAME_U"]."<br/>".$name_u ;
			//break;	
		//}
		
		if($row["ISLEADER"]==1){
			$namedep[]=$row["NAME_U"];
		}else{
			$name[]=$row["NAME_DEP"];
		}
		$stt ++;
	}
	$name = array_unique($name);
	$namedep = array_unique($namedep);
	$name_u = (count($namedep)>0?(implode("<br>",$namedep)."<br>"):"").implode("<br>",$name);
	
	return $name_u;
}
static function getNguoiXulyFromVban2($id_hscv){
	
	$sql = "
		select   *    from ( select  ID_HSCV , ID_PI from `".QLVBDHCommon::Table("hscv_hosocongviec")."` hscv1 ) hscv
		inner join " . QLVBDHCommon::Table("wf_processlogs") . " wfl on hscv.ID_PI = wfl.ID_PI 
		inner join
		(
			select u.`ID_U` , concat(emp.`FIRSTNAME`, ' ',emp.`LASTNAME`) AS NAME_U , de.`ID_DEP` , de.`NAME` as NAME_DEP from
			`qtht_users` u inner join `qtht_employees` emp on emp.`ID_EMP` = u.`ID_EMP`
			inner join `qtht_departments` de on de.`ID_DEP` = emp.`ID_DEP`

		)rs2
		on rs2.`ID_U` = wfl.ID_U_RECEIVE 
		where hscv.ID_HSCV =?  order by wfl.`ID_PL` DESC
		";
  // echo $sql;
 // echo $id_hscv;exit;
	//$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_hscv));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){		
		if($row["ISLEADER"]==1){
			$namedep[]=$row["NAME_U"];
		}else{
			$name[]=$row["NAME_DEP"];
		}
		$stt ++;
	}
	$name = array_unique($name);
	$namedep = array_unique($namedep);
	$name_u = (count($namedep)>0?(implode("<br>",$namedep)."<br>"):"").implode("<br>",$name);
	
	return $name_u;
	
}

	static function getHscvsByIdVbd($id_vbd){
		$sql = " select hs.ID_HSCV from `".QLVBDHCommon::Table("vbd_vanbanden")."` vbd
			inner join `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` fk_vbd_hs on fk_vbd_hs.ID_VBDEN = vbd.ID_VBD
			inner join `".QLVBDHCommon::Table("hscv_hosocongviec")."` hs on hs.`ID_HSCV` = fk_vbd_hs.`ID_HSCV`
			where ID_VBD = ?
			";
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($id_vbd));
		$re = $query->fetchAll();
		return $re;
	}
	static function getNguoibutpheFromVbanden($id_vbden){
	
	 $sql = "select NGUOIKY as ID_U from `".QLVBDHCommon::Table("hscv_butphe")."` where ID_VBD = ? 
		";
  // echo $sql;
   // echo $id_vbden;exit;
	//$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbden));
	$re = $query->fetchAll();
	//$stt = 1;
	//foreach ($re as $row){
		//if($stt == 2){
	//		$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOIKY"])."<br/>".$name_u ;
			 //break;	
		//}
	//	$stt ++;
	//}
//	return $name_u;
     
    return $re;
	
}

static function getNguoiXuly(){	
	$sql = "select u.ID_U,concat(emp.`FIRSTNAME`, ' ',emp.`LASTNAME`) AS NAME_U  from
			`qtht_users` u inner join `qtht_employees` emp on emp.`ID_EMP` = u.`ID_EMP`
			inner join `qtht_departments` de on de.`ID_DEP` = emp.`ID_DEP`			 
		"; 
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql);
	$re = $query->fetchAll();	
	$ten=array();
	foreach($re as $reitem){
	    $ten[$reitem['ID_U']]=$reitem['NAME_U'];
	}
	return $ten;
}

static function getNguoinhanFromVbanden($id_vbd){
	
	$sql = "select * from `".QLVBDHCommon::Table("vbd_dongluanchuyen")."` where ID_VBD = ?
		";
  //echo $sql;exit;
//  echo $id_hscv;
	$name_u = "";
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$query = $dbAdapter->query($sql,array($id_vbd));
	$re = $query->fetchAll();
	$stt = 1;
	foreach ($re as $row){
		//if($stt == 2){
			$name_u = UsersModel::getEmloyeeNameByIdUser($row["NGUOINHAN"])."<br/>".$name_u ;
			//break;	
		//}
		$stt ++;
	}
	return $name_u;

}
    //vuld 6/8/2018 add define func getReportDataCongViec
    static function getReportDataCongViec($fromdate,$todate,$id_svb,$id_lvbs){
		$user = Zend_Registry::get('auth')->getIdentity();
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "vbd.`NGAYDEN` >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "vbd.`NGAYDEN` <= '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";
		$where_kxl = "";
		if($where == ""){
			$where_kxl = "where `IS_KHONGXULY` =1";
		}
		else{
			$where_kxl = $where." and `IS_KHONGXULY` =1";
		}
				$where .= ' and IS_CONGVIEC=1';
				// 2/8/2018 vuld khong get vb khong co han xu ly
				$where .= " and vbd.NGAYHETHAN != '1970-01-01 00:00:00' and vbd.NGAYHETHAN != '0000-00-00 00:00:00' and vbd.NGAYHETHAN != '' ";
				//vuld end
				$sql = " select temp.`ID_VBD`,temp.`TENLOAI`,temp.ID_HSCV,temp.MASOVANBAN,temp.SD,temp.ID_CQ,temp.SODEN_IN,
                        temp.ID_LVB,temp.TRICHYEU,temp.GHICHU, temp.NGAYDEN,temp.SOKYHIEU,temp.NGUOITAO,temp.NGAYBANHANH,
                        temp.SOBAN,temp.ID_SVB,temp.IS_KHONGXULY,temp.COQUANBANHANH_TEXT,temp.KQ_DEXUAT,temp.IS_CHOXULY,
                        GROUP_CONCAT(DISTINCT wfl.`ID_U_RECEIVE` ORDER BY wfl.`ID_PL` DESC SEPARATOR ',') as NAME_U,
                        GROUP_CONCAT(DISTINCT ph.`ID_U` ORDER BY ph.`ID_PHOIHOP` DESC SEPARATOR ',') as NAME_PH,
                        GROUP_CONCAT(DISTINCT dlc.`NGUOINHAN` ORDER BY dlc.`ID_DLC` DESC SEPARATOR ',') as NAME_DB,					
                        GROUP_CONCAT(DISTINCT concat(dk.`MASO`, '~', vbdi.SOKYHIEU, '~', dk.FILENAME) ORDER BY dk.`ID_DK` DESC SEPARATOR ',') as FILE_MASO,
                        vbdi.`TRICHYEU` as TRICHYEU_FILE,dk.`NAM`,temp.SODEN
                         from
                        ( select hs.IS_CHOXULY, vbd.`ID_VBD` , hs.`ID_HSCV` , vbd.`MASOVANBAN` , vbd.`SODEN` as SD,
                        vbd.`SODEN`, vbd.`SODEN_IN`, vbd.`ID_CQ`,vbd.`ID_LVB` ,vbd.`TRICHYEU` , vbd.GHICHU,
                        vbd.`NGAYDEN` , vbd.`SOKYHIEU`, vbd.`NGUOITAO`,vbd.`NGAYBANHANH`, vbd.`SOBAN`,
                        vbd.ID_SVB,vbd.`IS_KHONGXULY` , vbd.`COQUANBANHANH_TEXT` , hsltd.COMMENT as
                        KQ_DEXUAT,vb_svb.ID_DEP,hs.`ID_PI`,lvb.NAME as TENLOAI from  `".QLVBDHCommon::Table("vbd_vanbanden")."` vbd
                        left join vb_sovanban vb_svb on vbd.ID_SVB = vb_svb.ID_SVB
                        left join  `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` fk_vbd_hs on fk_vbd_hs.ID_VBDEN = vbd.ID_VBD
                        left join  `".QLVBDHCommon::Table("hscv_hosocongviec")."`hs on hs.`ID_HSCV` = fk_vbd_hs.`ID_HSCV`
                        left join  `".QLVBDHCommon::Table("hscv_hosoluutheodoi")."`hsltd on hs.ID_HSCV = hsltd.ID_HSCV
                        left join `VB_LOAIVANBAN` lvb on lvb.`ID_LVB`=vbd.`ID_LVB`
                        $where) temp
                        left join  `".QLVBDHCommon::Table("hscv_phoihop")."` ph on ph.`ID_HSCV`= temp.ID_HSCV
                        left join  `".QLVBDHCommon::Table("vbd_dongluanchuyen")."` dlc on dlc.`ID_VBD`= temp.ID_VBD
                        left join  `".QLVBDHCommon::Table("wf_processlogs")."` wfl on temp.`ID_PI` = wfl.`ID_PI`				
                        left join `".QLVBDHCommon::Table("vbdi_vanbandi")."` vbdi on vbdi.`ID_HSCV` = temp.ID_HSCV
                        left join `".QLVBDHCommon::Table("gen_filedinhkem")."`dk on dk.`ID_OBJECT`=vbdi.`ID_VBDI` and dk.`TYPE` =5
                        group by temp.`ID_VBD` order by temp.SODEN_IN DESC
                       ";
                //echo $sql; exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
    //vuld end
}
?>