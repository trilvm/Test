<?php
/**
 * @author trunglv
 * @version 1.0
 * @example 
 */
require_once ('Zend/Controller/Action.php');
require_once 'Common/AjaxEngine.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/phienbanduthaoModel.php';
require_once 'hscv/models/gen_tempModel.php';
require_once 'hscv/models/hosocongviecModel.php';

class Hscv_VanBanDuThaoController extends AjaxEngine {
	/**
	 * Ham khoi tao
	 *
	 */        
	function init(){
		//Khong cho hien thi layout
		$this->_helper->layout->disableLayout();
	}
	/**
	 * Ham xu ly cho action index 
	 *
	 */
	function indexAction(){
		$this->returnResultHTML($this);
	}
	/**
	 * Ham ke thua tu lop AjaxEngine 
	 */
	protected function echoHTML(){
		$year = QLVBDHCommon::getYear();
                $auth = Zend_Registry::get('auth');
                $user = $auth->getIdentity();
                $this->view->user = $user;
		$iddivParent = $this->getParam('iddivParent');
		$is_noibo = $this->getParam('IS_NOIBO');		;
		$model = new VanBanDuThaoModel($year);
		$this->view->year = $year;
		$this->view->iddivParent = $iddivParent;
		$idHSCV = $this->_request->getParam('idHSCV');
		$this->view->idduthao = $this->_request->getParam('idduthao');
                $is_congviec = $this->_request->getParam('is_congviec');
                $this->view->is_congviec = $is_congviec;
                $this->view->isNoHSCV = 0;
		if($idHSCV ==0 || !$idHSCV){
			$temp = new gen_tempModel();
			$arr = array();
			$idHSCV = $temp->insert($arr);
			$this->view->isNoHSCV = 1;
		}		
		$this->view->idHSCV = $idHSCV;	
		$this->view->is_noibo = $is_noibo;	
		$this->view->data = $model->getListByIdHSCV($idHSCV);
		//Kiem tra cac truong hop truy xuat den file dinh kem
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
			$isreadonly = 0;
		$this->view->isreadonly = $isreadonly;
		$isCapnhat = 1;
		if($this->view->isNoHSCV == 1){
			$isCapnhat = 1;
		}
		else{if(hosocongviecModel::isLuutru($idHSCV,$year) == true || $isreadonly == 1){
			$isCapnhat = 0;
			$this->view->isreadonly = 1;
			}else{$isCapnhat = 1; }
		}
		$this->view->isCapnhat = $isCapnhat;
	}
	/**
	 * Lưu thông tin các văn bản dự thảo khi cập nhật hoặc thêm mới
	 */
	function saveAction(){
		$year = QLVBDHCommon::getYear();
		$type = $this->getParam('type');
		$iddivParent= $this->_request->getParam('iddivParent');
		$model = new VanBanDuThaoModel($year);                
                $idHSCV = $this->getParam('idHSCV');
		$idDuthao = $this->getParam('idDuthao');
		$idlvb = (int)$this->getParam('ID_LVB');
		$checksign = (int)$this->getParam('checkSign');
		$version = $this->getParam('version');
		$tenduthao = $this->getParam('duthao_tenvanbanduthao');
		$isNoHSCV = $this->_request->getParam('isNoHSCV');
                $comment= $this->getParam('comment');
                $formData=$this->_request->getParams();
				
		if($idDuthao>=1){
			//truong hop cap nhat
			$data_json = base64_decode($this->getParam('data'));
			//var_dump($data_json);exit;
			$data = json_decode($data_json);
			$data->TENDUTHAO = base64_decode($data->TENDUTHAO);
			$data->ID_LVB = base64_decode($data->ID_LVB);
			$data->comment = base64_decode($data->comment);			
			//Capnhat lai thong tin cua van ban du thao trong csdl
			$model->updateByIdDuthaoNoHSCV($idDuthao,$data);
			//doan javascript de cap nhat lai danh sach cac van ban du thao
                            echo 'loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		}else{
			if($idDuthao == 0 && $checksign == 0){
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
				//Them moi cac file dinh kem tuong ung voi cac phien ban du thao co trong du thao
                                    filedinhkemModel::insertFile($idPBDuthao,1,$iddivParent,$year,$type);
                                //ghi file chữ ký số
                                $auth = Zend_Registry::get('auth');
                                $user = $auth->getIdentity();
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
									global $db;
								   $data = $model->getListByIdHSCV($idHSCV);      
								   if(count($data) <=1){
								   $db->update(QLVBDHCommon::Table('hscv_hosocongviec'), array('ID_U_XULY' => $user->ID_U), "ID_HSCV=".$idHSCV);
								   }
                                // end ghi file chữ ký sốố
				//doan script de load lai trang danh sach cac van ban du thao trong ho so cong viec
                                echo '<script> window.parent.loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1); </script>';
			}else{
				                echo '<script> window.parent.loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1); </script>';
			}
		}
                exit; //Khong xu dung lop view
	}
	/**
	 * Ham hien thi khung nhap lieu cho van ban du thao
	 */
	function inputAction(){
		$year = QLVBDHCommon::getYear();
		$iddivParent = $this->getParam('iddivParent');
		$model = new VanBanDuThaoModel($year);
                global $auth;
                $this->view->user = $auth->getIdentity();
		$isnew = $this->getParam('isnew');
		if(!$isnew)
			$isnew = 0;
		$this->view->isnew = $isnew;
		$id_duthao = $this->getParam('idDuthao');
		$dtaCaphhat = $model->getDataByIdDuthao($id_duthao);
		//Khoi tao du lieu cho lop view
                $is_congviec = $this->getParam('is_congviec');
                $this->view->is_congviec = $is_congviec;
		$this->view->idDuthao = $dtaCaphhat->ID_DUTHAO;
		$this->view->tenduthao = $dtaCaphhat->TENDUTHAO;
		$this->view->nguoiky = $dtaCaphhat->NGUOIKY;
		$this->view->nguoisoan = $dtaCaphhat->NGUOISOAN;
		$this->view->trangthai =$dtaCaphhat->TRANGTHAI;
		$this->view->comment =$dtaCaphhat->COMMENT;
		$this->view->year = $year;
		$this->view->iddivParent = $iddivParent;
		$this->view->idHSCV = $this->_request->getParam('idHSCV');
		$this->view->isNoHSCV = $this->_request->getParam('isNoHSCV');
		$r = $model->getAdapter()->query("SELECT * FROM VB_LOAIVANBAN ORDER BY NAME");
		$this->view->loaivb = $r->fetchAll();
		$this->view->idlvb = $dtaCaphhat->ID_LVB;
	}
	/**
	 * Ham xoa cac van ban du thao tuong ung trong ho so cong viec
	 */
	function deleteAction(){
		$year = QLVBDHCommon::getYear();
		$iddivParent= $this->_request->getParam('iddivParent');
		$idHSCV = $this->_request->getParam('idHSCV');
		$model = new VanBanDuThaoModel($year);
		$hscv_idvanbanduthao = $this->getParam('DELidvanbanduthao'.$idHSCV); 
		for($i=0;$i<count($hscv_idvanbanduthao);$i++)
			$model->deleteOne($hscv_idvanbanduthao[$i],$year);
		//doan java script tra ve cho client de cap nhạt lai danh sach cac van ban du thao trong
		//ho so cong viec tuong ung 

		$model->deleteOne($this->_request->getParam('idDuthao'),$year);
		echo 'loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanduthao?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		exit; //Khong xu dung lop view
	}

	/**
	* Hàm chọn dự thảo được ban hành
	* Author: Thangtba
	*/
	function chonbanhanhAction() {

		$params = $this->getRequest()->getParams();
		$year = QLVBDHCommon::getYear();
                
                $db = Zend_Db_Table::getDefaultAdapter();
                
		//lấy tham số từ ajax
		$idpbdt = $params['idpbdt'];
        $idDuthao = $params['idDuthao'];
		$value = $params['value'];
                
                //reset toàn bộ phiên bản, sau đó check perfect
                $db->update('hscv_phienbanduthao_'.$year, array('CHONBANHANH' => 0), "ID_DUTHAO =".$idDuthao);
				$auth = Zend_Registry::get('auth');
				$id_u = $auth->getIdentity()->ID_U;
				if(hosocongviecModel::isVanthu($id_u)){
					$db->update('hscv_duthao_'.$year, array('TRANGTHAI' => 1), "ID_DUTHAO =".$idDuthao);
				}
                return $db->update('hscv_phienbanduthao_'.$year, array('CHONBANHANH' => $value), "ID_PB_DUTHAO =".$idpbdt);                
                
		exit;
	}
}

?>
