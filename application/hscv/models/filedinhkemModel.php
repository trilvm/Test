<?php
/**
 * @author trunglv
 * @version 1.0
 */
require_once ('Zend/Db/Table/Abstract.php');
require_once 'Common/ConvertFileUtils.php';
require_once 'hscv/models/FileTransfer.php';
require_once 'Common/Convert.php';
/**
 * Cau truc du lieu luu tru thong tin ve file dinh kem
 *
 */
class FileDinhKem {
	var $_id_dk;
	var $_folder;
	var $_id_object;
	var $_maso;
	var $_nam;
	var $_thang;
	var $_mime;
	var $_filename;
	var $_type;
	var $_content;
	var $_user;
	var $_id_identify;
	var $_time_update;
	var $_pathFile;
}
/**
 * Lop du lieu tuong ung voi bang filedinhkemModel
 *
 */
class filedinhkemModel extends Zend_Db_Table_Abstract {
	var $_name = 'gen_filedinhkem_2013';

	/**
	 * Ham khoi tao theo nam
	 *
	 * @param unknown_type $year
	 */
	function __construct($year){
		$this->_name ='gen_filedinhkem'.'_'.$year;
		$arr = array();
		parent::__construct($arr);
	}
	
	/**
	 * Cap nhat lai nam trong bang csdl
	 *
	 * @param unknown_type $year
	 */
	function setNameByYear($year){
		$this->_name ='gen_filedinhkem'.'_'.$year; 
	}
	/**
	 * Lay danh sach file theo id cua doi tuong 
	 *
	 * @param integer $idObject //id cua doi tuong
	 * @param integer $isTemp //co phai id tam hay khong , truong hop them moi
	 * @return tra ve cac danh sach file can tim
	 */
	function getListFile($idObject,$isTemp){
		$select;
		if($isTemp==1){ //La id tam
			//truong hop id tam type = -1 , 
			$select = $this->select()->where('ID_OBJECT=?',$idObject)
							->where('TYPE=?',-1);	
		}
		else{
			$select = $this->select()->where('ID_OBJECT=?',$idObject);
		}
		return $this->fetchAll($select);
	}
	
	/**
	 * Ham insert mot file dinh kem vao co so du lieu (Khong co truong id_object)
	 *
	 * @param unknown_type $file
	 * @return unknown
	 */
	function insertFileNoIdObject($file){
		//Lay ten bang
		//Insert du lieu
		$data = array( 'FOLDER'=> $file->_folder, 
					   'MASO'=> $file->_maso,
					   'FILENAME'=>$file->_filename,
					   'USER'=>$file->_user,
						'NAM'=> $file->_nam,
						'THANG'=>$file->_thang,
						'TYPE'=> -1,
						'CONTENT'=> $file->_content,
						'ID_OBJECT' => $file->_id_object,
						'MIME' => $file->_mime,
						'TIME_UPDATE'=>$file->_time_update
		);
		
		return $this->insert($data);
	}
	
	function insertFileWithIdObject($file){
		$data = array( 'FOLDER'=> $file->_folder, 
					   'MASO'=> $file->_maso,
					   'FILENAME'=>$file->_filename,
					   'USER'=>$file->_user,
						'NAM'=> $file->_nam,
						'THANG'=>$file->_thang,
						'CONTENT'=> $file->_content,
						'TYPE'=> $file->_type,
						'ID_OBJECT' => $file->_id_object,
						'MIME' => $file->_mime,
						'TIME_UPDATE'=>$file->_time_update
		);
		
		return $this->insert($data);
	}
	
	function updateFileWithNewIdObject($idObject,$type){
		//tim nhung hang co type = -1 va cap nhat lai
		$where = 'TYPE=-1';
		$data = array(
			'ID_OBJECT'=> $idObject,
			'TYPE' => $type
		);
		$this->update($data,$where);
	}
	
	function updateFileWithIdObject($idObject,$type,$idDK){
		$where = 'ID_DK='.$idDK;
		$data = array(
			'ID_OBJECT'=> $idObject,
			'TYPE' => $type
		);
		$this->update($data,$where);
	}
	/**
	 * Ham xoa mot file dinh kem theo id_dk cua file dinh kem
	 *
	 * @param unknown_type $idFile
	 */
	function deleteFile($idFile){
		//Xoa file trong o cung 
		$select = $this->select()->where("ID_DK=?",$idFile);
		$re = $this->fetchRow($select);
		if(!$re){
			echo "Không tìm thấy file cần xóa";
			//thong bao loi
		}else{
			$path = $re->FOLDER.DIRECTORY_SEPARATOR.$re->MASO ; 
			unlink($path);
			//Xoa file trong csdl
			$where = 'ID_DK='.$idFile;
			$this->delete($where);
		}	
	}
	
	static function deleteFileByObject($year,$idObject,$type){
		$year = QLVBDHCommon::getYear();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select ID_DK , MASO, FOLDER from gen_filedinhkem_$year 
		where ID_OBJECT=? and TYPE=?
		";
		$stm = $dbAdapter->query($sql,array($idObject,$type));
		$re = $stm->fetchAll();
		if(!$re || count($re)==0){
			//echo "Không tìm thấy file cần xóa";
			//thong bao loi
		}else{
			foreach($re as $item){
			  $path = $item['FOLDER'].DIRECTORY_SEPARATOR.$item['MASO']; 
			  unlink($path);
			  //Xoa file trong csdl
			 }
			  $sql = "delete from gen_filedinhkem_$year 
			  where ID_OBJECT=? and TYPE=?
			  ";
			  $stm = $dbAdapter->prepare($sql);
			  $stm->execute(array($idObject,$type));
		}	
	}
	/**
	 * Ham xoa file dinh kem theo chuoi bam dinh dang cua file dinh kem
	 *
	 * @param unknown_type $maso
	 */
	function deleteFileByMaso($maso){
		$select = $this->select()->where("MASO=?",$maso);
		$re = $this->fetchRow($select);
		if(!$re){
			echo "Không tìm thấy file cần xóa";
			//thong bao loi
		}else{
			$path = $re->FOLDER.DIRECTORY_SEPARATOR.$maso ; 
			unlink($path);
			//Xoa file trong csdl
			$where = 'ID_DK='.$re->ID_DK;
			$this->delete($where);
		}	
	}
	/**
	 * Ham xoa file dinh kem theo chuoi bam dinh dang cua file dinh kem
	 *
	 * @param unknown_type $maso
	 */
	function deleteFileByMasoDbOnly($maso){
		$select = $this->select()->where("MASO=?",$maso);
		$re = $this->fetchRow($select);
		if(!$re){
			echo "Không tìm thấy file cần xóa";
			//thong bao loi
		}
		else
		{			
			//Xoa file trong csdl
			$where = 'ID_DK='.$re->ID_DK;
			$this->delete($where);
		}	
	}
	
	/**
	 * Ham lay noi dung dang text cua file word hay file pdf
	 *
	 * @param unknown_type $file
	 */
	/*function getContent($file,$pdf=0){
		try{
			chdir($_SERVER['DOCUMENT_ROOT']."/tools");
			$scriptpath = "convert.bat";
			$arrfilename = explode(".",$file->_filename);
			
			
			$type=$arrfilename[count($arrfilename)-1];
			$extends = $type;
			
			copy($file->_pathFile,$file->_pathFile.".$extends");
			system($scriptpath.' "'.$file->_pathFile.".$extends".'" "'.$file->_pathFile.'.txt"');
			$data = array('CONTENT'=> Convert::ConvertToUnicode(file_get_contents($file->_pathFile.'.txt')));
			$this->update($data,'ID_DK='.$file->_id_dk);
			
			if($pdf==1){
				system($scriptpath.' "'.$file->_pathFile.".$extends".'" "'.$file->_pathFile.'.pdf"');
				if(file_exists($file->_pathFile.'.pdf"')){
					unlink($file->_pathFile);
					rename($file->_pathFile.'.pdf',$file->_pathFile);
					$this->update(array("MIME"=>"application/pdf","FILENAME"=>$arrfilename[0].".pdf"),'ID_DK='.$file->_id_dk);
				}
			}
			unlink($file->_pathFile.'.txt');
			unlink($file->_pathFile.".$extends");
		}catch(Exception $ex){
		
		}
	}

	*/
	// getcontent mới

	function getContent($filePath){		
        $rootPath = dirname(dirname(dirname(dirname(__FILE__))));
        $rootPath = str_replace("\\",'/',$rootPath);
		$config_antiword = "\"".$rootPath . '/library/tool/antiword/antiword.exe'.'"';
		$config_xpdf = "\"".$rootPath . '/library/tool/xpdf/bin32/pdftotext.exe'.'"';
        $content = "";
        if($filePath != ""){
            $filePath = str_replace("\\","/",$filePath);
            $arrfilename = explode(".",$filePath);
            $extends=$arrfilename[count($arrfilename)-1];
            if($extends == "pdf")
            {
                $content = shell_exec($config_xpdf." -enc UTF-8 ".$filePath." -");
            }else if ($extends == "doc"){
				//echo $config_antiword." -m UTF-8 ".$filePath;exit;
                $content = shell_exec($config_antiword." -m UTF-8 ".$filePath);							
						
            }else if($extends == "docx"){					
					 // Create new ZIP archive
					 $zip = new ZipArchive;
					 $dataFile = 'word/document.xml';					
					// Open received archive file
					 if (true === $zip->open($filePath)) {
					 // If done, search for the data file in the archive
					 if (($index = $zip->locateName($dataFile)) !== false) {
					 // If found, read it to the string
						 $data = $zip->getFromIndex($index);
					// Close archive file
					$zip->close();
					
				// Load XML from a string
				// Skip errors and warnings
					 $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				// Return data without XML formatting tags

					 $contents = explode('\n',strip_tags($xml->saveXML()));
						$content = '';
					foreach($contents as $i=>$content) {
					$content .= $contents[$i];
				    }		
					
				 }
				
				 }	
			}
        }	
        return $content; 
	}
	/**
	 * Lay vi tri noi luu giu file dinh kem trong o dia
	 * theo nam va thang
	 * @param unknown_type $nam
	 * @param unknown_type $thang
	 * @return unknown
	 */
	function getDir($nam,$thang){
		$con = Zend_Registry::get('config');
		$root = $con->file->root_dir;
		$temp_path = $con->file->temp_path;
		$dirPath = $root. DIRECTORY_SEPARATOR. $nam.DIRECTORY_SEPARATOR.$thang;
		if(!file_exists($root))
			mkdir($root);
		if(!file_exists($temp_path))
			mkdir($temp_path);
		if(!file_exists($root. DIRECTORY_SEPARATOR. $nam))
			mkdir($root. DIRECTORY_SEPARATOR. $nam);
		if(!file_exists($dirPath))
			mkdir($dirPath);
		return $dirPath;
	}
	/**
	 * Lay vi tri cua thu muc temp
	 *
	 * @return unknown
	 */
	function getTempPath(){
		$con = Zend_Registry::get('config');
		$root = $con->file->root_dir;
		$temp_path = $con->file->temp_path;
		if(!file_exists($root))
			mkdir($root);
		if(!file_exists($temp_path))
			mkdir($temp_path);
		return $temp_path; 
	}
	/**
	 * Cap nhat thong tin ve ma so cua file dinh kem
	 *
	 * @param unknown_type $id_file
	 * @param unknown_type $maso
	 */
	function updateMaSo($id_file , $maso){
		$data = array("MASO"=>$maso);
		$where = "ID_DK=".$id_file;
		$this->update($data,$where);
		
	}
	/**
	 * Lay file dinh kem theo id cua no
	 *
	 * @param unknown_type $idFile
	 * @return unknown
	 */
	function getFileById($idFile){
		$file = new FileDinhKem();
		$select = $this->select()->where("ID_DK=?",$idFile);
		$re = $this->fetchRow($select);
		$path ="";
		if(!$re){
			return NULL;
			//thong bao loi
		}else{
			$file->_pathFile =  $re->FOLDER.DIRECTORY_SEPARATOR.$re->MASO ;
			$file->_filename = $re->FILENAME;
			$file->_mime = $re->MIME; 
		}
		return $file;
	}
	/**
	 * Lay file dinh kem theo theo ma so duoc bam 
	 *
	 * @param unknown_type $maso
	 * @return unknown
	 */
	function getFileByMaso($maso){
		$file = new FileDinhKem();
		$select = $this->select()->where("MASO=?",$maso);
		$re = $this->fetchRow($select);
		$path ="";
		$config = Zend_Registry::get('config');
		if(!$re){
			return NULL;
			//thong bao loi
		}else{
			$file->_pathFile = $config->file->root_dir.DIRECTORY_SEPARATOR.$re->NAM.DIRECTORY_SEPARATOR.$re->THANG.DIRECTORY_SEPARATOR.$re->MASO ;
			//echo $file->_pathFile;
			//exit;
			$file->_filename = $re->FILENAME;
			$file->_mime = $re->MIME; 
			$file->_id_object = $re->ID_OBJECT;
			$file->_nam = QLVBDHCommon::getYear();
			$file->_type = $re->TYPE;
		}
		return $file;
	}
	
	function getFileByIdObjectAndType($idObject,$type){
		$select = $this->select()->where('ID_OBJECT=?',$idObject)
								 ->where('TYPE=?',$type);
		return $this->fetchAll($select);
	}

	
	static function copyFile($year,$old_object,$new_object,$old_type,$new_type){
		//copy trong database
		$sql = "
		select * from `gen_filedinhkem_".$year."`
		where  `ID_OBJECT` = ? and  `TYPE` = ?
		";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($old_object,$old_type));
		$re = $query->fetchAll();
		
		//insert
		//bat dau transaction
		foreach($re as $row)
		{
			$sql = "
			 insert into `gen_filedinhkem_".$year."` (`ID_OBJECT`,`FOLDER`,`MASO`,`NAM`,`THANG`,`MIME`,
			 `FILENAME`,`TYPE`,`CONTENT`,`USER`,`TIME_UPDATE`) values (?,?,?,?,?,?,?,?,?,?,?)";
			$arr_data = array(
			$new_object,
			 $row['FOLDER'],$row['MASO'],$row['NAM'],$row['THANG'],
			 $row['MIME'],$row['FILENAME'],$new_type,$row['CONTENT'],$row['USER'],
			 $row['TIME_UPDATE']
			);
			$stm = $dbAdapter->prepare($sql);
			$stm->execute($arr_data);
			$id_fdk = $dbAdapter->lastInsertId();
			//ket thuc transaction
			$path_oldfile = $row['FOLDER'].DIRECTORY_SEPARATOR.$row['MASO'];
			$new_maso = md5($id_fdk.$row['FILENAME'].$row['TIME_UPDATE']);
			//cap nhat lai ma so
			
			$sql = "update `gen_filedinhkem_".$year."` set `MASO`=? where `ID_DK`=?";
			$stm = $dbAdapter->prepare($sql);
			$id_fdk = $stm->execute(array($new_maso,$id_fdk));
			$new_path = $row['FOLDER'].DIRECTORY_SEPARATOR.$new_maso;
			copy($path_oldfile,$new_path);
		}
			
			
		//copy tren file server
		//lay duong dan den file
		//copy*/
	}
	
	
	
	static function  insertFileNoneUpload($idObject,$isTemp,$file_source,$filename,$mime,$type,$pdf=0){
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		$date = getdate();
		$year = QLVBDHCommon::getYear();		
		
		if(!$idObject)
			$idObject = 0;
		
		if(!$isTemp)
			$isTemp = 0;
			
		$model = new filedinhkemModel($year); //doi tuong model
		//Luu file xuong thu muc tam cua server
		$max_size = $con->file->maxsize;
		$temp_path = $model->getTempPath();
		$filepath = $temp_path.DIRECTORY_SEPARATOR.$filename;
		
		//$file_source = "C:\\1579.pdf";
		
		if (!copy($file_source,$filepath))
		{
			
			return -1;
			
		}else{
				$file = new FileDinhKem();
				$file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
				$file->_nam = $date['year'];
				$file->_thang = $date['mon'];
				$dirPath = $model->getDir($file->_nam,$file->_thang);
				$file->_folder = $dirPath;
				$file->_id_object = $idObject;
				$file->_user = $au->getIdentity()->ID_U;
				$file->_filename = $filename;
				$file->_mime = $mime;
				$file->_type = $type;
				$id_file = $model->insertFileWithIdObject($file);
				$maso = $id_file.$file->_filename.$file->_time_update;
				$maso = md5($maso);
				$model->updateMaSo($id_file,$maso);
				$newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
				rename($filepath,$newlocation);
				$file->_pathFile = $newlocation;
				$file->_id_dk = $id_file;
				//$model->getContent($file,$pdf);
				$file->_content = $model->getContent($file);
				return $id_file;
		}
	}
	
	
	
	static function  insertFile($idObject,$isTemp,$iddiv,$year,$type,$pdf=0,$is_nogetcontent=0){
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		$date = getdate();
		//if(!$type)
			//$type = -1;
		$year = QLVBDHCommon::getYear();		
		//echo $year;exit;
		if(!$idObject)
			$idObject = 0;
		
		if(!$isTemp)
			$isTemp = 0;
			
		$model = new filedinhkemModel($year); //doi tuong model
		//Luu file xuong thu muc tam cua server
		$max_size = $con->file->maxsize;
		//$adapter = new FileTransfer(); // doi tuong adapter nhan file dinh kem tu client
		//$adapter->addValidator('size', $max_size);
		$temp_path = $model->getTempPath();
		//$adapter->setDestination($temp_path); 
		//echo $temp_path;
		$filepath = $temp_path.DIRECTORY_SEPARATOR.$_FILES['uploadedfile']['name'];
		//echo $file;
		
		if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'],$filepath))
		{
			return -1;
			//echo "yes sir"; exit;
			//rename($adapter->getFileName('uploadedfile'),$newlocation);
		}else{
				$file = new FileDinhKem();
				$file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
				$file->_nam = $date['year'];
				//$model->setNameByYear($file->_nam);
				$file->_thang = $date['mon'];
				$dirPath = $model->getDir($file->_nam,$file->_thang);
				$file->_folder = $dirPath;
				$file->_id_object = $idObject;
				$file->_user = $au->getIdentity()->ID_U;
				$file->_filename = $_FILES['uploadedfile']['name'];
				$file->_mime = $_FILES['uploadedfile']['type'];
				
				$file->_content = $model->getContent($filepath);
				$file->_type = $type;
				$id_file = $model->insertFileWithIdObject($file);
				$maso = $id_file.$file->_filename.$file->_time_update;
				$maso = md5($maso);
				$model->updateMaSo($id_file,$maso);
				$newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
				rename($filepath,$newlocation);
				
				$file->_pathFile = $newlocation;
				$file->_id_dk = $id_file;
				//if($is_nogetcontent==0)
					//$model->getContent($file,$pdf);
				//$file->_content = $model->getContent($filepath);
				return $id_file;
		}
	}
	static function  insertFileVgca($idObject,$isTemp,$iddiv,$year,$type,$pdf=0,$is_nogetcontent=0){
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		$date = getdate();
		//if(!$type)
			//$type = -1;
		$year = QLVBDHCommon::getYear();		
		//echo $year;exit;
		if(!$idObject)
			$idObject = 0;
		
		if(!$isTemp)
			$isTemp = 0;
			
		$model = new filedinhkemModel($year); //doi tuong model
		//Luu file xuong thu muc tam cua server
		$max_size = $con->file->maxsize;
		//$adapter = new FileTransfer(); // doi tuong adapter nhan file dinh kem tu client
		//$adapter->addValidator('size', $max_size);
		$temp_path = $model->getTempPath();
		//$adapter->setDestination($temp_path); 
		//echo $temp_path;
		$filepath = $temp_path.DIRECTORY_SEPARATOR.$_FILES['uploadfile']['name'];
		//echo $file;
		
		if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'],$filepath))
		{
			return -1;
			//echo "yes sir"; exit;
			//rename($adapter->getFileName('uploadedfile'),$newlocation);
		}else{
				$file = new FileDinhKem();
				$file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
				$file->_nam = $date['year'];
				//$model->setNameByYear($file->_nam);
				$file->_thang = $date['mon'];
				$dirPath = $model->getDir($file->_nam,$file->_thang);
				$file->_folder = $dirPath;
				$file->_id_object = $idObject;
				$file->_user = $au->getIdentity()->ID_U;
				//$file->_filename = $_FILES['uploadfile']['name'];
				$file->_filename = str_replace(".pdf",".signed.pdf",$_FILES['uploadfile']['name']);
				$file->_mime = $_FILES['uploadfile']['type'];
				
				$file->_content = $model->getContent($filepath);
				$file->_type = $type;
				$id_file = $model->insertFileWithIdObject($file);
				$maso = $id_file.$file->_filename.$file->_time_update;
				$maso = md5($maso);
				$model->updateMaSo($id_file,$maso);
				$newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
				rename($filepath,$newlocation);
				
				$file->_pathFile = $newlocation;
				$file->_id_dk = $id_file;
				//if($is_nogetcontent==0)
					//$model->getContent($file,$pdf);
				//$file->_content = $model->getContent($filepath);
				return $id_file;
		}
	}
	static function inserDataToFileDb($data,$idObject,$isTemp,$type,$file_name,$mime,$encoding){
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		$date = getdate();
		$year = QLVBDHCommon::getYear();		
		
		if(!$idObject)
			$idObject = 0;
		
		if(!$isTemp)
			$isTemp = 0;
		
		$model = new filedinhkemModel($year); //doi tuong model
		//Luu file xuong thu muc tam cua server
		$max_size = $con->file->maxsize;
	
		$temp_path = $model->getTempPath();
		$filepath = $temp_path.DIRECTORY_SEPARATOR.$file_name;
		
		$fp = @fopen($filepath, 'w');
		if($encoding == "quoted-printable")
			stream_filter_append($fp, "convert.quoted-printable-decode");
		if($encoding == "base64")
			stream_filter_append($fp, "convert.base64-decode");

		@fwrite($fp, $data);
		@fclose($fp);

		
		$file = new FileDinhKem();
		$file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
		$file->_nam = $date['year'];
		$file->_thang = $date['mon'];
		$dirPath = $model->getDir($file->_nam,$file->_thang);
		$file->_folder = $dirPath;
		$file->_id_object = $idObject;
		$file->_user = $au->getIdentity()->ID_U;
		$file->_filename = $file_name;
		$file->_mime = $mime;
		$file->_type = $type;
		
		$id_file = $model->insertFileWithIdObject($file);
		$maso = $id_file.$file->_filename.$file->_time_update;
		$maso = md5($maso);
		$model->updateMaSo($id_file,$maso);
		$newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
		
		
		rename($filepath,$newlocation);
		
		$file->_pathFile = $newlocation;
		$file->_id_dk = $id_file;
		
		return $id_file;
	
	}
	
	static function insertFileIso($oldmaso){
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		$year = QLVBDHCommon::getYear();
		$model = new filedinhkemModel($year);
		$max_size = $con->file->maxsize;
		$temp_path = $model->getTempPath();
		$filepath = $temp_path.DIRECTORY_SEPARATOR.$_FILES['uploadedfile']['name'];
		if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'],$filepath)){
			return array("","","");
		}else{
			if($oldmaso!="" && $_FILES['uploadedfile']['name']!=""){
				unlink($con->file->root_dir.DIRECTORY_SEPARATOR."iso".DIRECTORY_SEPARATOR.$oldmaso);
			}
			$filename = $_FILES['uploadedfile']['name'];
			$mime = $_FILES['uploadedfile']['type'];
			$maso = $filename.date("Y-m-d H:i:s");
			$maso = md5($maso);
			$newlocation = $con->file->root_dir.DIRECTORY_SEPARATOR."iso".DIRECTORY_SEPARATOR.$maso;
			rename($filepath,$newlocation);
			return array($maso,$mime,$filename);
		}
	}

	static function getListFileByIdObject($id_object,$type){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "
			select * from ".QLVBDHCommon::Table('gen_filedinhkem')."
			where ID_OBJECT = ? and TYPE = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_object,$type));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}

	function checkFileScan($id_object){
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();		
		$sql = "select count(*) as CNT from ".QLVBDHCommon::Table('gen_filedinhkem')." where ID_OBJECT = ? and TYPE = ?";
		try{
			$qr = $dbAdapter->query($sql,array($id_object,-10));
			$row =  $qr->fetch();
            return $row['CNT'];
        }catch(Exception $ex){
			echo $ex->getMessage();
		}
	}
        
        
}

