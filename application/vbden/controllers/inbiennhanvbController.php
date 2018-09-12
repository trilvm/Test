<?php
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
require_once 'vbmail/models/vbmail_vanbannhanModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/HosoluutheodoiModel.php';
include_once 'vbdi/models/VanBanDiModel.php';
include_once 'qtht/models/nguoidungModel.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once('hscv/models/chuyennoiboModel.php');
require_once 'hscv/models/gen_tempModel.php';
require_once 'vbden/models/inbiennhanModel.php';

class vbden_inbiennhanvbController extends Zend_Controller_Action {
    public function inbiennhanAction()
    {   
           
                    
                global $auth;
		$user = $auth->getIdentity();
		//Lấy parameter
		$param = $this->getRequest()->getParams();
                
             if($this->getRequest()->getParam('fromdate'));
              $param['NGAYBANHANH_BD']=$this->getRequest()->getParam('fromdate');
            if($this->getRequest()->getParam('todate'));
              $param['NGAYBANHANH_KT']=$this->getRequest()->getParam('todate');
           
             
              
		//$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
		$config = Zend_Registry::get('config');
		$realyear = QLVBDHCommon::getYear();
                if( $this->getRequest()->getParam('ID_LVB')==33)
                        $this->view->saoy=1;
		//tinh chỉnh param
		$IS_SEE_ALL = (int)$param['IS_SEE_ALL'];
		$ID_VBD 	= $param['ID_VBD'];
		$TRICHYEU 	= $param['TRICHYEU'];		
		$ID_CQ 		= $param['ID_CQ'];
		$ID_SVB 	= $param['ID_SVB'];
              
		$ID_LVVB 	= $param['ID_LVVB'];
		$ID_LVB 	= $param['ID_LVB'];
		$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
		$DOMAT 		= $param['DOMAT'];
		$DOKHAN 	= $param['DOKHAN'];	
		$SODEN		= $param['SODEN'];
		$IS_PHOBIEN		= $param['IS_PHOBIEN'];
		$SOKYHIEU	= $param['SOKYHIEU'];
		$SODEN_ISLIKE	= $param['SODEN_ISLIKE'];
		$SORTBY	= $param['SORTBY'];
		if(!$SORTBY)
			$SORTBY = "NGAYDEN"; 
		$SORTTYPE	= $param['SORTTYPE'];
		if(!$SORTTYPE)
			$SORTTYPE ="DESC";
		
		$INNAME	= $param['INNAME'];
		$INFILE	= $param['INFILE'];
		if($param['INNAME']==0 && $param['INFILE']==0){
			$INNAME = 1;
		}
		if($param['NGAYDEN_BD']!=""){
			$ngayden_bd = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
			$ngayden_bd = implode("-",array_reverse(explode("/",$ngayden_bd)));
		}
                //echo $ngayden_bd;
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
		$hscvcount = $hscv->Count_vbd($parameter);			
		//Lấy dữ liệu
                
		$this->view->data = $hscv->SelectAll_vbd($parameter,($page-1)*$limit,$limit,$sort);
		$this->view->realyear 		= $realyear;
		$this->view->TRICHYEU 		= $TRICHYEU;
		$this->view->ID_CQ 		= $ID_CQ;
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
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
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

		$ID_LOAIHSCV = 1;
                $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);

		$actid = ResourceUserModel::getActionByUrl('vbden','inbiennhanvb','inbiennhan');
				
                   QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("In biên nhận ", $action, "PrintButton", "inbiennhan();");            
                   QLVBDHButton::EnableBack('');       
		
                 

		//$this->view->arr_idnews = vbd_dongluanchuyenModel::getIdVbdenChuaXemByIdUser($realyear,$user->ID_U);
		//var_dump($this->view->arr_news);	
		//page
		$this->view->title = "IN BIÊN NHẬN VĂN BẢN";
		//$this->view->subtitle = "Danh sách";
		$this->view->config = $config;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbden/inbiennhanvb/inbiennhan'");
               
        
    }
    /*phuongpt*/
    public function inAction()
    {
        $hscv = new hosocongviecModel();
       
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $idvb = $params['idvb'];
        //var_dump($idvb);exit;
        $this->view->datas = inbiennhanModel::InBienNhan($idvb);
        /* lay dong luan chuyen */
//        $hscv = vbdenModel::GetAllHSCV($idvb);
//        foreach ($hscv as $itemhscv) {
//            $arr_id[] = $itemhscv['ID_HSCV'];
//        }
//        $this->view->sendprocess = array();
//        foreach ($arr_id as $idhscv) 
//        {
//            $sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);
//            array_push($this->view->sendprocess, $sendprocess);    
//        }
    }
    
    public function inphieuchuyenAction()
    {
            global $auth;
		$user = $auth->getIdentity();
		//Lấy parameter
		$param = $this->getRequest()->getParams();
             if($this->getRequest()->getParam('fromdate'));
              $param['NGAYDEN_BD']=$this->getRequest()->getParam('fromdate');
            if($this->getRequest()->getParam('todate'));
              $param['NGAYDEN_KT']=$this->getRequest()->getParam('todate');
           
             
              
		//$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
		$config = Zend_Registry::get('config');
		$realyear = QLVBDHCommon::getYear();
                if( $this->getRequest()->getParam('ID_LVB')==33)
                        $this->view->saoy=1;
		//tinh chỉnh param
		$IS_SEE_ALL = (int)$param['IS_SEE_ALL'];
		$ID_VBD 	= $param['ID_VBD'];
		$TRICHYEU 	= $param['TRICHYEU'];		
		$ID_CQ 		= $param['ID_CQ'];
		$ID_SVB 	= $param['ID_SVB'];
              
		$ID_LVVB 	= $param['ID_LVVB'];
		$ID_LVB 	= $param['ID_LVB'];
		$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
		$DOMAT 		= $param['DOMAT'];
		$DOKHAN 	= $param['DOKHAN'];	
		$SODEN		= $param['SODEN'];
		$IS_PHOBIEN		= $param['IS_PHOBIEN'];
		$SOKYHIEU	= $param['SOKYHIEU'];
		$SODEN_ISLIKE	= $param['SODEN_ISLIKE'];
		$SORTBY	= $param['SORTBY'];
		if(!$SORTBY)
			$SORTBY = "NGAYDEN"; 
		$SORTTYPE	= $param['SORTTYPE'];
		if(!$SORTTYPE)
			$SORTTYPE ="DESC";
		
		$INNAME	= $param['INNAME'];
		$INFILE	= $param['INFILE'];
		if($param['INNAME']==0 && $param['INFILE']==0){
			$INNAME = 1;
		}
		if($param['NGAYDEN_BD']!=""){
			$ngayden_bd = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
			$ngayden_bd = implode("-",array_reverse(explode("/",$ngayden_bd)));
		}
                //echo $ngayden_bd;
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
		$hscvcount = $hscv->Count_vbd($parameter);			
		//Lấy dữ liệu
                
		$this->view->data = $hscv->SelectAll_vbd($parameter,($page-1)*$limit,$limit,$sort);
		$this->view->realyear 		= $realyear;
		$this->view->TRICHYEU 		= $TRICHYEU;
		$this->view->ID_CQ 		= $ID_CQ;
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
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
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
		$ID_LOAIHSCV = 1;
                $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);

		$actid = ResourceUserModel::getActionByUrl('vbden','inbiennhanvb','inbiennhan');
				
                   QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("In phiếu chuyển ", $action, "PrintButton", "inbiennhan();");            
                   QLVBDHButton::EnableBack('');       
		
                 

		//$this->view->arr_idnews = vbd_dongluanchuyenModel::getIdVbdenChuaXemByIdUser($realyear,$user->ID_U);
		//var_dump($this->view->arr_news);	
		//page
		$this->view->title = "IN BIÊN NHẬN VĂN BẢN";
		//$this->view->subtitle = "Danh sách";
		$this->view->config = $config;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbden/inbiennhanvb/inbiennhan'");
    }
    public function inphieuchuyenprAction()
    {
        
         $this->_helper->layout->disableLayout();
         $db = Zend_Db_Table::getDefaultAdapter();
         $idvb = $this->getRequest()->getParam('id_vbd');
         $idvb_str='';
        
       
         //$params= $this->getRequest()->getParams();
         //var_dump(implode(',',$idvb));exit;
          $sql="
            SELECT
                vb.*, lvb.NAME as LVBNAME, lvvb.NAME as LVVBNAME,
                svb.NAME as SVBNAME, cq.NAME as CQNAME,
                concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME
				, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID
            FROM
                ".QLVBDHCommon::Table("VBD_VANBANDEN")."  vb
		left join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv on fkhscv.ID_VBDEN = vb.ID_VBD
	    	left join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on hscv.ID_HSCV = fkhscv.ID_HSCV
	    	left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
                left join VB_LOAIVANBAN lvb on vb.ID_LVB = lvb.ID_LVB
                left join VB_LINHVUCVANBAN lvvb on vb.ID_LVVB =lvvb.ID_LVVB
                left join VB_SOVANBAN svb on vb.ID_SVB = svb.ID_SVB
                left join VB_COQUAN cq	on vb.ID_CQ = cq.ID_CQ
                left join QTHT_USERS u on vb.NGUOITAO = u.ID_U
                left join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
            WHERE
                vb.ID_VBD = ?
              ";
        
           $r = $db->query($sql,$idvb); 
           $data= ($r->fetchAll()) ;
           echo '<pre>';
           var_dump($data);exit;   
    }
    
}
?>
