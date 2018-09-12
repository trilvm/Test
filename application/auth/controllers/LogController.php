<?php

/**
 * LogController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'auth/models/LogModel.php';

class Auth_LogController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ActivityPoolController::indexAction() default action
		
		//Lấy parameter
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		$filter_object = $parameter["filter_object"];
		$fromdate = $parameter["fromdate"];
		$todate = $parameter["todate"];
		
		//Tinh chỉnh parameter
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		if($filter_object==0 || $filter_object=="")$filter_object=0;
		
		//New các model
		$this->log = new LogModel();
		
		//Khởi động các biến cho các model
		$this->log->_search = $search;
		$this->log->_type = $filter_object;
		if($fromdate!=""){
			$this->log->_fromdate =implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:01";
		}else{
			$this->log->_fromdate =QLVBDHCommon::getYear()."-1-1"." 00:00:01";
		}
		if($todate!=""){
			$this->log->_todate = implode("-",array_reverse(explode("/",$todate)))." 23:59:59";
		}
		
		//Lấy dữ liệu chính
		try{
			$rowcount = $this->log->Count();
			$this->view->data = $this->log->SelectAll(($page-1)*$limit,$limit,"ID_LOG DESC");
		}catch(Exception $ex){
			$this->log->_fromdate = "";
			$this->log->_todate = "";
			$fromdate = "";
			$todate = "";
			$rowcount = $this->log->Count();
			$this->view->data = $this->log->SelectAll(($page-1)*$limit,$limit,"ID_LOG DESC");
		}
		
		//Set biến cho view
		$this->view->title = "Theo dõi hệ thống";
		$this->view->subtitle = "Log";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object = $filter_object;
		$this->view->fromdate = $fromdate;
		$this->view->todate = $todate;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
		QLVBDHButton::AddButton("Xoá","","DeleteButton","DeleteButtonClick();");
		QLVBDHButton::AddButton("Xóa tất cả","","DeleteButton","DeleteAllButtonClick();");
	}
	
	function deleteAction(){
		$params = $this->_request->getParams();
		//var_dump($params) ; exit;
		$id_logs = $params["DEL"];
		foreach($id_logs as $id_log)
			LogModel::deleteOne($id_log);
		$this->_redirect('/auth/log');
	}

	function deleteallAction(){
		LogModel::deleteAll();
		$this->_redirect('/auth/log');
	}
}
