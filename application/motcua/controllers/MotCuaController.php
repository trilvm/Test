<?php

require_once ('Zend/Controller/Action.php');
require_once 'motcua/models/phieu_yeucau_bosungModel.php';
require_once 'motcua/models/motcua_hosoModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'motcua/models/LoaiModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'motcua/models/motcua_hosoForm.php';
require_once 'motcua/models/motcua_nhangomModel.php';
require_once 'motcua/models/LoaiModel.php';
require_once 'motcua/models/MotCuaNhanGomModel.php';
require_once 'config/motcua.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'qtht/models/SoVanBanModel.php' ;
require_once 'motcua/models/DongboHSMCModel.php';
require_once 'motcua/models/linhvucmotcuaModel.php';
class Motcua_motcuaController extends Zend_Controller_Action {

	function init(){
		$this->_helper->layout->disableLayout();
	}
	function inputbosungAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
		$this->view->year = $this->parameter["year"];
		$this->view->idHSCV = $this->parameter["id"];
		$this->view->id_loaihscv = $this->_request->getParam('type');
		
	}
	
	function saveyeucaubosungAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$idHSCV = $param["id"];
		$phieu_yc = new phieu_yeucau_bosung();
		$phieu_yc->_id_hscv = $idHSCV;
		$phieu_yc->_sophieu = $param['sophieu'];
		$phieu_yc->_ngay_yeucau = date('y-m-d h:m:s');
		$phieu_yc->_cacghichu = $param['ghichu'];
		$phieu_yc->_nguoiyeucau = $user->ID_U;
		$phieu_ycModel = new phieu_yeucau_bosungModel(qlvbdhCommon::GetYear());
		$phieu_ycModel->inserOne($phieu_yc);
		$db->update(qlvbdhCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_BOSUNG"=>1),"ID_HSCV=".$idHSCV);
		echo "<script>window.parent.document.frm.submit();</script>";
		
		$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
		$hoso = $hsmc->getHSMCByIdHSCV($idHSCV);
		QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,2,$param['ghichu'],$hoso->DIENTHOAI);
		
		exit;
		
	}
	
	function bosungAction(){
		global $auth;
		$user = $auth->getIdentity();
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->ID_HSCV = $this->parameter["id"];
		$this->view->idHSCV = $this->parameter["id"];
		$this->view->id_loaihscv = $this->_request->getParam('type');
		$phieu_ycModel = new phieu_yeucau_bosungModel(qlvbdhCommon::GetYear());
		$this->view->phieu_yc =  $phieu_ycModel->getNearPhieuYeuCauByIdHSCV($this->view->idHSCV);
		
	}
	
	function savebosungAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$idHSCV = $param["id"];
		$id_yeucau = $param["id_yeucau"];
		$phieu_yc = new phieu_yeucau_bosung();
		$phieu_yc->_id_yeucau = $id_yeucau;
		$phieu_yc->_nguoibosung = $user->ID_U;
		$phieu_yc->_ngay_bosung = date('y-m-d h:m:s');
		$phieu_yc->_cacghichu = $param['pre_ghichu']."\n"."<font color=blue><b>".$user->FULLNAME.":</b>".$param['ghichu']."</font>";
		$phieu_ycModel = new phieu_yeucau_bosungModel(qlvbdhCommon::GetYear());
		$phieu_ycModel->updateWhenBoSung($phieu_yc);
		$db->update(qlvbdhCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_BOSUNG"=>0),"ID_HSCV=".$idHSCV);
		echo "<script>window.parent.document.frm.submit();</script>";
		
		$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
		$hoso = $hsmc->getHSMCByIdHSCV($idHSCV);
		QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,1,"Đang xử lý",$hoso->DIENTHOAI);
				
		exit;
	}
	
	function trahosoAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$year = QLVBDHCommon::getYear();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
		
		$this->view->year = $year;
		$this->view->idHSCV = $this->parameter["id"];
		$this->view->id_loaihscv = $this->_request->getParam('type');
		$model = new motcua_hosoModel($year);
		$this->view->hsmcInfo = $model->getHSMCByIdHSCV($this->view->ID_HSCV); 
		$tlnhangomModel = new motcua_nhangomModel($year);
		$this->view->tailieu = $tlnhangomModel->getTaiLieuByIdHSMC($this->view->hsmcInfo->ID_HOSO);
		$this->view->TenLoaiHoSo = ($model->getTenLoaiHoSoById($this->view->hsmcInfo->ID_LOAIHOSO));
		QLVBDHCommon::GetTree(&$thumuc,"HSCV_THUMUC","ID_THUMUC","ID_THUMUC_CHA",1,1);
		$this->view->thumuc = $thumuc;
	}
	
	function savetrahosoAction(){
		global $auth;
		///global $db;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$idHSCV = $param["id"];
		$wf_id_t = $param["wf_id_t"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		//$year = $param["year"];
		$year = QLVBDHCommon::getYear();
		$ngay_tra = $param["ngay_tra"];
		$wf_name_user = $param["wf_name_user"];
		$wf_hanxuly_user = $param["wf_hanxuly_user"];
		//chuyen dinh dang ngay thang (30/12/2009 -> 2009-12-30)
		$ngay_tra = trim($ngay_tra);
		$arr = explode('/',$ngay_tra);
		$ngay_tra = date('y-m-d',mktime(null,null,null,$arr[1],$arr[0],$arr[2]));
		$luc_tra = $param["luc_tra"];
		$is_khongxuly = $param['is_khongxuly']; 
		if(!$is_khongxuly) $is_khongxuly = 0;
		if($is_khongxuly > 0) $is_khongxuly = 1;
		$db = Zend_Db_Table::getDefaultAdapter();
		//Zend_Registry::set('year',$year);
		//Kiem tra ho so da duoc tra hay khong
		//if(WFEngine::SendNextUserByObjectId($idHSCV,$wf_nexttransition,$user->ID_U,$wf_nextuser,$wf_name_user,$wf_hanxuly_user)==1){
			$motcuahsModel = new motcua_hosoModel($year);
			$motcuahsModel->updateAfterTraHoSo($idHSCV,$ngay_tra,$luc_tra,$is_khongxuly);
			//cap nhat trang thai cac du thao trong ho so mot cua
			$VBDTModel = new VanBanDuThaoModel($year);
			$VBDTModel->updateTrangthaiByIdHSCV($idHSCV,1);	
			$VBDTModel->updateNguoiKyByIdHSCV($idHSCV,$user->ID_U);	
		//}
		if($param["THUMUC"]>1){
			$hscv = new hosocongviecModel();
			$hscv->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("ID_THUMUC"=>$param["THUMUC"]),"ID_HSCV=".(int)$idHSCV);
			//echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã lưu trữ thành công.');window.parent.document.frm.submit();</script>";
			//exit;
		}
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
	}
	
	function insertAction(){
		
		exit;
	}
	/*
     * This fuction is input action. This action display form to input data
     */
	function inputAction() {
        global $auth;
        $user = $auth->getIdentity();

        //Enable Layout
        $this->_helper->layout->enableLayout();
        //lay id Ho so mot cua
        $id = (int) $this->_request->getParam('id');
        $error = $this->_request->getParam('error');
        $wf_id_t = (int) $this->_request->getParam('wf_id_t', 0);
        $type = (int) $this->_request->getParam('type', 3);
        $formData = $this->_request->getPost();
        //If it has submitted from listForm
        $validFrom = $this->getRequest()->getParam("inputSubmit");
        $arrayIdFile = $this->_request->getParam('idFile');
        //lay tu combobox
        $year = QLVBDHCommon::getYear();
        //thiet lap cac bien view
        $this->view->wf_id_t = $wf_id_t;
        $this->view->type = $type;
        $this->view->error = $error;
        $today = date("Y-m-d");
        $motcuaModel = new motcua_hosoModel($year);
        $filedinhkemModel = new filedinhkemModel($year);
        $this->view->formdata = $formData;
        //Lay danh sach thu tuc va le phi
        $loaimodel = new LoaiModel();
        $this->view->dataLoai = $loaimodel->fetchAll();
        //Lay id_hscv tu trang /hscv/hscv/list
        $linhvuc = new linhvucmotcuaModel();
        $this->view->data = $linhvuc->SelectAllByUID($user->ID_U);
        if ($formData["ID_LV_MC"] == 0) {
            $formData["ID_LV_MC"] = $this->view->data[0]["ID_LV_MC"];
        }
        $id_hscv = (int) $this->_request->getParam('id_hscv');
        if ($id_hscv > 0) {
            $rowHSMC = $motcuaModel->getHSMCByIdHSCV($id_hscv);
            if ($rowHSMC != null)
                $id = $rowHSMC->ID_HOSO;
        }
        //$form = new motcua_hosoForm(array('id'=>$id));
        //if id>0 It's edit action awake		
        $form = new motcua_hosoForm(array('id' => $id, "ID_LV_MC" => $formData["ID_LV_MC"]));
        //echo $formData["ID_LV_MC"];
        if ($id > 0) {
            $this->view->title = "cập nhật Hồ sơ một cửa";
            //$this->view->subtitle = "Cập nhật";

            $motcuas = $motcuaModel->fetchRow('ID_HOSO=' . $id);
            if ($motcuas != null)
                $form->populate($motcuas->toArray());
            else
                $this->_redirect('/motcua/motcua/input');
            $this->view->form = $form;
            //Save your id loai ho so
            $this->view->id = $id;
            $nhan_ngay = "";
            $nhan_ngay = implode("/", array_reverse(explode("-", $motcuas->NHAN_NGAY)));
            $nhanlai_ngay = "";
            $nhanlai_ngay = implode("/", array_reverse(explode("-", $motcuas->NHANLAI_NGAY)));
            $this->view->nhan_ngay = $nhan_ngay;
            $this->view->nhanlai_ngay = $nhanlai_ngay;
            $this->view->loaihoso = $motcuas->ID_LOAIHOSO;
            //$this->view->ID_LV_MC=$motcuas->ID_LV_MC;  
            $this->view->mahoso = $motcuas->MAHOSO;
            $this->view->idTemp = $motcuas->ID_HSCV;
            $this->view->id_stn = $motcuas->ID_STN;
            $this->view->so = $motcuas->SO;
        }
        else {
            $id_dkquamang = $this->getRequest()->getParam("id_dkquamang");
            if ($id_dkquamang > 0) {
                $this->view->id_dkquamang = $id_dkquamang;
                $data_dkquamang = DongboHSMCModel::getDetail($id_dkquamang);
                //var_dump($data_dkquamang);
                //$this->view->loaihoso = $data_dkquamang ["ID_LOAIHOSO"];

                $formData = array();
                $formData["ID_LOAIHOSO"] = $data_dkquamang ["ID_LOAIHOSO"];
                $formData["ID_LV_MC"] = $data_dkquamang ["ID_LV_MC"];
                //$form = new motcua_hosoForm(array('id'=>$id,"ID_LV_MC"=>$formData["ID_LV_MC"]));
                $formData["TENTOCHUCCANHAN"] = $data_dkquamang ["HOTEN"];
                $formData["DIACHI"] = $data_dkquamang ["DIACHI"];
                $formData["DIENTHOAI"] = $data_dkquamang ["DIENTHOAI"];
                $formData["EMAIL"] = $data_dkquamang ["EMAIL"];
                $form = new motcua_hosoForm(array('id' => $id, "ID_LV_MC" => $formData["ID_LV_MC"]));
            }
            //$form->populate($formData);

            $this->view->title = "thêm mới Hồ sơ một cửa";
            //$this->view->subtitle = "Thêm mới";

            $this->view->form = $form;
            //Luu danh sach file dinh kem neu submit bi loi     		
            try {
                if (count($arrayIdFile) > 0) {

                    $dataFiledinhkem = $filedinhkemModel->fetchRow("MASO='" . $arrayIdFile[0] . "'");
                    if ($dataFiledinhkem != null) {
                        $this->view->idTemp = $dataFiledinhkem->ID_OBJECT;
                    }
                }
            } catch (Exception $e2) {
                
            }
        }
        //if has error from add,update populate form and display error

        if ($error != null) {
            $form->populate($formData);
        } else
        if ($this->_request->isPost() && $validFrom != null) {
            $form = $this->view->form;
            //if ($form->isValid($_POST)) 
            //{ 
            $this->dispatch('saveAction');
            //}
            //else 
            //{
            if ($formData["ID_LOAIHOSO"] > 0) {
                $formData["LEPHI"] = $loaimodel->getLePhiByIdLoai($formData["ID_LOAIHOSO"]);
                $dataLoai = $loaimodel->fetchRow("ID_LOAIHOSO=" . $formData["ID_LOAIHOSO"]);
                if (count($dataLoai) > 0) {
                    $formData["TRICHYEU"] = $dataLoai->TENLOAI . " cho " . $formData["TENTOCHUCCANHAN"];
                    $this->view->songayxuly = $dataLoai->SONGAYXULY;
                    $this->view->tenloai = $dataLoai->TENLOAI;
                    $this->view->nhan_ngay = date("d/m/Y");
                    //$tempNhanNgay= DongboHSMCModel::addDateAll(strtotime($today),$dataLoai->SONGAYXULY);
                    //$this->view->nhanlai_ngay=date('d/m/Y',$tempNhanNgay);
                    $this->view->hanxuly = $dataLoai->SONGAYXULY;
                }
                $this->view->type_id = $formData["ID_LOAIHOSO"];
            }
            $form->populate($formData);
            //}
        } else {
            if ($formData["ID_LOAIHOSO"] > 0) {
                $formData["LEPHI"] = $loaimodel->getLePhiByIdLoai($formData["ID_LOAIHOSO"]);
                $dataLoai = $loaimodel->fetchRow("ID_LOAIHOSO=" . $formData["ID_LOAIHOSO"]);
                if (count($dataLoai) > 0) {
                    $formData["TRICHYEU"] = $dataLoai->TENLOAI . " cho " . $formData["TENTOCHUCCANHAN"];
                    $this->view->songayxuly = $dataLoai->SONGAYXULY;
                    $this->view->tenloai = $dataLoai->TENLOAI;
                    $this->view->nhan_ngay = date("d/m/Y");
                    //$tempNhanNgay= DongboHSMCModel::addDateAll(strtotime($today),$dataLoai->SONGAYXULY);
                    //$this->view->nhanlai_ngay=date('d/m/Y',$tempNhanNgay);
                    $this->view->hanxuly = $dataLoai->SONGAYXULY;
                }
                $this->view->type_id = $formData["ID_LOAIHOSO"];
            }
            $form->populate($formData);
            try {
                if (count($arrayIdFile) > 0) {

                    $dataFiledinhkem = $filedinhkemModel->fetchRow("MASO='" . $arrayIdFile[0] . "'");
                    if ($dataFiledinhkem != null) {
                        $this->view->idTemp = $dataFiledinhkem->ID_OBJECT;
                    }
                }
            } catch (Exception $e2) {
                
            }
        }

        if ($formData["ID_LOAIHOSO"] > 0)
            $type_id = $loaimodel->getIdLoaiHscvByIdLoai($formData["ID_LOAIHOSO"]);
        $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($type_id, $user->ID_U);
        if (count($createarr) > 0) {
            $this->view->wf_id_t = $createarr["ID_T"];
        } else {
            $this->view->wf_id_t = "";
        }

        $this->view->formdata = $formData;
        //Add button 
        QLVBDHButton::EnableSave("/motcua/motcua/save");
        if ($id > 0) {
            QLVBDHButton::AddButton("In phiếu", "", "PrintButton", 'window.open("/motcua/motcua/phieunhanhoso/id/' . $id . '");');
        }
        QLVBDHButton::EnableBack("/motcua/motcua");
    }
    /**
	 * Them moi/cap nhat Ho So mot Cua
	 *
	 */
	function saveAction()
	{
		$formData = $this->_request->getPost();			
		$id=(int)$this->_request->getParam("id",0);
    	$id_hscv=(int)$this->_request->getParam("ID_HSCV",0);
    	$type=(int)$this->_request->getParam("type",3);
    	$year= QLVBDHCommon::getYear();
    	$wf_id_t = (int)$this->_request->getParam("wf_id_t",0);    	
    	$wf_nexttransition = $formData["wf_nexttransition"];
		$wf_nextuser = $formData["wf_nextuser"];
		$wf_nextg = $formData["wf_nextg"];
		$wf_nextdep = $formData["wf_nextdep"];
		$wf_name_user = $formData["wf_name_user"];
		$wf_name_g = $formData["wf_name_g"];
		$wf_name_dep = $formData["wf_name_dep"];
		$wf_hanxuly_user = $formData["wf_hanxuly_user"];
		$wf_hanxuly_g = $formData["wf_hanxuly_g"];
		$wf_hanxuly_dep = $formData["wf_hanxuly_dep"];
    	$hscv=new hosocongviecModel();
		$motcua_hoso=new motcua_hosoModel($year);
		$filedinhkemModel =  new filedinhkemModel($year);	
		$motcuanhangomModel = new MotCuaNhanGomModel($year);	
		$loaimodel=new LoaiModel();	
    	//lay ngay hien tai		
    	$today = date("Y-m-d h:m:s");
		$getIdHscv=	$formData['ID_HSCV'];	
		//lay danh sach Id của filedinhkem
		$arrayIdFile=$formData['idFile'];		
		//lay id_session		
		$id_session=session_id();
		//Lay danh sach ten thu tuc
		$arrayTenThuTuc=$this->getRequest()->getParam('tenthutuc');
		//xu ly truong nhan_ngay			
		$nhan_ngay=implode("-",array_reverse(explode("/",$formData['NHAN_NGAY'])));		
		$nhanlai_ngay=implode("-",array_reverse(explode("/",$formData['NHANLAI_NGAY'])));	
		$type_id=$loaimodel->getIdLoaiHscvByIdLoai($formData["ID_LOAIHOSO"]);		
		if($type_id>0) 
			$type=$type_id;		

		if($getIdHscv==0)
		{
			$getIdHscv = $hscv->CreateHSCV(
			$formData["TRICHYEU"],1,
			$type,
			$today,
			$today,
			Zend_Registry::get('auth')->getIdentity()->ID_U,
			$wf_nextuser,
	        $formData["wf_name_user"],
	        $formData["wf_hanxuly_user"]);			
		} 	
		
		//svar_dump($formData);exit;
    	//var_dump($formData);exit;
		if($formData!=null)
    	{
    		try 
	    	{
	       		if($formData['SOKYHIEU_CHAR'])
					$sokyhieu =  $formData['SO'] . "/" .$formData['SOKYHIEU_CHAR'];
				$data = array(
	                    'ID_LOAIHOSO' 		=> $formData['ID_LOAIHOSO'],
	                    'TRICHYEU' 		=> $formData['TRICHYEU'],	                    
	                	'NGUOINHAN' 	=> $wf_nextuser,
	                	'MAHOSO' 		=> $formData['MAHOSO'],
	                	'TENTOCHUCCANHAN' 		=> $formData['TENTOCHUCCANHAN'],
	                	'DIACHI' 	=> $formData['DIACHI'] ,
	                	'NHAN_LUC'	=>(trim($formData['NHAN_LUC']) == ""?null:$formData['NHAN_LUC']),
	                	'NHAN_NGAY'=> $nhan_ngay,
	                	'NHANLAI_LUC'	=>(trim($formData['NHANLAI_LUC']) == ""?null:$formData['NHANLAI_LUC']),
	                	'NHANLAI_NGAY'=> $nhanlai_ngay,	                	
	                	'LEPHI'		=> (trim($formData['LEPHI_hidden']) == ""?null:$formData['LEPHI_hidden']),
	                	'TRANGTHAI' 	=> '1',
	                	'NGAYNHAN' 	=> $today,
	                	'CHUTHICH' 	=> $formData['CHUTHICH'],	 	 	                    
	                	'EMAIL' 	=> $formData['EMAIL'],	 
	                	'DIENTHOAI' 	=> $formData['DIENTHOAI'],	  
	       				'SO' 	=> $formData['SO'], 
						'ID_STN' => $formData['ID_STN'],
						'SOKYHIEU' 	=> $sokyhieu, 'SOKYHIEU_CHAR' 	=> $formData['SOKYHIEU_CHAR'],
	       		        'NGUOITAO'=>Zend_Registry::get('auth')->getIdentity()->ID_U ,
						'ID_DKQUAMANG' => (int)$formData['ID_DKQUAMANG']
						
	                );

		        if($id>0)	                
		        {
		        	$where="ID_HOSO=".$id;
		        	if($formData["ID_HSCV"]>0)
		        	{
		        		//$motcua_hoso->getDefaultAdapter()->beginTransaction();
			        	try
			        	{
			        		/*
			        		$data_hscv = $motcua_hoso->findHscv($formData["ID_HSCV"]);
				        	//Xoa hscv
				        	$motcua_hoso->getDefaultAdapter()->delete("HSCV_HOSOCONGVIEC_".$year,"ID_HSCV=".((int)$formData["ID_HSCV"]));
				        	//Xoa process
				        	$motcua_hoso->getDefaultAdapter()->delete("WF_PROCESSITEMS_".$year,"ID_PI=".$data_hscv['ID_PI']);
				        	//Xoa log
				        	$motcua_hoso->getDefaultAdapter()->delete("WF_PROCESSLOGS_".$year,"ID_PI=".$data_hscv['ID_PI']);
				        	//tạo hscv
				        	$getIdHscv = $hscv->CreateHSCV(
				        	$formData["TRICHYEU"],1,3,
				        	$nhan_ngay,
				        	$nhan_ngay,
				        	Zend_Registry::get('auth')->getIdentity()->ID_U,
				        	$wf_nextuser,
						    $formData["wf_name_user"],
						    $formData["wf_hanxuly_user"]);
						    //echo $getIdHscv;exit;
				        	if($getIdHscv>0)
				        	{
					            //cập nhật HSMC
							*/
					        $data +=array("ID_HSCV"=>$formData["ID_HSCV"]);
			           		$motcua_hoso->update($data,$where);
			           		/*
			            		//
				        		
				        	}
				        	else
				        	{
				        		$getIdHscv=0;
				        		$motcua_hoso->getDefaultAdapter()->rollBack();
				        	}
							*/
			        	}
			        	catch(Exception $ex)
			        	{
			        		$messageError="Lỗi cập nhật HSCV".$ex;	
							$this->_request->setParam('error',$messageError);
							$this->_request->setParams($formData);
							//$motcua_hoso->getDefaultAdapter()->rollBack();
							$this->dispatch('inputAction');		
			        		
			        	}             
		       	 	}
		        	//$motcua_hoso->update($data,$where);	
		        	
		        		        	
		        }
		        else 
		        {
		        	$data +=array("ID_HSCV"=>$getIdHscv);
		        	$id=$motcua_hoso->insert($data);
					
					//DongboHSMCModel::updateTiepnhan(1,$formData['ID_DKQUAMANG']);

		        }
				//cap nhat dong bo ho so tren website

				if($formData['ID_DKQUAMANG'] > 0){
					$paramcapnhatweb = array(
						"HOTEN" => 	$formData['TENTOCHUCCANHAN'],
						"DIACHI" => $formData['DIACHI'],
						"EMAIL" => $formData['EMAIL'],
						"DIENTHOAI" => $formData['DIENTHOAI'],
						"DIENTHOAI" => $formData['DIENTHOAI'],
						"NGAYTIEPNHAN" => $nhan_ngay,
						"NGAYHENTRA" => $nhanlai_ngay,
						"NGUOIDANGXULY"=> ""
					);
					DongboHSMCModel::updateAfterTiepnhan($formData['ID_DKQUAMANG'],$paramcapnhatweb);
				}

		        //cap nhat lai trang thai cho cac file dinh kem voi ID_HSCV
		        if($getIdHscv>0)
		        {
			        for($i=0;$i<count($arrayIdFile);$i++)
		        	{   
	                	$filedinhkemModel->update(array("ID_OBJECT"=>$getIdHscv,"TYPE"=>1),"MASO='".$arrayIdFile[$i]."'");				  
		        	}
		        }
	        	//Xoa het thu tuc truoc
	        	$motcuanhangomModel->delete("ID_HOSO=".$id);				  
	        	$motcuanhangomModel->delete("ID_SESSION='".$id_session."'");
	        	//Cap nhat lai trang thai thu tuc can co trong truong hop tao moi
	        	for($i=0;$i<count($arrayTenThuTuc);$i++)
	        	{   
	        		try 
	        		{
	        			$motcuanhangomModel->insert(array("ID_HOSO"=>$id,"ID_SESSION"=>null,"TEN_THUTUC"=>$arrayTenThuTuc[$i]));
	        		}
	        		catch(Exception $e2)
	        		{
	        			
	        		}
	        	}  	
		        
	        	$MASOHOSO = 'MSVB';
		        /**
		         * trunglv tao doi tuong de lay ma so van ban den
		         */
		        $hs_mocua = new hosomotcua();
		       
		        $hs_motcua->_id_loaihoso = $formData['ID_LOAIHOSO'];
		        $hs_motcua->_sothutu = $id; // so thu tu la id tang tu dong cua ho so
		       	$id_pb = Common_Maso::getIdDepByUser($wf_nextuser);
		        $hs_motcua->_id_phongban = $id_pb;
		        $MASOHOSO = Common_Maso::getMaSo(3,$hs_motcua);
	        	
		        $motcua_hoso->update(array('MAHOSO'=>$MASOHOSO),"ID_HOSO=".$id);
	        	//end trung lv
				QLVBDHCommon::InsertHSMCService($MASOHOSO,$formData['TENTOCHUCCANHAN'],$formData['TRICHYEU'],1,"Đang xử lý",$formData['DIENTHOAI']);
	        	//QLVBDHCommon::InsertHSMCService("a","b","c",1,"d");
	        	
	        	echo "
	        	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	        	<script>
	        	if(confirm('Tạo hồ sơ thành công. Bạn có muốn in phiếu biên nhận không?')){
	        		window.open('/motcua/motcua/phieunhanhoso/id/$id');
	        	}
	        	document.location.href = '/hscv/hscv/list/code/pre';
	        	</script>";
	        	exit;
	        	//$this->_redirect('/hscv/hscv/list/code/pre');		      
	        	
			}
			catch(Exception $e2)
			{
				$messageError="Có lỗi xảy ra khi thêm mới/update dữ liệu".$e2;	
				$this->_request->setParam('error',$messageError);
				$this->_request->setParams($formData);
				$this->dispatch('inputAction');			
			}
    	}
    	else{
    		$this->_redirect('/motcua/motcua/input');
    	}
		
       	
	}
	/**
     * delete action
     *
     */
    function deleteAction()
    {
    	$this->view->title = "Xóa Hồ sơ một cửa";
    	$year=QLVBDHCommon::getYear();
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/mocua/motcua/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
    	if($this->_request->isPost())
		{
			$idarray = $this->_request->getParam('DEL');
			if(count($idarray)<=0)
			{
				$this->view->deleteError="Không có mục nào để tiến hành xóa( xin vui lòng chọn một mục!)";
			}
			$counter=0;
			while ( $counter < count($idarray )) 
			{
				
				if ($idarray[$counter] > 0) 
				{
					try 
					{
						$delLoai = new motcua_hosoModel($year);
	                	$where = 'ID_HOSO = ' . $idarray[$counter];
	                	$delLoai->delete($where);
						
					}
					catch(Exception $er){ $adderror=$adderror.$idarray[$counter].' ; ';};
				}
				$counter++;
			}
			//already delete some or all items
			if($counter>0)
			{
				if($counter==count($idarray ))	
				{
					$this->view->deleteError="Xóa thành công các mục đã chọn";	
					$this->_redirect('/hscv/hscv/list');				
				}
				else 
				{
					$this->view->deleteError="Không xóa được mục đã chọn";
				}
			}
			if($adderror!='')
			{
				$this->view->deleteError="Xóa không thành công các mục với id= ".$adderror;
			}
		}
	}
	/**
	 * The default action - show list page
	 */
	public function indexAction() 
	{
		$this->_helper->layout->enableLayout();
		$this->view->title = "Danh sách Hồ sơ một cửa";
		//$this->view->subtitle="Danh sách";
		QLVBDHButton::EnableAddNew("/motcua/motcua/input");
		QLVBDHButton::EnableDelete("/motcua/motcua/delete");
		//Doc du lieu de hien thi len view
		$config = Zend_Registry::get('config');
		$page = $this->_request->getParam('page');
		$year = QLVBDHCommon::getYear();
		$limit = $this->_request->getParam('limit');
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$model=new motcua_hosoModel($year);
		$this->view->filter_object = $filter_object; 
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
		$this->view->data = $model->SelectAll(($page-1)*$limit,$limit,$search,$filter_object,$order);		
		$n_rows = $model->count($search,$filter_object);
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frmListMotCuas",$page) ;
		$this->view->limit=$limit;
		$this->view->page=$page;
		$this->view->year=$year;
	}
	public function phieunhanhosoAction(){
		global $auth;
		$this->_helper->layout->disableLayout();
		$id = $this->_request->getParam('id');
		$year = QLVBDHCommon::getYear();
		$model=new motcua_hosoModel($year);
		$loai = new LoaiModel();
		
		$nhangom = new MotCuaNhanGomModel($year);
		$data = $model->find((int)$id)->current();
		$tenloai = $loai->find($data->ID_LOAIHOSO)->current();
		$nhangom = $nhangom->fetchAll("ID_HOSO=".(int)$id);
		$user = $auth->getIdentity();
		$config = Zend_Registry::get('config');
		$this->view->config = $config;
		$this->view->user = $user;
		$this->view->tenloai = $tenloai->TENLOAI;
		$this->view->data = $data;
		$this->view->nhangom = $nhangom;
	}
	
	public function getsohosoAction(){
		$this->_helper->layout->disableLayout();
		$params =  $this->_request->getParams();
		$id_stn = $params["id_stn"];
		//echo $id_stn;
		echo motcua_hosoModel::getNextSoHoso($id_stn);
		exit;
	}
}