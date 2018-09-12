<?php
/**
 * ActionsController
 * 
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/blacklistModel.php';
class Qtht_blacklistController extends Zend_Controller_Action {
    /**
     * The default action - show the home page
     */
    public function indexAction() {
        // TODO Auto-generated ActionsController::indexAction() default action
        
        // Lấy parameter
        $config = Zend_Registry::get('config');
        $parameter = $this->getRequest()->getParams();
        $limit = $parameter["limit"];
        $page = $parameter["page"];
        $search = $parameter["search"];
       
        // Tinh chỉnh parameter
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;
       
        // Tạo mới model
        $this->_blacklistModel = new blacklistModel();
        
        //Khởi động các biến cho các model
        $this->_blacklistModel->_search = $search;        

        // Lấy dữ liệu chính
        $rowcount = $this->_blacklistModel->Count();
        $this->view->data = $this->_blacklistModel->SelectAll(($page-1)*$limit,$limit,"lasttry");
        
       
        
        // Set biến cho view
        $this->view->title = "Blacklist";
        //$this->view->subtitle = "Danh sách";
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
        
        QLVBDHButton::EnableDelete("/qtht/blacklist/Delete");
    }    
    /**
     * The delete action - delete item
     */
    public function deleteAction(){
        $this->_blacklistModel =  new blacklistModel();      
        $this->view->parameter =  $this->getRequest()->getParams();
        try{
            $this->_blacklistModel->delete("blacklist_id IN (".implode(",",$this->view->parameter["DEL"]).")");
        }catch(Exception $ex){
        }                
        $this->_redirect("/qtht/blacklist");
    }
}
