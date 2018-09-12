<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';

class Hscv_DexuatchoxulyController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->Layout->DisableLayout();
    }

    function inputAction() {
        $params = $this->getRequest()->getParams();
        $idhscv = $params['id'];
        $this->view->id = $idhscv;
        $this->view->ID_HSCV = $params["id"];
    }

    function isdongyAction(){
        $params = $this->getRequest()->getParams();
        
        $id_hscv_choxl = $params['id_dxchoxl'];
        $this->view->id_dxchoxl = $id_hscv_choxl;

        //lay vet de xuat cho xu ly
        $vet_dxcxl = Hscv_DexuatchoxulyModel::getNoiDungDeXuat($id_hscv_choxl);
        $this->view->vet_dxcxl = $vet_dxcxl;
    }

    function dongyAction() {

        $params = $this->getRequest()->getParams();
        $id_dxchoxl = $params['id_dxchoxl'];
        $kdongy = (int)$params['kdongy'];
        $ykien = $params['ykien'];
        
        $r = Hscv_DexuatchoxulyModel::updateDongY($id_dxchoxl, $kdongy, $ykien);
        if ($r == 1)
            echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    function saveAction() {
        global $auth;
        global $db;
        $params = $this->getRequest()->getParams();
        $user = $auth->getIdentity();
        $id_hscv = $params['id'];
        $noidung = $params['noidung'];

        //lấy người phê duyệt
        if ($id_hscv > 0) {
            $nguoipheduyet = Hscv_DexuatchoxulyModel::getNguoiDuyetYeuCau($id_hscv);
        }
        //lấy người gửi
        $nguoigui = $user->ID_U;

        $data = array(
            'ID_HSCV_CHOXL' => $id_hscv,
            'NOIDUNG' => $noidung,
            'NGAY' => date('Y-m-d H:i:s'),
            'NGUOIGUI' => $nguoigui,
            'NGUOIPHEDUYET' => $nguoipheduyet
        );
        $r = Hscv_DexuatchoxulyModel::saveDeXuatChoXuLy($data);
        $db->update(QLVBDHCommon::Table('hscv_hosocongviec'),array('IS_DXCHOXL' => 1),'ID_HSCV='.$id_hscv);
        if ($r == 1)
            echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }

    function listAction() {

        $this->_helper->Layout->EnableLayout();
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
        $hscvcount = Hscv_DexuatchoxulyModel::Count($parameter);
        if (($page - 1) * $limit == $hscvcount && $hscvcount > 0
            )$page--;
        //Lấy dữ liệu
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->data = Hscv_DexuatchoxulyModel::SelectAll($parameter, ($page - 1) * $limit, $limit, "");
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
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/hscv/dexuatchoxuly/list");
        $this->view->loaihscv = WFEngine::GetLoaiCongViecFromUser($user->ID_U);
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
        $this->view->user = $user;

        $this->view->title = 'Danh sách đề xuất chờ xử lý';
        //$this->view->subtitle = 'Danh sách đề xuất chờ xử lý';
    }

    function updatedadocAction() {

        $params = $params = $this->getRequest()->getParams();
        $id_hscv = $params['id_hscv'];
        global $db;
        $db->update('hscv_dexuat_choxuly_'.QLVBDHCommon::getYear(), array('DADOC' => 1), "ID_HSCV_CHOXL='".$id_hscv."'");
        exit;
    }

}

