<?php

/**
 * DanhMucCongViecNoiBoController
 * 
 * @author Vinhlt
 * @version 1.0 
 */
require_once 'qtht/models/CongViecNoiBoModel.php';
require_once 'Zend/Controller/Action.php';
require_once 'Common/ValidateInputData.php';
require_once 'config/qtht.php';
require_once 'nusoap/nusoap.php';

class Qtht_DanhMucCongViecNoiBoController extends Zend_Controller_Action {

    /**
     * The default action - show the home page
     */
    private $model; // Doi tuong bang loai van ban
    var $iscode = true;
    var $codehere = true;

    /**
     * Khoi tao du lieu controller
     */
    public function init() {
        $this->model = new CongViecNoiBoModel();

        $this->view->title = "Quản lý Danh mục Công việc nội bộ";
    }

    /**
     * Ham xu ly cho action xem (index)
     */
    public function indexAction() {
        QLVBDHButton::EnableAddNew("/qtht/DanhMucCongViecNoibo/input");
        QLVBDHButton::EnableDelete("/qtht/DanhMucCongViecNoibo/delete");
        //Doc du lieu de hien thi len view
        $config = Zend_Registry::get('config');
        $page = $this->_request->getParam('page');
        $limit = $this->_request->getParam('limit');
        if ($limit == 0 || $limit == "")
            $limit = $config->limit;
        if ($page == 0 || $page == "")
            $page = 1;
        $filter_object = $this->_request->getParam('filter_object');
        $this->view->filter_object = $filter_object;
        $search = $this->_request->getParam("search");
        $this->view->search = $search;
        $this->view->data = $this->model->findByMixed($page, $limit, $search, $filter_object);
        $n_rows = $this->model->count($search, $filter_object);
        $this->view->showPage = QLVBDHCommon::Paginator($n_rows, 5, $limit, "frm", $page);
        $this->view->limit = $limit;
        $this->view->page = $page;

        /*
          $synchronousStatus = $this->_request->getParam("synchronousStatus");
          if(isset($synchronousStatus)){
          $this->view->synchronousStatus = $synchronousStatus;
          $this->view->update = $this->_request->getParam("update");
          $this->view->insert = $this->_request->getParam("insert");
          }else{
          $this->view->synchronousStatus = 3;
          }
         */
    }

    /**
     * Ham controller cho chuc nang them moi loai van ban
     */
    public function saveAction() {
        if ($this->_request->isPost()) {
            //Doc du lieu tu form nhap lieu                        
            $active = $this->_request->getParam("active");
            if (!$active)
                $active = 0;
            $name = $this->_request->getParam("name");
            $id = $this->_request->getParam("InputIDDMNB");
            $hanxuly = $this->_request->getParam('HANXULY');
            $typehanxuly = $this->_request->getParam('TYPEHANXULY');

            //Kiem tra hop le cua du lieu nhap
            $this->checkInputData($name, $active);
            if ($id > 0) {
                //truong hop cap nhat
                //Thuc hien cap nhat du lieu
                $where = 'ID_DMNB=' . $id;
                try {
                    $this->model->update(array('NAME' => $name, 'ACTIVE' => $active, 'HANXULY' => $hanxuly,'TYPEHANXULY' => $typehanxuly
                            ), $where);
                } catch (Exception $e) {
                    //$this->_redirect('/default/error/error?control=DanhMuccongviecnoibo&mod=qtht&id=ERR11005006');
                }
            } else {
                //truong hop them moi
                //thuc hien them moi du lieu

                $arr_newdata = array("NAME" => $name, "ACTIVE" => $active, 'HANXULY' => (int) $hanxuly);
                //var_dump($arr_newdata);exit();
                try {
                    $this->model->insert($arr_newdata);
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                    //$this->_redirect('/default/error/error?control=DanhMuccongviecnoibo&mod=qtht&id=ERR11005007');
                }
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/qtht/DanhMucCongViecNoiBo');
    }

    /**
     * Ham xu ly cho action xoa loai van ban
     */
    public function deleteAction() {
        if ($this->_request->isPost()) {
            //Lay id cac van ban can xoa
            $idarray = $this->_request->getParam('DEL');

            //thuc hien xoa cac loai van ban duoc chon
            $where = 'ID_DMNB in (' . implode(',', $idarray) . ')';
            try {
                if (!$this->model->delete($where)) {
                    $this->_redirect('/default/error/error?control=DanhMuccongviecnoibo&mod=qtht&id=ERR11005008');
                }
            } catch (Exception $ex) {
                //Loi trong qua trinh xoa
                $this->_redirect('/default/error/error?control=DanhMuccongviecnoibo&mod=qtht&id=ERR11005008');
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/qtht/DanhMucCongViecNoiBo');
    }

    /**
     * Ham xu ly cho action cap nhat linh vuc van ban
     */
    public function inputAction() {


        //Tao phan button
        QLVBDHButton::EnableSave("/qtht/DanhMucCongViecNoibo/save");
        QLVBDHButton::AddButton("Trở lại", "", "BackButton", "BackButtonClick();");
        //Giu cac tham so cua trang danh sach
        $this->view->page = $this->_request->getParam('page');
        $this->view->limit = $this->_request->getParam('limit');
        $this->view->filter_object = $this->_request->getParam('filter_object');
        $this->view->search = $this->_request->getParam("search");

        if ($this->_request->isPost()) {
            //Lay id cua linh vuc van ban can cap nhat
            $idCapNhat = $this->_request->getParam('idDMNB');
            if ($idCapNhat > 0) {
                //truong hop cap nhat du lieu
                //Kiem tra loai van ban can cap nhat co tron csdl
                $rowcn = $this->model->find($idCapNhat);
                if ($rowcn->count() == 0) {
                    //loi khong tim thay id cua loai van ban
                    $this->_redirect('/default/error/error?control=DanhMuccongviecnoibo&mod=qtht&id=ERR11005002');
                }

                //Lay du lieu cua linh vuc van ban can cap nhat
                $this->view->namebefore = $rowcn->current()->NAME;
                $this->view->hanxulybefore = $rowcn->current()->HANXULY;
                $this->view->typehanxulybefore = $rowcn->current()->TYPEHANXULY;
                $this->view->activeselect = $rowcn->current()->ACTIVE;
                $this->view->id = $idCapNhat;

                $this->view->title = "cập nhật Danh mục nội bộ";
            } else {
                //truong hop them moi du lieu
                $this->view->title = "Thêm mới Danh mục nội bộ";
            }
        }
        //Hien thi trang nhap lieu
        $this->renderScript("DanhMucCongViecNoiBo/InputData.phtml");
    }

    /**
     * Ham xu ly cho action thong bao loi
     */
    public function errorAction() {
        
    }

    private function checkInputData($name, $active) {

        $strurl = '/default/error/error?control=danhmuccongviecnoibo&mod=qtht&id=';
        $strerr = "";
        $strerr .= ValidateInputData::validateInput('text128_re', $name, 'ERR11005001') . ",";
        $strerr .= ValidateInputData::validateInput('boolean', $active, "ERR11005005") . ",";
        if (strlen($strerr) != 2) {
            $this->_redirect($strurl . $strerr);
        }
        return true;
    }

    public function dongbocongviecnoiboAction() {

        $config = new Zend_Config_Ini('../application/config.ini', 'general', true);
        $arrLogin = array(
            "madonvi" => $config->service->lienthong->username,
            "password" => $config->service->lienthong->password
        );

        $client = new SoapClient($config->service->lienthong->uri);
        $session = $client->__call('Login', $arrLogin);

        $param = array(
            'session' => $session,
            'service_code' => 'DONGBODANHMUC',
            'service_name' => 'LVB',
            'parameter' => 'LVB'
        );

        $result = $client->__call('Execute', $param);

        $rs = ServicesCommon::DeseriallizeToArray(base64_decode($result));

        $datadb = $this->model->GetAllCongViecNoiBos();
        $count = count($rs);
        $status = 0;
        try {
            $updateCount = 0;
            $insertCount = 0;
            for ($i = 0; $i < $count; $i++) {
                if (in_array($rs[$i]['CODE'], $datadb)) {
                    if ($rs[$i]['NAME'] != '') {
                        $where = 'CODE="' . $rs[$i]['CODE'] . '"';
                        $idupdate = $this->model->update(array('NAME' => $rs[$i]['NAME'], 'KYHIEU' => $rs[$i]['KYHIEU']), $where);
                        if (count($idupdate) > 0) {
                            $updateCount++;
                        }
                    }
                } else {
                    if ($rs[$i]['NAME'] != '') {
                        $idinsert = $this->model->insert(
                                array(
                                    "NAME" => $rs[$i]['NAME'],
                                    "ACTIVE" => $rs[$i]['ACTIVE']
                                )
                        );
                        if (count($idinsert) > 0) {
                            $insertCount++;
                        }
                    }
                }
            }
            $status = 1;
        } catch (Exception $ex) {
            $status = 2;
        }
        $this->_redirect('/qtht/DanhMucCongViecNoiBo/index/synchronousStatus/' . $status . '/update/' . $updateCount . '/insert/' . $insertCount);
    }

}
