<?php
/*
 * EmployeesController
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'qtht/models/EmployeesModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'qtht/models/EmployeesForm.php';
require_once 'Zend/Controller/Action.php';
class Qtht_EmployeesController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /qtht/employees
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
		$this->employees = new EmployeesModel();
		
		//assign value for search action
		$this->employees->_search = $search;
		
		//Get data for view
		$rowcount = $this->employees->Count();
		$this->view->data = $this->employees->SelectAll(($page-1)*$limit,$limit,"NAME,ID_MNU");
		
		//View detail
		$this->view->title = "Quản trị Nhân viên";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListEmployees",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/qtht/employees/delete");
		QLVBDHButton::EnableAddNew("/qtht/employees/add");
	}
    /*
     * This fuction is add action
     */
	function addAction()
    {
     	$this->view->title = "Thêm mới Nhân viên";
     	//Add button 
    	QLVBDHButton::EnableSave("/qtht/employees/add");
		QLVBDHButton::EnableBack("/qtht/employees/");
		//QLVBDHButton::EnableHelp("");
       //If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom");		
        $form = new EmployeesForm(); 
        $this->view->form = $form;
        if ($this->_request->isPost() && $validFrom==null)
        {
        	$formData = $this->_request->getPost(); 
        	$form=$this->view->form;
        	if ($form->isValid($_POST)) 
			{   
				// or assign to the view object and render a view...
				$firstname = $formData['FIRSTNAME'];	            
				$lastname = $formData['LASTNAME'];
	       	    if ($firstname != '' && $lastname != '') 
	       	    {
	       	    	try 
	       	    	{
		                $data = array(
		                    'firstname' => $firstname,
		                    'lastname'  => $lastname,
		                	'birthdate'	=> ($formData['BIRTHDATE'] == ""?null:$formData['BIRTHDATE']),
		                	'id_dep'	=> ($formData['ID_DEP'] == ""?null:$formData['ID_DEP']),
		                );
		                $employees = new EmployeesModel();
		                $employees->insert($data);
		                $this->_redirect('/qtht/employees/');
		                return;
	       	    	}
	       	    	catch(Exception $e2)
	       	    	{
	       	    		//echo $e2;
	       	    		$form->populate($formData); 
	       	    		return;
	       	    	}
		        }
		     }   
	         else 
	         {
	        	 $this->view->form = $form;
				 return $this->render(); 	     	
        	 }
	    }    
	    
    }
    /**
     * Edit action
     *
     */
    function editAction()
    {
    	//Title of form
    	$this->view->title = "Chỉnh sửa thông tin người sử dụng";
    	//Add button
    	QLVBDHButton::EnableSave("/qtht/employees/add");
		QLVBDHButton::EnableBack("/qtht/employees/");
		//QLVBDHButton::EnableHelp("");
		//get id of Item from list
		$id_u = (int)$this->_request->getParam('id', 0);
		//If it has submitted from listForm
		$validFrom=$this->getRequest()->getParam("comeFrom");
        $employees = new EmployeesModel();
		//set idcurrentmenu for form
        $form = new EmployeesForm();
      	$this->view->form = $form;
        if ($this->_request->isPost() && $validFrom==null) 
        {
        	$formData = $this->_request->getPost(); 
        	var_dump($formData);
        	$form=$this->view->form;
        	if ($form->isValid($_POST)) 
			{  
				
				var_dump($formData);         			
			   	$id_u = (int)$this->_request->getPost('id');           
	            $firstname = $formData['FIRSTNAME'];
	            $lastname = $formData['LASTNAME'];
	            if ($id_u !== false) 
	            {
	            	
	            	try 
	            	{
		                if ($firstname != '' && $lastname!= '') 
		                {
		                	echo 'co validat ma ta';
		                    $data = array(
		                            'firstname' => $firstname,
				                    'lastname'  => $lastname,
				                	'birthdate'	=> ($formData['BIRTHDATE'] == ""?null:$formData['BIRTHDATE']),
				                	'id_dep'	=> ($formData['ID_DEP'] == ""?null:$formData['ID_DEP']),
		                    );
		                    $where = 'ID_EMP = ' . $id_u;	                    
		                    $employees->update($data, $where);
		                    	
		                    $this->_redirect('/qtht/employees/');
		                    return;
		                } 
		                else 
		                {
		                     $form->populate($formData);  
		                     $this->view->id =$id_u;
		                }
		            }
	            	catch(Exception $e1){echo $e1;};
	            }
	        }
	        else 
	        {
	        	//If it still contains errors, this will asign the previous values to form
        		$form->populate($formData); 
        		$this->view->id =$id_u;
	        }
        } 
        else 
        {
           // employees id should be $params['id']. This is select data with id   
         	$id_u = (int)$this->_request->getParam('id', 0);
            if ($id_u > 0) 
            {
                $employees = new EmployeesModel();
                $employees = $employees->fetchRow('ID_EMP='.$id_u);                
                $form->populate($employees->toArray());
                //Save your id employee
                $this->view->id=$id_u;               
            }
        }
        $this->render();
    }    
    function deleteAction()
    {
    	$this->view->title = "Xóa nhân viên";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/qtht/employees/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
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
						$delEmployees = new EmployeesModel();
	                	$where = 'ID_EMP = ' . $idarray[$counter];
	                	$delEmployees->delete($where);
						
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
					$this->_redirect('/qtht/employees/');				
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

