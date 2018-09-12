<?php

/**
 * HscvController
 * Xử lý các thao tác về hố sơ công việc như
 * Danh sách
 * Tìm kiếm
 * @deprecated 20/10/2009 by nguyennd
 * @author nguyennd
 * @version 1.0
 */
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once('qtht/models/UsersModel.php');
require_once('qtht/models/GoiyModel.php');
require_once('auth/models/ResourceUserModel.php');
require_once 'hscv/models/HosoluutheodoiModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/bosunghosoModel.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/ChuyenXuLyModel.php';
require_once 'motcua/models/dkWebModel.php';
include_once 'motcua/models/linhvucmotcuaModel.php';
require_once 'hscv/models/ThumuccanhanModel.php';
require_once 'taphscv/models/Taphscv_TaphosocongviecModel.php';
require_once "giaoviec/models/giaoviecservice.php";
class Hscv_HscvController extends Zend_Controller_Action {

    /**
     * The default action - show the home page
     */
    public function indexAction() {

    }

    /**
     * Tạo form list cho HSCV chung chung
     */
    public function listAction() {
        $this->view->start = (float) array_sum(explode(' ', microtime()));
        global $auth;
        $user = $auth->getIdentity();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        //$param['NAMECV'] = Convert::ConvertToUnicode($param['NAMECV']);
        $config = Zend_Registry::get('config');

        //tinh chỉnh param
        if ($param['NGAY_BD'] != "") {
            $ngaybd = $param['NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybd = implode("-", array_reverse(explode("/", $ngaybd)));
        }
        if ($param['NGAY_KT'] != "") {
            $ngaykt = $param['NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaykt = implode("-", array_reverse(explode("/", $ngaykt)));
        }
        $realyear = QLVBDHCommon::getYear();

        $id_thumuc = $param["id_thumuc"];
        $id_thumuc = $id_thumuc == "" ? 1 : $id_thumuc;
        //check quyen vao thu muc neu thu muc >1
        if ($id_thumuc > 1) {
            if (!ThuMucModel::CheckPermission($user->ID_U, $id_thumuc)) {
                $this->_redirect("/default/error/error?control=hscv&mod=hscv&id=ERR11001001");
            }
        }
        $ID_LOAIHSCV = $param['ID_LOAIHSCV'];
        $NAME = $param['NAMECV'];
        $MASOHOSO = $param['MASOHOSO'];
        $TENTOCHUCCANHAN = $param['TENTOCHUCCANHAN'];

        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        $TRANGTHAI = $param['TRANGTHAI'];
        $GOPY = $param['GOPY'];
        $INFILE = $param['INFILE'];
        $INNAME = $param['INNAME'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }

        //tim kiem noi dung van ban den
        $ID_SVB = (int) $param['ID_SVB'];
        $ID_LVB = (int) $param['ID_LVB'];
        $SODEN = $param['SODEN'];
        $SOKYHIEU = $param['SOKYHIEU'];
        $DUTHAO = $param['duthao'];

        if ($param['NGAYDEN_BD'] != "") {
            $NGAYDEN_BD = $param['NGAYDEN_BD'] . "/" . QLVBDHCommon::getYear();
            $NGAYDEN_BD = implode("-", array_reverse(explode("/", $NGAYDEN_BD)));
        }
        if ($param['NGAYDEN_KT'] != "") {
            $NGAYDEN_KT = $param['NGAYDEN_KT'] . "/" . QLVBDHCommon::getYear();
            $NGAYDEN_KT = implode("-", array_reverse(explode("/", $NGAYDEN_KT)));
        }

        if ($param['NGAYBANHANH_BD'] != "") {
            $NGAYBANHANH_BD = $param['NGAYBANHANH_BD'] . "/" . QLVBDHCommon::getYear();
            $NGAYBANHANH_BD = implode("-", array_reverse(explode("/", $NGAYBANHANH_BD)));
        }
        if ($param['NGAYBANHANH_KT'] != "") {
            $NGAYBANHANH_KT = $param['NGAYBANHANH_KT'] . "/" . QLVBDHCommon::getYear();
            $NGAYBANHANH_KT = implode("-", array_reverse(explode("/", $NGAYBANHANH_KT)));
        }

        if ($param['NHAN_NGAY_BD'] != "") {
            $NHAN_NGAY_BD = $param['NHAN_NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $NHAN_NGAY_BD = implode("-", array_reverse(explode("/", $NHAN_NGAY_BD)));
        }
        if ($param['NHAN_NGAY_KT'] != "") {
            $NHAN_NGAY_KT = $param['NHAN_NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $NHAN_NGAY_KT = implode("-", array_reverse(explode("/", $NHAN_NGAY_KT)));
        }


        $parameter = array(
            "DUTHAO" => $DUTHAO,
            "ID_THUMUC" => $id_thumuc,
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "NGAY_BD" => $ngaybd,
            "NGAY_KT" => $ngaykt,
            "TRANGTHAI" => $TRANGTHAI,
            "ID_U" => $user->ID_U,
            "ID_G" => $user->ID_G,
            "ID_DEP" => $user->ID_DEP,
            "NAME" => $NAME,
            "MASOHOSO" => $MASOHOSO,
            "TENTOCHUCCANHAN" => $TENTOCHUCCANHAN,
            "SCOPE" => $scope,
            "CODE" => $param['code'],
            "OBJECT" => $param['OBJECT'],
            "ID_SVB" => $param['ID_SVB'],
            "ID_LVB" => $param['ID_LVB'],
            "SODEN" => $param['SODEN'],
            "SOKYHIEU" => $SOKYHIEU,
            "NGAYDEN_BD" => $NGAYDEN_BD,
            "NGAYDEN_KT" => $NGAYDEN_KT,
            'NGAYBANHANH_BD' => $NGAYBANHANH_BD,
            'NGAYBANHANH_KT' => $NGAYBANHANH_KT,
            'NHAN_NGAY_BD' => $NHAN_NGAY_BD,
            'NHAN_NGAY_KT' => $NHAN_NGAY_KT,
            "INNAME" => $param['INNAME'],
            "INFILE" => $param['INFILE'],
            "ID_HSCV" => $param['idhscv'],
            "GOPY" => $param['GOPY']
        );



        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        if (!$MASOHOSO) {
            $hscvcount = $hscv->Count($parameter);
        } else {
            $hscvcount = 100;
            $limit = 100;
        }
        if (($page - 1) * $limit == $hscvcount && $hscvcount > 0
            )$page--;
        //Lấy dữ liệu
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->data = $hscv->SelectAll($parameter, ($page - 1) * $limit, $limit, "ID_HSCV desc");
        $this->view->realyear = $realyear;
        $this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
        $this->view->NAME = $NAME;
        $this->view->NGAY_BD = $param['NGAY_BD'];
        $this->view->NGAY_KT = $param['NGAY_KT'];
        $this->view->NAME = $NAME;
        $this->view->MASOHOSO = $MASOHOSO;
        $this->view->TENTOCHUCCANHAN = $TENTOCHUCCANHAN;

        $this->view->TRANGTHAI = $TRANGTHAI;
        $this->view->GOPY = $GOPY;
        $this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
        $this->view->id_thumuc = $id_thumuc;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;
        $this->view->user = $user;
        $this->view->code = $param['code'];
        $this->view->idhscv = $param['idhscv'];
        $this->view->OBJECT = $param['OBJECT'];

        //tim kiem van ban den
        $this->view->ID_SVB = $param['ID_SVB'];
        $this->view->ID_LVB = $param['ID_LVB'];
        $this->view->SODEN = $param['SODEN'];
        $this->view->SOKYHIEU = $SOKYHIEU;
        $this->view->NGAYDEN_BD = $param['NGAYDEN_BD'];
        $this->view->NGAYDEN_KT = $param['NGAYDEN_KT'];
        $this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
        $this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
        $this->view->NHAN_NGAY_KT = $param['NHAN_NGAY_KT'];
        $this->view->NHAN_NGAY_BD = $param['NHAN_NGAY_BD'];
        $this->view->DUTHAO = $DUTHAO;

        //tim kiem ho so mot cua
        //Create button
        if ($id_thumuc < 2) {
            $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
            if (count($createarr) > 0) {
                QLVBDHButton::AddButton($createarr["NAME"], "", "AddNewButton", "CreateButtonClick(\"" . $createarr["LINK"] . "/type/$ID_LOAIHSCV/wf_id_t/" . $createarr["ID_T"] . "/year/" . $realyear . "\")");
            }
        }
        //page
        $this->view->title = "Hồ sơ công việc";
        if (strtoupper($param['code']) == 'OLD') {
            $this->view->title = "hồ sơ công việc";
        } else if (strtoupper($param['code']) == 'PRE') {
            $this->view->title = "hồ sơ công việc";
        } else if (strtoupper($param['code']) == 'ZIP') {
            $this->view->title = "hồ sơ công việc lưu trữ";
        } else if (strtoupper($param['code']) == 'PHOIHOP') {
            $this->view->title = "Hồ sơ công việc phối hợp";
        } else {
            $this->view->title = "hồ sơ công việc";
        }

        $actid = ResourceUserModel::getActionByUrl("hscv", "hscv", "multisend");
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            QLVBDHButton::AddButton("Chuyển nhiều", "", "MultiButton", "document.location.href=\"/hscv/hscv/multisend\";");
        }
		QLVBDHButton::AddButton("Lưu tham khảo nhiều", "", "MultiButton", "document.location.href=\"/hscv/hscv/multiketthuc\";");

        $this->view->page = $page;
        $this->view->limit = $limit;

        $thumuc = new ThuMucModel();
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/hscv/list/id_thumuc/" . $id_thumuc . "/code/" . $param['code']);
        //$this->view->thumuc = $thumuc->SelectAll(($page-1)*$limit,$limit,4);
        $this->view->thumuc = ThuMucModel::GetTreeByPermission($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());

        //thangtba thêm: lấy ý kiến của lãnh đạo về đề xuất chờ xl
        require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
        $ykien = Hscv_DexuatchoxulyModel::getYKienLD();
        //var_dump($ykien);
        $this->view->dataYkien = $ykien;
        //end thangtba
		if($param['nolayout']==1){
			$this->_helper->layout->disableLayout();
			$this->renderScript("hscv/listnolayout.phtml");
		}
//        $this->view->chucdanh = hosocongviecModel::fetchChucDanh();
    }

    /**
     * Tạo form nhập liệu cho bút phê văn bản đến
     */
    function inputbutpheAction() {		
        $this->_helper->layout->disableLayout();

        global $auth;
        $user = $auth->getIdentity();
		//parameter
        $this->parameter = $this->getRequest()->getParams();		
        $this->view->wf_id_t = $this->parameter["wf_id_t"];
        $this->view->ID_HSCV = $this->parameter["id"];
        $this->view->year = $this->parameter["year"];
        $this->view->code = $this->parameter["code"];		
        //thangtba: thêm người bút phê
        $this->view->nguoibutphe = $user;
        //end thangtba
        $this->view->processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($this->view->ID_HSCV);
        if ($this->view->code == "butphe") {
            $this->_redirect("hscv/hscv/butphe/id/" . $this->parameter["id"] . "/wf_id_t/" . $this->parameter["wf_id_t"]);
        }else if($this->view->code == "butphenomulti"){
			$this->_redirect("hscv/hscv/butphe/id/" . $this->parameter["id"] . "/wf_id_t/" . $this->parameter["wf_id_t"] . "/nomulti/1");
		}
    }

    function butpheAction() {
		//$this->view->maso="546f0d25b8d8c5c95b2f4b530154a0bf";
        $this->_helper->layout->disableLayout();
        global $auth;
        $user = $auth->getIdentity();
        $this->view->user = $user;
        $goiy = new GoiyModel();
        //parameter
        $this->parameter = $this->getRequest()->getParams();		
        $this->view->wf_id_t = $this->parameter["wf_id_t"];
        $this->view->ID_HSCV = $this->parameter["id"];
        $vbdenmodel = new vbdenModel(QLVBDHCommon::getYear());
        $vbden = $vbdenmodel->findByHscv($this->view->ID_HSCV);
        $vbdeninfo = $vbdenmodel->find($vbden['ID_VBD'])->current();
        $masolienthong = $vbdeninfo->MASOLIENTHONG;
		$this->view->datacongviec = array();
		if($masolienthong != NULL)
		{
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $giaoviecservice = new GiaoViecService();
        $token = $giaoviecservice->login($madonvi,md5($password),"");
        $reCongViec = $giaoviecservice->SelectCongViecByIdlt(
                $token
                ,$masolienthong
        );
        $this->view->datacongviec = json_decode($reCongViec);
		}
		 // Lay nhomcv tu hscv
		$hscv = new hosocongviecModel();
		$hscvinfo = hosocongviecModel::getDetailById($this->parameter["id"]);
		$this->view->hscv = $hscvinfo;	
		
        // lay thong tin van ban den
         $vbd = new vbdenModel(QLVBDHCommon::getYear());
        $vbden = $vbd->findByHscv($this->parameter["id"]);
        $this->view->vanbanden = vbdenModel::getdetailVBD($vbden["ID_VBDEN"]);
                
	$this->view->nomulti = $this->parameter["nomulti"];
        $this->view->processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($this->view->ID_HSCV);
        $this->view->hanxuly = WFEngine::GetHanXuLy($this->view->wf_id_t);
        $this->view->ID_DEP = $user->ID_DEP;
        $this->view->goiy = $goiy->SelectAll(0, 0, "");		
		$this->view->hienthi= hosocongviecModel::getmasoByHSCVId($this->parameter["id"]);		
    }

    function savebpAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        
        $parameter = $this->getRequest()->getParams();
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $giaoviecservice = new GiaoViecService();
//        echo '<pre>';
//        print_r($parameter);exit;
        $idu = $parameter["ID_U"];
        $hanxuly = $parameter["HANXULY"];
        $type = $parameter["TYPE"];
        $id = $parameter["id"];	
        $wf_id_t = $parameter["wf_id_t"];
        $noidung = $parameter["NOIDUNG"];
        $istheodoi = $parameter["istheodoi"];
        $sms = $parameter["SMS"];
        $email = $parameter["EMAIL"];
        $arrnv = $parameter["checknv"];
        // Add param giao viec
        $loaicongviec = $parameter["LOAI_CV"];
		$nhomcv = $parameter["NHOMCV_GV"];
        $nhomcv_giaoviec = $parameter["LOAI_CV"];
        $danhmucnoibo = $parameter["DANHMUCNOIBO"];
        $giaitrinh = $parameter["GIAITRINH"];		
		if($nhomcv==3){
			$hanxulygiaoviec = $parameter["HANXULY_GIAOVIEC_UB"];
			$typehanxulygiaoviec = $parameter["TYPE_HANXULY_GIAOVIEC_UB"];
		
		}elseif($nhomcv==1){
			$hanxulygiaoviec = $parameter["HANXULY_GIAOVIEC_NB"];
			$typehanxulygiaoviec = $parameter["TYPE_HANXULY_GIAOVIECNB"];
			
		}else{
		
		}		
        $idusend = array();
        $hanxulysend = array();
        $noidungsend = array();
        $idudb = array();
        $iduph = array();
        $dbsms = array();
        $dbemail = array();
        for ($i = 0; $i < count($idu); $i++) {
            if ($type[$i] == 2) {
                $idusend[] = $idu[$i];
                $sendsms[] = $sms[$i];
                $sendemail[] = $email[$i];
                $hanxulysend[] = $hanxuly[$i];
                $noidungsend[] = $noidung;
            } else if ($type[$i] == 1) {
                $idudb[] = $idu[$i];
                $dbsms[] = $sms[$i];
                $dbemail[] = $email[$i];
            } else if ($type[$i] == 3) {
                $iduph[] = $idu[$i];
                $dbsms[] = $sms[$i];
                $dbemail[] = $email[$i];
            } else if ($type[$i] == 4) {
                $iducnb[] = $idu[$i];
            }
        }

        $processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($id);
        $usernk = $processinfo["ID_U_NK"];
        $usernc = $processinfo["ID_U_NC"];
        $vbd = new vbdenModel(QLVBDHCommon::getYear());
        $vbden = $vbd->findByHscv($id);
        $vbdeninfo = $vbd->find($vbden['ID_VBD'])->current();
        $masolienthong = $vbdeninfo->MASOLIENTHONG;
        $db->update(QLVBDHCommon::Table('vbd_vanbanden'), array('NGUOIBUTPHE' => $user->ID_U,'ID_HSCV' => $id), "ID_VBD=".$vbden['ID_VBDEN']);
         $hscv = new hosocongviecModel();
         // Cap nhat thong tin giao viec vao van ban den, hscv

         if ($nhomcv != 0 && $hanxulygiaoviec != "") {
             $hscv->update(array("HANXULY_GIAOVIEC"=>$hanxulygiaoviec,"TYPEHANXULY"=>$typehanxulygiaoviec,"LOAICV_GIAOVIEC"=>$loaicongviec,"ID_U_XULYGIAOVIEC"=>$idugiaoviec,"ID_U_PHOIHOPGIAOVIEC"=>$iduphoihop),"ID_HSCV=".$id);
         }  else {
             $hscv->update(array("LOAICV_GIAOVIEC"=>$loaicongviec),"ID_HSCV=".$id);
         }
         $vbd->update(array("NHOMCVVBD"=>$nhomcv,"GIAITRINH"=>$giaitrinh,"ID_DMNB"=>$danhmucnoibo),"ID_VBD=".$vbden["ID_VBDEN"]);
                
        //insert vao butphe
        if ($noidung != "") {
            $db->insert(QLVBDHCommon::Table("HSCV_BUTPHE"), array(
                "NOIDUNG" => $noidung,
                "NGUOIKY" => $usernk,
                "NGUOICHUYEN" => $usernc,
                "ID_VBD" => $vbden['ID_VBDEN'],
                "NGAYBP" => date("Y-m-d H:i:s")
            ));
        }
        $lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());		
		$id_u_vanthu=$lc->getNguoichuyenbyidhscv($id);
		$idudb[]=$id_u_vanthu;
        $lc->send($vbden['ID_VBDEN'], $idudb, $noidung, $user->ID_U, 0, $parameter, $dbsms, $dbemail);
		$lc->update(array("IS_BUTPHE" => 1), "ID_VBD='".(int)$vbden['ID_VBDEN']."' AND NGUOICHUYEN ='".$user->ID_U."' AND NGUOINHAN ='".$id_u_vanthu."'");
        if (count($idusend) > 0) {
            try {
                hosocongviecModel::SendAll(
                                $id,
                                $user->ID_U,
                                $wf_id_t,
                                array(),
                                array(),
                                array(),
                                array(),
                                array(),
                                array(),
                                $idusend,
                                $noidungsend,
                                $hanxulysend,
                                $vbden['ID_VBDEN'],
                                "ID_VBDEN",
                                "VBD_FK_VBDEN_HSCVS",
                                $sendsms,
                                $sendemail,
                                $arrnv
                );
                    if(($masolienthong != '' || $masolienthong != NULL) && COUNT($arrnv) > 0)
					{
                        $model = new UsersModel();
                        $nguoinhan = $model->getName($idusend[0]);
                        $token = $giaoviecservice->login($madonvi,md5($password),"");
                        $giaoviecservice->UpdateTrangThaiCongViec(
                                    $token
                                    ,$masolienthong
                                    ,1
                                    ,$idusend[0]
                                    ,$nguoinhan['TENNGUOITAO']
                            );
					}
            } catch (Exception $ex) {
                echo $ex->__toString();
            }
        } else {
            if ($istheodoi == 1) {
                HosoluutheodoiModel::luuTheodoi(QLVBDHCommon::getYear(), $id, "", $user->ID_U);
            }
        }
        if (count($iduph) > 0) {
            PhoiHopModel::AddPhoiHop($iduph, $vbden['ID_VBDEN']);
			$lc->send($vbden['ID_VBDEN'],$iduph,$noidung,$user->ID_U,0,null,$dbsms,$dbemail,1);
        }

        $id_cqs = $parameter["ID_CQ"];
        //var_dump($id_cqs); exit;
        if (count($id_cqs) > 0) {
            //chuyen van ban noi bo
            require_once('hscv/models/chuyennoiboModel.php');

            //if($noidung)
            //chuyennoiboModel::luuChuyennoibocobutphe($vbden['ID_VBDEN'],$user->ID_U,$id_cqs,$noidung);
            //else
            chuyennoiboModel::luuChuyennoibo($vbden['ID_VBDEN'], $user->ID_U, $id_cqs, $noidung);
        }
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    /**
     * Lưu nội dung bút phê
     */
    function savebutpheAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity(); 
        //Lấy parameter
        $param = $this->getRequest()->getParams();
//        echo '<pre>';
//        print_r($param);exit;
        $idhscv = $param["id"];		
        $wf_id_t = $param["wf_id_t"];
        $wf_nexttransition = $param["wf_nexttransition"];
        
        $wf_nextuser = $param["wf_nextuser"];
        $wf_nextg = $param["wf_nextg"];//
        $wf_nextdep = $param["wf_nextdep"];//
        
        $wf_nextgncc = $param["wf_selg"];//
        $wf_nextdepncc = $param["wf_seldep"];//
        
        $wf_name_user = $param["wf_name_user"];
        $wf_name_g = $param["wf_name_g"];
        $wf_name_dep = $param["wf_name_dep"];
        $wf_hanxuly_user = $param["wf_hanxuly_user"];
        $wf_hanxuly_g = $param["wf_hanxuly_g"];
        $wf_hanxuly_dep = $param["wf_hanxuly_dep"];
        $year = QLVBDHCommon::getYear();

        $processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
        $usernk = $processinfo["ID_U_NK"];
        $usernc = $processinfo["ID_U_NC"];
        $noidung = $param["NOIDUNG"];
        $hanxuly = $param["HANXULY"];
		 $idudb = array();
        if ($hanxuly == ""
            )$hanxuly = 0;

        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        $db->beginTransaction();
        try {
            $vbd = new vbdenModel(QLVBDHCommon::getYear());
            $vbden = $vbd->findByHscv($idhscv);
            $db->insert("HSCV_BUTPHE_" . $year, array(
                "NOIDUNG" => $noidung,
                "NGUOIKY" => $user->ID_U,
                "NGUOICHUYEN" => $user->ID_U,
                "HANXULY" => $wf_hanxuly_user,
                "ID_VBD" => $vbden['ID_VBDEN'],
                "ID_HSCV" => $idhscv,
                "NGAYBP" => date("Y-m-d H:i:s")
            ));
            if (!is_array($param["wf_nextuser"]) &&
                    !is_array($param["wf_nextg"]) &&
                    !is_array($param["wf_nextdep"])
            ) {
//                echo '<pre>';
//                print_r($param['depallok']);exit;
//                echo $idhscv.'-'. $wf_nexttransition.'-'.$user->ID_U.'-'. $wf_nextuser.'-'. WFEngine::$WFTYPE_USER.'-'. $wf_name_user.'-'. $wf_hanxuly_user;exit;
                if((int)$param['depallok']==1){
                    //send next phong
                   if (WFEngine::SendNextUserByObjectId2($idhscv, $wf_nexttransition,$user->ID_U, $wf_nextdepncc, WFEngine::$WFTYPE_DEP, $wf_name_user, $wf_hanxuly_user) == 1) {
                        $db->commit();
                    } else {
                        $db->rollBack();
                    } 
                } 
                if((int)$param['groallok']==1){
                    //send next nhom
//                    echo $idhscv.'-'. $wf_nexttransition,$user->ID_U.'-'. $wf_nextgncc.'-'. WFEngine::$WFTYPE_GROUP.'-'. $wf_name_user.'-'.$wf_hanxuly_user;exit;
                    if (WFEngine::SendNextUserByObjectId2($idhscv, $wf_nexttransition,$user->ID_U, $wf_nextgncc, WFEngine::$WFTYPE_GROUP, $wf_name_user, $wf_hanxuly_user) == 1) {
                        $db->commit();
                    } else {
                        $db->rollBack();
                    } 
                }
                
                if((int)$param['groallok']!=1 && (int)$param['depallok']!=1){
                    //send next user
                    if (WFEngine::SendNextUserByObjectId2($idhscv, $wf_nexttransition,$user->ID_U, $wf_nextuser, WFEngine::$WFTYPE_USER, $wf_name_user, $wf_hanxuly_user) == 1) {
                        $db->commit();
                    } else {
                        $db->rollBack();
                    } 
                }
                
                //send next group
            } else {
                hosocongviecModel::SendAll(
                                $idhscv,
                                $user->ID_U,
                                $wf_nexttransition,
                                $wf_nextg,
                                $wf_name_g,
                                $wf_hanxuly_g,
                                $wf_nextdep,
                                $wf_name_dep,
                                $wf_hanxuly_dep,
                                $wf_nextuser,
                                $wf_name_user,
                                $wf_hanxuly_user,
                                $vbden['ID_VBDEN'],
                                "ID_VBDEN",
                                "VBD_FK_VBDEN_HSCVS"
                );
                $db->commit();
            }

			$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
			$id_u_vanthu=$lc->getNguoichuyenbyidhscv($idhscv);
			$idudb[]=$id_u_vanthu;
		    $lc->send($vbden['ID_VBDEN'],$idudb,$noidung,$user->ID_U,0,null,$dbsms,$dbemail);			
            $lc->update(array("IS_BUTPHE" => 1), "ID_VBD='".(int)$vbden['ID_VBDEN']."' AND NGUOICHUYEN ='".$user->ID_U."' AND NGUOINHAN ='".$id_u_vanthu."'");
			//phien ban du thao ko cho xoa sau khi chuyen
			$dt = new PhienBanDuThaoModel(QLVBDHCommon::getYear());
			$id_pb = $dt->getidphienbanbyidhscv($idhscv);
			foreach($id_pb as $id_pbitem)
			$db->update("HSCV_PHIENBANDUTHAO_" . $year,array("IS_XOA"=>0),"ID_DUTHAO='".(int)$id_pbitem['ID_DUTHAO']."' AND ID_U ='".$user->ID_U."'");

        } catch (Exception $ex) {
            //$ex->__toString();exit;
            $db->rollBack();
        }
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    /**
     * Xem nội dung bút phê
     */
    function viewbutpheAction() {
        global $auth;
        $user = $auth->getIdentity();
		$this->view->idu = $user->ID_U;
        $this->_helper->layout->disableLayout();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
		$this->view->idhscv = $idhscv;
        $idvbd = $param["id_vbd"];
        $year = QLVBDHCommon::getYear();
        //Tạo đối tượng
        $butphe = new butpheModel($year);
        if ($idhscv > 0) {
            $this->view->data = $butphe->SelectByIdHSCV($idhscv);
        } else {
            $this->view->data = $butphe->SelectByIdVBD($idvbd);
        }
    }

    /**
     * Chuyển bút phê
     */
    function sendbutpheAction() {
        $this->_helper->layout->disableLayout();

        global $auth;
        $user = $auth->getIdentity();

        //parameter
        $this->parameter = $this->getRequest()->getParams();
        $this->view->wf_id_t = $this->parameter["wf_id_t"];
        $this->view->ID_HSCV = $this->parameter["id"];
        $this->view->year = $this->parameter["year"];
        Zend_Registry::set("year", $this->view->year);
    }

    /**
     * Chuyển lưu trữ
     */
    function zipAction() {
        $this->_helper->layout->disableLayout();

        global $auth;
        $user = $auth->getIdentity();

        //parameter
        $this->parameter = $this->getRequest()->getParams();
        $this->view->wf_id_t = $this->parameter["wf_id_t"];
        $this->view->ID_HSCV = $this->parameter["id"];
        $this->view->year = $this->parameter["year"];
        $this->view->code = $this->parameter["code"];
        Zend_Registry::set("year", $this->view->year);
        $this->view->processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($this->view->ID_HSCV);
    }

    /**
     * Chuyển lưu trữ
     */
    function savezipAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
        $wf_id_t = $param["wf_id_t"];
        $wf_nexttransition = $param["wf_nexttransition"];
        $wf_nextuser = $param["wf_nextuser"];
        $wf_name_user = $param["wf_name_user"];
        $wf_hanxuly_user = $param["wf_hanxuly_user"];
        $year = QLVBDHCommon::getYear();
        $thumuc = $param["THUMUC"];
        Zend_Registry::set("year", $year);

        $db->beginTransaction();
        try {
            $db->update("WF_PROCESSITEMS_" . $year, array("IS_FINISH" => 1), "ID_O = " . $idhscv);
			$vbd = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbd->findByHscv($idhscv);
			if($vbden['ID_VBDEN']>0){
				$db->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>QLVBDHCommon::getTreHan($vbden['NGAYTAO'],$vbden['HANXULYTOANBO']),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
			}
            //send next user
            if (WFEngine::SendNextUserByObjectId2($idhscv, $wf_nexttransition, $user->ID_U, $wf_nextuser, WFEngine::$WFTYPE_USER, $wf_name_user, $wf_hanxuly_user) == 1) {
                $db->commit();
            } else {
                $db->rollBack();
            }
        } catch (Exception $ex) {
            echo $ex->__toString();
            $db->rollBack();
        }
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    public function wayAction() {
        $this->_helper->layout->disableLayout();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
        $year = QLVBDHCommon::getYear();
        $type = $param["type"];
        $code = $param["code"];
        //thangtba them zo
        require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
        $id_hscv_choxl = $param['id_dxchoxl'];
        $this->view->id_dxchoxl = $id_hscv_choxl;

        $id_dxcxl = $param['id_dxcxl'];
        $ykien_noidung = Hscv_DexuatchoxulyModel::getYKienLdByIdHscv($id_dxcxl);
        $this->view->id_dxcxl=$id_dxcxl;
        $this->view->ykien=$ykien_noidung['YKIEN'];
        $this->view->noidung=$ykien_noidung['NOIDUNG'];
        //end thangtba them zo
        //Tao object
        $idobject = 0;
        $way = array();
        //if ($type == 1) {
            $lcvbd = new vbd_dongluanchuyenModel($year);
            $vbden = new vbdenModel($year);
            $idobject = $vbden->findByHscv($idhscv);
            $idobject = $idobject['ID_VBD'];
            $way = $lcvbd->way($idobject);
            require('hscv/models/chuyennoiboModel.php');
            $this->view->lcnoibo = chuyennoiboModel::getLuanchuyennoibo($idobject);
        //}

        $this->view->sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);
        //var_dump($this->view->sendprocess);
        $this->view->ID_HSCV = $idhscv;
        $this->view->type = $type;
        $this->view->year = QLVBDHCommon::getYear();
        $this->view->way = $way;
        $this->view->code = $code;
        $this->view->idobject = $idobject;
        //kiem tra dieu kien truy cap
        $isreadonly = $this->_request->getParam('isreadonly');
        if (!$isreadonly)
            $isreadonly = 0;
        $isCapnhat = 1;
        if (hosocongviecModel::isLuutru($idhscv, $year) == true || $isreadonly == 1) {
            $isCapnhat = 0;
        } else {
            $isCapnhat = 1;
        }
        $this->view->isCapnhat = 0;
    }

    function sendAction() {
        $this->_helper->layout->disableLayout();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
        $year = QLVBDHCommon::getYear();
        $type = $param["type"];
        $code = $param["code"];
        $idvbd = $param["idvbd"];

        $this->view->ID_HSCV = $idhscv;
        $this->view->year = $year;
        $this->view->type = $type;
        $this->view->code = $code;
        $this->view->idvbd = $idvbd;
    }

    function savesendAction() {
        global $auth;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];

        $year = QLVBDHCommon::getYear();
        $type = $param["type"];
        $code = $param["code"];
        $idvbd = $param["idvbd"];
        //echo $idvbd;
        //Văn bản đến
        if ($type == 1) {
            if ($idvbd == 0) {
                //Lấy ID văn bản đến từ idhscv
                $vbden = new vbdenModel($year);
                $lc = new vbd_dongluanchuyenModel($year);
                $idvbd = $vbden->findByHscv($idhscv);
                $idvbd = $idvbd['ID_VBD'];
                $lc->send($idvbd, $param['ID_U'], $param['NOIDUNG'], $user->ID_U, 0, $param);
            } else {
                $lc = new vbd_dongluanchuyenModel($year);
                //Thêm mới vào dòng luân chuyển
                $lc->send($idvbd, $param['ID_U'], $param['NOIDUNG'], $user->ID_U, 0, $param);
            }
        } else if ($type == 2) {//Văn bản đi
        }
        //var_dump($this->_request->getParams());
        //echo $idvbd;
        if ($code == "vbd") {
            echo "<script>window.parent.loadDivFromUrl('groupcontent" . $idvbd . "','/vbden/vbden/way/id_vbd/" . $idvbd . "/id/" . $idhscv . "/year/" . $year . "/type/" . $type . "/code/vbd',1);</script>";
        } else {
            echo "<script>window.parent.loadDivFromUrl('groupcontent" . $idhscv . "','/hscv/hscv/way/id/" . $idhscv . "/year/" . $year . "/type/" . $type . "',1);</script>";
        }
        exit;
    }

    function thuhoiAction() {
        $this->_helper->layout->disableLayout();
        $this->parameter = $this->getRequest()->getParams();
        //$this->view->current = WFEngine::GetCurrentTransitionInfoByIdHscv($this->parameter["id"]);
        $this->view->ID_HSCV = $this->parameter["id"];
        $this->view->year = $this->parameter["year"];
    }

    function savethuhoiAction() {


        global $auth;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        $hscv = new hosocongviecModel();
        $current = WFEngine::GetCurrentTransitionInfoByIdHscv($param['id']);
        //$code = hosocongviecModel::getTranspollCodeByIdTrans($current["ID_T"]);
        //echo "<script>alert('$code');</script>";
        //exit;
        try {
            if (hosocongviecModel::getTranspollCodeByIdTrans($current["ID_T"]) == "MCTHSKXL") {
                ChuyenXuLyModel::updateThuhoiKhongxulyMotcua($param['id']);
            }
        } catch (Exception $ex) {

        }
        //thu hoi van ban ban hanh
        try{
            $VBDTModel = new VanBanDuThaoModel($param['year']);
            $VBDTModel->updateTrangthaiByIdHSCV((int) $param['id'],3);
        }catch(Exception $ex){
        }
        
        $ok = $hscv->rollback($param['id'], $user->ID_U);
        if ($ok == 1) {
            $hscv->update(array("ID_THUMUC" => 1), "ID_HSCV=" . (int) $param['id']);
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã thu hồi công việc thành công.');window.parent.document.frm.submit();</script>";
        } else if ($ok == 2) {
            $hscv->getDefaultAdapter()->delete(QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS"), "ID_HSCV=" . (int) $param['id']);
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã thu hồi công việc thành công...');window.parent.document.frm.submit();</script>";
        } else {
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Thu hồi công việc không thành công.');window.parent.document.frm.submit();</script>";
        }
        exit;
    }

    function bosungAction() {
        $this->_helper->layout->disableLayout();
        $hscv = new hosocongviecModel();
        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
        $year = QLVBDHCommon::getYear();
        $this->view->data = $hscv->bosung($idhscv);
    }

    function tofolderAction() {
        global $db;
        $this->_helper->layout->disableLayout();
        $this->parameter = $this->getRequest()->getParams();
        $this->view->ID_HSCV = $this->parameter["id"];
        $thumuc = array();
        $auth = Zend_Registry::get('auth');
        $user = $auth->getIdentity();

        QLVBDHCommon::GetTreeWithJoinWhere(&$thumuc, "HSCV_THUMUC", "HSCV_PHANQUYEN_THUMUC", "ID_THUMUC", "ID_THUMUC", "ID_THUMUC", "ID_THUMUC_CHA", 1, 1, " ISCOQUAN=1 AND HSCV_PHANQUYEN_THUMUC.QUYENXEM = 1 AND HSCV_PHANQUYEN_THUMUC.ID_DEP=" . (int) $user->ID_DEP);
        $this->view->thumuc = $thumuc;
        if ($this->parameter["THUMUC"] > 1) {

            //check thu muc duoc chon voi thu muc co san
            //$rhscv = $db->query("SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv inner join HSCV_THUMUC thumuc on hscv.ID_THUMUC=thumuc.ID_THUMUC WHERE ID_HSCV=?",(int)$this->parameter["id"])->fetch();
            //da luu tru
            //if($rhscv["CNT"]==1){
            //check xem thu muc duoc cho la thu muc gi
            $rthumuc = $db->query("SELECT ISCOQUAN FROM HSCV_THUMUC WHERE ID_THUMUC=?", $this->parameter["THUMUC"])->fetch();
            if ($rthumuc["ISCOQUAN"] == 1) {
                //cap nhat thu muc co quan
                $hscv = new hosocongviecModel();
                $hscv->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), array("ID_THUMUC" => $this->parameter["THUMUC"]), "ID_HSCV=" . (int) $this->parameter["id"]);
                echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã lưu trữ thành công.');window.parent.document.frm.submit();</script>";
                exit;
            } else {
                //cap nhat thu muc co quan
                $hscv = new hosocongviecModel();
                $hscv->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), array("ID_THUMUC_HSCV" => $this->parameter["THUMUC"]), "ID_HSCV=" . (int) $this->parameter["id"]);
                echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã chuyển thư mục HSCV thành công.');window.parent.document.frm.submit();</script>";
                exit;
            }
            //}else{
            //}
        }
    }

    //action chuyen lai , khi vua moi them moi
    function chuyenlaiAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $this->view->id = $params['id'];
        $this->view->wf_id_t = $params['wf_id_t'];

        //exit;
    }

    //action chuyen lai , khi vua moi them moi
    function chuyenlaivtbpAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $this->view->id = $params['id'];
        //exit;
    }

    //luu chuyen lai sau khi them moi, can than !!! -> xoa ho so cong viec cu
    function savechuyenlaiAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $id_u = Zend_Registry::get('auth')->getIdentity()->ID_U;
        $id = $params['id'];
        $wf_id_t = $params['wf_id_t'];
        $this->view->id = $id;
        $this->view->wf_id_t = $wf_id_t;
        $year = QLVBDHCommon::getYear();
        //lay thong tin ve ho so cu
        $old_data = hosocongviecModel::getDetail($year, $id);
        $objhscv = new hosocongviecModel();

        //doi tuong db adapter
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        //Tro lai trang thai truoc do
        if ($re = WFEngine::RollBack($id, $id_u, true) != 0) {

            //chuyen lai cho nguoi khac
            $name = $old_data["NAME"];
            $userceceive = $params["wf_nextuser"];
            $noidung = $params["wf_name_user"];
            $hanxuly = $params["wf_hanxuly_user"];
            $next_transition_id = $params["wf_nexttransition"];

            if ($i = WFEngine::SendNextUserByObjectId($id, $next_transition_id, $id_u, $userceceive, $noidung, $hanxuly) > 0) {
                //cap nhat lai
                $extra = UsersModel::getEmloyeeNameByIdUser($userceceive);
                $dbAdapter->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), array("EXTRA" => $extra), "ID_HSCV=" . $id);
            }
        }
        //doan js refresh lai trang danh sach van ban vua xu ly
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    //luu chuyen lai sau khi them moi, can than !!! -> xoa ho so cong viec cu
    function savechuyenlaivtbpAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $id = $params['id'];
        $lastpl = WFEngine::GetCurrentTransitionInfoByIdHscv($id);
        $id_u = $lastpl["ID_U_NC"];
        $wf_id_t = $params['wf_id_t'];
        $this->view->id = $id;
        $this->view->wf_id_t = $wf_id_t;
        $year = QLVBDHCommon::getYear();
        //lay thong tin ve ho so cu
        $old_data = hosocongviecModel::getDetail($year, $id);
        $objhscv = new hosocongviecModel();

        //doi tuong db adapter
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        //echo $id_u;exit;
        //Tro lai trang thai truoc do
        if ($re = WFEngine::RollBack($id, $id_u, true) != 0) {

            //chuyen lai cho nguoi khac
            $name = $old_data["NAME"];
            $userceceive = $params["wf_nextuser"];
            $noidung = $params["wf_name_user"];
            $hanxuly = $params["wf_hanxuly_user"];
            $next_transition_id = $params["wf_nexttransition"];

            if ($i = WFEngine::SendNextUserByObjectId($id, $next_transition_id, $id_u, $userceceive, $noidung, $hanxuly) > 0) {
                //cap nhat lai
                $extra = UsersModel::getEmloyeeNameByIdUser($userceceive);
                $dbAdapter->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), array("EXTRA" => $extra), "ID_HSCV=" . $id);
            }
        }
        //doan js refresh lai trang danh sach van ban vua xu ly
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    function viewresultAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();

        $this->_helper->layout->disableLayout();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id"];
        $this->view->isreadonly = $param["isreadonly"];
        $this->view->ID_HSCV = $idhscv;
        //Tạo đối tượng
        $sql = "
			SELECT concat(SOKYHIEU,': ',TRICHYEU ) as TEN ,NGAYBANHANH,concat(emp.FIRSTNAME , ' ' , emp.LASTNAME) as EMPNAME , 0 As IS_DELETE 
			, 0 as ID_KQ_MC
			FROM
				" . QLVBDHCommon::Table("VBDI_VANBANDI") . " vbdi
				inner join QTHT_USERS u on u.ID_U = vbdi.NGUOIKY
				inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE ID_HSCV = ?

			union 

			select SOKYHIEU as TEN , NGAYBANHANH, NGUOIKY as EMPNAME , 1 as IS_DELETE,ID_KQ_MC from
			" . QLVBDHCommon::Table("motcua_ketqua") . "
			WHERE ID_HSCV = ?
		";
        $r = $db->query($sql, array($idhscv, $idhscv));
        $this->view->data = $r->fetchAll();
    }

    function giahanAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        //echo "alert('".$param['HANXULY']."');";
        if ($param['HANXULY'] != "") {
            $db->update(QLVBDHCommon::Table("WF_PROCESSLOGS"), array("HANXULY" => $param["HANXULY"]), "ID_PL=" . (int) $param["ID_PL"]);
        }
        exit;
    }

    function deleteAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $this->_helper->layout->disableLayout();
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id_hscv"];
        $type = $param["type"];
        $hscv = new hosocongviecModel();
        $hscv->deletehscv($idhscv, $user->ID_U, $type);
        $this->_redirect("/hscv/hscv/list/code/old");
        exit;
    }

    function deletevtbpAction() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $this->_helper->layout->disableLayout();
        $param = $this->getRequest()->getParams();
        $idhscv = $param["id_hscv"];
        $hscv = new hosocongviecModel();
        $actid = ResourceUserModel::getActionByUrl('hscv', 'hscv', 'deletevtbp');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            if (WFEngine::CanChuyenLaiForVTBP($idhscv)) {
                //echo "aa";exit;
                $hscv->deletehscvforvtbp($idhscv);
            }
        }
        $this->_redirect("/hscv/hscv/list/code/old");
        exit;
    }

    function listbosungAction() {
        global $auth;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        $config = Zend_Registry::get('config');

        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        $scope = array();
        if ($param['SCOPE']) {
            $scope = $param['SCOPE'];
        }
        if ($param['NGAY_BD'] != "") {
            $ngaybd = $param['NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybd = implode("-", array_reverse(explode("/", $ngaybd)));
        }
        if ($param['NGAY_KT'] != "") {
            $ngaykt = $param['NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaykt = implode("-", array_reverse(explode("/", $ngaykt)));
        }
        $ID_LOAIHSCV = $param['ID_LOAIHSCV'];
        $TENTOCHUCCANHAN = $param['TENTOCHUCCANHAN'];

        $parameter = array(
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "NGAY_BD" => $ngaybd,
            "MAHOSO" => $param['MABIENNHAN'],
            "NGAY_KT" => $ngaykt,
            "ID_U" => $user->ID_U,
            "TENTOCHUCCANHAN" => $TENTOCHUCCANHAN,
            "SCOPE" => $scope
        );

        $hscvcount = bosunghosoModel::Count($parameter);
        $this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
        $this->view->data = bosunghosoModel::SelectAll($parameter, ($page - 1) * $limit, $limit, "");

        $this->view->NGAY_BD = $param['NGAY_BD'];
        $this->view->NGAY_KT = $param['NGAY_KT'];
        $this->view->NAME = $param['NAMECV'];
        $this->view->TENTOCHUCCANHAN = $param['TENTOCHUCCANHAN'];
        $this->view->MABIENNHAN = $param['MABIENNHAN'];
        $this->view->title = "Hồ sơ một cửa chờ bổ sung";
        //$this->view->subtitle = "chờ bổ sung";
        $this->view->SCOPE = $scope;
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/hosoluutheodoi/index");
    }

    function updatedaxemphoihopAction() {
        $year = QLVBDHCommon::getYear();
        //$user = $auth->getIdentity();
        $params = $this->getRequest()->getParams();
        $id_u = $params["id_u"];
        $id_hscv = $params["id_hscv"];
        PhoiHopModel::updateDaXem($year, $id_u, $id_hscv);
        exit;
    }

    function qlvanbanketquaAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $idhscv = $params["ID_HSCV"];
        require_once('hscv/models/vanbanketquaModel.php');
        $is_delete = $params["isdelete"];
        if ($is_delete == 1) {
            $id_kq_mc = $params["id_kq_mc"];
            vanbanketquaModel::delete($id_kq_mc);
        } else {
            vanbanketquaModel::add($params);
        }

        $this->_redirect("/hscv/hscv/viewresult/iddivParent/groupcontent$idhscv/id/$idhscv");
    }

	function multiketthucAction(){
		global $auth;
        $user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();

		$this->view->title = "Lưu tham khảo hồ sơ công việc";
		QLVBDHButton::AddButton("Lưu tham khảo", "", "SaveButton", "CreateButtonClick()");

		$OBJECT = $param['ID_LVB']>0?"VBD":"";
		$this->view->ID_LVB = $param['ID_LVB'];
		$parameter = array(
            "ID_THUMUC" => 1,
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "OBJECT" => $OBJECT,
			"ID_LVB" => $param['ID_LVB'],
            "NGAY_BD" => $ngaybd,
            "NGAY_KT" => $ngaykt,
            "TRANGTHAI" => $TRANGTHAI,
            "ID_U" => $user->ID_U,
            "ID_G" => $user->ID_G,
            "ID_DEP" => $user->ID_DEP,
            "NAME" => $NAME,
            "TENTOCHUCCANHAN" => $TENTOCHUCCANHAN,
            "MASOHOSO" => $MASOHOSO,
            "SCOPE" => $scope,
            "CODE" => $param['code'],
            "ID_LV_MC" => $ID_LV_MC,
            "INFILE" => $INFILE,
            "INNAME" => $INNAME,
            "ID_PHUONG" => $ID_PHUONG
        );
		$tapHSCVModel = new Taphscv_TaphosocongviecModel();
		
		$object = new Taphscv();
		$object->_id_u = $user->ID_U;
		$object->_id_g = $user->ID_G;
		$object->_id_dept = $user->ID_DEP;

		$this->view->dataTapHSCV = $tapHSCVModel->getTapHSCV($object);
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());

		$hscv = new hosocongviecModel();
		$this->view->data = $hscv->SelectAll($parameter, 0, 0, "");
	}

    function multisendAction() {

        global $auth;
        $user = $auth->getIdentity();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $config = Zend_Registry::get('config');

        //tinh chỉnh param
        if ($param['NGAY_BD'] != "") {
            $ngaybd = $param['NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybd = implode("-", array_reverse(explode("/", $ngaybd)));
        }
        if ($param['NGAY_KT'] != "") {
            $ngaykt = $param['NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaykt = implode("-", array_reverse(explode("/", $ngaykt)));
        }
        $realyear = QLVBDHCommon::getYear();

        $id_thumuc = $param["id_thumuc"];
        $id_thumuc = $id_thumuc == "" ? 1 : $id_thumuc;
        //check quyen vao thu muc neu thu muc >1
        if ($id_thumuc > 1) {
            if (!ThuMucModel::CheckPermission($user->ID_U, $id_thumuc)) {
                $this->_redirect("/default/error/error?control=hscv&mod=hscv&id=ERR11001001");
            }
        }
        $ID_LOAIHSCV = $param['ID_LOAIHSCV'];
        $OBJECT = $param['OBJECT'];
        $ID_LV_MC = $param['ID_LV_MC'];
        $ID_PHUONG = $param['ID_PHUONG'];
        $NAME = $param['NAMECV'];
        $TENTOCHUCCANHAN = $param['NAMECD'];
        $MASOHOSO = $param['MASOHOSO'];
        $TRANGTHAI = $param['TRANGTHAI'];
        $INFILE = $param['INFILE'];
        $INNAME = $param['INNAME'];
        $scope = array();
        if ($param['SCOPE']) {
            $scope = $param['SCOPE'];
        }

        $parameter = array(
            "ID_THUMUC" => $id_thumuc,
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "OBJECT" => $OBJECT,
            "NGAY_BD" => $ngaybd,
            "NGAY_KT" => $ngaykt,
            "TRANGTHAI" => $TRANGTHAI,
            "ID_U" => $user->ID_U,
            "ID_G" => $user->ID_G,
            "ID_DEP" => $user->ID_DEP,
            "NAME" => $NAME,
            "TENTOCHUCCANHAN" => $TENTOCHUCCANHAN,
            "MASOHOSO" => $MASOHOSO,
            "SCOPE" => $scope,
            "CODE" => $param['code'],
            "ID_LV_MC" => $ID_LV_MC,
            "INFILE" => $INFILE,
            "INNAME" => $INNAME,
            "ID_PHUONG" => $ID_PHUONG
        );

        //Tạo đối tượng
        $hscv = new hosocongviecModel();

        //Lấy dữ liệu
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->data = $hscv->SelectAll($parameter, 0, 0, "");
        $this->view->realyear = $realyear;
        $this->view->OBJECT = $OBJECT;
        $this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
        $this->view->ID_LV_MC = $ID_LV_MC;
        $this->view->ID_PHUONG = $ID_PHUONG;
        $this->view->NAME = $NAME;
        $this->view->TENTOCHUCCANHAN = $TENTOCHUCCANHAN;
        $this->view->MASOHOSO = $MASOHOSO;
        $this->view->NGAY_BD = $param['NGAY_BD'];
        $this->view->NGAY_KT = $param['NGAY_KT'];
        $this->view->NAME = $NAME;
        $this->view->TRANGTHAI = $TRANGTHAI;
        $this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
        $this->view->id_thumuc = $id_thumuc;
        $this->view->SCOPE = $scope;
        $this->view->user = $user;
        $this->view->code = $param['code'];
        $this->view->idhscv = $param['idhscv'];
        $this->view->ID_U_RECEIVE = 0 + $param['ID_U_RECEIVE'];
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;
        $this->view->ID_U_NAME = $param['ID_U_NAME'];
        //page
        $this->view->title = "Hồ sơ công việc chuyển nhiều";
        //$this->view->subtitle = "Chuyển nhiều";

        $thumuc = new ThuMucModel();
        $this->view->thumuc = ThuMucModel::GetTreeByPermission($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
        $this->view->linhvuc = new linhvucmotcuaModel();
        $this->view->linhvuc = $this->view->linhvuc->SelectAll();
        $this->view->ID_U = $user->ID_U;
        $this->view->ID_DEP = 0 + $param['ID_DEP'];

        QLVBDHButton::AddButton("Chuyển", "", "SaveButton", "CreateButtonClick()");
        
    }

    function multisendsaveAction() {
        global $auth;
		global $db;
		$year = QLVBDHCommon::getYear();
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        for ($i = 0; $i < count($param['ID_HSCV']); $i++) {
            if ($param['ID_T'][$i] > 0) {
				$idhscv = $param['ID_HSCV'][$i];
				$macongviec = $param['MACONGVIEC_'.$idhscv];
				if(isset($macongviec) && $macongviec != '' && $macongviec != null)
				{
					$db->update("hscv_hosocongviec_" . $year,array("MACONGVIEC"=>$macongviec),"ID_HSCV='".$idhscv."'");
				}
                if (WFEngine::HaveSendAbleByTransition($param['ID_T'][$i], $param['ID_U_RECEIVE'])) {
                    WFEngine::SendNextUserByObjectId2($param['ID_HSCV'][$i], $param['ID_T'][$i], $user->ID_U, $param['ID_U_RECEIVE'], WFEngine::$WFTYPE_USER, "", $param['HANXULY'][$i]);
               }
            }
        }
        $this->_redirect("/hscv/hscv/multisend");
    }

    /**
     * Nhap lieu thu muc ho so cong viec
     */
    function inputthumuchscvAction() {
        global $db;
        $this->_helper->layout->disableLayout();
        $this->parameter = $this->getRequest()->getParams();
        $this->view->ID_HSCV = $this->parameter["id"];
        $this->view->ID_THUMUC_HSCV = $this->parameter["idtmhscv"];
        ;
        $thumuc = array();

        QLVBDHCommon::GetTreeWithWhere(&$thumuc, "HSCV_THUMUC", "ID_THUMUC", "ID_THUMUC_CHA", 1, 1, " ISCOQUAN=0 ");
        $this->view->thumuc = $thumuc;
    }

    /**
     * Luu thu muc hscv
     */
    function savethumuchscvAction() {

        $this->parameter = $this->getRequest()->getParams();
        //var_dump($parameter); exit;
        if ($this->parameter["THUMUC"] > 1) {

            $hscv = new hosocongviecModel();
            $hscv->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), array("ID_THUMUC_HSCV" => $this->parameter["THUMUC"]), "ID_HSCV=" . (int) $this->parameter["id"]);
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('Đã chuyển thư mục HSCV thành công.');window.parent.document.frm.submit();</script>";
        }
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>window.parent.document.frm.submit();</script>";
        exit;
    }

    function listthumuchscvAction() {
        $this->view->start = (float) array_sum(explode(' ', microtime()));
        global $auth;
        $user = $auth->getIdentity();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        //$param['NAMECV'] = Convert::ConvertToUnicode($param['NAMECV']);
        $config = Zend_Registry::get('config');

        //tinh chỉnh param
        if ($param['NGAY_BD'] != "") {
            $ngaybd = $param['NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybd = implode("-", array_reverse(explode("/", $ngaybd)));
        }
        if ($param['NGAY_KT'] != "") {
            $ngaykt = $param['NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaykt = implode("-", array_reverse(explode("/", $ngaykt)));
        }
        $realyear = QLVBDHCommon::getYear();

        $id_thumuc = $param["id_thumuc"];
        $id_thumuc = $id_thumuc == "" ? 1 : $id_thumuc;
        //check quyen vao thu muc neu thu muc >1
        if ($id_thumuc > 1) {
            if (!ThuMucModel::CheckPermission($user->ID_U, $id_thumuc)) {
                $this->_redirect("/default/error/error?control=hscv&mod=hscv&id=ERR11001001");
            }
        }
        $ID_LOAIHSCV = $param['ID_LOAIHSCV'];
        $NAME = $param['NAMECV'];
        $MASOHOSO = $param['MASOHOSO'];
        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        $TRANGTHAI = $param['TRANGTHAI'];
        $INFILE = $param['INFILE'];
        $INNAME = $param['INNAME'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }

        $parameter = array(
            "IS_THUMUC_HSCV" => 1,
            "ID_THUMUC" => $id_thumuc,
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "NGAY_BD" => $ngaybd,
            "NGAY_KT" => $ngaykt,
            "TRANGTHAI" => $TRANGTHAI,
            "ID_U" => $user->ID_U,
            "ID_G" => $user->ID_G,
            "ID_DEP" => $user->ID_DEP,
            "NAME" => $NAME,
            "MASOHOSO" => $MASOHOSO,
            "SCOPE" => $scope,
            "CODE" => $param['code'],
            "OBJECT" => $param['OBJECT'],
            "INNAME" => $param['INNAME'],
            "INFILE" => $param['INFILE']
        );

        //Tạo đối tượng
        $hscv = new hosocongviecModel();

        $hscvcount = $hscv->Count($parameter);
        if (($page - 1) * $limit == $hscvcount && $hscvcount > 0
            )$page--;
        //Lấy dữ liệu
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->data = $hscv->SelectAll($parameter, ($page - 1) * $limit, $limit, "");
        $this->view->realyear = $realyear;
        $this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
        $this->view->NAME = $NAME;
        $this->view->NGAY_BD = $param['NGAY_BD'];
        $this->view->NGAY_KT = $param['NGAY_KT'];
        $this->view->NAME = $NAME;
        $this->view->MASOHOSO = $MASOHOSO;
        $this->view->TRANGTHAI = $TRANGTHAI;
        $this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
        $this->view->id_thumuc = $id_thumuc;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;
        $this->view->user = $user;
        $this->view->code = $param['code'];
        $this->view->idhscv = $param['idhscv'];
        $this->view->OBJECT = $param['OBJECT'];


        //page
        $this->view->title = "thư mục hồ sơ công việc";
        //$this->view->subtitle = "<font color=red>danh sách thư mục hồ sơ công việc</font>";


        $actid = ResourceUserModel::getActionByUrl("hscv", "hscv", "multisend");
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            QLVBDHButton::AddButton("Chuyển nhiều", "", "MultiButton", "document.location.href=\"/hscv/hscv/multisend\";");
        }
        $this->view->page = $page;
        $this->view->limit = $limit;

        $thumuc = new ThuMucModel();
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/hscv/listthumuchscv/id_thumuc/" . $id_thumuc . "/code/" . $param['code']);
        //$this->view->thumuc = $thumuc->SelectAll(($page-1)*$limit,$limit,4);
        $this->view->thumuc = ThuMucModel::GetThumucHSCVTreeByPermission($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
    }

    function listchoxulyAction() {
        global $auth;
        $user = $auth->getIdentity();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        //$param['NAMECV'] = Convert::ConvertToUnicode($param['NAMECV']);
        $config = Zend_Registry::get('config');

        //tinh chỉnh param
        if ($param['NGAY_BD'] != "") {
            $ngaybd = $param['NGAY_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybd = implode("-", array_reverse(explode("/", $ngaybd)));
        }
        if ($param['NGAY_KT'] != "") {
            $ngaykt = $param['NGAY_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaykt = implode("-", array_reverse(explode("/", $ngaykt)));
        }
        $realyear = QLVBDHCommon::getYear();

        $id_thumuc = $param["id_thumuc"];
        $id_thumuc = $id_thumuc == "" ? 1 : $id_thumuc;
        //check quyen vao thu muc neu thu muc >1
        if ($id_thumuc > 1) {
            if (!ThuMucModel::CheckPermission($user->ID_U, $id_thumuc)) {
                $this->_redirect("/default/error/error?control=hscv&mod=hscv&id=ERR11001001");
            }
        }
        $ID_LOAIHSCV = $param['ID_LOAIHSCV'];
        $NAME = $param['NAMECV'];
        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        $TRANGTHAI = $param['TRANGTHAI'];
        $INFILE = $param['INFILE'];
        $INNAME = $param['INNAME'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }

        $parameter = array(
            "ID_THUMUC" => $id_thumuc,
            "ID_LOAIHSCV" => $ID_LOAIHSCV,
            "NGAY_BD" => $ngaybd,
            "NGAY_KT" => $ngaykt,
            "TRANGTHAI" => $TRANGTHAI,
            "ID_U" => $user->ID_U,
            "ID_G" => $user->ID_G,
            "ID_DEP" => $user->ID_DEP,
            "NAME" => $NAME,
            "SCOPE" => $scope,
            "CODE" => $param['code'],
            "OBJECT" => $param['OBJECT'],
            "INNAME" => $param['INNAME'],
            "INFILE" => $param['INFILE']
        );

        //Tạo đối tượng
        //$hscvcount = HosoluutheodoiModel::Count($parameter);
        if (($page - 1) * $limit == $hscvcount && $hscvcount > 0
            )$page--;
        //Lấy dữ liệu
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->data = HosocongviecModel::SelectAllChoxuly($parameter, ($page - 1) * $limit, $limit, "");
        $this->view->realyear = $realyear;
        $this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
        $this->view->NAME = $NAME;
        $this->view->NGAY_BD = $param['NGAY_BD'];
        $this->view->NGAY_KT = $param['NGAY_KT'];
        $this->view->NAME = $NAME;
        $this->view->TRANGTHAI = $TRANGTHAI;
        $this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
        $this->view->id_thumuc = $id_thumuc;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;
        $this->view->user = $user;
        $this->view->code = $param['code'];
        $this->view->idhscv = $param['idhscv'];
        $this->view->OBJECT = $param['OBJECT'];
        $this->view->title = "Hồ sơ công việc";
        //$this->view->subtitle = "Danh sách chờ xử lý";
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/hosoluutheodoi/index");
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
        $this->view->user = $user;

        //thangtba thêm: lấy ý kiến của lãnh đạo về đề xuất chờ xl
        require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
        $ykien = Hscv_DexuatchoxulyModel::getYKienLD();
        //var_dump($ykien);
        $this->view->dataYkien = $ykien;
        //end thangtba
    }

    function inputluuchoxulyAction() {
        $params = $this->_request->getParams();
        $this->_helper->Layout->disableLayout();
        $this->view->ID_HSCV = $params["id"];
        $this->view->data = HosocongviecModel::getLuuxulyInfoByHSCVId($this->view->ID_HSCV);
        $this->view->datafolder = ThumuccanhanModel::toComboThuMucPrivate();
        //luuChoxuly
    }

    function luuchoxulyAction() {
        global $db;
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        //var_dump($params); exit;
        $comment = $params["comment"];
        $id_hscv = $params['id_hscv'];
        $year = QLVBDHCommon::getYear();
        $authen = Zend_Registry::get('auth');
        $user = $authen->getIdentity();
        //var_dump($comment); exit;
        $re = HosocongviecModel::luuChoxuly($id_hscv, $comment, $user->ID_U);
        $db->insert(QLVBDHCommon::Table("hscv_fk_tmcn"), array("ID_OBJECT" => $id_hscv, "ID_TMCN" => $params["FOLDER"], "TYPE" => 0));
        echo "<script>window.parent.document.frm.submit();</script>";
        //echo $re;
        exit;
    }

    function phuchoiluuchoxulyAction() {
        global $auth;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        $r = HosocongviecModel::phuchoiluuChoxuly($param['id'], $user->ID_U);
        //var_dump($r);exit;
        echo "document.frm.submit();";
        exit;
    }

  public function listchobanhanhAction(){
		$this->view->start = (float) array_sum(explode(' ',microtime())); 
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
		$MASOHOSO = $param['MASOHOSO'];
		$TENTOCHUCCANHAN = $param['TENTOCHUCCANHAN'];
		
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
		
		//tim kiem noi dung van ban den
		$ID_SVB = (int)$param['ID_SVB'];
		$ID_LVB = (int)$param['ID_LVB'];
		$SODEN = $param['SODEN'];
		$SOKYHIEU = $param['SOKYHIEU'];
		
		if($param['NGAYDEN_BD']!=""){
			$NGAYDEN_BD = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
			$NGAYDEN_BD = implode("-",array_reverse(explode("/",$NGAYDEN_BD)));
		}
		if($param['NGAYDEN_KT']!=""){
			$NGAYDEN_KT = $param['NGAYDEN_KT']."/".QLVBDHCommon::getYear();
			$NGAYDEN_KT = implode("-",array_reverse(explode("/",$NGAYDEN_KT)));
		}

		if($param['NGAYBANHANH_BD']!=""){
			$NGAYBANHANH_BD = $param['NGAYBANHANH_BD']."/".QLVBDHCommon::getYear();
			$NGAYBANHANH_BD = implode("-",array_reverse(explode("/",$NGAYBANHANH_BD)));
		}
		if($param['NGAYBANHANH_KT']!=""){
			$NGAYBANHANH_KT = $param['NGAYBANHANH_KT']."/".QLVBDHCommon::getYear();
			$NGAYBANHANH_KT = implode("-",array_reverse(explode("/",$NGAYBANHANH_KT)));
		}	    
		
		if($param['NHAN_NGAY_BD']!=""){
			$NHAN_NGAY_BD = $param['NHAN_NGAY_BD']."/".QLVBDHCommon::getYear();
			$NHAN_NGAY_BD = implode("-",array_reverse(explode("/",$NHAN_NGAY_BD)));
		}	    
		if($param['NHAN_NGAY_KT']!=""){
			$NHAN_NGAY_KT = $param['NHAN_NGAY_KT']."/".QLVBDHCommon::getYear();
			$NHAN_NGAY_KT = implode("-",array_reverse(explode("/",$NHAN_NGAY_KT)));
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
			"MASOHOSO"=>$MASOHOSO,
			"TENTOCHUCCANHAN"=>$TENTOCHUCCANHAN,
			"SCOPE"=>$scope,
			"CODE"=>$param['code'],
			"OBJECT"=>$param['OBJECT'],
			"ID_SVB" => $param['ID_SVB'],
			"ID_LVB" => $param['ID_LVB'],
			"SODEN" => $param['SODEN'],
			"SOKYHIEU"=>$SOKYHIEU,
			"NGAYDEN_BD"=>$NGAYDEN_BD,
			"NGAYDEN_KT"=>$NGAYDEN_KT,
			'NGAYBANHANH_BD'=>$NGAYBANHANH_BD,
			'NGAYBANHANH_KT'=>$NGAYBANHANH_KT,
			'NHAN_NGAY_BD'=>$NHAN_NGAY_BD,
			'NHAN_NGAY_KT'=>$NHAN_NGAY_KT,
			"INNAME"=>$param['INNAME'],
			"INFILE"=>$param['INFILE']
		);

		
		
		//Tạo đối tượng
		$hscv = new hosocongviecModel();
		if(!$MASOHOSO){
			$hscvcount = $hscv->Count_chobanhanh($parameter);
		}else{
			$hscvcount = 100;
			$limit = 100;
		}
		if(($page-1)*$limit==$hscvcount && $hscvcount>0)$page--;
		//Lấy dữ liệu
		$this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
		$this->view->data = $hscv->SelectAll_chobanhanh($parameter,($page-1)*$limit,$limit,"");
		$this->view->realyear = $realyear;
		$this->view->ID_LOAIHSCV = $ID_LOAIHSCV;
		$this->view->NAME = $NAME;
		$this->view->NGAY_BD = $param['NGAY_BD'];
		$this->view->NGAY_KT = $param['NGAY_KT'];
		$this->view->NAME = $NAME;
		$this->view->MASOHOSO = $MASOHOSO;
		$this->view->TENTOCHUCCANHAN = $TENTOCHUCCANHAN;
		
		$this->view->TRANGTHAI = $TRANGTHAI;
		$this->view->datatrangthai = WFEngine::GetActivityFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
		$this->view->id_thumuc = $id_thumuc;
		$this->view->INNAME = $INNAME;
		$this->view->INFILE = $INFILE;
		$this->view->user = $user;
		$this->view->code = $param['code'];
		$this->view->idhscv = $param['idhscv'];
		$this->view->OBJECT = $param['OBJECT'];
		
		//tim kiem van ban den
		$this->view->ID_SVB = $param['ID_SVB'];
		$this->view->ID_LVB = $param['ID_LVB'];
		$this->view->SODEN = $param['SODEN'];
		$this->view->SOKYHIEU = $SOKYHIEU;
		$this->view->NGAYDEN_BD 	= $param['NGAYDEN_BD'];
		$this->view->NGAYDEN_KT 	= $param['NGAYDEN_KT'];
		$this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
		$this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
		$this->view->NHAN_NGAY_KT = $param['NHAN_NGAY_KT'];
		$this->view->NHAN_NGAY_BD = $param['NHAN_NGAY_BD'];
		
		//tim kiem ho so mot cua

		//Create button
		if($id_thumuc<2){
			$createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
			if(count($createarr)>0){
				QLVBDHButton::AddButton($createarr["NAME"],"","AddNewButton","CreateButtonClick(\"".$createarr["LINK"]."/type/$ID_LOAIHSCV/wf_id_t/".$createarr["ID_T"]."/year/".$realyear."\")");
			}
		}
		//page
		$this->view->title = "Hồ sơ công việc";
		if(strtoupper($param['code'])=='OLD'){
			$this->view->subtitle = "danh sách đã xử lý";
		}else if(strtoupper($param['code'])=='PRE'){
			$this->view->subtitle = "danh sách đã xử lý";
		}else if(strtoupper($param['code'])=='ZIP'){
			$this->view->subtitle = "danh sách lưu trữ";
		}else if(strtoupper($param['code'])=='PHOIHOP'){
			$this->view->subtitle = "danh sách phối hợp";
		}else{
			$this->view->subtitle = "<font color=red>danh sách chờ ban hành</font>";
		}

		$actid = ResourceUserModel::getActionByUrl("hscv","hscv","multisend");
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			QLVBDHButton::AddButton("Chuyển nhiều","","MultiButton","document.location.href=\"/hscv/hscv/multisend\";");
		}
		$this->view->page = $page;
		$this->view->limit = $limit;
		
		$thumuc = new ThuMucModel();
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/hscv/hscv/list/id_thumuc/".$id_thumuc."/code/".$param['code']);
		//$this->view->thumuc = $thumuc->SelectAll(($page-1)*$limit,$limit,4);
		$this->view->thumuc = ThuMucModel::GetTreeByPermission($user->ID_U);
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
	}
	
	public function nhapbutpheAction()
	{
		$this->_helper->layout->disableLayout();
		$this->view->idhscv = $this->getRequest()->getParam(idhscv);
		
		global $auth;
        $user = $auth->getIdentity();
		$this->view->fullname = $user->FULLNAME;
		$mdlHSCV = new hosocongviecModel();
		$this->view->data = $mdlHSCV->getNguoiButPhe();
	}
	
	public function savenguoibutpheAction()
	{
		global $db;
		global $auth;
		$year = QLVBDHCommon::getYear();
        $user = $auth->getIdentity();
		$this->_helper->layout->disableLayout();
		$idhscv = $this->getRequest()->getParam('idhscv');
		$this->view->idhscv = $idhscv;
		$noidung = $this->getRequest()->getParam('noidung');
		$nguoichuyen = $this->getRequest()->getParam('nguoiButPhe');
		$db->beginTransaction();
		try {
            $vbd = new vbdenModel(QLVBDHCommon::getYear());
            $vbden = $vbd->findByHscv($idhscv);
            $db->insert("HSCV_BUTPHE_" . $year, array(
                "NOIDUNG" => $noidung,
                "NGUOIKY" => $nguoichuyen,
                "NGUOICHUYEN" => $user->ID_U,
                "ID_VBD" => $vbden['ID_VBDEN'],
                "ID_HSCV" => $idhscv,
                "NGAYBP" => implode("-",array_reverse(explode("/",$this->getRequest()->getParam("NGAYBUTHE"))))
            ));
		} catch (Exception $ex) {
            $db->rollBack();
        }
		echo "<script>window.parent.document.frm.submit();</script>";
        exit;
	}
	
	public function deletebutpheAction()
	{
		global $db;
		global $auth;
		$year = QLVBDHCommon::getYear();
        $user = $auth->getIdentity();
		$this->_helper->layout->disableLayout();
		$idbp = $this->getRequest()->getParam('idbp');
		$idhscv = $this->getRequest()->getParam('idhscv');
		$this->view->idhscv = $idhscv;
		$mdl = new hosocongviecModel();
		$mdl->deleteNguoiButPhe($idbp);
		echo "loadDivFromUrl('groupcontent".$idhscv."','/hscv/hscv/viewbutphe/id/".$idhscv."/year/".$params["year"]."',1);";
		exit;
	}
        public function nhatkycongviecAction(){ 
            $this->_helper->layout->disableLayout();
            $param = $this->getRequest()->getParams();
            $this->view->id_hscv = (int)$param['idHSCV'];
            $ID_HSCV = (int)$param['idHSCV'];
            $hscv = hosocongviecModel::getDetailById($ID_HSCV);
            $macongviec = $hscv['MACONGVIEC'];
            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
            $madonvi = $configlienthong->service->lienthong->username;
            $password = $configlienthong->service->lienthong->password;
            $giaoviecservice = new GiaoViecService();
            $token = $giaoviecservice->login($madonvi,md5($password),"");
            $reCongViec = $giaoviecservice->SelectNhatKyCongViecByMacongviec(
                    $token
                    ,$macongviec
            );
            $this->view->congviec = json_decode($reCongViec);
            $this->view->count = count($this->view->congviec->data);
    }
        public function savenhatkyAction(){ 
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->disableLayout();
            global $auth;
            $user = $auth->getIdentity();
            $param = $this->getRequest()->getParams();
            $ID_HSCV = $param['id_hscv'];
            $hscv = hosocongviecModel::getDetailById($ID_HSCV);
            $macongviec = $hscv['MACONGVIEC'];
            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
            $madonvi = $configlienthong->service->lienthong->username;
            $password = $configlienthong->service->lienthong->password;
            $giaoviecservice = new GiaoViecService();
            $token = $giaoviecservice->login($madonvi,md5($password),"");
            $user_dep = DepartmentsModel::getNameById($user->ID_DEP);
            $giaoviecservice->createNhatKy(
                    $token
                    ,$macongviec
                    ,$user->ID_U
                    ,$user->FULLNAME
                    ,$param['tiendo']
                    ,$param['motatiendo']
                    ,$user_dep
            );
            
            // $giaoviecservice->UpdateNguoiXLCongViec(
            //         $token
            //         ,$macongviec
            //         ,$user->ID_U
            //         ,$user->FULLNAME
            //         ,$param['tiendo']
            // );
            $giaoviecservice->UpdateTienDoCV(
                $token
                ,$macongviec
                ,$param['tiendo']
            ); 
            
            if($param['lydotrehan']!='' || $param['lydotrehan']!=NULL)
                {
                    //  $giaoviecservice->UpdateLyDoTreHan(
                    //     $token
                    //     ,$macongviec
                    //     ,$user->ID_U
                    //     ,$user->FULLNAME
                    //     ,$param['lydotrehan']
                    // );
                    $giaoviecservice->UpdateLyDoTreHanSTT(
                        $token
                        ,$macongviec
                        ,$param['lydotrehan']
                    );
            }
            echo 1;exit;
        }
}
