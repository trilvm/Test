<?php
/*
 * ThuTucController
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'motcua/models/ThuTucModel.php';
require_once 'motcua/models/LoaiModel.php';
require_once 'motcua/models/ThuTucForm.php';
require_once 'Zend/Controller/Action.php';
class Motcua_ThuTucController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /motcua/thutuc
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
		$filter_object = $parameter["filter_object"];
		
		//Refinde parameters
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		if($filter_object==0 || $filter_object=="") $filter_object=0;
		
		//Define model
		$this->thutucs = new ThuTucModel();
		$this->loais=new LoaiModel();
		$this->thutucs->_id_loai = $filter_object;
		
		//assign value for search action
		$this->thutucs->_search = $search;
		
		//Get data for view
		$rowcount = $this->thutucs->Count();
		if($rowcount<=$limit) $page=1;
		$this->view->data = $this->thutucs->SelectAll(($page-1)*$limit,$limit,"TENTHUTUC,ID_THUTUC");
		
		//View detail
		$this->view->title = "Quản trị thủ tục hồ sơ Một cửa";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->filter_object=$filter_object;
		// Lấy dữ liệu phụ
        $this->view->loais = $this->loais->SelectAll(0,0,"");
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListThuTucs",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/motcua/thutuc/delete");
		QLVBDHButton::EnableAddNew("/motcua/thutuc/input");
	}
    /*
     * This fuction is input action. This action display form to input data
     */
	function inputAction()
    {
    	$id=(int)$this->_request->getParam('id');    	
    	$error=$this->_request->getParam('error');
    	$formData = $this->_request->getPost();   
    	$parameter = $this->getRequest()->getParams();
    	//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom"); 	
    	$this->view->error=$error;
    	$form = new ThuTucForm();
		$thutucModel=new ThuTucModel();
		//get current setting for page
		$limit = $parameter["limit"];
        $page = $parameter["page"];
        $search = $parameter["search"];
        $filter_object = $parameter["filter_object"];
        // Set biến cho view
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
    
        QLVBDHButton::EnableSave("/motcua/thutuc/save");
		QLVBDHButton::EnableBack("/motcua/thutuc");
		//QLVBDHButton::EnableHelp("");
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "Cập nhật Thủ tục hồ sơ Một cửa";
    		//$this->view->subtitle="Chỉnh sửa";
     		$thutucs = $thutucModel->fetchRow('ID_THUTUC='.$id); 
     		if($thutucs!=null)    		
               		$form->populate($thutucs->toArray());                
            else  $this->_redirect('/motcua/thutuc/input');
            $this->view->form = $form;
            //Save your id thutuc ho so
            $this->view->id=$id;           
        }
    	else 
    	{
    		$this->view->title = "Thêm mới Thủ tục hồ sơ Một cửa";
    		//$this->view->subtitle="Thêm mới"; 
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
     * save/edit action of thutuc controller
     *
     */
    function saveAction()
    {
    	$formData = $this->_request->getPost();
    	$id=(int)$this->_request->getParam("id",0);
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
			            'TENTHUTUC' 		=> $formData['TENTHUTUC'],
			        	'ID_LOAIHOSO' 	=> $formData['ID_LOAIHOSO'],			        	
			        	'ACTIVE' 		=> $formData['ACTIVE'],
			        	 );
		        $thutucModel = new ThuTucModel();	
		        if($id>0)	                
		        {
		        	$where="ID_THUTUC=".$id;
		        	$thutucModel->update($data,$where);	        	
		        }
		        else 
		        {
		        	$thutucModel->insert($data);	        	
		        }
		        $this->_redirect('/motcua/thutuc/');
		      
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
    	 $this->_redirect('/motcua/thutuc/input');
    }
    /**
     * delete action
     *
     */
    function deleteAction()
    {
    	$this->view->title = "Xóa Thủ tục hồ sơ";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/motcua/thutuc/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
    	if($this->_request->isPost())
		{
			$idarray = $this->_request->getParam('DEL');
			var_dump($idarray);
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
						$delThuTuc = new ThuTucModel();
	                	$where = 'ID_THUTUC = ' . $idarray[$counter];
	                	$delThuTuc->delete($where);
						
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
					$this->_redirect('/motcua/thutuc/');				
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
	}
}

