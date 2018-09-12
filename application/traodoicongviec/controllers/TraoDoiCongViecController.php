<?php
require_once ('Zend/Controller/Action.php');
require_once 'traodoicongviec/models/traodoicongviecModel.php';
require_once 'nusoap/nusoap.php';
require_once 'qtht/models/UsersModel.php';
require_once 'hscv/models/filedinhkemModel.php';

class Traodoicongviec_TraodoicongviecController extends Zend_Controller_Action {

    public function capnhattrangthaiAction(){        
        $this->_helper->layout->disableLayout();
        $param = $this->getRequest()->getParams();
        $this->view->id_hscv = (int)$param['id_hscv'];
        $year = QLVBDHCommon::getYear();
        $TraoDoiCongViecModel = new TraoDoiCongViecModel($year);        
        $this->view->data = $TraoDoiCongViecModel->getDetailInfoHSGiaoViec($this->view->id_hscv);
    }

    public function saveAction() {        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $config = Zend_Registry::get('config');  
        $year = QLVBDHCommon::getYear();
        
        $parameter = $this->getRequest()->getParams();
        $ID_HSCV = (int) $parameter["id_hscv"];
        
        $TraoDoiCongViecModel = new TraoDoiCongViecModel($year);
        
        if ((int)$ID_HSCV > 0) {            
            $DLCLIENTHONG = $TraoDoiCongViecModel->getMaSoDongLuanChuyenLt($ID_HSCV);
            if ((int)$DLCLIENTHONG > 0) {
                $wsdl = $config->service->lienthong->uri;
                $username = $config->service->lienthong->username;
                $password = $config->service->lienthong->password;
                $cliente = new SoapClient($wsdl);
                $session = $cliente->__call('Login', array(
                    'madonvi' => $username,
                    'password' => $password));
                $params = array(
                    'session' => $session,
                    'service_code' => 'TRAODOIGSCV',
                    'service_name' => 'CAPNHATTIENDO',
                    'parameter' => base64_encode($DLCLIENTHONG) . '~' . base64_encode($parameter["tiendo"]). '~' . base64_encode($parameter["motatiendo"])
                );
                $response = $cliente->__call('Execute', $params);                
            }
            $TraoDoiCongViecModel->getDefaultAdapter()->update(
                    QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"), 
                    array("TIENDO_GIAOVIEC" => $parameter["tiendo"],
						  'MOTATIENDO'=> $parameter["motatiendo"]							
					),'ID_HSCV = ' . $ID_HSCV);            
        }
        echo "<script>window.parent.document.frm.submit();</script>";
        exit;
    }
}
