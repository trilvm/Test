<?php
//require_once '../db/common.php';
class TD{
	function SelectTD()
	{
		$sql = "
			SELECT
				TDN.ID_NHAN,TD.TIEUDE AS NOIDUNG,DATE_FORMAT(TD.NGAYTAO,'%d/%m/%Y %Hh%i') AS NHACNHO, EMP.PHONE, EMP.EMAIL
			FROM
				TD_THONGTIN_".date("Y")." TD
				INNER JOIN TD_NHAN_".date("Y")." TDN ON TD.ID_THONGTIN = TDN.ID_THONGTIN
				INNER JOIN QTHT_USERS U ON TDN.NGUOINHAN = U.ID_U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
			WHERE
				(TD.SMS = 1 OR TD.EMAIL=1)
				AND
				(EMP.PHONE <> '' OR EMP.EMAIL <> '')
		";
		/*
				hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."' AND
				(emp.PHONE <> '' OR emp.EMAIL <> '')
		";
		*/
		//return $sql;
		$row = query($sql);
		$sql = "update TD_THONGTIN_".date("Y")." td SET SMS=0,EMAIL=0 where td.SMS=1 OR td.EMAIL=1";// AND hscv.NGAY_KT - INTERVAL hscv.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."'";
		query($sql);
		return $row;
	}
}