<?php
/**
 * @author trunglv
 * @version 1.0
 * @des Chuyen xu ly (chuyen ban hanh) ho so cong viec
 */
require_once ('Zend/Controller/Action.php');
require_once 'hscv/models/ToTrinhModel.php';
require_once 'hscv/models/ChuyenXuLyModel.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'hscv/models/hosocongviecModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/HosoluutheodoiModel.php';
require_once('qtht/models/nguoidungModel.php');
require_once('motcua/models/motcua_hosoModel.php');
require_once 'vbden/models/vbdenModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once('qtht/models/UsersModel.php');
require_once 'hscv/models/GopYModel.php';
require_once('qtht/models/SoVanBanModel.php');
require_once ('motcua/models/DongboHSMCModel.php');
require_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'traodoicongviec/models/traodoicongviecModel.php';
require_once 'giamsatgiaoviec/models/GiamSatGiaoViecModel.php';
require_once "giaoviec/models/giaoviecservice.php";
require_once 'qtht/models/DepartmentsModel.php';
class Hscv_chuyenxulyController extends Zend_Controller_Action {
	/**
	 * Ham khoi tao cho cac action
	 *
	 */
	function init(){
		//disable layout
		$this->_helper->layout->disableLayout();
	}
	/**
	 * action : index 
	 * Hien form nhap lieu de chuyen xu ly
	 */
	function indexAction(){
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$idHSCV = $this->parameter["id"];

		$year = $this->parameter["year"];
		$code= $this->parameter["code"];
		$id_loaihscv = $this->parameter["type"];
//chuyen du lieu cho lop view
		//tao lop model to trinh
		$totrinhModel = new ToTrinhModel($year);
		//lay danh sach co to trinh tuong ung voi ho so cong viec
		$this->view->data = $totrinhModel->getListToTrinhByIdHSCV($this->parameter["id"]);
		$this->view->idHSCV = $idHSCV;
		$this->view->id_loaihscv = $id_loaihscv;
		$this->view->code = $code;
		$this->view->wf_id_t = $this->parameter["wf_id_t"]; //wf id transition
		$this->view->ID_HSCV = $idHSCV;
                $hscv = hosocongviecModel::getDetailById($idHSCV);
                $macongviec = $hscv['MACONGVIEC'];
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $giaoviecservice = new GiaoViecService();
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $reCongViec = $giaoviecservice->SelectCongViecByMacongviec(
                        $token
                        ,$macongviec
                );
                $this->view->congviec = json_decode($reCongViec);
            //    var_dump($this->view->congviec->data);exit;
		$this->view->year = $year;
		if($code == "banhanh"){
			$this->view->isBanhanh = 1; // truong hop chuyen ban hanh van ban
			$this->view->canBanhanh = 1;
			$duthao = new VanBanDuThaoModel(QLVBDHCommon::getYear());
			if(count($duthao->getListByIdHSCV($idHSCV))<=0){
				$this->view->canBanhanh = 0;
			}
			
		}else{
			$this->view->isBanhanh = 0; // truong hop chuyen xu ly cong vien
		}
//Lay thong tin ve cac loai ho so (van ban den, soan thao van ban,ho so mot cua)
		$this->view->loaihs_des = ChuyenXuLyModel::getObjectByIdHSCV($idHSCV,$id_loaihscv,$year);

				
	}
	/**
	 * action : save
	 * Lưu tờ trình và chuyển xử lý cho người tiếp theo (work flow)
	 */
	function saveAction(){
/**
 * mota :
 * soan thao van ban :
 *  - chuyen xu ly
 *  - chuyen ban hanh -> cap nhat lai trang thai cac du thao trong ho so con viec
 *  
 * van ban den :
 * 	- chuyen xu ly
 *  - chuyen ban hanh du thao -> cap nhat lai trang thai cac du thao trong ho so con viec
 * ho so mot cua:
 *  - chuyen xu ly
 * luu to trinh cho cac lan chuyen ban hanh va chuyen xu ly
 */		
		//lay bien $auth tu registry 
		$auth = Zend_Registry::get('auth');
		//lay default db adapter
		$db = Zend_Db_Table::getDefaultAdapter();
		$user = $auth->getIdentity(); //lay thong tin cua user trong session
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		
		$idhscv = $param["id"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		$wf_nextg = $param["wf_nextg"];
		$sms = $param["wf_sms"];
		$email = $param["wf_email"];
		$wf_nextdep = $param["wf_nextdep"];
		$wf_name_user = $param["wf_name_user"];
		$wf_name_g = $param["wf_name_g"];
		$wf_name_dep = $param["wf_name_dep"];
		$wf_hanxuly_user = $param["wf_hanxuly_user"];
		$wf_hanxuly_g = $param["wf_hanxuly_g"];
		$wf_hanxuly_dep = $param["wf_hanxuly_dep"];
		
		$year = $param["year"];
        $id_u_nn = hosocongviecModel::getIDNguoiNhanByIdHSCV($idhscv,$user->ID_U);
        if((int)$id_u_nn > 0 ){
            $this->addPhoiHop($user->ID_U,$id_u_nn,$idhscv);
        }
        
        // set nam trong registry
		Zend_Registry::set('year',$year);
		$db->beginTransaction();
		try{
			//send next user , neu thanh cong thi luu to trinh vao co so du lieu
			if( !is_array($param["wf_nextuser"]) &&
            	!is_array($param["wf_nextg"]) &&
            	!is_array($param["wf_nextdep"])
            ){ 
                            $config = Zend_Registry::get('config'); 
                            $TraoDoiCongViecModel = new TraoDoiCongViecModel($year);
                            $giamSatGiaoViecModel = new GiamSatGiaoViecModel();
                            $checkIsGiaoViec = $giamSatGiaoViecModel->checkIsGiaoViec((int)$idhscv);
                            if((int)$checkIsGiaoViec==1 && (int)$param["isBanhanh"]==1 && (int)$param["TIENDO_GIAOVIEC"]<100){ 
                                $db->update(QLVBDHCommon::Table('hscv_hosocongviec'),array('TIENDO_GIAOVIEC'=>(int)$param["TIENDO_GIAOVIEC"]),'ID_HSCV = '.(int)$idhscv);
                                //cập nhật tiến độ trên services nếu có
                                $DLCLIENTHONG = $TraoDoiCongViecModel->getMaSoDongLuanChuyenLt($idhscv);
                                if ((int)$DLCLIENTHONG > 0) {
                                    $wsdl = $config->service->lienthong->uri;
                                    $username = $config->service->lienthong->username;
                                    $password = $config->service->lienthong->password;
                                    $cliente = new SoapClient($wsdl);
                                    $session = $cliente->__call('Login', array(
                                        'madonvi' => $username,
                                        'password' => $password));
                                    $paramsData = array(
                                        'session' => $session,
                                        'service_code' => 'TRAODOIGSCV',
                                        'service_name' => 'CAPNHATTIENDO',
                                        'parameter' => base64_encode($DLCLIENTHONG) . '~' . base64_encode($param["TIENDO_GIAOVIEC"])
                                    );
                                    $response = $cliente->__call('Execute', $paramsData);
                                }
                                $TraoDoiCongViecModel->getDefaultAdapter()->insert(
                                    QLVBDHCommon::Table("gscv_tiendo_log"), 
                                    array(
                                        'ID_HSCV' => $idhscv,
                                        "TIENDO" => $param["TIENDO_GIAOVIEC"],
                                        'MOTA'=> 'chuyển ban hành',
                                        'NGAYCAPNHAT'=>date('Y-m-d')
                                    )
                                );
                                //end cập nhật tiến độ trên services nếu có                                
                                $VBDTModel = new VanBanDuThaoModel($year);
                                $VBDTModel->updateTrangthaiByIdHSCVChonBanHanh($idhscv,1);	
                                $VBDTModel->updateNguoiKyByIdHSCV($idhscv,$user->ID_U);	
                                $type = (int)$param["type"];
                                $classalias = WFEngine::GetClassNameFromObjectId($idhscv);
                                $curtrans = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
                                if($classalias == "MOTCUA" && $curtrans["ISLAST"]==1){
                                        //if(ChuyenXuLyModel::isAlowaHSMC($wf_nextuser) == 1)
                                                ChuyenXuLyModel::updatePCMTraHSMC($idhscv);
                                                DongboHSMCModel::updateTrangthaiByIdHSCV($idhscv,2);
                                }
                                $db->commit();	
                            }else{ 
                                $db->update(QLVBDHCommon::Table('hscv_hosocongviec'),array('TIENDO_GIAOVIEC'=>(int)$param["TIENDO_GIAOVIEC"]),'ID_HSCV = '.(int)$idhscv);
                                if(WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$user->ID_U,$wf_nextuser,WFEngine::$WFTYPE_USER,$wf_name_user,$wf_hanxuly_user,$sms,$email)==1){
					$isBanhanh = $param["isBanhanh"];	
					if($isBanhanh == 1){
                                                //cập nhật tiến độ trên services nếu có
                                                $DLCLIENTHONG = $TraoDoiCongViecModel->getMaSoDongLuanChuyenLt($idhscv);
                                                if ((int)$DLCLIENTHONG > 0) {
                                                    $wsdl = $config->service->lienthong->uri;
                                                    $username = $config->service->lienthong->username;
                                                    $password = $config->service->lienthong->password;
                                                    $cliente = new SoapClient($wsdl);
                                                    $session = $cliente->__call('Login', array(
                                                        'madonvi' => $username,
                                                        'password' => $password));
                                                    $paramsData = array(
                                                        'session' => $session,
                                                        'service_code' => 'TRAODOIGSCV',
                                                        'service_name' => 'CAPNHATTIENDO',
                                                        'parameter' => base64_encode($DLCLIENTHONG) . '~' . base64_encode(100)
                                                    );
                                                    $response = $cliente->__call('Execute', $paramsData);
                                                }
                                                $TraoDoiCongViecModel->getDefaultAdapter()->insert(
                                                    QLVBDHCommon::Table("gscv_tiendo_log"), 
                                                    array(
                                                        'ID_HSCV' => $idhscv,
                                                        "TIENDO" => 100,
                                                        'MOTA'=> 'chuyển ban hành',
                                                        'NGAYCAPNHAT'=>date('Y-m-d')
                                                    )
                                                );
                                                //end cập nhật tiến độ trên services nếu có
						//cap nhat lai trang thai cua cac du thao van ban trong ho so con viec
						$VBDTModel = new VanBanDuThaoModel($year);
						$VBDTModel->updateTrangthaiByIdHSCVChonBanHanh($idhscv,1);	
						$VBDTModel->updateNguoiKyByIdHSCV($idhscv,$user->ID_U);				
					}
					$type = (int)$param["type"];
					$classalias = WFEngine::GetClassNameFromObjectId($idhscv);
					$curtrans = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
					if($classalias == "MOTCUA" && $curtrans["ISLAST"]==1){
						//if(ChuyenXuLyModel::isAlowaHSMC($wf_nextuser) == 1)
							ChuyenXuLyModel::updatePCMTraHSMC($idhscv);
							DongboHSMCModel::updateTrangthaiByIdHSCV($idhscv,2);
					}
					
					$db->commit();	
				}				
                            }
					try{
						if($isBanhanh == 1){
							$vbd = new vbdenModel(QLVBDHCommon::getYear());
							$vbden = $vbd->findByHscv($idhscv);
							if($vbden['ID_VBDEN']>0){
								$db->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>QLVBDHCommon::getTreHan($vbden['NGAYTAO'],$vbden['HANXULYTOANBO']),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
							}
						}
					}catch(Exception $ex){
						echo $ex;exit;
					}
            }else{
            	$vbd = new vbdenModel(QLVBDHCommon::getYear());
           		$vbden = $vbd->findByHscv($idhscv);
            	hosocongviecModel::SendAll(
	            	$idhscv,
	            	$user->ID_U,
	            	$wf_nexttransition,
	            	$wf_nextg,
	            	$wf_name_g,
	            	$wf_hanxuly_g,
	            	$wf_nextdep,
	            	$wf_name_dep,
	            	$wf_hanxuly_dep,
	            	$wf_nextuser,
	            	$wf_name_user,
	            	$wf_hanxuly_user,
	            	$vbden['ID_VBDEN'],
	            	"ID_VBDEN",
	            	"VBD_FK_VBDEN_HSCVS"
            	);
            	$type = (int)$param["type"];
				if($type >= 3){
					if(ChuyenXuLyModel::isAlowaHSMC($wf_nextuser) == 1)
						ChuyenXuLyModel::updatePCMTraHSMC($idhscv);
				}
				$isBanhanh = $param["isBanhanh"];
           		if($isBanhanh == 1){
					//cap nhat ngay phong chuyen mon tra hs mot cua
					
					//cap nhat lai trang thai cua cac du thao van ban trong ho so con viec
					$VBDTModel = new VanBanDuThaoModel($year);
					$VBDTModel->updateTrangthaiByIdHSCVChonBanHanh($idhscv,1);	
					$VBDTModel->updateNguoiKyByIdHSCV($idhscv,$user->ID_U);
					$db->update("WF_PROCESSITEMS_".QLVBDHCommon::getYear(),array("IS_FINISH"=>1),"ID_O = ".$id);

				}
				$db->commit();
            }
		}catch(Exception $ex){
			$db->rollBack();
		}
		
		//truong hop ho so khong xu ly
		$code = $param["code"];
		
		if($code == "mc_khongxl"){
			//update
			try{
				ChuyenXuLyModel::updateKhongxulyMotcua($idhscv);
			}catch(Exception $ex){
			
			}
		}
		if($param['macongviec']!= '' || $param['macongviec'] != NULL)
                {
                    $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                    $madonvi = $configlienthong->service->lienthong->username;
                    $password = $configlienthong->service->lienthong->password;
                    $giaoviecservice = new GiaoViecService();
                    $model = new UsersModel();
                    $nguoinhan = $model->getName($param['wf_nextuser']);
//                    $user_dep = UsersModel::getUserDepId($param['wf_nextuser']);
//                    $noidungchuyen = 'Trình ban giám đốc';
//                    if($isBanhanh ==1){
//                       $noidungchuyen = 'Chuyển ban hành'; 
//                    }
                    $token = $giaoviecservice->login($madonvi,md5($password),"");
//                    $giaoviecservice->createNhatKy(
//                            $token
//                            ,$param['macongviec']
//                            ,$param['wf_nextuser']
//                            ,$nguoinhan['TENNGUOITAO']
//                            ,$param['TIENDO_GIAOVIEC']
//                            ,$param['motatiendo'] != '' ? $parameter['motatiendo'] : $noidungchuyen
//                            ,$user_dep['NAME']
//                    );
                     $giaoviecservice->UpdateNguoiXLCongViec(
                                     $token
                                    ,$param['macongviec']
                                    ,$param['wf_nextuser']
                                    ,$nguoinhan['TENNGUOITAO']
                                    ,$param['TIENDO_GIAOVIEC']
				);
//					$giaoviecservice->UpdateTienDoCV(
//						$token
//						,$param['macongviec']
//						,$param['TIENDO_GIAOVIEC']
//					); 
                }
		
		//tra lai doan js de thu thi tai client
		if(WFEngine::GetClassNameFromObjectId($idhscv)=="MOTCUA"){
			if(hosocongviecModel::isfinishhosocv($idhscv)){
				$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
				$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
				QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,3,"Mời Ông/Bà đến nhận kết quả.",$hoso->DIENTHOAI);
			}else{
				$actid = ResourceUserModel::getActionByUrl('motcua','MotCua','savetrahoso');
				$nguoidung = new nguoidungModel();
				$nguoidung = $nguoidung->FindById($param["wf_nextuser"]);

				if(wfengine::IsFinishTransition($wf_nexttransition)){
					$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
					$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
					//QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,3,"Mời Ông/Bà đến nhận kết quả.",$hoso->DIENTHOAI,null);
				}else{
					$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
					$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
									
					$newphong = new motcua_hosoModel();
					$phong = $newphong->ws_getphong($nguoidung['ID_U']);		
					QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,1,"Đang xử lý",$hoso->DIENTHOAI,$phong);			
				
				}
			}
		}

		//phien ban du thao ko cho xoa sau khi chuyen
			
			$dt = new PhienBanDuThaoModel(QLVBDHCommon::getYear());
			$id_pb = $dt->getidphienbanbyidhscv($idhscv);
			foreach($id_pb as $id_pbitem)
			$db->update("HSCV_PHIENBANDUTHAO_" . $year,array("IS_XOA"=>0),"ID_DUTHAO='".(int)$id_pbitem['ID_DUTHAO']."' AND ID_U ='".$user->ID_U."'");

		//tra lai doan js de thu thi tai client
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
	}
	
	function banhanhAction(){
		$param = $this->_request->getParams();
		$wf_id_t = $param["wf_id_t"]; //wf id transition
		$idHSCV = $param["id"];
		$year = $param["year"];
		$id_loaihscv = $param["type"];
		$code = $param["code"];
		$this->_redirect("/hscv/chuyenxuly/index/code/".$code."/year/".$year."/wf_id_t/".$wf_id_t."/id/".$idHSCV."/type/".$id_loaihscv);
	}
	
	private function checkInputData($name,$active){
		
		$strurl='/default/error/error?control=chuyenxuly&mod=hscv&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('text128_re',$name,'ERR11002001').",";
		$strerr .= ValidateInputData::validateInput('boolean',$active,"ERR11002005").",";
		if(strlen($strerr)!=2){
			$this->_redirect($strurl.$strerr);
		}
		return true;
	}
	function chuyenxulyAction(){
		$this->_helper->layout->disableLayout();
		
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$totrinhModel = new ToTrinhModel(QLVBDHCommon::getYear());
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		
                // Lay nhomcv tu hscv
                $hscv = new hosocongviecModel();
                $hscvinfo = hosocongviecModel::getDetailById($this->parameter["id"]);
                
                //$this->view->nhomcv = $hscvinfo["NHOMCV"];
                
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
                $macongviec = $hscvinfo['MACONGVIEC'];
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $madonvi = $configlienthong->service->lienthong->username;
                $password = $configlienthong->service->lienthong->password;
                $giaoviecservice = new GiaoViecService();
                $token = $giaoviecservice->login($madonvi,md5($password),"");
                $reCongViec = $giaoviecservice->SelectCongViecByMacongviec(
                        $token
                        ,$macongviec
                );
                $this->view->congviec = json_decode($reCongViec);
		$this->view->CODE = $this->parameter["code"];
		$this->view->processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($this->view->ID_HSCV);
		$this->view->hanxuly = WFEngine::GetHanXuLy($this->view->wf_id_t);
		$this->view->data = $totrinhModel->getListToTrinhByIdHSCV($this->parameter["id"]);
		$this->view->user = $user;
		$this->view->ID_DEP = $user->ID_DEP;
                
                $vbd = new vbdenModel(QLVBDHCommon::getYear());
                $vbden = $vbd->findByHscv($this->parameter["id"]);
                $this->view->vanbanden = vbdenModel::getdetailVBD($vbden["ID_VBDEN"]);
                $this->view->hscv = $hscvinfo;
		//Check trinh ldvp
		if($this->parameter["code"]=="trinhldvp"){
			//check du thao
			$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("HSCV_DUTHAO")." duthao WHERE duthao.ID_HSCV = ?";
			$rduthao = $db->query($sql,$this->view->ID_HSCV)->fetch();
			$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("quanlypt")." phieutrinh WHERE phieutrinh.ID_HSCV = ?";
			$rphieutrinh = $db->query($sql,$this->view->ID_HSCV)->fetch();

			if($rduthao["CNT"]== 0 && $rphieutrinh["CNT"]==0){
				echo "<script>
				alert('Công việc chưa có dự thảo hoặc phiếu trình nào nên không thể trình lãnh đạo VP được.');
				window.parent.document.getElementById('groupcontent".$this->view->ID_HSCV."').style.display='none';
				window.parent.SwapDiv(".$this->view->ID_HSCV.",'/hscv/VanBanDuThao/index/year/".QLVBDHCommon::getYear()."/iddivParent/groupcontent".$this->view->ID_HSCV."/idHSCV/".$this->view->ID_HSCV."',5);</script>
				";exit;
			}
		}

	}
	function savechuyenxulyAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		
		$parameter = $this->getRequest()->getParams();
		
		$idu = $parameter["ID_U"];
		$sms = $parameter["SMS"];
		$email = $parameter["EMAIL"];
		$hanxuly = $parameter["HANXULY"];
		$type = $parameter["TYPE"];
		$id = $parameter["id"];
		$wf_id_t = $parameter["wf_id_t"];
		$noidung = $parameter["NOIDUNG"];
		$istheodoi = $parameter["istheodoi"];
                 // Add param giao viec
        $loaicongviec = $parameter["LOAI_CV"];
		$nhomcv = $parameter["NHOMCV_GV"];
        $nhomcv_giaoviec = $parameter["LOAI_CV"];
        $danhmucnoibo = $parameter["DANHMUCNOIBO"];
        $giaitrinh = $parameter["GIAITRINH"];		
		if($nhomcv==3){
			$hanxulygiaoviec = $parameter["HANXULY_GIAOVIEC_UB"];
			$typehanxulygiaoviec = $parameter["TYPE_HANXULY_GIAOVIEC_UB"];
		
		}elseif($nhomcv==1){
			$hanxulygiaoviec = $parameter["HANXULY_GIAOVIEC_NB"];
			$typehanxulygiaoviec = $parameter["TYPE_HANXULY_GIAOVIECNB"];
			
		}else{
		
		}		
				
		//var_dump($parameter); exit;
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
			}else if($type[$i]==3 ){
				$iduph[] = $idu[$i];
				$dbsms[] = $sms[$i];
				$dbemail[] = $email[$i];
			}else if($type[$i]==4 ){
				$iducnb[] = $idu[$i];
			}
		}
		// Bổ sung lưu use giao việc
                $idugiaoviec=json_encode($idusend);
                $iduphoihop=json_encode($iduph);
                
		$processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($id);
		$usernk = $processinfo["ID_U_NK"];
		$usernc = $processinfo["ID_U_NC"];
		$vbd = new vbdenModel(QLVBDHCommon::getYear());
                $vbden = $vbd->findByHscv($id);

                $hscv = new hosocongviecModel();
                // Cap nhat thong tin giao viec vao van ban den, hscv
               
                if ($nhomcv != 0 && $hanxulygiaoviec != "") {
                    $hscv->update(array("HANXULY_GIAOVIEC"=>$hanxulygiaoviec,"TYPEHANXULY"=>(int)$typehanxulygiaoviec,"LOAICV_GIAOVIEC"=>$loaicongviec,"ID_U_XULYGIAOVIEC"=>$idugiaoviec,"ID_U_PHOIHOPGIAOVIEC"=>$iduphoihop),"ID_HSCV=".$id);
                }  else {
                    $hscv->update(array("LOAICV_GIAOVIEC"=>$loaicongviec),"ID_HSCV=".$id);
                }
                $vbd->update(array("NHOMCVVBD"=>$nhomcv,"GIAITRINH"=>$giaitrinh,"ID_DMNB"=>$danhmucnoibo),"ID_VBD=".$vbden["ID_VBDEN"]);

		$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
		//var_dump($sendsms);exit;
		$lc->send($vbden['ID_VBDEN'],$idudb,$noidung,$user->ID_U,0,null,$dbsms,$dbemail);
		if(count($idusend)>0){			
			try{
	           	hosocongviecModel::SendAll(
	            	$id,
	            	$user->ID_U,
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
					$sendemail
	           	);
                        if($parameter['macongviec']!= '' || $parameter['macongviec'] != NULL)
                        {
                            $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                            $madonvi = $configlienthong->service->lienthong->username;
                            $password = $configlienthong->service->lienthong->password;
                            $giaoviecservice = new GiaoViecService();
                            $model = new UsersModel();
                            $nguoinhan = $model->getName($idusend[0]);
                            $token = $giaoviecservice->login($madonvi,md5($password),"");
//                            $user_dep = UsersModel::getUserDepId($idusend[0]);
//                            $giaoviecservice->createNhatKy(
//                                    $token
//                                    ,$parameter['macongviec']
//                                    ,$idusend[0]
//                                    ,$nguoinhan['TENNGUOITAO']
//                                    ,$parameter['TIENDO_GIAOVIEC']
//                                    ,$parameter['motatiendo'] != '' ? $parameter['motatiendo'] : 'Chuyển xử lý'
//                                    ,$user_dep['NAME']
//                            );
                            $giaoviecservice->UpdateNguoiXLCongViec(
                                    $token
                                    ,$parameter['macongviec']
                                    ,$idusend[0]
                                    ,$nguoinhan['TENNGUOITAO']
                                    ,$parameter['TIENDO_GIAOVIEC']
                                    );
//							// $giaoviecservice->UpdateTienDoCV(
//							// 	$token
//							// 	,$parameter['macongviec']
//							// 	,$parameter['TIENDO_GIAOVIEC']
//							// ); 
                          }
			}catch(Exception $ex){
				echo $ex->__toString();
			}
		}else{
			if($istheodoi==1){
				HosoluutheodoiModel::luuTheodoi(QLVBDHCommon::getYear(),$id,"",$user->ID_U);
			}
		}
		if(count($iduph)>0){
			PhoiHopModel::AddPhoiHop($iduph,$vbden['ID_VBDEN'],$user->ID_U);
			$lc->send($vbden['ID_VBDEN'],$iduph,$noidung,$user->ID_U,0,null,$dbsms,$dbemail,1);
		}
		
		if(count($iducnb)>0){
			//chuyen van ban 
			require_once('hscv/models/chuyennoiboModel.php');
			
			chuyennoiboModel::luuChuyennoibo($vbden['ID_VBDEN'],$user->ID_U,$iducnb,$noidung);

		}
		
		if($parameter["isbanhanh"]==1){
			$db->update("WF_PROCESSITEMS_".QLVBDHCommon::getYear(),array("IS_FINISH"=>1),"ID_O = ".$id);
			if($vbden['ID_VBDEN']>0){
				$db->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>QLVBDHCommon::getTreHan($vbden['NGAYTAO'],$vbden['HANXULYTOANBO']),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
			}
		}

		$id_cqs = $parameter["ID_CQ"];
		
		if(count($id_cqs)>0){
			//chuyen van ban noi bo
			if($parameter["NOIDUNGCHUYEN"])
				$noidung_chuyen = $parameter["NOIDUNGCHUYEN"]." (".$user->FULLNAME.") ";
			require_once('hscv/models/chuyennoiboModel.php');
			chuyennoiboModel::luuChuyennoibo($vbden['ID_VBDEN'],$user->ID_U,$id_cqs,$noidung_chuyen);

		}
		echo "<script>window.parent.document.frm.submit();</script>";
		
		exit;
	}
// chuyen nhieu buoc
   function chuyennhieubuocAction(){
		$this->_helper->layout->disableLayout();		
		global $auth;
		$user = $auth->getIdentity();
		$totrinhModel = new ToTrinhModel(QLVBDHCommon::getYear());
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$this->view->ID_HSCV = $this->parameter["id"];
		$this->view->processinfo = WFEngine::GetCurrentTransitionInfoByIdHscv($this->view->ID_HSCV);
		$this->view->hanxuly = WFEngine::GetHanXuLy($this->view->wf_id_t);
		$this->view->data = $totrinhModel->getListToTrinhByIdHSCV($this->parameter["id"]);
		$this->view->user = $user;
		$this->view->ID_DEP = $user->ID_DEP;
		$duthao = new VanBanDuThaoModel(QLVBDHCommon::getYear());
		$this->view->canBanhanh = 1;
		if(count($duthao->getListByIdHSCV($this->view->ID_HSCV))<=0){
			$this->view->canBanhanh = 0;
		}
	}

	 function ajaxchuyennhieubuocAction(){
		$this->_helper->layout->disableLayout();		
		global $auth;		
		//if($this->_request->isAjax()){
		$parameter = $this->getRequest()->getParams();
                $id_next = (int)$parameter['ID_NEXT']+1;		
		$action= WFEngine::GetNextTransitions_nb($parameter['user_id'],$parameter['ID_A']);
			//var_dump($action);
			if(count($action)>0){
		$html .= '<hr><table class=admintable>
				<tr>
					<td nowrap>Hành động</td>
					<td nowrap>
						<select name="hanhdong[]" onchange="changehd(this,'.$id_next.')">
								<option value=0>--Không thực hiện--</option>';
								
						foreach($action as $actionitem){							
								$html .= '<option value='.$actionitem['ID_T'].'_'.$actionitem['ID_A_END'].'_'.$actionitem['ISLAST'].'>'.$actionitem['NAME'].'</option>';							
						} 
								
						$html .= '</select>
					</td>
				</tr>
				<tr>
					<td nowrap>Người nhận</td>
					<td nowrap>
						<div class="nguoinhandiv"></div>
                    <input autocomplete="off" onclick="cancelEvent(event)" class="autocombobox" value="" type="text" style="width:200px" name="tempnguoinhan'.$id_next.'" id="tempnguoinhan'.$id_next.'" onkeydown="at_KeyDown(event)" onkeyup="at_Display(event)" onfocus="at_Load(\'tempnguoinhan'.$id_next.'\',\'nguoinhan'.$id_next.'\',DATA_nguoinhan'.$id_next.',true,\'changenn(this,'.$id_next.');\');" >
                    <input type="hidden" style="width:200px" name="nguoinhan[]" onchange="" id="nguoinhan'.$id_next.'" class="realnguoinhan" value="">
					</td>
				</tr>
				<tr>
					<td nowrap>Nội dung trình</td>
					<td nowrap>
						<textarea name="noidungtrinh[]" cols=100 rows=3></textarea>
					</td>
				</tr>
				<tr>
					<td nowrap>Ngày chuyển</td>
					<td nowrap>
						'. QLVBDHCommon::calendarFull(date("d/m/Y"),"NGAYCHUYEN[]","").'
					</td>
				</tr>
				<tr>					
				</tr>
				<tr>
					<td nowrap>Lý do trễ</td>
					<td nowrap>
						<textarea name="lydotre[]" cols=100 rows=2></textarea>
					</td>
				</tr>
			</table>';
			echo $html;
		}
			die;		
	}

	function ajaxchuyennhieubuocuserAction(){
		$this->_helper->layout->disableLayout();		
		global $auth;	
		$parameter = $this->getRequest()->getParams();
		//echo $parameter['ID_T'];	
//		$html .= '<option value=0>--Chọn người xử lý--</option>';				
//		$user = WFEngine::GetAccessUserFromTransition_nb($parameter['ID_T']);			
//			foreach($user as $useritem){							
//			 $html .= "<option value=".$useritem['ID_U'].">".$useritem['NAME']."</option>";							
//			}	
                $id = (int)$parameter["ID_NEXT"];
                $user = WFEngine::GetAccessUserFromTransition_nb($parameter['ID_T']);
                $html = QLVBDHCommon::AutoCompleteOnGrid(
                                        WFEngine::GetAccessUserFromTransition_nb($parameter['ID_T']),
				"ID_U",
				"NAME",
				"nguoinhan".$id);
                
			//echo $html."__".$parameter['ID_T'];	
	     //Lay han xu ly mac dinh
			global $db;
			$r = $db->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$parameter['ID_T']);
			$hanxuly = $r->fetch();
			$hanxuly = $hanxuly['HANXULY'];		
			echo $html.= "__"." <td nowrap>Hạn xử lý</td><td nowrap>".QLVBDHCommon::createInputHanxuly2($parameter['ID_A'],"wf_hanxuly_user[]",$hanxuly,$parameter['ID_T'],$parameter['ISLAST'])."</td>";
			die;	
		
	}
	

	function savechuyennhieubuocAction(){
		global $auth;
		global $db;
		$user = $auth->getIdentity();		
		$parameter = $this->getRequest()->getParams();	

			
		// tinh chỉnh param
		for($i=0;$i<count($parameter["nguoinhan"]);$i++){
			if($i==0) $nguoigui[]= $user->ID_U;
			else
			$nguoigui[]=$parameter["nguoinhan"][$i-1];
		}
		for($i=0;$i<count($parameter["hanhdong"]);$i++){			
			$hanhdong_=explode("_",$parameter["hanhdong"][$i]);
			if($hanhdong_[0]>0){
			$hanhdong[]=$hanhdong_[0];
			}
		}		
		$id=$parameter["id"];		
		$nguoinhan=$parameter["nguoinhan"];
		$noidungtrinh=$parameter["noidungtrinh"];
		$ngaychuyen=$parameter["NGAYCHUYEN"];
		$temp_wf_hanxuly_user=$parameter["temp_wf_hanxuly_user"];
		$wf_hanxuly_user=$parameter["wf_hanxuly_user"];
		$lydotre=$parameter["lydotre"];	
		$islast=$parameter["islast"];
		foreach($parameter['id_t'] as $id_t){
		$type[]=$parameter["type_".$id_t.""];
		$type_real[]=$parameter["type_real_".$id_t.""];
		}		
		$vbd = new vbdenModel(QLVBDHCommon::getYear());
        $vbden = $vbd->findByHscv($id);				
	 for($i=0;$i<count($hanhdong);$i++){
		if(count($nguoinhan[$i])>0){		
			try{			WFEngine::SendNextUserByObjectId2($id,$hanhdong[$i],$nguoigui[$i],$nguoinhan[$i],WFEngine::$WFTYPE_USER,$noidungtrinh[$i],$wf_hanxuly_user[$i],$sms,$email);	           	
			}catch(Exception $ex){
				echo $ex->__toString();
			}
		}
		if($islast[$i]==1){
			$db->update("WF_PROCESSITEMS_".QLVBDHCommon::getYear(),array("IS_FINISH"=>1),"ID_O = ".$id);
			if($vbden['ID_VBDEN']>0){
				$db->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>QLVBDHCommon::getTreHan($vbden['NGAYTAO'],$vbden['HANXULYTOANBO']),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
			}
		}
	  }
		echo "<script>window.parent.document.frm.submit();</script>";		
		exit;
	}
	// end chuyen nhieu buoc
	function chuyenphieutrinhAction(){
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$idHSCV = $this->parameter["id"];

		$year = $this->parameter["year"];
		$code= $this->parameter["code"];
		$id_loaihscv = $this->parameter["type"];
		//chuyen du lieu cho lop view
		//tao lop model to trinh
		$totrinhModel = new ToTrinhModel($year);
		//lay danh sach co to trinh tuong ung voi ho so cong viec
		$this->view->data = $totrinhModel->getListToTrinhByIdHSCV($this->parameter["id"]);
		$this->view->idHSCV = $idHSCV;
		$this->view->id_loaihscv = $id_loaihscv;
		$this->view->code = $code;
		$this->view->wf_id_t = $this->parameter["wf_id_t"]; //wf id transition
		
		//var_dump(ChuyenXuLyModel::getUserByGroup($this->view->wf_id_t,'NLD'));
		
		$config = Zend_Registry::get('config');
		$this->view->users_lds = ChuyenXuLyModel::getUserByChucdanh($this->view->wf_id_t,$config->pt_maldcq);
		$this->view->users_ldvps = ChuyenXuLyModel::getUserByChucdanh($this->view->wf_id_t,$config->pt_maldvp);
		$this->view->users_ldps = ChuyenXuLyModel::getUserByChucdanh($this->view->wf_id_t,$config->pt_maldp);


		$this->view->ID_HSCV = $idHSCV; 
		$this->view->year = $year;
		if($code == "banhanh"){
			$this->view->isBanhanh = 1; // truong hop chuyen ban hanh van ban
			$this->view->canBanhanh = 1;
			$duthao = new VanBanDuThaoModel(QLVBDHCommon::getYear());
			if(count($duthao->getListByIdHSCV($idHSCV))<=0){
				$this->view->canBanhanh = 0;
			}
			
		}else{
			$this->view->isBanhanh = 0; // truong hop chuyen xu ly cong vien
		}
		//Lay thong tin ve cac loai ho so (van ban den, soan thao van ban,ho so mot cua)
		$this->view->loaihs_des = ChuyenXuLyModel::getObjectByIdHSCV($idHSCV,$id_loaihscv,$year);
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$r = $dbAdapter->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$this->view->wf_id_t);
		$hanxuly = $r->fetch();
		$this->view->hanxuly = $hanxuly['HANXULY'];
		
				
	}

	function savechuyenphieutrinhAction(){

		//lay bien $auth tu registry 
		$auth = Zend_Registry::get('auth');
		//lay default db adapter
		$db = Zend_Db_Table::getDefaultAdapter();
		$user = $auth->getIdentity(); //lay thong tin cua user trong session
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		
		$idhscv = $param["id"];
		$wf_nexttransition = $param["wf_nexttransition"];
		$wf_nextuser = $param["wf_nextuser"];
		$wf_nextg = $param["wf_nextg"];
		$sms = $param["wf_sms"];
		$email = $param["wf_email"];
		$wf_nextdep = $param["wf_nextdep"];
		$wf_name_user = $param["wf_name_user"];
		$wf_name_g = $param["wf_name_g"];
		$wf_name_dep = $param["wf_name_dep"];
		$wf_hanxuly_user = $param["wf_hanxuly_user"];
		$wf_hanxuly_g = $param["wf_hanxuly_g"];
		$wf_hanxuly_dep = $param["wf_hanxuly_dep"];
		//var_dump($param); exit;
		$year = $param["year"];
		// set nam trong registry
		Zend_Registry::set('year',$year);
		$db->beginTransaction();
		
		$noidung = $param["NOIDUNG"];
		$id_u_ldcq = $param["ID_U_LDCQ"];
		$id_u_ldvp = $param["ID_U_LDVP"];
		$id_u_ldp = $param["ID_U_LDP"];
		$id_u_send = $id_u_ldp;
		if((int)$id_u_ldp == 0){
			$id_u_send = $id_u_ldvp;
			if((int)$id_u_ldvp == 0){
				$id_u_send = $id_u_ldcq;
			}
		}

		if(WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$user->ID_U,$id_u_send,WFEngine::$WFTYPE_USER,$noidung,$wf_hanxuly_user,$sms,$email)==1){
			$isBanhanh = $param["isBanhanh"];	
			if($isBanhanh == 1){
				//cap nhat lai trang thai cua cac du thao van ban trong ho so con viec
				$VBDTModel = new VanBanDuThaoModel($year);
				$VBDTModel->updateTrangthaiByIdHSCVChonBanHanh($idhscv,1);	
				$VBDTModel->updateNguoiKyByIdHSCV($idhscv,$user->ID_U);				
			}
			$type = (int)$param["type"];
			$classalias = WFEngine::GetClassNameFromObjectId($idhscv);
			$curtrans = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
			if($classalias == "MOTCUA" && $curtrans["ISLAST"]==1){
				//if(ChuyenXuLyModel::isAlowaHSMC($wf_nextuser) == 1)
					ChuyenXuLyModel::updatePCMTraHSMC($idhscv);
					DongboHSMCModel::updateTrangthaiByIdHSCV($idhscv,2);
			}
		
			$db->commit();	
			if(WFEngine::GetClassNameFromObjectId($idhscv)=="MOTCUA"){
				if(hosocongviecModel::isfinishhosocv($idhscv)){
					$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
					$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
					QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,3,"Mời Ông/Bà đến nhận kết quả.",$hoso->DIENTHOAI);
				}else{
					$actid = ResourceUserModel::getActionByUrl('motcua','MotCua','savetrahoso');
					$nguoidung = new nguoidungModel();
					$nguoidung = $nguoidung->FindById($param["wf_nextuser"]);

					if(wfengine::IsFinishTransition($wf_nexttransition)){
						$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
						$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
						//QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,3,"Mời Ông/Bà đến nhận kết quả.",$hoso->DIENTHOAI,null);
					}else{
						$hsmc = new motcua_hosoModel(qlvbdhCommon::GetYear());
						$hoso = $hsmc->getHSMCByIdHSCV($idhscv);
										
						$newphong = new motcua_hosoModel();
						$phong = $newphong->ws_getphong($nguoidung['ID_U']);		
						QLVBDHCommon::InsertHSMCService($hoso->MAHOSO,$hoso->TENTOCHUCCANHAN,$hoso->TRICHYEU,1,"Đang xử lý",$hoso->DIENTHOAI,$phong);			
					
					}
				}
			}

		}
		//tra lai doan js de thu thi tai client
		echo "<script>window.parent.document.frm.submit();</script>";
		exit;
	}

	function inphieutrinhAction(){
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity(); //lay thong tin cua user trong session
		$this->view->config = Zend_Registry::get('config');
		$this->_helper->layout->disableLayout();
		$params = $this->getRequest()->getParams();
		$id_u_ldcq = $params['id_u_ldcq'];
		if($id_u_ldcq)
			$this->view->data_u_ldcq = UsersModel::getUserInfoWitchChucdanh($id_u_ldcq);
		
		$id_u_ldvp = $params['id_u_ldvp'];
		if($id_u_ldvp)
			$this->view->data_u_ldvp = UsersModel::getUserInfoWitchChucdanh($id_u_ldvp);
		$id_u_ldp = $params['id_u_ldp'];
		if($id_u_ldp)
			$this->view->data_u_ldp = UsersModel::getUserInfoWitchChucdanh($id_u_ldp);
		if($user->ID_U)
			$this->view->data_u = UsersModel::getUserInfoWitchChucdanh($user->ID_U);
		$id_hscv = $params['id'];
		if($id_hscv)
			$this->view->data_hscv = hosocongviecModel::getDetailById($id_hscv);
		$this->view->NOIDUNG = $params["NOIDUNG"];//nl2br(htmlspecialchars($params["NOIDUNG"]));//str_replace(chr(13).chr(10),'\n',nl2br(htmlspecialchars($params["NOIDUNG"]))) ; ;
		//var_dump($params);
		
	}
    
    function addPhoiHop($id_u,$id_u_phoihop,$idhscv){
		$param = $this->getRequest()->getParams();
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		try{
			//$gopy->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_PHOIHOP"),array("ID_U_YC"=>$id_u,"ID_U"=>$id_u_phoihop,"ID_HSCV"=>$idhscv,"NGAYGUI"=>date("Y-m-d H:i:s")));
			$r = $gopy->getDefaultAdapter()->query("SELECT NAME FROM ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." WHERE ID_HSCV=?",$idhscv);
			$r = $r->fetch();
			QLVBDHCommon::SendMessage(
				$id_u,
				$id_u_phoihop,
				$r["NAME"],
				"hscv/hscv/list/code/phoihop"
			);
			$vbden = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbden->findByHscv($idhscv);
			$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
			$lc->send($vbden['ID_VBDEN'],array($id_u_phoihop),"",$id_u,1,"","","",1);
		}catch(Exception $ex){
			
		}
    }
}

?>
