<?php

/**
 * HscvController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/VanBanDiModel.php';

class Hscv_VanBanDiController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated HscvController::indexAction() default action
		$hscv = new VanBanDiModel();
		for($i=0;$i<100;$i++){
			$hscv->CreateHSCV("Ronaldo ronaldo real marid",1,2,"2009-12-12","2009-12-13",1,2);
		}
	}
	public function listAction(){
		
		global $auth;
		$user = $auth->getIdentity();
		
		//L?y parameter
		$param = $this->getRequest()->getParams();
		$config = Zend_Registry::get('config');
		
		//tinh ch?nh param
		$ngaybd = $param['NGAY_BD'];
		$ngaybd = implode("-",array_reverse(explode("/",$ngaybd)));
		$ngaykt = $param['NGAY_KT'];
		$ngaykt = implode("-",array_reverse(explode("/",$ngaykt)));
		$arrngaybd = explode("-",$ngaybd);
		$realyear = $arrngaybd[0];
		if($realyear<=0){
			$d = getdate();
			$realyear = $d['year'];
		}
		$id_thumuc = $param["id_thumuc"];
		$ID_LOAIHSCV = $param['ID_LOAIHSCV'];
		$NAME = $param['NAMECV'];
		$page = $param['page'];
		$limit = $param['limit1'];
		$isAjax = $param['isAjax'];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$TRANGTHAI = $param['TRANGTHAI'];
		
		$parameter = array(
			"ID_THUMUC"=>$id_thumuc,
			"ID_LOAIHSCV"=>$ID_LOAIHSCV,
			"NGAY_BD"=>$ngaybd,
			"NGAY_KT"=>$ngaykt,
			"TRANGTHAI"=>$TRANGTHAI,
			"ID_U"=>$user->ID_U,
			"NAME"=>$NAME
		);
		
		//T?o ??i t??ng
		$hscv = new VanBanDiModel();
		
		$hscvcount = $hscv->Count($parameter);
		
		//L?y d? li?u
		
		$this->view->data = $hscv->SelectAll($parameter,($page-1)*$limit,$limit,"");
		$this->view->realyear = $realyear;
		$this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
		$this->view->NAME = $NAME;
		$this->view->NGAY_BD = $param['NGAY_BD'];;
		$this->view->NGAY_KT = $param['NGAY_KT'];;
		$this->view->NAME = $NAME;
		$this->view->TRANGTHAI = $TRANGTHAI;
		$this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
		$this->view->id_thumuc = $id_thumuc;
		
		//Create button
		$createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
		if(count($createarr)>0){
			QLVBDHButton::AddButton($createarr["NAME"],"","AddNewButton","CreateButtonClick(\"".$createarr["LINK"]."/type/$ID_LOAIHSCV/wf_id_t/".$createarr["ID_T"]."\")");
		}
		
		//page
		$this->view->title = "Hồ sơ công việc";
		//$this->view->subtitle = "danh s�ch";
		$this->view->page = $page;
		$this->view->limit = $limit;
		
		if($isAjax){
			$this->_helper->layout->disableLayout();
			$this->view->showPage = QLVBDHCommon::PaginatorAjax($hscvcount,10,$limit,"frm",$page,"/hscv/hscv/list/id_thumuc/".$id_thumuc,"contenthscv") ;
		}else{
			$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/hscv/hscv/list/id_thumuc/".$id_thumuc) ;
		}
	}
	function inputbutpheAction(){
		$this->_helper->layout->disableLayout();
		
		global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
	}
	function inputAction()
	{
		
	}
}
