<?php
/**
 * ActionsController
 * 
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/ActionsModel.php';
require_once 'qtht/models/ModulesModel.php';
require_once 'config/qtht.php';

class Qtht_ActionsController extends Zend_Controller_Action {
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
        $filter_object = $parameter["filter_object"];
        
        // Tinh chỉnh parameter
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;
        if($filter_object==0 || $filter_object=="")$filter_object=0;
        
        // Tạo mới model
        $this->Actions = new ActionsModel();
        $this->Modules = new ModulesModel();
        
        //Khởi động các biến cho các model
        $this->Actions->_search = $search;
        $this->Actions->_id_m = $filter_object;

        // Lấy dữ liệu chính
        $rowcount = $this->Actions->Count();
        $this->view->data = $this->Actions->SelectAll(($page-1)*$limit,$limit,"ID_MOD,NAME");
        
        // Lấy dữ liệu phụ
        $this->view->Modules = $this->Modules->SelectAll(0,0,"");
        
        // Set biến cho view
        $this->view->title = MSG11001001;
        //$this->view->subtitle = "Danh sách";
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
        
        QLVBDHButton::EnableDelete("/qtht/Actions/Delete");
        QLVBDHButton::EnableAddNew("/qtht/Actions/Input");
        
        Zend_Dojo::enableView($this->view);
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
        $this->Actions = new ActionsModel();
        $this->Modules = new ModulesModel();
        
        // Lấy dữ liệu
        $this->view->Modules = $this->Modules->fetchAll();
        if($id>0){
            $this->view->data = $this->Actions->find($id)->current();
            $this->view->title = "cập nhật".MSG11001001;
            //$this->view->subtitle = MSG11001002;
        }else{
            $this->view->title = "thêm mới".MSG11001001;
            //$this->view->subtitle = MSG11001003;
        }
        
        // Set biến cho view
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
    
        QLVBDHButton::EnableSave("/qtht/Actions/Save");
        QLVBDHButton::EnableBack("/qtht/Actions");
        //QLVBDHButton::EnableHelp("");
        
    }
    /**
     * The save action - insert if item is new or update if if item is exist
     */
    public function saveAction(){
        $this->Actions = new ActionsModel();
        $this->view->parameter =  $this->getRequest()->getParams();
        $validator = new Zend_Validate_StringLength(1, 128);
        $validator->setMessages( array(
            Zend_Validate_StringLength::TOO_SHORT => 
                ERR11001001,
            Zend_Validate_StringLength::TOO_LONG  =>
                ERR11001002
        ));
        if (!$validator->isValid($this->view->parameter["NAME"])) {
            $messages = $validator->getMessages();
            // echo current($messages);
            if($this->view->parameter["ID_ACT"]>0){
                $this->_redirect('/default/error/error?id=3');
            } else {
                $this->_redirect('/default/error/error?id=2');
            }
        }
        if($this->view->parameter["ID_ACT"]>0){
            $this->Actions->update(array("NAME"=>$this->view->parameter["NAME"],"ID_MOD"=>$this->view->parameter["ID_MOD"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISPUBLIC"=>$this->view->parameter["ISPUBLIC"],"DESCRIPTION"=>$this->view->parameter["mota"]),"ID_ACT=".$this->view->parameter["ID_ACT"]);
        }else{
            $this->Actions->insert(array("NAME"=>$this->view->parameter["NAME"],"ID_MOD"=>$this->view->parameter["ID_MOD"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISPUBLIC"=>$this->view->parameter["ISPUBLIC"],"DESCRIPTION"=>$this->view->parameter["mota"]));
        }
        $this->_redirect("/qtht/Actions");
        
    }
    /**
     * The delete action - delete item
     */
    public function deleteAction(){
        $this->Actions = new ActionsModel();        
        $this->view->parameter =  $this->getRequest()->getParams();
        try{
            $this->Actions->delete("ID_ACT IN (".implode(",",$this->view->parameter["DEL"]).")");
        }catch(Exception $ex){
        }                
        $this->_redirect("/qtht/Actions");
    }
}
