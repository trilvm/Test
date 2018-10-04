<?php
require_once "giaoviec/models/giaoviecservice.php";
require_once "qtht/models/DepartmentsModel.php";
require_once 'hscv/models/hosocongviecModel.php';
require_once 'giaoviec/models/LoaiCongViecGiaoViecModel.php';
require_once 'giaoviec/models/GiaoViecModel.php';
require_once 'hscv/models/filedinhkemModel.php';
class Giaoviec_IndexController extends Zend_Controller_Action {
        
    public function xemtiendoAction() {
		$param = $this->getRequest()->getParams();
		$this->view->title = "Tiến độ giải quyết nhiệm vụ";
		$this->view->subtitle = "";
 
		$giaoviecservice = new GiaoViecService();
		$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
		$madonvi = $configlienthong->service->lienthong->username;
		$password = $configlienthong->service->lienthong->password;
		$token = $giaoviecservice->login($madonvi,md5($password),"");
        $this->view->data = json_decode($giaoviecservice->SelectTienDoCongViecByIDSTT($token,$param["id"]))->data;
        //  echo "<pre>"; var_dump($this->view->data);exit;
        QLVBDHButton::EnableBack("giaoviec/index/danhsach");
	}
    
    // 27/8/2016 vuld add func save cap nhat tien do
    public function savetiendoAction() {
        global $auth;
        $param = $this->getRequest()->getParams();
        // var_dump($param);exit;
        $con = Zend_Registry::get('config');
        $au = Zend_Registry::get('auth');
        
		$date = getdate();
		if(!$type)
			$type = -1;
		$year = QLVBDHCommon::getYear();		
		if(!$idObject)
			$idObject = 0;
		
		if(!$isTemp)
            $isTemp = 0;
        $ID_DK =null;
        //upload file va luu len service_filedinhkem
		if($_FILES['uploadedfile']['name'] != ''){
            $model = new filedinhkemModel($year); 
            $max_size = $con->file->maxsize;
            $temp_path = $model->getTempPath();
            
            $filepath = $temp_path.DIRECTORY_SEPARATOR.$_FILES['uploadedfile']['name'];
            if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'],$filepath))
            {
                return -1;
            }else{
                $file = new FileDinhKem();
                $file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
                $file->_nam = $date['year'];
                $file->_thang = $date['mon'];
                $dirPath = $model->getDir($file->_nam,$file->_thang);
                
                $file->_folder = $dirPath;
                $file->_id_object = $idObject;
                $file->_user = $au->getIdentity()->ID_U;
                $file->_filename = $_FILES['uploadedfile']['name'];
                $file->_mime = $_FILES['uploadedfile']['type'];
                $file->_content = $model->getContent($filepath);
                $file->_type = $type;
                $id_file = $model->insertFileWithIdObject($file);
                $maso = $id_file.$file->_filename.$file->_time_update;
                $maso = md5($maso);
                $model->updateMaSo($id_file,$maso);
                $newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
                rename($filepath,$newlocation);
                
                $file->_pathFile = $newlocation;
                $file->_id_dk = $id_file;
            }
            //save file db service
            $config = Zend_Registry::get('config');
            try {
                $wsdl       = $config->service->lienthong->uri;
                $username   = $config->service->lienthong->username;
                $password   = $config->service->lienthong->password;
                $cliente    = new SoapClient($wsdl);
                $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
            }catch (Exception $ex){
                
            }
            $filedinhkemModel = new filedinhkemModel($year);
            $fileInfo   = $filedinhkemModel->getFileByMaso($maso);
            $size       = filesize($fileInfo->_pathFile);
            $dataFile   = file_get_contents($fileInfo->_pathFile);
            $limit = 32896;
            $start = 0;
            $maSoVanBan = $param['id_vblienthongra'];
         //   var_dump($dataFile);exit;
            $maSoFileServices   = "";
       //     $fileContent = substr($dataFile,$start,$limit);
        //    var_dump($fileContent);exit;
            $arrFileInfo = array(
                base64_encode($maSoVanBan),
                base64_encode($fileInfo->_filename),
                base64_encode($maSoFileServices),
                base64_encode($fileInfo->_mime),
                base64_encode($dataFile)
            );
            $parameter = implode('~',$arrFileInfo);
            $paramRQ = array(
                'session' => $session,
                'service_code' => 'FILEVANBAN',
                'service_name' => 'SENDDINHKEM',
                'parameter' => $parameter
                );
            $ID_DK = $cliente->__call('Execute', $paramRQ);
        }
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $giaoviecservice = new GiaoViecService();
        $token = $giaoviecservice->login($madonvi,md5($password),"");
        
        $user = $auth->getIdentity();
        $model = new UsersModel();
        $nguoinhan = $model->getName($user->ID_U);
        $user_dep = UsersModel::getUserDepId($user->ID_U);

        //cap nhat tien do vao giaoviec_congviecdetail
        $giaoviecservice->UpdateTienDoCV(
            $token
            ,$param['id_congviec']
            ,$param['tiendo']
        );
        // tao nhat ky cong viec dinh kem file
        if($param["trangthaixacnhan"] ==1 ){
            $noidungchuyen = 'Xác nhận kết quả: Hoàn thành nhiệm vụ';
        }
        elseif($param["trangthaixacnhan"] ==2){
            $noidungchuyen = 'Xác nhận kết quả: Hoàn thành nhiệm vụ trễ hạn';
        }else{
            $noidungchuyen = 'Xác nhận kết quả: Chưa hoàn thành';
        }
        $giaoviecservice->createNhatKyDinhKem(
            $token
            ,$param['id_congviec']
            ,$user->ID_U
            ,$nguoinhan['TENNGUOITAO']
            ,$param['tiendo']
            ,$noidungchuyen
            ,$user_dep['NAME']
            ,$param['motatiendo']
            ,$ID_DK
        );
        // cap nhat xac nhan trang thai
        $result = json_decode($giaoviecservice->xacnhanTrangThaiCongViecSTT(
            $token
            ,$param["macongviectt"]
            ,$param["donvinhantt"]
            ,$param["trangthaixacnhan"]
        ));
        $this->_redirect('/giaoviec/index/xemtiendo/id/'.$param['id_congviecdetail']);
    }
    //vuld end
       
	public function indexAction() {
        global $auth;
        $user = $auth->getIdentity();
        $param = $this->getRequest()->getParams();
        $idnguoixuly = $user->ID_U;
        if(!isset($param["trangthai"])) $param["trangthai"] = -1;
        $this->view->title = "Giao việc";
        $this->view->subtitle = "Danh sách nhiệm vụ cần xử lý";
            $page = $param['page'];
            $limit = $param['limit1'];
            if ($limit == 0 || $limit == ""
                )$limit = 10;
            if ($page == 0 || $page == ""
                )$page = 1;
            $offset = ($page - 1) * $limit;
            $giaoviecservice = new GiaoViecService();
            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
            $madonvi = $configlienthong->service->lienthong->username;
            $password = $configlienthong->service->lienthong->password;
            $token = $giaoviecservice->login($madonvi,md5($password),"");
            $this->view->data = json_decode($giaoviecservice->selectCongViec($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$param["NGUOITHUCHIEN"],$idnguoixuly,$offset,$limit))->data;
            //  var_dump($this->view->data);exit;
            if(count($this->view->data) <= 0){
                $limit = '';
            }
            $this->view->trangthai = $param["trangthai"];
            $this->view->page = $page;
            $this->view->limit = $limit;
            $this->view->SOKYHIEU = $param["SOKYHIEU"];
            $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
            $this->view->NGUOITHUCHIEN = $param["NGUOITHUCHIEN"];
            $nhiemvucount = json_decode($giaoviecservice->countCongViec($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$param["NGUOITHUCHIEN"],$idnguoixuly))->data;

            $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/index");
            QLVBDHButton::EnableNhiemvuXulyToancoquan('/giaoviec/index/indexall');
        }
        
        public function danhsachAction() {
                global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if(!isset($param["trangthai"])) $param["trangthai"] = -1;
		$this->view->title = "Danh sách nhiệm vụ đã giao";
		$this->view->subtitle = "";
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = $config->limit;
                if ($page == 0 || $page == ""
                    )$page = 1;
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $idnguoitheogioi = $user->ID_U;
                $offset = ($page - 1) * $limit;
				$this->view->data = json_decode($giaoviecservice->selectCongViecGiao($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi,$offset,$limit))->data;
                $this->view->trangthai = $param["trangthai"];
                $this->view->CQLIENTHONG = $param["CQLIENTHONG"];
                $this->view->SOKYHIEU = $param["SOKYHIEU"];
                $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
                $nhiemvucount = json_decode($giaoviecservice->selectCongViecGiao($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi))->data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/danhsach");
                //if(hosocongviecModel::isVanthu($user->ID_U)) QLVBDHButton::EnableAddNew("/giaoviec/index/input");;
                if(hosocongviecModel::isAlowSeeAllVbDi()) QLVBDHButton::EnableNhiemvuToancoquan('/giaoviec/index/danhsachall');
                
              
	}
        
        
        public function danhsachallAction() {
             
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if(!isset($param["trangthai"])) $param["trangthai"] = -1;
		$this->view->title = "Danh sách nhiệm vụ đã giao toàn cơ quan";
		$this->view->subtitle = "";
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = $config->limit;
                if ($page == 0 || $page == ""
                    )$page = 1;
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $offset = ($page - 1) * $limit;
		$this->view->data = json_decode($giaoviecservice->selectCongViecGiao($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi,$offset,$limit))->data;
                $this->view->trangthai = $param["trangthai"];
                $this->view->CQLIENTHONG = $param["CQLIENTHONG"];
                $this->view->SOKYHIEU = $param["SOKYHIEU"];
                $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
                $nhiemvucount = json_decode($giaoviecservice->selectCongViecGiao($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi))->data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/danhsach");
        
                QLVBDHButton::EnableNhiemvuTheogioi('/giaoviec/index/danhsach');
	}
        public function danhsachnhiemvuAction() {
                global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if(!isset($param["trangthai"])) $param["trangthai"] = -1;
		$this->view->title = "Danh sách nhiệm vụ đã giao không qua văn bản";
		$this->view->subtitle = "";
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = $config->limit;
                if ($page == 0 || $page == ""
                    )$page = 1;
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $idnguoitheogioi = $user->ID_U;
                $offset = ($page - 1) * $limit;
		$this->view->data = json_decode($giaoviecservice->selectCongViecGiaoKhongVB($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi,$offset,$limit))->data;
                $this->view->trangthai = $param["trangthai"];
                $this->view->CQLIENTHONG = $param["CQLIENTHONG"];
                $this->view->SOKYHIEU = $param["SOKYHIEU"];
                $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
                $nhiemvucount = json_decode($giaoviecservice->selectCongViecGiaoKhongVB($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi))->data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/danhsach");
               // if(hosocongviecModel::isVanthu($user->ID_U)) QLVBDHButton::EnableAddNew("/giaoviec/index/input");;
                if(hosocongviecModel::isAlowSeeAllVbDi()) QLVBDHButton::EnableNhiemvuKhongVBToancoquan('/giaoviec/index/danhsachnhiemvuall');              
	}
        
        public function danhsachnhiemvuallAction() {
                global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		if(!isset($param["trangthai"])) $param["trangthai"] = -1;
		$this->view->title = "Danh sách nhiệm vụ đã giao không qua văn bản toàn cơ quan";
		$this->view->subtitle = "";
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = $config->limit;
                if ($page == 0 || $page == ""
                    )$page = 1;
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $offset = ($page - 1) * $limit;
		$this->view->data = json_decode($giaoviecservice->selectCongViecGiaoKhongVB($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi,$offset,$limit))->data;
                $this->view->trangthai = $param["trangthai"];
                $this->view->CQLIENTHONG = $param["CQLIENTHONG"];
                $this->view->SOKYHIEU = $param["SOKYHIEU"];
                $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
                $nhiemvucount = json_decode($giaoviecservice->selectCongViecGiaoKhongVB($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$idnguoitheogioi))->data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/danhsach");
        
                QLVBDHButton::EnableNhiemvuKhongVBTheogioi('/giaoviec/index/danhsach');             
	}
	public function tiepnhanAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
		$result = json_decode($giaoviecservice->acceptCongViec(
			$token
			,$param["id"]
			,$user->ID_U
			,$user->FULLNAME
			,DepartmentsModel::getNameById($user->ID_DEP)
		));
		echo json_encode($result);
		exit;
	}

	public function tuchoiAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
		$result = json_decode($giaoviecservice->rejectCongViec(
			$token
			,$param["macongviec"]
			,$user->ID_U
			,$user->FULLNAME
			,DepartmentsModel::getNameById($user->ID_DEP)
			,$param["lydo"]
		));
		echo json_encode($result);
		exit;
	}
        
        public function xacnhantrangthaiAction(){
            global $auth;
            $user = $auth->getIdentity();
            $param = $this->getRequest()->getParams();
            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
            $madonvi = $configlienthong->service->lienthong->username;
            $password = $configlienthong->service->lienthong->password;
            $giaoviecservice = new GiaoViecService();
            $token = $giaoviecservice->login($madonvi,md5($password),"");
            $result = json_decode($giaoviecservice->xacnhanTrangThaiCongViecSTT(
                $token
                ,$param["macongviectt"]
                ,$param["donvinhantt"]
                ,$param["trangthaixacnhan"]
            ));
            $model = new UsersModel();
            $nguoinhan = $model->getName($user->ID_U);
            $user_dep = UsersModel::getUserDepId($user->ID_U);
                        
            if($param["trangthaixacnhan"] ==1 ){
                $param['TIENDO_GIAOVIEC'] = '100';
                $noidungchuyen = 'Xác nhận kết quả: Hoàn thành nhiệm vụ';
            }
            elseif($param["trangthaixacnhan"] ==2){
                $param['TIENDO_GIAOVIEC'] = '100';
                $noidungchuyen = 'Xác nhận kết quả: Hoàn thành nhiệm vụ trễ hạn';
            }else{
                $param['TIENDO_GIAOVIEC'] = '0';
                $noidungchuyen = 'Xác nhận kết quả: Chưa hoàn thành';
            }
            
            $giaoviecservice->createNhatKy(
                $token
                ,$param['macongviectt']
                ,$user->ID_U
                ,$nguoinhan['TENNGUOITAO']
                ,$param['TIENDO_GIAOVIEC']
                ,$noidungchuyen
                ,$user_dep['NAME']
                ,$param["bosungchitiet"]
            );
        }
        
        public function giahanxulyAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$giaoviecservice = new GiaoViecService();
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
                $hanhientai =$param['hanxlhientai'];
                $songaygiahan =$param['songaygiahan'];
                $ngayketthuc = date();
                $ngayketthuc = QLVBDHCommon::addDateAll(strtotime($hanhientai), $songaygiahan);               
                $ngaydukienhoanthanhmoi = date('Y-m-d',$ngayketthuc);

		$result = json_decode($giaoviecservice->giahanxulyCongViec(
			$token
                        ,$param["macongviecgh"]
                        ,$param["donvinhangiahan"]
			,$ngaydukienhoanthanhmoi
		));
                echo json_encode($result);
		exit;
	}
        
	public function congcongAction(){
		$param = $this->getRequest()->getParams();
		$giaoviecservice = new GiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
		if($param["function"]=="selectThamGiaXuLy"){
			echo json_encode(json_decode($giaoviecservice->selectThamGiaXuLy($token,$param["macongviec"]))->data);
		}
		exit;
	}
        function inputAction() {

        $param = $this->_request->getParams();
        $id = $param["id"];
        $auth = Zend_Registry::get('auth');
        $user = $auth->getIdentity();
        $realyear = QLVBDHCommon::getYear();
        $this->view->year = $realyear;
        $this->view->user = $user;
        
        $giaoviecservice = new LoaiCongViecGiaoViecService();
			$configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
			$madonvi = $configlienthong->service->lienthong->username;
			$password = $configlienthong->service->lienthong->password;
			$token = $giaoviecservice->login($madonvi,md5($password),"");
        $this->view->dataloaicongviec = json_decode($giaoviecservice->selectDanhMucLoaiCongViec($token,1))->data;
        $this->view->name_u = UsersModel::getEmloyeeNameByIdUser($user->ID_U);
        $this->id = $id;
        $this->view->title = "Thêm mới nhiệm vụ";
        QLVBDHButton::EnableSave("giaoviec/index/save");
        QLVBDHButton::EnableBack("giaoviec/index/danhsach");
        }
        function saveAction(){
            $param = $this->_request->getParams();
                $giaoviecmodel = GiaoViecModel::TaoCongViecFromVBLT(-1,$param);
                if($giaoviecmodel->error->code > 0)
                {
                    echo $giaoviecmodel->error->message ;
                }
            $this->_redirect('/giaoviec/index/danhsach/');    
        }
	public function listnhanAction() {
		$param = $this->getRequest()->getParams();
		if(!isset($param["fromdate"]) ||$param["fromdate"] =="") {
                    $param["fromdate"] = date('01-m-Y');
                }
                if(!isset($param["todate"]) || $param["todate"] ==""){
                    $param["todate"] = date('d-m-Y');
                }
		$this->view->title = "Quản lý theo dõi, đôn đốc thực hiện nhiệm vụ";
		$this->view->subtitle = "";
                $giaoviecservice = new GiaoViecService();
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
		$token = $giaoviecservice->login($madonvi,md5($password),"");
                $this->view->dataDonVi = json_decode($giaoviecservice->getListDonVi($token))->data;
                $this->view->fromdate = $param["fromdate"];
                $this->view->todate = $param["todate"];
                //$this->view->month = $param["todate"];
                // vuld change month return view
                  $param["fromdate"] = str_replace('/','-',$param["fromdate"]);
                  $param["todate"] = str_replace('/','-',$param["todate"]);
                    
                  $monthFrom = explode("-",$param["fromdate"]);
                  $monthTo = explode("-",$param["todate"]);
                  if(ltrim($monthFrom[1],"0") == ltrim($monthTo[1],"0")){
                      $this->view->month = ltrim($monthTo[1],"0");
                  }else{
                      $this->view->month = ltrim($monthFrom[1],"0")." ĐẾN THÁNG ".ltrim($monthTo[1],"0");
                  }
                  // vuld end
                $param["sel_donvi"] = '000.01.01.H11';
		if(isset($param["fromdate"]) && isset($param["todate"]) && $param["fromdate"]!="" && $param["todate"] !=""){
			$param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
			$param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
                        $param["todate"] = strtotime($param["todate"]);
                        $param["todate"] = strtotime("+1 day",$param["todate"]);
                        $param["todate"] = date('Y-m-d',$param["todate"]);
                        $param["fromdate"] = date('Y-m-d',strtotime($param["fromdate"]));
			$donvilist = '000.01.01.H11' ;
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"]))->data;
            //var_dump($this->view->dataReport);exit;
        }
	}
        
        public function listnhandetailAction() {
                $this->view->title = "Quản lý theo dõi, đôn đốc thực hiện nhiệm vụ";
                $param = $this->getRequest()->getParams();
		$giaoviecservice = new GiaoViecService();
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
              // $param["sel_donvi"] = '121';
                $param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
                $param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
                $param["todate"] = strtotime($param["todate"]);
                $param["todate"] = strtotime("+1 day",$param["todate"]);
                $param["todate"] = date('Y-m-d',$param["todate"]);
                $param["fromdate"] = date('Y-m-d',strtotime($param["fromdate"]));
		//$donvilist = "-1,".implode(",",$param["sel_donvi"]);
                $donvilist = '000.01.01.H11' ;
		switch($param["REPORTTYPE"]){
			case 1:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				 break;
			case 2:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				break;
			case 3:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				break;
			case 4:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("index/listnhandetaildathuchien.phtml");
                                break;
			case 5:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				break;
			case 6:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				break;
			case 7:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				break;
                        case 8:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
				$this->renderScript("index/listnhandetaildangthuchien.phtml");
                                break;  
                        case 9:
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
                                $this->renderScript("index/listnhandetailchuathuchien.phtml");
                                break; 
                        case 10:
                                $param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
                                $param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));

                                $param["fromdate"] = strtotime($param["fromdate"]);
                                $param["fromdate"] = strtotime("-1 month",$param["fromdate"]);
                                $param["fromdate"] = date('Y-m-d',$param["fromdate"]);


                                $param["todate"] = strtotime($param["todate"]);
                                $param["todate"] = strtotime("-1 month",$param["todate"]);
                                $param["todate"] = date('Y-m-d',$param["todate"]);
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],$param["REPORTTYPE"]))->data;
                                $this->renderScript("index/listnhandetaildangthuchien.phtml");
                                break;
            //1/8/2018 vuld edit file return data
			case 11:
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],2))->data;
			$this->view->subTitle = "Tổng số nhiệm vụ được giao";
            //var_dump($this->view->dataReport);exit;
			break;
			case 12:
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],4))->data;
			$this->view->subTitle = "Số nhiệm vụ đã thực hiện trước hạn";
			$this->renderScript("index/listdetaildathuchien.phtml");
			break;
			case 13:
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],4))->data;
			$this->view->subTitle = "Số nhiệm vụ đã thực hiện đúng hạn";
			$this->renderScript("index/listdetaildathuchiendung.phtml");
			break;
			case 14:
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],4))->data;
			$this->view->subTitle = "Số nhiệm vụ đã thực hiện quá hạn";
			$this->renderScript("index/listdetaildathuchientre.phtml");
			break;
			case 15: 
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],8))->data;
			$this->view->subTitle = "Số nhiệm vụ đang thực hiện trong hạn";
			$this->renderScript("index/listdetaildangthuchien.phtml");
			break;				
			case 16:
			$this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecNhan($token,$param["fromdate"],$param["todate"],$donvilist,(int)$param["sel_nguoiky"],(int)$param["sel_nguoisoan"],$param["DONVINHAN"],8))->data;
			$this->view->subTitle = "Số nhiệm vụ đang thực hiện trễ hạn";
			$this->renderScript("index/listdetaildangthuchientre.phtml");
            //var_dump($this->view->dataReport);exit;
			break;
			//vuld end                            
		}
        }
    //vuld 6/8/2018 add func list ds toan cq 
    public function indexallAction() {
			global $auth;
			$user = $auth->getIdentity();
			$param = $this->getRequest()->getParams();
                //$idnguoixuly = $user->ID_U;
			if(!isset($param["trangthai"])) $param["trangthai"] = -1;
			$this->view->title = "Giao việc";
			$this->view->subtitle = "Danh sách nhiệm vụ cần xử lý";
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = 10;
                if ($page == 0 || $page == ""
                    )$page = 1;
                $offset = ($page - 1) * $limit;
                $giaoviecservice = new GiaoViecService();
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $this->view->data = json_decode($giaoviecservice->selectCongViec($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$param["NGUOITHUCHIEN"],$idnguoixuly,$offset,$limit))->data;
                //echo "<pre>";  var_dump($this->view->data);exit;
                if(count($this->view->data) <= 0){
                    $limit = '';
                }
                $this->view->trangthai = $param["trangthai"];
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->SOKYHIEU = $param["SOKYHIEU"];
                $this->view->TENCONGVIEC = $param["TENCONGVIEC"];
                $this->view->NGUOITHUCHIEN = $param["NGUOITHUCHIEN"];
                $nhiemvucount = json_decode($giaoviecservice->countCongViec($token,$param["trangthai"],$param["CQLIENTHONG"],$param["SOKYHIEU"],$param["TENCONGVIEC"],$param["NGUOITHUCHIEN"],$idnguoixuly))->data;

				$this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($nhiemvucount), 10, $limit, "frm", $page, "/giaoviec/index/indexall");
				QLVBDHButton::EnableNhiemvuXulyTheogioi('/giaoviec/index/index');

			}
    //vuld end
            public function giaonhiemvuAction() {
		$this->_helper->layout->disableLayout();
                $params = $this->_request->getParams();
                $this->view->id = $params['id'];
                $this->view->tencongviec = $params['tencongviec'];
                $this->view->macongviec = $params['macongviec'];
                $this->view->sokyhieu = base64_decode($params['sokyhieu']);
            }
            public function savegiaonhiemvuAction() {
                $this->_helper->layout->disableLayout();
                global $db;
                global $auth;        
                $user = $auth->getIdentity();
                $params = $this->_request->getParams();
                $id_u  = $params['ID_U'];
                if(COUNT($id_u) > 0)
                        {
                            for($i=0;$i < COUNT($id_u);$i++){
                                    $db->insert(QLVBDHCommon::Table("nhiemvu"),array(
                                            "ID_CONGVIEC"   => $params["id"],
                                            "TENCONGVIEC"   => json_decode(base64_decode($params['tencongviec'])),
                                            "ID_NGUOINHAN"  => $params['ID_U'][$i],
                                            "ID_NGUOIGIAO"  => $user->ID_U,
                                            "NOIDUNG"       => $params['NOIDUNG'],
                                            "SOKYHIEU_VBD"  => $params['sokyhieu'],
                                            "MACONGVIEC"    => $params['macongviec'],
                                            "NGAYGIAO"      => date('d/m/Y - H:i:s'),
                                            "TRANGTHAI"     => 1
                                    ));
                                if($params['macongviec']!= '' || $params['macongviec'] != NULL)
                                {
                                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                                $madonvi = $configlienthong->service->lienthong->username;
                                $password = $configlienthong->service->lienthong->password;
                                $giaoviecservice = new GiaoViecService();
                                $model = new UsersModel();
                                $nguoinhan = $model->getName($params['ID_U'][$i]);
                                $token = $giaoviecservice->login($madonvi,md5($password),"");
                                $giaoviecservice->UpdateNguoiXLCongViec(
                                        $token
                                        ,$params['macongviec']
                                        ,$params['ID_U'][$i]
                                        ,$nguoinhan['TENNGUOITAO']
                                        ,99
                                        );
                                }
                            }
                        }
                echo "<script>window.parent.document.frm.submit();</script>";       
            }
            public function danhsachgiaoAction() {
		$param = $this->getRequest()->getParams();
                global $auth;        
                $user = $auth->getIdentity();
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = 10;
                if ($page == 0 || $page == ""
                    )$page = 1;
                $offset = ($page - 1) * $limit;
		
                $data = GiaoViecModel::getdanhsachnhiemvugiao($user->ID_U,$offset,$limit);
                $this->view->title = "Danh sách nhiệm vụ đã giao";
                $this->view->data = $data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($data), 10, $limit, "frm", $page, "/giaoviec/index/danhsachgiao");
            }
            public function danhsachnhanAction() {
		$param = $this->getRequest()->getParams();
		$this->view->title = "Danh sách nhiệm vụ đã nhận";
                global $auth;        
                $user = $auth->getIdentity();
                $page = $param['page'];
                $limit = $param['limit1'];
                if ($limit == 0 || $limit == ""
                    )$limit = 10;
                if ($page == 0 || $page == ""
                    )$page = 1;
                $offset = ($page - 1) * $limit;
		
                $data = GiaoViecModel::getdanhsachnhiemvunhan($user->ID_U,$offset,$limit);
                $this->view->title = "Danh sách nhiệm vụ đã giao";
                $this->view->data = $data;
                $this->view->page = $page;
                $this->view->limit = $limit;
                $this->view->showPage = QLVBDHCommon::PaginatorWithAction(count($data), 10, $limit, "frm", $page, "/giaoviec/index/danhsachgiao");
            }

    // bao cao giao viec
    public function baocaogiaoviecAction(){
        $this->renderScript("report/baocaogiaoviec.phtml");
    }
        
    public function baocaogiaoviecviewAction(){
        $this->_helper->layout->disableLayout();
        $param = $this->getRequest()->getParams();
        if(!isset($param["fromdate"]) ||$param["fromdate"] =="") {
            $param["fromdate"] = date('01/m/Y');
        }
        if(!isset($param["todate"]) || $param["todate"] ==""){
            $param["todate"] = date('t/m/Y');
        }
        $this->view->title = "Quản lý theo dõi, đôn độc thực hiện nhiệm vụ đã giao";
        $this->view->subtitle = "";
        $giaoviecservice = new GiaoViecService();
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $token = $giaoviecservice->login($madonvi,md5($password),"");
        $this->view->dataDonVi = json_decode($giaoviecservice->getListDonVi($token))->data;
        $this->view->fromdate = $param["fromdate"];
        $this->view->todate = $param["todate"];
        
        if(isset($param["fromdate"]) && isset($param["todate"]) && $param["fromdate"]!="" && $param["todate"] !=""){
            $param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
            $param["tungay"] = strtotime($param["fromdate"]);
            $param["fromdate"] = date('Y-m-d',$param["tungay"]);
            $param["month"] = date('m',$param["tungay"]);
            $param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
            $param["todate"] = strtotime($param["todate"]);
            $param["todate"] = strtotime("+1 day",$param["todate"]);
            $param["todate"] = date('Y-m-d',$param["todate"]);
            if($param['selectBaoCao'] == '0'){
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiGiaoViecSTT($token,$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],(int)$param["sel_trangthai"]))->data;
			}elseif($param['selectBaoCao'] == '1'){
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiGiaoViecHoanThanhSTT($token,$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],(int)$param["sel_trangthai"]))->data;
			}elseif($param['selectBaoCao'] == '2'){
				$this->view->dataReport = json_decode($giaoviecservice->theoDoiGiaoViecXacNhanSTT($token,$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],(int)$param["xacnhantrangthai"]))->data;
            }    
            $this->view->month = $param["month"];   
            $this->renderScript("report/baocaogiaoviecview.phtml"); 
        }
        $is_in = $param['is_in'];		
        if($is_in){ 
            if($param["h_isexel"]==1){
                $this->view->is_in = 0;
                header("Content-Type: application/vnd.ms-excel; name='excel'");
                header("Content-Disposition: attachment; filename=baocaotkcongviec.xls;");
                header("Pragma: no-cache");
                header("Expires: 0");
            }else{$this->view->is_in = 1;}                     
            $this->renderScript("report/baocaogiaoviecin.phtml");
        }
    }
    public function baocaogiaoviecdetailAction(){
        // $this->_helper->layout->disableLayout();
        $param = $this->getRequest()->getParams();
        $this->view->title = "Quản lý theo dõi, đôn đốc thực hiện nhiệm vụ đã giao";
        QLVBDHButton::AddButton("Xuất Excel","Excel","Excel","fnExcelReport();");

        $giaoviecservice = new GiaoViecService();
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $token = $giaoviecservice->login($madonvi,md5($password),"");
        $this->view->fromdate=$param["fromdate"];
        $this->view->todate=$param["todate"];

        $param["fromdate"] = implode("-",array_reverse(explode("/",$param["fromdate"])));
        $param["todate"] = implode("-",array_reverse(explode("/",$param["todate"])));
        $param["todate"] = strtotime($param["todate"]);
        $param["todate"] = strtotime("+1 day",$param["todate"]);
        $param["todate"] = date('Y-m-d',$param["todate"]);
                
        switch($param["REPORTTYPE"]){
            case 1:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],2))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],2))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],2))->data;
            }
            $this->view->subTitle = "Tổng số nhiệm vụ được giao";
            $this->renderScript("report/baocaogiaoviecdetail.phtml");
            break;
            case 2:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }
            $this->view->subTitle = "Số nhiệm vụ đã thực hiện đúng hạn";
            $this->renderScript("report/listdetaildathuchiendung.phtml");
            break;
            case 3:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }
            $this->view->subTitle = "Số nhiệm vụ đã thực hiện quá hạn";
            $this->renderScript("report/listdetaildathuchientre.phtml");
            break;
            case 4:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],4))->data;
            }
            $this->view->subTitle = "Số nhiệm vụ đang thực hiện trong hạn";
            $this->renderScript("report/listdetailtongdunghan.phtml");
            break;              
            case 5:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }
            $this->view->subTitle = "Số nhiệm vụ đang thực hiện trễ hạn";
            $this->renderScript("report/listdetaildangthuchien.phtml");
            break;
            case 6:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }
            $this->view->subTitle = "Tổng số nhiệm vụ đã hoàn thành";
            $this->renderScript("report/listdetaildangthuchientre.phtml");
            break;
            case 7:
            if($param['selectBaoCao'] == '0'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '1'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecHTSTT($token,$param["sel_trangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }elseif($param['selectBaoCao'] == '2'){
                $this->view->dataReport = json_decode($giaoviecservice->theoDoiChiTietGiaoViecXNSTT($token,$param["xacnhantrangthai"],$param["fromdate"],$param["todate"],(int)$param["sel_nguoisoan"],8))->data;
            }
            $this->view->subTitle = "Tổng số nhiệm vụ chưa hoàn thành";
            $this->renderScript("report/listdetailtongtrehan.phtml");
            break;
        }
    }
}