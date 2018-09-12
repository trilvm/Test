<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TinhhinhxulyController
 *
 * @author phuongpt
 */
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';
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
require_once 'hscv/models/tinhhinhxulyModel.php';

class Hscv_TinhhinhxulyController extends Zend_Controller_Action {
    
    public function hscvchoxulyAction()
    {
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
        $OBJECT_CXL = $param['OBJECT_CXL'];
        $this->view->object_cxl = $OBJECT_CXL;
        $id_hscv_trehan = $param['hscv_trehan']; 
        $this->view->hscv_trehan= $id_hscv_trehan;
        
        $this->view->data = tinhhinhxulyModel::SelectAllChoxuly($OBJECT_CXL, $id_hscv_trehan, ($page - 1) * $limit, $limit, "");
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
        
//        $this->view->title = "Hồ sơ công việc <select onchange='document.location.href=this.value'>
//			<option style='color:red;font-weight:bold' value='/hscv/hscv/list'>CHƯA XỬ LÝ</option>
//			<option style='color:red;font-weight:bold' value='/hscv/hscv/list/code/old'>ĐÃ XỬ LÝ</option>
//			<option selected style='color:red;font-weight:bold' value='/hscv/hscv/listchoxuly'>CHỜ XỬ LÝ</option>
//			</select>";
        //$this->view->subtitle = "Danh sách chờ xử lý";
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/Tinhhinhxuly/hscvchoxuly");
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
        $this->view->user = $user;

        //thangtba thêm: lấy ý kiến của lãnh đạo về đề xuất chờ xl
        require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
        $ykien = Hscv_DexuatchoxulyModel::getYKienLD();
        $this->view->dataYkien = $ykien;
        
        QLVBDHButton::AddButton("Trở về","","BackButton","BackButtonClick();");
        $this->view->title = "Tình hình xử lý công việc";
    }
    
    public function hscvxulyAction()
    {
        $this->view->start = (float) array_sum(explode(' ', microtime()));
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
        $NAME = $param['NAMECV'];
        $MASOHOSO = $param['MASOHOSO'];
        $TENTOCHUCCANHAN = $param['TENTOCHUCCANHAN'];

        $page = $param['page'];
        $limit = $param['limit1'];
//        $this->view->limit = $limit;
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
//        $this->view->limit = $param['limit1'];
        $this->view->limit = $limit;
        $object_xl = $param['OBJECT_XL'];
        $this->view->object_xl = $object_xl;
        $code_xl = $param['codexl'];
        $this->view->code_xl = $code_xl;
        // flag hscv_trehan = 1(đã xử lý trễ hạn)
        $id_hscv_trehan = $param['hscv_trehan'];
        $this->view->hscv_trehan = $id_hscv_trehan;
        $data = new tinhhinhxulyModel();
        
        //$this->view->data = $data->SelectAllXuly($code_xl, $object_xl, $id_hscv_trehan, ($page - 1) * $limit, $limit);
        //phuongpt
		if($user->ISLEADER == 1 && $user->DEPLEADER == 1)
		{
			if($id_hscv_trehan == 1) {
				$this->view->data = $data->SelectAllDangXulyBT(0, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyBT(0, 0, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 2) {
				$this->view->data = $data->SelectAllDangXulyTH(0, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyTH(0, 0, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 3) {
				$this->view->data = $data->SelectAllDaXulyBT(0, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyBT(0, 0, 0, 0, $object_xl, 0,0);
			} else if($id_hscv_trehan == 4) {
				$this->view->data = $data->SelectAllDaXulyTH(0, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyTH(0, 0, 0, 0, $object_xl,0,0);
			}
		}
		//trường hợp là lãnh đạo văn phòng
		if($user->ISLEADER == 1 && $user->DEPLEADER == 0)
		{
			if($id_hscv_trehan == 1) {
				$this->view->data = $data->SelectAllDangXulyBT(0, $user->ID_DEP, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyBT(0, $user->ID_DEP, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 2) {
				$this->view->data = $data->SelectAllDangXulyTH(0, $user->ID_DEP, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyTH(0, $user->ID_DEP, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 3) {
				$this->view->data = $data->SelectAllDaXulyBT(0, $user->ID_DEP, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyBT(0, $user->ID_DEP, 0, 0, $object_xl, 0,0);
			} else if($id_hscv_trehan == 4) {
				$this->view->data = $data->SelectAllDaXulyTH(0, $user->ID_DEP, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyTH(0, $user->ID_DEP, 0, 0, $object_xl,0,0);
			}

		}
		//trường hợp là người dùng
		if($user->ISLEADER == 0)
		{
			if($id_hscv_trehan == 1) {
				$this->view->data = $data->SelectAllDangXulyBT($user->ID_U, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyBT($user->ID_U, 0, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 2) {
				$this->view->data = $data->SelectAllDangXulyTH($user->ID_U, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDangXulyTH($user->ID_U, 0, 0, 0, $object_xl, 0, 0);
			} else if($id_hscv_trehan == 3) {
				$this->view->data = $data->SelectAllDaXulyBT($user->ID_U, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyBT($user->ID_U, 0, 0, 0, $object_xl, 0,0);
			} else if($id_hscv_trehan == 4) {
				$this->view->data = $data->SelectAllDaXulyTH($user->ID_U, 0, 0, 0, $object_xl, ($page - 1) * $limit, $limit);
				$hscvcount = $data->Count_SelectAllDaXulyTH($user->ID_U, 0, 0, 0, $object_xl,0,0);
			}
		}
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
        $this->view->str_id= $param['hscv_trehan'];
        //tim kiem ho so mot cua
        //Create button
        if ($id_thumuc < 2) {
            $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV, $user->ID_U);
            if (count($createarr) > 0) {
                QLVBDHButton::AddButton($createarr["NAME"], "", "AddNewButton", "CreateButtonClick(\"" . $createarr["LINK"] . "/type/$ID_LOAIHSCV/wf_id_t/" . $createarr["ID_T"] . "/year/" . $realyear . "\")");
            }
        }
        //page
        

        $actid = ResourceUserModel::getActionByUrl("hscv", "hscv", "multisend");
//        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
//            QLVBDHButton::AddButton("Chuyển nhiều", "", "MultiButton", "document.location.href=\"/hscv/hscv/multisend\";");
//        }
        $this->view->page = $page;
//        $this->view->limit = $limit;

        $thumuc = new ThuMucModel();
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, $_SERVER["REQUEST_URI"]);
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
         
        QLVBDHButton::AddButton("Trở về","","BackButton","BackButtonClick();");
		if($object_xl == "VBD")
		{
			$this->view->title = "Tình hình xử lý văn bản đến";
		} else if($object_xl == "VBSOANTHAO") {
			$this->view->title = "Tình hình xử lý công việc";
		}
    }
}

?>
