<?php 
class Vanban_Nhapdulieu {
	static function getInfoFromXMLWithBase64Encode($str_xml){
		$decodinstr = base64_decode($str_xml);
		//parse xml
		
	}

	
	static function getHeaderMailFromXML($str){
		
		$arr_vb = array();
		
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($str);
		if($dom == false)
			return array();
		//protocol

		$tags = $dom->getElementsByTagName('PROTOCOL');
		$tag = $tags->item(0); 
		
		$protocol = $tag->nodeValue;
		
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["PROTOCOL"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["PROTOCOL"] = $tag->nodeValue;

		//version

		$tags = $dom->getElementsByTagName('VERSION');
		$tag = $tags->item(0); 
		
		$version = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["VERSION"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["VERSION"] = $tag->nodeValue;

		
		//name_sys
		
		$tags = $dom->getElementsByTagName('NAME_SYS');
		$tag = $tags->item(0); 
		$name_sys = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["NAME_SYS"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["NAME_SYS"] = $tag->nodeValue;
		
		//company sent
		
		$tags = $dom->getElementsByTagName('COMPANY_SENT');
		$tag = $tags->item(0); 
		$company_sent = $tag->nodeValue;

		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["COMPANY_SENT"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["COMPANY_SENT"] = $tag->nodeValue;
		
		//document identity
		
		$tags = $dom->getElementsByTagName('DOCUMENT_IDENTITY');
		$tag = $tags->item(0); 
		$document_identity = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["DOCUMENT_IDENTITY"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["DOCUMENT_IDENTITY"] = $tag->nodeValue;
		
		//md5 
		
		$tags = $dom->getElementsByTagName('MD5');
		$tag = $tags->item(0); 
		
		$md5 = $tag->nodeValue;

		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["MD5"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["MD5"] = $tag->nodeValue;

		
		$arr_vb["OK_CHECK"] = 0;
		if( md5($protocol.$version.$name_sys.$company_sent.$document_identity) == $md5)
			$arr_vb["OK_CHECK"] = 1;
		
		return $arr_vb;

	}

	static function getInfoVbFromXML($str,$tranfer_ecoding){
		
		//echo $str;
		$filename_temp = "C:/vanban.xml";
		
		$fp = @fopen($filename_temp, 'w');
		if($tranfer_ecoding == "quoted-printable")
				stream_filter_append($fp, "convert.quoted-printable-decode");
		if($tranfer_ecoding == "base64")
				stream_filter_append($fp, "convert.base64-decode");
		@fwrite($fp, $str);
		@fclose($fp);
		
		$dom = new DOMDocument();
		$dom = DOMDocument::load($filename_temp);
		if($dom == false)
			return array();
		$arr_vb = array();
		$sokyhieutags = $dom->getElementsByTagName('SOKYHIEU');
		$sokyhieutag = $sokyhieutags->item(0); 
		$sokyhieu = $sokyhieutag->nodeValue;
		$arr_vb["SOKYHIEU"] = $sokyhieutag->nodeValue;
		$tags = $dom->getElementsByTagName('NGAYBANHANH');
		$tag = $tags->item(0); 
		$ngaybanhanh = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["NGAYBANHANH"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["NGAYBANHANH"] = $tag->nodeValue;

		
		$tags = $dom->getElementsByTagName('COQUANBANHANH');
		$tag = $tags->item(0); 
		$coquanbanhanh = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["COQUANBANHANH"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["COQUANBANHANH"] = $tag->nodeValue;

		$tags = $dom->getElementsByTagName('TRICHYEU');
		$tag = $tags->item(0); 
		$trichyeu = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["TRICHYEU"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["TRICHYEU"] = $tag->nodeValue;
		
		$tags = $dom->getElementsByTagName('NGUOIKY');
		$tag = $tags->item(0); 
		$nguoiky = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["NGUOIKY"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["NGUOIKY"] = $tag->nodeValue;
		
		$tags = $dom->getElementsByTagName('MD5');
		$tag = $tags->item(0); 
		$md5 = $tag->nodeValue;
		if(strtoupper($tag->getAttribute('ENCODING')) == 'BASE64' )
			$arr_vb["MD5"] = base64_decode($tag->nodeValue);
		else
			$arr_vb["MD5"] = $tag->nodeValue;
		
		$arr_vb["OK_CHECK"] = 0;
		if(md5($sokyhieu.$coquanbanhanh.$ngaybanhanh.$trichyeu.$nguoiky) == $md5)
			$arr_vb["OK_CHECK"] = 1;

		return $arr_vb;


	}

	static function insertIntoDatabase($arr_data){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			insert into ".QLVBDHCommon::Table('vbmail_vanbannhan')." 
			(`CQ_GUI`,`SOKYHIEU`,`NGAYBANHANH`,`COQUANBANHANH`,`TRICHYEU`,`NGUOIKY`,`EMAIL_GUI`,`ID_CQ_GUI`)
			values (?,?,?,?,?,?,?,?)
		";
		
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array(
				$arr_data["CQ_GUI"],
				$arr_data["SOKYHIEU"],
				$arr_data["NGAYBANHANH"],
				$arr_data["COQUANBANHANH"],
				$arr_data["TRICHYEU"],
				$arr_data["NGUOIKY"],
				$arr_data["EMAIL_GUI"],
				$arr_data["ID_CQ_GUI"]
			
			));
			
			return  $dbAdapter->lastInsertId(QLVBDHCommon::Table('vbmail_vanbannhan'),'ID_VBNHAN'); 
		}catch(Exception $ex){
			return 0;
		}
	}
	/**
	*Lay thong tin cua van ban tu mail
	*
	*/
	static function getVbMmail(){
		
	
	}
}
?>