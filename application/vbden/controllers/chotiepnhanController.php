<?php

/**
 * @auth Thangtba
 * @name Danh sách văn bản liên thông chờ tiếp nhận
 */
 require_once 'vbden/models/vbdenModel.php';
require_once 'nusoap/nusoap.php';
require_once 'vbden/models/chotiepnhanModel.php';
require_once 'hscv/models/gen_tempModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'qtht/models/DepartmentsModel.php';

class vbden_chotiepnhanController extends Zend_Controller_Action {

    private $client;
    private $session;
    private $dataform;

    public function init() {
        global $config;
        try {
            $this->client = new SoapClient($config->service->lienthong->uri);
            $this->session = $this->client->__call('Login', array(
                    'madonvi' => $config->service->lienthong->username,
                    'password' => $config->service->lienthong->password));
        } catch (Exception $ex) {
            $this->session = null;
        }
        $this->dataform = $this->getRequest()->getParams();
        parent::init();
    }

    function indexAction() {
        $this->view->title = 'Danh sách văn bản chờ tiếp nhận';
        global $config;
        global $auth;        
    	$user = $auth->getIdentity();
        $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
        $limit = $this->dataform["limit1"];
        $page = $this->dataform["page"];

        //Refinde parameters
        if ($limit == 0 || $limit == "")$limit = $config->limit;
        if ($page == 0 || $page == "")$page = 1;
        $param = array(
            'session' => $this->session,
            'service_code' => 'VANBAN',
            'service_name' => 'VANBANCANTIEPNHAN',
            'parameter' => base64_encode(($page - 1) * $limit) . '~' . base64_encode($limit). '~' . base64_encode($code)
        );
        if ($this->session == null) {
            echo 'Không kết nối được đến webservices';
        } else {
            $response = $this->client->__call('Execute', $param);
            //echo '<h1>'.$response.'</h1>';exit;
            //var_dump(base64_decode($response));exit;
            if ($response != '') {
                require_once 'Common/ServicesCommon.php';
                $data = array_reverse(ServicesCommon::DeseriallizeToArray(base64_decode($response)));
            }			
            $cntData = count($data);
            
            for($i=0;$i<$cntData;$i++){                
                $thongTinKhac        = base64_decode($data[$i]['THONGTINKHAC']);
                $arrThongTinKhac     = ServicesCommon::DeseriallizeToArray($thongTinKhac);
                
                $data[$i]['SOTO']    = base64_decode($arrThongTinKhac[0]['SOTO']);
                $data[$i]['SOBAN']   = base64_decode($arrThongTinKhac[0]['SOBAN']);
                $data[$i]['LINHVUC'] = base64_decode($arrThongTinKhac[0]['LINHVUC']);
                $data[$i]['DOMAT']   = base64_decode($arrThongTinKhac[0]['DOMAT']);
                $data[$i]['DOKHAN']  = base64_decode($arrThongTinKhac[0]['DOKHAN']);
            }
            $modelVanBanDen = new vbdenModel();
            $dataLinhVucVanBan = $modelVanBanDen->getAllLinhVucVanBan();
            $arrLinhVuc = array();
            foreach($dataLinhVucVanBan as $item){
                $arrLinhVuc[$item['ID']] = $item['NAME']; 
            }           
            
            //var_dump($data[9]['NGAYBANHANH']);exit;
            $rowcount = ChoTiepNhanModel::Counts($this->session);
            $this->view->data = $data;
            $this->view->arrLinhVuc = $arrLinhVuc;            
            $this->view->showPage = QLVBDHCommon::Paginator($rowcount,5, $limit, "frm", $page);
            $this->view->limit = $limit;
            $this->view->page = $page;
            $this->view->session = $this->session;
            $this->view->search = $search;
        }
    }

    function tuchoiAction() {

        $istiepnhan = $this->dataform['istiepnhan'];
        $masovanban = $this->dataform['masovanban'];
        $masovanbancha = $this->dataform['masovanbancha'];
        $donvinhan = $this->dataform['donvinhan'];
        $donvigui = $this->dataform['donvigui'];
        $ID_VBLTCP = $this->dataform["ID_VBLTCP"];
        global $config;
        try {
            if ($this->session == '' || $this->session == 0) {
                $session = ChoTiepNhanModel::LoginService();
            } else $session = $this->session;
            global $auth;        
            $user = $auth->getIdentity();
            $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
            if((int)$ID_VBLTCP > 0 )
            {
                $client = new SoapClient($config->service->lienthong->uri);
                $sessionlt = $client->__call('Login', array(
                'madonvi' => $code,
                'password' => $config->service->lienthong->password));
                $dataltvpcp = array(
                        'session'=>$sessionlt,
                        'ID_VBLTCP' => $ID_VBLTCP,
                        'Timestamp' => date('Y-m-d H:i:s'),
                        'status' => '02',
                        'Description'=> 'Từ chối tiếp nhận văn bản',
                        'Staff' => $user->FULLNAME,
                        'Department' => DepartmentsModel::getNameById($user->ID_DEP)
                      );
                $paramvpcp = array(
                    'session' => $sessionlt,
                    'service_code' => 'VPCP',
                    'service_name' => 'QLVBDHCapNhatTrangThaiVPCP',
                    'parameter' => base64_encode(json_encode($dataltvpcp))
                );
                $this->client->__call('Execute', $paramvpcp);
            }
            $params = array(
                'session' => $session,
                'service_code' => 'TRANGTHAI',
                'service_name' => 'UPDATEFORDONVINHAN',
                'parameter' => base64_encode($masovanban)
                                . '~' . base64_encode(4). '~' . base64_encode($code)
                );
            $istuchoi = $this->client->__call('Execute', $params);
            if ($istuchoi) {
                echo "<script>alert('Từ chối thành công');</script>";
                $this->_redirect('/vbden/chotiepnhan/');
            }
            
        } catch (Exception $e) {
            echo $e->__toString();exit;
        }
        exit;
    }

    function luanchuyenAction() {
        $this->_helper->layout->DisableLayout();
        global $config;
        $client = new SoapClient($config->service->lienthong->uri);
        $model = new ChoTiepNhanModel();
		$session = $model->LoginService();
		$masovanban = $this->dataform['masovanban'];
                $ID_VBLTCP = $this->dataform['ID_VBLTCP'];
        if($ID_VBLTCP != NULL)
        {
        $params = array(
            'session' => $session,
            'service_code' => 'VPCP',
            'service_name' => 'DongluanchuyenVPCP',
            'parameter' => base64_encode($ID_VBLTCP)
        );
        $datalc = $client->__call('Execute', $params);
        $this->view->data = json_decode($datalc);
        $this->view->ID_VBLTCP = 1;
        }else{
        $this->view->data = $model->DongLuanChuyen($masovanban,$session);
        $this->view->ID_VBLTCP = 0;
        }

    }

    function fileAction() {
        $masovanban = $this->dataform['masovanban'];
        $model_vbd = new ChoTiepNhanModel();
        $data = $model_vbd->GetFileByMaSoVanBan($masovanban, $this->session);
        $year = QLVBDHCommon::getYear();
        //chuẩn bị lưu file theo Id_Object
        global $auth;
        global $config;
        $gen_temp = new gen_tempModel();
        $idObject = $gen_temp->getIdTemp();
        $this->session = ChoTiepNhanModel::LoginService();
        $date = getdate();
        foreach ($data as $value) {            
            /**
             * Chuẩn bị lưu vào db
             */
            $model = new filedinhkemModel($year); //doi tuong model
            //Luu file xuong thu muc tam cua server
            $max_size = $con->file->maxsize;
            //$adapter = new FileTransfer(); // doi tuong adapter nhan file dinh kem tu client
            //$adapter->addValidator('size', $max_size);
            $temp_path = $model->getTempPath();
            //$adapter->setDestination($temp_path);
            $filepath = $temp_path.DIRECTORY_SEPARATOR.$value['FILENAME'];
            
            $file = new FileDinhKem();
            $file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
            $file->_nam = $date['year'];
            //$model->setNameByYear($file->_nam);
            $file->_thang = $date['mon'];
            $dirPath = $model->getDir($file->_nam,$file->_thang);
            $file->_folder = $dirPath;
            $file->_id_object = $idObject;
            $file->_user = $auth->getIdentity()->ID_U;
            $file->_filename = $value['FILENAME'];
            $file->_mime = $value['MIME'];

            $file->_type = -1;
            $id_file = $model->insertFileWithIdObject($file);
            $maso = $id_file.$file->_filename.$file->_time_update;
            $maso = md5($maso);
            $model->updateMaSo($id_file,$maso);
            $newlocation = $dirPath. DIRECTORY_SEPARATOR. $maso;
            rename($filepath,$newlocation);

            $file->_pathFile = $newlocation;
            $file->_id_dk = $id_file;
            if(file_exists($newlocation)) {
                if($is_nogetcontent == 0){
                $model->getContent($file,$pdf);
                }
            }

            /**
             * copy từ link download vào ổ cứng
             */
            $url = "http://".$config->service->lienthong->host."/?download=1&session=$this->session&masovanban=$masovanban&maso=".$value['MASO'];
            $int = file_put_contents($dirPath.DIRECTORY_SEPARATOR.$maso
                    , file_get_contents($url));
            //end upload
        }
        echo $idObject;
        exit;
    }

}