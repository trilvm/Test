<?php
//require_once '../db/common.php';
class LCT{
	function SelectAlarm()
	{
		$sql = "
			SELECT
				lct.ID_LCT_P, lct.NOIDUNG, lct.DIADIEM, DATE_FORMAT(lct.NHACNHO,'%d/%m/%Y %Hh%i') AS NHACNHO, emp.PHONE, emp.EMAIL
			FROM
				LCT2_PERSONAL_".date("Y")." lct
				inner join QTHT_USERS u on lct.ID_U = u.ID_U
				inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE
				lct.NHACNHO IS NOT NULL AND
				(lct.SMS = 1 OR lct.EMAIL=1) AND
				lct.NHACNHO - INTERVAL lct.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."' AND
				(emp.PHONE <> '' OR emp.EMAIL <> '')
		";
		$row = query($sql);
		$sql = "update LCT2_PERSONAL_".date("Y")." lct SET lct.SMS=0,lct.EMAIL=0 where (lct.SMS = 1 OR lct.EMAIL=1) AND NHACNHO - INTERVAL lct.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."'";
		query($sql);
		return $row;
	}
}