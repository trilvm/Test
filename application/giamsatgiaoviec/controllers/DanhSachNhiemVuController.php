<?php
require_once 'Zend/Controller/Action.php';
require_once 'giamsatgiaoviec/models/DanhSachNhiemVuModel.php';
require_once 'giamsatgiaoviec/models/DanhSachNhiemVuModel.php';
require_once 'giamsatgiaoviec/models/LoaiCongViecGiaoViecModel.php';


class Giamsatgiaoviec_DanhSachNhiemVuController extends Zend_Controller_Action {

    public function init() {
        $this->view->title = "Danh sách văn bản đến giao việc";
    }

    function indexAction() {

        global $auth;
			$user = $auth->getIdentity();
			//Lấy parameter
			$param = $this->getRequest()->getParams();
			
			$config = Zend_Registry::get('config');
			$realyear = QLVBDHCommon::getYear();

			//tinh chỉnh param
			$IS_SEE_ALL = (int)$param['IS_SEE_ALL'];
			$ID_VBD 	= $param['ID_VBD'];
			$TRICHYEU 	= $param['TRICHYEU'];		
			$ID_CQ 		= $param['ID_CQ'];
                        $IS_NOIBO 		= $param['IS_NOIBO'];
                        $IS_LIENTHONG 		= $param['IS_LIENTHONG'];
			$ID_SVB 	= $param['ID_SVB'];
			$ID_LVVB 	= $param['ID_LVVB'];
			$ID_LVB 	= $param['ID_LVB'];
			$SOKYHIEU	= $param['SOKYHIEU'];
			$NGUOIXULY	= $param['NGUOIXULY'];
                        $LOAICONGVIEC   = $param['LOAICV_GIAOVIEC'];
                        
                        
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
			if($param['NGAYDEN_KT']!=""){
				$ngayden_kt = $param['NGAYDEN_KT']."/".QLVBDHCommon::getYear();
				$ngayden_kt = implode("-",array_reverse(explode("/",$ngayden_kt)));
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
                                "TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
				"ID_CQ"=>$ID_CQ,
				"ID_SVB"=>$ID_SVB,
                                "IS_NOIBO"=>$IS_NOIBO,
                                "IS_LIENTHONG"=>$IS_LIENTHONG,
				"ID_LVVB"=>$ID_LVVB,
				"ID_LVB"=>$ID_LVB,
				"NGAYDEN_BD"=>$ngayden_bd,
				"NGAYDEN_KT"=>$ngayden_kt,
				"ID_U"=>$user->ID_U,
				"SOKYHIEU"=>$SOKYHIEU,
				"IS_SEE_ALL" =>$IS_SEE_ALL, 
				"SCOPE"=>$scope,
				"INNAME"=>$INNAME,
				"INFILE"=>$INFILE,
				"SORTBY"=>$SORTBY,
				"SORTTYPE"=>$SORTTYPE,
                                "LOAICONGVIEC" => $LOAICONGVIEC,
				"NGUOIXULY" => $NGUOIXULY
			);
			
			if($param['IS_NOIBO']==1){
				$IS_NOIBO = $param['IS_NOIBO'];
			}else{
			
			}  
                        
                        if($param['IS_LIENTHONG']==1){
				$IS_LIENTHONG = $param['IS_LIENTHONG'];
			}else{
			
			}  
                        
			$page = $param['page'];
			$limit = $param['limit1'];		
			if($limit==0 || $limit=="")$limit=$config->limit;
			if($page==0 || $page=="")$page=1;		
			
			

			if($SORTBY !="DAXEM"){
				if($SORTTYPE!=""){
					$sort = $SORTBY." ".$SORTTYPE;
				}else{
					$sort = $SORTBY;
				}
			}else{
				$sort = "NGAYDEN DESC";
			}
			
			
			//Lấy dữ liệu
                        //var_dump($parameter);   
			$this->view->data = DanhSachNhiemVuModel::SelectAll($parameter,($page-1)*$limit,$limit,$sort);
                        $hscvcount = DanhSachNhiemVuModel::Count_vbd($parameter);
                        $this->view->realyear 		= $realyear;
			$this->view->TRICHYEU 		= $TRICHYEU;
			$this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
			$this->view->NGUOIXULY 		= $NGUOIXULY;
			$this->view->ID_CQ 		    = $ID_CQ;
                        $this->view->LOAICV_GIAOVIEC 		    = $LOAICONGVIEC;
			$this->view->ID_SVB 		= $ID_SVB;
			$this->view->ID_LVVB 		= $ID_LVVB;
			$this->view->ID_LVB 		= $ID_LVB;
			$this->view->ID_VBD 		= $ID_VBD;
			$this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
			$this->view->DOMAT 			= $DOMAT;
			$this->view->DOKHAN 		= $DOKHAN;
			$this->view->NGAYDEN_BD 	= $param['NGAYDEN_BD'];
			$this->view->NGAYDEN_KT 	= $param['NGAYDEN_KT'];
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
                        $this->view->IS_NOIBO = $IS_NOIBO;
                        $this->view->IS_LIENTHONG = $IS_LIENTHONG;
                        
                        $ID_LOAIHSCV = 1;
			$createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
                        /*
			$actid = ResourceUserModel::getActionByUrl('vbden','vbden','listall');
					if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
						QLVBDHButton::EnableVbdNhiemVuCoquan('/giamsatgiaoviec/danhsachnhiemvuall');
										   
					}

                        */
                        $loaiCongViecGiaoViecModel = new LoaiCongViecGiaoViecModel();
                        $this->view->dataloaicongviec = $loaiCongViecGiaoViecModel->getLoaiCongViecGiao(); 
        
			$this->view->title = "Danh sách nhiệm vụ";
			$this->view->config = $config;
			$this->view->page = $page;
			$this->view->limit = $limit;
			$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/giamsatgiaoviec/danhsachnhiemvu");

    }

    
}
        