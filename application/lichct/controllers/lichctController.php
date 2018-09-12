<?php

/**
 * lichctController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'lichct/models/calendarpersonaldetailModel.php';
require_once 'lichct/models/calendarpersonalModel.php';
require_once 'lichct/models/calendardepartmentdetailModel.php';
require_once 'lichct/models/calendardepartmentModel.php';
require_once 'lichct/models/calendarcorporationdetailModel.php';
require_once 'lichct/models/calendarcorporationModel.php';
require_once 'qtht/models/BussinessDateModel.php';
require_once 'auth/models/ResourceUserModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';

class Lichct_lichctController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function init(){
		global $auth;
		$user = $auth->getIdentity();
		$type = $this->getRequest()->getParam("type");
		if($this->getRequest()->getParam("action") == "day"){
			if($type=="department"){
				$actid = ResourceUserModel::getActionByUrl("lichct","lichct","editlichphong");
				$haveadd = ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0]);
			}
			if($type=="corporation"){
				$actid = ResourceUserModel::getActionByUrl("lichct","lichct","editlichcoquan");
				$haveadd = ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0]);
			}
			if($type=="personal"){
				$haveadd=true;
			}
			if($haveadd){
				QLVBDHButton::AddButton("Thêm mới","","AddNewButton","AddCalendar(\"".strtotime(date("Y-m-d"))."\",\"07:30\")");
			}
			if($type!="corporation"){
				QLVBDHButton::AddButton("Xem lịch tuần","","ViewWeekButton","ViewWeek();");
			}
			QLVBDHButton::AddButton("Theo ngày","/lichct/lichct/day/type/$type","Day","");
			QLVBDHButton::AddButton("Theo tuần","/lichct/lichct/day/code/week/type/$type","Week","");
		}else if($this->getRequest()->getParam("action")=="input"){
			QLVBDHButton::EnableSave("#");
			QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
			//QLVBDHButton::EnableHelp("");
		}
	}
	public function indexAction() {
		// TODO Auto-generated lichctController::indexAction() default action
		$this->view->title = "Lịch công tác cá nhân";
		//$this->view->subtitle = "cá nhân";
	}
	public function todayAction() {
		// TODO Auto-generated lichctController::indexAction() default action
		$this->view->title = "Lịch công tác hôm nay";
		//$this->view->subtitle = "Hôm nay";
	}
	public function dayAction() {
		// TODO Auto-generated lichctController::indexAction() default action
		global $auth;
		
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		
		//Khoi tao parameter
		$code = $param['code'];
		if($code=="")$code="day";
		$type = $param['type'];
		if($type=="")$type="personal";
		$date = $param['DATE'];
		if($type=="corporation"){
			$actid = ResourceUserModel::getActionByUrl("lichct","lichct","editlichcoquan");
			if(!ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
				$this->_redirect("/lichct/lichct/listweek");
			}
		}
		if($date!=""){
			$fromdate = implode("-",array_reverse(explode("/",$date."/".QLVBDHCommon::getYear())));
		}else{
			$fromdate = $param['fromdate'];
		}
		
		if($fromdate!=""){
			$date = explode("-",$fromdate);
			$date = $date[2]."/".$date[1];
		}else{
			$d = getdate();
			$fromdate = $d['year']."-".$d['mon']."-".$d['mday'];
			$date = $d['mday']."/".$d['mon'];
		}
		$fdate = strtotime($fromdate);
		$week = date("YW", $fdate);
		$this->view->week = $week;
		//echo date("Y-m-d",$fdate);
		if($code=='week'){
			$d = getdate($fdate);
			if($d['wday']==0)$d['wday']=7;
			$fdate = $fdate - ($d['wday']-1)*86400;
		}
		
		//tinh toan ngay tren navigator
		//ngay dau thang
		$d = getdate($fdate-1);
		$this->view->preweek = $d['mday']."/".$d['mon'];
		$d = getdate(strtotime($fromdate)-1);
		$this->view->predate = $d['mday']."/".$d['mon'];
		$d = getdate(strtotime($fromdate)+86401);
		$this->view->nextdate = $d['mday']."/".$d['mon'];
		$d = getdate($fdate+86400*7+1);
		$this->view->nextweek = $d['mday']."/".$d['mon'];
		
		//khởi tạo object
		if($type=="personal"){
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			if($param['ID_U']>0){
				$idowner = $param['ID_U'];
			}else{
				$idowner = $user->ID_U;
			}
		}else if($type=="department"){
			$cpd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_DEP;
		}else if($type=="corporation"){
			$cpd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
		}
		$this->view->data = array();
		$this->view->date = array();
		if($code=='week'){
			
			$this->view->rangedate = $cpd->SelectAllRange($idowner,date("Y-m-d",$fdate),date("Y-m-d",$fdate+(518400)));
			for($i=0;$i<7;$i++){
				$temp = array();
				$temp1 = array();
				$fromdate = getdate($fdate);
				$fromdate = $fromdate['year']."-".$fromdate['mon']."-".$fromdate['mday'];
				$temp = $cpd->SelectAllInDate($idowner,$fromdate);
				foreach($temp as $itemtemp){
					$begintime = date("Hi",strtotime($itemtemp['BEGINTIME']));
					$endtime = date("Hi",strtotime($itemtemp['ENDTIME']));
					if($begintime < "0730"){
						$this->view->rangedate[] = $itemtemp;
					}else if($endtime > "1730"){
						$this->view->rangedate[] = $itemtemp;
					}else{
						$temp1[] = $itemtemp;
					}
				}
				$this->view->data[] = $temp1;
				$this->view->date[] = $fdate;
				$fdate += 86400;
			}
			if($type=="personal"){
				$this->view->title = "Lịch công tác cá nhân (theo tuần)";
			}else if($type=="department"){
				$this->view->title = "Lịch công tác phòng (theo tuần)";
			}else if($type=="corporation"){
				$this->view->title = "Tổng hợp lịch cơ quan (theo tuần)";
			}
			//$this->view->subtitle = "Theo tuần";
		}else{
			$this->view->rangedate = $cpd->SelectAllRange($idowner,date("Y-m-d",$fdate),date("Y-m-d",$fdate));
			$temp = array();
			$temp1 = array();
			$temp = $cpd->SelectAllInDate($idowner,$fromdate);
			foreach($temp as $itemtemp){
				$begintime = date("Hi",strtotime($itemtemp['BEGINTIME']));
				$endtime = date("Hi",strtotime($itemtemp['ENDTIME']));
				if($begintime < "0729"){
					$this->view->rangedate[] = $itemtemp;
				}else if($endtime > "1730"){
					$this->view->rangedate[] = $itemtemp;
				}else{
					$temp1[] = $itemtemp;
				}
			}
			$this->view->data[] = $temp1;
			$this->view->date[] = $fdate;
			if($type=="personal"){
				$this->view->title = "Lịch công tác cá nhân (theo ngày)";
			}else if($type=="department"){
				$this->view->title = "Lịch công tác phòng (theo ngày)";
			}else if($type=="corporation"){
				$this->view->title = "Tổng hợp lịch cơ quan (theo ngày)";
			}
			//$this->view->subtitle = "Theo ngày";
		}
		$this->view->DATE = $date;
		$this->view->code = $code;
		$this->view->type = $type;
		$this->view->ID_U_CURRENT = $user->ID_U;
		$this->view->user = $user;
		$actid = ResourceUserModel::getActionByUrl("lichct","lichct","phonggopycanhan");
		
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			//echo $actid[0];
			$this->view->ISLEADER = 1;
			$this->view->ID_DEP_CURRENT = $user->ID_DEP;
		}
		$actid = ResourceUserModel::getActionByUrl("lichct","lichct","coquangopycanhan");
		
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			//echo $actid[0];
			$this->view->ISLEADER = 1;
			$this->view->ID_DEP_CURRENT = 1;
		}
		//echo "aaa".$user->DEPLEADER;
		$this->view->idowner = $idowner;
	}
	public function monthAction() {
	    // TODO Auto-generated lichctController::indexAction() default action		
		$param = $this->getRequest()->getParams();
	    $curmonth = $param['curmonth'];
		if($curmonth == 0){
			$d = getdate();
			$curmonth = $d['mon'];
		}else{
			$this->view->refresh=true;
		}		
		$fromdate = mktime(0,0,0,$curmonth,1,QLVBDHCommon::getYear());
		$fromdate = getdate($fromdate);
		$fromdate = mktime(0,0,0,$curmonth,1-$fromdate['wday']+1,QLVBDHCommon::getYear());
		$fromdate = getdate($fromdate);
		$this->view->fromdate = $fromdate;
		$this->view->curmonth = $curmonth;	
		
		$this->view->title = "Lịch công tác (theo tháng)";
		//$this->view->subtitle = "Theo tháng";
	}
	public function weekAction() {
		// TODO Auto-generated lichctController::indexAction() default action
		$this->view->title = "Lịch công tác (theo tháng)";
		//$this->view->subtitle = "Theo tháng";
	}
	public function allAction() {
		// TODO Auto-generated lichctController::indexAction() default action
		$this->view->title = "Lịch công tác (tất cả)";
		//$this->view->subtitle = "Tất cả";
	}
	public function saveAction(){
		global $auth;
	
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if($param['type']=="personal"){
			$cp = new calendarpersonalModel(QLVBDHCommon::getYear());
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_U;
			$ID_CD = "ID_CPD";
			$ID_C = "ID_CP";
			$ID = "ID_U";
		}else if($param['type']=="department"){
			$cp = new calendardepartmentModel(QLVBDHCommon::getYear());
			$cpd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_DEP;
			$ID_CD = "ID_CDD";
			$ID_C = "ID_CD";
			$ID = "ID_DEP";
		}else if($param['type']=="corporation"){
			$cp = new calendarcorporationModel(QLVBDHCommon::getYear());
			$cpd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			$ID_CD = "ID_CCD";
			$ID_C = "ID_CC";
			$ID = "ISENABLE";
			$idowner = 1;
		}
		$day = $param['day'];
		$code = $param['code'];
		$fromday = $param['BEGINDAY'];
		$today = $param['ENDDAY'];
		$fromday = implode("-",array_reverse(explode("/",$fromday)))." ".$param['BEGINTIME'];
		$today = implode("-",array_reverse(explode("/",$today)))." ".$param['ENDTIME'];
		try{
			if($param['id']){
				$cpd->update(array("BEGINTIME"=>$fromday,"ENDTIME"=>$today,"CONTENT"=>$param['CONTENT'],"DIADIEM"=>$param['DIADIEM'],"THANHPHAN"=>$param['THANHPHAN']),"$ID_CD=".(int)$param['id']);
			}else{
				$idcp = $cp->insert(array("$ID"=>$idowner,"DATE_CREATE"=>$fromday));
				$idcpd = $cpd->insert(array("$ID_C"=>$idcp,"BEGINTIME"=>$fromday,"ENDTIME"=>$today,"CONTENT"=>$param['CONTENT'],"DIADIEM"=>$param['DIADIEM'],"THANHPHAN"=>$param['THANHPHAN']));
				$param['id'] = $idcpd;
				//echo $param['UPTODEP'];exit;
				if($param['UPTODEP']==1){
					Lichct_lichctController::uptodep($param);
				}
			}
		}catch(Exception $ex){

		}
		$this->_redirect("/lichct/lichct/day/fromdate/".implode("-",array_reverse(explode("/",$day)))."/code/".$code."/type/".$param['type']);
	}
	public function deleteAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if($param['type']=="personal"){
			$cp = new calendarpersonalModel(QLVBDHCommon::getYear());
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_U;
			$ID_CD = "ID_CPD";
			$ID_C = "ID_CP";
			$ID = "ID_U";
			$current = $cpd->find($param["id"])->current();
			$currentcp = $cp->fetchAll("$ID=".$idowner." AND $ID_C=".$current->ID_CP);
			$currentid = $currentcp->current()->ID_CP;
		}else if($param['type']=="department"){
			$cp = new calendardepartmentModel(QLVBDHCommon::getYear());
			$cpd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_DEP;
			$ID_CD = "ID_CDD";
			$ID_C = "ID_CD";
			$ID = "ID_DEP";
			$current = $cpd->find($param["id"])->current();
			$currentcp = $cp->fetchAll("$ID=".$idowner." AND $ID_C=".$current->ID_CD);
			$currentid = $currentcp->current()->ID_CD;
		}else if($param['type']=="corporation"){
			$cp = new calendarcorporationModel(QLVBDHCommon::getYear());
			$cpd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			$ID_CD = "ID_CCD";
			$ID_C = "ID_CC";
			$current = $cpd->find($param["id"])->current();
			$currentcp = $cp->fetchAll("$ID_C=".$current->ID_CC);
			$currentid = $currentcp->current()->ID_CC;
		}
		try{
			$cpd->delete("$ID_CD=".(int)$param["id"]);
			$cp->delete("$ID_C=".$currentid);
		}catch(Exception $ex){
			echo $ex->__toString();
		}
		exit;
	}
	public function totalAction(){
		
	}
	public function updateAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$type = $param['type'];
		$begintime = $param['begintime'];
		$endtime = $param['endtime'];
		$date = substr($param['date'],0,4)."-".substr($param['date'],4,2)."-".substr($param['date'],6,2);
		$id = substr($param['id'],4);
		if($type=="personal"){
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_U;
		}else if($type=="department"){
			$cpd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_DEP;
		}else if($type=="corporation"){
			$cpd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			$idowner = 1;
		}
		$cpd->UpdateInDay($idowner,$id,$begintime,$endtime,$date);
		exit;
	}
	public function inputAction(){
		
		$param = $this->getRequest()->getParams();
		$day = $param['day'];
		$time = $param['time'];
		$this->view->day = date("Y-m-d",$day);
		$this->view->code = $param['code'];
		$this->view->type = $param['type'];
		if($param['id']>0){
			if($this->view->type=="personal"){
				$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			}else if($this->view->type=="department"){
				$cpd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			}else if($this->view->type=="corporation"){
				$cpd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			}
			$this->view->title = "Cập nhật lịch công tác";
			//$this->view->subtitle = "Cập nhật";
			$this->view->data = $cpd->find($param['id'])->current();
			$this->view->BEGINDAY = QLVBDHCommon::MysqlDateToVnDate($this->view->data->BEGINTIME);
			$this->view->ENDDAY = QLVBDHCommon::MysqlDateToVnDate($this->view->data->ENDTIME);
			$this->view->BEGINTIME = substr($this->view->data->BEGINTIME,11,5);
			$this->view->ENDTIME = substr($this->view->data->ENDTIME,11,5);
			$this->view->id = $param['id'];
		}else{
			$this->view->title = "Theo mới lịch công tác";
			//$this->view->subtitle = "Thêm mới";
			$BEGINTIME = getdate($day);
			$this->view->BEGINDAY = date("d/m/Y",$day);
			$this->view->ENDDAY = date("d/m/Y",$day);
			$this->view->BEGINTIME = $time;
			$this->view->ENDTIME = $time;
		}
	}
	static function uptodep($param){
		global $auth;
		$user = $auth->getIdentity();
		$id = $param['id'];
		$type = $param['type'];
		
		if($type=="personal"){
			$cdd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$cd = new calendardepartmentModel(QLVBDHCommon::getYear());
			$cp = new calendarpersonalModel(QLVBDHCommon::getYear());
			try{
				$current = $cpd->FindById($id,$user->ID_U);
				//check da co noi dung
				//echo $cdd->CheckExist($current['CONTENT'],$current['BEGINTIME'],$current['ENDTIME'],$user->ID_DEP);
				$idcdd = $cdd->CheckExist($current['CONTENT'],$current['BEGINTIME'],$current['ENDTIME'],$user->ID_DEP);
				if($idcdd==0){
					$idcd = $cd->insert(array("ID_DEP"=>$user->ID_DEP,"ID_U_UP"=>$user->ID_U,"DATE_CREATE"=>date("Y-m-d H:i:s"),"ID_CP"=>$current['ID_CP']));
					$cdd->insert(array("ID_CD"=>$idcd,"BEGINTIME"=>$current['BEGINTIME'],"ENDTIME"=>$current['ENDTIME'],"CONTENT"=>$current['CONTENT'],"DIADIEM"=>$current['DIADIEM'],"THANHPHAN"=>$current['THANHPHAN']));
					$cp->update(array("IS_DEP"=>1),"ID_CP = ".$current['ID_CP']);
				}else{
					$existcdd = $cdd->find($idcdd)->current();
					$cdd->update(array("THANHPHAN"=>$existcdd->THANHPHAN."; ".$current['THANHPHAN']),"ID_CDD=".$idcdd);
					$cp->update(array("IS_DEP"=>1),"ID_CP = ".$current['ID_CP']);
				}
			}catch(Exception $ex){
				echo $ex->__toString();
			}
		}else if($type=="department"){
			$cdd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$ccd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			$cd = new calendardepartmentModel(QLVBDHCommon::getYear());
			$cc = new calendarcorporationModel(QLVBDHCommon::getYear());
			try{
				$current = $cdd->FindById($id,$user->ID_DEP);
				//var_dump($current);
				//check da co noi dung
				//echo $cdd->CheckExist($current['CONTENT'],$current['BEGINTIME'],$current['ENDTIME'],$user->ID_DEP);
				$idccd = $ccd->CheckExist($current['CONTENT'],$current['BEGINTIME'],$current['ENDTIME']);
				if($idccd==0){
					$idcc = $cc->insert(array("ID_DEP"=>$user->ID_DEP,"DATE_CREATE"=>date("Y-m-d H:i:s"),"ID_CD"=>$current['ID_CD']));
					$ccd->insert(array("ID_CC"=>$idcc,"BEGINTIME"=>$current['BEGINTIME'],"ENDTIME"=>$current['ENDTIME'],"CONTENT"=>$current['CONTENT'],"DIADIEM"=>$current['DIADIEM'],"THANHPHAN"=>$current['THANHPHAN']));
					$cd->update(array("IS_COQUAN"=>1),"ID_CD = ".$current['ID_CD']);
				}else{
					$existccd = $ccd->find($idccd)->current();
					$ccd->update(array("THANHPHAN"=>$existccd->THANHPHAN."; ".$current['THANHPHAN']),"ID_CCD=".$idccd);
					$cd->update(array("IS_COQUAN"=>1),"ID_CD = ".$current['ID_CD']);
				}
			}catch(Exception $ex){
				echo $ex->__toString();
			}
		}
	}
	function uptodepAction(){
		$param = $this->getRequest()->getParams();
		Lichct_lichctController::uptodep($param);
		exit;
	}
	function noteAction(){
		global $auth;
		
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$type = $param['type'];
		$gopy = $param['gopy'];
		//echo $gopy;
		$id = $param['id'];
		if($type=="personal"){
			$cp = new calendarpersonalModel(QLVBDHCommon::getYear());
			$cpd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$currentcpd = $cpd->find($id)->current();
			$cp->update(array("GOP_Y"=>$gopy),"ID_CP=".$currentcpd->ID_CP);
		}else if($type=="department"){

		}
		exit;
	}
	function printAction(){
		global $auth;
		$this->_helper->layout->disableLayout();
		$param = $this->getRequest()->getParams();
		$user = $auth->getIdentity();
		if($param['excel']=="yes"){
			header("Cache-Control: no-stor,no-cache,must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0", false);
			header("Cache-control: private");
			header("Content-Type: application/force-download");
			header("Content-Disposition: inline; attachment;filename=lichcongtactuan.xls");
			header("Content-Transfer-Encoding: binary");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		$date = $param['date'];
		if($date!=""){
			$fromdate = implode("-",array_reverse(explode("/",$date."/".QLVBDHCommon::getYear())));
		}
		if($fromdate!=""){
			
		}else{
			$d = getdate();
			$fromdate = $d['year']."-".$d['mon']."-".$d['mday'];
		}
		$fdate = strtotime($fromdate);
		$week = date("YW", $fdate);
		$this->view->week = $week;
		$d = getdate($fdate);
		if($d['wday']==0)$d['wday']=7;
		$fdate = $fdate - ($d['wday']-1)*86400;
		$this->view->data = array();
		$this->view->date = array();
		$type = $param['type'];
		$this->view->type = $type;
		if($type=="personal"){
			$ccd = new calendarpersonaldetailModel(QLVBDHCommon::getYear());
			$cc = new calendarpersonalModel(QLVBDHCommon::getYear());
			if($param['ID_U']>0){
				$idowner = $param['ID_U'];
			}else{
				$idowner = $user->ID_U;
			}
		}else if($type=="department"){
			$ccd = new calendardepartmentdetailModel(QLVBDHCommon::getYear());
			$cc = new calendardepartmentModel(QLVBDHCommon::getYear());
			$idowner = $user->ID_DEP;
		}else if($type=="corporation"){
			$ccd = new calendarcorporationdetailModel(QLVBDHCommon::getYear());
			$cc = new calendarcorporationModel(QLVBDHCommon::getYear());
			$idowner = 1;
		}
		$this->view->rangedate = $ccd->SelectAllRange($idowner,date("Y-m-d",$fdate),date("Y-m-d",$fdate+(518400)));
		for($i=0;$i<7;$i++){
			$fromdate = getdate($fdate);
			$fromdate = $fromdate['year']."-".$fromdate['mon']."-".$fromdate['mday'];
			$this->view->data[] = $ccd->SelectAllInDate($idowner,$fromdate);
			$this->view->date[] = $fdate;
			$fdate += 86400;
		}
		$this->view->idowner = $idowner;
		if($param['excel']=="yes"){
			$this->renderScript("lichct/printexcel.phtml");	
		}else if($param['export']=="yes" && $type=="corporation"){
			$config = Zend_Registry::get('config');
			require_once 'common/plugin/printtotextlct.php';
			$f = fopen($config->file->root_dir."/lichct/$week".".html","w");
			fwrite($f,$html);
			fclose($f);
			exit;
		}
	}
	function viewcqAction(){
		$param = $this->getRequest()->getParams();
		$date = $param['date'];
		if($date!=""){
			$fromdate = implode("-",array_reverse(explode("/",$date."/".QLVBDHCommon::getYear())));
		}
		if($fromdate!=""){
			
		}else{
			$d = getdate();
			$fromdate = $d['year']."-".$d['mon']."-".$d['mday'];
		}
		$fdate = strtotime($fromdate);
		$week = date("YW", $fdate);
		$config = Zend_Registry::get('config');
		if(file_exists($config->file->root_dir."/lichct/$week".".html")){
			$f = fopen($config->file->root_dir."/lichct/$week".".html","r");
			$content = fread($f,filesize($config->file->root_dir."/lichct/$week".".html"));
			echo $content;
		}else{
			
		}
		exit;
	}
	function listweekAction(){
		$this->view->title = "Lịch công tác cơ quan";
		//$this->view->subtitle = "Cơ quan";
		$param = $this->getRequest()->getParams();
		$week = $param['week'];
		$this->view->week = $week;
		if($week==""){
			$d = getdate();
			$fromdate = $d['year']."-".$d['mon']."-".$d['mday'];
			$fdate = strtotime($fromdate);
			$this->view->week = date("W", $fdate);
			$week = date("YW", $fdate);
		}else{
			$week = QLVBDHCommon::getYear().$week;
		}
		
		$config = Zend_Registry::get('config');
		if(file_exists($config->file->root_dir."/lichct/$week".".html")){
			$f = fopen($config->file->root_dir."/lichct/$week".".html","r");
			$this->view->content = fread($f,filesize($config->file->root_dir."/lichct/$week".".html"));
		}
		$filebaocao = array();
		if (is_dir($config->file->root_dir."/lichct/")) {
		    if ($dh = opendir($config->file->root_dir."/lichct/")) {
		        while (($file = readdir($dh)) !== false) {
		            if(substr($file,0,4)==QLVBDHCommon::getYear()){
		            	$filebaocao[] = substr($file,4,2);
		            }
		        }
		        closedir($dh);
		    }
		}
		$this->view->filebaocao = $filebaocao;
	}
}
