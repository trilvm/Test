<?php
/*
 * Users controller
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/EmployeesModel.php';
require_once 'qtht/models/UsersForm.php';
require_once 'Zend/Controller/Action.php';
// hieuvt 
require_once 'qtht/models/fk_users_actionsModel.php';
// end hieuvt
/**
 * UsersController is class to control user management system with add,edit,sendu,sendm.
 * 
 * @author truongvc
 * @version 1.0
 */
class Qtht_UsersController extends Zend_Controller_Action 
{
	 /*
     * This is index action for module /qtht/users/
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
		$this->users = new UsersModel();
		
		//assign value for search action
		$this->users->_search = $search;
		
		//Get data for view
		$rowcount = $this->users->Count();
		$this->view->data = $this->users->SelectAll(($page-1)*$limit,$limit,"USERNAME,ID_U");
		
		//View detail
		$this->view->title = "Quản trị người sử dụng";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListUsers",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/qtht/users/delete");
		QLVBDHButton::EnableAddNew("/qtht/users/add");
	}     
    function deleteAction()
    {
    	$this->view->title = "Xóa người sử dụng";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/qtht/users/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
// hieuvt
        $this->fk_users_actions = new fk_users_actionsModel();
// end hieuvt        
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
				
				if ($idarray[$counter] > 0) 
				{				
					try 
					{
// hieuvt
                        $this->fk_users_actions->delete("ID_U=".$idarray[$counter]);              
// end hieuvt						
						$delusers = new UsersModel();
	                	$where = 'ID_U = ' . $idarray[$counter];
	                	$delusers->delete($where);
						
					}
					catch(Exception $er){ $adderror=$adderror.$idarray[$counter].' ; ';};
				}
				$counter++;
			}
			//already delete some or all items
			if($counter>0)
			{
				if($counter==count($idarray ))	
				{
					$this->view->deleteError="Xóa thành công các mục đã chọn";	
					$this->_redirect('/qtht/users/');				
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
    /*
     * This fuction is input action. This action display form to input data
     */
	function inputAction()
    {
    	$id=(int)$this->_request->getParam('id');    	
    	$error=$this->_request->getParam('error');
    	$formData = $this->_request->getPost();   
    	//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom"); 	
    	$this->view->error=$error;
    	QLVBDHButton::EnableSave("/qtht/users/save");
		QLVBDHButton::EnableBack("/qtht/users");
		//QLVBDHButton::EnableHelp("");
		$form = new UsersForm(array('idCurrentUser'=>$id));
		$usersModel=new UsersModel();
// hieuvt
        $id_u = 0;
        // New các model
        $this->fk_users_actions = new fk_users_actionsModel();
        // Khởi động các biến cho các model
        $this->fk_users_actions->_id_u = $id;
        // Lấy dữ liệu phụ
        $this->view->fk_users_actions = $this->fk_users_actions->SelectAll(0,0,"");
// end hieuvt

		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "Cập nhật người dùng";
    		
     		$users = $usersModel->fetchRow('ID_U='.$id); 
     		if($users!=null)
     		{
     			$form->populate($users->toArray());                
     		}
            else  $this->_redirect('/qtht/users/input');
            $this->view->form = $form;
            //Save your id loai ho so
            $this->view->id=$id;           
        }
    	else 
    	{
    		$this->view->title = "Thêm mới người dùng";
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
     	//Add button 
    }
    /**
     * save/edit action of loai controller
     *
     */
    function saveAction()
    {
    	$formData = $this->_request->getPost();
    	$id=(int)$this->_request->getParam("id",0);
    	$id_u=(int)$this->_request->getParam("id_u",0);
    	//active from list form
    	$active = $this->_request->getParam('active'); 
    	if($active!=null&&$id_u>0)
		{
			  $check=true;
			  if($active=='0') 
			  {
			  	$active='1';
			  }
			  else if($active=='1') 
			  {
			  	$active='0';
			  }
			  else $check=false;
			  if($check)
			  {
			  	$data = array(
                	'active'	=> $active,
               	 	);
               	$users = new UsersModel();
              	$where = 'ID_U = ' . $id_u;	                                     
              	$users->update($data, $where);	                    	
              	$this->_redirect('/qtht/users/');	
			  }
			  else $this->_redirect('/qtht/users/');	
			  
		}		
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
		                    'username' 		=> $formData['USERNAME'],		                	
		                	'id_emp' 		=> $formData['ID_EMP'],
		                	'active' 		=> $formData['ACTIVE'],
		                		 
		                );
		        $usersModel = new UsersModel();	
		        if($id>0)	                
		        {
		        	$where="ID_U=".$id;
		        	$usersModel->update($data,$where);	        	
		        }
		        else 
		        {
		        	$data+=array('password'=> md5($formData['PASSWORD']));
		        	$id =$usersModel->insert($data);	        	
		        }
// hieuvt
		        /**
		         * Add User into Action
		         */
		        $this->fk_users_actions = new fk_users_actionsModel();
		        $this->view->parameter =  $this->getRequest()->getParams(); 
		        var_dump($this->view->parameter["ACT_ID"]);       
		        try
		        {
		            // delete all item have this id
		            $this->fk_users_actions->delete("ID_U=".$id);
		            // insert into database for each id check in grid
		            for($i=0;$i<count($this->view->parameter["ACT_ID"]);$i++)
		            {            
		                $this->fk_users_actions->insert(array("ID_U"=>$id,"ID_ACT"=>$this->view->parameter["ACT_ID"][$i]));                
		            }            
		        }
		        catch(Exception $ex)
		        {
		        	echo $ex;
		        	exit;
		        }
// end hieuvt
		        $this->_redirect('/qtht/users/');
		      
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
    	 $this->_redirect('/qtht/users/input');
    }
    
}

