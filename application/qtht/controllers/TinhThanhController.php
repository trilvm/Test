<?php
/*
 * TinhThanh controller
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'qtht/models/TinhThanhModel.php';
require_once 'qtht/models/TinhThanhForm.php';
require_once 'Zend/Controller/Action.php';
class Qtht_TinhThanhController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /qtht/tinhthanh
	 * @return voi
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
		$this->tinhthanh = new TinhThanhModel();
		
		//assign value for search action
		$this->tinhthanh->_search = $search;
		
		//Get data for view
		$rowcount = $this->tinhthanh->Count();
		$this->view->data = $this->tinhthanh->SelectAll(($page-1)*$limit,$limit,"TEN_TINHTHANH");
		//View detail
		$this->view->title = "Quản trị Tỉnh thành";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListTinhThanh",$page) ;
		
		//Enable button		
		QLVBDHButton::EnableAddNew("/qtht/tinhthanh/input");
                QLVBDHButton::EnableDelete("/qtht/tinhthanh/delete");
	}  
     /*
     * This fuction is input action. This action display form to input data
     */
	function inputAction()
    {
    	$id=(int)$this->_request->getParam('id');    	
    	$error=$this->_request->getParam('error');
    	$formData = $this->_request->getPost();  
    	//Because rootmenu cannot edit by user, it send direct list menu if id=1(1 is id of root menu)
		if($id_p==1)   $this->_redirect('/qtht/tinhthanh/'); 
    	//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom"); 	
    	$this->view->error=$error;
    	QLVBDHButton::EnableSave("/qtht/tinhthanh/save");
		QLVBDHButton::EnableBack("/qtht/tinhthanh");
		//QLVBDHButton::EnableHelp("");
		$form = new TinhThanhForm();
		$depModel=new TinhThanhModel();
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "Cập nhật tỉnh thành";
    		//$this->view->subtitle = "Chỉnh sửa";
    		
     		$deps = $depModel->fetchRow('ID_TINHTHANH='.$id); 
     		if($deps!=null)    		
               		$form->populate($deps->toArray());                
            else  $this->_redirect('/qtht/tinhthanh/input');
            $this->view->form = $form;
            //Save your id 
            $this->view->id=$id;           
        }
    	else 
    	{
    		$this->view->title = "Thêm mới tỉnh thành";
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
    	$tinhthanhModel = new TinhThanhModel();
    	$active = $this->_request->getParam('act');    
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
		                    'CODE' => $formData['CODE'],                   
		                	'TEN_TINHTHANH'	=> $formData['TEN_TINHTHANH'],
		                	'ISLOCAL'	=> ($formData['ISLOCAL'] == ""?null:$formData['ISLOCAL']),
		                );		      
		        if($id>0)	                
		        {
		        	$where="ID_TINHTHANH=".$id;
		        	$tinhthanhModel->update($data,$where);	        	
		        }
		        else 
		        {
		        	$tinhthanhModel->insert($data);	        	
		        }
		        $this->_redirect('/qtht/tinhthanh/');
		      
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
    	 $this->_redirect('/qtht/tinhthanh/input');
    } 
    /**
     * Delete items action
     *
     */  
    function deleteAction()
    {
    	$this->view->title = "Xóa tỉnh thành";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/qtht/tinhthanh/","","",2);
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
						$delTinhThanh = new TinhThanhModel();
	                	$where = 'ID_TINHTHANH = ' . $idarray[$counter];
	                	$delTinhThanh->delete($where);
						
					}
					catch(Exception $er){ $adderror=$adderror.$idarray[$counter].' ; ';  };
				}
				$counter++;
			}
			//already delete some or all items
			if($counter>0 && $checkroot)
			{
				if($counter==count($idarray ))	
				{
					$this->view->deleteError="Xóa thành công các mục đã chọn";	
					$this->_redirect('/qtht/tinhthanh/');				
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

