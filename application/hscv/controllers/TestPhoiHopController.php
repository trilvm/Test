<?php
/*
 * Phoi hop controller
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'hscv/models/PhoiHopModel.php';
require_once 'hscv/models/HoSoCongViecModel.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
/**
 * PhoiHopController dung de quan tri so nguoi phoi hop voi mot HSCV cu the
 * 
 * @author truongvc
 * @version 1.0
 */
class Hscv_TestPhoiHopController extends Zend_Controller_Action 
{
/*
     * This is index action for module /hscv/phoihop/
     * @return void
     */
	function indexAction()
    {
    	//Get parameters
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		
	
		//Refinde parameters
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		
		//Define model
		$this->hscv = new PhoiHopModel();
		
		//assign value for search action
		$this->hscv->_search = $search;
		
		//Get data for view
		$rowcount = $this->hscv->Count();
		$this->view->data = $this->hscv->SelectAllHscv(($page-1)*$limit,$limit,"ID_HSCV");
		//View detail
		$this->view->title = "Danh sách hồ sơ công việc";
		$this->view->subtitle = "Phối hợp";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListPhoihop",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/hscv/phoihop/delete");
		QLVBDHButton::EnableAddNew("/hscv/phoihop/input");
		
		
	} 
}
