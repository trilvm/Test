<?php

require_once 'Zend/Controller/Action.php';
include_once 'thongbao/models/thongbao.php';
include_once 'hscv/models/filedinhkemModel.php';
class Thongbao_IndexController extends Zend_Controller_Action{
	public function indexAction(){
        // Lấy parameter
        $config = Zend_Registry::get('config');
        $parameter = $this->getRequest()->getParams();
        $limit = $parameter["limit"];
        $page = $parameter["page"];
        $search = $parameter["search"];
        $filter_object = $parameter["filter_object"];
        
        // Tinh chỉnh parameter
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;
        if($filter_object==0 || $filter_object=="")$filter_object=0;

		//maindata
		$thongbao = new thongbao();
	
		$rowcount = $thongbao->Count();
        $this->view->data = $thongbao->SelectAll(($page-1)*$limit,$limit,"hieuluc desc,ngaytao desc");

		//view data
		$this->view->title = "Danh sách thông báo";
		//$this->view->subtitle = "danh sách";
		QLVBDHButton::EnableDelete("/thongbao/index/Delete");
		QLVBDHButton::EnableAddNew("/thongbao/index/Input");

		$this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;
	}
	public function inputAction(){
		global $db;
		$params = $this->getRequest()->getParams();
		$limit = $params["limit"];
        $page = $params["page"];
        $search = $params["search"];
        $filter_object = $params["filter_object"];
        $id = $params["id"];

		if($params["id"]>0){
			$this->view->data = $db->query("SELECT * FROM TB_THONGBAO WHERE ID_TB=".(int)$params["id"])->fetch();
			$this->view->title = "Cập nhật thông báo";
			//$this->view->subtitle = "cập nhật";
                        $this->view->idTemp =$id;
                        $this->view->type=2016;
		}else{
			$this->view->title = "Thêm mới thông báo";
			//$this->view->subtitle = "thêm mới";
		}

		// Set biến cho view
        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->filter_object = $filter_object;

		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
	}
	public function saveAction(){
		global $db;
		$params = $this->getRequest()->getParams();
		$limit = $params["limit"];
        $page = $params["page"];
        $search = $params["search"];
        $filter_object = $params["filter_object"];
                $year=QLVBDHCommon::getYear();
                $arrayIdFile=$params['idFile'];
                $filedinhkemModel=new filedinhkemModel($year); 
		global $auth;
		$user = $auth->getIdentity();
		
		$ngayketthuc = implode("-",array_reverse(explode("/",$params["NGAYKETTHUC"])));
		$ngaybatdau = implode("-",array_reverse(explode("/",$params["NGAYBATDAU"])));
		$ngaytao = date("Y-m-d H:i:s",time());
		
		$thongbao = new thongbao();

		if($params["id"]>0){
			$thongbao->update(
				array(
					"TIEUDE"=>$params["TIEUDE"],
					"NOIDUNG"=>$params["NOIDUNG"],
					"NGAYKETTHUC"=>$ngayketthuc,
					"NGUOITAO"=>$user->ID_U,
					"NGAYBATDAU"=>$ngaybatdau,
					"NGAYTAO"=>$ngaytao
				),
				"ID_TB=".(int)$params["id"]
			);
		}else{
			$id=$thongbao->insert(
				array(
					"TIEUDE"=>$params["TIEUDE"],
					"NOIDUNG"=>$params["NOIDUNG"],
					"NGAYKETTHUC"=>$ngayketthuc,
					"NGUOITAO"=>$user->ID_U,
					"NGAYBATDAU"=>$ngaybatdau,
					"NGAYTAO"=>$ngaytao
				)
			);
                        //Save attachment files
	        	if(count($arrayIdFile)>0)
	           	{	           	
		           	for($i=0;$i<count($arrayIdFile);$i++)
		        	{   
	                	$filedinhkemModel->update(array("ID_OBJECT"=>$id,"TYPE"=>2016),"MASO='".$arrayIdFile[$i]."'");				  
		        	}
	           	}
		}
		$this->_redirect("/thongbao/index/index/page/$page/limit/$limit");
		exit;
	}
	public function deleteAction(){
		$iso = new thongbao();
		$id = $this->_request->getParam('DEL');
		try{
			$iso->delete("ID_TB IN (".implode(",",$id).")");
		}catch(Exception $ex){
			
		}
		$this->_redirect("/thongbao/index/index");
	}
	public function getAction(){
		$params = $this->getRequest()->getParams();
		$thongbao = new thongbao();
                $year=QLVBDHCommon::getYear();
                $filedinhkemModel=new filedinhkemModel($year); 
		//$params["id"]=0;
		if($params["id"]>0){
			$data = $thongbao->SelectById($params["id"]);
			echo "
			<table width=100% cellspacing=0 cellpadding = 0 border = 0>
			<tr><td class=thongbaotitle>".htmlspecialchars($data["TIEUDE"])."<img class=jqmClose style='float:right' width=20 height=20 src='/images/icon/icon-DeleteButton1.png' align=absmiddle onclick='$(\"#dialog\").jqmHide();'></td></tr>
			</table>
			<div style='overflow-y:auto;max-height:275px'>
			<table width=100% cellspacing=0 cellpadding = 0 border = 0>";
				echo "
					<tr><td class=thongbaoinfo><img src='/images/icon-admin.png' width=15 height=15 ><span style='padding-left:3px'>".htmlspecialchars($data["NGUOITAONAME"])."</span><span style='float:right'>Ngày đăng: ".QLVBDHCommon::doDateStandard2VietSimple($data["NGAYTAO"])."</span></td></tr>
					<tr><td class=thongbaocontent>".$data["NOIDUNG"]."</td></tr>
				";
                                $filedinhkems=$filedinhkemModel->getListFileByIdObject($data["ID_TB"],2016);
                                if(count($filedinhkems)>=1){
                                    echo " <tr><td class=thongbaocontent>File đính kèm</td></tr>";
                                    foreach($filedinhkems as $filedinhkem){
                                        echo "<tr><td class=thongbaocontent><a href='/hscv/File/download?year=".$year."&maso=".$filedinhkem['MASO']."'>".$filedinhkem['FILENAME']."</a></td></tr>";
                                    }
                                }
                                
			echo "</table>
			</div>
			";
		}else{
			$data = $thongbao->SelectAllVisible(0,0,"hieuluc desc,ngaytao desc");
			echo "
			<table width=100% cellspacing=0 cellpadding = 0 border = 0>
				<tr><td class=thongbaoheader><img src='/images/alarm.gif' align=absmiddle>Thông báo toàn cơ quan (Có ".count($data)." thông báo)<img style='float:right' width=20 height=20 src='/images/icon/icon-DeleteButton1.png' align=absmiddle onclick='$(\"#dialog\").jqmHide();'></td></tr>
			</table>
			<div style='overflow-y:auto;max-height:275px'>
			";
			echo "<table width=100% cellspacing=0 cellpadding = 0 border = 0>";
			foreach($data as $item){
				echo "
					<tr><td class=thongbaotitle>".htmlspecialchars($item["TIEUDE"])."</td></tr>
					<tr><td class=thongbaoinfo><img src='/images/icon-admin.png' width=15 height=15 ><span style='padding-left:3px'>".htmlspecialchars($item["NGUOITAONAME"])."</span><span style='float:right'>Ngày đăng: ".QLVBDHCommon::doDateStandard2VietSimple($item["NGAYTAO"])."</span></td></tr>
					<tr><td class=thongbaocontent>".$item["NOIDUNG"]."</td></tr>
				";
                                $filedinhkems=$filedinhkemModel->getListFileByIdObject($item["ID_TB"],2016);
                                if(count($filedinhkems)>=1){
                                    echo "<tr><td class=thongbaocontent>File đính kèm</td></tr>";
                                    foreach($filedinhkems as $filedinhkem){
                                        echo "<tr><td class=thongbaocontent><a href='/hscv/File/download?year=".$year."$maso=".$filedinhkem['MASO']."'>".$filedinhkem['FILENAME']."</a></td></tr>";
                                    }
                                }
			}
			echo "</table>
			</div>
			";
		}
		exit;
	}
}