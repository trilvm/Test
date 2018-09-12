<?php

require_once ('Zend/Controller/Action.php');
require_once 'motcua/models/phieu_yeucau_bosungModel.php';
require_once 'motcua/models/motcua_hosoModel.php';
require_once 'motcua/models/HoSoMotCuaModel.php';
require_once 'motcua/models/LoaiModel.php';
require_once 'motcua/models/HoSoMotCuaForm.php';
require_once 'hscv/models/hosocongviecModel.php';
class Motcua_HoSoMotCuaController extends Zend_Controller_Action {

	function init(){
		$this->_helper->layout->disableLayout();
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
		$model=new HoSoMotCuaModel($year);
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
		$wf_id_t = $param["wf_id_t"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		$year = $param["year"];
		$wf_name_user = $param["wf_name_user"];
		$wf_hanxuly_user = $param["wf_hanxuly_user"];
		Zend_Registry::set('year',$year);
		
		if(WFEngine::SendNextUserByObjectId($idHSCV,$wf_nexttransition,$user->ID_U,$wf_nextuser,$wf_name_user,$wf_hanxuly_user)==1){
		
			$phieu_yc = new phieu_yeucau_bosung();
			$phieu_yc->_id_hscv = $idHSCV;
			$phieu_yc->_sophieu = $param['sophieu'];
			$phieu_yc->_ngay_yeucau = date('y-m-d h:m:s');
			$phieu_yc->_cacghichu = $param['ghichu'];
			$phieu_ycModel = new phieu_yeucau_bosungModel($year);
			$phieu_ycModel->inserOne($phieu_yc);
		}

		
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
		
	}
	
	function bosungAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
		$this->view->year = $this->parameter["year"];
		$this->view->idHSCV = $this->parameter["id"];
		$this->view->id_loaihscv = $this->_request->getParam('type');
		$phieu_ycModel = new phieu_yeucau_bosungModel($this->view->year);
		$this->view->phieu_yc =  $phieu_ycModel->getNearPhieuYeuCauByIdHSCV($this->view->idHSCV);
		
	}
	
	function savebosungAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$idHSCV = $param["id"];
		$wf_id_t = $param["wf_id_t"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		$year = $param["year"];
		$id_yeucau = $param["id_yeucau"];
		$wf_name_user = $param["wf_name_user"];
		$wf_hanxuly_user = $param["wf_hanxuly_user"];
		Zend_Registry::set('year',$year);
		
		if(WFEngine::SendNextUserByObjectId($idHSCV,$wf_nexttransition,$user->ID_U,$wf_nextuser,$wf_name_user,$wf_hanxuly_user)==1){
		
			$phieu_yc = new phieu_yeucau_bosung();
			$phieu_yc->_id_yeucau = $id_yeucau;
			$phieu_yc->_ngay_bosung = date('y-m-d h:m:s');
			$phieu_yc->_cacghichu = $param['ghichu'].$param['pre_ghichu'];
			$phieu_ycModel = new phieu_yeucau_bosungModel($year);
			$phieu_ycModel->updateWhenBoSung($phieu_yc);
		}

		
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
		
	}
	
	function trahosoAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
		$this->view->year = $this->parameter["year"];
		$this->view->idHSCV = $this->parameter["id"];
		$this->view->id_loaihscv = $this->_request->getParam('type');
		//ngay tra , luc tra , trang thai , co xu ly khong
		}
	
	function savetrahosoAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$idHSCV = $param["id"];
		$wf_id_t = $param["wf_id_t"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		$year = $param["year"];
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
		Zend_Registry::set('year',$year);
		//Kiem tra ho so da duoc tra hay khong
		if(WFEngine::SendNextUserByObjectId($idHSCV,$wf_nexttransition,$user->ID_U,$wf_nextuser,$wf_name_user,$wf_hanxuly_user)==1){
			$motcuahsModel = new motcua_hosoModel($year);
			$motcuahsModel->updateAfterTraHoSo($idHSCV,$ngay_tra,$luc_tra,$is_khongxuly);
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
	function inputAction()
    {
    	//Enable Layout
    	$this->_helper->layout->enableLayout();
    	//lay id Ho so mot cua
    	$id=(int)$this->_request->getParam('id');    	
    	$error=$this->_request->getParam('error');
    	$wf_id_t=(int)$this->_request->getParam('wf_id_t',0);
    	$type=(int)$this->_request->getParam('type',3);
    	$formData = $this->_request->getPost();   
    	//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom"); 	
		//$year : năm truyền theo.nếu không có mặc định là 2009
    	$year=QLVBDHCommon::getYear();
    	//thiet lap cac bien view
    	$this->view->wf_id_t = $wf_id_t;    	
    	$this->view->type =  $type;
    	$this->view->error=$error;
    	$this->view->year=$year;
    	QLVBDHButton::EnableSave("/motcua/hosomotcua/save");
		QLVBDHButton::EnableBack("/motcua/hosomotcua");
		//QLVBDHButton::EnableHelp("");
		$form = new HoSoMotCuaForm();
		$motcuaModel=new HoSoMotCuaModel($year);
		
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "chỉnh sửa Hồ sơ Một cửa";
    		
     		$motcuas = $motcuaModel->fetchRow('ID_HOSO='.$id); 
     		if($motcuas!=null)    		
               		$form->populate($motcuas->toArray());                
            else  $this->_redirect('/motcua/hosomotcua/input');
            $this->view->form = $form;
            //Save your id loai ho so
            $this->view->id=$id;                     
        }
    	else 
    	{
    		$this->view->title = "Thêm mới Hồ sơ Một cửa";
    		$this->view->form = $form;
    	}
    	//if has error from add,update populate form and display error
    	if($error!=null)
    	{
    		$form->populate($formData); 
    	}
    	else
    	if ($this->_request->isPost() && $error==null && $validFrom==null)
        {
    		$form=$this->view->form;
    		if ($form->isValid($_POST)) 
			{ 
				$this->dispatch('saveAction');
			}
        }
     	//Add button 
    }
    /**
	 * Them moi/cap nhat Ho So mot Cua
	 *
	 */
	function saveAction()
	{
		$formData = $this->_request->getPost();
    	$id=(int)$this->_request->getParam("id",0);
    	$id_hscv=(int)$this->_request->getParam("id_hscv",0);
    	$type=(int)$this->_request->getParam("type",3);
    	$year=(int)$this->_request->getParam("year",2013);
    	$wf_nextuser=(int)$this->_request->getParam("wf_nextuser",0);
    	$hscv=new hosocongviecModel();
    	//lay ngay hien tai
		$today = date("Y-m-d h:m:s");		
    	$id_hscv = $hscv->CreateHSCV($formData["TRICHYEU"],1,$type,"2009-12-12","2009-12-13",Zend_Registry::get('auth')->getIdentity()->ID_U,$this->view->parameter["wf_nextuser"]);
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
	                    'ID_LOAIHOSO' 		=> $formData['ID_LOAIHOSO'],
	                    'TRICHYEU' 		=> $formData['TRICHYEU'],
	                    'ID_HSCV' 		=> (trim($formData['idhscv']) == ""?$id_hscv:$formData['idhscv']),
	                	'NGUOINHAN' 	=> $wf_nextuser,
	                	'MAHOSO' 		=> $formData['MAHOSO'],
	                	'TENTOCHUCCANHAN' 		=> $formData['TENTOCHUCCANHAN'],
	                	'DIACHI' 	=> $formData['DIACHI'] ,
	                	'NHAN_LUC'	=>(trim($formData['NHAN_LUC']) == ""?null:$formData['NHAN_LUC']),
	                	'NHAN_NGAY'=> (trim($formData['NHAN_NGAY']) == ""?null:$formData['NHAN_NGAY']),
	                	'LEPHI'		=> (trim($formData['LEPHI']) == ""?null:$formData['LEPHI']),
	                	'TRANGTHAI' 	=> '1',
	                	'NGAYNHAN' 	=> $today,
	                	'CHUTHICH' 	=> $formData['CHUTHICH'],	 	 	                    
	                	'EMAIL' 	=> $formData['EMAIL'],	 
	                	'DIENTHOAI' 	=> $formData['DIENTHOAI'],	                	
	                );
		        $motcuamodel = new HoSoMotCuaModel($year);		                	                
		                      	                
		        if($id>0)	                
		        {
		        	$where="ID_HOSO=".$id;
		        	$motcuamodel->update($data,$where);	        	
		        }
		        else 
		        {
		        	$motcuamodel->insert($data);	        	
		        }
		        //Cập nhật lại trang thái cho Ho so cong viec
		        if($id_hscv>0)
            	{
            		try 
            		{
                		$hscvData = array(
	                    'TRANGTHAI' 		=> '2',
	                		 
		                );
	                    $where = 'ID_HSCV = ' . $id_hscv;	
	                    $hscvModel=new hosocongviecModel($year);                   
	                    $hscvModel->update($hscvData, $where);
            		}
            		catch(Exception $mar){echo $mar;}
                }
		        $this->_redirect('/motcua/hosomotcua/');		      
	        	
			}
			catch(Exception $e2)
			{
				$messageError="Có lỗi xảy ra khi thêm mới/update dữ liệu".$e2;
				$this->_request->setParam('error',$messageError);
				$this->_request->setParams($formData);
				$this->dispatch('inputAction');			
			}
    	}
    	else 
    	$this->_redirect('/vbdi/banhanh/input');
		
       		
	}
	/**
     * delete action
     *
     */
    function deleteAction()
    {
    	$this->view->title = "Xóa Hồ sơ một cửa";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/mocua/hosomotcua/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
    	if($this->_request->isPost())
		{
			$idarray = $this->_request->getParam('DEL');
			var_dump($idarray);
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
						$delLoai = new HoSoMotCuaModel();
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
					$this->_redirect('/motcua/hosomotcua/');				
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
	
}

?>
