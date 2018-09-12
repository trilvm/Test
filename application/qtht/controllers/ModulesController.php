<?php
/**
 * ModulesController
 * 
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/ModulesModel.php';
require_once 'config/qtht.php';

class Qtht_ModulesController extends Zend_Controller_Action {
    /**
     * The default action - show the home page
     */
    public function indexAction() {
        // TODO Auto-generated ModulesController::indexAction() default action
        // Lấy parameter
        $config = Zend_Registry::get('config');
        $parameter = $this->getRequest()->getParams();
        $limit = $parameter["limit"];
        $page = $parameter["page"];
        $search = $parameter["search"];
        $filter_object = $parameter["filter_object"];
        
        // Tinh chỉnh parameter
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;
        if($filter_object==0 || $filter_object=="")$filter_object=0;
        
        // New các model
        $this->Modules = new ModulesModel();        
        
        // Khởi động các biến cho các model
        $this->Modules->_search = $search;
        
        // Lấy dữ liệu chính
        $rowcount = $this->Modules->Count();
        $this->view->data = $this->Modules->SelectAll(($page-1)*$limit,$limit,"");
        
        // Set biến cho view
        $this->view->title = MSG11011001;
        //$this->view->subtitle = "Danh sách";
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
        
        // Enable button
        QLVBDHButton::EnableDelete("/qtht/Modules/Delete");
        QLVBDHButton::EnableAddNew("/qtht/Modules/Input");

    }
    /**
     * The input action - show the input page
     */
    public function inputAction() {
        // Lấy parameter
        $parameter = $this->getRequest()->getParams();
        $limit = $parameter["limit"];
        $page = $parameter["page"];
        $search = $parameter["search"];
        $filter_object = $parameter["filter_object"];
        $id = $parameter["id"];
        
        // New các model
        $this->Modules = new ModulesModel();        
        
        // Lấy dữ liệu        
        if($id>0){
            $this->view->data = $this->Modules->find($id)->current();
            $this->view->title = "Cập nhật ".MSG11011001;
            //$this->view->subtitle = MSG11011002;
        }else{
            $this->view->title = "Thêm mới ".MSG11011001;
            //$this->view->subtitle = MSG11011003;
        }
        
        // Set biến cho view
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
    
        QLVBDHButton::EnableSave("/qtht/Modules/Save");
        QLVBDHButton::EnableBack("/qtht/Modules");
       // QLVBDHButton::EnableHelp("");
                
    }
    /**
     * The save action - insert if item is new or update if if item is exist
     */
    public function saveAction(){
        $this->Modules = new ModulesModel();
        $this->view->parameter =  $this->getRequest()->getParams();
        $validator = new Zend_Validate_StringLength(1, 128);
        $validator->setMessages( array(
            Zend_Validate_StringLength::TOO_SHORT => 
                ERR11011001,
            Zend_Validate_StringLength::TOO_LONG  =>
                ERR11011002
        ));
        if (!$validator->isValid($this->view->parameter["NAME"])) {
            $messages = $validator->getMessages();
            // echo current($messages);
            if($this->view->parameter["ID_MOD"]>0){
                $this->_redirect('/default/error/error?id=3');
            } else {
                $this->_redirect('/default/error/error?id=2');
            }
        }
        if($this->view->parameter["ID_MOD"]>0){
            $this->Modules->update(array("NAME"=>$this->view->parameter["NAME"],"URL"=>$this->view->parameter["URL"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISPUBLIC"=>$this->view->parameter["ISPUBLIC"]),"ID_MOD=".$this->view->parameter["ID_MOD"]);
        }else{
            $this->Modules->insert(array("NAME"=>$this->view->parameter["NAME"],"URL"=>$this->view->parameter["URL"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISPUBLIC"=>$this->view->parameter["ISPUBLIC"]));
        }
        $this->_redirect("/qtht/Modules");
    }
    /**
     * The delete action - delete item
     */
    public function deleteAction(){
        $this->Modules = new ModulesModel();
        $this->view->parameter =  $this->getRequest()->getParams();
        try{
            $this->Modules->delete("ID_MOD IN (".implode(",",$this->view->parameter["DEL"]).")");
        }catch(Exception $ex){
        }
        $this->_redirect("/qtht/Modules");        
    }
}
