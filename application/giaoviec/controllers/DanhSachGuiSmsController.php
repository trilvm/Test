<?php
require_once 'Zend/Controller/Action.php';
require_once 'giaoviec/models/DanhSachGuiSmsModel.php';
require_once "giaoviec/models/giaoviecservice.php";

class Giaoviec_DanhSachGuiSmsController extends Zend_Controller_Action {
    
    /**
     * The default action - show the home page
     */
    private $model; // Doi tuong 

    /**
     * Khoi tao du lieu controller
     */

    public function init() {
        $this->model = new DanhSachGuiSmsModel();        
        $this->view->title = "Danh sách nội dung gửi SMS đến lãnh đạo các đơn vị";
    }

    function indexAction() {

        $config = Zend_Registry::get('config');
        $parameter = $this->getRequest()->getParams();
        
        $ID_DONVI = $parameter['ID_DONVI'];
        $this->view->ID_DONVI = $ID_DONVI;
        
        $limit = $parameter["limit"];
        if ((int)$limit == 0){
            $limit = $config->limit;
        }
        $page = $parameter["page"];
        if ($page == 0 || $page == ""){
            $page = 1;
        }
        
        $model= new DanhSachGuiSmsModel();
        
         //check lĩnh vực văn bản
        if ($parameter['ID_DONVI'] > 0 && $parameter['ID_DONVI'] != "") {
            $donvi = $parameter['ID_DONVI'];
        }else{
            $donvi = 0;
        }
        $countdata=count($model->SelectAll());
        
        $this->view->data = $model->SelectAll(($page - 1) * $limit, $limit,$donvi);
        $this->view->limit = $limit;
        $this->view->page = $page;
        $this->view->showPage = QLVBDHCommon::Paginator($countdata, 5, $limit, "frm", $page);

        //Enable button
       // QLVBDHButton::EnableAddNew("/qtht/danhsachguisms/input");
        QLVBDHButton::EnableDelete("/qtht/danhsachguisms/delete");
        QLVBDHButton::EnableBack("");
    }

    function vn_str_filter ($str){
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
			'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        
       foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
       }
		return $str;
    }
	

    function addsmsAction(){
        if ($this->_request->isPost()) {
            //Lay id cac van ban can xoa
            $this->_helper->layout->disableLayout();
            $params = $this->_request->getParams();
            $idarray = $this->_request->getParam('DEL');
            $noidung =  implode(' ; ', $idarray) ;
            $noidungkhongdau = $this->vn_str_filter($noidung);
            $arr_newdata = array(
                    'NOIDUNG' => 'Nhac viec UBND tinh:'.strip_tags($noidungkhongdau),
                    'TENDONVI' => $params['tendonvi'],
                    'TENNGUOINHAN' => $params['tennguoinhan'],
                    'ID_DONVI' => $params['id_donvi'],
                    'SODIENTHOAI' => $params['sodienthoai']
            );
            try {
                 $danhsachguisms = new DanhSachGuiSmsModel($year);
                 $danhsachguisms->insert($arr_newdata);
            } catch (Exception $ex) {
                //Khong the them moi loai van ban
                //$this->_redirect('/giamsatgiaoviec/danhsachguisms');
                echo "<script>window.parent.location.href='/giaoviec/danhsachguisms';</script>";
            }

        }
        //chuyen den trang xem danh sach cac loai van ban
       echo "<script>window.parent.location.href='/giaoviec/danhsachguisms';</script>";
    }
    
    
    /**
     * Ham controller cho chuc nang them moi loai van ban
     */
    public function saveAction() {
        if ($this->_request->isPost()) {
            //Doc du lieu tu form nhap lieu
            $active = $this->_request->getParam("active");
            if($active == NULL){
                $active = '9';
            }
            
            $tendonvi = $this->_request->getParam("tendonvi");
            $id = $this->_request->getParam("InputID");
            $noidung = $this->_request->getParam('noidung');
            $sodienthoai = $this->_request->getParam('sodienthoai');
            if ($id > 0) {

                //truong hop cap nhat
                //Thuc hien cap nhat du lieu
                $where = 'ID=' . $id;
                try {
                    $this->model->update(array(
                        'TENDONVI' => $tendonvi,
                        'NOIDUNG' => $noidung,
                        'TRANGTHAI' => $active,
                        'SODIENTHOAI' => $sodienthoai
                            ), $where);
                } catch (Exception $e) {
                    echo $e->__toString();
                    exit;
                    $this->_redirect('/giaoviec/danhsachguisms');
                }
            } else {

                //truong hop them moi
                //thuc hien them moi du lieu
                $arr_newdata = array(
                    'TENDONVI' => $tendonvi,
                        'NOIDUNG' => $noidung,
                         'TRANGTHAI' => $active,
                        'SODIENTHOAI' => $sodienthoai
                );
                try {
                    $this->model->insert($arr_newdata);
                    $this->_redirect('/giaoviec/danhsachguisms');
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                    $this->_redirect('/giaoviec/danhsachguisms');
                }
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhsachguisms');
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
                    $this->_redirect('/giaoviec/danhsachguisms');
                }
            } catch (Exception $ex) {
                //Loi trong qua trinh xoa
                $this->_redirect('/giaoviec/danhsachguisms');
            }
        }
        //chuyen den trang xem danh sach cac loai van ban
        $this->_redirect('/giaoviec/danhsachguisms');
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
                    $this->_redirect('/giaoviec/danhsachguisms');
                }

                //Lay du lieu cua linh vuc van ban can cap nhat
                $this->view->tendonvibefore = $rowcn->current()->TENDONVI;
                $this->view->noidungbefore = $rowcn->current()->NOIDUNG;
                $this->view->sodienthoaibefore = $rowcn->current()->SODIENTHOAI;
                $this->view->activeselect = $rowcn->current()->TRANGTHAI;
                $this->view->id = $idCapNhat;

                $this->view->title = "Cập nhật nội dung gửi sms";
            } else {
                //truong hop them moi du lieu
                $this->view->title = "Thêm mới nội dung gửi sms";
            }
        }
        //Hien thi trang nhap lieu
        $this->renderScript("danhsachguisms/InputData.phtml");
    }
    
     public function sendAction() {
            $param = $this->getRequest()->getParams();
            $giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
            $result = json_decode($giaoviecservice->sendSMS(
                    $token
                    ,$param["Phone"]
                     ,$param["Content"]
            ));
            if($result->data->Result == true){
                $where = 'ID=' . $param["id"];
                try {
                    $this->model->update(array(
                        'TRANGTHAI' => 2,
                        'THOIGIAN' => date('Y-m-d')
                            ), $where);
                } catch (Exception $e) {
                    echo $e->__toString();
                    exit;
                }
            }
            echo json_encode($result);
            exit;
     }
       public function sendfromdanhsachAction() {
            $param = $this->getRequest()->getParams();
            $giaoviecservice = new GiaoViecService();
            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'lienthong');
            $madonvi = $configlienthong->lienthong->user_id;
            $password = $configlienthong->lienthong->password;
            $token = $giaoviecservice->login($madonvi,md5($password),"");
            $noidungkhongdau = $this->vn_str_filter($param["Content"]);
            $result = json_decode($giaoviecservice->sendSMS(
                    $token
                    ,$param["Phone"]
                    ,'Nhac viec UBND tinh:'.strip_tags($noidungkhongdau)
            ));
            if($result->data->Result == true){
                
                $arr_newdata = array(
                        'TENDONVI' => $param["Donvi"],
                        'NOIDUNG' => 'Nhac viec UBND tinh:'.strip_tags($noidungkhongdau),
                        'TRANGTHAI' => 2,
                        'THOIGIAN' => date('Y-m-d'),
                        'SODIENTHOAI' => $param["Phone"]
                );
                try {
                    $this->model->insert($arr_newdata);
                } catch (Exception $ex) {
                    //Khong the them moi loai van ban
                }
                
            }
           
     }
}
        