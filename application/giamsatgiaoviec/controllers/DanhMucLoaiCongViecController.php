<?php
require_once 'Zend/Controller/Action.php';
require_once 'giamsatgiaoviec/models/LoaiCongViecGiaoViecModel.php';

class Giamsatgiaoviec_DanhMucLoaiCongViecController extends Zend_Controller_Action {
    
    /**
     * The default action - show the home page
     */
    private $model; // Doi tuong 

    /**
     * Khoi tao du lieu controller
     */

    public function init() {
        $this->model = new LoaiCongViecGiaoViecModel();        
        $this->view->title = "Danh mục Loại công việc giao";
    }

    function indexAction() {

        $config = Zend_Registry::get('config');
        $parameter = $this->getRequest()->getParams();
        
        $limit = $parameter["limit"];
        if ((int)$limit == 0){
            $limit = $config->limit;
        }
        $page = $parameter["page"];
        if ($page == 0 || $page == ""){
            $page = 1;
        }
        $model= new LoaiCongViecGiaoViecModel();
        $countdata=count($model->SelectAll());
        $this->view->data = $model->SelectAll(($page - 1) * $limit, $limit);
        $this->view->limit = $limit;
        $this->view->page = $page;
        $this->view->showPage = QLVBDHCommon::Paginator($countdata, 5, $limit, "frm", $page);

        //Enable button
        //QLVBDHButton::EnableDongBo("/giamsatgiaoviec/danhmucloaicongviec/dongbo");  
        QLVBDHButton::EnableAddNew("/qtht/danhmucloaicongviecgiaoviec/input");
        QLVBDHButton::EnableDelete("/qtht/danhmucloaicongviecgiaoviec/delete");
        QLVBDHButton::EnableBack("");
    }

    function dongboAction() {
        $model = new LoaiCongViecGiaoViecModel();
        $session = $model->LoginService();
        $model->DongBoDanhmuc($session);
        $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec/index');
    }
    
    
    /**
     * Ham controller cho chuc nang them moi loai van ban
     */
    public function saveAction() {
        if ($this->_request->isPost()) {
            //Doc du lieu tu form nhap lieu
            $active = $this->_request->getParam("active");
            if (!$active) {
                $active = 0;
            }
            $name = $this->_request->getParam("name");
            $id = $this->_request->getParam("InputIDLCV");
            $code = $this->_request->getParam('code');

            if ($id > 0) {

                //truong hop cap nhat
                //Thuc hien cap nhat du lieu
                $where = 'ID_LCV=' . $id;
                try {
                    $this->model->update(array(
                        'NAME' => $name,
                        'CODE' => $code,
                        'ACTIVE' => $active
                            ), $where);
                } catch (Exception $e) {
                    echo $e->__toString();
                    exit;
                    $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
                }
            } else {

                //truong hop them moi
                //thuc hien them moi du lieu
                $arr_newdata = array(
                    "NAME" => $name,
                    'CODE' => $code,
                    "ACTIVE" => $active
                );
                try {
                    $this->model->insert($arr_newdata);
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                    $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
                }
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
    }

    /**
     * Ham xu ly cho action xoa loai van ban
     */
    public function deleteAction() {
        if ($this->_request->isPost()) {
            //Lay id cac van ban can xoa
            $idarray = $this->_request->getParam('DEL');

            //thuc hien xoa cac loai van ban duoc chon
            $where = 'ID_LCV in (' . implode(',', $idarray) . ')';
            try {
                if (!$this->model->delete($where)) {
                    $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
                }
            } catch (Exception $ex) {
                //Loi trong qua trinh xoa
                $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
    }

    /**
     * Ham xu ly cho action cap nhat linh vuc van ban
     */
    public function inputAction() {

        //Tao phan button
        QLVBDHButton::EnableSave("#");
        QLVBDHButton::AddButton("Trở lại", "", "BackButton", "BackButtonClick();");
        //Giu cac tham so cua trang danh sach
        $this->view->page = $this->_request->getParam('page');
        $this->view->limit = $this->_request->getParam('limit');
        $this->view->filter_object = $this->_request->getParam('filter_object');
        $this->view->search = $this->_request->getParam("search");

        if ($this->_request->isPost()) {
            //Lay id cua linh vuc van ban can cap nhat
            $idCapNhat = $this->_request->getParam('idLCV');
            if ($idCapNhat > 0) {
                //truong hop cap nhat du lieu
                //Kiem tra loai van ban can cap nhat co tron csdl
                $rowcn = $this->model->find($idCapNhat);
                if ($rowcn->count() == 0) {
                    //loi khong tim thay id cua loai van ban
                    $this->_redirect('/giamsatgiaoviec/danhmucloaicongviec');
                }

                //Lay du lieu cua linh vuc van ban can cap nhat
                $this->view->namebefore = $rowcn->current()->NAME;
                $this->view->codebefore = $rowcn->current()->CODE;
                $this->view->activeselect = $rowcn->current()->ACTIVE;
                $this->view->id = $idCapNhat;

                $this->view->title = "Cập nhật Loại công việc giao";
            } else {
                //truong hop them moi du lieu
                $this->view->title = "Thêm mới Loại công việc giao";
            }
        }
        //Hien thi trang nhap lieu
        $this->renderScript("danhmucloaicongviec/InputData.phtml");
    }

    function checkexistAction() {

        $checkData = $this->getRequest()->getParams();

        $maso = $checkData['code'];
        $id = $checkData['id'];

        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = sprintf("select * from CVGV_LOAICONGVIECGIAOVIEC where CODE = '%s'", $maso);
        $r = $db->query($sql)->fetchAll();

        $is_dup_code = false;

        foreach ($r as $item) {
            if ($id > 0) {//cập nhật
                if ($id != $item['ID_LCV']) {
                    if ($maso == $item['CODE']) {
                        $is_dup_code = true;
                    }
                }
            } else {//thêm mới
                if ($maso == $item['CODE']) {
                    $is_dup_code = true;
                }
            }
        }
        if ($is_dup_code){
            echo 1; //trung code
        }else{
            echo 0;
        }
        exit;
    }
}
        