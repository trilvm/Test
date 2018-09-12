<?php

/**
 * IndexController - The default controller class
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'traodoi/models/ThongTinModel.php';
require_once 'vbdi/models/vbdi_dongluanchuyenModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'hscv/models/PhoiHopModel.php';
require_once 'hscv/models/bosunghosoModel.php';
require_once 'mailclient/models/mail.php';
require_once('hscv/models/chuyennoiboModel.php');
require_once('lichcttext/models/lct.php');
require_once 'qlcuochop/models/quanlycuochopModel.php';
require_once 'default/models/kiemTraNhanVienModel.php';
require_once 'vbden/models/vbd_vanbandenModel.php';
require_once 'vbdi/models/BanHanhVanBanModel.php';
require_once 'Common/ServicesCommon.php';
class IndexController extends Zend_Controller_Action 
{
	/**
	 * The default action - show the home page
	 */
    public function indexAction() 
    {
        global $auth;
        global $db;
    	$user = $auth->getIdentity();
        $this->view->user=$user;
		
    	//var_export($user);
         if($user->ID_G==8) {
            $IS_LDP=1;
            $db= Zend_Db_Table::getDefaultAdapter();
            $sql="SELECT ID_U,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME  FROM qtht_users  us
                  INNER JOIN qtht_employees emp ON us.ID_EMP= emp.ID_EMP
                  WHERE emp.ID_DEP=? and ID_U <> $user->ID_U"; 
            $r= $db->query($sql,$user->ID_DEP);
            $arr_idu= $r->fetchAll();
            $model_check= new kiemTraNhanVienModel();
            $arr_nv= array();
            // foreach ($arr_idu as $value) {
                // $count=0;
                // $count= $model_check->SelectVbByID_U($value['ID_U']);
                // $arr_nv[]= array($value['FULLNAME'],$count);
                 
            // }
          
            $this->view->arr_nv= $arr_nv;
            $this->view->ldp=1;
        }
        if($user->ISLEADER == 1)
        {
            $sql="SELECT ID_D FROM fk_user_dep ud WHERE ID_U = ?";
            $r=$db->query($sql,$user->ID_U)->fetchAll();
            $this->view->dep_quanly=$r;
  
        }
       
		$id_u = $user->ID_U;
		$d = getdate();
		$realyear = QLVBDHCommon::getYear();
		$this->view->title="Trang chủ";
		//$this->view->subtitle="Truy cập nhanh";
		$this->view->user = $auth->getIdentity();
		$this->view->year = $realyear;
		
		//Lấy các activity của người dùng
		$this->view->activity = WFEngine::GetActivityFromLoaiCV(0,$this->view->user->ID_U);
		$this->view->hscv = new hosocongviecModel();
		$this->view->bosung = new bosunghosoModel();
		//Lấy thông tin trao đổi nội bộ		
		$thongtinModel=new ThongTinModel(QLVBDHCommon::getYear());			
		$dataInbox=$thongtinModel->CountInbox();		
		if(count($dataInbox)>0)
		{
			$this->view->inbox=$dataInbox["C"];
			$this->view->unread=$dataInbox["C"]-$dataInbox["UNREAD"];
		}
		$thongtinModel->_danhan=0;
		$thongtinModel->_id_u=$id_u;
                //thangtba thêm lưu chờ đề xuất xử lý
                require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
                $countDeXuat = Hscv_DexuatchoxulyModel::CountDeXuatChoXuLyByUser($id_u);
                $this->view->countDeXuat = $countDeXuat;
                //end thangtba
		$this->view->dataTraoDoi = $thongtinModel->SelectAllForInbox(0,3,"id_thongtin DESC,ngaygui DESC",1);		
		$this->view->cdb_vbdi = vbdi_dongluanchuyenModel::getVbdiChuaXemByIdUser($realyear,$user->ID_U);
		
		$this->view->cdb_vbdi = $db->query("
		SELECT vb.ID_VBDI, TRICHYEU, SOKYHIEU, NGAYBANHANH
		FROM ".QLVBDHCommon::Table("vbdi_vanbandi")." vb
		INNER JOIN ".QLVBDHCommon::Table("vbdi_dongluanchuyen")." lc on lc.ID_VBDI=vb.ID_VBDI
		WHERE
			lc.NGUOINHAN = ? AND lc.DA_XEM=0
		GROUP BY vb.ID_VBDI
		LIMIT 0,3
		",$user->ID_U)->fetchAll();
                
//                Tuanpp dem so ho so muon
                $this->view->countyeucaumuon = $db->query("
		SELECT COUNT(hscvdtm.`ID_TAPHOSO`) as countmuon,GROUP_CONCAT(hscvdt.`ID_TAPHOSO`) as listtaphosomuon
                FROM `hscvdt_muonhoso` hscvdtm
                INNER JOIN hscvdt_taphoso hscvdt on hscvdt.`ID_TAPHOSO`=hscvdtm.`ID_TAPHOSO`
                WHERE hscvdtm.`IS_DUYET`=0 and hscvdtm.`IS_YEUCAU`=0
		")->fetchAll();
//                end Tuanpp

//                Tuanpp dem so ho so dong
                $this->view->countyeucaudong = $db->query("
		SELECT COUNT(hscvdtm.`ID_TAPHOSO`) as countdong,GROUP_CONCAT(hscvdt.`ID_TAPHOSO`) as listtaphosodong
                FROM `hscvdt_muonhoso` hscvdtm
                INNER JOIN hscvdt_taphoso hscvdt on hscvdt.`ID_TAPHOSO`=hscvdtm.`ID_TAPHOSO`
                WHERE hscvdtm.`IS_DUYET`=0 and hscvdtm.`IS_YEUCAU`=1
		")->fetchAll();
//                end Tuanpp                
                
//                Tuanpp chi tiet ho so muon
                $this->view->chitiethsmuon = $db->query("
		SELECT hscvdtm.*,hscvdt.*
                FROM `hscvdt_muonhoso` hscvdtm
                INNER JOIN hscvdt_taphoso hscvdt on hscvdt.`ID_TAPHOSO`=hscvdtm.`ID_TAPHOSO`
                WHERE hscvdtm.`IS_DUYET`=0 and hscvdtm.`IS_YEUCAU`=0
                ORDER BY hscvdt.`ID_TAPHOSO` ASC
		")->fetchAll();
//                end Tuanpp
                
//                Tuanpp chi tiet ho so dong
                $this->view->chitiethsdong = $db->query("
		SELECT hscvdtm.*,hscvdt.*
                FROM `hscvdt_muonhoso` hscvdtm
                INNER JOIN hscvdt_taphoso hscvdt on hscvdt.`ID_TAPHOSO`=hscvdtm.`ID_TAPHOSO`
                WHERE hscvdtm.`IS_DUYET`=0 and hscvdtm.`IS_YEUCAU`=1
                ORDER BY hscvdt.`ID_TAPHOSO` ASC
		")->fetchAll();
//                end Tuanpp                
                
                
		$this->view->count_cdb_vbdi = $db->query("
		SELECT count(*) as C FROM (SELECT vb.ID_VBDI
		FROM ".QLVBDHCommon::Table("vbdi_vanbandi")." vb
		INNER JOIN ".QLVBDHCommon::Table("vbdi_dongluanchuyen")." lc on lc.ID_VBDI=vb.ID_VBDI
		WHERE
			lc.NGUOINHAN = ? AND lc.DA_XEM=0
		GROUP BY vb.ID_VBDI) t
		",$user->ID_U)->fetch();
		$this->view->count_cdb_vbdi = $this->view->count_cdb_vbdi["C"];

		$this->view->cdb_vbden = $db->query("
		SELECT vb.ID_VBD, TRICHYEU, SOKYHIEU, NGAYBANHANH
		FROM ".QLVBDHCommon::Table("vbd_vanbanden")." vb
		INNER JOIN ".QLVBDHCommon::Table("vbd_dongluanchuyen")." lc on lc.ID_VBD=vb.ID_VBD
		WHERE
			lc.NGUOINHAN = ? AND lc.DA_XEM=0
		GROUP BY vb.ID_VBD
		LIMIT 0,3
		",$user->ID_U)->fetchAll();
               
                ///////////////////////////////////////////
                $giaoviecservice = new GiaoViecService();
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $this->view->nhiemvu = json_decode($giaoviecservice->selectCongViec($token,1,0,'','','',$user->ID_U))->data;
		$this->view->count_nhiemvu = count($this->view->nhiemvu);
                
                $this->view->count_cdb_vbden = $db->query("
		SELECT count(*) as C FROM (SELECT vb.ID_VBD
		FROM ".QLVBDHCommon::Table("vbd_vanbanden")." vb
		INNER JOIN ".QLVBDHCommon::Table("vbd_dongluanchuyen")." lc on lc.ID_VBD=vb.ID_VBD
		WHERE
			lc.NGUOINHAN = ? AND lc.DA_XEM=0
		GROUP BY vb.ID_VBD) t
		",$user->ID_U)->fetch();
		$this->view->count_cdb_vbden = $this->view->count_cdb_vbden["C"];
		
		$this->view->dem_cdb_cnb = chuyennoiboModel::getListAlert();
		$this->view->phoihop_data = PhoiHopModel::getNewPhoiHopByUser($realyear,$user->ID_U);
		$db->update(QLVBDHCommon::Table("GEN_MESSAGE"),array("STATUS"=>1),"ID_U_RECEIVE = ".$user->ID_U);
		
		$lct = new lct();

		$this->view->lctcanhan = $lct->getPersonal(date("Y-m-d"),0,$user->ID_U);
		$this->view->lctphong = $lct->getDepartment(date("Y-m-d"),0,$user->ID_DEP,1);
		$this->view->lctcoquan = $lct->getCorporation(date("Y-m-d"),0,1);
		$this->view->ishomepage = 1;
                
		// Quản lý cuộc họp
		$this->view->id_u = $id_u;
       // $this->view->dataCH = quanlycuochopModel::getData($id_u);
	   // check van thu
	    $actid = ResourceUserModel::getActionByUrl("vbden","vbden","input");
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){ 
			$this->view->chobanhanh = BanHanhVanBanModel::countDuthao(QLVBDHCommon::GetYear(),"",0);
		}//else{ 
			//$parameter = array(
			//"ID_THUMUC"=>1,
			//"ID_U"=>$user->ID_U,
		//	"ID_G"=>$user->ID_G,
		//	"ID_DEP"=>$user->ID_DEP
		//	);
		//	$hscv = new hosocongviecModel();
		//	$this->view->chobanhanh = $hscv->Count_chobanhanh($parameter);
		//}
		$this->view->vbtn = self::CountVBCanTiepNhan();
		
		//phuongpt - Tình hình xử lý văn bản đến
		$mdlVanBanDen = new vbd_vanbandenModel();
		
		//trường hợp là lãnh đạo cơ quan
		if($user->ISLEADER == 1 && $user->DEPLEADER == 1)
		{
			//văn bản đến
			$this->view->vbddangxuly = $mdlVanBanDen->CountVBDangXulyBT(0, 0, 0, 0);	
			$this->view->vbddangtre = $mdlVanBanDen->CountVBDangXulyTH(0, 0, 0, 0);
			$this->view->vbddung = $mdlVanBanDen->CountVBDaXulyBT(0, 0, 0, 0);
			$this->view->vbdxongtre = $mdlVanBanDen->CountVBDaXulyTH(0, 0, 0, 0);
			
			//hồ sơ công việc
			$this->view->cvdangxuly = $this->view->hscv->CountHSCVDangXulyBT(0, 0, 0, 0);
			$this->view->cvdangtre = $this->view->hscv->CountHSCVDangXulyTH(0, 0, 0, 0);
			$this->view->cvdung = $this->view->hscv->CountHSCVDaXulyBT(0, 0, 0, 0);
			$this->view->cvxongtre = $this->view->hscv->CountHSCVDaXulyTH(0, 0, 0, 0);
		}
		//trường hợp là lãnh đạo văn phòng
		if($user->ISLEADER == 1 && $user->DEPLEADER == 0)
		{
			$this->view->vbddangxuly = $mdlVanBanDen->CountVBDangXulyBT(0, $user->ID_DEP, 0, 0);
			$this->view->vbddangtre = $mdlVanBanDen->CountVBDangXulyTH(0, $user->ID_DEP, 0, 0);
			$this->view->vbddung = $mdlVanBanDen->CountVBDaXulyBT(0, $user->ID_DEP, 0, 0);
			$this->view->vbdxongtre = $mdlVanBanDen->CountVBDaXulyTH(0, $user->ID_DEP, 0, 0);
			
			//hồ sơ công việc
			$this->view->cvdangxuly = $this->view->hscv->CountHSCVDangXulyBT(0, $user->ID_DEP, 0, 0);
			$this->view->cvdangtre = $this->view->hscv->CountHSCVDangXulyTH(0, $user->ID_DEP, 0, 0);
			$this->view->cvdung = $this->view->hscv->CountHSCVDaXulyBT(0, $user->ID_DEP, 0, 0);
			$this->view->cvxongtre = $this->view->hscv->CountHSCVDaXulyTH(0, $user->ID_DEP, 0, 0);
		}
		//trường hợp là người dùng
		if($user->ISLEADER == 0)
		{
			$this->view->vbddangxuly = $mdlVanBanDen->CountVBDangXulyBT($user->ID_U, 0, 0, 0);
			$this->view->vbddangtre = $mdlVanBanDen->CountVBDangXulyTH($user->ID_U, 0, 0, 0);
			$this->view->vbddung = $mdlVanBanDen->CountVBDaXulyBT($user->ID_U, 0, 0, 0);
			$this->view->vbdxongtre = $mdlVanBanDen->CountVBDaXulyTH($user->ID_U, 0, 0, 0);
			
			//hồ sơ công việc
			$this->view->cvdangxuly = $this->view->hscv->CountHSCVDangXulyBT($user->ID_U, 0, 0, 0);
			$this->view->cvdangtre = $this->view->hscv->CountHSCVDangXulyTH($user->ID_U, 0, 0, 0);
			$this->view->cvdung = $this->view->hscv->CountHSCVDaXulyBT($user->ID_U, 0, 0, 0);
			$this->view->cvxongtre = $this->view->hscv->CountHSCVDaXulyTH($user->ID_U, 0, 0, 0);
		}
                self::CapNhatVanBanGiaHan();
	}

    public static function CountVBCanTiepNhan(){
        $config = Zend_Registry::get('config');
        global $auth;        
    	$user = $auth->getIdentity();
		$code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);		
		
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));

			$param = array(
				'session'  =>  $session,
				'service_code' => 'VANBAN',
				'service_name' => 'VANBANCANTIEPNHAN',
				'parameter' => base64_encode(0) . '~' . base64_encode(999). '~' . base64_encode($code)
			);
			$response=$cliente->__call('Execute', $param);
			$data = array_reverse(ServicesCommon::DeseriallizeToArray(base64_decode($response)));
			$cntData = count($data);
			return $cntData;        
		}catch (Exception $ex){
            
        }
    }
    public static function CapNhatVanBanGiaHan(){
        $config = Zend_Registry::get('config');
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
            $param = array(
                    'session'  =>  $session,
                    'service_code' => 'TRAODOIGSCV',
                    'service_name' => 'GETCAPNHATVANBANGIAHAN',
                    'parameter' => ''
            );
            $dataResult=base64_decode($cliente->__call('Execute', $param)); 
            
            if($dataResult!=0){
                require_once 'Common/ServicesCommon.php';                
                $dataResultArray = ServicesCommon::DeseriallizeToArray($dataResult);
                $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                foreach($dataResultArray as $item){
                    $arrayUpdate =array(
                        "HANXULYTOANBO" => $item['HANXULY'],
                        'NGAYHETHAN'=>$item['NGAYKETTHUC']
                    );
                    $update = $dbAdapter->update(QLVBDHCommon::table('vbd_vanbanden'),$arrayUpdate,"DLCLIENTHONG = '".$item['ID_DLC']."'");
                    if($update){
                        $param = array(
                                'session'  =>  $session,
                                'service_code' => 'TRAODOIGSCV',
                                'service_name' => 'CAPNHATVANBANGIAHAN',
                                'parameter' => $item['ID_DLC']
                        );
                        $dataResult=base64_decode($cliente->__call('Execute', $param)); 
                    }  
                }
            }            
        }catch (Exception $ex){            
        }
    }
}
