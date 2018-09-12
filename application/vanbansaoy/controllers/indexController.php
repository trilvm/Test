<?php

/**
 * Vbsaoy_indexController
 *
 * @author
 * @version
 */

require_once 'Zend/Controller/Action.php';
require_once 'vanbansaoy/models/vanbansaoyModel.php';
require_once 'Common/ValidateInputData.php';
require_once 'hscv/models/hosocongviecModel.php';


class Vanbansaoy_IndexController extends Zend_Controller_Action {



	public function init() {
		$year = QLVBDHCommon::getYear();
		$this->view->title="danh sách văn bản sao y";
		$this->model = new vanbansaoyModel();

	}
        public function indexAction() {
		$year = QLVBDHCommon::getYear();
		$this->view->title="danh sách văn bản sao y";
		$this->model = new vanbansaoyModel();

	}

	public function listAction(){
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$ISLEADER =$data_session->ISLEADER;
		$this->view->ISLEADER= $ISLEADER;
                $this->view->title="Danh sách văn bản sao y";
		if($ISLEADER==1) QLVBDHButton::EnableAddNew("/vanbansaoy/index/input","Thêm mới đề nghị sao y");
                else QLVBDHButton::EnableAddNew("/vanbansaoy/index/input","Thêm mới yêu cầu sao y");
		QLVBDHButton::EnableDelete("");
                QLVBDHButton::AddButton('In danh sách sao y', '', 'PrintButton','danhsachsaoy()');
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$config = Zend_Registry::get('config');
		$page= $this->_request->getParam('page');
		$limit= $this->_request->getParam('limit1');
		$param= $this->_request->getParams();
		
		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$this->view->filter_object = $filter_object;
		$combo_object = $this->_request->getParam('combo_object');
		$this->view->combo_object = $combo_object;
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
                $this->view->data = $this->model->findByMixed($page,$limit,$search,$ISLEADER,"ID_VBSY DESC");		
		//$this->view->data_isnew=$this->model->getnguoixuly($ID_VBSY);                

		$n_rows = $this->model->count($search,$filter_object,$ISLEADER);
		//var_dump($n_rows);
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
		//var_dump($this->view->showPage);
		$this->view->page = $page;
		$this->view->limit = $limit;

		//QLVBDHButton::AddButton("");
	}

	public function inputAction(){		
		//$params = $this->_request->getParams();
		QLVBDHButton::EnableSave("/vanbansaoy/index/save");
		QLVBDHButton::EnableBack("/vanbansaoy/index/list");
		//   var_dump($params);
		$idCapNhat = $this->_request->getParam('ID_VBSY');
		$ID_VBSY= $this->_request->getParam('ID_VBSY');
		//    var_dump($idCapNhat);
                $auth = Zend_Registry::get('auth');
                $data_session = $auth->getIdentity();
                $this->view->IDU = $data_session->ID_U;
		$ISLEADER =$data_session->ISLEADER;
		$this->view->ISLEADER= $ISLEADER;
                if ($ISLEADER==1) $this->view->title="Thêm mới đề nghị sao y";
                else $this->view->title="Thêm mới yêu cầu sao y";
		if($idCapNhat>0){
			if ($ISLEADER==1) $this->view->title="Cập nhật đề nghị sao y";
                        else $this->view->title="Cập nhật yêu cầu sao y";
			$rowcn = $this->model->find($idCapNhat)->current();			
			$this->view->KYHIEUVANBANSAOY=$rowcn->KYHIEUVANBANSAOY;
                        $this->view->LYDO=$rowcn->LYDO;
			$this->view->TRICHYEU=$rowcn->TRICHYEU;
                        $this->view->IDU=$rowcn->NGUOIDENGHI;
			$this->view->data_nguoinhan= $this->model->getDetail1($ID_VBSY);

		}
		$this->view->ID_VBSY = $idCapNhat;
		//var_dump($this->view->ID_VBSY);
	}
	public function saveAction(){
		$params = $this->_request->getParams();		

		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
                $ISLEADER =$data_session->ISLEADER;
		$year = QLVBDHCommon::getYear();
		$model = new vanbansaoyModel();
		//var_dump($params);exit;
		$id=$params["ID_VBSY"];
                if ($this->_request->isPost()) {
		if($id >0){
			$where = 'ID_VBSY='.$id;
                        // tuanpp Update Vanbansaoy
                        if($ISLEADER==1) {
				$model->getDefaultAdapter()->update("vbsaoy_vanbansaoy",array(
					"KYHIEUVANBANSAOY"=>$params["KYHIEUVANBANSAOY"],
					"TRICHYEU"=>$params["TRICHYEU"],
                                        "LYDO"=>$params["LYDO"],
                                        "ISFINISHED"=>1,
                                        "NGUOIDENGHI"=>$params["NGUOIDENGHI"],
                                        "ID_VBD"=>$params["ID_VBD"]
				),$where);
                        }
                        else {
                            $model->getDefaultAdapter()->update("vbsaoy_vanbansaoy",array(
					"KYHIEUVANBANSAOY"=>$params["KYHIEUVANBANSAOY"],
					"TRICHYEU"=>$params["TRICHYEU"],
                                        "LYDO"=>$params["LYDO"],
                                        "ID_VBD"=>$params["ID_VBD"]
				),$where);
                        }
//                                tuanpp xoa list ID Nguoi nhan cũ
			if(count($params["ID_U"])>0){
				$model->getDefaultAdapter()->delete('vbsaoy_pheduyet',"ID_VBSY=".(int)$id." AND ID_U not in (".implode(",",$params["ID_U"]).")");
			}
//                                tuanpp them ID Nguoi nhan moi
			if(count($params["ID_U"])>0){
				foreach($params["ID_U"] as $idu){
					$r = $model->getDefaultAdapter()->query("SELECT * FROM vbsaoy_pheduyet where ID_VBSY=? and id_u=?",
					array($id,$idu));
					if($r->rowCount()==1){
					}else{
						$model->getDefaultAdapter()->insert('vbsaoy_pheduyet',
						array(
	          		"ID_VBSY"=>$id,
	          		"ID_U"=>$idu,	          		
						));
					}

				}
			}
			
		}else{
//                       tuanpp Thêm mới VBSY
                    if($ISLEADER==1) {
				$model->getDefaultAdapter()->insert("vbsaoy_vanbansaoy",array(
					"KYHIEUVANBANSAOY"=>$params["KYHIEUVANBANSAOY"],
					"TRICHYEU"=>$params["TRICHYEU"],
					"LYDO"=>$params["LYDO"],					
					"NGUOITAO"=>$id_u,
                                        "ISFINISHED"=>1,
                                        "ISNEW"=>1,
                                        "NGUOIDENGHI"=>$params["NGUOIDENGHI"],
                                        "NGAYTAO"=>date("Y-m-d"),
                                        "ID_VBD"=>$params["ID_VBD"]

				));
                    }
                    else {
                        $model->getDefaultAdapter()->insert("vbsaoy_vanbansaoy",array(
					"KYHIEUVANBANSAOY"=>$params["KYHIEUVANBANSAOY"],
					"TRICHYEU"=>$params["TRICHYEU"],
					"LYDO"=>$params["LYDO"],
					"NGUOITAO"=>$id_u,
                                        "ISFINISHED"=>0,
                                        "ISNEW"=>1,
                                        "NGUOIDENGHI"=>$id_u,
                                        "NGAYTAO"=>date("Y-m-d"),
                                        "ID_VBD"=>$params["ID_VBD"]
                            ));
                    }

			
//                                tuanpp Thêm danh sách người nhận
			$id = $model->getDefaultAdapter()->lastInsertId("vbsaoy_vanbansaoy");
			if(count($params["ID_U"])>0){
				//	var_dump($params["ID_U"]);exit;
				for($i=0;$i<count($params["ID_U"]);$i++)
				{

					$model->getDefaultAdapter()->insert('vbsaoy_pheduyet',array(
		        "ID_VBSY"=>$id,
		        "ID_U"=>$params["ID_U"][$i],

					));
				}
			}
                        
		}

		
		echo 1;}                
                else $this->_redirect('/vanbansaoy/index/list');
		exit;
	}
	public function gettyAction(){
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$model = new vanbansaoyModel();
		$result = $model->GetTrichYeu($params['ID_VBD']);
		echo $result;
		exit;				

	}
	public function setpheduyetAction(){
		$year = QLVBDHCommon::getYear();
		$model=new vanbansaoyModel();
		$params = $this->_request->getParams();

		$id= $params['ID_VBSY'];	
		$where = 'ID_VBSY='.$id;
		$model->getDefaultAdapter()->update("vbsaoy_vanbansaoy",array(
					"ISFINISHED"=>1,												            
		),$where);
                //tuanpp gui cho van thu
                
		echo 1;
		exit;
	}
	public function detailAction(){
		$this->_helper->Layout->disableLayout();
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$ID_VBSY = $params["ID_VBSY"];
		$this->view->data = vanbansaoyModel::getDetail($year,$ID_VBSY);
		//	var_dump($this->view->data);
		$this->view->data1 = vanbansaoyModel::getnguoixuly($ID_VBSY);


	}
	function updatedadocAction(){
		$params = $this->_request->getParams();
		$ID_VBSY = $params['ID_VBSY'];
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		$is_nguoitao = $params["is_nguoitao"];
		$ln = 0;
		if($is_nguoitao ==1)
		vanbansaoyModel::updateisnew($ID_VBSY);
		vanbansaoyModel::updateUserReceive($ID_VBSY,$id_u);
		echo 1;
		exit;
	}

	public function deleteAction()
	{
		$year = QLVBDHCommon::getYear();
		$model = new vanbansaoyModel();

		if($this->_request->isPost())
		{
			//Lay id cua cac linh vuc van ban can xoa
			$idarray = $this->_request->getParam('DEL');
			//var_dump($idarray );
			//Thuc hien xoa cac linh vuc van ban duoc chon boi user
			$where = 'ID_VBSY in ('.implode(',',$idarray).')';

			try{
				$model->delete($where);				
				$model->getDefaultAdapter()->delete('vbsaoy_pheduyet',$where);
			} catch (Exception  $ex)
			{ echo $ex->__toString();
			exit;

			}


		}
		else
		{

		}

		$this->_redirect('/vanbansaoy/index/list');

	}
      public function inAction()
      {   
          $this->_helper->Layout->disableLayout();
          $params = $this->_request->getParams();
          $ID_VBSY = $params['ID_VBSY'];
          $db_vb= new vanbansaoyModel();
          $data= $db_vb->GetDetailSaoYById($ID_VBSY);
          $this->view->data=$data;
          //var_dump($data);exit;

      }
      
      public function danhsachsaoyAction()
      {
                $auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$ISLEADER =$data_session->ISLEADER;
		$this->view->ISLEADER= $ISLEADER;
                $this->view->title="Danh sách văn bản sao y";
                QLVBDHButton::AddButton('In danh sách sao y', '', 'PrintButton','indssy()');
                QLVBDHButton::EnableBack('/vanbansaoy/index/list');
		$params = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$config = Zend_Registry::get('config');
		$page= $this->_request->getParam('page');
		$limit= $this->_request->getParam('limit1');
		$param= $this->_request->getParams();
		
		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$this->view->filter_object = $filter_object;
		$combo_object = $this->_request->getParam('combo_object');
		$this->view->combo_object = $combo_object;
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
                $this->view->data = $this->model->findByMixed($page,$limit,$search,$ISLEADER,"ID_VBSY DESC");		
		//$this->view->data_isnew=$this->model->getnguoixuly($ID_VBSY);                

		$n_rows = $this->model->count($search,$filter_object,$ISLEADER);
		//var_dump($n_rows);
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
		//var_dump($this->view->showPage);
		$this->view->page = $page;
		$this->view->limit = $limit;

		//QLVBDHButton::AddButton("");
      }
      public function indssyAction()
      {   
          $this->_helper->Layout->disableLayout();
          $params = $this->_request->getParams();
          $array_id= $params['CHECK'];
          $db_vb= new vanbansaoyModel();
          $data= $db_vb->GetDetailSaoYByArrayId($array_id);
          $this->view->data=$data;
       //   var_dump($data);
		
      }


}