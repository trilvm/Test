<?php
require_once 'Zend/Controller/Action.php';
require_once 'giaoviec/models/LoaiCongViecGiaoViecModel.php';

class Giaoviec_DanhMucLoaiCongViecController extends Zend_Controller_Action {
   
    public function indexAction() {

        $this->view->title = "Giao việc";
        $this->view->subtitle = "Danh mục loại công việc";
        $giaoviecservice = new LoaiCongViecGiaoViecService();
		$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
		$madonvi = $configlienthong->service->lienthong->username;
		$password = $configlienthong->service->lienthong->password;
		$token = $giaoviecservice->login($madonvi,md5($password),"");
        
        $this->view->data = json_decode($giaoviecservice->listDanhMucLoaiCongViec($token,1))->data;

        //Enable button
        QLVBDHButton::EnableAddNew("/qtht/giaoviec/input");
        //QLVBDHButton::EnableDelete("/qtht/giaoviec/delete");
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
            $name = $this->_request->getParam("name");
            $id = $this->_request->getParam("InputIDLCV");
            $code = $this->_request->getParam('code');

            if ($id > 0) {

                //truong hop cap nhat
                //Thuc hien cap nhat du lieu
                $where = 'ID_LCV=' . $id;
                try {
                    $loaicvgiaoviecservice = new LoaiCongViecGiaoViecService();
                    $configlienthong = new Zend_Config_Ini('../application/config.ini', 'lienthong');
                    $madonvi = $configlienthong->lienthong->user_id;
                    $password = $configlienthong->lienthong->password;
                    $token = $loaicvgiaoviecservice->login($madonvi,md5($password),"");
                    $arr_updatedata = array(
                    "NAME" => $name,
                    'CODE' => $code,
                    "ACTIVE" => $active,
                    "ID_LCV" => $id
                    );
                    $result = json_decode($loaicvgiaoviecservice->update(
			$token
			,$arr_updatedata
                    ));

                } catch (Exception $e) {
                    echo $e->__toString();
                    exit;
                    $this->_redirect('/giaoviec/danhmucloaicongviec');
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
                    //$this->model->insert($arr_newdata);
                    $loaicvgiaoviecservice = new LoaiCongViecGiaoViecService();

					$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
					$madonvi = $configlienthong->service->lienthong->username;
					$password = $configlienthong->service->lienthong->password;
					$token = $loaicvgiaoviecservice->login($madonvi,md5($password),"");
					
                    $result = json_decode($loaicvgiaoviecservice->insert(
			$token
			,$arr_newdata
                    ));
                   
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                    $this->_redirect('/giaoviec/danhmucloaicongviec');
                }
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhmucloaicongviec');
    }

    /**
     * Ham xu ly cho action xoa loai van ban
     */
    public function deleteAction() {
        if ($this->_request->isPost()) {
            //Lay id cac van ban can xoa
            $idarray = $this->_request->getParam('DEL');

            //thuc hien xoa cac loai van ban duoc chon
            $where = '(' . implode(',', $idarray) . ')';
            try {
                $loaicvgiaoviecservice = new LoaiCongViecGiaoViecService();
                $giaoviecservice = new LoaiCongViecGiaoViecService();
                
				
				$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $result = json_decode($loaicvgiaoviecservice->delete(
                    $token
                    ,$where
                ));

                $this->_redirect('/giaoviec/danhmucloaicongviec');
            } catch (Exception $ex) {
                //Loi trong qua trinh xoa
                $this->_redirect('/giaoviec/danhmucloaicongviec');
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhmucloaicongviec');
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
                $giaoviecservice = new LoaiCongViecGiaoViecService();
                
				
				$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
				
               $rowcn = json_decode($giaoviecservice->findDanhMucLoaiCongViec($token,$idCapNhat))->data;
                
                //Lay du lieu cua linh vuc van ban can cap nhat
                $this->view->namebefore = $rowcn[0]->NAME;
                $this->view->codebefore = $rowcn[0]->CODE;
                $this->view->activeselect = $rowcn[0]->ACTIVE;
                $this->view->id = $idCapNhat;

                $this->view->title = "Cập nhật loại công việc giao";
            } else {
                //truong hop them moi du lieu
                $this->view->title = "Thêm mới loại công việc giao";
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
        