<?php
/**
 * DanhmucnhomnguoidungController
 * 
 * @author longtv
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/GroupsModel.php';
require_once 'qtht/models/ModulesModel.php';
require_once 'qtht/models/fk_groups_actionsModel.php';
require_once 'config/qtht.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/fk_users_groupsModel.php';

class Qtht_DanhmucnhomnguoidungController extends Zend_Controller_Action {
    /**
     * The default action - show the home page
     */
    public function indexAction() {
        // TODO Auto-generated DanhmucnhomnguoidungController::indexAction() default action

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
        $this->Groups = new GroupsModel();                
        
        // Khởi động các biến cho các model
        $this->Groups->_search = $search;
        
        // Lấy dữ liệu chính
        $rowcount = $this->Groups->Count();
        $this->view->data = $this->Groups->SelectAll(($page-1)*$limit,$limit,"ORDERS,NAME,ID_G");
        
        // Set biến cho view
        $this->view->title = MSG11009001;
        //$this->view->subtitle = "Danh sách";
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
        
        // Enable button
        QLVBDHButton::EnableDelete("/qtht/Danhmucnhomnguoidung/Delete");
        QLVBDHButton::EnableAddNew("/qtht/Danhmucnhomnguoidung/Input");
        
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
        $this->Groups = new GroupsModel();
        $this->fk_groups_actions = new fk_groups_actionsModel();
        
        // Khởi động các biến cho các model
        $this->view->action = $this->fk_groups_actions->SelectAllByIDG($id);
        
        // Lấy dữ liệu        
        if($id>0){
            $this->view->data = $this->Groups->find($id)->current();
            $this->view->title = "Cập nhật ".MSG11009001;
            //$this->view->subtitle = MSG11009002;
            $this->view->user = UsersModel::SelectByGroup($id);
            $this->view->ID_U_DAIDIEN = $deps->ID_U_DAIDIEN;
        }else{
            $this->view->title = "Thêm mới ".MSG11009001;
            //$this->view->subtitle = MSG11009003;
        }
        
        // Set biến cho view
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;

		if($id > 0){
				
				//Truong hop cap nhat
				$rowcn = $this->Groups->find($id);
				
				//kiem tra id cua chuc danh can cap nhat co nam trong csdl
				if($rowcn->count() >0 )
				{
					$this->view->activeselect = $rowcn->current()->ACTIVE;
					$this->view->id= $id;
				}	
				else{
					// Loi khi khong tim thay id cua chuc danh trong co so du lieu
					$this->_redirect('/default/error/error?control=Danhmucnhomnguoidung&mod=qtht&id=ERR11002002');
				}	
				
				//$this->view->subtitle = "Cập nhật";
			}
			else{
				//Truong hop them moi
				//$this->view->subtitle = "Thêm mới";
				
			}
			
		$this->view->datagr = $this->Groups->getListGr($id);
		//var_dump($this->view->datagr);
		QLVBDHButton::EnableSave("/qtht/Danhmucnhomnguoidung/Save");
        QLVBDHButton::EnableBack("/qtht/Danhmucnhomnguoidung");   
		        
    }
    /**
     * The save action - insert if item is new or update if if item is exist
     */
    public function saveAction(){
        $this->Groups = new GroupsModel();
        $this->view->parameter =  $this->getRequest()->getParams();

		$validator = new Zend_Validate_StringLength(1, 128);
        $validator->setMessages( array(
            Zend_Validate_StringLength::TOO_SHORT => 
                ERR11009001,
            Zend_Validate_StringLength::TOO_LONG  =>
                ERR11009002
        ));
        if (!$validator->isValid($this->view->parameter["NAME"])) {
            $messages = $validator->getMessages();
            // echo current($messages);
            if($this->view->parameter["ID_G"]>0){
                $this->_redirect('/default/error/error?id=3');
            } else {
                $this->_redirect('/default/error/error?id=2');
            }
        }
        $id_g = (int)$this->view->parameter["ID_G"];   
	    if($this->view->parameter["ID_G"]>0){
            $this->Groups->update(array(
            "NAME"=>$this->view->parameter["NAME"],
            "ACTIVE"=>$this->view->parameter["ACTIVE"],
            "ID_U_DAIDIEN"=>$this->view->parameter["ID_U_DAIDIEN"]==0?null:$this->view->parameter["ID_U_DAIDIEN"],
            "ORDERS"=>$this->view->parameter["ORDERS"]
            ),
            "ID_G=".$this->view->parameter["ID_G"]
            );            
        }else{
            $id_g = $this->Groups->insert(array(
            "NAME"=>$this->view->parameter["NAME"],
            "ACTIVE"=>$this->view->parameter["ACTIVE"],
            "ID_U_DAIDIEN"=>$this->view->parameter["ID_U_DAIDIEN"]==0?null:$this->view->parameter["ID_U_DAIDIEN"],
            "ORDERS"=>$this->view->parameter["ORDERS"]
            ));
        }
        /**
         * Add Group into Action
         */
        $this->fk_groups_actions = new fk_groups_actionsModel();
        $this->view->parameter =  $this->getRequest()->getParams();        
        try{
            // delete all item have this id
            $this->fk_groups_actions->delete("ID_G=".$id_g);
            // insert into database for each id check in grid
            for($i=0;$i<count($this->view->parameter["ID_ACT"]);$i++){            
                $this->fk_groups_actions->insert(array("ID_G"=>$id_g,"ID_ACT"=>$this->view->parameter["ID_ACT"][$i]));                
            }            
        }catch(Exception $ex){
        }
        $this->fk_groups_actions->getDefaultAdapter()->query("UPDATE QTHT_USERS U SET ORDERS=(SELECT MIN(ORDERS) FROM QTHT_GROUPS G INNER JOIN fk_users_groups UG ON G.ID_G = UG.ID_G WHERE UG.ID_U = U.ID_U)");

		$fk_usersg = new fk_users_groupsModel();
		$arr_emp = $this->_request->getParam("SEL_EMP");
		//echo $id_g;
		//var_dump($arr_emp);
		$fk_usersg->deleteByIdG($id_g);
		$fk_usersg->insertOneGroupMultiEmp($id_g,$arr_emp);
		//exit;	
		$this->_redirect("/qtht/Danhmucnhomnguoidung");
    }
    /**
     * The delete action - delete item
     */
    public function deleteAction(){
        $this->Groups = new GroupsModel();
        $this->fk_groups_actions = new fk_groups_actionsModel();
        $this->view->parameter =  $this->getRequest()->getParams();
        try{
            for($i=0;$i<count($this->view->parameter["DEL"]);$i++){   
                $this->fk_groups_actions->delete("ID_G=".$this->view->parameter["DEL"][$i]);              
            } 
            $this->Groups->delete("ID_G IN (".implode(",",$this->view->parameter["DEL"]).")");
        }catch(Exception $ex){
        }
        $this->_redirect("/qtht/Danhmucnhomnguoidung");          
    }    
}
