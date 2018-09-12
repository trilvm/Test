<?php

class XulyvanbandenModel {
	
	
	static function getReportData($id_cqs,$id_lvbs,$fromdate,$todate,$id_phongbans){
		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
		 $where_fromdate = "vbd.`NGAYDEN` < '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
		$where_todate = "vbd.`NGAYDEN` > '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		
		$where_cq = "";
		if(count($id_cqs) >0){
		if(array_search("0",$id_cqs) == FALSE && $id_cqs[0] != "0"){
			$arr_cq = implode(',',$id_cqs);
			$where_cq="vbd.`ID_CQ` in (".$arr_cq.")";
			array_push($where_arr,$where_cq);
		}
		}
		$where_lvb = "";
		if(count($id_lvbs) >0){
		if(array_search("0",$id_lvbs) == FALSE && $id_lvbs[0] != "0"){
			$arr_lvb = implode(',',$id_lvbs);
			$where_lvb="vbd.`ID_LVB` in (".$arr_lvb.")";
			array_push($where_arr,$where_lvb);
		}
		}
		$where = implode(' and ',$where_arr);
		if($where !="") $where = "where " . $where; 
		echo $where;
		//$where ="";
		$where_pb = "";
		if(count($id_phongbans) >0){
		if(array_search("0",$id_phongbans) == FALSE && $id_phongbans[0] != "0"){
			$arr_pb = implode(',',$id_phongbans);
			$where_pb=" where rs2.`ID_DEP` in (".$arr_pb.")";
			
		}
		}
		echo $where_pb ;
		//$where_pb = "";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select
vbdse.`ID_VBD` , vbdse.`ID_HSCV` , vbdse.`MASOVANBAN` , vbdse.`SD`, vbdse.`SODEN`, vbdse.`ID_LVB`,
		vbdse.`ID_CQ`,vbdse.`TRICHYEU` ,vbdse.`NGAYDEN` , vbdse.`SOKYHIEU`,vbdse.`NGUOITAO`,
		dk.`NAME_DEP` AS NAME_DEP, dk.`NAME_U` AS U_SEND ,dk.`TR_NAME` as TR_NAME , dk.`ID_PL` 
from
(select vbd.`ID_VBD` , hs.`ID_HSCV` , vbd.`MASOVANBAN` , vbd.`SODEN` as SD, vbd.`SODEN`,
		vbd.`ID_CQ`,vbd.`ID_LVB` ,vbd.`TRICHYEU` ,vbd.`NGAYDEN` , vbd.`SOKYHIEU`,vbd.`NGUOITAO`
from `".QLVBDHCommon::Table("vbd_vanbanden")."` vbd
inner join `".QLVBDHCommon::Table("hscv_hosocongviec")."` hs on hs.`ID_HSCV` = vbd.`ID_HSCV`

".$where."

) vbdse
inner join
(select  rs1.`ID_HSCV` , rs1.`ID_U_SEND`  , rs1.`ID_T` ,
rs2.`NAME_DEP` as NAME_DEP  , TR_NAME , NAME_U , rs1.`ID_PL`

from
( select hscv.`ID_HSCV` , wfl.`ID_U_SEND`  ,
wfl.`ID_T`   , wftp.`NAME` as TR_NAME  , wfl.`ID_PL`  from
(select ID_HSCV , ID_PI from `".QLVBDHCommon::Table("hscv_hosocongviec")."` hscv1 where  hscv1.`ID_LOAIHSCV` =1) hscv
inner join `".QLVBDHCommon::Table("wf_processlogs")."` wfl
on hscv.`ID_PI` = wfl.`ID_PI`
inner join `wf_transitions` wft on wft.`ID_T` = wfl.`ID_T`
inner join `wf_transitionpools` wftp on wftp.`ID_TP` = wft.`ID_TP`
where wfl.`ID_U_RECEIVE`!=0
) rs1
inner join
(
 select u.`ID_U` , concat(emp.`FIRSTNAME`, ' ',emp.`LASTNAME`) AS NAME_U , de.`ID_DEP` , de.`NAME` as NAME_DEP from
`qtht_users` u inner join `qtht_employees` emp on emp.`ID_EMP` = u.`ID_EMP`
 inner join `qtht_departments` de on de.`ID_DEP` = emp.`ID_DEP`

)rs2
on rs2.`ID_U` = rs1.ID_U_SEND
".$where_pb."
) dk
on dk.ID_HSCV = vbdse.ID_HSCV
order by vbdse.`SD` DESC , dk.`ID_PL`
		
		";
	
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
	
	
	
}

?>
