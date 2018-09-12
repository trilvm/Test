<?php

/**
 * Congviec_mainController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'congviec/models/Qlgv_gopyModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'qtht/models/UsersModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'congviec/models/Qlgv_workModel.php';

class Congviec_noidunggopyController extends Zend_Controller_Action {
	

	
	public function init() {
	    $this->_helper->layout->disableLayout();
		$this->view->title="Nội dung góp ý ";
		$year = QLVBDHCommon::getYear();
		$this->model = new Qlgv_gopyModel($year);
		
		
	}
	
	public function indexAction(){
		
		$this->_helper->layout->disableLayout();		
		$params = $this->_request->getParams();		
		$id_work = $params["ID_WORK"];		
		$this->view->data = Qlgv_gopyModel::getAll($id_work);	    		 
	    $this->view->id_work = $id_work;
		
	}
   public function inputAction(){ 
		//$this->view->subtitle="Thêm mới";
		//$this->view->subtitle="Thêm mới";
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$idCapNhat = $this->_request->getParam('ID_JOURNAL');
	//	var_dump($idCapNhat);
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
		
			
	}
   public function saveAction(){	
		//	$id = $this->_request->getParam('ID_JOURNAL');
		$params = $this->_request->getParams();
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		$username =$data_session->USERNAME; 
		$year = QLVBDHCommon::getYear();
		//$year = QLVBDHCommon::getYear();
		//$model = new Qlgv_ykienModel($year);
		$id=$params["ID_JOURNAL"];	
	
		 if($id > 0){
               $where = 'ID_JOURNAL='.$id;
               try{ $this->model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_journal'),array(
               "CONTENT"=>$params["CONTENT"]
              
               
                              ),$where);
               	
               }catch (Exception  $ex){
               	$this->_redirect('/default/error/error?control=noidungyk&mod=congviec&id=ERR11004006');
               }
               $fileModel = new filedinhkemModel($year);
		         for($i=0;$i<count($params["idFile"]);$i++){   
			            $fileModel->update(array("ID_OBJECT"=>$id,"TYPE"=>12),"MASO='".$params["idFile"][$i]."'");
			        }
               
		 }else{
		 
		$this->model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_journal'),array(
		"CONTENT"=>$params["CONTENT"],
		"TIME"=>date("Y-m-d H:i:s"),
		"ID_WORK"=>$params["ID_WORK"],
		"NGUOINK"=>$id_u
		
		));
		$id = $this->model->getDefaultAdapter()->lastInsertId(QLVBDHCommon::Table('qlgv_journal'));
	  // var_dump($id );
		 }
		 
		 $fileModel = new filedinhkemModel($year);
		 for($i=0;$i<count($params["idFile"]);$i++){   
			     $fileModel->update(array("ID_OBJECT"=>$id,"TYPE"=>12),"MASO='".$params["idFile"][$i]."'");
		 }
		  echo "<script>
     	var url='/congviec/noidunggopy/index?ID_WORK=".$params["ID_WORK"]."';
		window.parent.loadDivFromUrl('groupcontent".$params["ID_WORK"]."',url,1);
		//alert(window.parent.document.getElementById('Noidungnk".$params["ID_WORK"]."').innerHTML);
		window.parent.document.getElementById('Noidungnk".$params["ID_WORK"]."').innerHTML='(".Qlgv_workModel::countnoidung($params["ID_WORK"]).")';
		</script>";
		  
		exit;
	}
	
	public function deleteAction(){
	 	$params = $this->_request->getParams();
	 	$iddel=$params["ID_JOURNAL"];	
	  	
	 	$where='ID_JOURNAL='.$iddel;
	 	$where1='ID_OBJECT='.$iddel;
	 	try{ 
	 		$this->model->getDefaultAdapter()->delete(QLVBDHCommon::Table('gen_filedinhkem'),$where1);
	 		$this->model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_journal'),$where);
	 		
	 	}catch (Exception $ex){
	 		echo $ex->__toString();
	 	}
	 //	$iddel1=$params[""];
	 	 echo "<script>
     	var url='/congviec/noidunggopy/index?ID_WORK=".$params["ID_WORK"]."';
		window.parent.loadDivFromUrl('groupcontent".$params["ID_WORK"]."',url,1);
		window.parent.document.getElementById('Noidungnk".$params["ID_WORK"]."').innerHTML='(".Qlgv_workModel::countnoidung($params["ID_WORK"]).")';
		</script>";
	 	exit;
	 	
	 }
	
	
	

}
?>

