<?php

/**
 * vbdenController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'vbden/models/vbdendraftModel.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'config/vbden.php';
// Dùng bên listAction
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'vbden/models/fk_vbden_hscvsModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once('vbden/models/vbdenModel.php');
require_once 'vbmail/models/vbmail_vanbannhanModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/HosoluutheodoiModel.php';
include_once 'vbdi/models/VanBanDiModel.php';
include_once 'qtht/models/nguoidungModel.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once('hscv/models/chuyennoiboModel.php');
require_once 'hscv/models/gen_tempModel.php';
require_once 'giaoviec/models/LoaiCongViecGiaoViecModel.php';
require_once "giaoviec/models/giaoviecservice.php";
class vbden_vbdenController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
			
	}
	/**
	 * The input action - show edit or new page
	 */
	public function inputAction(){
        
	   $this->view->datalvvb = array();
		QLVBDHCommon::GetTree(&$this->view->datalvvb,"vb_linhvucvanban","ID_LVVB","ACTIVE=1 AND ID_LVVB_PARENT",1,1);
		global $auth;
		global $db;
	    // Lấy parameter
        $parameter = $this->getRequest()->getParams();        
        $id_hscv = $parameter["id_hscv"];
        $config = Zend_Registry::get('config');
        $user = $auth->getIdentity();
		$this->view->user = $user;
        // Set Year
        $year = QLVBDHCommon::getYear();

        // New các model
        $this->vbden = new vbdenModel($year);   
        $svb = new SoVanBanModel();
        $this->view->svb = $svb->fetchAll();   

        // Lấy dữ liệu
        if($id_hscv>0){
            
            $this->view->data = $this->vbden->findByHscv($id_hscv);
           	$this->view->title = MSG11001001;
        }else{
            /**
             * Thangtba thêm chức năng tiếp nhận văn bản liên thông
             */
             $masovanban = $parameter['masovanban'];
             if ((int)$parameter['id_object'] > 0) $this->view->id_object = $parameter['id_object'];
             if ($masovanban != '') {
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $giaoviecservice = new GiaoViecService();
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $reCongViec = $giaoviecservice->SelectCongViecByIdlt(
                        $token
                        ,$masovanban
                );
                $this->view->datacongviec = json_decode($reCongViec);
                 $this->view->islienthong = 1;
                 global $config;
                 try {
                    $client = new SoapClient($config->service->lienthong->uri);
                    $session = $client->__call('Login', array(
                            'madonvi' => $config->service->lienthong->username,
                            'password' => $config->service->lienthong->password));
                    /**
                     * Phần lấy nội dung
                     */
                    global $auth;        
                    $user = $auth->getIdentity();
                    $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
                    $params = array(
                        'session' => $session,
                        'service_code' => 'VANBAN',
                        'service_name' => 'TIEPNHANVANBAN',
                        'parameter' => base64_encode($masovanban).'~'.base64_encode($config->service->lienthong->username).'~' . base64_encode($code)
                    );
                    $response = $client->__call('Execute', $params);
                    $data_arr = ServicesCommon::DeseriallizeToArray(base64_decode($response));
                    //var_dump($data_arr);
                    $data->TRICHYEU = $data_arr[0]["TRICHYEU"];
                    $data->NGAYBANHANH = $data_arr[0]["NGAYBANHANH"];
                    $data->COQUANBANHANH_TEXT = $data_arr[0]["NOIBANHANH"];
                    $data->SOKYHIEU = $data_arr[0]["SOKYHIEU"];
                    $data->ID_LVB_TEXT = $data_arr[0]["ID_LOAIVANBAN"];
                    $data->MASOLIENTHONG = $data_arr[0]["MASOVANBAN"];
					$data->NGUOIKY =  $data_arr[0]["NGUOIBANHANH"];
                    $data->DLCLIENTHONG = $data_arr[0]["ID_DLC"];
                    $data->NGAYCHUYENLIENTHONG = $data_arr[0]["NGAYGOI"];
                    $data->IS_GIAOVIEC = $data_arr[0]["IS_GV"];
					$thongTinPhu =  $data_arr[0]["THONGTINKHAC"];
                    // Thêm phần quản lý giao việc
                    //$data->CAP_GV =  $data_arr[0]["CAP_GV"];
                    $data->HANXULYTOANBO = $data_arr[0]["HANXULY"];
                    $data->TYPEHANXULY = $data_arr[0]["TYPEHANXULY"];
                    $data->NHIEMVU = $data_arr[0]["NHIEMVU"];
					$data->NGAYHETHAN =$data_arr[0]["NGAYKETTHUC"];
					$data->HANXULYLIENTHONG = $data_arr[0]["HANXULY"];
                                        $data->ID_VBLTCP = $data_arr[0]["ID_VBLTCP"];
                    // $data->NGAYBATDAU_GV =  $data_arr[0]["NGAYBATDAU_GV"];
                    // $data->LOAICV_GV =  $data_arr[0]["LOAICV_GV"];
                    
                    $arrThongTinPhu = ServicesCommon::DeseriallizeToArray(base64_decode($thongTinPhu));
					$data->SOBAN    = base64_decode($arrThongTinPhu[0]["SOBAN"]);
					$data->SOTO     = base64_decode($arrThongTinPhu[0]["SOTO"]);
					$data->DOMAT    = base64_decode($arrThongTinPhu[0]["DOMAT"]);
					$data->DOKHAN   = base64_decode($arrThongTinPhu[0]["DOKHAN"]);
					$data->ID_LVVB  = base64_decode($arrThongTinPhu[0]["LINHVUC"]);
                    
                    /**
                     * END:Phần lấy nội dung
                     */
                } catch (Exception $ex) {
                    $session = null;
                }
             }
            /**
             * End: Thangtba
             */
             else
                $this->view->title = MSG11001001;

        }
      
        //nhan van ban qua mail
		$id_vbnhan = $parameter["ID_VBNHAN"];
		$data ;
		if($id_vbnhan > 0){
			$files_vbnhan = filedinhkemModel::getListFileByIdObject($id_vbnhan,13);
			$this->view->count_files_vbnhan = count($files_vbnhan);
			$this->view->id_vbnhan = $id_vbnhan;
			$array_vbnhan = vbmail_vanbannhanModel::getById($id_vbnhan);
			
			$data->TRICHYEU = $array_vbnhan["TRICHYEU"];
			$data->NGAYBANHANH = $array_vbnhan["NGAYBANHANH"];
			$data->NGUOIKY = $array_vbnhan["NGUOIKY"];
			$data->COQUANBANHANH_TEXT = $array_vbnhan["COQUANBANHANH"];
			$data->SOKYHIEU = $array_vbnhan["SOKYHIEU"];
			//var_dump($files_vbnhan);
			//var_dump($array_vbnhan);
			
			//$this->view->
			
		}
		$id_vbnhannoibo = $parameter["id_vbnhannoibo"];
		if($id_vbnhannoibo > 0){
			$files_vbnhannobo = filedinhkemModel::getListFileByIdObject($id_vbnhannoibo,15);
			$this->view->files_vbnhannobo = $files_vbnhannobo;
			$this->view->count_files_vbnhannobo = count($files_vbnhannobo);
			$this->view->id_vbnhannoibo= $id_vbnhannoibo;
			$array_vbnhan = chuyennoiboModel::getInfovbnhannoibo($id_vbnhannoibo);
                        $array_vbden = vbdenModel::getdetailVBD($array_vbnhan['ID_VBD']);
                        if($array_vbden['MASOLIENTHONG']!=NULL || $array_vbden['MASOLIENTHONG']!=0){
                        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                        $madonvi = $configlienthong->service->lienthong->username;
                        $password = $configlienthong->service->lienthong->password;
                        $giaoviecservice = new GiaoViecService();
                        $token = $giaoviecservice->login($madonvi,md5($password),"");
                        $reCongViec = $giaoviecservice->SelectCongViecByIdlt(
                                $token
                                ,$array_vbden['MASOLIENTHONG']
                        );
                        }
                        $this->view->datacongviec = json_decode($reCongViec);
			$data->TRICHYEU = $array_vbnhan["TRICHYEU"];
			$data->NGAYBANHANH = $array_vbnhan["NGAYBANHANH"];
			$data->NGUOIKY = $array_vbnhan["NGUOIKY"];
			$data->COQUANBANHANH_TEXT = $array_vbnhan["COQUANBANHANH_TEXT"];
			$data->SOKYHIEU = $array_vbnhan["SOKYHIEU"];
			$data->ID_LVB = $array_vbnhan["ID_LVB"];
			$data->SOKYHIEU = $array_vbnhan["SOKYHIEU"];
			$data->ID_CQ = $array_vbnhan["ID_CQ"];
			$data->ID_LVVB = $array_vbnhan["ID_LVVB"];
			$data->ID_VBDNB = $array_vbnhan["ID_VBDNB"];
                        $data->MASOLIENTHONG = $array_vbden['MASOLIENTHONG'];
		}

		// Set biến cho view
        $data->NGAYDEN = date("Y-m-d");
		$this->view->data = $data;
		$this->view->year = $year;
    
        QLVBDHButton::EnableSave("/vbden/vbden/Save");
        QLVBDHButton::EnableBack("/vbden/vbden");
                
        global $auth;
		$user = $auth->getIdentity();
            	
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = WFEngine::GetBeginTransition('VBD',$user->ID_DEP);//$this->parameter["wf_id_t"];
		$next_trs = WFEngine::GetNextTransitionsByTransition($this->view->wf_id_t);
		$this->view->hanxuly = WFEngine::GetHanXuLy($next_trs["ID_T"]);
		$this->view->id_loaihscv = WFEngine::GetIdLoaiHSCVFromIdT($this->view->wf_id_t);
		//echo $this->view->id_loaihscv;
		$this->view->config = $config;
		if($parameter['idvbdi']>0){
			$vbdi = new VanBanDiModel(QLVBDHCommon::getYear());
			$this->view->data = $vbdi->find($parameter['idvbdi'])->current();
			$data->TRICHYEU = $this->view->data->TRICHYEU;
			$data->NGAYBANHANH = $this->view->data->NGAYBANHANH;
			$uk = new nguoidungModel();
			$uk = $uk->FindById($this->view->data->NGUOIKY);
			$data->NGUOIKY = $uk["FIRSTNAME"]." ".$uk["LASTNAME"];
			$data->ID_CQ = $this->view->data->ID_CQ;
			$data->SOKYHIEU = $this->view->data->SOKYHIEU;
			$data->ID_LVB = $this->view->data->ID_LVB;
			$this->view->ID_VBDI = $parameter['idvbdi'];
			$this->view->data = $data;
		}
		if($parameter['idvbdraft']>0){
			$datadraft = vbdendraftModel::SelectOne($parameter['idvbdraft']);
			$data->ID_SVB = $datadraft["ID_SVB"];
			$data->ID_LVVB = $datadraft["ID_LVVB"];
			$data->ID_CQ = $datadraft["ID_CQ"];
			$data->ID_LVB = $datadraft["ID_LVB"];
			$data->SOKYHIEU = $datadraft["SOKYHIEU"];
			$data->SODEN = $datadraft["SODEN"];
			$data->NGAYDEN = implode("-",array_reverse(explode("/",$datadraft["NGAYDEN"])));
			$data->NGAYBANHANH = implode("-",array_reverse(explode("/",$datadraft["NGAYBANHANH"])));
			$data->NGAYTAO = $datadraft["NGAYTAO"];
			$data->COQUANBANHANH_TEXT = $datadraft["COQUANBANHANH_TEXT"];
			$data->COQUANNHAN = $datadraft["COQUANNHAN"];
			$data->TRICHYEU = $datadraft["TRICHYEU"];
			$data->SOBAN = $datadraft["SOBAN"];
			$data->SOTO = $datadraft["SOTO"];
			$data->DOMAT = $datadraft["DOMAT"];
			$data->DOKHAN = $datadraft["DOKHAN"];
			$data->NGUOIKY = $datadraft["NGUOIKY"];
			$data->SODEN_IN = $datadraft["SODEN_IN"];
			$data->SOKYHIEU_IN = $datadraft["SOKYHIEU_IN"];
			$this->view->data = $data;
			$this->view->ID_VBD_DRAFT = $parameter['idvbdraft'];
		}
		if($parameter['draft']==1){
            if($parameter['idvbdraft'] > 0){
                $db = Zend_Db_Table::getDefaultAdapter();
                $select = $db->select();
                $select->from(QLVBDHCommon::Table("vbd_vanbanden_draft"));
                $select->where("ID_VBD = ".$parameter['idvbdraft']);
                $data = $db->fetchAll($select);
                $this->view->data = (object)$data[0];
                $this->view->idvbdraft = $parameter['idvbdraft'] ;
				$this->view->id_object = $parameter['idvbdraft'];
            }
			$this->view->title = "TIẾP NHẬN VĂN BẢN ĐẾN";
			$this->renderScript("vbden/inputdraft.phtml");
			$this->view->draft = 1;
		}
	}
    /**
     * The save action - insert if item is new or update if if item is exist
     */
    public function saveAction(){
		$config = Zend_Registry::get('config');
		$result = 0;
		$this->view->parameter =  $this->getRequest()->getParams();
		$parameter = $this->getRequest()->getParams();
		
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
                
		$db = Zend_Db_Table::getDefaultAdapter();
		if($parameter['draft']==1){            
			$this->saveDraft($parameter);
			echo 1;
			exit;
		}else{
			if($this->view->parameter["COQUANNHAN"]==NULL){
				$cqid=vbdenModel::GetDataCQN_ID();
			}else{
				$cqid=$this->view->parameter["COQUANNHAN"];
			}            
			$year = QLVBDHCommon::getYear();
			$is_kcxl = $parameter["is_kcxl"];
			if(!$parameter["is_kcxl"])
				$is_kcxl = 0;
			
			$this->vbden = new vbdenModel($year);
			$this->filedinhkem = new filedinhkemModel($year);			
			Zend_Date::setOptions(array('format_type' => 'php'));

			/**
			 * trunglv tao doi tuong de lay ma so van ban den
			 */
			$MASOVANBAN = 'MSVB';
            $MASOVANBAN = $this->getMaSoVanBan($parameter); 
			
			//kiem tra so den
			$arr_param_soden = array();
			$arr_param_soden['ID_SVB'] = $this->view->parameter["ID_SVB"];
			$arr_param_soden['ID_LVB'] = $this->view->parameter["ID_LVB"];
			$arr_param_soden["SOKYHIEU"] = $this->view->parameter["SOKYHIEU"];
			$arr_param_soden["ID_CQ"] = $this->view->parameter["ID_CQ"];
			$arr_param_soden["NGAYBANHANH"] = implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"])));
			
			$sql_getsoden = vbdenModel::checkAllDataSQL($year,$arr_param_soden);
			
			$soden_re = $this->vbden->getDefaultAdapter()->getConnection()->query($sql_getsoden);
			$cur_soden_row = $soden_re->fetch();
			$cur_soden = $cur_soden_row['DEM'];
			if($cur_soden >=1 ){
				$this->vbden->getDefaultAdapter()->closeConnection();
				echo 2;
				exit;
			}
			/**
			 * end trunglv
			 */
			if($parameter["id_vbnhan"]){
				vbmail_vanbannhanModel::deleteVbNhan($parameter["id_vbnhan"]);
			}
			if($parameter["NGAYHETHAN"]){				
				$ngayhethan = implode("-", array_reverse(explode("/", $parameter["NGAYHETHAN"])));				
			}else{
				$ngayhethan = date("Y-m-d", QLVBDHCommon::addDate(time(), (int) $parameter["HANXULYTOANBO"]));
			}         
			if($this->view->parameter["id_hscv"]>0){
                
				$this->vbden->getDefaultAdapter()->beginTransaction();
				try{
					$objhscv = new hosocongviecModel();
					$hscv = $this->vbden->findHscv($this->view->parameter["id_hscv"]);
					//Xoa hscv
					$this->vbden->getDefaultAdapter()->delete("HSCV_HOSOCONGVIEC_".QLVBDHCommon::getYear(),"ID_HSCV=".((int)$this->view->parameter["id_hscv"]));
					//Xoa process
					$this->vbden->getDefaultAdapter()->delete("WF_PROCESSITEMS_".QLVBDHCommon::getYear(),"ID_PI=".$hscv['ID_PI']);
					//Xoa log
					$this->vbden->getDefaultAdapter()->delete("WF_PROCESSLOGS_".QLVBDHCommon::getYear(),"ID_PI=".$hscv['ID_PI']);
					//tạo hscv
					$id_hscv = $objhscv->CreateHSCV(
                            $this->view->parameter["TRICHYEU"],
							1,
							1,
							implode("-", array_reverse(explode("/", $this->view->parameter["NGAYDEN"]))), 
							implode("-", array_reverse(explode("/", $this->view->parameter["NGAYDEN"]))),
							Zend_Registry::get('auth')->getIdentity()->ID_U, 
							$this->view->parameter["wf_nextuser"], 
							$this->view->parameter["wf_name_user"], 
							$this->view->parameter["wf_hanxuly_user"],
							0, 
							$this->view->parameter["wf_sms"], 
							$this->view->parameter["wf_email"], 
							$this->view->parameter["wf_nextuser"]
					);
					if($id_hscv>0){ 
						//cập nhật vbden
						$this->vbden->update(array(
							"NGAYHETHAN"=>$ngayhethan,
                            "HANXULYTOANBO" =>$this->view->parameter["HANXULYTOANBO"],
							"TYPEHANXULY" => (int)$this->view->parameter["HANXULYNVTYPE_GIAOVIEC"],
                            "NHIEMVU" => $this->view->parameter["NHIEMVU"],
							"ID_SVB"=>(int)$this->view->parameter["ID_SVB"],
							"NGUOITAO"=>Zend_Registry::get('auth')->getIdentity()->ID_U,
							"ID_LVVB"=>(int)$this->view->parameter["ID_LVVB"],
							"ID_HSCV"=>$id_hscv,
							"ID_CQ"=>(int)$this->view->parameter["ID_CQ"],
							"ID_LVB"=>(int)$this->view->parameter["ID_LVB"],
							"MASOVANBAN"=>$MASOVANBAN,"SOKYHIEU"=>$this->view->parameter["SOKYHIEU"],
							"GHICHU"=>$this->view->parameter["GHICHU"],
							"SODEN"=>(int)$this->view->parameter["SODEN"],
							"NGAYDEN"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYDEN"]))),
							"NGAYBANHANH"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"]))),
							"NGAYTAO"=>Date("Y-m-d"),
							"COQUANBANHANH_TEXT"=>QLVBDHCommon::makeCQName($this->view->parameter["COQUANBANHANH_TEXT"]),
							"TRICHYEU"=>$this->view->parameter["TRICHYEU"],
							"SOBAN"=>(int)$this->view->parameter["SOBAN"],
							"SOTO"=>(int)$this->view->parameter["SOTO"],
							"DOMAT"=>(int)$this->view->parameter["DOMAT"],
							"DOKHAN"=>(int)$this->view->parameter["DOKHAN"],
							"NGUOIKY"=>$this->view->parameter["NGUOIKY"]),
							"ID_VBD=".$this->view->parameter["ID_VBD"]);
						for($i=0;$i<count($this->view->parameter["idFile"]);$i++){   
							$this->filedinhkem->update(array("ID_OBJECT"=>$id_hscv,"TYPE"=>1),"MASO='".$this->view->parameter["idFile"][$i]."'");
						}
						$this->vbden->getDefaultAdapter()->commit();
					}else{
						$this->vbden->getDefaultAdapter()->rollBack();
					}
					$this->vbden->getDefaultAdapter()->closeConnection();
				}catch(Exception $ex){
					$this->vbden->getDefaultAdapter()->closeConnection();
					echo 0; 					
					exit;
					$this->vbden->getDefaultAdapter()->rollBack();
					
				}
				$this->vbden->getDefaultAdapter()->closeConnection();
			}
			else{
                                $is_congviec = 0;
				//
				$hscv = new hosocongviecModel();
				$fkvbd = new fk_vbden_hscvsModel(QLVBDHCommon::getYear());
				// begin transaction
				$this->vbden->getDefaultAdapter()->beginTransaction();
				$masovanbanlt = $parameter['MASOLIENTHONG'];
                                if($masovanbanlt != '' || $masovanbanlt != NULL )
                                {
                                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                                $madonvi = $configlienthong->service->lienthong->username;
                                $password = $configlienthong->service->lienthong->password;
                                $giaoviecservice = new GiaoViecService();
                                $token = $giaoviecservice->login($madonvi,md5($password),"");
                                $reCongViec = $giaoviecservice->SelectCongViecByIdlt(
                                        $token
                                        ,$masovanbanlt
                                );
                                $this->view->datacongviec = json_decode($reCongViec);
                                if(count($this->view->datacongviec->data) >0 )
                                    {
                                    $is_congviec = 1;   
                                    }
                                }
				$idvbd = $this->vbden->insert(array(
                //tuanpp cập nhật ngayhethan trong vanbanden , luu de su dung sau
                "NGAYHETHAN"=>$ngayhethan,
                    "HANXULYTOANBO" =>  $this->view->parameter["HANXULYTOANBO"] != "" ? $this->view->parameter["HANXULYTOANBO"] : null,
					"TYPEHANXULY" => (int)$this->view->parameter["HANXULYNVTYPE_GIAOVIEC"],
                    "NHIEMVU" => $this->view->parameter["NHIEMVU"],
					"HANXULYLIENTHONG" => $this->view->parameter["HANXULYLIENTHONG"],
                //tuanpp end
				"ID_SVB"=>(int)$this->view->parameter["ID_SVB"],
				"NGUOITAO"=>Zend_Registry::get('auth')->getIdentity()->ID_U,
				"ID_LVVB"=>(int)$this->view->parameter["ID_LVVB"],
				"ID_CQ"=>(int)$this->view->parameter["ID_CQ"],
				"ID_LVB"=>(int)$this->view->parameter["ID_LVB"],
				"MASOVANBAN"=>$MASOVANBAN,
				"SOKYHIEU"=>$this->view->parameter["SOKYHIEU"],
				"GHICHU"=>$this->view->parameter["GHICHU"],
				"SODEN"=>$this->view->parameter["SODEN"],
				"NGAYDEN"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYDEN"]))),
				"NGAYBANHANH"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"]))),
				"NGAYTAO"=>Date("Y-m-d"),
				"COQUANBANHANH_TEXT"=>QLVBDHCommon::makeCQName($this->view->parameter["COQUANBANHANH_TEXT"]),        	
				"COQUANNHAN"=>$cqid,
				"TRICHYEU"=>$this->view->parameter["TRICHYEU"],
				"SOBAN"=>(int)$this->view->parameter["SOBAN"],
				"SOTO"=>(int)$this->view->parameter["SOTO"],
				"DOMAT"=>(int)$this->view->parameter["DOMAT"],
				"DOKHAN"=>(int)$this->view->parameter["DOKHAN"],
				"NGUOIKY"=>$this->view->parameter["NGUOIKY"],
				"SODEN_IN"=>(int)$this->view->parameter["SODEN_IN"],
				"SOKYHIEU_IN"=>(int)$this->view->parameter["SOKYHIEU_IN"],
				"IS_KHONGXULY" => (int)$is_kcxl, 
				"IS_PHOBIEN" => $this->view->parameter["IS_PHOBIEN"],
				"IS_LIENTHONG" => $this->view->parameter["islienthong"]>0?$this->view->parameter["islienthong"]:null,
                    "MASOLIENTHONG" => $this->view->parameter["MASOLIENTHONG"] != "" ? $this->view->parameter["MASOLIENTHONG"] : null,
                    "DLCLIENTHONG" => $this->view->parameter["DLCLIENTHONG"] != "" ? $this->view->parameter["DLCLIENTHONG"] : null,
                    "IS_GIAOVIEC" => (int) $this->view->parameter["IS_GIAOVIEC"],
                    "NGAYCHUYENLIENTHONG" => $this->view->parameter["NGAYCHUYENLIENTHONG"] != "" ? $this->view->parameter["NGAYCHUYENLIENTHONG"] : null,
                                "IS_CONGVIEC"  =>  $is_congviec,
                                "ID_VBLTCP"  =>  $this->view->parameter["ID_VBLTCP"]
				));
				vbdendraftModel::DeleteOne($parameter['ID_VBD_DRAFT']); 

				// insert file đính kèm
				for($i=0;$i<count($this->view->parameter["idFile"]);$i++){   
					$this->filedinhkem->update(array("ID_OBJECT"=>$idvbd,"TYPE"=>3),"MASO='".$this->view->parameter["idFile"][$i]."'");
				}
				
				if($idvbd > 0){
                                    $this->updateTrangThaiLienThong($config,(int)$this->view->parameter["MASOLIENTHONG"]);
                                    if((int)$this->view->parameter["ID_VBLTCP"] > 0)
                                    {
                                        global $auth;        
                                        $user = $auth->getIdentity();
                                        $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
                                        $client = new SoapClient($config->service->lienthong->uri);
                                        $sessionlt = $client->__call('Login', array(
                                            'madonvi' => $code,
                                            'password' => $config->service->lienthong->password));
                                        $dataltvpcp = array(
                                                'session'=>$sessionlt,
                                                'ID_VBLTCP' => $this->view->parameter["ID_VBLTCP"],
                                                'Timestamp' => date('Y-m-d H:i:s'),
                                                'status' => '03',
                                                'Description'=> 'Văn bản đã tiếp nhận',
                                                'Staff' => $user->FULLNAME,
                                                'Department' => DepartmentsModel::getNameById($user->ID_DEP)
                                              );
                                        $params = array(
                                            'session' => $sessionlt,
                                            'service_code' => 'VPCP',
                                            'service_name' => 'QLVBDHCapNhatTrangThaiVPCP',
                                            'parameter' => base64_encode(json_encode($dataltvpcp))
                                        );
                                        $idtt = $client->__call('Execute', $params); 
                                    }    
					//neu la van ban nhan noi bo, xoa van ban nhan noi bo
					$idvbdnb = $this->view->parameter["ID_VBDNB"];
					if($idvbdnb){
						
						try{
							//luu noi dung but phe
							$ifovbnoi = chuyennoiboModel::getInfovbnhannoibo($idvbdnb);
							if($ifovbnoi["NOIDUNG_BUTPHE"])
							{
								$db->insert(QLVBDHCommon::Table("hscv_butphe"),array(
									"NOIDUNG"=> $ifovbnoi["NOIDUNG_BUTPHE"],
									"NGUOIKY"=> $ifovbnoi["NGUOIBUTPHE"],
									"ID_VBD"=> $idvbd,
									"NGAYBP"=>$ifovbnoi["NGAYBUTPHE"]
								));
							}

							$db->delete("vbd_nhannoibo","ID_VBDNB = $idvbdnb");
							$this->filedinhkem->delete(array("ID_OBJECT=".$idvbdnb,"TYPE=15"));
						}catch(Exception $ex){
							//var_dump($ex);
						}
					}

					//luu van ban chuyen noi bo
					
					$actid = ResourceUserModel::getActionByUrl('hscv','chuyennoibo','chuyennoiboinput');
					if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){							
							$id_cqs = $this->view->parameter["ID_CQNGOAI"];
							chuyennoiboModel::luuchuyennoibo($idvbd,$user->ID_U,$id_cqs,$noidung);
					}
				}

				//insert HSCV
				if($is_kcxl == 0){
					$actid = ResourceUserModel::getActionByUrl('hscv','hscv','alowbpd');
					if($config->hscv->vtbp==1 && ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
						$arr_id_u_bp_old = $this->view->parameter["ID_U_BP"];
						$arr_noidung_bp_old = $this->view->parameter["NOIDUNG_BP"];
						$arr_ngay_bp_old = $this->view->parameter["NGAYBUTPHE"];
						
						$arr_id_u_bp= array();
						$arr_noidung_bp= array();
						$arr_ngay_bp= array();

						if(is_array($arr_id_u_bp_old)){
							// làm mới lại danh sách but phê vì có khi hắn trống
							for($loopbp=0;$loopbp<20;$loopbp++){
								if($arr_id_u_bp_old[$loopbp]>0){
									$arr_id_u_bp[] = $arr_id_u_bp_old[$loopbp];
									$arr_noidung_bp[] = $arr_noidung_bp_old[$loopbp];
									$arr_ngay_bp[] = $arr_ngay_bp_old[$loopbp];
								}
							}
						}
						if(is_array($arr_id_u_bp_old)){
							$id_hscv = $hscv->CreateHSCV(
                                    $this->view->parameter["TRICHYEU"],
									1, 
									$this->view->parameter["id_loaihscv"], 
									implode("-", array_reverse(explode("/",
									$this->view->parameter["NGAYDEN"]))), 
									implode("-", array_reverse(explode("/", $this->view->parameter["NGAYDEN"]))),
									Zend_Registry::get('auth')->getIdentity()->ID_U, 
									$arr_id_u_bp[0],
									$this->view->parameter[""],
									0
                            );
                        } else {
                            $id_hscv = $hscv->CreateHSCV(
                                    $this->view->parameter["TRICHYEU"],
									1,
									$this->view->parameter["id_loaihscv"],
									implode("-", array_reverse(explode("/", $this->view->parameter["NGAYDEN"]))), 
									implode("-", array_reverse(explode("/", $this->view->parameter["NGAYDEN"]))),
									Zend_Registry::get('auth')->getIdentity()->ID_U,
									$this->view->parameter["ID_U_BP"], 
									$this->view->parameter[""], 
									0
                            );
						}
						//echo $id_hscv;exit;
						if($id_hscv>0){
							$fkvbd->insert(array("ID_VBDEN"=>$idvbd,"ID_HSCV"=>$id_hscv));
						}else{
						}
						// Chuyen but phe nhieu lan trừ dùng cuối cùng
						if(is_array($arr_id_u_bp_old)){
							$vbd = new vbdenModel(QLVBDHCommon::getYear());
							$vbden = $vbd->findByHscv($id_hscv);
							for($loopbp=0;$loopbp<count($arr_id_u_bp)-1;$loopbp++){
								WFEngine::SendNextUserByObjectId($id_hscv
									,$this->view->parameter["wf_id_t_butphe"]
									,$arr_id_u_bp[$loopbp]
									,$arr_id_u_bp[$loopbp+1]
									,$arr_noidung_bp[$loopbp]
									,0);
								// Lưu bút phê
								//insert vao butphe
								if($arr_noidung_bp[$loopbp]!=""){
									$vbd->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_BUTPHE"),array(
										"NOIDUNG"=>$arr_noidung_bp[$loopbp],
										"NGUOIKY"=>$arr_id_u_bp[$loopbp],
										"NGUOICHUYEN"=>$user->ID_U,
										"ID_VBD"=>$vbden['ID_VBDEN'],
										"ID_HSCV"=>$id_hscv,
										"NGAYBP"=>implode("-", array_reverse( explode("/",$arr_ngay_bp[$loopbp])))
									));
								}
							}
						}
						
						//Trả lại như cũ để xử lý dòng cuối cùng
						if(is_array($arr_id_u_bp_old)){
							$parameter["NOIDUNG_BP"] = $arr_noidung_bp[count($arr_noidung_bp)-1];
							$parameter["ID_U_BP"] = $arr_id_u_bp[count($arr_noidung_bp)-1];
						}

						//if(trim($parameter["NOIDUNG_BP"])!="" || count($parameter["ID_U_XL"])>0){
						//chuyen xu ly
						
						
							$idu = $parameter["ID_U_XL"];
							$sms = $parameter["SMS"];
							$email = $parameter["EMAIL"];
                                                        $arrnv = $parameter["checknv"];
							//var_dump($email);exit;
							$hanxuly = $parameter["HANXULY"];
							$type = $parameter["TYPE_XL"];
							$id = $id_hscv;
							$wf_id_t = $parameter["wf_id_t"];
							$noidung = $parameter["NOIDUNG_BP"];
							$istheodoi = $parameter["istheodoi"];
							
							$vbd = new vbdenModel(QLVBDHCommon::getYear());
							$vbden = $vbd->findByHscv($id_hscv);
							//insert vao butphe
							if($noidung!=""){
								$vbd->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_BUTPHE"),array(
									"NOIDUNG"=>$noidung,
									"NGUOIKY"=>$parameter["ID_U_BP"],
									"NGUOICHUYEN"=>$user->ID_U,
									"ID_VBD"=>$vbden['ID_VBDEN'],
									"ID_HSCV"=>$id_hscv,
									"NGAYBP"=>date("Y-m-d H:i:s")
								));
							}
							$idusend = array();
							$sendsms = array();
							$sendemail = array();
							$hanxulysend = array();
							$noidungsend = array();
							$idudb = array();
							$iduph = array();
							$dbsms = array();
							$dbemail = array();
							for($i=0;$i<count($idu);$i++){
								if($type[$i]==2){
									$idusend[] = $idu[$i];
									$sendsms[] = $sms[$i];
									$sendemail[] = $email[$i];
									$hanxulysend[] = $hanxuly[$i];
									$noidungsend[] = $noidung;
								}else if($type[$i]==1){
									$idudb[] = $idu[$i];
									$dbsms[] = $sms[$i];
									$dbemail[] = $email[$i];
								}else{
									$iduph[] = $idu[$i];
									$idudb[] = $idu[$i];
									$dbsms[] = $sms[$i];
									$dbemail[] = $email[$i];
								}
							}

							$idudb[] = $user->ID_U;
							$dbsms[] = 0;
							$dbemail[] = 0;

							$processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($id);
							$usernk = $processinfo["ID_U_NK"];
							$usernc = $processinfo["ID_U_NC"];
							
							$vbd = new vbdenModel(QLVBDHCommon::getYear());
							$vbden = $vbd->findByHscv($id);
					
							$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
							//var_dump($dbemail);exit;
							$lc->send($vbden['ID_VBDEN'],$idudb,$noidung,$parameter["ID_U_BP"],0,null,$dbsms,$dbemail);
							if(count($idusend)>0){
								try{
									hosocongviecModel::SendAll(
										$id,
										$parameter["ID_U_BP"],
										$wf_id_t,
										array(),
										array(),
										array(),
										array(),
										array(),
										array(),
										$idusend,
										$noidungsend,
										$hanxulysend,
										$vbden['ID_VBDEN'],
										"ID_VBDEN",
										"VBD_FK_VBDEN_HSCVS",
										$sendsms,
										$sendemail,
                                                                                $arrnv
									);
                                                                        if(($masovanbanlt != '' || $masovanbanlt != NULL ) && count($arrnv) > 0 )
                                                                        {
                                                                                $model = new UsersModel();
                                                                                $nguoinhan = $model->getName($idusend[0]);
                                                                                $token = $giaoviecservice->login($madonvi,md5($password),"");
                                                                                $giaoviecservice->UpdateTrangThaiCongViec(
                                                                                            $token
                                                                                            ,$masovanbanlt
                                                                                            ,1
                                                                                            ,$idusend[0]
                                                                                            ,$nguoinhan['TENNGUOITAO']
                                                                                    );
                                                                        }
                                                                       // var_Dump($reTrangThai);exit;
								}catch(Exception $ex){
									echo $ex->__toString();
								}
								
							}else{
								/* nguyend comment vì cần phải chuyển xử lý
								if($istheodoi==1){
									HosoluutheodoiModel::luuTheodoi(QLVBDHCommon::getYear(),$id,"",$parameter["ID_U_BP"]);
								}
								*/
							}
							if(count($iduph)>0){
								PhoiHopModel::AddPhoiHop($iduph,$vbden['ID_VBDEN'],$parameter["ID_U_BP"]);
							}
							$this->vbden->getDefaultAdapter()->commit();
						//}
					}else{
						if( !is_array($this->view->parameter["wf_nextuser"]) &&
							!is_array($this->view->parameter["wf_nextg"]) &&
							!is_array($this->view->parameter["wf_nextdep"])
						){
							
							$id_hscv = $hscv->CreateHSCV(
								$this->view->parameter["TRICHYEU"],1,$this->view->parameter["id_loaihscv"],
								implode("-",array_reverse(explode("/",$this->view->parameter["NGAYDEN"]))),
								implode("-",array_reverse(explode("/",$this->view->parameter["NGAYDEN"]))),
								Zend_Registry::get('auth')->getIdentity()->ID_U,
								$this->view->parameter["wf_nextuser"],
								$this->view->parameter["wf_name_user"],
								$this->view->parameter["wf_hanxuly_user"],
								0,
								$this->view->parameter["wf_sms"],
								$this->view->parameter["wf_email"],
								$this->view->parameter["wf_nextuser"]
							);
							//echo $id_hscv;exit;
							if($id_hscv>0){
								$fkvbd->insert(array("ID_VBDEN"=>$idvbd,"ID_HSCV"=>$id_hscv));
								if((int)$parameter["ID_THUMUC"])
									thumucModel::updateThumucHscv($id_hscv,$parameter["ID_THUMUC"]);
								
								try{
									$r = $db->query("select wft.* from WF_TRANSITIONS wft
									inner join	". QLVBDHCommon::Table("wf_processitems") ." wfit on wft.ID_P = wfit.ID_P
									where wft.ISFIRST=1 and wfit.ID_O =?",array($id_hscv));
									$activity = $r->fetch();
									
									if($activity["ISLAST"] == 1)
										HosoluutheodoiModel::luuTheodoi(QLVBDHCommon::getYear(),$id_hscv,"",$this->view->parameter["wf_nextuser"]);
								}catch(Exception $ex){
								}
								$this->vbden->getDefaultAdapter()->commit();
							}else{
								$this->vbden->getDefaultAdapter()->rollBack();
							}
							$this->vbden->getDefaultAdapter()->closeConnection();
							
						}else{
							$this->vbden->getDefaultAdapter()->closeConnection();
							echo 0; 
							
							exit;
						}
					}
				}
			}
			
	// chuyển để biết
			//Lấy ID văn bản đến từ idhscv
			$vbden = new vbdenModel($year);
			$lc = new vbd_dongluanchuyenModel($year);
			$id_vbd = $vbden->findByHscv($id_hscv);
			$id_vbd = $id_vbd['ID_VBD'];
			//Thêm mới vào dòng luân chuyển
			
			$lc->send($id_vbd,$parameter['ID_U'],$parameter['NOIDUNGCHUYEN'],Zend_Registry::get('auth')->getIdentity()->ID_U);			
	// kết thúc
			if($parameter["ID_VBDI"]>0){
				global $db;
				$db->update(QLVBDHCommon::table("VBDI_DONGLUANCHUYEN"),array("DA_TIEPNHAN"=>1),"ID_VBDI=".(int)$parameter["ID_VBDI"]." AND NGUOINHAN=".Zend_Registry::get('auth')->getIdentity()->ID_U);
			}
			
			// Xoa van ban nhap
			vbdendraftModel::DeleteOne($parameter['ID_VBD_DRAFT']);

			echo 1; //luu thanh cong
			exit;
		}
    }
    /**
     * The delete action - delete item
     */
    public function deleteAction(){        
      
        $parameter = $this->getRequest()->getParams();  
        // Set Year
        $year = QLVBDHCommon::getYear();    
        $idvbden = $parameter["id"];
        if($parameter["id"]){
	        $this->vbden = new vbdenModel($year);
	        try{
	            $this->vbden->delete("ID_VBD = $idvbden AND IS_KHONGXULY=1");
	        }catch(Exception $ex){
	        }
        }
        $this->_redirect("/vbden/vbden/index");  
    }
    /**
     * The check action
     */
    public function checkAction(){        
        $this->view->parameter =  $this->getRequest()->getParams();
        $parameter = $this->getRequest()->getParams();  
        // Set Year
        $year = $parameter["year"];
        Zend_Registry::set('year',$year);
        $year = (!isset($year) || $year == '' || $year == 0)?Zend_Date::now()->get(Zend_Date::YEAR):$year;        
        // Set result
        $result = false;
        
        $this->vbden = new vbdenModel($year);
        $type = (int)$this->view->parameter["TYPE"];
        $value = $this->view->parameter["VALUECHECKED"];
		$arr_param_soden = array();
		$arr_param_soden['ID_SVB'] = $this->view->parameter["ID_SVB"];
		$arr_param_soden['ID_LVB'] = $this->view->parameter["ID_LVB"];
        $arr_param_soden['SODEN_IN'] = $this->view->parameter["SODEN"];
		switch ($type) {
			case 1:
			    // kiểm tra số ký hiệu có hợp lệ hay không			 
			    $arr_value_skh = array();
			    $arr_value_skh["SOKYHIEU"] = $this->view->parameter["SOKYHIEU"];
			    $arr_value_skh["ID_CQ"] = $this->view->parameter["ID_CQ"];
			    $arr_value_skh["NGAYBANHANH"] = implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"])));
			    $arr_value_skh["ID_SVB"] = $this->view->parameter["ID_SVB"];
			    $result = vbdenModel::checkSoKyHieu($year,$arr_value_skh);
			    break;
                        case 6:
			    // kiểm tra văn bản liên thông			 
			    $arr_value_skh = array();
			    $arr_value_skh["SOKYHIEU"] = $this->view->parameter["SOKYHIEU"];
			    $arr_value_skh["COQUANBANHANH_TEXT"] = $this->view->parameter["COQUANBANHANH_TEXT"];
			    $arr_value_skh["NGAYBANHANH"] = implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"])));
			    $result = vbdenModel::checkVbLienthong($year,$arr_value_skh);
			    break;
			case 3:
				//Kiem tra toan bo (so ky hieu + so den)
				$arr_param_all['ID_SVB'] = $this->view->parameter["ID_SVB"];
				$arr_param_all['ID_LVB'] = $this->view->parameter["ID_LVB"];
        		$arr_param_all["SOKYHIEU"] = $this->view->parameter["SOKYHIEU"];
			   	$arr_param_all["ID_CQ"] = $this->view->parameter["ID_CQ"];
			    $arr_param_all["NGAYBANHANH"] = implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"])));
			    
			    $arr_param_all['SODEN'] = $this->view->parameter["SODEN"];
				$arr_param_all["COQUANBANHANH_TEXT"] = $this->view->parameter["COQUANBANHANH_TEXT"];
			    if(is_null($arr_param_all["COQUANBANHANH_TEXT"]))
			    	$arr_param_all["COQUANBANHANH_TEXT"] = "";
				$is_update = $this->view->parameter["ODKHDF"];
			    $id_vbd = $this->view->parameter["GGHDJJSJ"];
				
			    if($is_update == 1){
			    	$result = 1;		    		
			    }else
			  		$result = vbdenModel::checkAllData($year,$arr_param_all,$parameter["skipdraft"]);
				break;
			case 2:
			    // kiểm tra số đến có hợp lệ hay không
			    $result = $this->vbden->check_soden($this->view->parameter["SODEN"],$arr_param_soden);
			    break;
			case 4:
			    // lấy về số đến tăng tự động theo sổ văn bản và năm
				$result = $this->vbden->get_soden($arr_param_soden);
			    break;
                        /*
                         * Tuanpp them hạn xử lý mặc định dựa vào LVB
                         */
                        case 5:			    
				$result = $this->vbden->GetDefaultHXL($arr_param_soden['ID_LVB']);
			    break;
//                        Tuanpp end
		}
		echo (int)$result;
		exit;  
    }
    
    //danh sach dong luan chuyen
    
    function wayAction(){
		$this->_helper->layout->disableLayout();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		
		$year = $param["year"];
		$type = $param["type"];
		$code = $param["code"];
		$arr_id = array();
		$hscv = vbdenModel::GetAllHSCV($param["id_vbd"]);
		foreach($hscv as $itemhscv){
			$arr_id[] = $itemhscv['ID_HSCV'];
		}
		//$arr_id = explode(',',$param["id"]) ;
		
		$this->view->sendprocess = array();
		
		//Tao object
		
		$idobject = $param["id_vbd"];
		
		$this->view->ID_HSCV = $arr_id[0];
		$way = array();
		if($type==1){
				$lcvbd = new vbd_dongluanchuyenModel($year);
				$this->view->ID_VBD = $idobject;
				$way = $lcvbd->way($idobject);
		}
		foreach ($arr_id as $idhscv)
		{
			
			$sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);
			array_push($this->view->sendprocess,$sendprocess);
			
			
		}
		
		$this->view->type = $type;
		$this->view->year = $year;
		$this->view->way = $way;
		$this->view->code = $code;
		$this->view->idobject = $idobject;
		//kiem tra dieu kien truy cap
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
			$isreadonly = 0;
		$isCapnhat = 1;
		if(hosocongviecModel::isLuutru($idhscv,$year) == true || $isreadonly == 1){
			$isCapnhat = 0;
			}else{$isCapnhat = 1; }
		
		$this->view->isCapnhat = $isCapnhat;
		
		$this->view->lcnoibo = chuyennoiboModel::getLuanchuyennoibo($idobject);
                

	}
    
    
    
    public function detailAction(){
    	$this->_helper->layout->disablelayout();
    	global $db;
    	$param =  $this->getRequest()->getParams();
    	$idhscv = $param["id"];
		$alias = WFEngine::GetClassNameFromObjectId($idhscv);
		$year = $param["year"];
		$type = $param["type"];
		if($param["isidvbden"] == 1){$alias='VBD';};
		if($alias=='VBD'){
			$tablename = QLVBDHCommon::Table("vbd_vanbanden");
			
			$sql = "
	    		SELECT 
	    			obj.*,svb.NAME as SVBNAME,
	    			concat(empnt.FIRSTNAME,' ',empnt.LASTNAME) as EMPNTNAME,
	    			lvvb.NAME as LVVBNAME,
	    			lvb.NAME as LVBNAME, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID
	    		FROM
	    			$tablename obj
	    			left join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv on fkhscv.ID_VBDEN = obj.ID_VBD
	    			left join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on hscv.ID_HSCV = fkhscv.ID_HSCV
	    			left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
					left join VB_SOVANBAN svb on svb.ID_SVB = obj.ID_SVB
	    			left join QTHT_USERS unt on obj.NGUOITAO = unt.ID_U
	    			left join VB_LINHVUCVANBAN lvvb on lvvb.ID_LVVB = obj.ID_LVVB
	    			left join VB_LOAIVANBAN lvb on lvb.ID_LVB = obj.ID_LVB
	    			left join QTHT_EMPLOYEES empnt on empnt.ID_EMP = unt.ID_EMP
	    		WHERE
	    			hscv.ID_HSCV = ? 	
	    	";
			if($param["isidvbden"] == 1){
				$sql = "
				SELECT
                vb.*, lvb.NAME as LVBNAME, lvvb.NAME as LVVBNAME,
                svb.NAME as SVBNAME, cq.NAME as CQNAME,
                concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME
				, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID 
            FROM
                ".QLVBDHCommon::Table("VBD_VANBANDEN")."  vb
				left join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv on fkhscv.ID_VBDEN = vb.ID_VBD
	    		left join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on hscv.ID_HSCV = fkhscv.ID_HSCV
	    		left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
                
				left join VB_LOAIVANBAN lvb on vb.ID_LVB = lvb.ID_LVB
                left join VB_LINHVUCVANBAN lvvb on vb.ID_LVVB =lvvb.ID_LVVB
                left join VB_SOVANBAN svb on vb.ID_SVB = svb.ID_SVB
                left join VB_COQUAN cq	on vb.ID_CQ = cq.ID_CQ
                left join QTHT_USERS u on vb.NGUOITAO = u.ID_U
                left join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
            WHERE
                vb.ID_VBD = ?";
			}
			
			$r = $db->query($sql,$idhscv);
	    	$this->view->data = $r->fetch();
	    	$sql = "
	    		SELECT dk.* FROM ".QLVBDHCommon::Table("GEN_FILEDINHKEM")." dk 
	    		WHERE
	    			 ID_OBJECT = '".$this->view->data['ID_VBD']."'
	    			 and
	    			 TYPE = 3
	    	";
	    	//echo $sql;
	    	$r = $db->query($sql);
	    	$this->view->file = $r->fetchAll();
	    	//var_dump($this->view->data);
		}else if($alias=='VBSOANTHAO'){
			$tablename = QLVBDHCommon::Table("hscv_congviecsoanthao");
			$sql = "
	    		SELECT 
                    hscv.NGAY_KT,
	    			obj.*,
	    			concat(empyc.FIRSTNAME,' ',empyc.LASTNAME) as EMPYCNAME,
	    			concat(empxl.FIRSTNAME,' ',empxl.LASTNAME) as EMPXLNAME,
					concat(empxt.FIRSTNAME,' ',empxt.LASTNAME) as EMPTNAME
					, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID
	    		FROM
	    			$tablename obj
	    			inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on hscv.ID_HSCV = obj.ID_HSCV
	    			left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
					left join QTHT_USERS uyc on obj.NGUOIYEUCAU = uyc.ID_U
	    			left join QTHT_USERS uxl on obj.NGUOIXULY = uxl.ID_U
					left join QTHT_USERS uxt on obj.NGUOITAO = uxt.ID_U
	    			left join QTHT_EMPLOYEES empyc on empyc.ID_EMP = uyc.ID_EMP
	    			left join QTHT_EMPLOYEES empxl on empxl.ID_EMP = uxl.ID_EMP
					left join QTHT_EMPLOYEES empxt on empxt.ID_EMP = uxt.ID_EMP
	    		WHERE
	    			hscv.ID_HSCV = ?
	    	";
	    	$r = $db->query($sql,$idhscv);
	    	$this->view->data = $r->fetch();
	    	$sql = "
	    		SELECT dk.* FROM ".QLVBDHCommon::Table("GEN_FILEDINHKEM")." dk 
	    		WHERE
	    			 ID_OBJECT = '$idhscv'
	    			 and
	    			 TYPE = 1
	    	";
	    	$r = $db->query($sql);
	    	$this->view->file = $r->fetchAll();
		}else{
			$tablename = QLVBDHCommon::Table("motcua_hoso");
			$sql = "
	    		SELECT
	    			obj.*,
	    			concat(empnn.FIRSTNAME,' ',empnn.LASTNAME) as EMPXLNAME,
	    			lhs.TENLOAI as LHSNAME, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID
	    		FROM
	    			$tablename obj
	    			inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on hscv.ID_HSCV = obj.ID_HSCV
	    			left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
					left join QTHT_USERS unn on obj.NGUOINHAN = unn.ID_U
	    			left join MOTCUA_LOAI_HOSO lhs on lhs.ID_LOAIHOSO = obj.ID_LOAIHOSO
	    			left join QTHT_EMPLOYEES empnn on empnn.ID_EMP = unn.ID_EMP
	    		WHERE
	    			hscv.ID_HSCV = ? 	
	    	";
	    	$r = $db->query($sql,$idhscv);
	    	$this->view->data = $r->fetch();
	    	$sql = "
	    		SELECT dk.* FROM ".QLVBDHCommon::Table("GEN_FILEDINHKEM")." dk 
	    		WHERE
	    			 ID_OBJECT = '$idhscv'
	    			 and
	    			 TYPE = 1
	    	";
	    	$r = $db->query($sql);
	    	$this->view->file = $r->fetchAll();
		}
    	$this->view->alias = $alias;
    }
    /**
	 * Tạo form list cho HSCV chung chung
	 */
	public function listAction(){
			global $auth;
			$user = $auth->getIdentity();
			//Lấy parameter
			$param = $this->getRequest()->getParams();
			
			//$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
			$config = Zend_Registry::get('config');
			$realyear = QLVBDHCommon::getYear();
					if( $this->getRequest()->getParam('ID_LVB')==33)
							$this->view->saoy=1;
			//tinh chỉnh param
			$IS_SEE_ALL = (int)$param['IS_SEE_ALL'];
			$ID_VBD 	= $param['ID_VBD'];
			$TRICHYEU 	= $param['TRICHYEU'];		
			$ID_CQ 		= $param['ID_CQ'];
			$ID_SVB 	= $param['ID_SVB'];
			$ID_LVVB 	= $param['ID_LVVB'];
			$ID_LVB 	= $param['ID_LVB'];
			$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
			$DOMAT 		= $param['DOMAT'];
			$DOKHAN 	= $param['DOKHAN'];	
			$SODEN		= $param['SODEN'];
			$IS_PHOBIEN		= $param['IS_PHOBIEN'];
			$SOKYHIEU	= $param['SOKYHIEU'];
			$SODEN_ISLIKE	= $param['SODEN_ISLIKE'];
			$NGUOIXULY	= $param['NGUOIXULY'];
			$SORTBY	= $param['SORTBY'];
			if(!$SORTBY)
				$SORTBY = "NGAYDEN"; 
			$SORTTYPE	= $param['SORTTYPE'];
			if(!$SORTTYPE)
				$SORTTYPE ="DESC";
			
			$INNAME	= $param['INNAME'];
			$INFILE	= $param['INFILE'];
			if($param['INNAME']==0 && $param['INFILE']==0){
				$INNAME = 1;
			}
			if($param['NGAYDEN_BD']!=""){
				$ngayden_bd = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
				$ngayden_bd = implode("-",array_reverse(explode("/",$ngayden_bd)));
			}
			if($param['NGAYDEN_KT']!=""){
				$ngayden_kt = $param['NGAYDEN_KT']."/".QLVBDHCommon::getYear();
				$ngayden_kt = implode("-",array_reverse(explode("/",$ngayden_kt)));
			}
			if($param['NGAYBANHANH_BD']!=""){
				$ngaybanhanh_bd = $param['NGAYBANHANH_BD']."/".QLVBDHCommon::getYear();
				$ngaybanhanh_bd = implode("-",array_reverse(explode("/",$ngaybanhanh_bd)));
			}
			if($param['NGAYBANHANH_KT']!=""){
				$ngaybanhanh_kt = $param['NGAYBANHANH_KT']."/".QLVBDHCommon::getYear();
				$ngaybanhanh_kt = implode("-",array_reverse(explode("/",$ngaybanhanh_kt)));
			}	    
			
			if($param['CHUA_DOC']==1){
				$CHUA_DOC = $param['CHUA_DOC'];
			}else{
			
			}  
			$page = $param['page'];
			$limit = $param['limit1'];		
			if($limit==0 || $limit=="")$limit=$config->limit;
			if($page==0 || $page=="")$page=1;
			$scope = array();
			if($param['SCOPE']){
				$scope = $param['SCOPE'];
			}
			$parameter = array(
				
				"TRICHYEU"=>$TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
				"ID_CQ"=>$ID_CQ,
				"ID_SVB"=>$ID_SVB,
				"ID_LVVB"=>$ID_LVVB,
				"ID_LVB"=>$ID_LVB,
				"SODEN"=>$SODEN,
				"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
				"DOMAT"=>$DOMAT,
				"DOKHAN"=>$DOKHAN,		
				"NGAYDEN_BD"=>$ngayden_bd,
				"NGAYDEN_KT"=>$ngayden_kt,
				"NGAYBANHANH_BD"=>$ngaybanhanh_bd,
				"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
				"ID_U"=>$user->ID_U,
				"SOKYHIEU"=>$SOKYHIEU,
				"IS_SEE_ALL" =>$IS_SEE_ALL, 
				"SCOPE"=>$scope,
				"INNAME"=>$INNAME,
				"INFILE"=>$INFILE,
				"SORTBY"=>$SORTBY,
				"SORTTYPE"=>$SORTTYPE,
				"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
				"CHUA_DOC" => $CHUA_DOC,
				"NGUOIXULY" => $NGUOIXULY
			);
			
			if($param['IS_PHOBIEN']==1){
				$IS_PHOBIEN = $param['IS_PHOBIEN'];
			}else{
			
			}  
			$page = $param['page'];
			$limit = $param['limit1'];		
			if($limit==0 || $limit=="")$limit=$config->limit;
			if($page==0 || $page=="")$page=1;		
			$scope = array();
			if($param['SCOPE']){
				$scope = $param['SCOPE'];
			}
			$parameter = array(
				
				"TRICHYEU"=>$TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
				"ID_CQ"=>$ID_CQ,
				"ID_SVB"=>$ID_SVB,
				"ID_LVVB"=>$ID_LVVB,
				"ID_LVB"=>$ID_LVB,
				"SODEN"=>$SODEN,
				"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
				"DOMAT"=>$DOMAT,
				"DOKHAN"=>$DOKHAN,		
				"NGAYDEN_BD"=>$ngayden_bd,
				"NGAYDEN_KT"=>$ngayden_kt,
				"NGAYBANHANH_BD"=>$ngaybanhanh_bd,
				"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
				"ID_U"=>$user->ID_U,
				"SOKYHIEU"=>$SOKYHIEU,
				"IS_SEE_ALL" =>$IS_SEE_ALL, 
				"SCOPE"=>$scope,
				"INNAME"=>$INNAME,
				"INFILE"=>$INFILE,
				"SORTBY"=>$SORTBY,
				"SORTTYPE"=>$SORTTYPE,
				"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
				"CHUA_DOC" => $CHUA_DOC,
				"IS_PHOBIEN"=>$IS_PHOBIEN,
				"NGUOIXULY"=>$NGUOIXULY
			);
			

			if($SORTBY !="DAXEM"){
				if($SORTTYPE!=""){
					$sort = $SORTBY." ".$SORTTYPE;
				}else{
					$sort = $SORTBY;
				}
			}else{
				$sort = "NGAYDEN DESC";
			}
			
			//Tạo đối tượng
			$hscv = new hosocongviecModel();
			$hscvcount = $hscv->Count_vbd($parameter);			
			//Lấy dữ liệu

			$this->view->data = $hscv->SelectAll_vbd($parameter,($page-1)*$limit,$limit,$sort);
			
			$this->view->realyear 		= $realyear;
			$this->view->TRICHYEU 		= $TRICHYEU;
			$this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
			$this->view->NGUOIXULY 		= $NGUOIXULY;
			$this->view->ID_CQ 		    = $ID_CQ;
			$this->view->ID_SVB 		= $ID_SVB;
			$this->view->ID_LVVB 		= $ID_LVVB;
			$this->view->ID_LVB 		= $ID_LVB;
			$this->view->ID_VBD 		= $ID_VBD;
			$this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
			$this->view->DOMAT 			= $DOMAT;
			$this->view->DOKHAN 		= $DOKHAN;
			$this->view->NGAYDEN_BD 	= $param['NGAYDEN_BD'];
			$this->view->NGAYDEN_KT 	= $param['NGAYDEN_KT'];
			$this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
			$this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
			$this->view->SODEN 			= $param['SODEN'];
			$this->view->SOKYHIEU 			= $param['SOKYHIEU'];
			$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
			$this->view->SCOPE = $scope;
			$this->view->INNAME = $INNAME;
			$this->view->INFILE = $INFILE;
			$this->view->SORTBY = $SORTBY;
			$this->view->SORTTYPE = $SORTTYPE;
			$this->view->SODEN_ISLIKE = $SODEN_ISLIKE;
			$this->view->user = $user;
			$this->view->IS_SEE_ALL = $IS_SEE_ALL;
			$this->view->ADVANCED= $param['ADVANCEDVALUE'];
			$this->view->CHUA_DOC = $CHUA_DOC;
			$this->view->IS_PHOBIEN = $IS_PHOBIEN;

			$ID_LOAIHSCV = 1;
			$createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);

			$actid = ResourceUserModel::getActionByUrl('vbden','vbden','listall');
					if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
						QLVBDHButton::EnableVbdCoquan('/vbden/vbden/listall');
										   
					}
			$actid = ResourceUserModel::getActionByUrl('vbden','vbden','input');
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
				QLVBDHButton::AddButton("Thêm mới","","AddNewButton","CreateButtonClick(\"/vbden/vbden/input/type/1\")");
								   
			}
			$this->view->title = "Danh sách văn bản đến";
			$this->view->config = $config;
			$this->view->page = $page;
			$this->view->limit = $limit;
			$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbden/vbden/list");
	}

	/* Trang danh sách nhiệm vụ */

    public function listnhiemvuAction() {
        global $auth;
        $user = $auth->getIdentity();
        //Lấy parameter
        $param = $this->getRequest()->getParams();

        //$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
        $config = Zend_Registry::get('config');
        $realyear = QLVBDHCommon::getYear();
        if ($this->getRequest()->getParam('ID_LVB') == 33)
            $this->view->saoy = 1;
        //tinh chỉnh param
        $IS_SEE_ALL = (int) $param['IS_SEE_ALL'];
        $ID_VBD = $param['ID_VBD'];
        $TRICHYEU = $param['TRICHYEU'];
        $ID_CQ = $param['ID_CQ'];
        $ID_SVB = $param['ID_SVB'];
        $ID_LVVB = $param['ID_LVVB'];
        $ID_LVB = $param['ID_LVB'];
        $COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT'] == MSG11001017 ? '' : $param['COQUANBANHANH_TEXT'];
        $DOMAT = $param['DOMAT'];
        $DOKHAN = $param['DOKHAN'];
        $SODEN = $param['SODEN'];
        $IS_PHOBIEN = $param['IS_PHOBIEN'];
        $SOKYHIEU = $param['SOKYHIEU'];
        $SODEN_ISLIKE = $param['SODEN_ISLIKE'];
        $NGUOIXULY = $param['NGUOIXULY'];
        $SORTBY = $param['SORTBY'];
        if (!$SORTBY)
            $SORTBY = "NGAYDEN";
        $SORTTYPE = $param['SORTTYPE'];
        if (!$SORTTYPE)
            $SORTTYPE = "DESC";

        $INNAME = $param['INNAME'];
        $INFILE = $param['INFILE'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }
        if ($param['NGAYDEN_BD'] != "") {
            $ngayden_bd = $param['NGAYDEN_BD'] . "/" . QLVBDHCommon::getYear();
            $ngayden_bd = implode("-", array_reverse(explode("/", $ngayden_bd)));
        }
        if ($param['NGAYDEN_KT'] != "") {
            $ngayden_kt = $param['NGAYDEN_KT'] . "/" . QLVBDHCommon::getYear();
            $ngayden_kt = implode("-", array_reverse(explode("/", $ngayden_kt)));
        }
        if ($param['NGAYBANHANH_BD'] != "") {
            $ngaybanhanh_bd = $param['NGAYBANHANH_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaybanhanh_bd = implode("-", array_reverse(explode("/", $ngaybanhanh_bd)));
        }
        if ($param['NGAYBANHANH_KT'] != "") {
            $ngaybanhanh_kt = $param['NGAYBANHANH_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaybanhanh_kt = implode("-", array_reverse(explode("/", $ngaybanhanh_kt)));
        }

        if ($param['CHUA_DOC'] == 1) {
            $CHUA_DOC = $param['CHUA_DOC'];
        } else {
            
        }
        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == "")
            $limit = $config->limit;
        if ($page == 0 || $page == "")
            $page = 1;
        $scope = array();
        if ($param['SCOPE']) {
            $scope = $param['SCOPE'];
        }
        $parameter = array(
            "TRICHYEU" => $TRICHYEU,
            "TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
            "ID_CQ" => $ID_CQ,
            "ID_SVB" => $ID_SVB,
            "ID_LVVB" => $ID_LVVB,
            "ID_LVB" => $ID_LVB,
            "SODEN" => $SODEN,
            "COQUANBANHANH_TEXT" => $COQUANBANHANH_TEXT,
            "DOMAT" => $DOMAT,
            "DOKHAN" => $DOKHAN,
            "NGAYDEN_BD" => $ngayden_bd,
            "NGAYDEN_KT" => $ngayden_kt,
            "NGAYBANHANH_BD" => $ngaybanhanh_bd,
            "NGAYBANHANH_KT" => $ngaybanhanh_kt,
            "ID_U" => $user->ID_U,
            "SOKYHIEU" => $SOKYHIEU,
            "IS_SEE_ALL" => $IS_SEE_ALL,
            "SCOPE" => $scope,
            "INNAME" => $INNAME,
            "INFILE" => $INFILE,
            "SORTBY" => $SORTBY,
            "SORTTYPE" => $SORTTYPE,
            "SODEN_ISLIKE" => $SODEN_ISLIKE,
            "CHUA_DOC" => $CHUA_DOC,
            "NGUOIXULY" => $NGUOIXULY
        );

        if ($param['IS_PHOBIEN'] == 1) {
            $IS_PHOBIEN = $param['IS_PHOBIEN'];
        } else {
            
        }
        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == "")
            $limit = $config->limit;
        if ($page == 0 || $page == "")
            $page = 1;
        $scope = array();
        if ($param['SCOPE']) {
            $scope = $param['SCOPE'];
        }
        $parameter = array(
            "TRICHYEU" => $TRICHYEU,
            "TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
            "ID_CQ" => $ID_CQ,
            "ID_SVB" => $ID_SVB,
            "ID_LVVB" => $ID_LVVB,
            "ID_LVB" => $ID_LVB,
            "SODEN" => $SODEN,
            "COQUANBANHANH_TEXT" => $COQUANBANHANH_TEXT,
            "DOMAT" => $DOMAT,
            "DOKHAN" => $DOKHAN,
            "NGAYDEN_BD" => $ngayden_bd,
            "NGAYDEN_KT" => $ngayden_kt,
            "NGAYBANHANH_BD" => $ngaybanhanh_bd,
            "NGAYBANHANH_KT" => $ngaybanhanh_kt,
            "ID_U" => $user->ID_U,
            "SOKYHIEU" => $SOKYHIEU,
            "IS_SEE_ALL" => $IS_SEE_ALL,
            "SCOPE" => $scope,
            "INNAME" => $INNAME,
            "INFILE" => $INFILE,
            "SORTBY" => $SORTBY,
            "SORTTYPE" => $SORTTYPE,
            "SODEN_ISLIKE" => $SODEN_ISLIKE,
            "CHUA_DOC" => $CHUA_DOC,
            "IS_PHOBIEN" => $IS_PHOBIEN,
            "NGUOIXULY" => $NGUOIXULY
        );


        if ($SORTBY != "DAXEM") {
            if ($SORTTYPE != "") {
                $sort = $SORTBY . " " . $SORTTYPE;
            } else {
                $sort = $SORTBY;
            }
        } else {
            $sort = "NGAYDEN DESC";
        }

        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        $hscvcount = $hscv->Count_vbd($parameter);
        //Lấy dữ liệu

        $this->view->data = $hscv->SelectAll_vbd($parameter, ($page - 1) * $limit, $limit, $sort);

        $this->view->realyear = $realyear;
        $this->view->TRICHYEU = $TRICHYEU;
        $this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
        $this->view->NGUOIXULY = $NGUOIXULY;
        $this->view->ID_CQ = $ID_CQ;
        $this->view->ID_SVB = $ID_SVB;
        $this->view->ID_LVVB = $ID_LVVB;
        $this->view->ID_LVB = $ID_LVB;
        $this->view->ID_VBD = $ID_VBD;
        $this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
        $this->view->DOMAT = $DOMAT;
        $this->view->DOKHAN = $DOKHAN;
        $this->view->NGAYDEN_BD = $param['NGAYDEN_BD'];
        $this->view->NGAYDEN_KT = $param['NGAYDEN_KT'];
        $this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
        $this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
        $this->view->SODEN = $param['SODEN'];
        $this->view->SOKYHIEU = $param['SOKYHIEU'];
        $this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
        $this->view->SCOPE = $scope;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;
        $this->view->SORTBY = $SORTBY;
        $this->view->SORTTYPE = $SORTTYPE;
        $this->view->SODEN_ISLIKE = $SODEN_ISLIKE;
        $this->view->user = $user;
        $this->view->IS_SEE_ALL = $IS_SEE_ALL;
        $this->view->ADVANCED = $param['ADVANCEDVALUE'];
        $this->view->CHUA_DOC = $CHUA_DOC;
        $this->view->IS_PHOBIEN = $IS_PHOBIEN;

        $ID_LOAIHSCV = 1;
        $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV, $user->ID_U);

        $actid = ResourceUserModel::getActionByUrl('vbden', 'vbden', 'listall');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            QLVBDHButton::EnableVbdCoquan('/vbden/vbden/listall');
        }
        $actid = ResourceUserModel::getActionByUrl('vbden', 'vbden', 'input');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            QLVBDHButton::AddButton("Thêm mới", "", "AddNewButton", "CreateButtonClick(\"/vbden/vbden/input/type/1\")");
        }
        $this->view->title = "Danh sách văn bản đến giao việc";
        $this->view->config = $config;
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/vbden/vbden/listnhiemvu");
    }

	function listdraftAction()
	{
		$this->view->title = "VĂN BẢN ĐẾN MỚI VÀO SỔ";
		$id_svb = $this->_request->getParam('ID_SVB');
		if($id_svb == 0)
		{
			$id_svb = 0;
		}
		$this->view->data = vbdendraftModel::SelectAll($id_svb);
		$this->view->id_svb = $id_svb;
		QLVBDHButton::AddButton("Thêm mới","","AddNewButton","document.location.href=\"/vbden/vbden/input/type/1/draft/1\";");
	}
	function inputvbdenAction(){
		$this->view->title = "Cập nhật thông tin văn bản đến";
		//$this->view->subtitle = "Cập nhật";
		$params = $this->_request->getParams();
		$id_vbd = $params["id_vbd"];
		$this->view->id_vbd = $id_vbd;
		$year = QLVBDHCommon::getYear();
		//kiem tra id_vbd co ton tai hay khong
		$ton_tai = 0;
		
		if($id_vbd != 0){
			//kiem tra id_vbd co ton tai hay khong
			if(vbdenModel::isExists($year,$id_vbd)){
				$ton_tai = 1;
			}
		}
		if($ton_tai == 0){
			//nhay den trang thong bao loi
			$this->_redirect("/vbden/vbden/list");
		}
		$dlcModel = new vbd_dongluanchuyenModel($year);
		$this->view->data_chuyendebiet = $dlcModel->way($id_vbd);
		
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$this->view->user = $user;
		$this->view->name_u = UsersModel::getEmloyeeNameByIdUser($user->ID_U) ;
		
		$data = vbdenModel::getDetails($year,$id_vbd);
		
		$this->view->ID_VBD 		= $data["ID_VBD"];
		$this->view->TRICHYEU 		= $data["TRICHYEU"];
		$this->view->GHICHU 		= $data["GHICHU"];
		$this->view->ID_CQ 			= $data["ID_CQ"];
		$this->view->ID_SVB 		= $data["ID_SVB"];
		$this->view->ID_LVVB 		= $data["ID_LVVB"];
		$this->view->ID_LVB 		= $data["ID_LVB"];
		$this->view->COQUANBANHANH_TEXT = $data["COQUANBANHANH_TEXT"];
		
		$this->view->COQUANNHAN = $data["COQUANNHAN"];
		$this->view->DOMAT 			= $data["DOMAT"];
		$this->view->DOKHAN 		= $data["DOKHAN"];
		$this->view->SOTO 			= $data["SOTO"];
		$this->view->SOBAN 		= 	$data["SOBAN"];
		$this->view->NGAYBANHANH    = $data["NGAYBANHANH"];
		$this->view->SOKYHIEU = $data["SOKYHIEU"];
		$this->view->NGAYDEN = $data["NGAYDEN"];
		$this->view->SODEN = $data["SODEN"];
		$this->view->NGUOIKY = $data["NGUOIKY"];
		$this->view->SODEN_IN = (int)$data["SODEN_IN"];
		$this->view->IS_PHOBIEN = (int)$data["IS_PHOBIEN"];
		$this->view->HANXULYTOANBO = (float)$data["HANXULYTOANBO"];
		//var_dump($data);
		//echo $this->view->TRICHYEU;
		QLVBDHButton::EnableSave("/vbden/vbden/Save");
        QLVBDHButton::EnableBack("/vbden/vbden");
	}
	
	function savevbdenAction(){
		
		/*
		Lay so den da ton tai trong csdl
		*/
		$this->view->parameter =  $this->getRequest()->getParams();
		
		$ngayhethan = date("Y-m-d", QLVBDHCommon::addDate(time(),(int)$this->view->parameter["HANXULYTOANBO"]));

		$year = QLVBDHCommon::getYear();
		$id_vbd = $this->view->parameter['ID_VBD'];
		if($id_vbd <= 0)
		{
			//kiem tra them khong ton tai
			$this->_redirect("/vbden/vbden/list");
		}
		$arr_col = Common_Sovanban::getColumNameGroup(1);
		$len_arr = count($arr_col);
		
		$old_data = vbdenModel::getDetails($year,$id_vbd);
		$old_soden = $old_data["SODEN"];
		$aff_old = 0;
		$col_name = "";
		foreach ($arr_col as $itcol){
			switch ($itcol)
			{
				case 'ID_SVB':
					$aff_old = $old_data["ID_SVB"];
					$col_name='ID_SVB';
					break;
				case 'ID_LVB':
					$aff_old = $old_data["ID_LVB"];
					$col_name='ID_LVB';
					break;
				default:
					break; 
			}
		}
				
		
		$result = 0;
		
        $parameter = $this->getRequest()->getParams();  
        // Set Year
        //$year = $parameter["year"];
        //Zend_Registry::set('year',$year);
        $year = (!isset($year) || $year == '' || $year == 0)?Zend_Date::now()->get(Zend_Date::YEAR):$year;
        Zend_Registry::set('year',$year);
        
        $this->vbden = new vbdenModel($year);
        $this->filedinhkem = new filedinhkemModel($year);
        
        Zend_Date::setOptions(array('format_type' => 'php'));
     
       	
		$arr_param_soden = array();
		$arr_param_soden['ID_SVB'] = $this->view->parameter["ID_SVB"];
		$arr_param_soden['ID_LVB'] = $this->view->parameter["ID_LVB"];
		$arr_param_soden["ID_CQ"] = $this->view->parameter["ID_CQ"];
		$arr_param_soden["NGUOI_KY"] = $this->view->parameter["NGUOI_KY"];
	    $arr_param_soden["NGAYBANHANH"] = implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"])));
	    $arr_param_soden["NGAYBANHANH"] += " 00:00:00";
		$arr_param_soden["SOKYHIEU"] = $this->view->parameter["SOKYHIEU"];		
        $arr_param_soden["SODEN"] = $this->view->parameter["SODEN"];	
        if(is_null($old_detail["COQUANBANHANH_TEXT"]))
			    		$old_detail["COQUANBANHANH_TEXT"] = "";
		if($old_detail["ID_CQ"] >0)
		    			$old_detail["COQUANBANHANH_TEXT"] = "";
    	//kiem tra so ky hieu
    	$check_skh = false;
    	if( $old_data["ID_CQ"] == $arr_param_all["ID_CQ"] &&
			$old_data["NGAYBANHANH"] == $arr_param_all["NGAYBANHANH"] &&
			$old_data["SOKYHIEU"] == $arr_param_all["SOKYHIEU"] &&
			$old_data["COQUANBANHANH_TEXT"] == $arr_param_all["COQUANBANHANH_TEXT"] &&
			$old_data["ID_CQ"] == $arr_param_all["ID_CQ"])
				
		$check_skh = true;
        
        /*Ket thuc kiem tra so ky hieu*/
        
       	$MASOVANBAN = 'MSVB';
        /**
         * trunglv tao doi tuong de lay ma so van ban den
         */
        $vbden = new vbden();
        $vbden->_id_lvb = $parameter['ID_LVB'];
        $vbden->_id_cq = $parameter['ID_CQ'];
        $vbden->_soden = $parameter['SODEN'];
        $vbden->_domat = $parameter['DOMAT'];
        $vbden->_dokhan = $parameter['DOKHAN'];
        $MASOVANBAN = Common_Maso::getMaSo(1,$vbden);
        //kiem tra so den
        $soden = $parameter['SODEN'];
		$check_soden = true;
        if($this->view->parameter[$col_name] == $aff_old){
			if($soden != $old_soden)
				$check_soden = false;
		}
        $sql_check ="";
        if($check_skh == true && $check_soden == true){
        	//khong kiem tra so ky hieu va so den
        }
        else{
        	if($check_skh == false && $check_soden == false){
        		//kiem tra so ky hieu va so den
        		$sql_check = vbdenModel::checkAllDataSQL($year,$arr_param_soden); 	
        	}else{
        		if($check_skh == false)
        			$sql_check = vbdenModel::checkSoKyHieuSql($year,$arr_param_soden);
        		if($check_soden == false)
        			 $sql_check = Common_Sovanban::checkExistsSodenSQL($year,$arr_param_soden,$vbden->_soden );
        			
        	}
        	
        	
        }
        
       	$sql_lock = 'LOCK TABLE vbd_vanbanden_'.$year. ' WRITE';
		//$sql_getsoden = Common_Sovanban::getCurrentSodenSQL($year,$arr_param_soden);
		//$sql_getsoden = vbdenModel::checkAllDataSQL($year,$arr_param_soden);
		//$this->vbden->getDefaultAdapter()->getConnection()->exec($sql_lock);
		if($sql_check !=""){
			$soden_re = $this->vbden->getDefaultAdapter()->getConnection()->query($sql_check);
			$cur_soden_row = $soden_re->fetch();
			$cur_soden = $cur_soden_row['DEM'];
		}
		//neu du lieu khong hop le $cur_soden > 0
		if($cur_soden >= 1 ){
			
			$this->vbden->getDefaultAdapter()->closeConnection();
			echo 2;
			exit;
		}
		
		
        
		try{
	        	
				//cập nhật vbden
				$this->vbden->update(array(
				"NGAYHETHAN"=>$ngayhethan,
				"HANXULYTOANBO"=>(int)$this->view->parameter["HANXULYTOANBO"],
				"ID_SVB"=>(int)$this->view->parameter["ID_SVB"],
				"NGUOITAO"=>Zend_Registry::get('auth')->getIdentity()->ID_U,
				"ID_LVVB"=>(int)$this->view->parameter["ID_LVVB"],
				"ID_HSCV"=>$id_hscv,"ID_CQ"=>(int)$this->view->parameter["ID_CQ"],
				"ID_LVB"=>(int)$this->view->parameter["ID_LVB"],
				"MASOVANBAN"=>$MASOVANBAN,
				"SOKYHIEU"=>$this->view->parameter["SOKYHIEU"],
				"SODEN"=>$this->view->parameter["SODEN"],
				"NGAYDEN"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYDEN"]))),
				"NGAYBANHANH"=>implode("-",array_reverse(explode("/",$this->view->parameter["NGAYBANHANH"]))),
				"NGAYTAO"=>Zend_Date::now()->toString("Y-m-j"),
				"COQUANBANHANH_TEXT"=>$this->view->parameter["COQUANBANHANH_TEXT"],
				"ID_CQ"=>$this->view->parameter["ID_CQ"],
				"COQUANNHAN"=>$this->view->parameter["COQUANNHAN"],
				"TRICHYEU"=>$this->view->parameter["TRICHYEU"],
				"GHICHU"=>$this->view->parameter["GHICHU"],
				"SOBAN"=>(int)$this->view->parameter["SOBAN"],
				"SOTO"=>(int)$this->view->parameter["SOTO"],
				"DOMAT"=>(int)$this->view->parameter["DOMAT"],
				"DOKHAN"=>(int)$this->view->parameter["DOKHAN"],
				"NGUOIKY"=>$this->view->parameter["NGUOIKY"],
				"SODEN_IN"=>(int)$this->view->parameter["SODEN_IN"],
				"SOKYHIEU_IN"=> (int)$this->view->parameter["SOKYHIEU_IN"],
				"IS_PHOBIEN"=> (int)$this->view->parameter["IS_PHOBIEN"]),
				"ID_VBD=".$this->view->parameter["ID_VBD"]);

				// Cập nhật hồ sơ công việc, quy trinh
				hosocongviecModel::UpdateNameByIdVBD($this->view->parameter["ID_VBD"]);

				$this->vbden->getDefaultAdapter()->closeConnection();
			   	
			   	/* Luu, cap nhat chuyen de biet*/
				
			   	$auth = Zend_Registry::get('auth');
				$user = $auth->getIdentity();
			   	$lc = new vbd_dongluanchuyenModel($year);
		       
	        	$arr_old_keep = $parameter['ID_OLD'];
			    
	        	$old_lcs = $lc->way($id_vbd);
				
			    $old_delete = array() ;
			    foreach ($old_lcs as $old_lc){
			    	$ln = 1;
					foreach ($arr_old_keep as $lc_keep){
						if($old_lc["ID_DLC"] == $lc_keep)
							$ln = 0;
					}
					if($ln == 1)
						array_push($old_delete,$old_lc["ID_DLC"]);
			    		
				}
			    
				
				foreach ($old_delete as $itdel){
					try 
					{
							$where = 'ID_DLC = ' . $itdel;
							$lc->delete($where);
							
					}
					catch(Exception $er){
						
					};	
			    }
			   
	      
			
				//Thêm mới vào dòng luân chuyển
				try 
				{
					
					$arr_user_re =$parameter['ID_U']; 
					if(!is_array($arr_user_re))
						$arr_user_re = array();
					$lc->send($id_vbd,$arr_user_re,$parameter['NOIDUNG'],$user->ID_U);			                	
					//echo $arru[$counter];
					
				}
				catch(Exception $er)
				{ 
				}
				
				/* Ket thuc Luu, cap nhat chuyen de biet*/
				
				/**
				 * end trunglv
				 */
				



				//cap nhat file dinh kem
				/*//COMMENT vì chuyển nội bộ re update type từ 15 thành 3 làm nhân file đính kèm
				$filedinhkem = new filedinhkemModel($year);
				for($i=0;$i<count($this->view->parameter["idFile"]);$i++){   
					$filedinhkem->update(array("ID_OBJECT"=>$id_vbd,"TYPE"=>3),"MASO='".$this->view->parameter["idFile"][$i]."'");
				}
				*/
			   	
			   	
				
				//luu van ban chuyen noi bo
				$actid = ResourceUserModel::getActionByUrl('hscv','chuyennoibo','chuyennoiboinput');
				if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
							$id_cqs = $this->view->parameter["ID_CQNGOAI"];
							chuyennoiboModel::luuchuyennoibo($id_vbd,$user->ID_U,$id_cqs,"");
				}
				
				echo 1;
				$this->vbden->getDefaultAdapter()->closeConnection();
			   	exit;
        
			}catch(Exception $ex){
					$this->vbden->getDefaultAdapter()->closeConnection(); 
					exit;
			}
			
	}
	
	function updatedadocAction(){
		global $db;
		$params = $this->_request->getParams();
		$id_vbd = $params['id_vbd'];
		$id_u = $params['id_u'];
		$year = $params['year'];
		$reload = $params['reload'];
		if($reload!=1){
			echo vbd_dongluanchuyenModel::updateDaDoc($year,$id_vbd,$id_u);
		}else{
			vbd_dongluanchuyenModel::updateDaDoc($year,$id_vbd,$id_u);
			$cdb_vbden = $db->query("
			SELECT vb.ID_VBD, TRICHYEU, SOKYHIEU, NGAYBANHANH
			FROM ".QLVBDHCommon::Table("vbd_vanbanden")." vb
			INNER JOIN ".QLVBDHCommon::Table("vbd_dongluanchuyen")." lc on lc.ID_VBD=vb.ID_VBD
			WHERE
				lc.NGUOINHAN = ? AND lc.DA_XEM=0
			GROUP BY vb.ID_VBD
			LIMIT 0,3
			",$id_u)->fetchAll();
			$count_cdb_vbden = $db->query("
			SELECT count(*) as C FROM (SELECT vb.ID_VBD
			FROM ".QLVBDHCommon::Table("vbd_vanbanden")." vb
			INNER JOIN ".QLVBDHCommon::Table("vbd_dongluanchuyen")." lc on lc.ID_VBD=vb.ID_VBD
			WHERE
				lc.NGUOINHAN = ? AND lc.DA_XEM=0
			GROUP BY vb.ID_VBD) t
			",$id_u)->fetch();
			$count_cdb_vbden = $count_cdb_vbden["C"];
			$html ="";
			if(count($cdb_vbden)>0){
				$html .="<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"home-grid\">
					<tr>
						<th nowrap>Số ký hiệu</th>
						<th width=\"100%\">Trích yếu</th>
						<th nowrap>Ngày ban hành</th>
						<th nowrap></th>
					</tr>";
				foreach ($cdb_vbden as $vbden_item){
				$html .="
					<tr>
						<td align=\"left\" nowrap><a href=
								\"/vbden/vbden/list/CHUA_DOC/1/ID_VBD/".$vbden_item['ID_VBD']."#vbden".$vbden_item['ID_VBD']."\">".$vbden_item["SOKYHIEU"]."</a></td>
						<td align=\"left\" width=\"100%\"><a href=
								\"/vbden/vbden/list/CHUA_DOC/1/ID_VBD/".$vbden_item['ID_VBD']."#vbden".$vbden_item['ID_VBD']."\">".htmlspecialchars($vbden_item["TRICHYEU"])."</a></td>
						<td align=\"left\" nowrap>".QLVBDHCOMMON::MysqlDateToVnDate($vbden_item["NGAYBANHANH"])."</td>
						<td align=\"left\" nowrap><a href=\"#\" onclick=\"loadDivFromUrl('vanbanden','/vbden/vbden/updatedadoc?year=".QLVBDHCommon::getYear()."&id_vbd=".$vbden_item['ID_VBD']."&id_u=".$id_u."&reload=1',1); return false;\">Đã xem</a></td>
					</tr>";
				}
				$html .= "</table>";
			}else{
				$html .= "
				<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"home-grid\">
				<tr><th style='text-align:left'>Không có văn bản đến mới</th></tr>
				</table>
				";
			}
			echo $html;
		}
		exit;	
	}
	
	function vbdilienquanAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$id_vbd = $params["id_vbd"];
		$year = QLVBDHCommon::getYear();
		$this->view->data = vbdenModel::getListVanbandiByIdVbden($id_vbd,$year);
		
	}
	function listallAction(){
	    $this->view->datalvvb = array();
		QLVBDHCommon::GetTree(&$this->view->datalvvb,"vb_linhvucvanban","ID_LVVB","ACTIVE=1 AND ID_LVVB_PARENT",1,1);
	   		
		global $auth;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		// 8/8/2018 vuld add link vbden 
        $SOKYHIEU = $param["SOKYHIEU"];
        if(isset($param["base64"])){
            $SOKYHIEU = base64_decode($param['SOKYHIEU']);   
        }
        //vuld end 
		//$param['TRICHYEU'] = Convert::ConvertToUnicode($param['TRICHYEU']);
		$config = Zend_Registry::get('config');
		$realyear = QLVBDHCommon::getYear();
		
		//tinh chỉnh param
		$IS_SEE_ALL = (int)$param['IS_SEE_ALL'];
		$ID_VBD 	= $param['ID_VBD'];
		$TRICHYEU 	= $param['TRICHYEU'];		
		$ID_CQ 		= $param['ID_CQ'];
		$ID_SVB 	= $param['ID_SVB'];
		$ID_LVVB 	= $param['ID_LVVB'];
		$ID_LVB 	= $param['ID_LVB'];
		$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
		$DOMAT 		= $param['DOMAT'];
		$DOKHAN 	= $param['DOKHAN'];	
		$SODEN		= $param['SODEN'];
		$IS_PHOBIEN		= $param['IS_PHOBIEN'];
		$SOKYHIEU	= $SOKYHIEU;
		$SODEN_ISLIKE	= $param['SODEN_ISLIKE'];
		$NGUOIXULY	= $param['NGUOIXULY'];
		$SORTBY	= $param['SORTBY'];
		if(!$SORTBY)
			$SORTBY = "NGAYDEN"; 
		$SORTTYPE	= $param['SORTTYPE'];
		if(!$SORTTYPE)
			$SORTTYPE ="DESC";
		
		$INNAME	= $param['INNAME'];
		$INFILE	= $param['INFILE'];
		if($param['INNAME']==0 && $param['INFILE']==0){
			$INNAME = 1;
		}
		if($param['NGAYDEN_BD']!=""){
			$ngayden_bd = $param['NGAYDEN_BD']."/".QLVBDHCommon::getYear();
			$ngayden_bd = implode("-",array_reverse(explode("/",$ngayden_bd)));
		}
		if($param['NGAYDEN_KT']!=""){
			$ngayden_kt = $param['NGAYDEN_KT']."/".QLVBDHCommon::getYear();
			$ngayden_kt = implode("-",array_reverse(explode("/",$ngayden_kt)));
		}
	    if($param['NGAYBANHANH_BD']!=""){
			$ngaybanhanh_bd = $param['NGAYBANHANH_BD']."/".QLVBDHCommon::getYear();
			$ngaybanhanh_bd = implode("-",array_reverse(explode("/",$ngaybanhanh_bd)));
		}
		if($param['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $param['NGAYBANHANH_KT']."/".QLVBDHCommon::getYear();
			$ngaybanhanh_kt = implode("-",array_reverse(explode("/",$ngaybanhanh_kt)));
		}	    
		
		if($param['CHUA_DOC']==1){
			$CHUA_DOC = $param['CHUA_DOC'];
		}else{
		
		}  
		$page = $param['page'];
		$limit = $param['limit1'];		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;		
		$scope = array();
		if($param['SCOPE']){
			$scope = $param['SCOPE'];
		}
		$parameter = array(
			
			"TRICHYEU"=>$TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
			"ID_CQ"=>$ID_CQ,
			"ID_SVB"=>$ID_SVB,
			"ID_LVVB"=>$ID_LVVB,
			"ID_LVB"=>$ID_LVB,
			"SODEN"=>$SODEN,
			"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
			"DOMAT"=>$DOMAT,
			"DOKHAN"=>$DOKHAN,		
			"NGAYDEN_BD"=>$ngayden_bd,
			"NGAYDEN_KT"=>$ngayden_kt,
		    "NGAYBANHANH_BD"=>$ngaybanhanh_bd,
			"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
			"ID_U"=>$user->ID_U,
			"SOKYHIEU"=>$SOKYHIEU,
			"IS_SEE_ALL" =>$IS_SEE_ALL, 
			"SCOPE"=>$scope,
			"INNAME"=>$INNAME,
			"INFILE"=>$INFILE,
			"SORTBY"=>$SORTBY,
			"SORTTYPE"=>$SORTTYPE,
			"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
			"CHUA_DOC" => $CHUA_DOC,
			"NGUOIXULY" => $NGUOIXULY
		);
		
		if($param['IS_PHOBIEN']==1){
			$IS_PHOBIEN = $param['IS_PHOBIEN'];
		}else{
		
		}  
		$page = $param['page'];
		$limit = $param['limit1'];		
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;		
		$scope = array();
		if($param['SCOPE']){
			$scope = $param['SCOPE'];
		}
		$parameter = array(
			
			"TRICHYEU"=>$TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
			"ID_CQ"=>$ID_CQ,
			"ID_SVB"=>$ID_SVB,
			"ID_LVVB"=>$ID_LVVB,
			"ID_LVB"=>$ID_LVB,
			"SODEN"=>$SODEN,
			"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
			"DOMAT"=>$DOMAT,
			"DOKHAN"=>$DOKHAN,		
			"NGAYDEN_BD"=>$ngayden_bd,
			"NGAYDEN_KT"=>$ngayden_kt,
		    "NGAYBANHANH_BD"=>$ngaybanhanh_bd,
			"NGAYBANHANH_KT"=>$ngaybanhanh_kt,
			"ID_U"=>$user->ID_U,
			"SOKYHIEU"=>$SOKYHIEU,
			"IS_SEE_ALL" =>$IS_SEE_ALL, 
			"SCOPE"=>$scope,
			"INNAME"=>$INNAME,
			"INFILE"=>$INFILE,
			"SORTBY"=>$SORTBY,
			"SORTTYPE"=>$SORTTYPE,
			"SODEN_ISLIKE"	=> $SODEN_ISLIKE,
			"CHUA_DOC" => $CHUA_DOC,
			"IS_PHOBIEN"=>$IS_PHOBIEN,
			"NGUOIXULY"=>$NGUOIXULY
		);
		

		if($SORTBY !="DAXEM"){
			if($SORTTYPE!=""){
				$sort = $SORTBY." ".$SORTTYPE;
			}else{
				$sort = $SORTBY;
			}
		}else{
			$sort = "NGAYDEN DESC";
		}
		
		//Tạo đối tượng
		$hscv = new hosocongviecModel();
		$hscvcount = $hscv->Count_vbd($parameter,1);
        
		//Lấy dữ liệu

		$this->view->data = $hscv->SelectAll_vbd($parameter,($page-1)*$limit,$limit,$sort,1);
		
		$this->view->realyear 		= $realyear;
		$this->view->TRICHYEU 		= $TRICHYEU;
		$this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
		$this->view->NGUOIXULY 		= $NGUOIXULY;
		$this->view->ID_CQ 			= $ID_CQ;
		$this->view->ID_SVB 		= $ID_SVB;
		$this->view->ID_LVVB 		= $ID_LVVB;
		$this->view->ID_LVB 		= $ID_LVB;
		$this->view->ID_VBD 		= $ID_VBD;
		$this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
		$this->view->DOMAT 			= $DOMAT;
		$this->view->DOKHAN 		= $DOKHAN;
		$this->view->NGAYDEN_BD 	= $param['NGAYDEN_BD'];
		$this->view->NGAYDEN_KT 	= $param['NGAYDEN_KT'];
		$this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
		$this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
		$this->view->SODEN 			= $param['SODEN'];
		$this->view->SOKYHIEU 			= $SOKYHIEU;
		$this->view->vbden = new vbdenModel(QLVBDHCommon::getYear());
		$this->view->SCOPE = $scope;
		$this->view->INNAME = $INNAME;
		$this->view->INFILE = $INFILE;
		$this->view->SORTBY = $SORTBY;
		$this->view->SORTTYPE = $SORTTYPE;
		$this->view->SODEN_ISLIKE = $SODEN_ISLIKE;
		$this->view->user = $user;
		$this->view->IS_SEE_ALL = $IS_SEE_ALL;
		$this->view->ADVANCED= $param['ADVANCEDVALUE'];
	    $this->view->CHUA_DOC = $CHUA_DOC;
		$this->view->IS_PHOBIEN = $IS_PHOBIEN;

		$ID_LOAIHSCV = 1;
	    $createarr = WFEngine::GetCreateProcessButtonFromLoaiCV($ID_LOAIHSCV,$user->ID_U);
            QLVBDHButton::EnableVbdChuyen('');
		if(count($createarr)>0){
			QLVBDHButton::AddButton("Thêm mới","","AddNewButton","CreateButtonClick(\"/vbden/vbden/input/type/1\")");
		}
		//$this->view->arr_idnews = vbd_dongluanchuyenModel::getIdVbdenChuaXemByIdUser($realyear,$user->ID_U);
		//var_dump($this->view->arr_news);	
		//page
		$this->view->id_lvvb=$param['ID_LVVB'];
		$this->view->title = "Danh sách văn bản đến toàn cơ quan";
		//$this->view->subtitle = "Danh sách văn bản toàn cơ quan";
		$this->view->config = $config;
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbden/vbden/listall");
                
	}

	function getsvbbycoquannhanAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$arr_svb = vbdenModel::GetSVBByCoQuanNhan($params["COQUANNHAN"]);
		//echo $params["COQUANNHAN"];
		//var_dump($arr_svb);
		//$arr = array ('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);

		echo json_encode($arr_svb);
		exit;
	}
    function kiemtravanbandenAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $trichyeu = $params["TRICHYEU"];
        $sokyhieu = $params["SOKYHIEU"];
        $ketqua = 0;

        $trichyeudmbatbuoc = vbdenModel::getAllDanhMucBatBuoc(1);
        $sokyhiedmbatbuoc = vbdenModel::getAllDanhMucBatBuoc(2);
        foreach ($trichyeudmbatbuoc as $itemtrichyeudmbatbuoc) {
            if (mb_strpos(mb_strtolower($trichyeu, 'UTF-8'), mb_strtolower($itemtrichyeudmbatbuoc["LIKE_NAME"], 'UTF-8')) !== false) {
                $ketqua = 1;
                break;
            }
        }
        foreach ($sokyhiedmbatbuoc as $itemsokyhiedmbatbuoc) {

            if (mb_strpos(mb_strtolower($sokyhieu, 'UTF-8'), mb_strtolower($itemsokyhiedmbatbuoc["LIKE_NAME"], 'UTF-8')) !== false) {
                $ketqua = 1;
                break;
            }
        }
        echo json_encode($ketqua);
        exit;
    }
	function chuyenxllaivtbpAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$this->view->id = $params['id'];
	}
	function savechuyenxllaivtbpAction(){
		global $auth;
		global $db;
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		
		// xu ly truong hop chua co HSCV
		if($params["ishscvnull"]==1){
			//Lấy thông tin văn bản đến
			$vbd = $db->query("SELECT TRICHYEU, NGAYDEN FROM ".QLVBDHCommon::Table("VBD_VANBANDEN")." WHERE ID_VBD=?",$params["id"])->fetch();
			// thêm mới HSCV
			$hscv = new hosocongviecModel();
			$fkvbd = new fk_vbden_hscvsModel(QLVBDHCommon::getYear());
			$id_hscv = $hscv->CreateHSCV(
				$vbd["TRICHYEU"],1,WFEngine::GetIdLoaiHSCVFromIdT($params["wf_id_t"]),
				$vbd["NGAYDEN"],
				$vbd["NGAYDEN"],
				$auth->getIdentity()->ID_U,
				$params["ID_U_BP"],
				"",
				0
			);
			if($id_hscv>0){
			$fkvbd->insert(array("ID_VBDEN"=>$params["id"],"ID_HSCV"=>$id_hscv));
			}else{
			}
		}

		$hscvall = vbdenModel::GetAllHSCV($params["id"]);
		
		//roolback het cac hscv co the roolback duoc
		$hscv = new hosocongviecModel();
		
		foreach($hscvall as $itemhscv){
			if(WFEngine::CanChuyenLaiForVTBP($itemhscv["ID_HSCV"])){
				$lastlc = WFEngine::GetCurrentTransitionInfoByIdHscv($itemhscv["ID_HSCV"]);
				$ok = $hscv->rollback($itemhscv["ID_HSCV"],$lastlc["ID_U_NC"]);
				if($ok==1){
					$hscv->update(array("ID_THUMUC"=>1),"ID_HSCV=".$itemhscv["ID_HSCV"]);
				}else if($ok==2){
					$hscv->getDefaultAdapter()->delete(QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS"),"ID_HSCV=".$itemhscv["ID_HSCV"]);
				}else{
				}
			}
		}
		
		//chuyen but phe
		$hscvall = vbdenModel::GetAllHSCV($params["id"]);
		foreach($hscvall as $itemhscv){
			if(WFEngine::CanBP($itemhscv["ID_HSCV"])){
				WFEngine::ChangeUBP($itemhscv["ID_HSCV"],$params["ID_U_BP"]);
				$lastlc = WFEngine::GetCurrentTransitionInfoByIdHscv($itemhscv["ID_HSCV"]);
				$id = $itemhscv["ID_HSCV"];
				$xltrans = WFEngine::GetNextTransitionsByTransition($lastlc["ID_T"]);
				$wf_id_t = $xltrans["ID_T"];
				$idumain = $lastlc["ID_U_NK"];
				break;
			}
		}
		$user = $auth->getIdentity();
		
		$parameter = $this->getRequest()->getParams();
		
		$idu = $parameter["ID_U"];
		$hanxuly = $parameter["HANXULY"];
		$type = $parameter["TYPE"];
		$noidung = $parameter["NOIDUNG"];
		$istheodoi = $parameter["istheodoi"];
		
		$idusend = array();
		$hanxulysend = array();
		$noidungsend = array();
		$idudb = array();
		for($i=0;$i<count($idu);$i++){
			if($type[$i]==2){
				$idusend[] = $idu[$i];
				$hanxulysend[] = $hanxuly[$i];
				$noidungsend[] = $noidung;
			}else if($type[$i]==1){
				$idudb[] = $idu[$i];
			}else{
				$iduph[] = $idu[$i];
				$idudb[] = $idu[$i];
			}
		}
		
		$processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($id);
		$usernk = $processinfo["ID_U_NK"];
		$usernc = $processinfo["ID_U_NC"];
		$vbd = new vbdenModel(QLVBDHCommon::getYear());
        $vbden = $vbd->findByHscv($id);
		//insert vao butphe
		if($noidung!=""){
			$db->delete(QLVBDHCommon::Table("HSCV_BUTPHE"),"ID_VBD=".$vbden['ID_VBDEN']);
			$db->insert(QLVBDHCommon::Table("HSCV_BUTPHE"),array(
				"NOIDUNG"=>$noidung,
				"NGUOIKY"=>$usernk,
				"NGUOICHUYEN"=>$usernc,
				"ID_VBD"=>$vbden['ID_VBDEN'],
				"NGAYBP"=>date("Y-m-d H:i:s")
			));
		}
		$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
		$lc->send($vbden['ID_VBDEN'],$idudb,$noidung,$idumain);
		if(count($idusend)>0){
			try{
	           	hosocongviecModel::SendAll(
	            	$id,
	            	$idumain,
	            	$wf_id_t,
	            	array(),
	            	array(),
	            	array(),
	            	array(),
	            	array(),
	            	array(),
	            	$idusend,
	            	$noidungsend,
	            	$hanxulysend,
	            	$vbden['ID_VBDEN'],
	            	"ID_VBDEN",
	            	"VBD_FK_VBDEN_HSCVS"
	           	);
	           	$lc->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_THEODOI"=>0),"ID_HSCV=".(int)$id);
			}catch(Exception $ex){
				echo $ex->__toString();
			}
		}else{
			if($istheodoi==1){
				HosoluutheodoiModel::luuTheodoi(QLVBDHCommon::getYear(),$id,"",$idumain);
			}else{
				$lc->getDefaultAdapter()->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_THEODOI"=>0),"ID_HSCV=".(int)$id);
			}
		}
		if(count($iduph)>0){
			PhoiHopModel::AddPhoiHop($iduph,$vbden['ID_VBDEN']);
		}
		echo "<script>window.parent.document.frm.submit();</script>";
		//var_dump($idusend);
		
		exit;
	}
	function inputchuyenxulylanhdaoAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$this->view->id = $params['id'];
		
		// Lấy danh sách hồ sơ công việc mà chuyển nhiều
		$arr_id = array();
		$hscv = vbdenModel::GetAllHSCV($params["id"]);
		foreach($hscv as $itemhscv){
			$arr_id[] = $itemhscv['ID_HSCV'];
		}

		//nếu số lượng > 1 thì thông báo
		if(count($arr_id)!=1){
			echo "<script>alert('Văn bản này không thể chuyển giúp lãnh đạo.');</script>";exit;
		}else{

			global $auth;
			$this->view->idhscv=$arr_id[0];
			$user = $auth->getIdentity();
			$this->view->wf_id_t = WFEngine::GetBeginTransition('VBD',$user->ID_DEP);
			$this->view->currenttrans = WFEngine::GetCurrentTransitionInfoByIdHscv($arr_id[0]);
			$next_trs = WFEngine::GetAllNextTransitionsByTransition($this->view->currenttrans["ID_T"]);
			$canexecute = false;
			foreach($next_trs as $xltransitem){
				if($xltransitem["ALIAS"]=="BUTPHE" && $xltransitem["ID_A_BEGIN"] == $xltransitem["ID_A_END"]){
					$canexecute=true;
				}
			}
			if(!$canexecute){echo "<script>alert('Văn bản này không thể chuyển giúp lãnh đạo.');</script>";exit;}
			$next_trs = WFEngine::GetNextTransitionsByTransition($this->view->wf_id_t);
			$this->view->hanxuly = WFEngine::GetHanXuLy($next_trs["ID_T"]);
		}
	}
	function savechuyenxulylanhdaoAction(){
		global $auth;
		$user = $auth->getIdentity();
		$this->view->parameter = $this->_request->getParams();
		$parameter = $this->_request->getParams();

		$arr_id_u_bp_old = $this->view->parameter["ID_U_BP"];
		$arr_noidung_bp_old = $this->view->parameter["NOIDUNG_BP"];
		$arr_ngay_bp_old = $this->view->parameter["NGAYBUTPHE"];
		
		$arr_id_u_bp= array();
		$arr_noidung_bp= array();
		$arr_ngay_bp= array();

		if(is_array($arr_id_u_bp_old)){
			// làm mới lại danh sách but phê vì có khi hắn trống
			for($loopbp=0;$loopbp<20;$loopbp++){
				if($arr_id_u_bp_old[$loopbp]>0){
					$arr_id_u_bp[] = $arr_id_u_bp_old[$loopbp];
					$arr_noidung_bp[] = $arr_noidung_bp_old[$loopbp];
					$arr_ngay_bp[] = $arr_ngay_bp_old[$loopbp];
				}
			}
		}
		$id_hscv = $this->view->parameter["idhscv"];
		$idvbd = $this->view->parameter["idvbd"];
		$idhscv = $this->view->parameter["idhscv"];
		// Chuyen but phe nhieu lan trừ dùng cuối cùng
		if(is_array($arr_id_u_bp_old)){
			$vbd = new vbdenModel(QLVBDHCommon::getYear());
			for($loopbp=0;$loopbp<count($arr_id_u_bp)-1;$loopbp++){
				WFEngine::SendNextUserByObjectId($id_hscv
					,$this->view->parameter["wf_id_t_butphe"]
					,$arr_id_u_bp[$loopbp]
					,$arr_id_u_bp[$loopbp+1]
					,$arr_noidung_bp[$loopbp]
					,0);
				// Lưu bút phê
				//insert vao butphe
				if($arr_noidung_bp[$loopbp]!=""){
					$vbd->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_BUTPHE"),array(
						"NOIDUNG"=>$arr_noidung_bp[$loopbp],
						"NGUOIKY"=>$arr_id_u_bp[$loopbp],
						"NGUOICHUYEN"=>$user->ID_U,
						"ID_VBD"=>$idvbd,
						"ID_HSCV"=>$id_hscv,
						"NGAYBP"=>implode("-", array_reverse( explode("/",$arr_ngay_bp[$loopbp])))
					));
				}
			}
		}
		
		//Trả lại như cũ để xử lý dòng cuối cùng
		if(is_array($arr_id_u_bp_old)){
			$parameter["NOIDUNG_BP"] = $arr_noidung_bp[count($arr_noidung_bp)-1];
			$parameter["ID_U_BP"] = $arr_id_u_bp[count($arr_noidung_bp)-1];
		}

		//if(trim($parameter["NOIDUNG_BP"])!="" || count($parameter["ID_U_XL"])>0){
		//chuyen xu ly
		
		$idu = $parameter["ID_U_XL"];
		$sms = $parameter["SMS"];
		$email = $parameter["EMAIL"];
		//var_dump($email);exit;
		$hanxuly = $parameter["HANXULY"];
		$type = $parameter["TYPE_XL"];
		$id = $id_hscv;
		$wf_id_t = $parameter["wf_id_t"];
		$noidung = $parameter["NOIDUNG_BP"];
		$istheodoi = $parameter["istheodoi"];
		
		$vbd = new vbdenModel(QLVBDHCommon::getYear());
		//insert vao butphe
		if($noidung!=""){
			$vbd->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_BUTPHE"),array(
				"NOIDUNG"=>$noidung,
				"NGUOIKY"=>$parameter["ID_U_BP"],
				"NGUOICHUYEN"=>$user->ID_U,
				"ID_VBD"=>$idvbd,
				"ID_HSCV"=>$id_hscv,
				"NGAYBP"=>date("Y-m-d H:i:s")
			));
		}
		$idusend = array();
		$sendsms = array();
		$sendemail = array();
		$hanxulysend = array();
		$noidungsend = array();
		$idudb = array();
		$iduph = array();
		$dbsms = array();
		$dbemail = array();
		for($i=0;$i<count($idu);$i++){
			if($type[$i]==2){
				$idusend[] = $idu[$i];
				$sendsms[] = $sms[$i];
				$sendemail[] = $email[$i];
				$hanxulysend[] = $hanxuly[$i];
				$noidungsend[] = $noidung;
			}else if($type[$i]==1){
				$idudb[] = $idu[$i];
				$dbsms[] = $sms[$i];
				$dbemail[] = $email[$i];
			}else{
				$iduph[] = $idu[$i];
				$idudb[] = $idu[$i];
				$dbsms[] = $sms[$i];
				$dbemail[] = $email[$i];
			}
		}

		$idudb[] = $user->ID_U;
		$dbsms[] = 0;
		$dbemail[] = 0;

		$processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
		$usernk = $processinfo["ID_U_NK"];
		$usernc = $processinfo["ID_U_NC"];
		
		$vbd = new vbdenModel(QLVBDHCommon::getYear());

		$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
		//var_dump($idvbd);exit;
		$lc->send($idvbd,$idudb,$noidung,$parameter["ID_U_BP"],0,null,$dbsms,$dbemail);
		if(count($idusend)>0){
			try{
				hosocongviecModel::SendAll(
					$id,
					$parameter["ID_U_BP"],
					$wf_id_t,
					array(),
					array(),
					array(),
					array(),
					array(),
					array(),
					$idusend,
					$noidungsend,
					$hanxulysend,
					$idvbd,
					"ID_VBDEN",
					"VBD_FK_VBDEN_HSCVS",
					$sendsms,
					$sendemail
				);
			}catch(Exception $ex){
				echo $ex->__toString();
			}
			
		}else{

		}
		if(count($iduph)>0){
			PhoiHopModel::AddPhoiHop($iduph,$idvbd,$parameter["ID_U_BP"]);
		}
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
	}
    /**
    * @purpose : Cập nhật trạng thái liên thông văn bản lên trung tập tích hợp dữ liệu
    * @author : baotq
    * @update : 24/06/2013 Phan Thiết - Bình Thuận
    * @parameter : array $config 
    * @parameter : integer $masolienthong 
    */
    private function updateTrangThaiLienThong($config,$masolienthong){
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session    = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
            global $auth;        
            $user = $auth->getIdentity();
            $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
            $params = array(
            'session'       => $session,
            'service_code'  => 'TRANGTHAI',
            'service_name'  => 'UPDATEFORDONVINHAN',
            'parameter'     => base64_encode($masolienthong) . '~' . base64_encode(3). '~' . base64_encode($code)
            );
            $cliente->__call('Execute', $params);
        }catch (Exception $ex){
            
        }
    }
    /**
    * @purpose : tạo mã số văn bản
    * @author : baotq
    * @update : 24/06/2013 Phan Thiết - Bình Thuận
    * @parameter : array $parameter 
    */
    private function getMaSoVanBan($parameter){
        $vbden = new vbden();
        $vbden->_id_lvb = $parameter['ID_LVB'];
        $vbden->_id_cq = $parameter['ID_CQ'];
        $vbden->_soden = $parameter['SODEN_IN'];
        $vbden->_domat = $parameter['DOMAT'];
        $vbden->_dokhan = $parameter['DOKHAN'];
        $vbden->_ngaybanhanh = $parameter['NGAYBANHANH'];
        return Common_Maso::getMaSo(1,$vbden);
    }
    
    /**
    * @purpose : Tiếp nhận văn bản đến, lưu nháp
    * @author : baotq
    * @update : 24/06/2013 Phan Thiết - Bình Thuận
    * @param : array $params 
    */
    private function saveDraft($params){
		global $auth;
        $year = QLVBDHCommon::getYear();
        $db = Zend_Db_Table::getDefaultAdapter();
		$user = $auth->getIdentity();
		$idu = $user->ID_U;
		if($idu<=0){
			for($i=0;$i<10;$i++){
				$user = $auth->getIdentity();
				$idu = $user->ID_U;
				if($idu>0)break;
			}
		}
        try{
			if($params["COQUANNHAN"]==NULL){
				$cqid=vbdenModel::GetDataCQN_ID();
			}else{
				$cqid=$params["COQUANNHAN"];
			}
            $idvbd=$params["idvbdraft"];
            $data = array(
            "ID_SVB"=>(int)$params["ID_SVB"],
            "NGUOITAO"=>$idu,
            "ID_LVVB"=>(int)$params["ID_LVVB"],
            "ID_CQ"=>(int)$params["ID_CQ"],
            "ID_LVB"=>(int)$params["ID_LVB"],
            "SOKYHIEU"=>$params["SOKYHIEU"],
            "SODEN"=>$params["SODEN"],
            "NGAYDEN"=>implode("-",array_reverse(explode("/",$params["NGAYDEN"]))),
            "NGAYBANHANH"=>implode("-",array_reverse(explode("/",$params["NGAYBANHANH"]))),
            "NGAYTAO"=>Date("Y-m-d"),
            "COQUANBANHANH_TEXT"=>QLVBDHCommon::makeCQName($params["COQUANBANHANH_TEXT"]),        	
            "COQUANNHAN"=>$cqid,
            "TRICHYEU"=>$params["TRICHYEU"],
            "SOBAN"=>(int)$params["SOBAN"],
            "SOTO"=>(int)$params["SOTO"],
            "DOMAT"=>(int)$params["DOMAT"],
            "DOKHAN"=>(int)$params["DOKHAN"],
            "NGUOIKY"=>$params["NGUOIKY"],
            "SODEN_IN"=>(int)$params["SODEN_IN"],
            "SOKYHIEU_IN"=>(int)$params["SOKYHIEU_IN"],
            );
            if($idvbd > 0){
                $db->update(QLVBDHCommon::table("vbd_vanbanden_draft"),$data," ID_VBD = ".$idvbd);
            }else{
                $idvbd = $db->insert(QLVBDHCommon::table("vbd_vanbanden_draft"),$data);
            }
            // insert file đính kèm
            $idvbd=$db->lastInsertId();
            $this->filedinhkem = new filedinhkemModel($year);
            for($i=0;$i<count($params["idFile"]);$i++){
                $this->filedinhkem->update(array("ID_OBJECT"=>$idvbd,"TYPE"=>1003),"MASO='".$params["idFile"][$i]."'");
            }
        }catch(Exception $ex){
            echo $ex->getMessage();
            exit;
        }
    }
	public function deletedraftAction(){
		global $db;
		
		$params = $this->_request->getParams();
		$idvbdraft = $params["idvbdraft"];
		$db->query("delete from ".QLVBDHCommon::Table("vbd_vanbanden_draft")." where ID_VBD=?",$idvbdraft);
		$this->_redirect("/vbden/vbden/listdraft");
	}
}
