<?php

/**
 * isoController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
include_once 'iso/models/iso.php';
include_once 'hscv/models/filedinhkemModel.php';
include_once 'auth/models/ResourceUserModel.php';

class Iso_IsoController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated isoController::indexAction() default action
	}
	public function listAction(){
		global $auth;
		$user = $auth->getIdentity();
		$iso = new iso();
		$data = array();
		$year = $this->getRequest()->getParam("YEAR1");
		$year = $year==""?'9001-2008':$year;
		QLVBDHCommon::GetTreeWithWhere(&$data,"ISO_ITEM","ID_ITEM","ID_ITEM_PARENT",1,1,"YEAR='".$year."'");
		
		$this->view->data =$data;
		$this->view->title="Tài liệu ISO";
		$this->view->subtitle="Danh sách";
		$this->view->bug = $this->getRequest()->getParam("bug");
		$this->view->lock = 1;
		$this->view->year=$year;
		$actid = ResourceUserModel::getActionByUrl('iso','iso','input');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){	
			$this->view->lock = 0;
			QLVBDHButton::EnableDelete("/iso/iso/Delete");
			QLVBDHButton::EnableAddNew("/iso/iso/Input");
		}
	}
	public function inputAction(){
		$this->view->title="Tài liệu ISO";
		$params = $this->getRequest()->getParams();
		if($params["id"]>0){
			$iso = new iso();
			$this->view->subtitle = "Cập nhật";
			$this->view->data = $iso->find($params["id"])->current();
		}else{
			$this->view->subtitle = "Thêm mới";
		}
		$this->view->year = $params["YEAR"];
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		QLVBDHButton::EnableHelp("");
	}
	public function saveAction(){
		$params = $this->getRequest()->getParams();
		if($params["ID_ITEM"]>0){
			$iso = new iso();
			$olddata = $iso->find($params["ID_ITEM"])->current();
			$re = filedinhkemModel::insertFileIso($olddata->FILE_MASO);
			if($re[0]!=""){
				$iso->update(
					array(
						"ID_ITEM_PARENT"=>$params["ID_ITEM_PARENT"],
						"NAME"=>$params["NAME"],
						"MAHIEU"=>$params["MAHIEU"],
						"GHICHU"=>$params["GHICHU"],
						"FILE_MASO"=>$re[0],
						"FILE_NAME"=>$re[2],
						"FILE_MIME"=>$re[1],
						"YEAR"=>$params["YEAR"]
					),
					"ID_ITEM=".((int)$params["ID_ITEM"])
				);
			}else{
				$iso->update(
					array(
						"ID_ITEM_PARENT"=>$params["ID_ITEM_PARENT"],
						"NAME"=>$params["NAME"],
						"MAHIEU"=>$params["MAHIEU"],
						"GHICHU"=>$params["GHICHU"],
						"YEAR"=>$params["YEAR"]
					),
					"ID_ITEM=".((int)$params["ID_ITEM"])
				);
			}
			//echo $re;
		}else{
			$iso = new iso();
			$re = filedinhkemModel::insertFileIso("");
			$id = $iso->insert(
				array(
					"ID_ITEM_PARENT"=>$params["ID_ITEM_PARENT"],
					"NAME"=>$params["NAME"],
					"MAHIEU"=>$params["MAHIEU"],
					"GHICHU"=>$params["GHICHU"],
					"FILE_MASO"=>$re[0],
					"FILE_NAME"=>$re[2],
					"FILE_MIME"=>$re[1],
					"YEAR"=>$params["YEAR"]
				)
			);
		}
		$this->_redirect("/iso/iso/list/YEAR1/".$params["YEAR"]);
	}
	public function tempAction(){
		$this->view->title="Tài liệu ISO";
		$this->view->subtitle="Danh sách";
	}
	public function downloadAction(){
		$con = Zend_Registry::get('config');
		$id = $this->_request->getParam('id');
		$iso = new iso();
		$data = $iso->find($id)->current();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$data->FILE_MIME ); 
		header( 'Content-Disposition: attachment; filename="'.$data->FILE_NAME.'"' ); 
		header( "Content-Description: Excel output" );
		echo file_get_contents($con->file->root_dir.DIRECTORY_SEPARATOR."iso".DIRECTORY_SEPARATOR.$data->FILE_MASO); 
		exit;
	}
	public function deleteAction(){
		$iso = new iso();
		$id = $this->_request->getParam('DEL');
		try{
			$iso->delete("ID_ITEM IN (".implode(",",$id).")");
		}catch(Exception $ex){
			$this->_redirect("/iso/iso/list/bug/1");
		}
		$this->_redirect("/iso/iso/list");
	}
}
