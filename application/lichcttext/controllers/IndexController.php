<?php

/**
 * IndexController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'lichcttext/models/lct.php';
require_once 'lichcttext/models/TableModel.php';
include_once 'qtht/models/DepartmentsModel.php';
include_once 'qtht/models/BussinessDateModel.php';
require_once 'qtht/models/UsersModel.php';
include_once 'auth/models/ResourceUserModel.php';
require_once 'vbdi/models/vbdi_dongluanchuyenModel.php';


class Lichcttext_IndexController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated IndexController::indexAction() default action
		global $auth;
		
		$user = $auth->getIdentity();
		
		//param
		$param = $this->getRequest()->getParams();
		$idu = $param['ID_U'];
		$iddep = $param['ID_DEP'];
		$week = $param['week'];
		if($idu==0){
			$idu = $user->ID_U;
		}
		
		//Lay ngay hien tai
		$d = $param['BEGINDATE'];
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		
		$d1 = getdate($t-86400*7-1);
		$this->view->preweek = $d1['mday']."/".$d1['mon'];
		$d1 = getdate($t+86400*7+1);
		$this->view->nextweek = $d1['mday']."/".$d1['mon'];
		
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		//lock
		$this->view->lock = 1;
		$actid = ResourceUserModel::getActionByUrl("lichcttext","index","saved");
		if($user->ID_U == $idu){
			$this->view->lock = 0;
		}
		$this->view->isuptocq = 0;
		$actid = ResourceUserModel::getActionByUrl('lichcttext','index','uptocq');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			if($this->view->lock==0){
				$this->view->isuptocq = 1;
			}
		}
		$this->view->autoaddcq = 0;
		$actid = ResourceUserModel::getActionByUrl('lichcttext','index','autoaddcq');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			if($this->view->lock==0){
				$this->view->autoaddcq = 1;
			}
		}
		//echo "aaa".$this->view->autoaddcq;
		//echo $this->view->isuptocq;
		//Lay lich
		$data = array();
		for($i=1;$i<=7;$i++){
			$currentdate_str = date("Y-m-d",$begindate);
			$curentdate_arr = getdate($begindate);
			$data[$i-1][0] = array($curentdate_arr['wday'],date("d/m",$begindate),$begindate);//Thứ
			$data[$i-1][1] = lct::getPersonal($currentdate_str,1,$idu);//sang
			$data[$i-1][2] = lct::getPersonal($currentdate_str,2,$idu);//chieu
			$data[$i-1][3] = lct::getPersonal($currentdate_str,3,$idu);//toi
			$begindate += 86400;
		}
		
		//view
		$this->view->title="Lịch công tác cá nhân";
		//$this->view->subtitle="cá nhân";
		$this->view->data = $data;
//                print_r($data);exit;
		$this->view->ID_U = $idu;
		$this->view->user = $user;
		$this->view->ID_DEP = $iddep;
		$this->view->week = date("d/m",$begindate-7*86400);
		$this->view->BEGINDATE = $param['BEGINDATE']==""?date("d/m"):$param['BEGINDATE'];
		if($this->view->lock==0){
			QLVBDHButton::AddButton("Thêm mới","","AddNewButton","Addnew();");
			QLVBDHButton::AddButton("Xóa","","DeleteButton","DeleteAll();");
			QLVBDHButton::EnableSave("/lichcttext/index/savep");
		}
		$this->view->config = Zend_Registry::get('config');
	}
        public function searchAction() {		
		global $auth;
		
		$user = $auth->getIdentity();
		
		//param
		$param = $this->getRequest()->getParams();
		$idu = $param['ID_U'];
		$iddep = $param['ID_DEP'];		
		if($idu==0){
			$idu = $user->ID_U;
		}
                
                if($iddep==0){
			$iddep = $user->ID_DEP;
		}
		
		//view
		$this->view->title="Tìm kiếm lịch công tác";		
		$this->view->ID_U = $idu;
		$this->view->user = $user;
		$this->view->ID_DEP = $iddep;
                // phòng - tuanpp
                //Lay ds phong
		$dep = array();
		QLVBDHCommon::GetTree(&$dep,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);		
		//view
		$this->view->dep = $dep;
                // phòng - tuanpp end				
		$this->view->config = Zend_Registry::get('config');
	}
        public function searchviewAction() {
                global $auth;
		
		$user = $auth->getIdentity();
		
		//param
		$param = $this->getRequest()->getParams();
                $this->_helper->layout->disableLayout();
		$config = Zend_Registry::get("config");
		$fromdate = $param["fromdate"];
			if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
                $LCTTYPE = $param["LCTTYPE"];
                $this->view->LCTTYPE = $LCTTYPE;
                $noidung = $param["noidung"];
                $this->view->noidung = $noidung;
                $TYPE = $param["TYPE"];
                $this->view->type = $TYPE;
                $buoi = $param["buoi"];
                $this->view->buoi = $buoi;
                $id_dep = $param["ID_DEP"];
                if (!$id_dep) $id_dep = $user->ID_DEP;
                $this->view->ID_DEP = $id_dep;
                $id_u = $param["ID_U"];
                if (!$id_u) $id_u = $user->ID_U;
                $this->view->ID_U = $id_u;
                $id_dep2 = $param["ID_DEP2"];
                if (!$id_dep2) $id_dep2 = $user->ID_DEP;
                $this->view->ID_DEP2 = $id_dep2;
                $sorttype = $param["SORTTYPE"];
                $this->view->SORTTYPE = $sorttype;                        
			$this->view->todate = $todate;
			$this->view->fromdate = $fromdate;
                        //tham so phan trang
			$page = $param['page'];
			$limit = $param['limit1'];
			if($limit==0 || $limit=="")$limit=10;
			if($page==0 || $page=="")$page=1;
			$this->view->page = $page;
			$this->view->limit = $limit;
                        $data = lct::SearchData($LCTTYPE, $noidung, $TYPE, $fromdate, $todate, $buoi, $id_dep, $id_u, $id_dep2, $sorttype,($page-1)*$limit,$limit);                        						
			
                        $count = lct::CountSearchData($LCTTYPE, $noidung, $TYPE, $fromdate, $todate, $buoi, $id_dep, $id_u, $id_dep2, $sorttype);
//                        print_r($count);exit;
                        $this->view->data = $data;
                        $this->view->showPage = QLVBDHCommon::Paginator($count,5,$limit,"frm2",$page) ;
//			$this->view->showPage = QLVBDHCommon::PaginatorWithAction($count,10,$limit,"frm",$page,"/lichcttext/index/searchview");
                        
            }
	function indexdAction(){
		// TODO Auto-generated IndexController::indexAction() default action
		global $auth;
		
		$user = $auth->getIdentity();
		//param
		$param = $this->getRequest()->getParams();
		$week = $param['week'];
		$iddep = $param['ID_DEP'];
		if($iddep==0){
			$iddep = $user->ID_DEP;
		}
		//Lay ngay hien tai
		$d = $param['BEGINDATE'];
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		
		$d1 = getdate($t-86400*7-1);
		$this->view->preweek = $d1['mday']."/".$d1['mon'];
		$d1 = getdate($t+86400*7+1);
		$this->view->nextweek = $d1['mday']."/".$d1['mon'];
		
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		//lock
		$this->view->lock = 1;
		$actid = ResourceUserModel::getActionByUrl("lichcttext","index","saved");
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0]) && $user->ID_DEP == $iddep){
			$this->view->lock = 0;
		}
		$this->view->phongaddtocq = 0;
		$actid = ResourceUserModel::getActionByUrl('lichcttext','index','phongaddtocq');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			if($this->view->lock==0){
				$this->view->phongaddtocq = 1;
			}
		}
		//Lay lich
		$data = array();
		for($i=1;$i<=7;$i++){
			$currentdate_str = date("Y-m-d",$begindate);
			$curentdate_arr = getdate($begindate);
			$data[$i-1][0] = array($curentdate_arr['wday'],date("d/m",$begindate),$begindate);//Thứ
			$data[$i-1][1] = lct::getDepartment($currentdate_str,1,$iddep,$this->view->lock);//sang
			$data[$i-1][2] = lct::getDepartment($currentdate_str,2,$iddep,$this->view->lock);//sang
			$data[$i-1][3] = lct::getDepartment($currentdate_str,3,$iddep,$this->view->lock);//sang
			$begindate += 86400;
		}
		
		//Lay ds phong
		$dep = array();
		QLVBDHCommon::GetTree(&$dep,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
		
		//view
		$this->view->dep = $dep;
		$this->view->ID_DEP = $iddep;
		$this->view->user = $user;
		$this->view->title="Lịch công tác phòng";
		//$this->view->subtitle="phòng";
		$this->view->data = $data;
		$this->view->BEGINDATE = $param['BEGINDATE']==""?date("d/m"):$param['BEGINDATE'];

		$this->view->BEFOREWEEK = lct::GetHSCV($begindate);
		$this->view->week = date("d/m",$begindate-7*86400);
		QLVBDHButton::AddButton("Xuất Excel","","Excel","XemLichCoQuan(1);");
		if($this->view->lock==0){
			QLVBDHButton::AddButton("Ban hành","","TongHop","Tonghop();");
			QLVBDHButton::AddButton("Xóa","","DeleteButton","DeleteAll();");
			QLVBDHButton::EnableSave("/lichcttext/index/saved");
		}
		$this->view->config = Zend_Registry::get('config');
		if($param['isexcel'] ==1){
			$this->view->enddate = date("d/m/Y",$begindate-1);	
			$this->view->begindate = date("d/m/Y",$begindate-7*86400);
			$this->view->week = date("YW", $begindate)-1;
			$this->_helper->layout->disableLayout();
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=lichcongtac.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
			$this->renderScript("index/viewlichd.phtml");
		}
	}
	function indexcAction(){
		// TODO Auto-generated IndexController::indexAction() default action
		global $auth;
		
		$user = $auth->getIdentity();
		//param
		$param = $this->getRequest()->getParams();
		$week = $param['week'];
		$iddep = $user->ID_DEP;
		
		//Lay ngay hien tai
		$d = $param['BEGINDATE'];
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		
		$d1 = getdate($t-86400*7-1);
		$this->view->preweek = $d1['mday']."/".$d1['mon'];
		$d1 = getdate($t+86400*7+1);
		$this->view->nextweek = $d1['mday']."/".$d1['mon'];
		
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		//get lich du kien
		$begindatestr = getdate($begindate);
		$begindatestr = $begindatestr['year']."-".$begindatestr['mon']."-".$begindatestr['mday'];
		
		$enddate = $begindate+(6*86400);
		$enddatestr = getdate($enddate);
		$enddatestr = $enddatestr['year']."-".$enddatestr['mon']."-".$enddatestr['mday'];
		
		$this->view->DUKIEN = lct::GetDuKien($begindatestr,$enddatestr);
        $UsersModel = new UsersModel();
        $employeeList = $UsersModel->getAllFullName();
		$this->view->employeeList = $employeeList;
		
		//lock
		$this->view->lock = 1;
		$actid = ResourceUserModel::getActionByUrl("lichcttext","index","savec");
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			$this->view->lock = 0;
		}
		
		//Lay lich
		$data = array();
		for($i=1;$i<=7;$i++){
			$currentdate_str = date("Y-m-d",$begindate);
			$curentdate_arr = getdate($begindate);
			$data[$i-1][0] = array($curentdate_arr['wday'],date("d/m",$begindate),$begindate);//Thứ
			$data[$i-1][1] = lct::getCorporation($currentdate_str,1,0);//$this->view->lock);//sang
			$data[$i-1][2] = lct::getCorporation($currentdate_str,2,0);//$this->view->lock);//sang
			$data[$i-1][3] = lct::getCorporation($currentdate_str,3,0);//$this->view->lock);//sang
			$begindate += 86400;
		}
		
		//view
		$this->view->title="Danh sách đăng ký lịch cơ quan";
		//$this->view->subtitle="cơ quan";
		$this->view->data = $data;
		$this->view->BEGINDATE = $param['BEGINDATE']==""?date("d/m"):$param['BEGINDATE'];
		$this->view->week = date("d/m",$begindate-7*86400);
		QLVBDHButton::AddButton("Xuất Excel","","Excel","XemLichCoQuan(1);");
		QLVBDHButton::AddButton("Xem lịch cơ quan","","ViewCQ","XemLichCoQuan(0);");
		if($this->view->lock==0){
			QLVBDHButton::AddButton("Ban hành","","TongHop","Tonghop();");
			QLVBDHButton::AddButton("Xóa","","DeleteButton","DeleteAll();");
			QLVBDHButton::EnableSave("/lichcttext/index/saved");
		}
        $year = QLVBDHCommon::getYear();
        $dongluanchuyen_vbdi = new vbdi_dongluanchuyenModel($year);
            //$array_chuyendebiet=$dongluanchuyen_vbdi->findAllNguoiNhan($id);
            $array_chuyendebiet = $dongluanchuyen_vbdi->way($year, $id);
            if (count($array_chuyendebiet) > 0) {
                //var_dump($array_chuyendebiet);
                $this->view->data_chuyendebiet = $array_chuyendebiet;
            }
		$this->view->config = Zend_Registry::get('config');
                
                //cuongnc
                //echo '<pre>';
                $this->view->dataCT = lct::getDataCt();
                //exit;
	}
	function savepAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		//var_dump($param);exit;
		lct::updatePersonal($param,$idu,$iddep);
		$this->_redirect("/lichcttext/index?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}
	function savedAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		
		lct::updateDepartment($param,$idu,$iddep,0);
		$this->_redirect("/lichcttext/index/indexd?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}
	function phanphoilichAction(){
        try{
            $param = $this->getRequest()->getParams();
            lct::phanPhoiLCTPersonal($param);
            ajax::ship('phanPhoiResult','1');
            exit;
        }catch( Exception $ex)
        {
            ajax::ship('errorPhanPhoi',$ex->__toString());
            ajax::ship('phanPhoiResult','0');
            exit;
        }
    }
	function savecAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		//var_dump($param);exit;
		//var_dump($param);
		lct::updateCorporation($param,$idu,0);
                //$model = new Lichcttext_TableModel();
                lct::addCtManage($param['CHUTRI']);//lưu chủ trì
		//Lay ngay hien tai
		$d = $param['BEGINDATE'];
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		$begindatestr = getdate($begindate);
		$begindatestr = $begindatestr['year']."-".$begindatestr['mon']."-".$begindatestr['mday'];
		
		$enddate = $begindate+(7*86400)-10;
		$enddatestr = getdate($enddate);
		$enddatestr = $enddatestr['year']."-".$enddatestr['mon']."-".$enddatestr['mday'];
		
		lct::UpdateDuKien($begindatestr,$enddatestr,$param["DUKIEN"]);
		
		$this->_redirect("/lichcttext/index/indexc?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}
	function deletepAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		//lct::updatePersonal($param,$idu,$iddep);
		lct::deletePersonal($param,$idu);
		$this->_redirect("/lichcttext/index?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}
	function deletedAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		
		lct::deleteDepartment($param,$iddep);
		
		exit;
	}
	function deletecAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		
		//param
		$param = $this->getRequest()->getParams();
		
		lct::deleteCorporation($param);
		
		exit;
	}
	function tonghopdAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		
		lct::updateDepartment($param,$idu,$iddep,1);
		$this->_redirect("/lichcttext/index/indexd?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}	
	function tonghopcAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		//param
		$param = $this->getRequest()->getParams();
		
		lct::updateCorporation($param,$idu,1);
		$this->_redirect("/lichcttext/index/indexc?BEGINDATE=".$param['BEGINDATE']);
		exit;
	}	  

	function viewlichcqAction(){
	// TODO Auto-generated IndexController::indexAction() default action
		global $auth;
		$this->_helper->layout->disableLayout();
		$user = $auth->getIdentity();
		
		//param
		$param = $this->getRequest()->getParams();
		$iddep = $user->ID_DEP;
		
		//Lay ngay hien tai
		$d = $param['BEGINDATE'];
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		//get lich du kien
		$begindatestr = getdate($begindate);
		$begindatestr = $begindatestr['year']."-".$begindatestr['mon']."-".$begindatestr['mday'];
		
		$enddate = $begindate+(7*86400)-10;
		$enddatestr = getdate($enddate);
		$enddatestr = $enddatestr['year']."-".$enddatestr['mon']."-".$enddatestr['mday'];
		
		$this->view->DUKIEN = lct::GetDuKien($begindatestr,$enddatestr);
		
		$this->view->lock = 1;
		
		$this->view->week = date("YW", $begindate);
		$this->view->config = Zend_Registry::get('config');
		
		$this->view->begindate = date("d/m/Y",$begindate);

		//Lay lich
		$data = array();
		for($i=1;$i<=7;$i++){
			$currentdate_str = date("Y-m-d",$begindate);
			$curentdate_arr = getdate($begindate);
			$data[$i-1][0] = array($curentdate_arr['wday'],date("d/m",$begindate),$begindate);//Thứ
			$data[$i-1][1] = lct::getCorporation($currentdate_str,1,1);//sang
			$data[$i-1][2] = lct::getCorporation($currentdate_str,2,1);//sang
			$data[$i-1][3] = lct::getCorporation($currentdate_str,3,1);//sang
			$begindate += 86400;
		}
		$this->view->enddate = date("d/m/Y",$begindate-1);
		$this->view->data = $data;
		$this->view->BEGINDATE = $param['BEGINDATE']==""?date("d/m"):$param['BEGINDATE'];
		if($param['isexcel'] ==1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=lichcongtac.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
			$this->renderScript("index/viewlichcqe.phtml");
		}
	}
	function inputpAction(){
		$this->view->title=" Thêm mới Lịch công tác cá nhân";
		//$this->view->subtitle="thêm mới";
		QLVBDHButton::EnableSave("");
		QLVBDHButton::EnableBack("");
	}
	function inputsavepAction(){
		
		global $auth;
		
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$iddep = $user->ID_DEP;
		
		$param = $this->_request->getParams();
		$ngaybd = $param['BEGINDAY'];
		$ngaykt = $param['ENDDAY'];
		$noidung = $param['NOIDUNG'];
		$thanhphan = $param['THANHPHAN'];
		$diadiem = $param['DIADIEM'];
		$buoi = $param['BUOI'];
		$ngaybd = strtotime(implode("-",array_reverse(explode("/",$ngaybd))));
		$ngaykt = strtotime(implode("-",array_reverse(explode("/",$ngaykt))));
		
		$data = Array();
		$data['NOIDUNG'] = Array();
		$data['THANHPHAN'] = Array();
		$data['DIADIEM'] = Array();
		$data['BUOI'] = Array();
		$data['NGAY'] = Array();
		$data['ID_LCT_P'] = Array();
		
		while($ngaybd<=$ngaykt){
			if(!BussinessDateModel::IsNonWorkingDate(getdate($ngaybd))){
				foreach($buoi as $itembuoi){
					$data['NOIDUNG'][] = $noidung;
					$data['THANHPHAN'][] = $thanhphan;
					$data['DIADIEM'][] = $diadiem;
					$data['BUOI'][] = $itembuoi;
					$data['NGAY'][] = $ngaybd;
					$data['ID_LCT_P'][] = "";
				}
			}
			$ngaybd += 86400;
		}
		//var_dump($data);exit;
		lct::updatePersonal($data,$idu,$iddep);
		$this->_redirect("/lichcttext/index?BEGINDATE=".date("d/m",$ngaybd));
		
		exit;
	}
	function alarmAction(){
		global $auth;
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		$param = $this->_request->getParams();
		$this->view->BEGINDATE = $param['BEGINDATE']==""?date("d/m"):$param['BEGINDATE'];
		if($param["NHACNHO"]!="" && $param["NHACNHO_TIME_HOUR"]!="" && $param["code"]=="accept"){
			lct::setAlarm($param,$idu);
		}else{
			lct::disAlarm($param,$idu);
		}
		$this->_redirect("/lichcttext/index?BEGINDATE=".$this->view->BEGINDATE);
		
		exit;
	}
}

