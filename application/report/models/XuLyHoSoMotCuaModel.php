<?php

class XulyhosomotcuaModel {
	
	
	static function getReportData($id_cqs,$id_lvbs,$fromdate,$todate,$id_phongbans){
		
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select hs_motcua.`TRICHYEU`,
dk.`NAME_DEP` AS NAME_DEP, dk.`NAME_U` AS U_SEND ,dk.`TR_NAME` as TR_NAME , dk.`ID_PL`,
hs_motcua.`ID_HOSO`,hs_motcua.`MAHOSO` , hs_motcua.`LEPHI`, hs_motcua.`NHAN_NGAY`, hs_motcua.`NGUOINHAN`,
		hs_motcua.`KHONGXULY` , hs_motcua.`NHANLAI_NGAY`,hs_motcua.`TENTOCHUCCANHAN`,hs_motcua.`TRANGTHAI`
from
(
     select  motcua.`TRICHYEU` , motcua.`ID_HOSO`,hs.`ID_HSCV`,motcua.`MAHOSO` , motcua.`LEPHI`, motcua.`NHAN_NGAY`, motcua.`NGUOINHAN`,
		motcua.`KHONGXULY` , motcua.`NHANLAI_NGAY`,motcua.`TENTOCHUCCANHAN`,motcua.`TRANGTHAI`
from `".QLVBDHCommon::Table("motcua_hoso")."` motcua
inner join `".QLVBDHCommon::Table("hscv_hosocongviec")."` hs on hs.`ID_HSCV` = motcua.`ID_HSCV`



) hs_motcua
inner join
(select  rs1.`ID_HSCV` , rs1.`ID_U_SEND`  , rs1.`ID_T` ,
rs2.`NAME_DEP` as NAME_DEP  , TR_NAME , NAME_U , rs1.`ID_PL`

from
( select hscv.`ID_HSCV` , wfl.`ID_U_SEND`  ,
wfl.`ID_T`   , wftp.`NAME` as TR_NAME  , wfl.`ID_PL`  from
(select ID_HSCV , ID_PI from `".QLVBDHCommon::Table("hscv_hosocongviec")."` hscv1 where  hscv1.`ID_LOAIHSCV` =3) hscv
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

) dk
on dk.ID_HSCV = hs_motcua.ID_HSCV
order by hs_motcua.`ID_HOSO` DESC , dk.`ID_PL`
		";
	
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
	
	
	
}

?>
