<?php
class VBDI{
	function SelectVBDIByNgayBH($NGAYBH){
		if(count(explode("\\",$NGAYBH))==3){
			$NGAYBH = implode("-",array_reverse(explode("\\",$NGAYBH)));
		}else if(count(explode("/",$NGAYBH))==3){
			$NGAYBH = implode("-",array_reverse(explode("/",$NGAYBH)));
		}else if(count(explode("-",$NGAYBH))==3){

		}
		$sql = sprintf("
			SELECT
				*
			FROM
				VBDI_VANBANDI_".date("Y")." vbdi
			WHERE
				NGAYBANHANH='%s'
		",
			mysql_real_escape_string($NGAYBH)
		);
		$row = query($sql);
		return $row;
	}
	function SelectVBDI($SOKYHIEU)
	{
		$sql = sprintf("
			SELECT
				*
			FROM
				VBDI_VANBANDI_".date("Y")." vbdi
			WHERE
				SOKYHIEU='%s'
		",
			mysql_real_escape_string($SOKYHIEU)
		);
		$row = query($sql);
		return $row;
	}
	function GetFile($SOKYHIEU){
		$sql = sprintf("
			SELECT
				ID_VBDI
			FROM
				VBDI_VANBANDI_".date("Y")." vbdi
			WHERE
				SOKYHIEU='%s'
		",
			mysql_real_escape_string($SOKYHIEU)
		);
		$row = query($sql);
		$idvbd = 0;
		//select file
		$data = "<DATA>";
		while ($rowitem = mysql_fetch_assoc($row)) {
			$idvbd=$rowitem['ID_VBDI'];
		}
		$sql = sprintf("
			SELECT
				*
			FROM
				GEN_FILEDINHKEM_".date("Y")." f
			WHERE
				ID_OBJECT='%s' AND TYPE=5
		",
			mysql_real_escape_string($idvbd)
		);
		//return $sql;
		$row = query($sql);
		while ($rowitem = mysql_fetch_assoc($row)) {
			$data.="<ITEM>";
			$data.="<FILENAME>";
			$data.=base64_encode($rowitem["FILENAME"]);
			$data.="</FILENAME>";
			$data.="<FILEDATA>";
			$data.=Common::Base64File($rowitem["FOLDER"]."\\".$rowitem["MASO"]);
			$data.="</FILEDATA>";
			$data.="</ITEM>";
		}
		$data.="</DATA>";
		return $data;
	}
}