<?php
/**
 * ChukysoController
 * @package hscv
 * Upload file chu ky
 * @author cuongnc
 * @version 1.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/gen_tempModel.php';
require_once 'hscv/models/hosocongviecModel.php';


require_once 'Common/AjaxEngine.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'hscv/models/phienbanduthaoModel.php';



class Chukyso_ChukysoController extends Zend_Controller_Action {
	
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
	
	public function viewloadAction()
	{
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
		
		//Doan javascript de load lai danh sach file dinh kem tai client
		
		//truongvc
		
		if($from=="attachment")
			$url = '/hscv/file/attachment?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		else
			$url = '/hscv/file/index/enableDragAndDrop/'.$enableDragAndDrop.'?iddiv='.$iddiv.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type."&pdf=".$pdf."&is_nogetcontent=".$is_nogetcontent;
		
		echo "<script> window.parent.loadDivFromUrl('".$iddiv."','$url"."',1); </script>";
		
		exit;
	}	
	
	public function saveAction(){
//		$return['Status']= true;
//                    $return['FileName']= 'Lỗi tải tệp'; 
//             $return['FileServer']="";
//                $return['Message']= '';
//                echo json_encode($return);exit;
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
		$idfile = filedinhkemModel::insertFileVgca($idObject,$isTemp,$iddiv,$year,$type,$pdf,$is_nogetcontent);
		
		
		$return = array();
                if($idfile!=-1){
                   $return['Status']= true;
                    $return['FileName']= $idfile; 
                }else{
                    $return['Status']= false;
                    $return['FileName']= 'Lỗi tải tệp'; 
                }
                
                $return['FileServer']="";
                $return['Message']= '';
                echo json_encode($return);exit;
	}
	
	public function saveduthaoAction(){
		// $duthao = new DuThao();
		// echo '<pre>';
		// var_dump($duthao);exit;
		global $auth;
		$user = $auth->getIdentity();
		$year = QLVBDHCommon::getYear();
		$type = $this->_request->getParam('type');
		$iddivParent= $this->_request->getParam('iddivParent');
		$model = new VanBanDuThaoModel($year);
		$modeldt = new PhienBanDuThaoModel($year);		
                $idHSCV = $this->_request->getParam('idHSCV');
		$idDuthao = $this->_request->getParam('idDuthao');
		$idlvb = (int)$this->_request->getParam('ID_LVB');
		$version = $this->_request->getParam('version');
		$tenduthao = base64_decode($this->_request->getParam('duthao_tenvanbanduthao'));
		
		$isNoHSCV = $this->_request->getParam('isNoHSCV');
                $comment= $this->_request->getParam('comment');
                $formData=$this->_request->getParams();
				
		if($idDuthao>=1){
			//truong hop cap nhat
			$data_json = base64_decode($this->_request->getParam('data'));
			//var_dump($data_json);exit;
			$data = json_decode($data_json);
			$data->TENDUTHAO = base64_decode($data->TENDUTHAO);
			$data->ID_LVB = base64_decode($data->ID_LVB);
			$data->comment = base64_decode($data->comment);			
			//Capnhat lai thong tin cua van ban du thao trong csdl
			if((int)$this->_request->getParam('vgca') != 1)
			{
			$model->updateByIdDuthaoNoHSCV($idDuthao,$data);
			}else{
				$idPBDuthao = $modeldt->insertOne($idDuthao,$version,$user->ID_U,$comment);
				$idfile =  filedinhkemModel::insertFileVgca($idPBDuthao,1,$iddivParent,$year,$type,0,0);
				$return = array();
				if($idfile!=-1){
					$return['Status']= true;
					$return['FileName']= $idfile; 
					
				}else{
					$return['Status']= false;
					$return['FileName']= 'Lỗi tải tệp'; 
					
				}
				$return['Message'] = '';
				$return['FileServer']="";
				echo json_encode($return);exit;
			}
			//doan javascript de cap nhat lai danh sach cac van ban du thao
			// $return = array();
			// if($idfile!=-1){
			// $return['Status']= true;
			// 	$return['FileName']= $idfile; 
			// }else{
			// 	$return['Status']= false;
			// 	$return['FileName']= 'Lỗi tải tệp'; 
			// }
			
			// $return['FileServer']="";
			// $return['Message']= '';
			// echo json_encode($return);exit;				
			echo 'loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		}else{
			if($idDuthao == 0){
				//truong hop them moi
				$myAuth = Zend_Registry::get('auth');
				$duthao = new DuThao();
				$duthao->_nguoisoan = $myAuth->getIdentity()->ID_U;
				$duthao->_id_hscv = $idHSCV;
				$duthao->_trangthai = 0;
				if($isNoHSCV == 1)
					$duthao->_trangthai = -1;
				$duthao->_tenduthao = $tenduthao;
				$duthao->_idlvb = $idlvb;
                                $duthao->_comment = $comment;
				//Them moi du thao
				
				$idDuthao = $model->insertOne($duthao);
				//Them moi cac phien ban du thao
				$phienbanModel = new PhienBanDuThaoModel($year);
				$idPBDuthao = $phienbanModel->insertOne($idDuthao,$version,$myAuth->getIdentity()->ID_U);
		// 		echo '<pre>';
		// var_dump( (int)$myAuth->getIdentity()->ID_U);exit;
				//Them moi cac file dinh kem tuong ung voi cac phien ban du thao co trong du thao
				$idfile =  filedinhkemModel::insertFileVgca($idPBDuthao,1,$iddivParent,$year,$type,0,0,(int)$this->_request->getParam('id_user'));
				
                                //ghi file chữ ký số
				//$auth = Zend_Registry::get('auth');
				//$user = $auth->getIdentity();
				// if($formData['outputdata']){				
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
									// $masoChuKiSo=$maso;
									$file_model->updateMaSo($id_file,$maso);
									$newlocation = $dirPath.DIRECTORY_SEPARATOR.$maso;  						  
									rename($filepath,$newlocation);			      
									}
						// }
				} 
			global $db;
			$data = $model->getListByIdHSCV($idHSCV);      
			if(count($data) <=1){
			$db->update(QLVBDHCommon::Table('hscv_hosocongviec'), array('ID_U_XULY' => $user->ID_U), "ID_HSCV=".$idHSCV);
			}
			$return = array();
			if($idfile!=-1){
				$return['Status']= true;
				$return['FileName']= $idfile; 
				
			}else{
				$return['Status']= false;
				$return['FileName']= 'Lỗi tải tệp'; 
				
			}
			$return['Message'] = '';
			$return['FileServer']="";
			
			echo json_encode($return);exit;
                                // end ghi file chữ ký sốố
				//doan script de load lai trang danh sach cac van ban du thao trong ho so cong viec
                            //    echo '<script> window.parent.loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1); </script>';
			}
		}
		
                //exit; //Khong xu dung lop view
	}

    
}
