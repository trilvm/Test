<?php
//require_once '../db/common.php';
class Addin{
	function CheckPermissionOfDuThao($last_id_object,$nam,$username)
	{
		//lay phien ban du thao
		$sql = sprintf("
				SELECT PB.VERSION, DT.`ID_DUTHAO`,DT.TENDUTHAO,HSCV.NAME as TENHSCV FROM HSCV_PHIENBANDUTHAO_%s PB
					INNER JOIN HSCV_DUTHAO_%s DT ON PB.ID_DUTHAO = DT.ID_DUTHAO
					INNER JOIN HSCV_HOSOCONGVIEC_%s HSCV ON HSCV.`ID_HSCV` = DT.`ID_HSCV`
					INNER JOIN WF_PROCESSITEMS_%s WF ON HSCV.`ID_HSCV` = WF.`ID_O`
					INNER JOIN QTHT_USERS U ON WF.`ID_U` = U.`ID_U`
				WHERE
					PB.ID_PB_DUTHAO = %s
					AND U.`USERNAME`= '%s'
					AND WF.`IS_FINISH` = 0
			"
			,mysql_real_escape_string($nam)
			,mysql_real_escape_string($nam)
			,mysql_real_escape_string($nam)
			,mysql_real_escape_string($nam)
			,mysql_real_escape_string($last_id_object)
			,mysql_real_escape_string($username)
		);
		$result = query($sql);
		$item = mysql_fetch_assoc($result);

		//lay phien ban max
		$sql = sprintf("
			SELECT
				max(VERSION) as VERSION
			FROM
				HSCV_PHIENBANDUTHAO_%s pb
			WHERE
				pb.ID_DUTHAO = ".$item["ID_DUTHAO"]."
			GROUP BY pb.ID_DUTHAO
		"
		,mysql_real_escape_string($nam)
		);
		
		$result = query($sql);
		$itemversion = mysql_fetch_assoc($result);

		if($item["ID_DUTHAO"]>0 && $item["VERSION"]==$itemversion["VERSION"]){
			return base64_encode($item["ID_DUTHAO"]) . "~" . base64_encode($item["TENDUTHAO"]) . "~" . base64_encode($item["TENHSCV"]);
		}else{
			return base64_encode(0) . "~" . base64_encode(0) . "~" . base64_encode(0);
		}
	}
	function InsertPhienBanDuThao($idduthao,$idu,$filename,$realfilename,$nam,$thang){

		$realfilename = ereg_replace("[ ]+[(][0-9]+[)]", "", $realfilename);
		$realfilename = ereg_replace("[-][0-9]*[.][d][o][c]", ".doc", $realfilename);

		// Lấy id phiên bản dự thảo mới nhất
		$sql = sprintf("
			SELECT ID_PB_DUTHAO FROM HSCV_PHIENBANDUTHAO_%s WHERE ID_DUTHAO = %s ORDER BY VERSION DESC LIMIT 0,1
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($idduthao)
		);
		$r = query($sql);
		$itemversion = mysql_fetch_assoc($r);
		$idpbduthaoold = $itemversion["ID_PB_DUTHAO"];

		//insert phien ban
		$sql = sprintf("
			INSERT INTO HSCV_PHIENBANDUTHAO_%s (ID_DUTHAO,VERSION,ID_U,CHONBANHANH)
			SELECT
				%s
				,max(VERSION)+1
				,%s
				,0
			FROM
				HSCV_PHIENBANDUTHAO_%s pb
			WHERE
				pb.ID_DUTHAO = %s
			GROUP BY pb.ID_DUTHAO
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($idduthao)
		,mysql_real_escape_string($idu)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($idduthao)
		);
		query($sql);
		$idpb = mysql_insert_id();

		$sql = sprintf("
			INSERT INTO GEN_FILEDINHKEM_%s(
				FOLDER,
				ID_OBJECT,
				MASO,
				NAM,
				THANG,
				MIME,
				FILENAME,
				TYPE,
				CONTENT,
				USER,
				TIME_UPDATE
			)VALUES(
				'".PATH_FILE_UPLOAD."\\\\%s\\\\%s'
				,$idpb
				,'%s'
				,%s
				,%s
				,'application/msword'
				,'%s'
				,2
				,''
				,%s
				,'".date("Y-m-d H:i:s")."'
			)
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($filename)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($realfilename)
		,mysql_real_escape_string($idu)
		);
		query($sql);
		$idfiledk = mysql_insert_id();

		$realfilename = ereg_replace("[.][a-zA-Z]*", "", $realfilename);
		//copy cac file con lai

		//Select các file còn lại
		$sql = sprintf("
			SELECT
					FOLDER,
					".$idpb." as ID_OBJECT,
					MASO,
					NAM,
					THANG,
					MIME,
					FILENAME,
					TYPE,
					CONTENT,
					USER,
					TIME_UPDATE
				FROM
					GEN_FILEDINHKEM_%s
				WHERE
					ID_OBJECT = '%s'
					AND TYPE = 2
					AND NOT FILENAME LIKE '%s'
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($idpbduthaoold)
		,mysql_real_escape_string($realfilename)."%"
		);

		$r = query($sql);
		while ($row = mysql_fetch_assoc($r)) {
			//copy file
			$masonew = md5(rand().Date("Y-m-d H:i:s"));
			copy(
				PATH_FILE_UPLOAD."\\".$row["NAM"]."\\".$row["THANG"]."\\".$row["MASO"]
				, PATH_FILE_UPLOAD."\\".$row["NAM"]."\\".$row["THANG"]."\\".$masonew
			);
			$sql = sprintf("INSERT INTO GEN_FILEDINHKEM_%s(
					FOLDER,
					ID_OBJECT,
					MASO,
					NAM,
					THANG,
					MIME,
					FILENAME,
					TYPE,
					CONTENT,
					USER,
					TIME_UPDATE
				)VALUES(
					'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
					,'%s'
				)
				"
				,mysql_real_escape_string($nam)
				,mysql_real_escape_string($row["FOLDER"])
				,mysql_real_escape_string($idpb)
				,mysql_real_escape_string($masonew)
				,mysql_real_escape_string($row["NAM"])
				,mysql_real_escape_string($row["THANG"])
				,mysql_real_escape_string($row["MIME"])
				,mysql_real_escape_string($row["FILENAME"])
				,mysql_real_escape_string($row["TYPE"])
				,mysql_real_escape_string($row["CONTENT"])
				,mysql_real_escape_string($row["USER"])
				,mysql_real_escape_string($row["TIME_UPDATE"])
			);
			query($sql);
		}

		return $idfiledk;
	}
	function CreateDuThao($idu,$idhscv,$trichyeu,$idloaivb,$filename,$realfilename,$nam,$thang){
		//Tao du thao
		$sql = sprintf(
			"
				INSERT INTO HSCV_DUTHAO_%s(ID_HSCV,NGUOISOAN,TENDUTHAO,TRANGTHAI,ID_LVB)
				VALUES('%s',".$idu.",'%s',0,'%s')
			"
			,mysql_real_escape_string($nam)
			,mysql_real_escape_string($idhscv)
			,mysql_real_escape_string($trichyeu)
			,mysql_real_escape_string($idloaivb)
		);
		query($sql);
		$iddt = mysql_insert_id();
		//insert phien ban
		$sql = sprintf("
			INSERT INTO HSCV_PHIENBANDUTHAO_%s (ID_DUTHAO,VERSION,ID_U,CHONBANHANH)
			VALUES(
				".$iddt."
				,1
				,".$idu."
				,0
			)
		"
		,mysql_real_escape_string($nam)
		);
		//return $sql;
		query($sql);
		$idpb = mysql_insert_id();
		$sql = sprintf("
			INSERT INTO GEN_FILEDINHKEM_%s(
				FOLDER,
				ID_OBJECT,
				MASO,
				NAM,
				THANG,
				MIME,
				FILENAME,
				TYPE,
				CONTENT,
				USER,
				TIME_UPDATE
			)VALUES(
				'".PATH_FILE_UPLOAD."\\\\%s\\\\%s'
				,$idpb
				,'%s'
				,%s
				,%s
				,'application/msword'
				,'%s'
				,2
				,''
				,%s
				,'".date("Y-m-d H:i:s")."'
			)
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($filename)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($realfilename)
		,mysql_real_escape_string($idu)
		);
		query($sql);
		return $iddt;
	}

	function CreateFileAfterDS($idu,$idobject,$maso,$realfilename,$nam,$thang){
		$sql = sprintf("
			INSERT INTO GEN_FILEDINHKEM_%s(
				FOLDER,
				ID_OBJECT,
				MASO,
				NAM,
				THANG,
				MIME,
				FILENAME,
				TYPE,
				CONTENT,
				USER,
				TIME_UPDATE
			)VALUES(
				'".PATH_FILE_UPLOAD."\\\\%s\\\\%s'
				,'%s'
				,'%s'
				,%s
				,%s
				,'application/msword'
				,'%s'
				,-1
				,''
				,%s
				,'".date("Y-m-d H:i:s")."'
			)
		"
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($idobject)
		,mysql_real_escape_string($maso)
		,mysql_real_escape_string($nam)
		,mysql_real_escape_string($thang)
		,mysql_real_escape_string($realfilename)
		,mysql_real_escape_string($idu)
		);
		query($sql);
		return 1;
	}
}