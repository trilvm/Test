<?php
/**
 * @author trunglv
 * @version 1.0
 */
require_once ('Zend/Controller/Action.php');
require_once ('hscv/models/PhienBanDuThaoModel.php');
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';

class Hscv_PhienBanDuThaoController extends Zend_Controller_Action {
	
	function init(){
		//Khong cho hien thi layout
		$this->_helper->layout->disableLayout();
	}
	/**
	 * action hien thi  danh sach cac phien ban du thao
	 *
	 */
	function indexAction(){
		global $auth;
		$user = $auth->getIdentity();
		$year = QLVBDHCommon::getYear(); 
		$idDuthao = $this->_request->getParam('idDuthao');
		$idHSCV = $this->_request->getParam('idHSCV');
		$model = new PhienBanDuThaoModel($year);
		$this->view->data = $model->getListByIdDuthao($idDuthao,$year);
		$this->view->year = $year;
		$this->view->idDuthao = $idDuthao;
		$this->view->isXoa = $this->_request->getParam('isXoa');
		$this->view->ID_U=$user->ID_U;
                $this->view->user=$user;
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
		     $isreadonly = 0;
		$isCapnhat = 1;
		if(hosocongviecModel::isLuutru($idHSCV,$year) == true || $isreadonly == 1){
			$isCapnhat = 0;
			
			}else{$isCapnhat = 1; }
		$this->view->isCapnhat = $isCapnhat;
		
		
	}
	/**
	 * action hien thi khung nhap lieu de them moi
	 * hay cap nhat cac phien ban du thao
	 */
	function inputAction(){
		
		$year = QLVBDHCommon::getYear(); 
		$idPBDuthao = $this->_request->getParam('idPBDuthao');
		$model = new PhienBanDuThaoModel($year);
		$re = $model->getDataById($idPBDuthao);
                global $auth;
		$user = $auth->getIdentity();
                $this->view->user=$user;
		$this->view->version = $re->VERSION;
		$this->view->year = $year;
		$this->view->idPBDuthao =$idPBDuthao ;
		$this->view->type=3;
		$this->view->iddiv = $this->_request->getParam('iddiv');
		$this->view->iddivParent = $this->_request->getParam('iddivParent');
		$this->view->idOldFile = $this->_request->getParam('idOldFile');
		$this->view->idDuthao= $this->_request->getParam('idDuthao');
		$this->view->sophienban = $this->_request->getParam('sophienban');
		$this->view->idHSCV = $this->_request->getParam('idHSCV');
		$is_new = $this->_request->getParam('is_new');
		//is_new = 1 : them moi else cap nhat
		if(!$is_new)
			$is_new = 0;
		$this->view->is_new = $is_new;
	}
	/**
	 * Ham luu thong tin ve phien ban du thao khi cap nhat
	 * hay them moi
	 */
	function saveAction(){
		//Lay du lieu nhan ve tu client
                $formData=$this->_request->getParams();
		$iddiv= $this->_request->getParam('iddiv');
		$type = $this->_request->getParam('type');
		$version = $this->_request->getParam('version');
		$idDuthao = $this->_request->getParam('idDuthao');
		$year = QLVBDHCommon::getYear(); //nam cua bang du lieu
		$idPBDuthao = $this->_request->getParam('idPBDuthao');
		$idHSCV = $this->_request->getParam('idHSCV');
        $comment=$this->_request->getParam('comment');
		global $auth;
		$user = $auth->getIdentity();
		if(!$idPBDuthao){
			$idPBDuthao = 0;
		}
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
		$idOld = $this->_request->getParam('idOldFile');
		//tao cac lop model tuong ung
		$fdk = new filedinhkemModel($year);
		$model = new PhienBanDuThaoModel($year);
		if($idPBDuthao == 0){//truong hop them moi phien ban du thao
			//themoi phien ban du thao truoc de lay id
			$idPBDuthao = $model->insertOne($idDuthao,$version,$user->ID_U,$comment);
		}
		else{ // truong hop cap nhat phien ban du thao
			//echo $user->ID_U;exit;
			$model->updateVersion($idPBDuthao,$version,$user->ID_U,$comment);
		}
		//Cap nhat file dinh kem (them file moi, xoa file cu) 
		$re = filedinhkemModel::insertFile($idPBDuthao,$isTemp,$iddiv,$year,$type);
                //ghi file chữ ký số
                if($formData['outputdata']){				
                        $file_model = new filedinhkemModel($year);					
                        $file = new FileDinhKem();	
                        $date = getdate();					
                        $array_file_so=explode(";",$formData['outputdata']);	

                        for($i=0;$i<count($array_file_so);$i=$i+2){	
                                 //$i=$i+2;
                                //lấy tên file
                              $array_file_so_namefile=explode("=",$array_file_so[$i]);
                                //lấy content
                                  $array_file_so_content=explode("=",$array_file_so[$i+1]);

                                if($array_file_so_content[0] !=""){
                                 //Lay thong tin ve file dinh kem va luu xuong csdl
                                  $file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
                                  $file->_nam = $date['year'];
                                  //$file_model->setNameByYear($file->_nam);
                                  $file->_thang = $date['mon'];
                                  $dirPath = $file_model->getDir($file->_nam,$file->_thang);
                                  $file->_folder = $dirPath;
                                  $file->_id_object = $idPBDuthao;
                                  $file->_user = $user->ID_U;
                                  $file->_filename =  $array_file_so_namefile[1];
                                  $file->_type=$type;						  
                                  $file->_mime="";
                                  $file_so_mime=explode(".",$array_file_so_namefile[1]);
                                  if($file_so_mime[1]=="pdf"){
                                        $file->_mime="application/pdf";
                                  }else{	
                                        $file->_mime="application/octet-stream";
                                  }						 
                                  $filepath = $dirPath.DIRECTORY_SEPARATOR.$array_file_so_namefile[1];						  
                                  file_put_contents($filepath,base64_decode($array_file_so_content[1]));		
                                  $id_file = $file_model->insertFileWithIdObject($file);
                                  $maso = $id_file.$file->_filename.$file->_time_update;
                                  $maso = md5($maso);
                                  //$masoChuKiSo=$maso;
                                  $file_model->updateMaSo($id_file,$maso);
                                  $newlocation = $dirPath.DIRECTORY_SEPARATOR.$maso;  						  
                                  rename($filepath,$newlocation);			      
                                  }

                        }			

                }
                // end ghi file chữ ký sốố                
                //$id_file
		if($re != -1){
			if($idOld>0)
				$fdk->deleteFile($idOld);	
		}
		//doan js cap nhat lai danh sach cac van ban du thao
		echo "<script>window.parent.loadDivFromUrl('groupcontent".$idHSCV."','/hscv/VanBanDuThao/index/year/".$year."/iddivParent/groupcontent".$idHSCV."/idHSCV/".$idHSCV."/IS_NOIBO/',1);</script>";
		exit; //khong xu dung lop view
	}
	/**
	 * Ham xoa phien ban du thao
	 *
	 */
	function deleteAction(){
		$idDuthao = $this->_request->getParam('idDuthao');
		$year = QLVBDHCommon::getYear(); //nam cua bang du lieu
		$model = new PhienBanDuThaoModel($year);
		$fdk = new filedinhkemModel($year);
		$arr_id = $this->_request->getParam('DELidpbdt'.$idDuthao); // Lay thong tin ve cac phien ban du thao duoc chon xoa
		//var_dump($arr_id);exit;
		for($i = 0 ; $i< count($arr_id); $i++){
			$arr = explode(',',$arr_id[$i]);
			$model->deleteOneById($arr[0]);
			$fdk->deleteFileByMaso($arr[1]);
		}
		//doan javascript cap nhat lai danh sach cac phien ban du thao
		echo "loadDivFromUrl('PhienBanDiv".$idDuthao."','/hscv/PhienBanDuThao?year=".$year."&idDuthao=".$idDuthao."',1);";
		exit ; //khong xu dung lop view
	}
}

?>
