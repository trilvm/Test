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
require_once 'qtht/models/GoiyModel.php';
require_once 'Zend/Controller/Action.php';
/**
 * MenusController is class to control user management system with add,edit,delete
 * 
 * @author truongvc
 * @version 1.0
 */
class Qtht_GoiyController extends Zend_Controller_Action 
{
	
	/**
	 * This is index action for module /qtht/menus
	 *
	 */
	function indexAction()
    {
		$model = new GoiyModel();
    	$this->view->title="Gợi ý bút phê";
    	//$this->view->subtitle="Danh sách";
		$this->view->data = $model->SelectAll(0,0,"");
		QLVBDHButton::EnableDelete("/qtht/goiy/delete");
		QLVBDHButton::EnableAddNew("/qtht/goiy/input");
	}
	/*
     * This fuction is input action
     */
	function inputAction()
	{
		$start = (float) array_sum(explode(' ',microtime()));
		//L?y parameter
		$parameter = $this->getRequest()->getParams();
		$id = $parameter["id"];
		$error=$this->_request->getParam('error');
		$this->view->error=$error;
		 

		//New cac model
		$this->goiy = new GoiyModel();

		//L?y d? li?u
		if($id>0){
			$this->view->data = $this->goiy->FindById($id);
			$this->view->title = "Cập nhật gợi ý bút phê";
			//$this->view->subtitle = "Cập nhật";
			$this->view->id=$id;
		}else{
			$this->view->title = "Thêm mới gợi ý bút phê";
			//$this->view->subtitle = "Thêm mới";
		}

		//Set bi?n cho view
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		//QLVBDHButton::EnableHelp("");
	}
	/*
     * This fuction is save action
     */
	function saveAction()
    {
    	$goiy = new GoiyModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		
		try
		{
			if($this->view->parameter["id"]>0)
			{
				$goiy->update(array("NOIDUNG"=>$this->view->parameter["NOIDUNG"]),"ID_GOIY=".(int)$this->view->parameter["id"]);
			}
			else
			{
				$goiy->insert(array("NOIDUNG"=>$this->view->parameter["NOIDUNG"]));
			}
		}
		catch(Exception $ex)
		{
		}
		$this->_redirect("/qtht/goiy");
    }
    
    /*
     * This is delete action for module /qtht/menus/
     * @return void
     */   
    function deleteAction()
    {
    	$this->goiy = new GoiyModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try
		{
			$this->goiy->delete("ID_GOIY IN (".implode(",",$this->view->parameter["DEL"]).")");
		}
		catch(Exception $ex)
		{
			//$this->_redirect("/default/error/error?control=danhmucnguoidung&mod=qtht&id=ERR11001001");
		}
		$this->_redirect("/qtht/goiy");
    }
  
}

