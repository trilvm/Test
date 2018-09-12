<?php
/*
 * LoaiController
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
require_once 'motcua/models/LoaiModel.php';
require_once 'motcua/models/LoaiForm.php';
require_once 'Zend/Controller/Action.php';
require_once 'motcua/models/linhvucmotcuaModel.php';
class Motcua_LoaiController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /motcua/loai
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
		$LINHVUC = $parameter["LINHVUC"];
		//Refinde parameters
		if($limit==0 || $limit=="")$limit=100;
		if($page==0 || $page=="")$page=1;
		
		//Define model
		$linhvuc = new linhvucmotcuaModel();
		$this->view->linhvuc = $linhvuc;
		$this->view->datalinhvuc = $linhvuc->SelectAll(0,0,"NAME");
		$this->loais = new LoaiModel();
		
		//assign value for search action
		$this->loais->_search = $search;
		
		//Get data for view
		$rowcount = $this->loais->Count();
		$this->loais->_linhvuc = $LINHVUC;
		$this->view->data = $this->loais->SelectAll($parameter,($page-1)*$limit,$limit,"NAME,ID_MNU");
		
		//View detail
		$this->view->title = "Quản trị loại hồ sơ Một cửa";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->LINHVUC=$LINHVUC;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frmListLoais",$page) ;
		
		//Enable button
		QLVBDHButton::EnableDelete("/motcua/loai/delete");
		QLVBDHButton::EnableAddNew("/motcua/loai/input");
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
    	QLVBDHButton::EnableSave("/motcua/loai/save");
		QLVBDHButton::EnableBack("/motcua/loai");
		//QLVBDHButton::EnableHelp("");
		$form = new LoaiForm();
		$loaiModel=new LoaiModel();
		$linhvuc = new linhvucmotcuaModel();
		$this->view->data = $linhvuc->SelectAll(0,0,"NAME");
		$this->view->dataloai = $loaiModel->GetAllLoais("");
		$this->view->LINHVUC = $this->getRequest()->getParam("LINHVUC"); 
		//if id>0 It's edit action awake
    	if($id>0)
    	{
    		$this->view->title = "chỉnh sửa Loại hồ sơ Một cửa";
    		//$this->view->subtitle ="Chỉnh sửa";
     		$loais = $loaiModel->fetchRow('ID_LOAIHOSO='.$id);
			$this->view->formdata = $loais;
     		if($loais!=null)    		
               		$form->populate($loais->toArray());                
            else  $this->_redirect('/motcua/loai/input');
            $this->view->form = $form;
            //Save your id loai ho so
            $this->view->id=$id;     
			$this->view->ID_ONWEBSITE = $loais->ID_ONWEBSITE;
        }
    	else 
    	{
    		$this->view->title = "Thêm mới Loại hồ sơ Một cửa";
    		//$this->view->subtitle ="Thêm mới";
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
    	if($formData!=null)
    	{
	    	try 
	    	{
	       		$data = array(
	       				'CODE' 		=> $formData['CODE'],
			            'TENLOAI' 		=> $formData['TENLOAI'],
			        	'SONGAYXULY' 	=> $formData['SONGAYXULY'],
			        	'LEPHI' 		=> $formData['LEPHI'],
			        	'CHUTHICH' 		=> $formData['CHUTHICH'],
			        	'ID_LOAIHSCV'=>$formData['ID_LOAIHSCV'],
						'ID_LV_MC'=>$formData['ID_LV_MC'],
						'IS_UPDATE'=>1
	       				 );
		        $loaiModel = new LoaiModel();	
		        if($id>0)	                
		        {
		        	//truong hop cap nhat loai ho so dong bo tren website
					if($formData["ID_ONWEBSITE"] > 0){
						$data["IS_UPDATE"] = 1;
					}
					$where="ID_LOAIHOSO=".$id;
		        	$loaiModel->update($data,$where);	 
					
		        }
		        else 
		        {
		        	$data+=array('ID_LOAIHOSO' 	=> $formData['ID_LOAIHOSO']);
		        	$loaiModel->insert($data);	        	
		        }
		        //copy thu tuc
				if($formData['ID_LOAI_MC']!=0){
					//xoa cac thu tuc cu
					$loaiModel->getDefaultAdapter()->delete("motcua_thutuc_canco","ID_LOAIHOSO=".$id);
					$loaiModel->getDefaultAdapter()->query("INSERT INTO motcua_thutuc_canco(ID_LOAIHOSO,TENTHUTUC,ACTIVE) SELECT '".$id."' as ID_LOAIHOSO, TENTHUTUC,ACTIVE FROM motcua_thutuc_canco WHERE ID_LOAIHOSO=".$formData['ID_LOAI_MC']);
				}
				$LINHVUC = $this->getRequest()->getParam("LINHVUC"); 
				$this->_redirect('/motcua/loai/index/LINHVUC/'.$LINHVUC);
		      
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
    	 $this->_redirect('/motcua/loai/input');
    }
    /**
     * delete action
     *
     */
    function deleteAction()
    {
    	$this->view->title = "Xóa loại hồ sơ";
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/motcua/loai/","","",2);
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
						$delLoai = new LoaiModel();
	                	$where = 'ID_LOAIHOSO = ' . $idarray[$counter];
	                	$delLoai->delete($where);
						
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
					$this->_redirect('/motcua/loai/');				
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

