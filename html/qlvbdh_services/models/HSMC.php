<?php
//require_once '../db/common.php';
class HSMC{
	public static function Select(){
		$sql = sprintf("
			SELECT * FROM SERVICES_MOTCUA_TRACUU WHERE ISGET = 0
			"
		);
		$row = query($sql);
		$sql = sprintf("
			UPDATE SERVICES_MOTCUA_TRACUU SET ISGET = 1 WHERE ISGET = 0
			"
		);
		query($sql);
		return $row;
	}
	public static function Insert($masohoso,$tentochuccanhan,$tenhoso,$trangthai,$ghichu){
		//Check ton tai
		$sql = sprintf("
			SELECT COUNT(*) as CNT FROM WEB_MOTCUA_TRACUU WHERE MASOHOSO = '%s'
			",
			mysql_real_escape_string($masohoso)
		);
		$cnt = query($sql);
		while($item = mysql_fetch_assoc($cnt)){
			if($item['CNT']==1){
				$sql = sprintf("
					UPDATE WEB_MOTCUA_TRACUU SET
						`KEY`='%s',`TENTOCHUCCANHAN`='%s',`TENHOSO`='%s',`TRANGTHAI`='%s',`GHICHU`='%s'
					WHERE
						`MASOHOSO`='%s'
					",
					mysql_real_escape_string($masohoso),
					mysql_real_escape_string($tentochuccanhan),
					mysql_real_escape_string($tenhoso),
					mysql_real_escape_string($trangthai),
					mysql_real_escape_string($ghichu),
					mysql_real_escape_string($masohoso)
				);
			    query($sql);
			}else{
				$sql = sprintf("
					INSERT INTO WEB_MOTCUA_TRACUU(
						`MASOHOSO`,`KEY`,`TENTOCHUCCANHAN`,`TENHOSO`,`TRANGTHAI`,`GHICHU`
					)VALUES(
						'%s','%s','%s','%s','%s','%s'
					)
					",
					mysql_real_escape_string($masohoso),
					mysql_real_escape_string($masohoso),
					mysql_real_escape_string($tentochuccanhan),
					mysql_real_escape_string($tenhoso),
					mysql_real_escape_string($trangthai),
					mysql_real_escape_string($ghichu)
				);
			    query($sql);
			}
		}
	}
	public static function GetInfo($masohoso, $barcode){
		$sql = sprintf("
			SELECT smc.*,mc.DIACHI,mc.NHANLAI_NGAY AS NGAY_NHANLAI FROM SERVICES_MOTCUA_TRACUU smc inner join MOTCUA_HOSO_".date("Y")." mc on smc.MASOHOSO=mc.MAHOSO WHERE smc.MASOHOSO = '%s' OR smc.BARCODE = '%s' ORDER BY smc.ID_TRACUU DESC LIMIT 0,1
		",mysql_real_escape_string($masohoso), mysql_real_escape_string($barcode));
		//return $sql;
		$row = query($sql);
		return $row;
	}
}