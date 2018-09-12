<?php

/**
 * vbdenController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'config/vbden.php';
// Dùng bên listAction
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'vbden/models/fk_vbden_hscvsModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once('vbden/models/vbdenModel.php');
require_once('vbden/models/vbdennhanModel.php');
require_once 'vbmail/models/vbmail_vanbannhanModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/HosoluutheodoiModel.php';
include_once 'vbdi/models/VanBanDiModel.php';
include_once 'qtht/models/nguoidungModel.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/ThuMucModel.php';
include_once 'qtht/models/UsersModel.php';



require_once('hscv/models/chuyennoiboModel.php');

class vbden_vbdennhanController extends Zend_Controller_Action {
	
    public function detailAction(){
    	$this->_helper->layout->disablelayout();
    	global $db;
    	$param =  $this->getRequest()->getParams();
    	$idhscv = $param["id"];
		$alias = WFEngine::GetClassNameFromObjectId($idhscv);
		$year = $param["year"];
		$type = $param["type"];
		$this->view->data   = chuyennoiboModel::getInfonamevbnhannoibo($idhscv);
		$sql = "
	    		SELECT dk.* FROM ".QLVBDHCommon::Table("GEN_FILEDINHKEM")." dk 
	    		WHERE
	    			 ID_OBJECT = '".$this->view->data['ID_VBDNB']."'
	    			 and
	    			 TYPE = 15
	    	";
	    	
	    	$r = $db->query($sql);
	    	$this->view->file = $r->fetchAll(); 	
    }
    /**
	 * Tạo form list cho HSCV chung chung
	 */
	public function listAction(){
		$this->view->start = (float) array_sum(explode(' ',microtime())); 
		global $auth;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		//$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
		$config = Zend_Registry::get('config');
		
		$realyear = QLVBDHCommon::getYear();
		//tinh chỉnh param
		
		
		$TRICHYEU 	= $param['TRICHYEU'];		
		$ID_CQ 		= $param['ID_CQ'];
		
		
		$ID_LVB 	= $param['ID_LVB'];
		$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
		
		$SOKYHIEU	= $param['SOKYHIEU'];
		
		$INNAME	= $param['INNAME'];
		$INFILE	= $param['INFILE'];
		if($param['INNAME']==0 && $param['INFILE']==0){
			$INNAME = 1;
		}
		if($param['NGAYDEN_BD']!=""){
			$ngayden_bd = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
			$ngayden_bd = implode("-",array_reverse(explode("/",$ngayden_bd)));
		}
		if($param['NGAYDEN_KT']!=""){
			$ngayden_kt = $param['NGAYDEN_KT']."/".QLVBDHCommon::getYear();
			$ngayden_kt = implode("-",array_reverse(explode("/",$ngayden_kt)));
		}
	    if($param['NGAYBANHANH_BD']!=""){
			$ngaybanhanh_bd = $param['NGAYBANHANH_BD']."/".QLVBDHCommon::getYear();
			$ngaybanhanh_bd = implode("-",array_reverse(explode("/",$ngaybanhanh_bd)));
		}
		if($param['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $param['NGAYBANHANH_KT']."/".QLVBDHCommon::getYear();
			$ngaybanhanh_kt = implode("-",array_reverse(explode("/",$ngaybanhanh_kt)));
		}	    
		



		if($param['CHUA_DOC']==1){
			$CHUA_DOC = $param['CHUA_DOC'];
		}else{
		
		}  
		$page = $param['page'];
		$limit = $param['limit1'];		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;		
		$scope = array();
		if($param['SCOPE']){
			$scope = $param['SCOPE'];
		}
		$parameter = array(
			
			"TRICHYEU"=>$TRICHYEU,
			"ID_CQ"=>$ID_CQ,
			"ID_LVVB"=>$ID_LVVB,
			"ID_LVB"=>$ID_LVB,
			"SODEN"=>$SODEN,
			"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
			"DOMAT"=>$DOMAT,
			"DOKHAN"=>$DOKHAN,		
			"NGAYDEN_BD"=>$ngayden_bd,
			"NGAYDEN_KT"=>$ngayden_kt,
		    "NGAYBANHANH_BD"=>$ngaybanhanh_bd,
			"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
			"ID_U"=>$user->ID_U,
			"SOKYHIEU"=>$SOKYHIEU,
			"IS_SEE_ALL" =>$IS_SEE_ALL, 
			"SCOPE"=>$scope,
			"INNAME"=>$INNAME,
			"INFILE"=>$INFILE,
			"SORTBY"=>$SORTBY,
			"SORTTYPE"=>$SORTTYPE,
			"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
			"CHUA_DOC" => $CHUA_DOC
		);
		
		if($param['IS_PHOBIEN']==1){
			$IS_PHOBIEN = $param['IS_PHOBIEN'];
		}else{
		
		}  
		$page = $param['page'];
		$limit = $param['limit1'];		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;		
		$scope = array();
		if($param['SCOPE']){
			$scope = $param['SCOPE'];
		}
		$parameter = array(
			
			"TRICHYEU"=>$TRICHYEU,
			"ID_CQ"=>$ID_CQ,
			"ID_SVB"=>$ID_SVB,
			"ID_LVVB"=>$ID_LVVB,
			"ID_LVB"=>$ID_LVB,
			"SODEN"=>$SODEN,
			"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
			"DOMAT"=>$DOMAT,
			"DOKHAN"=>$DOKHAN,		
			"NGAYDEN_BD"=>$ngayden_bd,
			"NGAYDEN_KT"=>$ngayden_kt,
		    "NGAYBANHANH_BD"=>$ngaybanhanh_bd,
			"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
			"ID_U"=>$user->ID_U,
			"SOKYHIEU"=>$SOKYHIEU,
			"IS_SEE_ALL" =>$IS_SEE_ALL, 
			"SCOPE"=>$scope,
			"INNAME"=>$INNAME,
			"INFILE"=>$INFILE,
			"SORTBY"=>$SORTBY,
			"SORTTYPE"=>$SORTTYPE,
			"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
			"CHUA_DOC" => $CHUA_DOC,
			"IS_PHOBIEN"=>$IS_PHOBIEN
		);
		

		if($SORTBY !="DAXEM"){
			if($SORTTYPE!=""){
				$sort = $SORTBY." ".$SORTTYPE;
			}else{
				$sort = $SORTBY;
			}
		}else{
			$sort = "NGAYDEN DESC";
		}
		
		//Tạo đối tượng
		$hscv = new hosocongviecModel();
		$hscvcount = chuyennoiboModel::countvbnoibo($parameter);			
		//Lấy dữ liệu

		$this->view->data = chuyennoiboModel::selectAllvbnoibo($parameter,($page-1)*$limit,$limit,$sort);
		
		$this->view->realyear 		= $realyear;
		$this->view->TRICHYEU 		= $TRICHYEU;
		$this->view->ID_CQ 			= $ID_CQ;
		$this->view->ID_SVB 		= $ID_SVB;
		$this->view->ID_LVVB 		= $ID_LVVB;
		$this->view->ID_LVB 		= $ID_LVB;
		$this->view->ID_VBD 		= $ID_VBD;
		$this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
		$this->view->DOMAT 			= $DOMAT;
		$this->view->DOKHAN 		= $DOKHAN;
		$this->view->NGAYDEN_BD 	= $param['NGAYDEN_BD'];
		$this->view->NGAYDEN_KT 	= $param['NGAYDEN_KT'];
		$this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
		$this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
		$this->view->SODEN 			= $param['SODEN'];
		$this->view->SOKYHIEU 			= $param['SOKYHIEU'];
		$this->view->vbdennhan = new vbdennhanModel();
		$this->view->SCOPE = $scope;
		$this->view->INNAME = $INNAME;
		$this->view->INFILE = $INFILE;
		$this->view->SORTBY = $SORTBY;
		$this->view->SORTTYPE = $SORTTYPE;
		$this->view->SODEN_ISLIKE = $SODEN_ISLIKE;
		$this->view->user = $user;
		$this->view->IS_SEE_ALL = $IS_SEE_ALL;
		$this->view->ADVANCED= $param['ADVANCEDVALUE'];
	    $this->view->CHUA_DOC = $CHUA_DOC;
		$this->view->IS_PHOBIEN = $IS_PHOBIEN;

		
	    
		
		$this->view->arr_idnews = vbd_dongluanchuyenModel::getIdVbdenChuaXemByIdUser($realyear,$user->ID_U);
		//var_dump($this->view->arr_news);	
		//page
		$this->view->title = "Danh sách văn bản đến nhận ";
		//$this->view->subtitle = "Danh sách";
		$this->view->config = $config;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbden/vbdennhan/list");	
	}
	
	function deleteAction(){
		$parameter = $this->getRequest()->getParams();  
        $db = Zend_Db_Table::getDefaultAdapter();
        $year = QLVBDHCommon::getYear();    
        $id = (int)$parameter["id"];
        if($parameter["id"]){
	        $this->vbden = new vbdenModel($year);
	        try{
	           $db->delete("vbd_nhannoibo","ID_VBDNB = $id");
	        }catch(Exception $ex){

	        }
        }
        $this->_redirect("/vbden/vbdennhan/list");  
	}
	
	
}
