<?php

require_once 'Zend/Controller/Action.php';
require_once 'version/models/Version.php';

class Version_versionController extends Zend_Controller_Action {

    private $client = null;
    private $session = null;
    private $dataform = null;
    private $version_current = null;

    public function listAction() {
        $this->view->title = 'Danh sách phiên bản cần cập nhật';
        global $config;
        try {
            $this->client = new SoapClient($config->service->lienthong->uri);
            $this->session = $this->client->__call('Login', array(
                        'madonvi' => $config->service->lienthong->username,
                        'password' => $config->service->lienthong->password));
        } catch (Exception $ex) {
            $this->session = 0;
        }
       
        $param = array(
            'session' => $this->session,
            'service_code' => 'PHIENBAN',
            'service_name' => 'DANHSACHPHIENBANCANCAPNHAT',
            'parameter' => $config->service->lienthong->username
        );

        if ($this->session == null) {
            echo 'Bạn không có quyền thực hiện';
        } else {
            $response = $this->client->__call('Execute', $param);            
            if ($response != '') {         
                require_once 'Common/ServicesCommon.php';
                $data = array_reverse(ServicesCommon::DeseriallizeToArray(base64_decode($response)));
               // var_dump($data);exit;
            }
        }
        $this->view->data = $data;
        //QLVBDHButton::EnableAddNew("/version/quanlyversion/danhsachphienban", "Cập nhật phiên bản mới");
        QLVBDHButton::EnableBack('');
    }

    public function danhsachfileAction() {

        $this->view->ngaycapnhat = date('Y/m/d');
        $this->view->giocapnhat = date('H:i');
        $params = $this->getRequest()->getParam("id");
        
        global $config;
        $link = ($config->service->lienthong->host);
        try {
            $this->client = new SoapClient($config->service->lienthong->uri);
            $this->session = $this->client->__call('Login', array(
                        'madonvi' => $config->service->lienthong->username,
                        'password' => $config->service->lienthong->password));
        } catch (Exception $ex) {
            $this->session = 0;
        }
        $param = array(
            'session' => $this->session,
            'service_code' => 'PHIENBAN',
            'service_name' => 'DANHSACHFILE',
            'parameter' => $params
        );

        if ($this->session == null) {
            echo 'Bạn không có quyền thực hiện';
        } else {
            $response = $this->client->__call('Execute', $param);
            
            if ($response != '') {      
                $data=explode("\n", base64_decode($response));  
               for($i=0;$i<count($data)-1;$i++)
               {
                   $data2[]=$data[$i];
               }
                // var_dump($data2);exit;
                $arr = explode(',', $data[0]);
                $this->view->phanhe=$arr[6];
               // var_dump($arr);exit;
                require_once 'Common/ServicesCommon.php';
                $this->view->data = $data2;
                $model_version = new VersionModel();
                $result = $model_version->selectmasofile($params, $config->service->lienthong->username); 
                foreach ($result as $key => $value) {
                    $arr_file[]= $value['MASO_FILE'];
                    
                }
                
                $this->view->array_file=$arr_file;
                $this->view->id = $params;
                $this->view->sl = count($data) - 1;
                $this->view->pb=$arr[4];
                $this->view->title = 'Danh sách file phiên bản '.$arr[4].'. Phân hệ '.$arr[6];
            }
        }
        QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Cập Nhật", $action, "SaveButton", "SaveButtonClick();");
        QLVBDHButton::EnableBack('');
    }

    public function theodoicapnhatAction() {
        
        global $config;
        $id_vers = $this->getRequest()->getParam("id_vers"); 
        $model_version = new VersionModel();
        $result = $model_version->selectversion($id_vers, $config->service->lienthong->username);
       // var_dump($result);
        $this->view->data = $result;
        $this->view->ngaycapnhat=$result[0]['NGAY_CAP_NHAT'];
        $this->view->title = 'Theo dõi cập nhật của phiên bản '.$result[0]['VERSION'].'. Phân hệ '.$result[0]['PHAN_HE'];
        $this->view->id_ver = $id_vers;
        $this->view->id_donvi = $config->service->lienthong->username;
        //  QLVBDHButton::EnableDelete('');
        QLVBDHButton::EnableBack('');
    }

    public function capnhatfileAction() {
        
        global $config;
        $params = ($this->_request->getParam('DOWN'));
        $id = ($this->_request->getParam('ID'));
        $path = ($this->_request->getParam('PATH'));        
        $name = ($this->_request->getParam('NAME'));
        $check = ($this->_request->getParam('CHECK'));
        $kichban = $this->_request->getParam('KB');
        $phanhe = $this->_request->getParam('PH'); 
        $phienban= $this->_request->getParam('PB');
        $ngay = $this->_request->getParam('ngaycapnhat');
        $gio = $this->_request->getParam('giocapnhat');
        $ngaygio = $ngay . ' ' . $gio . ':00';
        $sl = $this->getRequest()->getParam("sl");
        $model_version = new VersionModel();
        $i = 0;
        
         $model_version->deletefile($id);
        
        foreach ($check as $value) {
           
            $data = array(
                'MASO_FILE' => $params[$value],
                'ID_VERSION' =>$id,
                'PATH' =>      $path[$value],
                'FILE_NAME' => $name[$value],
                'ID_DONVI' =>  $config->service->lienthong->username,
                'KICH_BAN' =>  $kichban[$value],
                'NGAY_CAP_NHAT' => $ngaygio,
                'PHAN_HE' =>   $phanhe[$value],
                'VERSION'=>$phienban
            );
            $i++;

            $model_version->insertfile($data);
        }

        $this->_redirect('version/version/list');
    }
        
    

}

?>
