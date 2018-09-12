<?php

/**
 * Congviec_mainController
 *
 * @author
 * @version
 */

require_once 'Zend/Controller/Action.php';
require_once 'congviec/models/Qlgv_workModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'hscv/models/filedinhkemModel.php';

class Congviec_mainController extends Zend_Controller_Action {



	public function init() {
		$year = QLVBDHCommon::getYear();
		$this->view->title="danh sách công việc";
		$this->model = new Qlgv_workModel($year);

	}

	public function listAction(){
		//$this->view->subtitle="Danh sách công việc";
		QLVBDHButton::EnableAddNew("/congviec/main/input");
		QLVBDHButton::EnableDelete("");
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$config = Zend_Registry::get('config');
		$page= $this->_request->getParam('page');
		$limit= $this->_request->getParam('limit1');
		$param= $this->_request->getParams();
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$ISLEADER =$data_session->ISLEADER;
		$this->view->ISLEADER= $ISLEADER;
		//var_dump($ISLEADER);
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$this->view->filter_object = $filter_object;
		$combo_object = $this->_request->getParam('combo_object');
		$this->view->combo_object = $combo_object;
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
//                $this->view->data = $this->model->findByMixed($page,$limit,$search,$filter_object,$combo_object,$ISLEADER,"ID_WORK DESC");
		$this->view->data = $this->model->findByMixed2($page,$limit,$search,'3',$combo_object,$ISLEADER,"ID_WORK DESC", $data_session->ID_U);
		//$this->view->data_isnew=$this->model->getnguoixuly($id_work);
                $this->view->cv_duocgiao_chuaht = $this->model->count2('0','0');
                $this->view->cv_duocgiao_daht = $this->model->count2('1','0');
                $this->view->cv_duocgiao_trehan = $this->model->count2('2','0');
                $this->view->cv_dagiao_chuaht = $this->model->count2('0','1');
                $this->view->cv_dagiao_daht = $this->model->count2('1','1');
                $this->view->cv_dagiao_trehan = $this->model->count2('2','1');

		$n_rows = $this->model->count($search,$filter_object,$ISLEADER);
		//var_dump($n_rows);
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
		//var_dump($this->view->showPage);
		$this->view->page = $page;
		$this->view->limit = $limit;

		//QLVBDHButton::AddButton("");
	}

	public function inputAction(){
		$this->view->title="Thêm mới công việc";
		//$params = $this->_request->getParams();
		QLVBDHButton::EnableSave("/congviec/main/save");
		QLVBDHButton::EnableBack("/congviec/main/list");
		//   var_dump($params);
		$idCapNhat = $this->_request->getParam('ID_WORK');
		$id_work= $this->_request->getParam('ID_WORK');
		//    var_dump($idCapNhat);
			
		if($idCapNhat>0){
			$this->view->title="Cập nhật công việc";
			$rowcn = $this->model->find($idCapNhat)->current();
			$this->view->BEGINDATE = $rowcn->BEGINDATE;
			$this->view->ENDDATE = $rowcn->ENDDATE;
			$this->view->NAME=$rowcn->NAME;
			$this->view->DESCRIPTION=$rowcn->DESCRIPTION;
			$this->view->STATUS= $rowcn->STATUS;
			$this->view->data_nguoinhan= $this->model->getDetail1($id_work);
			$this->view->nguoigiao = $rowcn->NGUOIGIAO;
		}
		$this->view->id_work = $idCapNhat;
		//var_dump($this->view->id_work);
	}
	public function saveAction(){
		$params = $this->_request->getParams();
		//var_dump($params); exit;
		//	$ar= $params["ID_U"];

		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		$year = QLVBDHCommon::getYear();
		$model = new Qlgv_workModel($year);
		//var_dump($params);exit;
		$id=$params["ID_WORK"];
		if($id >0){
			$where = 'ID_WORK='.$id;
			if($params["STATUS"]==100){
					
		  try{ $model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
               "STATUS"=>$params["STATUS"],
               "ISFINISHED"=>1             
		  ),$where);

		  }catch (Exception  $ex){
		  	echo $ex->__toString();
		  	exit;
		  }
			}else{
			 try{ $model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
               "STATUS"=>$params["STATUS"],
			   "ISFINISHED"=>0	
			 ),$where);

			 }catch (Exception  $ex){
			 	echo $ex->__toString();
			 	exit;
			 }
			}
			/////////////////////////////////

			if($params["BEGINDATE"]!=""){
				$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
					"NAME"=>$params["NAME"],
					"DESCRIPTION"=>$params["DESCRIPTION"],
					"BEGINDATE" => implode("-",array_reverse(explode("/",$params["BEGINDATE"]))),
					"ENDDATE" => implode("-",array_reverse(explode("/",$params["ENDDATE"]))),
					"NGUOIGIAO" => $params["ID_NGUOIGIAOVIEC"],

				),$where);
			}else {
				$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
					"NAME"=>$params["NAME"],
					"DESCRIPTION"=>$params["DESCRIPTION"],
					"BEGINDATE"=>date("Y-m-d H:i:s"),
					"ENDDATE" => implode("-",array_reverse(explode("/",$params["ENDDATE"]))),
					"NGUOIGIAO" => $params["ID_NGUOIGIAOVIEC"],

				),$where);

			}
			///////////////////////////////////////////////////////////



			if(count($params["ID_U"])>0){
				$model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_listexecuser'),"ID_WORK=".(int)$id." AND ID_U not in (".implode(",",$params["ID_U"]).")");
			}
			if(count($params["ID_U"])>0){
				foreach($params["ID_U"] as $idu){
					$r = $model->getDefaultAdapter()->query("SELECT * FROM ".QLVBDHCommon::Table('qlgv_listexecuser')." where id_work=? and id_u=?",
					array($id,$idu));
					if($r->rowCount()==1){
						$where= "ID_WORK=".$id;
						$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_listexecuser'),array("ISMAIN"=>0),$where);
					}else{
						$model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_listexecuser'),
						array(
	          		"ID_WORK"=>$id,
	          		"ID_U"=>$idu,
	          		"ISMAIN"=>0
						));
					}

				}
			}

			////////////////////////////////////

			$u_main=$params["ISMAIN"];
			if(count($u_main)!=0){
					
				$where_update_main = "ID_WORK=$id and ID_U=$u_main ";
				$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_listexecuser'),
				array("ISMAIN"=>1) ,$where_update_main);

			}
			$fileModel = new filedinhkemModel($year);
			for($i=0;$i<count($params["idFile"]);$i++){
				$fileModel->update(array("ID_OBJECT"=>$id,"TYPE"=>11),"MASO='".$params["idFile"][$i]."'");
			}

		}else{

			if($params["BEGINDATE"]!=""){
				$model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_work'),array(
					"NAME"=>$params["NAME"],
					"DESCRIPTION"=>$params["DESCRIPTION"],
					"BEGINDATE" => implode("-",array_reverse(explode("/",$params["BEGINDATE"]))),
					"ENDDATE" => implode("-",array_reverse(explode("/",$params["ENDDATE"]))),
					"NGUOITAO"=>$id_u,
					"NGUOIGIAO" => $params["ID_NGUOIGIAOVIEC"],

				));
			}else {
				$model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_work'),array(
					"NAME"=>$params["NAME"],
					"DESCRIPTION"=>$params["DESCRIPTION"],
					"BEGINDATE"=>date("Y-m-d H:i:s"),
					"ENDDATE" => implode("-",array_reverse(explode("/",$params["ENDDATE"]))),
					"NGUOITAO"=>$id_u,
					"NGUOIGIAO" => $params["ID_NGUOIGIAOVIEC"],
				));

			}

			$id = $model->getDefaultAdapter()->lastInsertId(QLVBDHCommon::Table('qlgv_work'));
			if(count($params["ID_U"])>0){
				//	var_dump($params["ID_U"]);exit;
				for($i=0;$i<count($params["ID_U"]);$i++)
				{

					$model->getDefaultAdapter()->insert(QLVBDHCommon::Table('qlgv_listexecuser'),array(
		        "ID_WORK"=>$id,
		        "ID_U"=>$params["ID_U"][$i],

					));
				}
			}
			$u_main=$params["ISMAIN"];

			if(count($u_main)!=0){
					
				$where_update_main = "ID_WORK=$id and ID_U=$u_main ";
				$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_listexecuser'),
				array("ISMAIN"=>1) ,$where_update_main);

			}
			$fileModel = new filedinhkemModel($year);
		 for($i=0;$i<count($params["idFile"]);$i++){

		 	$fileModel->update(array("ID_OBJECT"=>$id,"TYPE"=>11),"MASO='".$params["idFile"][$i]."'");
		 }
		}


		//($params["ISMAIN"][$i]==$params["ID_U"][$i]?1:0);
		echo 1;
		exit;
	}
	public function tiendoAction(){
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$model = new Qlgv_workModel($year);
		$id=$params["ID_WORK"];
		if($id >0){
			$where = 'ID_WORK='.$id;
			if($params["STATUS"]==100){
					
		  try{ $model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
               "STATUS"=>$params["STATUS"],
               "ISFINISHED"=>1,
		       "FINISHDATE"=>date("Y-m-d H:i:s") 
		                   
		  ),$where);

		  }catch (Exception  $ex){
		  	echo $ex->__toString();
		  	exit;
		  }
			}else{
			 try{ $model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
               "STATUS"=>$params["STATUS"]
			 	
			 ),$where);

			 }catch (Exception  $ex){
			 	echo $ex->__toString();
			 	exit;
			 }
			}
			echo 1;
			exit;
		}
		///////////////////////////////////////////////////////////

	}
	public function setcongviecAction(){
		$year = QLVBDHCommon::getYear();
		$model=new Qlgv_workModel($year);
		$params = $this->_request->getParams();

		$id= $params['ID_WORK'];

		$ngay=$params['ENDDATE'];

		$freedate = new Zend_Session_Namespace('freedate');
		$free = $freedate1->free;
		$delay = QLVBDHCommon::countdate($ngay,$free);
			
		//  if($delay>0){

		//  	echo  " <font color=red><i>(Trễ ".(floor($delay/8)>0?floor($delay/8)." ngày ":"").($delay%8)." giờ)</i></font>";
		//  }

		$where = 'ID_WORK='.$id;
		$model->getDefaultAdapter()->update(QLVBDHCommon::Table('qlgv_work'),array(
					"ISFINISHED"=>1,
					"STATUS"=>100,
					"FINISHDATE"=>date("Y-m-d H:i:s"),
		            "TREDATE"=>(int)$delay
		),$where);
		echo 1;
		exit;
	}
	public function detailAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$id_work = $params["ID_WORK"];
		$this->view->data = Qlgv_workModel::getDetail($year,$id_work);
		//	var_dump($this->view->data);
		$this->view->data1 = Qlgv_workModel::getnguoixuly($id_work);


	}
	function updatedadocAction(){
		$params = $this->_request->getParams();
		$id_work = $params['id_work'];
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		$is_nguoitao = $params["is_nguoitao"];
		$ln = 0;
		if($is_nguoitao ==1)
		Qlgv_workModel::updateisnew($id_work);
		Qlgv_workModel::updateUserReceive($id_work,$id_u);
		echo 1;
		exit;
	}

	public function deleteAction()
	{
		$year = QLVBDHCommon::getYear();
		$model = new Qlgv_workModel($year);

		if($this->_request->isPost())
		{
			//Lay id cua cac linh vuc van ban can xoa
			$idarray = $this->_request->getParam('DEL');
			//var_dump($idarray );
			//Thuc hien xoa cac linh vuc van ban duoc chon boi user
			$where = 'ID_WORK in ('.implode(',',$idarray).')';

			try{
				$model->delete($where);
				$model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_journal'),$where);
				$model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_remark'),$where);
				$model->getDefaultAdapter()->delete(QLVBDHCommon::Table('qlgv_listexecuser'),$where);
			} catch (Exception  $ex)
			{ echo $ex->__toString();
			exit;

			}


		}
		else
		{

		}

		$this->_redirect('/congviec/main/list');

	}


}