<?php
/*
 * chudesController
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'traodoi/models/ChuDeModel.php';
require_once 'traodoi/models/ChuDeForm.php';
require_once 'Zend/Controller/Action.php';
/**
 * chudesController is class to control user management system with add,edit,delete
 * 
 * @author truongvc
 * @version 1.0
 */
class Traodoi_ChuDeController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /traodoi/chude
	 *
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
		$this->chudes = new ChuDeModel();
		
		//assign value for search action
		$this->chudes->_search = $search;
		
		//Get data for view
		$this->view->data = $this->chudes->SelectAll(($page-1)*$limit,$limit,"ten_chude ASC,id_chude DESC");
		
		//View detail
		$this->view->title = "Danh sách chủ đề";
		//$this->view->subtitle = "Quản trị Chủ đề";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListchudes",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/traodoi/chude/delete");
		QLVBDHButton::EnableAddNew("/traodoi/chude/input");
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
    	QLVBDHButton::EnableSave("/qtht/chudes/save");
		QLVBDHButton::EnableBack("/qtht/chudes");
		$form =new ChuDeForm();
		$chudeModel=new ChuDeModel();
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "Cập nhật chủ đề";
    		//$this->view->subtitle = "Chỉnh sửa";
    		$chudes = $chudeModel->fetchRow('id_chude='.$id); 
     		if(count($chudes)>0)    		
     		{
		   		$form->populate($chudes->toArray());                
		 	}
            else  $this->_redirect('/traodoi/chude/input');
            $this->view->form = $form;
            //Save your id menu
            $this->view->id=$id;           
        }
    	else 
    	{
    		$this->view->title = "Thêm mới chủ đề";
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
    	$chudes = new ChuDeModel();
    	//
    	global $auth;
		$user = $auth->getIdentity();
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
		                    'ten_chude' 		=> $formData['ten_chude'],
		                	'trangthai' 	=> $formData['trangthai'],		                	
		                	
		                );		        
		        if($id>0)	                
		        {
		        	$where="id_chude=".$id;
		        	$chudes->update($data,$where);	        	
		        }
		        else 
		        {
		        	$chudes->insert($data);	        	
		        }
		        $this->_redirect('/traodoi/chude/');
		      
	        	//return;
			}
			catch(Exception $e2)
			{
				$messageError="Có lỗi xảy ra khi thêm mới/update dữ liệu".$e2;
				$this->_request->setParam('error',$messageError);
				$this->_request->setParams($formData);
				$this->dispatch('inputAction');			
			}
    	}
    	else 
    	 $this->_redirect('/traodoi/chude/input');
    }
    
    /*
     * This is delete action for module /qtht/chudes/
     * @return void
     */   
    function deleteAction()
    {
		 global $db;
    	$this->view->title = "Xóa Menu";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/traodoi/chude/","","",2);
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
				if ($idarray[$counter] > 0) 
				{
					try 
					{
						$delchudes = new ChuDeModel();
	                	$where = 'id_chude = ' . $idarray[$counter];
	                	$delchudes->delete($where);
						$db->query("UPDATE ".QLVBDHCommon::Table("td_thongtin")." SET id_chude=0 WHERE id_chude=".$idarray[$counter]);
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
					$this->_redirect('/traodoi/chude/');
										
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

