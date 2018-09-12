<?php
//require_once '../db/common.php';
class HSCV{
	function SelectHSCV()
	{
		$sql = "
			SELECT
				pl.ID_PL, hscv.NAME AS NOIDUNG, DATE_FORMAT(hscv.NGAY_KT,'%d/%m/%Y %Hh%i') AS NHACNHO, emp.PHONE, emp.EMAIL
			FROM
				HSCV_HOSOCONGVIEC_".date("Y")." hscv
				inner join WF_PROCESSITEMS_".date("Y")." pi on hscv.ID_HSCV = pi.ID_O
				inner join WF_PROCESSLOGS_".date("Y")." pl on pi.ID_PI = pl.ID_PI
				inner join QTHT_USERS u on pl.ID_U_RECEIVE = u.ID_U
				inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE
				(pl.SMS = 1 OR pl.EMAIL=1)
				AND (emp.PHONE <> '' OR emp.EMAIL <> '')";
		/*
				hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."' AND
				(emp.PHONE <> '' OR emp.EMAIL <> '')
		";
		*/
		//echo $sql;exit;
		//return $sql;
		$row = query($sql);
		$sql = "update WF_PROCESSLOGS_".date("Y")." pl SET SMS=0,EMAIL=0 where pl.SMS=1 OR pl.EMAIL=1";// AND hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."'";
		query($sql);
		return $row;
	}
}