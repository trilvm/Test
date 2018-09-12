<?php
/*
 * MenusController
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'qtht/models/MenusModel.php';
require_once 'qtht/models/MenusForm.php';
require_once 'Zend/Controller/Action.php';
/**
 * MenusController is class to control user management system with add,edit,delete
 * 
 * @author truongvc
 * @version 1.0
 */
class Qtht_MenusController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /qtht/menus
	 *
	 */
	function indexAction()
    {
    	global $db;
    	//Get parameters
		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
		if($parameter["code"]=="order"){
			$ID_ORDER = $parameter["ID_ORDER"];
			$ORDER = $parameter["ORDER"];
			for($i=0;$i<count($ID_ORDER);$i++){
				try{
				$db->update("QTHT_MENUS",array("ORDERS"=>$ORDER[$i]),"ID_MNU=".$ID_ORDER[$i]);
				}catch(Exception $ex){
					
				}
			}
		}
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		
		//Refinde parameters
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		
		//Define model
		$this->menus = new MenusModel();
		
		//assign value for search action
		$this->menus->_search = $search;
		
		//Get data for view
		$rowcount = $this->menus->Count();
		//$this->view->data = $this->menus->SelectAll(($page-1)*$limit,$limit,"NAME,ID_MNU");
		$data = array();
		QLVBDHCommon::GetTree(&$data,"VIEW_MENUS","ID_MNU","ID_MNU_PARENT",1,1);
		$this->view->data = $data;
		
		//View detail
		$this->view->title = "Quản trị Menu";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListMenus",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/qtht/menus/delete");
		QLVBDHButton::EnableAddNew("/qtht/menus/add");
	}
	/*
     * This fuction is input action
     */
	function inputAction()
	{
		$id=(int)$this->_request->getParam('id');    	
    	$error=$this->_request->getParam('error');
    	$formData = $this->_request->getPost();   
    	//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom"); 	
    	$this->view->error=$error;
    	QLVBDHButton::EnableSave("/qtht/menus/save");
		QLVBDHButton::EnableBack("/qtht/menus");
		$form = new MenusForm(array('idCurrentMenu'=>$id));
		$menuModel=new MenusModel();
		//list of Action
		$this->view->act = $menuModel->GetAllActions();   
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "Cập nhật menu";
    		//$this->view->subtitle = "Chỉnh sửa";
    		//Because rootmenu cannot edit by user, it send direct list menu if id=1(1 is id of root menu)
			if($id==1)   $this->_redirect('/qtht/menus/');
			
     		$menus = $menuModel->fetchRow('ID_MNU='.$id); 
     		if($menus!=null)    		
     		{
		   		$form->populate($menus->toArray());                
		   		//get Current ACTID of current menu
		   		$this->view->ACTID=$menus->ACTID;
		   		
     		}
            
            else  $this->_redirect('/qtht/menus/input');
            $this->view->form = $form;
            //Save your id menu
            $this->view->id=$id;           
        }
    	else 
    	{
    		$this->view->title = "Thêm mới menu";
    		//$this->view->subtitle = "Thêm mới";
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
	}
	/*
     * This fuction is save action
     */
	function saveAction()
    {
    	$formData = $this->_request->getPost();
    	$id=(int)$this->_request->getParam("id",0);
    	//khoi tao
    	$menus = new MenusModel();
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
		                    'name' 		=> $formData['NAME'],
		                	'script' 	=> $formData['SCRIPT'],
		                	'icon' 		=> $formData['ICON'],
		                	'url' 		=> $formData['URL'],
		                	'actid' 	=> ($formData['ACTID'] == ""?null:$formData['ACTID']),
		                	'id_mnu_parent'	=> $formData['ID_MNU_PARENT'],
		                	'islastmenu'=> $formData['ISLASTMENU'],
		                	'popup' 	=> $formData['POPUP'],
		                	'width'		=> (trim($formData['WIDTH']) == ""?null:$formData['WIDTH']),
		                	'height' 	=> (trim($formData['HEIGHT']) == ""?null:$formData['HEIGHT']),
		                	'top' 		=> (trim($formData['TOP']) == ""?null:$formData['TOP']),
		                	'xleft' 	=> (trim($formData['XLEFT']) == ""?null:$formData['XLEFT']),	 	 	                    
		                	'iscenter' 	=> $formData['ISCENTER'],	 
		                );		        
		        if($id>0)	                
		        {
		        	$where="ID_MNU=".$id;
		        	$menus->update($data,$where);	        	
		        }
		        else 
		        {
		        	$menus->insert($data);	        	
		        }
		        $this->_redirect('/qtht/menus/');
		      
	        	//return;
			}
			catch(Exception $e2)
			{
				$messageError="Có lỗi xảy ra khi thêm mới/update dữ liệu";
				$this->_request->setParam('error',$messageError);
				$this->_request->setParams($formData);
				$this->dispatch('inputAction');			
			}
    	}
    	else 
    	 $this->_redirect('/motcua/loai/input');
    }
    
    /*
     * This is delete action for module /qtht/menus/
     * @return void
     */   
    function deleteAction()
    {
    	$this->view->title = "Xóa Menu";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/qtht/menus/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
        $checkroot=true;
    	if($this->_request->isPost())
		{
			$idarray = $this->_request->getParam('DEL');
					
			if(count($idarray)<=0)
			{
				$this->view->deleteError="Không có mục nào để tiến hành xóa( xin vui lòng chọn một mục!)";
			}
			$counter=0;
			while ( $counter < count($idarray )) 
			{
				if($idarray[$counter]==1)
				{
					$this->view->deleteError=' Danh mục root không được xóa';
					$checkroot=false;
				}
				if ($idarray[$counter] > 0) 
				{
					try 
					{
						$delMenus = new MenusModel();
	                	$where = 'ID_MNU = ' . $idarray[$counter];
	                	$delMenus->delete($where);
						
					}
					catch(Exception $er){ $adderror=$adderror.$idarray[$counter].' ; ';};
				}
				$counter++;
			}
			//already delete some or all items
			if($counter>0 && $checkroot)
			{
				if($counter==count($idarray ))	
				{
					$this->view->deleteError="Xóa thành công các mục đã chọn";
					$this->_redirect('/qtht/menus/');
										
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
		else 
		{
			
		}
	
    }
  
}

