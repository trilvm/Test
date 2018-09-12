<?php
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/HosoluutheodoiModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once('qtht/models/UsersModel.php');
require_once('auth/models/ResourceUserModel.php');
require_once 'hscv/models/ThumuccanhanModel.php';
require_once 'hscv/models/VanBanLienQuanModel.php';
require_once 'taphscv/models/Taphscv_TaphosocongviecModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'traodoicongviec/models/traodoicongviecModel.php';
require_once 'nusoap/nusoap.php';
class Hscv_hosoluutheodoiController extends Zend_Controller_Action {
	var $object;
	function init(){
		global $auth;
        $user = $auth->getIdentity();
        $this->object = new Taphscv();
        $this->object->_id_u = $user->ID_U;
        $this->object->_id_g = $user->ID_G;
        $this->object->_id_dept = $user->ID_DEP;
	}
	
	function indexAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		//$param['NAMECV'] = Convert::ConvertToUnicode($param['NAMECV']);
		$config = Zend_Registry::get('config');
		
		//tinh chỉnh param
		if($param['NGAY_BD']!=""){
			$ngaybd = $param['NGAY_BD']."/".QLVBDHCommon::getYear();
			$ngaybd = implode("-",array_reverse(explode("/",$ngaybd)));
		}
		if($param['NGAY_KT']!=""){
			$ngaykt = $param['NGAY_KT']."/".QLVBDHCommon::getYear();
			$ngaykt = implode("-",array_reverse(explode("/",$ngaykt)));
		}
		$realyear = QLVBDHCommon::getYear();

		$id_thumuc = $param["id_thumuc"];
		$id_thumuc = $id_thumuc==""?1:$id_thumuc;
		//check quyen vao thu muc neu thu muc >1
		if($id_thumuc>1){
			if(!ThuMucModel::CheckPermission($user->ID_U,$id_thumuc)){
				$this->_redirect("/default/error/error?control=hscv&mod=hscv&id=ERR11001001");
			}
		}
		$ID_LOAIHSCV = $param['ID_LOAIHSCV'];
		$NAME = $param['NAMECV'];
		$page = $param['page'];
		$limit = $param['limit1'];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$TRANGTHAI = $param['TRANGTHAI'];
		$INFILE = $param['INFILE'];
		$INNAME = $param['INNAME'];
		if($param['INNAME']==0 && $param['INFILE']==0){
			$INNAME = 1;
		}
		
		$parameter = array(
			"ID_THUMUC"=>$id_thumuc,
			"ID_LOAIHSCV"=>$ID_LOAIHSCV,
			"NGAY_BD"=>$ngaybd,
			"NGAY_KT"=>$ngaykt,
			"TRANGTHAI"=>$TRANGTHAI,
			"ID_U"=>$user->ID_U,
			"ID_G"=>$user->ID_G,
			"ID_DEP"=>$user->ID_DEP,
			"NAME"=>$NAME,
			"SCOPE"=>$scope,
			"CODE"=>$param['code'],
			"OBJECT"=>$param['OBJECT'],
			"INNAME"=>$param['INNAME'],
			"INFILE"=>$param['INFILE']
		);
		
		//Tạo đối tượng
		$hscvcount = HosoluutheodoiModel::Count($parameter);
		if(($page-1)*$limit==$hscvcount && $hscvcount>0)$page--;
		//Lấy dữ liệu
		$this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
                $this->view->data = HosoluutheodoiModel::SelectAll($parameter,($page-1)*$limit,$limit,"");
		$this->view->realyear = $realyear;
		$this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
		$this->view->NAME = $NAME;
		$this->view->NGAY_BD = $param['NGAY_BD'];
		$this->view->NGAY_KT = $param['NGAY_KT'];
		$this->view->NAME = $NAME;
		$this->view->TRANGTHAI = $TRANGTHAI;
		$this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
		$this->view->id_thumuc = $id_thumuc;
		$this->view->INNAME = $INNAME;
		$this->view->INFILE = $INFILE;
		$this->view->user = $user;
		$this->view->code = $param['code'];
		$this->view->idhscv = $param['idhscv'];
		$this->view->OBJECT = $param['OBJECT'];
		$this->view->title="Hồ sơ công việc lưu kết thúc";
		//$this->view->subtitle="Lưu theo dõi";
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/hscv/hosoluutheodoi/index");
		$this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
		$this->view->user = $user;
	}
	
	function inputluutheodoiAction(){
		$params = $this->_request->getParams();
		$this->_helper->Layout->disableLayout();
		$this->view->ID_HSCV = $params["id"];
		$this->view->data = HosoluutheodoiModel::getByHSCVId($this->view->ID_HSCV);
		$this->view->datafolder=ThumuccanhanModel::toComboThuMucPrivate();
        
        // kiểm tra có lưu vào hồ sơ công việc điện tử chưa
        $id_vbden = HosoluutheodoiModel::getIdVbdenByIdhscv($params["id"]);
        $id_vbdi = HosoluutheodoiModel::getIdVbdiByIdhscv($params["id"]);
        $id_duthao = HosoluutheodoiModel::getIdDuthaoByIdhscv($params["id"]);
        $this->view->vanbanden = vbdenModel::getdetailVBD($id_vbden);
        $tapHSCVModel = new Taphscv_TaphosocongviecModel();
        $isSaveToHSCVDT = 0; 
        if((int)$id_vbden > 0){
            //$count = $tapHSCVModel->isExist(1,$id_vbden);
            //if($count == 0){
                $isSaveToHSCVDT = 1; 
            //}
        }
        if((int)$id_vbdi > 0){
            //$cnt = $tapHSCVModel->isExist(2,$id_vbdi);
            //if($cnt == 0){
                $isSaveToHSCVDT = 1; 
            //}
        }
        if((int)$id_duthao > 0){
            //$cnt1 = $tapHSCVModel->isExist(3,$id_duthao);
            //if($cnt1 == 0){
                $isSaveToHSCVDT = 1; 
            //}
        }
        
        if($isSaveToHSCVDT == 1){
            $dataTapHSCV = $tapHSCVModel->getTapHSCV($this->object);
            $this->view->vbden = $id_vbden;
            $this->view->vbdi = $id_vbdi;
            $this->view->duthao = $id_duthao;
            $this->view->dataTapHSCV = $dataTapHSCV;
        }
    }

	function luutheodoiAction(){
		global $db;
		global $auth;
                $config = Zend_Registry::get('config');
		$user = $auth->getIdentity();
		$year = QLVBDHCommon::getYear();

		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$comment = $params["comment"];
		$id_hscv = $params['id_hscv'];
        // Lưu giải trình, cập nhật tiến độ
		$parameter["tiendo"]=100;
		$parameter["motatiendo"]="Lưu kết thúc, lý do : ".$comment;
		$TraoDoiCongViecModel = new TraoDoiCongViecModel($year);        
        if ((int)$id_hscv > 0) {            
            $DLCLIENTHONG = $TraoDoiCongViecModel->getMaSoDongLuanChuyenLt($id_hscv);
            if ((int)$DLCLIENTHONG > 0) {
                $wsdl = $config->service->lienthong->uri;
                $username = $config->service->lienthong->username;
                $password = $config->service->lienthong->password;
                $cliente = new SoapClient($wsdl);
                $session = $cliente->__call('Login', array(
                    'madonvi' => $username,
                    'password' => $password));
                $params = array(
                    'session' => $session,
                    'service_code' => 'TRAODOIGSCV',
                    'service_name' => 'CAPNHATTIENDO',
                    'parameter' => base64_encode($DLCLIENTHONG) . '~' . base64_encode($parameter["tiendo"]). '~' . base64_encode($parameter["motatiendo"])
                );
                $response = $cliente->__call('Execute', $params);                
            }
            $TraoDoiCongViecModel->getDefaultAdapter()->update(
                    QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), 
                    array("TIENDO_GIAOVIEC" => $parameter["tiendo"],
						  'MOTATIENDO'=> $parameter["motatiendo"]							
					),'ID_HSCV = ' . $id_hscv[0]);            
        }
           // kết thúc  Lưu giải trình, cập nhật tiến độ               
               
            if($params['idtaphoso']>0){
		if(!is_array($id_hscv)){
                        $this->model = new Taphscv_TaphosocongviecModel();
                        $flag="false";
			if($params["vbden"] != ""){
                            $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $params["vbden"], 1);
                            if($check=='true')
                            {
                                $flag='true';
				$r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $params["vbden"], 1);
                            }
			}
			if($params["vbdi"] != ""){
                            $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $params["vbdi"], 2);
                            if($check=='true')
                            {
                                $flag='true';
				$r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $params["vbdi"], 2);
                            }
			}
                        $modelvblq = new VanBanLienQuanModel($year);
                        $vblqs = $modelvblq->getListByIdHSCV($id_hscv,$year);
                        if(count($vblqs)>0 && is_array($vblqs)){
                            foreach($vblqs as $vblq){
                                if((int)$vblq['ISSYSTEM']==1){
                                    $arrayVblq=  explode('-', $vblq['NAME']);
                                    $vanbanden= HosoluutheodoiModel::getVBDenLuuTheoDoi($arrayVblq[0],$arrayVblq[1],end($arrayVblq));
                                    if($vanbanden["ID_VBD"] != ""){
                                        $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $vanbanden["ID_VBD"], 1);
                                        if($check=='true')
                                        {
                                            $flag='true';
                                            $r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $vanbanden["ID_VBD"], 1);
                                        }
                                    }
                                    $vanbandi= HosoluutheodoiModel::getVBDiLuuTheoDoi($arrayVblq[0],$arrayVblq[1],end($arrayVblq));
                                    if($vanbandi["ID_VBDI"] != ""){
                                        $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $vanbandi["ID_VBDI"], 2);
                                        if($check=='true')
                                        {
                                            $flag='true';
                                            $r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $vanbandi["ID_VBDI"], 2);
                                        }
                                    }
                                }else{
                                    
                                    $vanbanngoai= HosoluutheodoiModel::getVBNgoaiLuuTheoDoi($vblq['ID_VBLQ']);
                                    $trichyeu=$vanbanngoai['NAME'];
                                    $filename = $vanbanngoai['FILENAME'];
                                    $mime = $vanbanngoai['MIME'];
                                    $maso=md5($vanbanngoai['MASO']);
                                    $db->insert('hscvdt_dinhkem_taphoso', array(
                                        'ID_TAPHOSO' => $params["idtaphoso"],
                                        'FILENAME' => $filename,
                                        'FILECODE' => $maso,
                                        'FILEMIME' => $mime
                                    ));
                                    $taphosoMdl = new Taphscv_TaphosocongviecModel();
                                    $id_object = $taphosoMdl->getPrimarykeyWhenInsert("ID_DINHKEM", 'hscvdt_dinhkem_taphoso');

                                    //insert zo vanban
                                    $db->insert('hscvdt_vanban', array(
                                        'ID_TAPHOSO' => $params["idtaphoso"],
                                        'SOKYHIEU' => '',
                                        'ID_OBJECT' => $id_object,
                                        'NGAYBANHANH' => date('Y-m-d'),
                                        'COQUANBANHANH' => '',
                                        'TRICHYEU' => $trichyeu,
                                        'LOAI' => 3,
                                        'NAM' => QLVBDHCommon::getYear()
                                    ));
                                    
                                    $newfile =$config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $maso;
                                    $file=$config->file->root_dir.DIRECTORY_SEPARATOR.$vanbanngoai['NAM'].DIRECTORY_SEPARATOR.$vanbanngoai['THANG'].DIRECTORY_SEPARATOR.$vanbanngoai['MASO'] ;			
                                    if (!copy($file, $newfile)) {
                                        //echo "failed to copy";
                                    }                                    
                                }
                            }    
                        }
			if($params["duthao"] != ""){
                            $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $params["duthao"], 3);
                            if($check=='true')
                            {        
                                $flag='true';
				$r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $params["duthao"], 3);
                            }
			}
                        if($flag=='true'){                            
                            HosoluutheodoiModel::luuTheodoi($year,$id_hscv,$comment,$user->ID_U);
                            $db->insert(QLVBDHCommon::Table("hscv_fk_tmcn"),array("ID_OBJECT"=>$id_hscv,"ID_TMCN"=>$params["FOLDER"],"TYPE"=>0));
                            echo "<script>window.parent.document.frm.submit();</script>";
                        }else{
                            echo "<script>alert('Không thể tiếp tục. Vui lòng chọn tập hồ sơ công việc điện tử khác.');</script>";
                        }                            
			exit;	
		}else{
			$this->model = new Taphscv_TaphosocongviecModel();

			foreach($id_hscv as $id_hscv_item){
				HosoluutheodoiModel::luuTheodoi($year,$id_hscv_item,"",$user->ID_U);
				if($params["idtaphoso"]>0){
					// Lay id cac the loai
                                    $id_vbden = HosoluutheodoiModel::getIdVbdenByIdhscv($id_hscv_item);
                                    $id_vbdi = HosoluutheodoiModel::getIdVbdiByIdhscv($id_hscv_item);
                                    $id_duthao = HosoluutheodoiModel::getIdDuthaoByIdhscv($id_hscv_item);
                                    if($id_vbden != ""){
                                        $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $id_vbden, 1);
                                        if($check=='true')
                                        { 
                                            $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $id_vbden, 1);
                                        }
                                    }
                                    if($id_vbdi != ""){
                                        $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"],  $id_vbdi, 2);
                                        if($check=='true')
                                        { 
                                            $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $id_vbdi, 2);
                                        }
                                    }
                                    $modelvblq = new VanBanLienQuanModel($year);
                                    $vblqs = $modelvblq->getListByIdHSCV($id_hscv_item,$year);
                                    if(count($vblqs)>0 && is_array($vblqs)){
                                        foreach($vblqs as $vblq){
                                            if((int)$vblq['ISSYSTEM']==1){
                                                $arrayVblq=  explode('-', $vblq['NAME']);
                                                $vanbanden= HosoluutheodoiModel::getVBDenLuuTheoDoi($arrayVblq[0],$arrayVblq[1],end($arrayVblq));
                                                if($vanbanden["ID_VBD"] != ""){
                                                    $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $vanbanden["ID_VBD"], 1);
                                                    if($check=='true')
                                                    {
                                                        $flag='true';
                                                        $r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $vanbanden["ID_VBD"], 1);
                                                    }
                                                }
                                                $vanbandi= HosoluutheodoiModel::getVBDiLuuTheoDoi($arrayVblq[0],$arrayVblq[1],end($arrayVblq));
                                                if($vanbandi["ID_VBDI"] != ""){
                                                    $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $vanbandi["ID_VBDI"], 2);
                                                    if($check=='true')
                                                    {
                                                        $flag='true';
                                                        $r = $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $vanbandi["ID_VBDI"], 2);
                                                    }
                                                }
                                            }else{
                                                $vanbanngoai= HosoluutheodoiModel::getVBNgoaiLuuTheoDoi($vblq['ID_VBLQ']);
                                                $trichyeu=$vanbanngoai['NAME'];
                                                $filename = $vanbanngoai['FILENAME'];
                                                $mime = $vanbanngoai['MIME'];
                                                $maso=md5($vanbanngoai['MASO']);
                                                $db->insert('hscvdt_dinhkem_taphoso', array(
                                                    'ID_TAPHOSO' => $params["idtaphoso"],
                                                    'FILENAME' => $filename,
                                                    'FILECODE' => $maso,
                                                    'FILEMIME' => $mime
                                                ));
                                                $taphosoMdl = new Taphscv_TaphosocongviecModel();
                                                $id_object = $taphosoMdl->getPrimarykeyWhenInsert("ID_DINHKEM", 'hscvdt_dinhkem_taphoso');

                                                //insert zo vanban
                                                $db->insert('hscvdt_vanban', array(
                                                    'ID_TAPHOSO' => $params["idtaphoso"],
                                                    'SOKYHIEU' => '',
                                                    'ID_OBJECT' => $id_object,
                                                    'NGAYBANHANH' => date('Y-m-d'),
                                                    'COQUANBANHANH' => '',
                                                    'TRICHYEU' => $trichyeu,
                                                    'LOAI' => 3,
                                                    'NAM' => QLVBDHCommon::getYear()
                                                ));
                                                
                                                $newfile =$config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $maso;
                                                $file=$config->file->root_dir.DIRECTORY_SEPARATOR.$vanbanngoai['NAM'].DIRECTORY_SEPARATOR.$vanbanngoai['THANG'].DIRECTORY_SEPARATOR.$vanbanngoai['MASO'] ;			
                                                if (!copy($file, $newfile)) {
                                                    //echo "failed to copy";
                                                }  
                                            }
                                        }    
                                    }
                                    if($id_duthao != ""){
                                        $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $id_duthao, 3);
                                        if($check=='true')
                                        { 
                                            $this->model->AddVanBanToTapHSCV($params["idtaphoso"], $id_duthao, 3);
                                        }
                                    }
				}
			}

			$this->_redirect("/hscv/hscv/multiketthuc");
		}
            }else{
                if(!is_array($id_hscv)){
                    HosoluutheodoiModel::luuTheodoi($year,$id_hscv,$comment,$user->ID_U);
                    $db->insert(QLVBDHCommon::Table("hscv_fk_tmcn"),array("ID_OBJECT"=>$id_hscv,"ID_TMCN"=>$params["FOLDER"],"TYPE"=>0));
                    echo "<script>window.parent.document.frm.submit();</script>";
                    exit;	
                }else{
                    foreach($id_hscv as $id_hscv_item){
                        HosoluutheodoiModel::luuTheodoi($year,$id_hscv_item,"",$user->ID_U);                        
                    }
                    $this->_redirect("/hscv/hscv/multiketthuc");
                }
            }
	}
	
	function phuchoiluutheodoiAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		echo HosoluutheodoiModel::phuchoiluuTheodoi($param['id'],$user->ID_U);

		echo "document.frm.submit();";
		exit;
	}
        
        public function checkintaphscvdtAction()
        {
            $this->_helper->layout->disableLayout();
            $params = $this->_request->getParams();
            $this->model = new Taphscv_TaphosocongviecModel();
            $id_vbden = HosoluutheodoiModel::getIdVbdenByIdhscv($params["id"]);
            $id_vbdi = HosoluutheodoiModel::getIdVbdiByIdhscv($params["id"]);
            $id_duthao = HosoluutheodoiModel::getIdDuthaoByIdhscv($params["id"]);
            if((int)$id_vbden > 0){
                $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $id_vbden, 1);
                if($check=='true')
                {
                    $flag='true';
                }
            }
            if((int)$id_vbdi > 0){
                $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $id_vbdi, 2);
                if($check=='true')
                {
                    $flag='true';
                }
            }
            if((int)$id_duthao > 0){
	    $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $params["id"], 4);
                $check=$this->model->checkVanBanToTapHSCV($params["idtaphoso"], $id_duthao, 3);
                if($check=='true')
                {        
                    $flag='true';
                }
            }
            if($flag=='true'){
                echo "";
            }else{
                if((int)$params["idtaphoso"]== 0){
                    echo "Chưa chọn HSCV điện tử.";
                }else{
                    echo "Đã tồn tại.";
                }
            }
            exit;
        }
	
}