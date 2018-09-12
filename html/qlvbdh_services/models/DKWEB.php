<?php 

class DKWEB {
	
	function insertFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASOHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MALOAIHOSO');
			$params['MALOAIHOSO'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TENTOCHUCCANHAN');
			$params['TENTOCHUCCANHAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIACHI');
			$params['DIACHI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'EMAIL');
			$params['EMAIL'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'GUI_LUC');
			$params['GUI_LUC'] = $value ;
			
			DKWEB::insertToDatabase($params);
			return 1;
		}
		
		
	}
	function insertFileFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('FILE');
		
		$i=0;$k=0;
		for( ; $i<$tagshsmc->length;$i++){
			$k++;
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'FILENAME');
			$params['FILENAME'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASO');
			$params['MASO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TENTAILIEU');
			$params['TENTAILIEU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');  ;

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MIME');
			$params['MIME'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DATA');
			$params['LINK'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');
			
			$fn = "C:\\Upload\\motcua\\".$params['MASO'];
			$handle = fopen($fn, "w");
			
			fclose($handle);
			file_put_contents($fn,$params['LINK']);
			$sql =  sprintf( "  select count(*) as DEM from ". "gtvt_motcua_file" . "  where  MASO = '%s'  ",
						mysql_real_escape_string($params["MASO"])
						);
		
			$cnt = query($sql);
			
			while($item = mysql_fetch_assoc($cnt)){
				
				if($item["DEM"] > 0 ){
					//truong hop cap nhat
					$sql = sprintf("
						UPDATE " . "gtvt_motcua_file" ." 
							set   LINK = '%s',
							FILENAME = '%s',
							TENTAILIEU = '%s',
							MAHOSO = '%s'
							where MASO = '%s'
						",
						
						mysql_real_escape_string($params["LINK"]),
						mysql_real_escape_string($params["FILENAME"]),
						mysql_real_escape_string($params["TENTAILIEU"]),
						mysql_real_escape_string($params["MAHOSO"]),
						mysql_real_escape_string($params["MASO"])
							);
					
					query($sql);
				}else{
					//truong hop them moi
					
					
					$sql = sprintf("
							INSERT INTO " . "gtvt_motcua_file" ." (
								 FILENAME , TENTAILIEU, MASO,MAHOSO,MIME
							)VALUES(
								'%s','%s','%s','%s','%s'
							)
							",
							
							mysql_real_escape_string($params["FILENAME"]),
							mysql_real_escape_string($params["TENTAILIEU"]),
							mysql_real_escape_string($params["MASO"]),
							mysql_real_escape_string($params["MAHOSO"]),
							mysql_real_escape_string($params["MIME"])
						);
					
					query($sql);
				}


		}
		
		
		}
		
		
	}

static function insertFileToDatabase($params){
		
		return "ss";
		//Kiem tra ho so da lay ve chua
		$sql =  sprintf( "  select count(*) as DEM from ". "gtvt_motcua_file" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "gtvt_motcua_file" ." 
						set   LINK = '%s',
						FILENAME = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["LINK"]),
					mysql_real_escape_string($params["FILENAME"]),
					
					mysql_real_escape_string($params["MAHOSO"])
						);
				return $sql;
				query($sql);
			}else{
				//truong hop them moi
				
				
				$sql = sprintf("
						INSERT INTO " . "gtvt_motcua_file" ." (
							 FILENAME , MASO,MAHOSO
						)VALUES(
							'%s','%s','%s'
						)
						",
						
						mysql_real_escape_string($params["FILENAME"]),
						mysql_real_escape_string($params["MASO"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				return $sql;
				query($sql);
			}


		}
		
		
	}

	function getAttributeHosoFromXMLTag($xmlnode,$tagmame){
		
		$childnodes = $xmlnode->getElementsByTagName($tagmame);
		$node = $childnodes->item(0);
		$value = $node->nodeValue;
		return $value ;
	}

	function insertToDatabase($params){
		
		
		//Kiem tra ho so da lay ve chua
		$sql =  sprintf( "  select count(*) as DEM from ". "gtvt_motcua_hoso_web" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "gtvt_motcua_hoso_web" ." 
						set   TENTOCHUCCANHAN = '%s',
						MALOAIHOSO = '%s',
						DIACHI = '%s',
						EMAIL = '%s',
						DIENTHOAI = '%s',
						GUI_LUC = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TENTOCHUCCANHAN"]),
					mysql_real_escape_string($params["MALOAIHOSO"]),
					mysql_real_escape_string($params["DIACHI"]),
					mysql_real_escape_string($params["EMAIL"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["GUI_LUC"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				query($sql);
			}else{
				//truong hop them moi
				$sql = sprintf("
						INSERT INTO " . "gtvt_motcua_hoso_web" ." (
							TENTOCHUCCANHAN,MALOAIHOSO , DIACHI , EMAIL , DIENTHOAI, GUI_LUC, MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TENTOCHUCCANHAN"]),
						mysql_real_escape_string($params["MALOAIHOSO"]),
						mysql_real_escape_string($params["DIACHI"]),
						mysql_real_escape_string($params["EMAIL"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["GUI_LUC"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				
				query($sql);
			}


		}
		
		
	}

	//thi cong cong trinh

	function insertWeb_thicong_ctFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAY_DON');
			$params['NGAY_DON'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_CANHAN');
			$params['TEN_CANHAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_COQUAN');
			$params['TEN_COQUAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TUYEN_DUONG');
			$params['TUYEN_DUONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LY_TRINH_TU');
			$params['LY_TRINH_TU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LY_TRINH_DEN');
			$params['LY_TRINH_DEN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_CONGTRINH');
			$params['TEN_CONGTRINH'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_thicong_ct" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_thicong_ct" ." 
						set   TEN_CANHAN = '%s',
						TEN_COQUAN = '%s',
						LY_DO = '%s',
						LY_TRINH_TU = '%s',
						LY_TRINH_DEN = '%s',
						TUYEN_DUONG = '%s',
						NGAY_DON = '%s',
						TEN_CONGTRINH = '%s',
						DIENTHOAI = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TEN_CANHAN"]),
					mysql_real_escape_string($params["TEN_COQUAN"]),
					mysql_real_escape_string($params["LY_DO"]),
					mysql_real_escape_string($params["LY_TRINH_TU"]),
					mysql_real_escape_string($params["LY_TRINH_DEN"]),
					mysql_real_escape_string($params["TUYEN_DUONG"]),
					mysql_real_escape_string($params["NGAY_DON"]),
					mysql_real_escape_string($params["TEN_CONG_TRINH"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_thicong_ct" ." (
							TEN_CANHAN,TEN_COQUAN,LY_DO,LY_TRINH_TU,LY_TRINH_DEN,TUYEN_DUONG,NGAY_DON,TEN_CONGTRINH,DIENTHOAI,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TEN_CANHAN"]),
						mysql_real_escape_string($params["TEN_COQUAN"]),
						mysql_real_escape_string($params["LY_DO"]),
						mysql_real_escape_string($params["LY_TRINH_TU"]),
						mysql_real_escape_string($params["LY_TRINH_DEN"]),
						mysql_real_escape_string($params["TUYEN_DUONG"]),
						mysql_real_escape_string($params["NGAY_DON"]),
						mysql_real_escape_string($params["TEN_CONG_TRINH"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}

	
	function insertWeb_phuhieu_xhdFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAYDON');
			$params['NGAYDON'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASODON');
			$params['MASODON'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_DOANHNGHIEP');
			$params['TEN_DOANHNGHIEP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_QUOCTE');
			$params['TEN_QUOCTE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIACHI_TRUSO');
			$params['DIACHI_TRUSO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_NOICAP');
			$params['DKKD_NOICAP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_SO');
			$params['DKKD_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_NGAY');
			$params['DKKD_NGAY'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'PHAMVI_HOATDONG');
			$params['PHAMVI_HOATDONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'FAX');
			$params['FAX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_phuhieu_xhd" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_phuhieu_xhd" ." 
						set   TEN_DOANHNGHIEP = '%s',
						MASODON = '%s',
						NGAYDON = '%s',
						TEN_QUOCTE = '%s',
						DIACHI_TRUSO = '%s',
						DKKD_NOICAP = '%s',
						DKKD_SO = '%s',
						DKKD_NGAY = '%s',
						PHAMVI_HOATDONG = '%s',
						DIENTHOAI = '%s',
						FAX = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
					mysql_real_escape_string($params["MASODON"]),
					mysql_real_escape_string($params["NGAYDON"]),
					mysql_real_escape_string($params["DIACHI_TRUSO"]),
					mysql_real_escape_string($params["DKKD_NOICAP"]),
					mysql_real_escape_string($params["DKKD_SO"]),
					mysql_real_escape_string($params["DKKD_NGAY"]),
					mysql_real_escape_string($params["PHAMVI_HOATDONG"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["FAX"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_phuhieu_xhd" ." (
							TEN_DOANHNGHIEP,MASODON,NGAYDON,DIACHI_TRUSO,DKKD_NOICAP,DKKD_SO,DKKD_NGAY,PHAMVI_HOATDONG,DIENTHOAI,FAX,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
						mysql_real_escape_string($params["MASODON"]),
						mysql_real_escape_string($params["NGAYDON"]),
						mysql_real_escape_string($params["DIACHI_TRUSO"]),
						mysql_real_escape_string($params["DKKD_NOICAP"]),
						mysql_real_escape_string($params["DKKD_SO"]),
						mysql_real_escape_string($params["DKKD_NGAY"]),
						mysql_real_escape_string($params["PHAMVI_HOATDONG"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["FAX"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}

	// xe du lich
	function insertWeb_phuhieu_xdlFromWebsite($data){
			$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAYDON');
			$params['NGAYDON'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASODON');
			$params['MASODON'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_DOANHNGHIEP');
			$params['TEN_DOANHNGHIEP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_QUOCTE');
			$params['TEN_QUOCTE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIACHI_TRUSO');
			$params['DIACHI_TRUSO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_NOICAP');
			$params['DKKD_NOICAP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_SO');
			$params['DKKD_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DKKD_NGAY');
			$params['DKKD_NGAY'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'PHAMVI_HOATDONG');
			$params['PHAMVI_HOATDONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'FAX');
			$params['FAX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_phuhieu_xdl" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_phuhieu_xdl" ." 
						set   TEN_DOANHNGHIEP = '%s',
						MASODON = '%s',
						NGAYDON = '%s',
						TEN_QUOCTE = '%s',
						DIACHI_TRUSO = '%s',
						DKKD_NOICAP = '%s',
						DKKD_SO = '%s',
						DKKD_NGAY = '%s',
						PHAMVI_HOATDONG = '%s',
						DIENTHOAI = '%s',
						FAX = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
					mysql_real_escape_string($params["MASODON"]),
					mysql_real_escape_string($params["NGAYDON"]),
					mysql_real_escape_string($params["DIACHI_TRUSO"]),
					mysql_real_escape_string($params["DKKD_NOICAP"]),
					mysql_real_escape_string($params["DKKD_SO"]),
					mysql_real_escape_string($params["DKKD_NGAY"]),
					mysql_real_escape_string($params["PHAMVI_HOATDONG"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["FAX"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_phuhieu_xdl" ." (
							TEN_DOANHNGHIEP,MASODON,NGAYDON,DIACHI_TRUSO,DKKD_NOICAP,DKKD_SO,DKKD_NGAY,PHAMVI_HOATDONG,DIENTHOAI,FAX,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
						mysql_real_escape_string($params["MASODON"]),
						mysql_real_escape_string($params["NGAYDON"]),
						mysql_real_escape_string($params["DIACHI_TRUSO"]),
						mysql_real_escape_string($params["DKKD_NOICAP"]),
						mysql_real_escape_string($params["DKKD_SO"]),
						mysql_real_escape_string($params["DKKD_NGAY"]),
						mysql_real_escape_string($params["PHAMVI_HOATDONG"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["FAX"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}
	
	function insertWeb_phuhieu_xtxFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAYDON');
			$params['NGAYDON'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASODON');
			$params['MASODON'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIENTHOAI');
			$params['DIENTHOAI'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_DOANHNGHIEP');
			$params['TEN_DOANHNGHIEP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_QUOCTE');
			$params['TEN_QUOCTE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'CHU_DOANHNGHIEP');
			$params['CHU_DOANHNGHIEP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'FAX');
			$params['FAX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_phuhieu_xtx" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_phuhieu_xtx" ." 
						set   TEN_DOANHNGHIEP = '%s',
						MASODON = '%s',
						NGAYDON = '%s',
						TEN_QUOCTE = '%s',
						DIACHI_TRUSO = '%s',
						
						CHU_DOANHNGHIEP = '%s',
						DIENTHOAI = '%s',
						FAX = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
					mysql_real_escape_string($params["MASODON"]),
					mysql_real_escape_string($params["NGAYDON"]),
					mysql_real_escape_string($params["DIACHI_TRUSO"]),
					
					mysql_real_escape_string($params["CHU_DOANHNGHIEP"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["FAX"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_phuhieu_xtx" ." (
							TEN_DOANHNGHIEP,MASODON,NGAYDON,DIACHI_TRUSO,CHU_DOANHNGHIEP,DIENTHOAI,FAX,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TEN_DOANHNGHIEP"]),
						mysql_real_escape_string($params["MASODON"]),
						mysql_real_escape_string($params["NGAYDON"]),
						mysql_real_escape_string($params["DIACHI_TRUSO"]),
						
						mysql_real_escape_string($params["CHU_DOANHNGHIEP"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["FAX"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}
	

	function insertWeb_phuhieu_tcdFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAYDON');
			$params['NGAYDON'] = $value ;
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MASODON');
			$params['MASODON'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'QUANLY_TUYEN');
			$params['QUANLY_TUYEN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 


			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'CHU_DOANHNGHIEP');
			$params['CHU_DOANHNGHIEP'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			
			
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_phuhieu_tcd" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_phuhieu_tcd" ." 
						set  
						MASODON = '%s',
						NGAYDON = '%s',
						QUANLY_TUYEN = '%s',
						
						CHU_DOANHNGHIEP = '%s',
					
						where MAHOSO = '%s'
					",
					
					
					mysql_real_escape_string($params["MASODON"]),
					mysql_real_escape_string($params["NGAYDON"]),
					mysql_real_escape_string($params["QUANLY_TUYEN"]),
					
					mysql_real_escape_string($params["CHU_DOANHNGHIEP"]),
			
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_phuhieu_tcd" ." (
							MASODON,NGAYDON,QUANLY_TUYEN,CHU_DOANHNGHIEP,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s'
						)
						",
						
						mysql_real_escape_string($params["MASODON"]),
						mysql_real_escape_string($params["NGAYDON"]),
						mysql_real_escape_string($params["QUANLY_TUYEN"]),
						
						mysql_real_escape_string($params["CHU_DOANHNGHIEP"]),
					
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}
	

	function insertWeb_capphep_dcFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('HOSO');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_CANHAN');
			$params['TEN_CANHAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TEN_COQUAN');
			$params['TEN_COQUAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LOAI_XE');
			$params['LOAI_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BIEN_SO');
			$params['BIEN_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'CHU_PHUONGTIEN');
			$params['CHU_PHUONGTIEN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LOAI_HANG');
			$params['LOAI_HANG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TL_XE');
			$params['TL_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TL_HANG');
			$params['TL_HANG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DD_DI');
			$params['DD_DI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DD_DEN');
			$params['DD_DEN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'PHUC_VU');
			$params['PHUC_VU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TUYEN_DUONG');
			$params['TUYEN_DUONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TUYEN_DUONG');
			$params['TUYEN_DUONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DD_DUNGDO');
			$params['DD_DUNGDO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TU_NGAY');
			$params['TU_NGAY'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DEN_NGAY');
			$params['DEN_NGAY'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'KT_XE_RONG');
			$params['KT_XE_RONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'KT_XE_CAO');
			$params['KT_XE_CAO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'KT_XE_DAI');
			$params['KT_XE_DAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TL_XE_TK');
			$params['TK_XE_TK'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'SO_TRUCXE');
			$params['SO_TRUCXE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NGAY_KIEMDINH_LT');
			$params['NGAY_KIEMDINH_LT'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NHAN_HIEU');
			$params['NHAN_HIEU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TENTOCHUCCANHAN');
			$params['TENTOCHUCCANHAN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');
			
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'DIACHI');
			$params['DIACHI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');


			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_TU_1');
			$params['TG_DD_TU_1'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_TU_2');
			$params['TG_DD_TU_2'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_TU_3');
			$params['TG_DD_TU_3'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');


			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_DEN_1');
			$params['TG_DD_DEN_1'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_DEN_2');
			$params['TG_DD_DEN_2'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_DD_DEN_3');
			$params['TG_DD_DEN_3'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');


			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_TU_1');
			$params['TG_VC_TU_1'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_TU_2');
			$params['TG_VC_TU_2'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_TU_3');
			$params['TG_VC_TU_3'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');


			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_DEN_1');
			$params['TG_VC_DEN_1'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_DEN_2');
			$params['TG_VC_DEN_2'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TG_VC_DEN_3');
			$params['TG_VC_DEN_3'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8');
			
			
			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_capphep_dc" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_capphep_dc" ." 
						set   TEN_CANHAN = '%s',
						DIENTHOAI = '%s',
						TEN_COQUAN = '%s',
						LOAI_XE = '%s',
						BIEN_SO = '%s',
						CHU_PHUONGTIEN = '%s',
						LOAI_HANG = '%s',
						TL_XE = '%s',
						TL_HANG = '%s',
						DD_DI = '%s',
						DD_DEN = '%s',
						PHUC_VU = '%s',
						TUYEN_DUONG = '%s',
						DD_DUNGDO = '%s',
						TU_NGAY = '%s',
						DEN_NGAY = '%s',
						KT_XE_RONG = '%s',
						KT_XE_DAI = '%s',
						KT_XE_CAO = '%s',
						SO_TRUCXE = '%s',
						NGAY_KIEMDINH_LT = '%s',
						NHAN_HIEU = '%s',
						TENTOCHUCCANHAN = '%s',
						DIACHI = '%s',
						TG_DD_TU_1 = '%s',
						TG_DD_TU_2 = '%s',
						TG_DD_TU_3 = '%s',
						TG_DD_DEN_1 = '%s',
						TG_DD_DEN_2 = '%s',
						TG_DD_DEN_3 = '%s',
						TG_VC_TU_1 = '%s',
						TG_VC_TU_2 = '%s',
						TG_VC_TU_3 = '%s',
						TG_VC_DEN_1 = '%s',
						TG_VC_DEN_2 = '%s',
						TG_VC_DEN_3 = '%s'
						
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TEN_CANHAN"]),
					mysql_real_escape_string($params["DIENTHOAI"]),
					mysql_real_escape_string($params["TEN_COQUAN"]),
					mysql_real_escape_string($params["LOAI_XE"]),
					mysql_real_escape_string($params["BIEN_SO"]),
					mysql_real_escape_string($params["CHU_PHUONGTIEN"]),
					mysql_real_escape_string($params["LOAI_HANG"]),
					mysql_real_escape_string($params["TL_XE"]),
					mysql_real_escape_string($params["TL_HANG"]),
					mysql_real_escape_string($params["DD_DI"]),
					mysql_real_escape_string($params["DD_DEN"]),
					mysql_real_escape_string($params["PHUC_VU"]),
					mysql_real_escape_string($params["TUYEN_DUONG"]),
					mysql_real_escape_string($params["DD_DUNGDO"]),
					mysql_real_escape_string($params["TU_NGAY"]),
					mysql_real_escape_string($params["DEN_NGAY"]),
					mysql_real_escape_string($params["KT_XE_RONG"]),
					mysql_real_escape_string($params["KT_XE_DAI"]),
					mysql_real_escape_string($params["KT_XE_CAO"]),
					mysql_real_escape_string($params["SO_TRUCXE"]),
					mysql_real_escape_string($params["NHAN_HIEU"]),
					mysql_real_escape_string($params["TENTOCHUCCANHAN"]),
					mysql_real_escape_string($params["DIACHI"]),
					mysql_real_escape_string($params["TG_DD_TU_1"]),
					mysql_real_escape_string($params["TG_DD_TU_2"]),
					mysql_real_escape_string($params["TG_DD_TU_3"]),
					mysql_real_escape_string($params["TG_DD_DEN_1"]),
					mysql_real_escape_string($params["TG_DD_DEN_2"]),
					mysql_real_escape_string($params["TG_DD_DEN_3"]),
					mysql_real_escape_string($params["TG_VC_TU_1"]),
					mysql_real_escape_string($params["TG_VC_TU_2"]),
					mysql_real_escape_string($params["TG_VC_TU_3"]),
					mysql_real_escape_string($params["TG_VC_DEN_1"]),
					mysql_real_escape_string($params["TG_VC_DEN_2"]),
					mysql_real_escape_string($params["TG_VC_DEN_3"]),
					
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				//return "dddd";
				$sql = sprintf("
						INSERT INTO " . "web_capphep_dc" ." (
							TEN_CANHAN,DIENTHOAI,TEN_COQUAN,LOAI_XE,BIEN_SO,CHU_PHUONGTIEN,LOAI_HANG,TL_XE,TL_HANG,DD_DI,DD_DEN,PHUC_VU,TUYEN_DUONG,DD_DUNGDO,TU_NGAY,DEN_NGAY,KT_XE_RONG,KT_XE_DAI,KT_XE_CAO,SO_TRUCXE,NHAN_HIEU,TENTOCHUCCANHAN,DIACHI,TG_DD_TU_1,TG_DD_TU_2,TG_DD_TU_3,TG_DD_DEN_1,TG_DD_DEN_2,TG_DD_DEN_3,
							TG_VC_TU_1,TG_VC_TU_2,TG_VC_TU_3,TG_VC_DEN_1,TG_VC_DEN_2,TG_VC_DEN_3,
							MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["TEN_CANHAN"]),
						mysql_real_escape_string($params["DIENTHOAI"]),
						mysql_real_escape_string($params["TEN_COQUAN"]),
						mysql_real_escape_string($params["LOAI_XE"]),
						mysql_real_escape_string($params["BIEN_SO"]),
						mysql_real_escape_string($params["CHU_PHUONGTIEN"]),
						mysql_real_escape_string($params["LOAI_HANG"]),
						mysql_real_escape_string($params["TL_XE"]),
						mysql_real_escape_string($params["TL_HANG"]),
						mysql_real_escape_string($params["DD_DI"]),
						mysql_real_escape_string($params["DD_DEN"]),
						mysql_real_escape_string($params["PHUC_VU"]),
						mysql_real_escape_string($params["TUYEN_DUONG"]),
						mysql_real_escape_string($params["DD_DUNGDO"]),
						mysql_real_escape_string($params["TU_NGAY"]),
						mysql_real_escape_string($params["DEN_NGAY"]),
						mysql_real_escape_string($params["KT_XE_RONG"]),
					mysql_real_escape_string($params["KT_XE_DAI"]),
					mysql_real_escape_string($params["KT_XE_CAO"]),
					mysql_real_escape_string($params["SO_TRUCXE"]),
					mysql_real_escape_string($params["NHAN_HIEU"]),
					mysql_real_escape_string($params["TENTOCHUCCANHAN"]),
					mysql_real_escape_string($params["DIACHI"]),
					mysql_real_escape_string($params["TG_DD_TU_1"]),
					mysql_real_escape_string($params["TG_DD_TU_2"]),
					mysql_real_escape_string($params["TG_DD_TU_3"]),
					mysql_real_escape_string($params["TG_DD_DEN_1"]),
					mysql_real_escape_string($params["TG_DD_DEN_2"]),
					mysql_real_escape_string($params["TG_DD_DEN_3"]),
					mysql_real_escape_string($params["TG_VC_TU_1"]),
					mysql_real_escape_string($params["TG_VC_TU_2"]),
					mysql_real_escape_string($params["TG_VC_TU_3"]),
					mysql_real_escape_string($params["TG_VC_DEN_1"]),
					mysql_real_escape_string($params["TG_VC_DEN_2"]),
					mysql_real_escape_string($params["TG_VC_DEN_3"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}

	function hhh(){
		return 1;
	}

	
	
	
	
	static function insertDataSyns($tblname,$data){
	
		switch($tblname){
			case "web_thicong_ct":
				return DKWEB::insertWeb_thicong_ctFromWebsite($data);
			break;

			case "web_phuhieu_xhd":
				return DKWEB::insertWeb_phuhieu_xhdFromWebsite($data);	
			break;
			case "web_danhsach_xhd":
				return DKWEB::insertWeb_danhsach_xhdFromWebsite($data);
			break;
			case "web_phuhieu_xtx":
				return DKWEB::insertWeb_phuhieu_xtxFromWebsite($data);
			break;
			case "web_danhsach_xtx":
				return DKWEB::insertWeb_danhsach_xtxFromWebsite($data);
			break;

			case "web_phuhieu_tcd":
				return DKWEB::insertWeb_phuhieu_tcdFromWebsite($data);
			break;
			
			case "web_danhsach_tcd":
				return DKWEB::insertWeb_danhsach_tcdFromWebsite($data);
			break;
			
			case "web_phuhieu_xdl":
				return DKWEB::insertWeb_phuhieu_xdlFromWebsite($data);
			break;

			case "web_danhsach_xdl":
				return DKWEB::insertWeb_danhsach_xdlFromWebsite($data);
			break;

			case "web_capphep_dc":
				return DKWEB::insertWeb_capphep_dcFromWebsite($data);
			break;
			
			
		}

	}
	
	
	
	//danh sach tuyen co dinh

	function insertWeb_danhsach_tcdFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('ITEM');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TUYEN_DUONG');
			$params['TUYEN_DUONG'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'SO_XE');
			$params['SO_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'HIEU_XE');
			$params['HIEU_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NAM_SX');
			$params['NAM_SX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'TRONG_TAI');
			$params['TRONG_TAI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'CU_LY');
			$params['CU_LY'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BEN_DI');
			$params['BEN_DI'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BEN_DEN');
			$params['BEN_DEN'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'GHICHU');
			$params['GHICHU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_danhsach_tcd" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_danhsach_tcd" ." 
						set   TUYEN_DUONG = '%s',
						SO_XE = '%s',
						HIEU_XE = '%s',
						NAM_SX = '%s',
						TRONG_TAI = '%s',
						CU_LY = '%s',
						BEN_DEN = '%s',
						BEN_DI = '%s',
						GHICHU = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["TUYEN_DUONG"]),
					mysql_real_escape_string($params["SO_XE"]),
					mysql_real_escape_string($params["HIEU_XE"]),
					mysql_real_escape_string($params["NAM_SX"]),
					mysql_real_escape_string($params["TRONG_TAI"]),
					mysql_real_escape_string($params["CU_LY"]),
					mysql_real_escape_string($params["BEN_DEN"]),
					mysql_real_escape_string($params["BEN_DI"]),
					mysql_real_escape_string($params["GHICHU"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_danhsach_tcd" ." (
							TUYEN_DUONG,SO_XE,HIEU_XE,NAM_SX,TRONG_TAI,CU_LY,BEN_DEN,BEN_DI,GHICHU,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)
						",
							mysql_real_escape_string($params["TUYEN_DUONG"]),
							mysql_real_escape_string($params["SO_XE"]),
							mysql_real_escape_string($params["HIEU_XE"]),
							mysql_real_escape_string($params["NAM_SX"]),
							mysql_real_escape_string($params["TRONG_TAI"]),
							mysql_real_escape_string($params["CU_LY"]),
							mysql_real_escape_string($params["BEN_DEN"]),
							mysql_real_escape_string($params["BEN_DI"]),
							mysql_real_escape_string($params["GHICHU"]),
							mysql_real_escape_string($params["MAHOSO"])
					);
				
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}
	
	
	//danh sach xe taxi

	function insertWeb_danhsach_xtxFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('ITEM');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BIEN_SO');
			$params['BIEN_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'SO_GHE');
			$params['SO_GHE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			

			
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NAM_SX');
			$params['NAM_SX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'GHICHU');
			$params['GHICHU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_danhsach_xtx" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_danhsach_xtx" ." 
						set   BIEN_SO = '%s',
						
						SO_GHE = '%s',
						NAM_SX = '%s',
					
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["BIEN_SO"]),
					
					mysql_real_escape_string($params["SO_GHE"]),
					mysql_real_escape_string($params["NAM_SX"]),
					
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_danhsach_xtx" ." (
							BIEN_SO,SO_GHE,NAM_SX,MAHOSO
						)VALUES(
							'%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["BIEN_SO"]),
						
						mysql_real_escape_string($params["SO_GHE"]),
						mysql_real_escape_string($params["NAM_SX"]),
					
						mysql_real_escape_string($params["MAHOSO"])
					);
				
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}

	
	
	//danh sach phu hieu xe du lich
	function insertWeb_danhsach_xdlFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('ITEM');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BIEN_SO');
			$params['BIEN_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'SO_GHE');
			$params['SO_GHE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LOAI_XE');
			$params['LOAI_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NAM_SX');
			$params['NAM_SX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'GHICHU');
			$params['GHICHU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_danhsach_xdl" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_danhsach_xdl" ." 
						set   BIEN_SO = '%s',
						LOAI_XE = '%s',
						SO_GHE = '%s',
						NAM_SX = '%s',
						GHICHU = '%s'
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["BIEN_SO"]),
					mysql_real_escape_string($params["LOAI_XE"]),
					mysql_real_escape_string($params["SO_GHE"]),
					mysql_real_escape_string($params["NAM_SX"]),
					mysql_real_escape_string($params["GHICHU"]),
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_danhsach_xdl" ." (
							BIEN_SO,LOAI_XE,SO_GHE,NAM_SX,GHICHU,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["BIEN_SO"]),
						mysql_real_escape_string($params["LOAI_XE"]),
						mysql_real_escape_string($params["SO_GHE"]),
						mysql_real_escape_string($params["NAM_SX"]),
						mysql_real_escape_string($params["GHICHU"]),
						mysql_real_escape_string($params["MAHOSO"])
					);
				
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}



	//danh sach phu hieu xe hop dong

 function insertWeb_danhsach_xhdFromWebsite($data){
		$dom = new DOMDocument();
		$dom = DOMDocument::loadXML($data);
		$tagshsmc = $dom->getElementsByTagName('ITEM');
		
		
		for($i=0 ; $i<$tagshsmc->length;$i++){
			$params = array();
			$item = $tagshsmc->item($i);
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'MAHOSO');
			$params['MAHOSO'] = $value ;

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'BIEN_SO');
			$params['BIEN_SO'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'SO_GHE');
			$params['SO_GHE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			

			$value = DKWEB::getAttributeHosoFromXMLTag($item,'LOAI_XE');
			$params['LOAI_XE'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 
			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'NAM_SX');
			$params['NAM_SX'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			
			$value = DKWEB::getAttributeHosoFromXMLTag($item,'GHICHU');
			$params['GHICHU'] = html_entity_decode(base64_decode($value),ENT_NOQUOTES,'UTF-8'); 

			//Kiem tra ho so da lay ve chua
			$sql =  sprintf( "  select count(*) as DEM from ". "web_danhsach_xhd" . "  where  MAHOSO = '%s'  ",
						mysql_real_escape_string($params["MAHOSO"])
						);
		
		//return $sql;
		$cnt = query($sql);
		
		while($item = mysql_fetch_assoc($cnt)){
			
			if($item["DEM"] > 0 ){
				//truong hop cap nhat
				$sql = sprintf("
					UPDATE " . "web_danhsach_xhd" ." 
						set   BIEN_SO = '%s',
						LOAI_XE = '%s',
						SO_GHE = '%s',
						NAM_SX = '%s',
					
						where MAHOSO = '%s'
					",
					
					mysql_real_escape_string($params["BIEN_SO"]),
					mysql_real_escape_string($params["LOAI_XE"]),
					mysql_real_escape_string($params["SO_GHE"]),
					mysql_real_escape_string($params["NAM_SX"]),
					
					mysql_real_escape_string($params["MAHOSO"])
						);
				
				query($sql);
			}else{
				//truong hop them moi
				
				$sql = sprintf("
						INSERT INTO " . "web_danhsach_xhd" ." (
							BIEN_SO,LOAI_XE,SO_GHE,NAM_SX,MAHOSO
						)VALUES(
							'%s','%s','%s','%s','%s'
						)
						",
						mysql_real_escape_string($params["BIEN_SO"]),
						mysql_real_escape_string($params["LOAI_XE"]),
						mysql_real_escape_string($params["SO_GHE"]),
						mysql_real_escape_string($params["NAM_SX"]),
					
						mysql_real_escape_string($params["MAHOSO"])
					);
				
				//return $sql;
				query($sql);
			}


		}
		
			
		}
		
		
	}

	
	///gop y




	function getGopy(){
		$sql = "
						select * from web_motcua_gopy gy where ( IS_GET = 0 OR IS_GET is NULL)
			";
		$data = query($sql);
		$xml='<?xml version="1.0" encoding="UTF-8"?>  <GOPYS>';
			while($item = mysql_fetch_assoc($data)){
	    			$xml.="<GOPY>";
					
					$xml.="<NOIDUNG>".base64_encode($item['NOIDUNG'])."</NOIDUNG>";
					$xml.="<MAHOSO>".($item['MAHOSO'])."</MAHOSO>";
					$xml.="<NAME_U>".base64_encode($item['NAME_U'])."</NAME_U>";
					$xml.="<DATESEND>".($item['DATESEND'])."</DATESEND>";
					
					$xml.="</GOPY>";

					$sql = "UPDATE web_motcua_gopy SET IS_GET=1 where ID_WM_GOPY=".$item['ID_WM_GOPY'];
					query($sql);
	    }
	    $xml.="</GOPYS>";
	    return $xml;
	}
	

	
	function file_post_contents($url,$headers=false) {
		$url = parse_url($url);

		if (!isset($url['port'])) {
		  if ($url['scheme'] == 'http') { $url['port']=80; }
		  elseif ($url['scheme'] == 'https') { $url['port']=443; }
		}
		$url['query']=isset($url['query'])?$url['query']:'';

		$url['protocol']=$url['scheme'].'://';
		$eol="\r\n";

		$headers =  "POST ".$url['protocol'].$url['host'].$url['path']." HTTP/1.0".$eol.
					"Host: ".$url['host'].$eol.
					"Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
					"Content-Type: application/x-www-form-urlencoded".$eol.
					"Content-Length: ".strlen($url['query']).$eol.
					$eol.$url['query'];
		$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
		if($fp) {
		  fputs($fp, $headers);
		  $result = '';
		  while(!feof($fp)) { $result .= fgets($fp, 128); }
		  fclose($fp);
		  if (!$headers) {
			//removes headers
			$pattern="/^.*\r\n\r\n/s";
			$result=preg_replace($pattern,'',$result);
		  }
		  return $result;
		}
	}
	

}