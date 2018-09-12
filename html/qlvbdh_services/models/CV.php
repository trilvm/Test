<?php
//require_once '../db/common.php';
class CV{
	function SelectCV()
	{
		$sql = "
			SELECT
				hscv.ID_HSCV,hscv.NAME AS NOIDUNG, DATE_FORMAT(hscv.NGAY_KT,'%d/%m/%Y %Hh%i') AS NHACNHO, emp.PHONE, emp.EMAIL
			FROM
				HSCV_HOSOCONGVIEC_".date("Y")." hscv
				inner join QTHT_USERS u on hscv.ID_U_NN = u.ID_U
				inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE
				(hscv.SMS = 1 OR hscv.EMAIL=1)
				AND (emp.PHONE <> '' OR emp.EMAIL <> '')";
		/*
				hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."' AND
				(emp.PHONE <> '' OR emp.EMAIL <> '')
		";
		*/
		//return $sql;
		$row = query($sql);
		$sql = "update HSCV_HOSOCONGVIEC_".date("Y")." hscv SET SMS=0,EMAIL=0 where hscv.SMS=1 OR hscv.EMAIL=1";// AND hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."'";
		query($sql);
		return $row;
	}
}