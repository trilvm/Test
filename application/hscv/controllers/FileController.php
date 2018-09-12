<?php

/**
 * FileController
 * @package hscv
 * Quan ly file dinh kem
 * @author trunglv
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/gen_tempModel.php';
require_once 'hscv/models/hosocongviecModel.php';

class Hscv_FileController extends AjaxEngine {
	
	/**
	 * Ham khoi tao du lieu cho controller action
	 *
	 */
	function init(){
		//disable layout
		$this->_helper->layout->disableLayout();
		
	}
	/**
	 * Ham xu ly index controller , in danh sach file dinh kem 
	 */
	public function indexAction() {
		// TODO Auto-generated UploadController::indexAction() default action
		$params = $this->_request->getParams();
		$this->view->enableDragAndDrop = $params["enableDragAndDrop"];
		if($params["isDragAndDrop"] == 1){
			$this->view->idObject = $params["idObject"];
			$this->renderScript("File/dragfile.phtml");
		}else{
			$this->returnResultHTML($this);
		}
	}
	public function attachmentAction()
	{
		$this->returnResultHTML($this);
	}	
	private function upload(){ 
		//Lay cac bien toan cuc
		$con = Zend_Registry::get('config');
		$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		$date = getdate();
		$iddiv= $this->_request->getParam('iddiv');
		$year = QLVBDHCommon::getYear(); //nam cua bang du lieu
		$type = $this->_request->getParam('type');
		if(!$type)
			$type = -1;
		if(!$year)
			$year = $date['year'];		
		$idObject = $this->_request->getParam('idObject');//id cua doi tuong chua file dinh kem
		if(!$idObject)
			$idObject = 0;
		$isTemp = $this->_request->getParam('isTemp');
		if(!$isTemp)
			$isTemp = 0;
			
		$model = new filedinhkemModel($year); //doi tuong model
		//Luu file xuong thu muc tam cua server
		$max_size = $con->file->maxsize;
		$adapter = new FileTransfer(); // doi tuong adapter nhan file dinh kem tu client
		$adapter->addValidator('size', $max_size);
		$temp_path = $model->getTempPath();
		$adapter->setDestination($temp_path); 
		
			if (!$adapter->receive()) {    
				//Loi khong the luu file 
				//thong bao loi o day
			}else{
			 //luu file thanh cong , cap nhat thong tin ve file xuong csdl
			}
	
		//Lay thong tin ve file dinh kem va luu xuong csdl
		$file = new FileDinhKem();
		$file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
		$file->_nam = $date['year'];
		$model->setNameByYear($file->_nam);
		$file->_thang = $date['mon'];
		$dirPath = $model->getDir($file->_nam,$file->_thang);
		$file->_folder = $dirPath;
		$file->_id_object = $idObject;
		$file->_user = $au->getIdentity()->ID_U;
		$file->_filename = basename($adapter->getFileName('uploadedfile'));
		$file->_mime = $adapter->getMine('uploadedfile');
		
		$id_file = $model->insertFileNoIdObject($file);
		$maso = $id_file.$file->_filename.$file->_time_update;
		$maso = md5($maso);
		$model->updateMaSo($id_file,$maso);
		$newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
		rename($adapter->getFileName('uploadedfile'),$newlocation);
		$file->_pathFile = $newlocation;
		$file->_id_dk = $id_file;
		$model->getContent($file);
		//return $id_file;
	}
	/**
	 * Ham upload file ve server
	 */
	public function saveAction(){  
		//$con = Zend_Registry::get('config');
		//$au = Zend_Registry::get('auth');
		//Lay tham so nhan tu client
		//$date = getdate();
		$enableDragAndDrop= (int)$this->_request->getParam('enableDragAndDrop');
		$iddiv= $this->_request->getParam('iddiv');
		$year = QLVBDHCommon::getYear(); //nam cua bang du lieu
		$type = $this->_request->getParam('type');
		//truongvc attachment for traodoi module
		$from =$this->_request->getParam('from');
		if(!$type)
			$type = -1;
		if(!$year)
			$year = $date['year'];		
		$idObject = $this->_request->getParam('idObject');//id cua doi tuong chua file dinh kem
		if(!$idObject)
			$idObject = 0;
		$isTemp = $this->_request->getParam('isTemp');
		if(!$isTemp)
			$isTemp = 0;		
		$pdf = $this->_request->getParam('pdf');
		$is_nogetcontent = $this->_request->getParam('is_nogetcontent');
		
		//echo $pdf;exit;
		filedinhkemModel::insertFile($idObject,$isTemp,$iddiv,$year,$type,$pdf,$is_nogetcontent);
		//Doan javascript de load lai danh sach file dinh kem tai client
		
		//truongvc
		
		if($from=="attachment")
			$url = '/hscv/file/attachment?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		else
			$url = '/hscv/file/index/enableDragAndDrop/'.$enableDragAndDrop.'?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		
		echo "<script> window.parent.loadDivFromUrl('".$iddiv."','$url"."',1); </script>";
		//var_dump($this->_request->getParams());
		exit;
	}
	
	public function addfileAction(){
		$this->upload();
		exit;
	}
	
	public function updateAction(){
		$idOld = $this->_request->getParam('idOldFile');
		$idObject = $this->_request->getParam('idObject');
		$type = $this->_request->getParam('type');
		//xoa
		$year = QLVBDHCommon::getYear();
		$model = new filedinhkemModel($year);
		$model->deleteFile($idOld);
		$id_file = $this->upload();
		$model->updateFileWithIdObject($idObject,$type,$id_file);
		exit;
	}
	/*
	 * Ham ajax tra ket qua duoi dang html ve cho client  
	 */
	protected function echoHTML(){
		//Lay id object va idTemp , year tu client
		
		//if(!$type )
			//$type = -1;
		$date = getdate();
		$year = QLVBDHCommon::getYear();
		$iddiv= $this->_request->getParam('iddiv');
		if(!$year)
			$year = $date['year'];		
		$idObject = $this->getParam('idObject');
		$is_new = $this->getParam('is_new');
		if($is_new == 1){//truong hop chua co id cua Object va chua khoi tao id Object
			//Tao mot truong id Object tam cho 
			$tempTbl = new gen_tempModel();
			$idObject = $tempTbl->getIdTemp();
			
		}
		else{
			//truong hop da co id cua Object
		}
		
		$this->view->idObject = $idObject;		
		$this->view->isTemp = $isTemp; 
		$this->view->year = $year;
		//Lay danh sach file dinh kem co idObject va $isTemp
		$model = new filedinhkemModel($year);
		
		//$idObject,$isTemp,$file_source,$filename,$mime,$type,$pdf=0
		//filedinhkemModel::insertFileNoneUpload($idObject,$isTemp,"C:\\1579.pdf","trunglv","application/pdf",$type,$pdf);
		$type = $this->getParam('type');
		$pdf = $this->getParam('pdf');
		$is_nogetcontent = $this->getParam('is_nogetcontent');
		
		
		if($type != -1)
			$this->view->data = $model->getFileByIdObjectAndType($idObject,$type);
		else
		    $this->view->data= $model->getListFile($idObject,$isTemp);		
		$this->view->iddiv= $iddiv;
		$this->view->type=$type;
		$this->view->pdf=$pdf;
		$this->view->is_nogetcontent = $is_nogetcontent;
		//kiem tra quyen truy cap
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
			$isreadonly = 0;
		$isCapnhat = 1;		
		if($is_new == 1){
			$isCapnhat = 1;
		}
		else{if($isreadonly == 1){
			$isCapnhat = 0;
			
			}else{$isCapnhat = 1; }
		}
		$this->view->isCapnhat = $isCapnhat;	
		//hosocongviecModel::isLuutru($idObject,$year)	
	}
	/**
	 * Ham ajax hien thi khung nhap lieu file dinh kem
	 */
	function inputAction(){
		//disable layout
		
		$this->view->enableDragAndDrop = $this->_request->getParam('enableDragAndDrop');
		$this->view->idObject = $this->_request->getParam('idObject');
		$this->view->isTemp = $this->_request->getParam('isTemp');
		$this->view->year = QLVBDHCommon::getYear();
		$this->view->iddiv = $this->_request->getParam('iddiv');
		$this->view->type = $this->_request->getParam('type');
		$this->view->from=$this->_request->getParam('from');
		$this->view->pdf=$this->_request->getParam('pdf');
		$this->view->is_nogetcontent= $this->_request->getParam('is_nogetcontent');
		if($this->_request->getParam('vgca')==true){
                    $this->renderScript("file/vgca.phtml");
                }
	}
	/**
	 * Ham ajax xoa file dinh kem
	 */
	function deleteAction(){
		$date = getdate();
		$year = QLVBDHCommon::getYear();
		$iddiv= $this->_request->getParam('iddiv');
		$enableDragAndDrop = (int)$this->_request->getParam('enableDragAndDrop');
		//truongvc attachment for traodoi module
		$from =$this->_request->getParam('from');
		if(!$year)
			$year = $date['year'];		
		$maso = $this->_request->getParam('maso');
		$idObject = $this->_request->getParam('idObject');
		if(!$idObject)
			$idObject = 0;
		$isTemp = $this->_request->getParam('isTemp');
		if(!$isTemp)
			$isTemp = 0;
		$type = $this->_request->getParam('type');
		$model = new filedinhkemModel($year);
		$arr_maso = $this->getParam('DELidfiledk'.$idObject);		
		$pdf = $this->_request->getParam('pdf');
		$is_nogetcontent = $this->_request->getParam('is_nogetcontent');
		for($i=0 ; $i< count($arr_maso);$i++)
			$model->deleteFileByMaso($arr_maso[$i]);
		//var_dump($arr_maso);
		
		if($from=="attachment")
			$url = '/hscv/file/attachment?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		else
			$url = '/hscv/file/index/enableDragAndDrop/'.$enableDragAndDrop.'?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		
			
		$this->_redirect($url);
		//echo "parent.loadDivFromUrl('".$iddiv."','$url"."',1);";
		//$url = '/hscv/file?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type;
		echo "<script> window.parent.loadDivFromUrl('".$iddiv."','$url"."',1); </script>";
		//echo "<script> loadDiv('".$iddiv."','/hscv/file',1,".$idObject.",".$isTemp.",".$year."); </script>";
		//echo "";
		exit;
	}
	/**
	 * Ham tai file dinh kem ve may
	 */
	function downloadAction(){
		$date = getdate();
		$year = QLVBDHCommon::getYear();		
		if(!$year)
			$year = $date['year'];		
		$maso = $this->_request->getParam('maso');
		$is_hienthi = $this->_request->getParam('is_hienthi');
		$model = new filedinhkemModel($year);
		$file = $model->getFileByMaso($maso);
                $extFile = pathinfo($file->_filename, PATHINFO_EXTENSION);
                if(strtolower($extFile)=="pdf"){
                    $file->_mime= 'application/pdf';
                }
		//var_dump($file);exit;
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$file->_mime );
                $extensionFile=substr(strrchr($file->_filename,'.'),1);
                if($is_hienthi!=1){
			if($file->_type==2){
                            if($extensionFile=='doc'||$extensionFile=='docx'){
                                header( 'Content-Disposition: attachment; filename="'.$file->_id_object."_".$file->_nam."_".$file->_filename.'"' );
                            }else{
                                header( 'Content-Disposition: attachment; filename="'.$file->_filename.'"' );
                            }
			}else if($file->_type==-1){
                            if($extensionFile=='doc'||$extensionFile=='docx'){
                                header( 'Content-Disposition: attachment; filename="'.$file->_id_object."_".$file->_nam."_ds_".$file->_filename.'"' );
                            }else{
                                header( 'Content-Disposition: attachment; filename="'.$file->_filename.'"' );
                            }
                        }else{
                            header( 'Content-Disposition: attachment; filename="'.$file->_filename.'"' );
			}
		}
		echo file_get_contents($file->_pathFile);
		exit;
	}

	    
    function checkfilescanAction(){
        $this->_helper->layout->disableLayout();
        $idObject= $this->_request->getParam('idObject');
        $year = QLVBDHCommon::getYear();
        $model = new filedinhkemModel($year);
        $count = $model->checkFileScan($idObject);
        
        ajax::ship('idObject',$idObject);
        ajax::ship('year',$year);
        ajax::ship('scanresult',$count);
        exit;
    }
    
}
