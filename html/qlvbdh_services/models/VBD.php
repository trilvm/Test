<?php
//require_once '../db/common.php';
class VBD{
	function SelectVBD()
	{
		$sql = "
			SELECT
				vbd.ID_VBD, vbd.TRICHYEU as NOIDUNG, DATE_FORMAT(lc.NGAYCHUYEN,'%d/%m/%Y %Hh%i') AS NHACNHO, emp.PHONE, emp.EMAIL
			FROM
				VBD_VANBANDEN_".date("Y")." vbd
				inner join VBD_DONGLUANCHUYEN_".date("Y")." lc on lc.ID_VBD = vbd.ID_VBD
				inner join QTHT_USERS u on lc.NGUOINHAN = u.ID_U
				inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE
				(lc.SMS = 1 OR lc.EMAIL=1) AND
				(emp.PHONE <> '' OR emp.EMAIL <> '')
		";
		$row = query($sql);
		$sql = "update VBD_DONGLUANCHUYEN_".date("Y")." vbd SET SMS=0,EMAIL=0 where (vbd.SMS = 1 OR vbd.EMAIL=1)";
		query($sql);
		return $row;
	}
}