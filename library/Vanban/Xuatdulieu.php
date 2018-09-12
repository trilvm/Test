<?php
require_once 'qtht/models/CoQuanModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'mailclient/models/EmailEngine/EmailEngineOut.php';
class Vanban_Xuatdulieu{
	static function exportInfoFromSystem($id,$loai_vb){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		if((int)$id == 0)
			return array(); //khong co van ban can tim
		$table_name = "";
		$id_name = "";
		if($loai_vb == 1){ //van ban den
			$table_name = QLVBDHCommon::Table('vbd_vanbanden');
			$id_name = "ID_VBD";
		}
		else if($loai_vb == 2){
			$table_name = QLVBDHCommon::Table('vbdi_vanbandi');
			$id_name = "ID_VBDI";
		}else if((int)$loai_vb == 0){//mac dinh la van ban di
			$table_name = QLVBDHCommon::Table('vbdi_vanbandi');
			$id_name = "ID_VBDI";
		}else if((int)$loai_vb > 2){
			return ;
		}
		$sql = "
			select * from $table_name where $id_name=?
		";
		//echo $sql;
		try{
			$query = $dbAdapter->query($sql,array($id));
			return $query->fetch();
			
		}catch(Exception $ex){
			return array();
		}
	}

	static function exportToXML($arr_data){
		
		//var_dump($arr_data); exit;
		if(count($arr_data ) ==0)
			return -1; //Khong ton tai van ban

		//var_dump($arr_data);
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->createElement('Vanban','');
		$root->setAttribute('MASO', $arr_data["MASOVANBAN"]);
		
		//So ky hieu
		$skhtag = $dom->createElement('SOKYHIEU',$arr_data["SOKYHIEU"]);
		$root->appendChild($skhtag);
		$root->appendChild($skhtag);
		$cqbhtag = $dom->createElement('COQUANBANHANH',CoQuanModel::getNameById($arr_data["ID_CQ"]));
		$root->appendChild($cqbhtag);
		$date_vn = implode("-",array_reverse(explode("-",$arr_data["NGAYBANHANH"])));
		$nbhtag = $dom->createElement('NGAYBANHANH',base64_encode($date_vn));
		$nbhtag->setAttribute('ENCODING','BASE64');
		$root->appendChild($nbhtag);
		
		$tytag = $dom->createElement('TRICHYEU',base64_encode($arr_data["TRICHYEU"]));
		$tytag->setAttribute('ENCODING','BASE64');
		$root->appendChild($tytag);

		$nktag = $dom->createElement('NGUOIKY',UsersModel::getEmloyeeNameByIdUser($arr_data["NGUOIKY"]));
		$root->appendChild($nktag);
		
		$md5hash = md5($arr_data["SOKYHIEU"].CoQuanModel::getNameById($arr_data["ID_CQ"]).base64_encode($date_vn).
		base64_encode($arr_data["TRICHYEU"]).UsersModel::getEmloyeeNameByIdUser($arr_data["NGUOIKY"])	
		);
		$md5tag = $dom->createElement('MD5',$md5hash);

		$root->appendChild($md5tag);

		

		$dom->appendChild($root);
		return $dom->saveXML();
		
	}
	
	static function exportToSubject($maso){
		/**/
		$config_general = new Zend_Config_Ini('../application/config.ini', 'general');
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->createElement('SUBJECT');
		$protocol = 'UNITEK_VB_MAIL';
		$protocoltag = $dom->createElement('PROTOCOL',$protocol);
		$root->appendChild($protocoltag);
		$version = '1.0';
		$versiontag = $dom->createElement('VERSION',$version);
		$root->appendChild($versiontag);
		$name_systag = $dom->createElement('NAME_SYS',$config_general->sys_info->name_system);
		$root->appendChild($name_systag);
		$company_senttag = $dom->createElement('COMPANY_SENT',$config_general->sys_info->company);
		$root->appendChild($company_senttag);
		$document_identitytag= $dom->createElement('DOCUMENT_IDENTITY',$maso);
		$root->appendChild($document_identitytag);
		$md5hash = md5($protocol.$version.$config_general->sys_info->name_system.$config_general->sys_info->company.$maso);
		$md5_tag = $dom->createElement('MD5',$md5hash);		
		$root->appendChild($md5_tag);
		$dom->appendChild($root);
		return $dom->saveXML();
	}
	/**
	** Tra ve ma so cua file dinh kem
	*/
	static function exportAttachFromSystem($id,$loai_vb){
		//5 :đi
		//3: van ban den
		$type = 5;
		if($loai_vb == 1)
			$type = 3;
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select MASO from ".QLVBDHCommon::Table('gen_filedinhkem')." where
			TYPE = ? and ID_OBJECT=?
		";
		
		try{
			$qr = $dbAdapter->query($sql,array($type,$id));
			$rows = $qr->fetchAll();
			$arr_maso = array();
			foreach($rows as $row){
				array_push($arr_maso,$row["MASO"]);
			}
			
			//var_dump($rows); exit;
			return $arr_maso;
		}catch(Exception $ex){
			return array();
		}

	}

	static function insertIntotDatabase($arr_data){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			insert into ".QLVBDHCommon::Table('vbmail_vanbanchuyen')." 
			(`ID_VBDI`,`CQ_NHAN`,`EMAIL_NHAN`,`DA_CHUYEN`,`ID_CQ`,`ID_U`)
			values (?,?,?,?,?,?)
		";
		
		
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array(
				$arr_data["ID_VBDI"],
				$arr_data["CQ_NHAN"],
				$arr_data["EMAIL_NHAN"],
				$arr_data["DA_CHUYEN"],
				$arr_data["ID_CQ"],
				$arr_data["ID_U"]
			));
				return $dbAdapter->lastInsertId(QLVBDHCommon::Table('vbmail_vanbanchuyen'),'ID_VBCHUYEN');
		}catch(Exception $ex){
			return 0;
		}
	}

	

	
}
?>