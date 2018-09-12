<?php

/**
 * Congviec_mainController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'congviec/models/Qlgv_ykienModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'qtht/models/UsersModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'congviec/models/Qlgv_workModel.php';

class Congviec_noidungykController extends Zend_Controller_Action {
	

	
	public function init() {
	    $this->_helper->layout->disableLayout();
		$this->view->title="Nội dung ý kiến của lãnh đạo";
		$year = QLVBDHCommon::getYear();
		$this->model = new Qlgv_ykienModel($year);
		
		
	}
	
	public function indexAction(){
		
		$this->_helper->layout->disableLayout();
		global $db;		
		$params = $this->_request->getParams();
		
		$id_work = $params["ID_WORK"];
		$id_remark= $params["ID_REMARK"];
		
		$this->view->data = Qlgv_ykienModel::getAll($id_work);
		
	    		 
	    $this->view->id_work = $id_work;
		
	}
	
	public function inputAction(){ 
		//$this->view->subtitle="Thêm mới";
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$idCapNhat = $this->_request->getParam('ID_REMARK');
		if($idCapNhat>0)
		{
			$rowcn = $this->model->find($idCapNhat)->current();
			 
				
			
				$this->view->content = $rowcn->CONTENT;
			    $this->view->TIME = $rowcn->TIME;
				
				$this->view->ID_REMARK= $idCapNhat;
			
		}
		$id_work = $params["ID_WORK"];
		$this->view->id_work = $id_work;
		$this->view->id=$idCapNhat;
		//$this->renderScript("noidungyk/input.phtml");
		
	}

	
	public function saveAction(){		
		$params = $this->_request->getParams();
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		$username =$data_session->USERNAME; 
		$year = QLVBDHCommon::getYear();
		//$year = QLVBDHCommon::getYear();
		//$model = new Qlgv_ykienModel($year);
		$id=$params["ID_REMARK"];	
		 
		 if($id > 0){		 	   
               $where = 'ID_REMARK='.$id;
               try{ $this->model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_remark'),array(
               "CONTENT"=>$params["CONTENT"],
               "TIME"=> date("Y-m-d H:i:s")               
               ),$where);
               	
               }catch (Exception  $ex){
               	$this->_redirect('/default/error/error?control=noidungyk&mod=congviec&id=ERR11004006');
               }
               
		 }else{
		 
		$this->model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_remark'),array(
		"CONTENT"=>$params["CONTENT"],
		"TIME"=>date("Y-m-d H:i:s"),
		"ID_WORK"=>$params["ID_WORK"],
		"NGUOIGOPY"=>$id_u		
		));
		//$id = $this->model->getDefaultAdapter()->lastInsertId(QLVBDHCommon::Table('qlgv_remark'));
	   
		 }		 
		echo "<script>
     	var url='/congviec/noidungyk/index?ID_WORK=".$params["ID_WORK"]."';
		window.parent.loadDivFromUrl('groupcontent".$params["ID_WORK"]."',url,1);
		window.parent.document.getElementById('Noidungyk".$params["ID_WORK"]."').innerHTML='(".Qlgv_workModel::countnoidungyk($params["ID_WORK"]).")';
		</script>";
		exit;
	}
	
	 public function deleteAction(){
	 	$params = $this->_request->getParams();
	 	$iddel=$params["ID_REMARK"];
	 
	    $where='ID_REMARK='.$iddel;	 
	 	try{ 	 		
	 		$this->model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_remark'),$where);
	 		
	 	}catch (Exception $ex){
	 		//array();
	 		
	 		echo $ex->__toString();
	 	}
	 //	$iddel1=$params[""];
	 	 echo "<script>
     	var url='/congviec/noidungyk/index?ID_WORK=".$params["ID_WORK"]."';
		window.parent.loadDivFromUrl('groupcontent".$params["ID_WORK"]."',url,1);
		window.parent.document.getElementById('Noidungyk".$params["ID_WORK"]."').innerHTML='(".Qlgv_workModel::countnoidungyk($params["ID_WORK"]).")';
		
		</script>";
	 	exit;
	 	
	 }
	

}
?>

