<?php
require_once 'Zend/Controller/Action.php';
require_once 'giaoviec/models/DanhSachGuiMailModel.php';

class Giaoviec_DanhSachGuiMailController extends Zend_Controller_Action {
    
    /**
     * The default action - show the home page
     */
    private $model; // Doi tuong 

    /**
     * Khoi tao du lieu controller
     */

    public function init() {
        $this->model = new DanhSachGuiMailModel();        
        $this->view->title = "Danh sách email lãnh đạo các đơn vị gửi báo cáo";
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
        $model= new DanhSachGuiMailModel();
        $countdata=count($model->SelectAll());
        $this->view->data = $model->SelectAll(($page - 1) * $limit, $limit);
        $this->view->limit = $limit;
        $this->view->page = $page;
        $this->view->showPage = QLVBDHCommon::Paginator($countdata, 5, $limit, "frm", $page);

        //Enable button
        QLVBDHButton::EnableAddNew("/qtht/danhsachguimail/input");
        QLVBDHButton::EnableDelete("/qtht/danhsachguimail/delete");
        QLVBDHButton::EnableBack("");
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
            $tennguoinhan = $this->_request->getParam("tennguoinhan");
            $id = $this->_request->getParam("InputID");
            $masotendonvi = explode('|', $this->_request->getParam('madonvi'));
            $masodonvi = $masotendonvi[0];
            $tendonvi = $masotendonvi[1];
            $email = $this->_request->getParam('email');
            $sodienthoai = $this->_request->getParam('sodienthoai');
            $email2 = $this->_request->getParam('email2');
            //
            if ($id > 0) {

                //truong hop cap nhat
                //Thuc hien cap nhat du lieu
                $where = 'ID=' . $id;
                try {
                    $this->model->update(array(
                        'TENDONVI' => $tendonvi,
                        'TENNGUOINHAN' => $tennguoinhan,
                        'MADONVI' => $masodonvi,
                        'EMAIL' => $email,
                        'SODIENTHOAI' => $sodienthoai,
                        'EMAIL2' => $email2,
                        'ACTIVE' => $active
                            ), $where);
                } catch (Exception $e) {
                    echo $e->__toString();
                    exit;
                    $this->_redirect('/giaoviec/danhsachguimail');
                }
            } else {

                //truong hop them moi
                //thuc hien them moi du lieu
                $arr_newdata = array(
                    'TENDONVI' => $tendonvi,
                    'TENNGUOINHAN' => $tennguoinhan,
                        'MADONVI' => $masodonvi,
                        'EMAIL' => $email,
                        'SODIENTHOAI' => $sodienthoai,
                        'EMAIL2' => $email2,
                        'ACTIVE' => $active
                );
                try {
                    $this->model->insert($arr_newdata);
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                    $this->_redirect('/giaoviec/danhsachguimail');
                }
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhsachguimail');
    }

    /**
     * Ham xu ly cho action xoa loai van ban
     */
    public function deleteAction() {
        if ($this->_request->isPost()) {
            //Lay id cac van ban can xoa
            $idarray = $this->_request->getParam('DEL');

            //thuc hien xoa cac loai van ban duoc chon
            $where = 'ID in (' . implode(',', $idarray) . ')';
            try {
                if (!$this->model->delete($where)) {
                    $this->_redirect('/giaoviec/danhsachguimail');
                }
            } catch (Exception $ex) {
                //Loi trong qua trinh xoa
                $this->_redirect('/giaoviec/danhsachguimail');
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhsachguimail');
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
            $idCapNhat = $this->_request->getParam('id');
            if ($idCapNhat > 0) {
                //truong hop cap nhat du lieu
                //Kiem tra loai van ban can cap nhat co tron csdl
                $rowcn = $this->model->find($idCapNhat);
                if ($rowcn->count() == 0) {
                    //loi khong tim thay id cua loai van ban
                    $this->_redirect('/giaoviec/danhsachguimail');
                }

                //Lay du lieu cua linh vuc van ban can cap nhat
                $this->view->tendonvibefore = $rowcn->current()->TENDONVI;
                $this->view->tennguoinhanbefore = $rowcn->current()->TENNGUOINHAN;
                $this->view->madonvibefore = $rowcn->current()->MADONVI;
                $this->view->emailbefore = $rowcn->current()->EMAIL;
                $this->view->sodienthoaibefore = $rowcn->current()->SODIENTHOAI;
                $this->view->email2before = $rowcn->current()->EMAIL2;
                $this->view->activeselect = $rowcn->current()->ACTIVE;
                $this->view->id = $idCapNhat;

                $this->view->title = "Cập nhật Danh sách email";
            } else {
                //truong hop them moi du lieu
                $this->view->title = "Thêm mới Danh sách email";
            }
        }
        //Hien thi trang nhap lieu
        $this->renderScript("danhsachguimail/InputData.phtml");
    }

    function checkexistAction() {

        $checkData = $this->getRequest()->getParams();

        $madonvi = $checkData['madonvi'];
        $id = $checkData['id'];

        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = sprintf("select * from gscv_danhsachguimail where MADONVI = '%s'", $madonvi);
        $r = $db->query($sql)->fetchAll();

        $is_dup_code = false;

        foreach ($r as $item) {
            if ($id > 0) {//cập nhật
                if ($id != $item['ID']) {
                    if ($maso == $item['MADONVI']) {
                        $is_dup_code = true;
                    }
                }
            } else {//thêm mới
                if ($maso == $item['MADONVI']) {
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
        