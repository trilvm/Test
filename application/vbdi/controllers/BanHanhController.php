<?php

/**
 * BanHanhController
 * 
 * @author truongvc
 * @version 
 */
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'vbdi/models/VanBanDiModel.php';
require_once 'qtht/models/MenusModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
require_once 'qtht/models/CoQuanModel.php';
require_once 'qtht/models/LinhVucVanBanModel.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'vbdi/models/vbdi_dongluanchuyenModel.php';
require_once 'vbdi/models/BanHanhForm.php';
// hieuvt
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'vbden/models/chotiepnhanModel.php';

require_once 'hscv/models/ThuMucModel.php';
require_once 'qtht/models/nguoidungModel.php';
require_once 'config/hscv.php';
require_once 'config/vbdi.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/gen_tempModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'traodoicongviec/models/TraoDoiCongViecModel.php';
require_once 'giamsatgiaoviec/models/LoaiCongViecGiaoViecModel.php';
require_once 'giamsatgiaoviec/models/GiamSatGiaoViecModel.php';
require_once "giaoviec/models/giaoviecservice.php";
require_once "giaoviec/models/GiaoViecModel.php";
require_once 'giaoviec/models/LoaiCongViecGiaoViecModel.php';

// end hieuvt
class vbdi {

    var $_id_lvb;
    var $_id_cq;
    var $_domat;
    var $_dokhan;
    var $_sodi;

}

class Vbdi_BanHanhController extends Zend_Controller_Action {

    /**
     * The default action - show the home page
     */
    public function indexAction() {
        $this->view->title = "Danh sách văn bản Đi";
        //$this->view->subtitle = "Danh sách";
        QLVBDHButton::EnableAddNew("/vbdi/banhanh/input");
        QLVBDHButton::EnableDelete("/vbdi/banhanh/delete");
        //Doc du lieu de hien thi len view
        $config = Zend_Registry::get('config');
        $page = $this->_request->getParam('page');
        $year = QLVBDHCommon::getYear();
        $limit = $this->_request->getParam('limit');
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        $filter_object = $this->_request->getParam('filter_object');
        $model = new VanBanDiModel($year);
        $this->view->filter_object = $filter_object;
        $search = $this->_request->getParam("search");
        $this->view->search = $search;
        $order = 'SODI DESC';
        $this->view->data = $model->SelectAll(($page - 1) * $limit, $limit, $search, $filter_object, $order);
        $n_rows = $model->count($search, $filter_object);
        
        $this->view->showPage = QLVBDHCommon::Paginator($n_rows, 5, $limit, "frmListBanHanhs", $page);
        $this->view->limit = $limit;
        $this->view->page = $page;
        $this->view->user = $auth->getIdentity();
    }

    /**
     * ban hanh van ban
     *
     */
    function saveAction() {
        //lay thong tin user luu tren session
        $auth = Zend_Registry::get('auth');
        $user = $auth->getIdentity();
        //Lay du lieu tu client
        $formData = $this->_request->getPost();
        $id = (int) $this->_request->getParam("id", 0);
        $id_duthao = (int) $this->_request->getParam("idduthao", 0);
        $year = QLVBDHCommon::getYear();
        $giamSatGiaoViecModel = new GiamSatGiaoViecModel();
        /* Tao ma so cho van ban */
        $vbdi = new vbdi();
        $vbdi->_id_lvb = $formData['ID_LVB'];
        $vbdi->_id_cq = $formData['ID_CQ'];
        $vbdi->_sodi = $formData['SODI'];
        $vbdi->_domat = $formData['DOMAT'];
        $vbdi->_dokhan = $formData['DOKHAN'];
        $MASOVANBAN = "";
        $MASOVANBAN = Common_Maso::getMaSo(2, $vbdi);
        $filedinhkemModel = new filedinhkemModel($year);
        /* Ket thuc tao ma so cho van ban */
        /* Lay du lieu cu cua van ban de tinh so di */
        $old_data = array();
        $arr_col = array();
        $len_arr = 0;
        if ($id > 0) { //truong hop cap nhat
            $old_data = VanBanDiModel::getDetail($id, $year);
            $arr_col = Common_Sovanban::getColumNameGroup(2);
            $len_arr = count($arr_col);
        }

        /* Kiem tra so di hop le */
        $sodi = trim($formData['SODI']);
        $array = array();
        $array["ID_CQ"] = $formData['ID_CQ'];
        $array["ID_LVB"] = $formData['ID_LVB'];
        $array["ID_SVB"] = $formData['ID_SVB'];
        $sql_getsodi = Common_Sovanban::checkExistsSodiSQL($year, $array, $sodi);
        $vbdi = new VanBanDiModel($year);
        /* Lock bang vbdi_vanbandi lai */
        //$sql = 'LOCK TABLE vbdi_vanbandi_' . $year . ' WRITE';

        /* bat dau dong bo van ban di */

        $is_checked_up = 0;
        if ($id > 0) { //truong hop cap nhat
            $old_sodi = $old_data["SODI"];
            $old_cq = $old_data["ID_CQ"];
            $aff_old = 0;
            $col_name = "";
            foreach ($arr_col as $itcol) {
                switch ($itcol) {
                    case 'ID_SVB':
                        $aff_old = $old_data["ID_SVB"];
                        $col_name = 'ID_SVB';
                        break;
                    case 'ID_LVB':
                        $aff_old = $old_data["ID_LVB"];
                        $col_name = 'ID_LVB';
                        break;
                    default:
                        break;
                }
            }
            if ($old_cq == $formData["ID_CQ"] && $aff_old == $formData[$col_name]) {
                if ($sodi == $old_sodi)
                    $is_checked_up = 1;
            }
        }
        if ($is_checked_up == 0) {
            $sodi_re = $vbdi->getDefaultAdapter()->getConnection()->query($sql_getsodi);
            $cur_sodi_row = $sodi_re->fetch();
            $cur_sodi = $cur_sodi_row['DEM'];
            if ($cur_sodi > 0) {
                $vbdi->getDefaultAdapter()->closeConnection();
                echo -2; // sua thanh -2
                exit;
            }
        }
		if(!is_array($formData['idFile'])){
            $formData['idFile'] = array();
        }
		if(!is_array($formData['ID_CQLIENTHONG'])){
            $formData['ID_CQLIENTHONG'] = array();
        }
        
        /* Ket thuc kiem tra so di */
        //Loc du lieu nhan duoc
        if ($formData['NGAYBANHANH'] != null)
        //doi thanh format cua csdl
            $ngaybanhanh = implode("-", array_reverse(explode("/", $formData['NGAYBANHANH'])));

        $data = array(
            'ID_LVB' => $formData['ID_LVB'],
            'ID_HSCV' => (trim($formData['idhscv']) == "" ? null : $formData['idhscv']),
            'ID_CQ' => $formData['ID_CQ'],
            'NGUOITAO' => $formData['NGUOITAO'],
            'NGUOIKY' => (int)$formData['NGUOIKY'],
            'ID_LVVB' => $formData['ID_LVVB'],
            'ID_SVB' => $formData['ID_SVB'],
            'SOKYHIEU' => $formData['SOKYHIEU'],
            'TRICHYEU' => $formData['TRICHYEU'],
            'SODI' => (trim($formData['SODI']) == "" ? 0 : $formData['SODI']),
            'SOBAN' => (trim($formData['SOBAN']) == "" ? 0 : $formData['SOBAN']),
            'SOTO' => (trim($formData['SOTO']) == "" ? 0 : $formData['SOTO']),
            'DOKHAN' => $formData['DOKHAN'],
            'DOMAT' => $formData['DOMAT'],
            'NGAYTAO' => $today,
            'NGUOISOAN' => (int)$formData['NGUOISOAN'],
            'NGAYBANHANH' => $ngaybanhanh,
            'MASOVANBAN' => $MASOVANBAN,
            'SODI_IN' => (trim($formData['SODI_IN']) == "" ? 0 : $formData['SODI_IN']),
            'SOKYHIEU_IN' => (trim($formData['SOKYHIEU_IN']) == "" ? 0 : $formData['SOKYHIEU_IN']),
            'NGUOI_THEMMOI' => $user->ID_U,
            'NOIDEN' => $formData['NOIDEN'],
			'IS_NOIBO'=>$formData['noibo'],
			'GHICHU'=>$formData['GHICHU'],
			'MASOLIENTHONG'=> null
        );
        
        $config = Zend_Registry::get('config');
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
        }catch (Exception $ex){
            
        }
        
        $isThemMoi=0;
        //neu khong co loi
        if ($id > 0) {
            try {
            //-----//
            //ghi file chữ ký số
            // tenfile=abc.doc;data=kkdkdkkd;
            $masoChuKiSo=null;
            if($formData['outputdata']){				
                    $file_model = new filedinhkemModel($year);					
                    $file = new FileDinhKem();	
                    $date = getdate();					
                    $array_file_so=explode(";",$formData['outputdata']);	

                    for($i=0;$i<count($array_file_so);$i=$i+2){	
                             //$i=$i+2;
                            //lấy tên file
                          $array_file_so_namefile=explode("=",$array_file_so[$i]);
                            //lấy content
                              $array_file_so_content=explode("=",$array_file_so[$i+1]);

                            if($array_file_so_content[0] !=""){
                             //Lay thong tin ve file dinh kem va luu xuong csdl
                              $file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
                              $file->_nam = $date['year'];
                              //$file_model->setNameByYear($file->_nam);
                              $file->_thang = $date['mon'];
                              $dirPath = $file_model->getDir($file->_nam,$file->_thang);
                              $file->_folder = $dirPath;
                              $file->_id_object = $id;
                              $file->_user = $user->ID_U;
                              $file->_filename =  $array_file_so_namefile[1];
                              $file->_type=5;						  
                              $file->_mime="";
                              $file_so_mime=explode(".",$array_file_so_namefile[1]);
                              if($file_so_mime[1]=="pdf"){
                                    $file->_mime="application/pdf";
                              }else{	
                                    $file->_mime="application/octet-stream";
                              }						 
                              $filepath = $dirPath.DIRECTORY_SEPARATOR.$array_file_so_namefile[1];						  
                              file_put_contents($filepath,base64_decode($array_file_so_content[1]));		
                              $id_file = $file_model->insertFileWithIdObject($file);
                              $maso = $id_file.$file->_filename.$file->_time_update;
                              $maso = md5($maso);
			      $masoChuKiSo=$maso;
                              $file_model->updateMaSo($id_file,$maso);
                              $newlocation = $dirPath.DIRECTORY_SEPARATOR.$maso;  						  
                              rename($filepath,$newlocation);			      
								if($masoChuKiSo!=null && $masoChuKiSo!=""){
									array_push($formData['idFile'],$masoChuKiSo);
								}							  
                              }

                    }			

            }
            
             if (isset($formData["ID_CQLIENTHONG"]) 
                        && (!isset($formData["idFile"]) 
                                || count($formData["idFile"]) == 0 
                                || !is_array($formData["idFile"]))) {
                    if ((int) $formData["luuLienThong"] == 1) {
                        echo -99;
                        exit;
                    }
                }
            //End ghi file chữ kí số
            $modelChoTiepNhan = new ChoTiepNhanModel();
            $masovanbanLienThong = (int) $formData['MASOLIENTHONG'];
            if($masovanbanLienThong > 0){
                //láy những đơn vị cũ
                $dongLuanChuyenLienThongTruocDo= $modelChoTiepNhan->DongLuanChuyen($masovanbanLienThong,$session);
                $cqLienThongTruocDo=array();
                foreach ($dongLuanChuyenLienThongTruocDo as $rowLuanChuyenTruocDo){
                    if($rowLuanChuyenTruocDo['DONVI_NHAN'] != '' && $rowLuanChuyenTruocDo['DONVI_NHAN'] != null){
                       array_push($cqLienThongTruocDo, $rowLuanChuyenTruocDo['DONVI_NHAN']);
                    }
                }
//                if(count($formData["ID_CQLIENTHONG"]) > 0 ){
//                    //$formData["ID_CQLIENTHONG"]=  array_unique(array_merge($cqLienThongTruocDo,$formData["ID_CQLIENTHONG"]));
//                    $formData["ID_CQLIENTHONG"]=  array_unique($formData["ID_CQLIENTHONG"]);
//                }else{
//                    //$formData["ID_CQLIENTHONG"]= array_unique($cqLienThongTruocDo);
//                    $formData["ID_CQLIENTHONG"]=array();
//                }
                // cap nhat
                if((int)$formData["luuLienThong"]==1){
                // Liên thông văn bản
                if(count($formData["ID_CQLIENTHONG"]) > 0 ){
                    $idHso = $data['ID_HSCV'];
                    //$donvinhan_lienthong = implode(",", $formData["ID_CQLIENTHONG"]);
                    $arrDonvinhan_lienthong=array();
                    foreach($formData["ID_CQLIENTHONG"] as $k=>$itemCqLienThong){
                                $ten='type_real_HANXULYNV_GIAOVIEC'.$k;
                        $tenDate ='NGAY_KETTHUC_GIAOVIEC'.$k;
                                $tendvtt ='ID_DONVITRUCTHUOC_LIENTHONG'.$k;
                        $dataitem=array(
                            'DONVI'=>$itemCqLienThong,
                            'NHIEMVU'=>$formData["NOIDUNGNV_GIAOVIEC"][$k],
                            'HANXULY'=>$formData["HANXULYNV_GIAOVIEC"][$k],
                            'TYPEHANXULY'=>$formData[$ten],
                                    'NGAYKETTHUC'=>  QLVBDHCommon::doDateViet2Standard($formData[$tenDate]),
                                    'MADVTT'=>$formData[$tendvtt],
                        );
                        array_push($arrDonvinhan_lienthong, $dataitem);
                    }
                    $donvinhan_lienthong=json_encode($arrDonvinhan_lienthong);
                    
                    $maSoVanBan = $formData['MASOLIENTHONG'];
                    $arrThongTinPhu = array (
                        base64_encode($formData['SOTO']),
                        base64_encode($formData['SOBAN']),
                        base64_encode($formData['DOMAT']),
                        base64_encode($formData['DOKHAN']),
                        base64_encode($formData['ID_LVVB'])
                    );
                    $headerThongTinPhu = '5~SOTO~SOBAN~DOMAT~DOKHAN~LINHVUC~';
                    $thongTinPhu = base64_encode($headerThongTinPhu . implode('~',$arrThongTinPhu));
                    $arrParam = array(
                        base64_encode($formData['MASOLIENTHONG']),
                        base64_encode($ngaybanhanh),
                        base64_encode($formData['NGUOIKY_TEXT']),
                        base64_encode(''),
                        base64_encode($formData['TRICHYEU']),
                        base64_encode($formData['ID_LVB_TEXT']),
                        base64_encode(''),
                        base64_encode($formData['SOKYHIEU']),
                        base64_encode($donvinhan_lienthong),
                        base64_encode($formData['GHICHU']),
                        base64_encode($thongTinPhu),
                        base64_encode($formData['CAP_GIAOVIEC']),
                        base64_encode($formData['LOAICV_GIAOVIEC']),
                        base64_encode($formData['NOIDUNG_GIAOVIEC']),
                        base64_encode($formData['HANXULY_GIAOVIEC']),
                        base64_encode((int)$formData['idhscv']),
                        base64_encode($giamSatGiaoViecModel->checkIsGiaoViec((int)$formData['idhscv'])),
                        base64_encode(UsersModel::getsokyhieucqnbbyidu($user->ID_CQ)),
                        base64_encode($formData['NGUOISOAN_TEXT']),
                        base64_encode((int)$formData['NGUOIKY']),
                        base64_encode((int)$formData['NGUOISOAN']),                                
                        base64_encode($formData['MADLCLIENTHONGCHA'])
                    );
                    $parameter = implode('~',$arrParam);
                    $param = array(
                            'session'  =>  $session,
                            'service_code' => 'VANBAN',
                            'service_name' => 'CAPNHATVANBAN',
                            'parameter' => $parameter
                        );
                    $result = $cliente->__call('Execute',$param);
                    // Xoá file đính kèm cũ
                    $paramRQ = array(
                            'session' => $session,
                            'service_code' => 'FILEVANBAN',
                            'service_name' => 'REMOVE',
                            'parameter' => base64_encode($maSoVanBan)
                            );
                    $resultDelete = $cliente->__call('Execute', $paramRQ);
                    // Gửi file mới
                    $limit              = 32896; //max 409600 //min 32896
                    $arrayIdFile        = $formData['idFile'];
                    for ($y = 0; $y < count($arrayIdFile); $y++) {
                        $maSoFileServices   = "";
                        $fileInfo   = $filedinhkemModel->getFileByMaso($arrayIdFile[$y]);
                        $size       = filesize($fileInfo->_pathFile);
                        $dataFile   = file_get_contents($fileInfo->_pathFile);
                        $count      = ceil($size/$limit);

                        if($size > $limit){
                            $start = 0;
                            $end   = $limit;
                            for($i = 0; $i<$count;$i++){
                                $start = $limit*$i;
                                $end   = $limit*($i+1);
                                if($end > $size){
                                    $end = $size;
                                }
                                //echo '[ '.$start.' | '.$end.' ]';
                                $fileContent = substr($dataFile,$start,$limit);
                                $arrFileInfo = array(
                                    base64_encode($maSoVanBan),
                                    base64_encode($fileInfo->_filename),
                                    base64_encode($maSoFileServices),
                                    base64_encode($fileInfo->_mime),
                                    base64_encode($fileContent)
                                );
                                $parameter = implode('~',$arrFileInfo);
                                $paramRQ = array(
                                        'session' => $session,
                                        'service_code' => 'FILEVANBAN',
                                        'service_name' => 'SEND',
                                        'parameter' => $parameter
                                        );
                                $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                            }
                        }else{
                            $arrFileInfo = array(
                                base64_encode($maSoVanBan),
                                base64_encode($fileInfo->_filename),
                                base64_encode($maSoFileServices),
                                base64_encode($fileInfo->_mime),
                                base64_encode($dataFile),
                                );
                            $parameter = implode('~',$arrFileInfo);
                                
                            $paramRQ = array(
                                        'session' => $session,
                                        'service_code' => 'FILEVANBAN',
                                        'service_name' => 'SEND',
                                        'parameter' => $parameter
                                        );
                            $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                        }
                    }
                    
                    // Cập nhật trạng thái sẵng sàng
                    $arrParamServicesTrangThai = array(
                        base64_encode($maSoVanBan),
                        base64_encode(2)
                    );
                    
                    $parameterTrangThai = implode('~',$arrParamServicesTrangThai);
                    $paramRQTrangThai = array(
                        'session' => $session,
                        'service_code' => 'TRANGTHAI',
                        'service_name' => 'UPDATE',
                        'parameter' => $parameterTrangThai
                    );
                    $resultUpdateTrangthai = $cliente->__call('Execute', $paramRQTrangThai); 
       
                }
                }
                $data['MASOLIENTHONG'] = (int)$formData['MASOLIENTHONG'];                
                }else{
                    //them moi neu co ID_CQLIENTHONG.
                    if((int)$formData["luuLienThong"]==1){
                        if(count($formData["ID_CQLIENTHONG"]) > 0 ){
                        //$donvinhan_lienthong = implode(",",$formData["ID_CQLIENTHONG"]);
                        $arrDonvinhan_lienthong=array();
                        foreach($formData["ID_CQLIENTHONG"] as $k=>$itemCqLienThong){
                                $ten='type_real_HANXULYNV_GIAOVIEC'.$k;
                            $tenDate ='NGAY_KETTHUC_GIAOVIEC'.$k;
                                $tendvtt ='ID_DONVITRUCTHUOC_LIENTHONG'.$k;                                
                            $dataitem=array(
                                'DONVI'=>$itemCqLienThong,
                                'NHIEMVU'=>$formData["NOIDUNGNV_GIAOVIEC"][$k],
                                'HANXULY'=>$formData["HANXULYNV_GIAOVIEC"][$k],
                                'TYPEHANXULY'=>$formData[$ten],
                                    'NGAYKETTHUC'=>  QLVBDHCommon::doDateViet2Standard($formData[$tenDate]),
                                    'MADVTT'=>$formData[$tendvtt],
                            );
                            array_push($arrDonvinhan_lienthong, $dataitem);
                        }
                        $donvinhan_lienthong=json_encode($arrDonvinhan_lienthong);
                        $arrThongTinPhu = array (
                            base64_encode($formData['SOTO']),
                            base64_encode($formData['SOBAN']),
                            base64_encode($formData['DOMAT']),
                            base64_encode($formData['DOKHAN']),
                            base64_encode($formData['ID_LVVB'])
                        );
                        $headerThongTinPhu = '5~SOTO~SOBAN~DOMAT~DOKHAN~LINHVUC~';
                        $thongTinPhu = base64_encode($headerThongTinPhu . implode('~',$arrThongTinPhu));
                        $arrParam = array(
                            base64_encode($formData['MASOLIENTHONGCHA']),
                            base64_encode($ngaybanhanh),
                            base64_encode(""),
                            base64_encode($formData['TRICHYEU']),
                            base64_encode($formData['ID_LVB_TEXT']),
                            base64_encode($formData['GHICHU']),
                            base64_encode($donvinhan_lienthong),
                            base64_encode($formData['NGUOIKY_TEXT']),
                            base64_encode($formData['SOKYHIEU']),
                            base64_encode($thongTinPhu),
                                base64_encode($formData['CAP_GIAOVIEC']),
                                base64_encode($formData['LOAICV_GIAOVIEC']),
                                base64_encode($formData['NOIDUNG_GIAOVIEC']),
                                base64_encode($formData['HANXULY_GIAOVIEC']),
                                base64_encode((int)$formData['idhscv']),
                                base64_encode($giamSatGiaoViecModel->checkIsGiaoViec((int)$formData['idhscv'])),
                            base64_encode(UsersModel::getsokyhieucqnbbyidu($user->ID_CQ)),
                            base64_encode($formData['NGUOISOAN_TEXT']),
                        base64_encode((int)$formData['NGUOIKY']),
                        base64_encode((int)$formData['NGUOISOAN']),                                
                        base64_encode($formData['MADLCLIENTHONGCHA']),
                        base64_encode(""),
                        base64_encode($formData['ID_VBLTCP'])
                        );
                        $parameter = implode('~',$arrParam);
                        $paramRQ = array(
                            'session' => $session,
                            'service_code' => 'VANBAN',
                            'service_name' => 'GUIVANBAN',
                            'parameter' => $parameter
                        );
                        $maSoVanBandatas = $cliente->__call('Execute', $paramRQ);
                        //echo $maSoVanBan;exit;
                        $maSoVanBandata = explode("~",$maSoVanBandatas);
                        $maSoVanBan = (int)$maSoVanBandata[0];
                        $ID_VBLTCP  = (int)$maSoVanBandata[1];       
                        $data['MASOLIENTHONG'] = $maSoVanBan;
                        $data['ID_VBLTCP'] = $ID_VBLTCP;
                        // Chuẩn bị gửi file lên dịch vụ                
                        $limit              = 32896; //max 409600 //min 32896
                        $arrayIdFile        = $formData['idFile'];
                        for ($y = 0; $y < count($arrayIdFile); $y++) {
                            $maSoFileServices   = "";
                            $fileInfo   = $filedinhkemModel->getFileByMaso($arrayIdFile[$y]);
                            $size       = filesize($fileInfo->_pathFile);
                            $dataFile   = file_get_contents($fileInfo->_pathFile);
                            $count      = ceil($size/$limit);

                            if($size > $limit){
                                $start = 0;
                                $end   = $limit;
                                for($i = 0; $i<$count;$i++){
                                    $start = $limit*$i;
                                    $end   = $limit*($i+1);
                                    if($end > $size){
                                        $end = $size;
                                    }
                                    //echo '[ '.$start.' | '.$end.' ]';
                                    $fileContent = substr($dataFile,$start,$limit);
                                    $arrFileInfo = array(
                                        base64_encode($maSoVanBan),
                                        base64_encode($fileInfo->_filename),
                                        base64_encode($maSoFileServices),
                                        base64_encode($fileInfo->_mime),
                                        base64_encode($fileContent)
                                    );
                                    $parameter = implode('~',$arrFileInfo);
                                    $paramRQ = array(
                                            'session' => $session,
                                            'service_code' => 'FILEVANBAN',
                                            'service_name' => 'SEND',
                                            'parameter' => $parameter
                                            );
                                    $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                                }
                            }else{
                                $arrFileInfo = array(
                                    base64_encode($maSoVanBan),
                                    base64_encode($fileInfo->_filename),
                                    base64_encode($maSoFileServices),
                                    base64_encode($fileInfo->_mime),
                                    base64_encode($dataFile),
                                    );
                                $parameter = implode('~',$arrFileInfo);

                                $paramRQ = array(
                                            'session' => $session,
                                            'service_code' => 'FILEVANBAN',
                                            'service_name' => 'SEND',
                                            'parameter' => $parameter
                                            );
                                $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                            }
                        }

                        // Cập nhật trạng thái sẵng sàng
                        $arrParamServicesTrangThai = array(
                            base64_encode($maSoVanBan),
                            base64_encode(2)
                        );

                        $parameterTrangThai = implode('~',$arrParamServicesTrangThai);
                        $paramRQTrangThai = array(
                            'session' => $session,
                            'service_code' => 'TRANGTHAI',
                            'service_name' => 'UPDATE',
                            'parameter' => $parameterTrangThai
                        );
                        $resultUpdateTrangthai = $cliente->__call('Execute', $paramRQTrangThai); 
                    }     
                    }
                }
                //--// 
                $where = "ID_VBDI=" . $id;
				
				if (isset($formData["ID_CQLIENTHONG"]) 
                        && is_array($formData["ID_CQLIENTHONG"]) 
                        && count($formData["ID_CQLIENTHONG"]) > 0) {
			$dataEncodeCQLienThong= array();
                    foreach($formData["ID_CQLIENTHONG"] as $key=>$itemCQLT){
                        $ten='type_real_HANXULYNV_GIAOVIEC'.$key;
                        $tenDate ='NGAY_KETTHUC_GIAOVIEC'.$key;
                        $tendvtt ='ID_DONVITRUCTHUOC_LIENTHONG'.$key;                                
                        $dataitenCQLT =array(
                            'DONVI'=>$itemCQLT,
                            'NHIEMVU'=>$formData["NOIDUNGNV_GIAOVIEC"][$key],
                            'HANXULY'=>$formData["HANXULYNV_GIAOVIEC"][$key],
                            'TYPEHANXULY'=>$formData[$ten],
                            'NGAYKETTHUC'=>  QLVBDHCommon::doDateViet2Standard($formData[$tenDate]),
                            'MADVTT'=>$formData[$tendvtt],
                        );
                        array_push($dataEncodeCQLienThong,$dataitenCQLT);
                    }
                    //$data['CQLIENTHONG_DRAFT']=  json_encode($formData["ID_CQLIENTHONG"]);
		    $data['CQLIENTHONG_DRAFT'] = json_encode($dataEncodeCQLienThong);
                }else{
                     $data['CQLIENTHONG_DRAFT']= json_encode(array());
                }
                $vbdi->update($data, $where);
            } catch (Exception $ex) {
                /* ket thuc dong bo van ban di */
                $vbdi->getDefaultAdapter()->closeConnection();
                echo $ex;
                exit; //ket thuc tai day
            }
        } else {
            try {
                $data['MASOLIENTHONG'] = 0;
                //ghi file chữ ký số
                // tenfile=abc.doc;data=kkdkdkkd;
                $masoChuKiSo=null;
                if($formData['outputdata']){				
                        $file_model = new filedinhkemModel($year);					
                        $file = new FileDinhKem();	
                        $date = getdate();					
                        $array_file_so=explode(";",$formData['outputdata']);	

                        for($i=0;$i<count($array_file_so);$i=$i+2){	
                                 //$i=$i+2;
                                //lấy tên file
                              $array_file_so_namefile=explode("=",$array_file_so[$i]);
                                //lấy content
                                  $array_file_so_content=explode("=",$array_file_so[$i+1]);

                                if($array_file_so_content[0] !=""){
                                 //Lay thong tin ve file dinh kem va luu xuong csdl
                                  $file->_time_update = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
                                  $file->_nam = $date['year'];
                                  //$file_model->setNameByYear($file->_nam);
                                  $file->_thang = $date['mon'];
                                  $dirPath = $file_model->getDir($file->_nam,$file->_thang);
                                  $file->_folder = $dirPath;
                                  $file->_id_object = $formData['IdObjectTemp'];
                                  $file->_user = $user->ID_U;
                                  $file->_filename =  $array_file_so_namefile[1];
                                  $file->_type=5;						  
                                  $file->_mime="";
                                  $file_so_mime=explode(".",$array_file_so_namefile[1]);
                                  if($file_so_mime[1]=="pdf"){
                                        $file->_mime="application/pdf";
                                  }else{	
                                        $file->_mime="application/octet-stream";
                                  }						 
                                  $filepath = $dirPath.DIRECTORY_SEPARATOR.$array_file_so_namefile[1];						  
                                  file_put_contents($filepath,base64_decode($array_file_so_content[1]));		
                                  $id_file = $file_model->insertFileWithIdObject($file);
                                  $maso = $id_file.$file->_filename.$file->_time_update;
                                  $maso = md5($maso);
                                  $masoChuKiSo=$maso;
                                  $file_model->updateMaSo($id_file,$maso);
                                  $newlocation = $dirPath.DIRECTORY_SEPARATOR.$maso;  						  
                                  rename($filepath,$newlocation);			      
									if($masoChuKiSo!=null && $masoChuKiSo!=""){
										array_push($formData['idFile'],$masoChuKiSo);
									}								  
                                  }

                        }			

                }
                
                 if (isset($formData["ID_CQLIENTHONG"]) 
                        && (!isset($formData["idFile"]) 
                                || count($formData["idFile"]) == 0 
                                || !is_array($formData["idFile"]))) {
                    if ((int) $formData["luuLienThong"] == 1) {
                        echo -99;
                        exit;
                    }
                }
                //End ghi file chữ kí số
                if((int)$formData["luuLienThong"]==1){
                // Liên thông văn bản
                if($formData["ID_CQLIENTHONG"] > 0 ){
                    //$donvinhan_lienthong = implode(",",$formData["ID_CQLIENTHONG"]);
                    $arrDonvinhan_lienthong=array();
                    foreach($formData["ID_CQLIENTHONG"] as $k=>$itemCqLienThong){
                            $ten='type_real_HANXULYNV_GIAOVIEC'.$k;
                        $tenDate ='NGAY_KETTHUC_GIAOVIEC'.$k;
                            $tendvtt ='ID_DONVITRUCTHUOC_LIENTHONG'.$k;                                
                        $dataitem=array(
                            'DONVI'=>$itemCqLienThong,
                            'NHIEMVU'=>$formData["NOIDUNGNV_GIAOVIEC"][$k],
                            'HANXULY'=>$formData["HANXULYNV_GIAOVIEC"][$k],
                            'TYPEHANXULY'=>$formData[$ten],
                                'NGAYKETTHUC'=>  QLVBDHCommon::doDateViet2Standard($formData[$tenDate]),
                                'MADVTT'=>$formData[$tendvtt],                                    
                        );
                        array_push($arrDonvinhan_lienthong, $dataitem);
                    }
                    $donvinhan_lienthong=json_encode($arrDonvinhan_lienthong);
                    $arrThongTinPhu = array (
                        base64_encode($formData['SOTO']),
                        base64_encode($formData['SOBAN']),
                        base64_encode($formData['DOMAT']),
                        base64_encode($formData['DOKHAN']),
                        base64_encode($formData['ID_LVVB'])
                    );
                    $headerThongTinPhu = '5~SOTO~SOBAN~DOMAT~DOKHAN~LINHVUC~';
                    $thongTinPhu = base64_encode($headerThongTinPhu . implode('~',$arrThongTinPhu));
                    $arrParam = array(
                        base64_encode($formData['MASOLIENTHONGCHA']),
                        base64_encode($ngaybanhanh),
                        base64_encode(""),
                        base64_encode($formData['TRICHYEU']),
                        base64_encode($formData['ID_LVB_TEXT']),
                        base64_encode($formData['GHICHU']),
                        base64_encode($donvinhan_lienthong),
                        base64_encode($formData['NGUOIKY_TEXT']),
                        base64_encode($formData['SOKYHIEU']),
                        base64_encode($thongTinPhu),
                        base64_encode($formData['CAP_GIAOVIEC']),
                        base64_encode($formData['LOAICV_GIAOVIEC']),
                        base64_encode($formData['NOIDUNG_GIAOVIEC']),
                        base64_encode($formData['HANXULY_GIAOVIEC']),
                        base64_encode((int)$formData['idhscv']),
                        base64_encode($giamSatGiaoViecModel->checkIsGiaoViec((int)$formData['idhscv'])),
                        base64_encode(UsersModel::getsokyhieucqnbbyidu($user->ID_CQ)),
                        base64_encode($formData['NGUOISOAN_TEXT']),
                        base64_encode((int)$formData['NGUOIKY']),
                        base64_encode((int)$formData['NGUOISOAN']),                                
                        base64_encode($formData['MADLCLIENTHONGCHA']),
                        base64_encode(""),
                        base64_encode($formData['ID_VBLTCP'])
                    );
                    $parameter = implode('~',$arrParam);
                    $paramRQ = array(
                        'session' => $session,
                        'service_code' => 'VANBAN',
                        'service_name' => 'GUIVANBAN',
                        'parameter' => $parameter
                    );
                    
                    $maSoVanBandatas = $cliente->__call('Execute', $paramRQ);
                    //echo $maSoVanBan;exit;
                    $maSoVanBandata = explode("~",$maSoVanBandatas);
                    $maSoVanBan = (int)$maSoVanBandata[0];
                    $ID_VBLTCP  = (int)$maSoVanBandata[1];       
                    $data['MASOLIENTHONG'] = $maSoVanBan;
                    $data['ID_VBLTCP'] = $ID_VBLTCP;
                    if($formData['macongviec']!= '' || $formData['macongviec'] != NULL)
                    {
                    $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                    $madonvi = $configlienthong->service->lienthong->username;
                    $password = $configlienthong->service->lienthong->password;
                    $giaoviecservice = new GiaoViecService();
                    $token = $giaoviecservice->login($madonvi,md5($password),"");
                    $user_dep = DepartmentsModel::getNameById($user->ID_DEP);
                    $giaoviecservice->createNhatKy(
                            $token
                            ,$formData['macongviec']
                            ,$user->ID_U
                            ,$user->FULLNAME
                            ,100
                            ,'Đã ban hành'
                            ,$user_dep
                    );
                    $giaoviecservice->UpdateTienDoCV(
                        $token
                        ,$formData['macongviec']
                        ,100
                    ); 
                    
                    $CqVpubnd = "000.01.01.H11";
                    if( in_array($CqVpubnd, $formData["ID_CQLIENTHONG"])==true)
                    {
                        // $giaoviecservice->UpdateTrangThaiCongViec(
                        // $token
                        // ,$maSoVanBan
                        // ,3
                        // ,$user->ID_U
                        // ,$user->FULLNAME
                        // ,$formData['macongviec']
                        // );
                        $giaoviecservice->UpdateTrangThaiCV(
                            $token
                            ,$maSoVanBan
                            ,3
                            ,$formData['macongviec']
                        ); 
                    }
                  }
                $isgiaoviec =0;
                if($formData["LOAICV_GIAOVIEC"]!="")
                { 
                   $isgiaoviec =1;
                }
                    // Chuẩn bị gửi file lên dịch vụ                
                    $limit              = 32896; //max 409600 //min 32896
                    $arrayIdFile        = $formData['idFile'];
                    for ($y = 0; $y < count($arrayIdFile); $y++) {
                        $maSoFileServices   = "";
                        $fileInfo   = $filedinhkemModel->getFileByMaso($arrayIdFile[$y]);
                        $size       = filesize($fileInfo->_pathFile);
                        $dataFile   = file_get_contents($fileInfo->_pathFile);
                        $count      = ceil($size/$limit);
                        
                        if($size > $limit){
                            $start = 0;
                            $end   = $limit;
                            for($i = 0; $i<$count;$i++){
                                $start = $limit*$i;
                                $end   = $limit*($i+1);
                                if($end > $size){
                                    $end = $size;
                                }
                                //echo '[ '.$start.' | '.$end.' ]';
                                $fileContent = substr($dataFile,$start,$limit);
                                $arrFileInfo = array(
                                    base64_encode($maSoVanBan),
                                    base64_encode($fileInfo->_filename),
                                    base64_encode($maSoFileServices),
                                    base64_encode($fileInfo->_mime),
                                    base64_encode($fileContent)
                                );
                                $parameter = implode('~',$arrFileInfo);
                                $paramRQ = array(
                                        'session' => $session,
                                        'service_code' => 'FILEVANBAN',
                                        'service_name' => 'SEND',
                                        'parameter' => $parameter
                                        );
                                $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                            }
                        }else{
                            $arrFileInfo = array(
                                base64_encode($maSoVanBan),
                                base64_encode($fileInfo->_filename),
                                base64_encode($maSoFileServices),
                                base64_encode($fileInfo->_mime),
                                base64_encode($dataFile),
                                );
                            $parameter = implode('~',$arrFileInfo);
                                
                            $paramRQ = array(
                                        'session' => $session,
                                        'service_code' => 'FILEVANBAN',
                                        'service_name' => 'SEND',
                                        'parameter' => $parameter
                                        );
                            $maSoFileServices = $cliente->__call('Execute', $paramRQ);
                        }
                    }
                    
                    // Cập nhật trạng thái sẵng sàng
                    $arrParamServicesTrangThai = array(
                        base64_encode($maSoVanBan),
                        base64_encode(2)
                    );
                    
                    $parameterTrangThai = implode('~',$arrParamServicesTrangThai);
                    $paramRQTrangThai = array(
                        'session' => $session,
                        'service_code' => 'TRANGTHAI',
                        'service_name' => 'UPDATE',
                        'parameter' => $parameterTrangThai
                    );
                    $resultUpdateTrangthai = $cliente->__call('Execute', $paramRQTrangThai); 
                }
                }
				
				if (isset($formData["ID_CQLIENTHONG"]) 
                        && is_array($formData["ID_CQLIENTHONG"]) 
                        && count($formData["ID_CQLIENTHONG"]) > 0) {
                    $dataEncodeCQLienThong= array();
                    foreach($formData["ID_CQLIENTHONG"] as $key=>$itemCQLT){
                        $ten='type_real_HANXULYNV_GIAOVIEC'.$key;
                        $tenDate ='NGAY_KETTHUC_GIAOVIEC'.$key; 
                        $tendvtt ='ID_DONVITRUCTHUOC_LIENTHONG'.$key;                                
                        $dataitenCQLT =array(
                            'DONVI'=>$itemCQLT,
                            'NHIEMVU'=>$formData["NOIDUNGNV_GIAOVIEC"][$key],
                            'HANXULY'=>$formData["HANXULYNV_GIAOVIEC"][$key],
                            'TYPEHANXULY'=>$formData[$ten],
                            'NGAYKETTHUC'=>  QLVBDHCommon::doDateViet2Standard($formData[$tenDate]),
                            'MADVTT'=>$formData[$tendvtt],
                        );
                        array_push($dataEncodeCQLienThong,$dataitenCQLT);
                    }
                    //$data['CQLIENTHONG_DRAFT']=  json_encode($formData["ID_CQLIENTHONG"]);
		    $data['CQLIENTHONG_DRAFT'] = json_encode($dataEncodeCQLienThong);
                }else{
                     $data['CQLIENTHONG_DRAFT']= json_encode(array());
                }
                $id = $vbdi->insert($data);
                $isThemMoi=1;
            } catch (Exception $ex) {
                echo $ex->__toString();
                /* ket thuc dong bo van ban di */
                $vbdi->getDefaultAdapter()->closeConnection();
                echo $ex;
                exit;
            }
            //cap nhat trang thai cua du thao
        }

        /* ket thuc dong bo van ban di */
        $vbdi->getDefaultAdapter()->closeConnection();

        //if($id == 0){
        $vbduthao = new VanBanDuThaoModel($year);
        $id_hscv = trim($formData['idhscv']) == "" ? null : $formData['idhscv'];
        if ((int)$id_hscv > 0) {
            global $db;
	    $hscv = new hosocongviecModel(QLVBDHCommon::getYear());
            $vbduthao->updateTrangthaiByIdDuthao($id_duthao, 2);
            if ($formData['THUMUC'] > 0) {
                $rthumuc = $db->query("SELECT ISCOQUAN FROM HSCV_THUMUC WHERE ID_THUMUC=?",$formData['THUMUC'])->fetch();
                //check xem thu muc duoc cho la thu muc gi
                if ($rthumuc["ISCOQUAN"] == 1) {                
                    $hscv->update(array("ID_THUMUC" => $formData['THUMUC']), "ID_HSCV=" . (int)$id_hscv);
                } else {
                    $hscv->update(array("ID_THUMUC_HSCV" => $formData['THUMUC']), "ID_HSCV=" . (int)$id_hscv);
                }
            }
        }
        //}


        /* Luu, cap nhat chuyen de biet */


        $lc = new vbdi_dongluanchuyenModel($year);
        if ($id > 0) {
            $arr_old_keep = $formData['ID_OLD'];

            $old_lcs = vbdi_dongluanchuyenModel::way($year, $id);

            $old_delete = array();
            foreach ($old_lcs as $old_lc) {
                $ln = 1;

                foreach ($arr_old_keep as $lc_keep) {
                    if ($old_lc["ID_VBDI_DLC"] == $lc_keep)
                        $ln = 0;
                }
                if ($ln == 1)
                    array_push($old_delete, $old_lc["ID_VBDI_DLC"]);
            }

            //var_dump($old_delete);
            foreach ($old_delete as $itdel) {
                try {
                    $where = 'ID_VBDI_DLC = ' . $itdel;
                    $lc->delete($where);
                } catch (Exception $er) {
                    //echo $er->__toString();
                };
            }
        }

        //Thêm mới vào dòng luân chuyển
        try {

            $arr_user_re = $formData['ID_U'];
            if (!is_array($arr_user_re))
                $arr_user_re = array();
            $lc->send($id, $arr_user_re, $formData['NOIDUNG'], $user->ID_U);
            //echo $arru[$counter];
        } catch (Exception $er) {

        }

        /* Ket thuc Luu, cap nhat chuyen de biet */

        /* File dinh kem */       
        if($isThemMoi && $isThemMoi==1){
        $arrayIdFile = $formData['idFile'];
        for ($i = 0; $i < count($arrayIdFile); $i++) {
            $filedinhkemModel->update(array("ID_OBJECT" => $id, "TYPE" => 5), "MASO='" . $arrayIdFile[$i] . "'");
        }
	}
        /* Ket thuc File dinh kem */

        //Cap nhat chuyen noi bo o day de luu duoc file dinh kem cua van ban di
        if ($id) {
            $actid = ResourceUserModel::getActionByUrl('hscv', 'chuyennoibo', 'chuyennoiboinput');
            if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
                require_once('hscv/models/chuyennoiboModel.php');
                $id_cqs = $formData["ID_CQNGOAI"];

                chuyennoiboModel::vbdi_luuchuyennoibo((int) $id, $user->ID_U, $id_cqs, $noidung);
            }
        }
		$this->view->id = $id;
        // xu ly lai 
//         if(isset($formData['IS_DINHKY']))
//                {
//                    $CqVpubnd = "000.01.01.H11";
//                    if( in_array($CqVpubnd, $formData["ID_CQLIENTHONG"])==false)
//                    {
//                    $hscv = new hosocongviecModel();
//                    $ok = $hscv->rollbackXuLy($formData['idhscv'], $user->ID_U);
//                        if ($ok == 1) 
//                            {
//                                $hscv->update(array("ID_THUMUC" => 1), "ID_HSCV=" . (int) $formData['idhscv']);
//                            }else if($ok == 2)
//                            {
//                                 $hscv->getDefaultAdapter()->delete(QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS"), "ID_HSCV=" . (int) $formData['idhscv']);
//                            }
//                    }
//                }
        
        echo 1; // neu thanh cong tra ve 1 (doi thanh tra ve id cua van ban di)
        exit;
    }

    function inputAction() {
        
        $nguoidungModel = new nguoidungModel();
        $coQuanModel = new CoQuanModel();
		$dataCoQuan = $coQuanModel->getAllCoQuanName();
		$this->view->listCoQuan = $dataCoQuan;
        $this->view->datalvvb = array();
        QLVBDHCommon::GetTree(&$this->view->datalvvb,"vb_linhvucvanban","ID_LVVB","ACTIVE=1 AND ID_LVVB_PARENT",1,1);
        $this->view->linhvucdb = $nguoidungModel->nguoidunglinhvuc();
        //var_dump($this->view->linhvucdb);exit; 
        //Thêm các button
        QLVBDHButton::EnableSave("vbdi/banhanh/input");
        QLVBDHButton::EnableSaveLienThong("vbdi/banhanh/input");
        QLVBDHButton::EnableBack("vbdi/banhanh/list");
        //lay tham so
        $param = $this->_request->getParams();	
        $year = QLVBDHCommon::getYear();
        $id = $param["id"];
        $auth = Zend_Registry::get('auth');
        $user = $auth->getIdentity();
        $this->view->user = $user;
        $this->view->name_u = UsersModel::getEmloyeeNameByIdUser($user->ID_U);
        $giamSatGiaoViecModel = new GiamSatGiaoViecModel();
        if ($id > 0) {            
            //truong hop cap nhat
            $this->view->title = "Cập nhật văn bản Đi";
            //$this->view->subtitle = "Cập nhật";
            //Lay thong tin ve van ban di
            $this->view->data = VanBanDiModel::getDetail($id, $year);
            $this->view->idhscv = $this->view->data["ID_HSCV"];
            $dongluanchuyen_vbdi = new vbdi_dongluanchuyenModel($year);
            //$array_chuyendebiet=$dongluanchuyen_vbdi->findAllNguoiNhan($id);
            $array_chuyendebiet = $dongluanchuyen_vbdi->way($year, $id);
            if (count($array_chuyendebiet) > 0) {
                //var_dump($array_chuyendebiet);
                $this->view->data_chuyendebiet = $array_chuyendebiet;
            }
			$config = Zend_Registry::get('config');
			try {
				$wsdl       = $config->service->lienthong->uri;
				$username   = $config->service->lienthong->username;
				$password   = $config->service->lienthong->password;
				$cliente    = new SoapClient($wsdl);
				$session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
			}catch (Exception $ex){
				
			}
			$modelChoTiepNhan = new ChoTiepNhanModel();
            $masovanbanLienThong = (int) $this->view->data['MASOLIENTHONG'];
            if($masovanbanLienThong > 0){
                //láy những đơn vị cũ
                $dongLuanChuyenLienThongTruocDo= $modelChoTiepNhan->DongLuanChuyen($masovanbanLienThong,$session);
                $cqLienThongTruocDo=array();
                foreach ($dongLuanChuyenLienThongTruocDo as $rowLuanChuyenTruocDo){
                    if($rowLuanChuyenTruocDo['DONVI_NHAN'] != '' && $rowLuanChuyenTruocDo['DONVI_NHAN'] != null){
                        $dataItemLuanChuyenTruocDo=array(
                            'DONVI'=>$rowLuanChuyenTruocDo['DONVI_NHAN'],
                            'NHIEMVU'=>$rowLuanChuyenTruocDo['NHIEMVU'],
                            'HANXULY'=>$rowLuanChuyenTruocDo['HANXULY'],
                            'TYPEHANXULY'=>$rowLuanChuyenTruocDo['TYPEHANXULY'],
                            'NGAYKETTHUC'=>  QLVBDHCommon::MysqlDateToVnDate($rowLuanChuyenTruocDo['NGAYKETTHUC']),
                            'MADVTT'=>  $rowLuanChuyenTruocDo['MADVTT']
                        );
                       //array_push($cqLienThongTruocDo, $rowLuanChuyenTruocDo['DONVI_NHAN']);
		       array_push($cqLienThongTruocDo, $dataItemLuanChuyenTruocDo);
                    }
                }
                $cqLienThongTruocDoUnique = array_map("unserialize", array_unique(array_map("serialize", $cqLienThongTruocDo)));
                $this->view->data["CQLIENTHONG_DRAFT"] = json_encode($cqLienThongTruocDoUnique);
			}
        } else {
            //truong hop them moi
            $this->view->title =  "Thêm mới văn bản Đi";
            //$this->view->subtitle = "Thêm mới";
            //Tao mot so gia tri mac dinh
            $data = array();
            $data["DOKHAN"] = 1;
            $data["DOMAT"] = 1;
            $data["NGAYBANHANH"] = date('Y-m-d');
            //$data["ID_CQ"] = ;
            $id_duthao = $param["idduthao"];
            if ($id_duthao > 0) {
                $this->view->title = "Ban hành văn bản dự thảo";
                //$this->view->subtitle = "Ban hành";
                $duthaoModel = new VanBanDuThaoModel($year);
                    $chuyenDvNgoaiDaTa= $giamSatGiaoViecModel->getGiaoViecChuyenDonViNgoai2($id_duthao); 
                    $this->view->chuyenDvNgoaiDaTa=$chuyenDvNgoaiDaTa;
                $getDuthao = $duthaoModel->fetchRow("ID_DUTHAO=" . $id_duthao);
                $vanBanDiModel = new VanBanDiModel();
                $maSoLienThong = $vanBanDiModel->findMaSoLienThongByHscv($getDuthao->ID_HSCV);
                $maDLCLienThong = $vanBanDiModel->findMaDLCLienThongByHscv($getDuthao->ID_HSCV);

                if ($getDuthao != null) {
                    $this->view->idduthao = $getDuthao->ID_DUTHAO;
                    $this->view->idhscv = $getDuthao->ID_HSCV;
                    $hscv = hosocongviecModel::getDetailById($getDuthao->ID_HSCV);
                    $vbdemodel = new vbdenModel($year);
                    $vbden = $vbdemodel->findByHscv($getDuthao->ID_HSCV);
                    $vbdeninfo = $vbdemodel->find($vbden['ID_VBD'])->current();
                    $this->view->ID_VBLTCP  = $vbdeninfo->ID_VBLTCP;  
                    $this->view->macongviec = $hscv['MACONGVIEC'];
//                    if($hscv['MACONGVIEC'] != '' || $hscv['MACONGVIEC'] != NULL)
//                    {
//                    $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
//                    $madonvi = $configlienthong->service->lienthong->username;
//                    $password = $configlienthong->service->lienthong->password;
//                    $giaoviecservice = new GiaoViecService();
//                    $token = $giaoviecservice->login($madonvi,md5($password),"");
//                    $reCongViec = $giaoviecservice->SelectCongViecByMacongviec(
//                            $token
//                            ,$hscv['MACONGVIEC']
//                    );
//                    $congviec = json_decode($reCongViec);
//                    $this->view->datacongviec = $congviec;
//                    $this->view->IS_DINHKY = $congviec->data[0]->IS_DINHKY;
//                    }
                    $data["TRICHYEU"] = $getDuthao->TENDUTHAO;
                    $data["ID_LVB"] = $getDuthao->ID_LVB;
                    $r = $duthaoModel->getAdapter()->query("SELECT * FROM " . QLVBDHCommon::Table("hscv_phienbanduthao") . " WHERE ID_DUTHAO=? AND CHONBANHANH=1 ORDER BY ID_PB_DUTHAO", $getDuthao->ID_DUTHAO);

                    $pbduthao = $r->fetch();
                    $this->view->id_pb_duthao = $pbduthao["ID_PB_DUTHAO"];
                    $r = $duthaoModel->getAdapter()->query("SELECT * FROM VB_SOVANBAN WHERE ID_LVB=? AND TYPE=2", $getDuthao->ID_LVB);
                    $svb = $r->fetch();
                    $data["ID_SVB"] = $svb["ID_SVB"];
                    $data["NGUOIKY"] = $getDuthao->NGUOIKY;
                    $data["NGUOISOAN"] = $getDuthao->NGUOISOAN;
                    $array_lcdb = array();
                    $i = 0;
                    if ($data["NGUOITAO"] > 0) {
                        //$array_lcdb[$i] = array();
                        //$array_lcdb[$i]["NG"] = $user->ID_U;
                        //$array_lcdb[$i]["EMPNN"] = UsersModel::getEmloyeeNameByIdUser($data["NGUOITAO"]);
                        //$i++;
                    }
                    if ($data["NGUOIKY"] > 0) {
                        $array_lcdb[$i] = array();
                        $array_lcdb[$i]["NGUOINHAN"] = $data["NGUOIKY"];
                        $array_lcdb[$i]["EMPNN"] = UsersModel::getEmloyeeNameByIdUser($data["NGUOIKY"]);
                        $dep_data = UsersModel::getUserDepId($data["NGUOIKY"]);
                        $array_lcdb[$i]["DEP_NN"] = $dep_data["ID_DEP"];
                    }
                    $this->view->cdb_macdinh = $array_lcdb;
                }
                $this->view->maSoLienThong = $maSoLienThong;                
                $this->view->maDLCLienThong = $maDLCLienThong;
                $this->view->id_duthao = $id_duthao;
                //check xem co the luu thu muc duoc khong
                $id_hscv = ($getDuthao->ID_HSCV);
                $getDuthao = $duthaoModel->fetchAll("ID_HSCV=" . $getDuthao->ID_HSCV . " AND TRANGTHAI<>2");

                $this->view->isluuthumuc = 0;

                $curtrans = WFEngine::GetCurrentTransitionInfoByIdHscv($id_hscv);

                if ($curtrans["ISLAST"] == 1) {

                    if (count($getDuthao) == 1) {
                        $this->view->isluuthumuc = 1;
                        $thumuc = array();
                        QLVBDHCommon::GetTreeWithJoinWhere(&$thumuc, "HSCV_THUMUC", "HSCV_PHANQUYEN_THUMUC", "ID_THUMUC", "ID_THUMUC", "ID_THUMUC", "ID_THUMUC_CHA", 1, 1, "(ISCOQUAN=1 OR ID_THUMUC_CHA IS NULL) AND HSCV_PHANQUYEN_THUMUC.QUYENXEM = 1 AND HSCV_PHANQUYEN_THUMUC.ID_DEP=" . (int) $user->ID_DEP);
                        $this->view->thumuc = $thumuc;
                    }
                }
            }            
            if((int)$this->view->idhscv>0){
                $giaoviecData= $giamSatGiaoViecModel->getInfoHSCVById($this->view->idhscv);
                $data["CAP_GIAOVIEC"] = $giaoviecData['CAP_GIAOVIEC'];
                $data["LOAICV_GIAOVIEC"] = $giaoviecData['LOAICV_GIAOVIEC'];
                $data["NOIDUNG_GIAOVIEC"] = $giaoviecData['NOIDUNG_GIAOVIEC'];
                $data["HANXULY_GIAOVIEC"] = $giaoviecData['HANXULY_GIAOVIEC'];
            }
            $this->view->data = $data;
            
        }
		
        $this->view->is_noibo = $param["is_noibo"];
        $this->view->id = $id;
        $svb = new SoVanBanModel();
        $this->view->svb = $svb->fetchAll();
        $lvb = new LoaiVanBanModel();
        $this->view->lvb = $lvb->fetchAll();
        $r = $lvb->getDefaultAdapter()->query("SELECT u.ID_U,dep.KYHIEU_PB FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP INNER JOIN QTHT_DEPARTMENTS dep on emp.ID_DEP = dep.ID_DEP");
        $this->view->ns = $r->fetchAll();
        $r = $lvb->getDefaultAdapter()->query("SELECT vb.ID_CQ,vb.KYHIEU,vb.NAME,svb.ID_SVB FROM VB_COQUAN vb left join vb_sovanban svb on vb.ID_CQ=svb.ID_CQ WHERE ISSYSTEMCQ=1");
        $this->view->cq = $r->fetchAll();
        
		$this->view->SAOY = $param["saoy"];
		// Chuc nang sao y
		if($param["saoy"]==1){
			$vanbandensaoy = vbdenModel::getDetails(QLVBDHCommon::GetYear(),$param["idvbd"]);
			if($vanbandensaoy["SOKYHIEU"]!=""){
				$this->view->data["NGAYBANHANH"] = $vanbandensaoy["NGAYBANHANH"];
				$this->view->data["ID_LVB"] = $vanbandensaoy["ID_LVB"];
				$this->view->data["TRICHYEU"] = $vanbandensaoy["TRICHYEU"];
			}
		}
    }
	
	/**
	* @create : 05-07-2013 17:33
	* @purpose : Xử lý dữ liệu cơ quan, hỗ trợ giao diện lựa chọn cơ quan nhận cho input
	* @author : baotq
	*/
    private function checkSokyhieu($sokyhieu, $id_cq, $id_lvb) {
        $year = QLVBDHCommon::getYear();
        $vbdiModel = new VanBanDiModel($year);
        $where = "";
        if ($id_cq != "")
            $where.=" AND ID_CQ=" . $id_cq;
        if ($id_lvb != "")
            $where.=" AND ID_LVB=" . $id_lvb;
        $id = $checkData['id'];
        if ($sokyhieu != null) {
            try {
                if ($id > 0) {
                    $tempData = $vbdiModel->fetchRow("SOKYHIEU='" . $sokyhieu . "' AND ID_VBDI <> " . $id . $where);
                } else {
                    $tempData = $vbdiModel->fetchRow("SOKYHIEU='" . $sokyhieu . "'" . $where);
                }

                if ($tempData != null)
                    return 1;
                else
                    return 2;
            } catch (Exception $e2) {
                return 3;
            }
        } else {
            return 0;
        }
    }

    private function checkSodi($sodi, $id_cq, $id_lvb, $id_svb) {
        $arr_data = $sodi_cu = Common_Sovanban::getCurrentSodi();
    }

    /**
     * Kiểm tra trạng thái số ký hiệu
     *
     */
    function checkAction() {
        $this->_helper->layout->disablelayout();
        $checkData = $this->getRequest()->getParams();
        //$year=QLVBDHCommon::getYear();
        //$vbdiModel=new VanBanDiModel($year);
        $sodi = $checkData['sodi'];
        $sokyhieu = $checkData['sokyhieu'];
        $id_cq = $checkData['id_cq'];
        $id_lvb = $checkData['id_lvb'];
        $id_svb = $checkData['id_svb'];
        $re = $this->checkSodi($sodi, $id_cq, $id_lvb, $id_svb);
        echo $re;
        exit;
    }

    /**
     * delete action
     *
     */
    function deleteAction() {
        $this->view->title = "Xóa văn bản đi";
        $year = QLVBDHCommon::getYear();
        //add button
        QLVBDHButton::AddButton("Danh sách", "/vbdi/banhanh/", "", "", 2);
        //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror = '';
        if ($this->_request->isPost()) {
            $idarray = $this->_request->getParam('DEL');

            if (count($idarray) <= 0) {
                $this->view->deleteError = "Không có mục nào để tiến hành xóa( xin vui lòng chọn một mục!)";
            }
            $counter = 0;
            while ($counter < count($idarray)) {

                if ($idarray[$counter] > 0) {
                    try {
                        $delLoai = new VanBanDiModel($year);
                        $where = 'ID_VBDI = ' . $idarray[$counter];
                        $delLoai->delete($where);
                    } catch (Exception $er) {
                        $adderror = $adderror . $idarray[$counter] . ' ; ';
                    };
                }
                $counter++;
            }
            //already delete some or all items
            if ($counter > 0) {
                if ($counter == count($idarray)) {
                    $this->view->deleteError = "Xóa thành công các mục đã chọn";
                    $this->_redirect('/vbdi/banhanh/');
                } else {
                    $this->view->deleteError = "Không xóa được mục đã chọn";
                }
            }
            if ($adderror != '') {
                $this->view->deleteError = "Xóa không thành công các mục với id= " . $adderror;
            }
        }
    }

    /*
     * Xem văn bản đi toàn cơ quan
     */

    public function listallAction() {
        global $auth;
        $user = $auth->getIdentity();
        $this->view->user = $user;
        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $config = Zend_Registry::get('config');
        $realyear = QLVBDHCommon::getYear();
        //tinh chỉnh param
        $ID_VBDI = $param['ID_VBDI'];
        $TRICHYEU = $param['TRICHYEU'];
        $ID_CQ = $param['ID_CQBH'];
        //$ID_CQBH 	= $param['ID_CQBH'];
        $ID_SVB = $param['ID_SVB'];
        $ID_LVVB = $param['ID_LVVB'];
        $ID_LVB = $param['ID_LVB'];
        $COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT'] == MSG11001017 ? '' : $param['COQUANBANHANH_TEXT'];
        $DOMAT = $param['DOMAT'];
        $DOKHAN = $param['DOKHAN'];
        $SOKYHIEU = $param["SOKYHIEU"];
        $SODI = $param["SODI"];
        $NGUOISOAN = $param["NGUOISOAN"];
        $IS_SEE_ALL = $param["IS_SEE_ALL"];
        if ($param['NGAYBANHANH_BD'] != "") {
            $ngaytao_bd = $param['NGAYBANHANH_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_bd = implode("-", array_reverse(explode("/", $ngaytao_bd)));
        }
        if ($param['NGAYBANHANH_KT'] != "") {
            $ngaytao_kt = $param['NGAYBANHANH_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_kt = implode("-", array_reverse(explode("/", $ngaytao_kt)));
        }

        $page = $param['page'];
        $limit = $param['limit1'];
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;
        /*
          $scope = array();
          if($param['SCOPE']){
          $scope = $param['SCOPE'];
          }
         */
        $INNAME = $param['INNAME'];
        $INFILE = $param['INFILE'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }
        $SORTBY = $param["SORTBY"];
        if (!$SORTBY)
            $SORTBY = "NGAYBANHANH";
        $SORTTYPE = $param['SORTTYPE'];
        if (!$SORTTYPE)
            $SORTTYPE = "DESC";

        if ($param['CHUA_DOC'] == 1) {
            $CHUA_DOC = $param['CHUA_DOC'];
        } else {
            
        }
        $parameter = array(
            "TRICHYEU" => $TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
            "ID_CQ" => $ID_CQ,
            //"ID_CQBH"=>$ID_CQBH,
            "ID_SVB" => $ID_SVB,
            "ID_LVVB" => $ID_LVVB,
            "ID_LVB" => $ID_LVB,
            "SODI" => $SODI,
            "COQUANBANHANH_TEXT" => $COQUANBANHANH_TEXT,
            "DOMAT" => $DOMAT,
            "DOKHAN" => $DOKHAN,
            "NGAYBANHANH_BD" => $ngaytao_bd,
            "NGAYBANHANH_KT" => $ngaytao_kt,
            "SOKYHIEU" => $SOKYHIEU,
            "ID_U" => $user->ID_U,
            "IS_SEE_ALL" => $IS_SEE_ALL,
            "SCOPE" => $scope,
            "INNAME" => $INNAME,
            "INFILE" => $INFILE,
            "SORTBY" => $SORTBY,
            "SORTTYPE" => $SORTTYPE,
            "CHUA_DOC" => $CHUA_DOC,
            "NGUOISOAN" => $NGUOISOAN
        );

        if ($SORTBY != "DAXEM") {
            if ($SORTTYPE != "") {
                $sort = $SORTBY . " " . $SORTTYPE;
            } else {
                $sort = $SORTBY;
            }
        } else {
            $sort = "NGAYBANHANH DESC";
        }
        //echo $sort; exit;
        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        $hscvcount = $hscv->Count_vbdi($parameter, 1);
        //Lấy dữ liệu
        $this->view->data = $hscv->SelectAll_vbdi($parameter, ($page - 1) * $limit, $limit, $sort, 1);
        //$this->view->ID_VBDI =
        $this->view->realyear = $realyear;
        $this->view->TRICHYEU = $TRICHYEU;
		$this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
        $this->view->NGUOISOAN = $NGUOISOAN;
        $this->view->ID_CQBH = $ID_CQ;
        $this->view->ID_VBDI = $ID_VBDI;
        //$this->view->ID_CQBH 		= $ID_CQBH;
        $this->view->ID_SVB = $ID_SVB;
        $this->view->ID_LVVB = $ID_LVVB;
        $this->view->ID_LVB = $ID_LVB;
        $this->view->SOKYHIEU = $SOKYHIEU;
        $this->view->SODI = $SODI;
        $this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
        $this->view->DOMAT = $DOMAT;
        $this->view->DOKHAN = $DOKHAN;
        $this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
        $this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
        $this->view->SCOPE = $scope;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;

        $this->view->SORTBY = $SORTBY;
        $this->view->SORTTYPE = $SORTTYPE;
        $this->view->user = $user;
        $this->view->vbdi = new VanBanDiModel(QLVBDHCommon::getYear());
        $this->view->IS_SEE_ALL = $IS_SEE_ALL;
        $this->view->ADVANCED = $param['ADVANCEDVALUE'];
        $this->view->CHUA_DOC = $CHUA_DOC;
        //page
        $this->view->title = "Danh sách văn bản đi toàn cơ quan";
        //$this->view->subtitle = "Danh sách toàn cơ quan";
        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/vbdi/banhanh/listall");
        $act = ResourceUserModel::getActionByUrl("vbdi", "banhanh", "input");
        //var_dump($act);
        if(hosocongviecModel::isAlowSeeAllVbDi()) QLVBDHButton::EnableVbdiChuyen('/vbdi/banhanh/list');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $act[0])) {           
            QLVBDHButton::EnableAddNew("/vbdi/banhanh/input");
        }
        //$this->view->arr_idnews = vbdi_dongluanchuyenModel::getIdVbdiChuaXemByIdUser($realyear,$user->ID_U);
        $this->view->year = $realyear;
    }

    /**
     * Xem list văn bản đi
     */
    public function listAction() {
        //$this->view->start = (float) array_sum(explode(' ',microtime()));
        global $auth;
        $user = $auth->getIdentity();
        $this->view->user = $user;
        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $config = Zend_Registry::get('config');

        $realyear = QLVBDHCommon::getYear();
        //tinh chỉnh param
        $ID_VBDI = $param['ID_VBDI'];
        $TRICHYEU = $param['TRICHYEU'];
        $ID_CQ = $param['ID_CQBH'];
        //$ID_CQBH 	= $param['ID_CQBH'];
        $ID_SVB = $param['ID_SVB'];
        $ID_LVVB = $param['ID_LVVB'];
        $ID_LVB = $param['ID_LVB'];
        $COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT'] == MSG11001017 ? '' : $param['COQUANBANHANH_TEXT'];
        $DOMAT = $param['DOMAT'];
        $DOKHAN = $param['DOKHAN'];
        $SOKYHIEU = $param["SOKYHIEU"];
        $SODI = $param["SODI"];
        $NGUOISOAN = $param["NGUOISOAN"];
        $NGUOIKY = $param["NGUOIKY"];
        $IS_SEE_ALL = $param["IS_SEE_ALL"];
        if ($param['NGAYBANHANH_BD'] != "") {
            $ngaytao_bd = $param['NGAYBANHANH_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_bd = implode("-", array_reverse(explode("/", $ngaytao_bd)));
        }
        if ($param['NGAYBANHANH_KT'] != "") {
            $ngaytao_kt = $param['NGAYBANHANH_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_kt = implode("-", array_reverse(explode("/", $ngaytao_kt)));
        }

        $page = $param['page'];
        $limit = $param['limit1'];
        if($limit==0 || $limit=="")$limit=$config->limit;
	if($page==0 || $page=="")$page=1;
        /*
          $scope = array();
          if($param['SCOPE']){
          $scope = $param['SCOPE'];
          }
         */
        $INNAME = $param['INNAME'];
        $INFILE = $param['INFILE'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }
        $SORTBY = $param["SORTBY"];
        if (!$SORTBY)
            $SORTBY = "NGAYBANHANH";
        $SORTTYPE = $param['SORTTYPE'];
        if (!$SORTTYPE)
            $SORTTYPE = "DESC";

        if ($param['CHUA_DOC'] == 1) {
            $CHUA_DOC = $param['CHUA_DOC'];
        } else {
            
        }

        $parameter = array(
            "TRICHYEU" => $TRICHYEU,
			"TRICHYEU_ISLIKE" => $param['TRICHYEU_ISLIKE'],
            "ID_CQ" => $ID_CQ,
            //"ID_CQBH"=>$ID_CQBH,
            "ID_SVB" => $ID_SVB,
            "ID_LVVB" => $ID_LVVB,
            "ID_LVB" => $ID_LVB,
            "SODI" => $SODI,
            "COQUANBANHANH_TEXT" => $COQUANBANHANH_TEXT,
            "DOMAT" => $DOMAT,
            "DOKHAN" => $DOKHAN,
            "NGAYBANHANH_BD" => $ngaytao_bd,
            "NGAYBANHANH_KT" => $ngaytao_kt,
            "SOKYHIEU" => $SOKYHIEU,
            "ID_U" => $user->ID_U,
            "IS_SEE_ALL" => $IS_SEE_ALL,
            "SCOPE" => $scope,
            "INNAME" => $INNAME,
            "INFILE" => $INFILE,
            "SORTBY" => $SORTBY,
            "SORTTYPE" => $SORTTYPE,
            "CHUA_DOC" => $CHUA_DOC,
            "NGUOISOAN" => $NGUOISOAN,
            "NGUOIKY" => $NGUOIKY
        );


        if ($SORTBY != "DAXEM") {
            if ($SORTTYPE != "") {
                $sort = $SORTBY . " " . $SORTTYPE;
            } else {
                $sort = $SORTBY;
            }
        } else {
            $sort = "NGAYBANHANH DESC";
        }
        //echo $sort; exit;
        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        $hscvcount = $hscv->Count_vbdi($parameter);

        //var_dump(($page - 1) * $limit.' '.$limit);
        //Lấy dữ liệu
        $this->view->data = $hscv->SelectAll_vbdi($parameter, ($page - 1) * $limit, $limit, $sort);
        //$this->view->ID_VBDI =
        $this->view->realyear = $realyear;
        $this->view->TRICHYEU = $TRICHYEU;
		$this->view->TRICHYEU_ISLIKE = $param['TRICHYEU_ISLIKE'];
        $this->view->NGUOISOAN = $NGUOISOAN;
        $this->view->NGUOIKY = $NGUOIKY;
        $this->view->ID_CQBH = $ID_CQ;
        $this->view->ID_VBDI = $ID_VBDI;
        //$this->view->ID_CQBH 		= $ID_CQBH;
        $this->view->ID_SVB = $ID_SVB;
        $this->view->ID_LVVB = $ID_LVVB;
        $this->view->ID_LVB = $ID_LVB;
        $this->view->SOKYHIEU = $SOKYHIEU;
        $this->view->SODI = $SODI;
        $this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
        $this->view->DOMAT = $DOMAT;
        $this->view->DOKHAN = $DOKHAN;
        $this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
        $this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
        $this->view->SCOPE = $scope;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;

        $this->view->SORTBY = $SORTBY;
        $this->view->SORTTYPE = $SORTTYPE;
        $this->view->user = $user;
        $this->view->vbdi = new VanBanDiModel(QLVBDHCommon::getYear());
        $this->view->IS_SEE_ALL = $IS_SEE_ALL;
        $this->view->ADVANCED = $param['ADVANCEDVALUE'];
        $this->view->CHUA_DOC = $CHUA_DOC;
        //page
        $this->view->title = "Danh sách văn bản đi";
        //$this->view->subtitle = "danh sách";

        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/vbdi/banhanh/list");
        $act = ResourceUserModel::getActionByUrl("vbdi", "banhanh", "input");
        //var_dump($act);
        if(hosocongviecModel::isAlowSeeAllVbDi())QLVBDHButton::EnableVbdiCoquan('/vbdi/banhanh/listall');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $act[0])) {            
            QLVBDHButton::EnableAddNew("/vbdi/banhanh/input");
        }
        //$this->view->arr_idnews = vbdi_dongluanchuyenModel::getIdVbdiChuaXemByIdUser($realyear,$user->ID_U);
        $this->view->year = $realyear;
    }

	public function listnoiboAction() {
        //$this->view->start = (float) array_sum(explode(' ',microtime()));
        global $auth;
        $user = $auth->getIdentity();
        $this->view->user = $user;
        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $config = Zend_Registry::get('config');

        $realyear = QLVBDHCommon::getYear();
        //tinh chỉnh param
        $ID_VBDI = $param['ID_VBDI'];
        $TRICHYEU = $param['TRICHYEU'];
        $ID_CQ = $param['ID_CQBH'];
        //$ID_CQBH 	= $param['ID_CQBH'];
        $ID_SVB = $param['ID_SVB'];
        $ID_LVVB = $param['ID_LVVB'];
        $ID_LVB = $param['ID_LVB'];
        $COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT'] == MSG11001017 ? '' : $param['COQUANBANHANH_TEXT'];
        $DOMAT = $param['DOMAT'];
        $DOKHAN = $param['DOKHAN'];
        $SOKYHIEU = $param["SOKYHIEU"];
        $SODI = $param["SODI"];
        $IS_SEE_ALL = $param["IS_SEE_ALL"];
        if ($param['NGAYBANHANH_BD'] != "") {
            $ngaytao_bd = $param['NGAYBANHANH_BD'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_bd = implode("-", array_reverse(explode("/", $ngaytao_bd)));
        }
        if ($param['NGAYBANHANH_KT'] != "") {
            $ngaytao_kt = $param['NGAYBANHANH_KT'] . "/" . QLVBDHCommon::getYear();
            $ngaytao_kt = implode("-", array_reverse(explode("/", $ngaytao_kt)));
        }

        $page = $param['page'];
        $limit = $param['limit1'];
        if($limit==0 || $limit=="")$limit=$config->limit;
	if($page==0 || $page=="")$page=1;
        /*
          $scope = array();
          if($param['SCOPE']){
          $scope = $param['SCOPE'];
          }
         */
        $INNAME = $param['INNAME'];
        $INFILE = $param['INFILE'];
        if ($param['INNAME'] == 0 && $param['INFILE'] == 0) {
            $INNAME = 1;
        }
        $SORTBY = $param["SORTBY"];
        if (!$SORTBY)
            $SORTBY = "NGAYBANHANH";
        $SORTTYPE = $param['SORTTYPE'];
        if (!$SORTTYPE)
            $SORTTYPE = "DESC";

        if ($param['CHUA_DOC'] == 1) {
            $CHUA_DOC = $param['CHUA_DOC'];
        } else {
            
        }

        $parameter = array(
            "TRICHYEU" => $TRICHYEU,            
            //"ID_CQBH"=>$ID_CQBH,
            "ID_SVB" => $ID_SVB,
            "ID_LVVB" => $ID_LVVB,
            "ID_LVB" => $ID_LVB,
            "SODI" => $SODI,            
            "DOMAT" => $DOMAT,
            "DOKHAN" => $DOKHAN,
            "NGAYBANHANH_BD" => $ngaytao_bd,
            "NGAYBANHANH_KT" => $ngaytao_kt,
            "SOKYHIEU" => $SOKYHIEU,
            "ID_U" => $user->ID_U,
            "IS_SEE_ALL" => $IS_SEE_ALL,
            "SCOPE" => $scope,
            "INNAME" => $INNAME,
            "INFILE" => $INFILE,
            "SORTBY" => $SORTBY,
            "SORTTYPE" => $SORTTYPE,
            "CHUA_DOC" => $CHUA_DOC,
			"IS_NOIBO" =>1
        );


        if ($SORTBY != "DAXEM") {
            if ($SORTTYPE != "") {
                $sort = $SORTBY . " " . $SORTTYPE;
            } else {
                $sort = $SORTBY;
            }
        } else {
            $sort = "NGAYBANHANH DESC";
        }
        //echo $sort; exit;
        //Tạo đối tượng
        $hscv = new hosocongviecModel();
        $hscvcount = $hscv->Count_vbdi($parameter);

        //var_dump(($page - 1) * $limit.' '.$limit);
        //Lấy dữ liệu
        $this->view->data = $hscv->SelectAll_vbdi($parameter, ($page - 1) * $limit, $limit, $sort);
        //$this->view->ID_VBDI =
        $this->view->realyear = $realyear;
        $this->view->TRICHYEU = $TRICHYEU;
        $this->view->ID_CQBH = $ID_CQ;
        $this->view->ID_VBDI = $ID_VBDI;
        //$this->view->ID_CQBH 		= $ID_CQBH;
        $this->view->ID_SVB = $ID_SVB;
        $this->view->ID_LVVB = $ID_LVVB;
        $this->view->ID_LVB = $ID_LVB;
        $this->view->SOKYHIEU = $SOKYHIEU;
        $this->view->SODI = $SODI;
        $this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
        $this->view->DOMAT = $DOMAT;
        $this->view->DOKHAN = $DOKHAN;
        $this->view->NGAYBANHANH_BD = $param['NGAYBANHANH_BD'];
        $this->view->NGAYBANHANH_KT = $param['NGAYBANHANH_KT'];
        $this->view->SCOPE = $scope;
        $this->view->INNAME = $INNAME;
        $this->view->INFILE = $INFILE;

        $this->view->SORTBY = $SORTBY;
        $this->view->SORTTYPE = $SORTTYPE;
        $this->view->user = $user;
        $this->view->vbdi = new VanBanDiModel(QLVBDHCommon::getYear());
        $this->view->IS_SEE_ALL = $IS_SEE_ALL;
        $this->view->ADVANCED = $param['ADVANCEDVALUE'];
        $this->view->CHUA_DOC = $CHUA_DOC;
        //page
        $this->view->title = "Danh sách văn bản nội bộ";
        //$this->view->subtitle = "danh sách";

        $this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount, 10, $limit, "frm", $page, "/vbdi/banhanh/listnoibo");            
        //$this->view->arr_idnews = vbdi_dongluanchuyenModel::getIdVbdiChuaXemByIdUser($realyear,$user->ID_U);
        $this->view->year = $realyear;
    }

    /**
      @author : trunglv
      @des: Ham ajax tai server nhan du lieu la so di , loai van ban, so van ban , co quan ban hanh
      Ham tra ve so di : Neu tham so type = 2
      Ham tra ve ket qua : Neu tham so type = 1
     */
    function getsodiAction() {
        $params = $this->_request->getParams();
        $type = $params["type"];
        $year = QLVBDHCommon::getYear();
        $is_update = $params["is_update"];
        $id = $params["id"];
        if ($id > 0) {
            //lay cac truon hop cu trong csdl
            $old_data = VanBanDiModel::getDetail($id, $year);
            $arr_col = Common_Sovanban::getColumNameGroup(2);
            $len_arr = count($arr_col);
            $old_sodi = $old_data["SODI"];
            $old_cq = $old_data["ID_CQ"];
            $aff_old = 0;
            $col_name = "";
            foreach ($arr_col as $itcol) {
                switch ($itcol) {
                    case 'ID_SVB':
                        $aff_old = $old_data["ID_SVB"];
                        $col_name = 'id_svb';
                        break;
                    case 'ID_LVB':
                        $aff_old = $old_data["ID_LVB"];
                        $col_name = 'id_lvb';
                        break;
                    default:
                        break;
                }
                if ($old_cq == $params["id_cq"] && $aff_old == $params[$col_name]) {

                    echo -2; //truong hop giu nguyen so di
                    exit;
                }
            }
        }
        if ($type == 2) {
            //truong hop muon lay so di
            $year = QLVBDHCommon::getYear();
            $array = array();
            $array["ID_CQ"] = $params["id_cq"];
            $array["ID_LVB"] = $params["id_lvb"];
            $array["ID_SVB"] = $params["id_svb"];
            $result = Common_Sovanban::getCurrentSodi($year, $array);
            if ($result == "")
                echo 0;
            else
                echo $result;
        }else if ($type == 1) {
            //truong hop nguoi dung tu nhap so di khi da nhap cac truong khac nhu co quan ban hanh , loai van ban , ....
            $year = QLVBDHCommon::getYear();
            $array = array();
            $array["ID_CQ"] = $params["id_cq"];
            $array["ID_LVB"] = $params["id_lvb"];
            $array["ID_SVB"] = $params["id_svb"];
            $arr_col = Common_Sovanban::getColumNameGroup(2);
            foreach ($arr_col as $item) {
                if ($array[$item] == 0) {
                    echo -1;
                    exit;
                }
            }
            $result = Common_Sovanban::getCurrentSodi($year, $array);
            if ($result == "")
                echo 0;
            else {
                //if($result > $params["sodi"] ) echo 1;
                //else echo 0;
                echo $result;
            }
        }
        exit;
    }

    function detailAction() {
        $params = $this->_request->getParams();
        $id_vbdi = $params['id_vbdi'];
        $year = $params['year'];
        $this->view->data = VanBanDiModel::getDetail($id_vbdi, $year);
    }

    /**
      Lay danh sach dong luan chuyen, chuyen de biet
     */
    public function wayAction() {
        $this->_helper->layout->disableLayout();

        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $id_vbdi = $param["id"];
        $year = $param["year"];
        $this->view->type = $type;
        $this->view->year = $year;
        $this->view->idobject = $id_vbdi;
        $this->view->way = vbdi_dongluanchuyenModel::way($year, $id_vbdi);
        //kiem tra dieu kien truy cap
        $isreadonly = $this->_request->getParam('isreadonly');
        if (!$isreadonly)
            $isreadonly = 0;
        require('hscv/models/chuyennoiboModel.php');
        $this->view->lcnoibo = chuyennoiboModel::vbdi_getLuanchuyennoibo($id_vbdi);
		$this->view->sendprocess = array();
		$hscv = VanBanDiModel::GetAllHSCV($id_vbdi);
		foreach($hscv as $itemhscv){
			$arr_id[] = $itemhscv['ID_HSCV'];
		}		
		foreach ($arr_id as $idhscv)
		{			
			$sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);			
			array_push($this->view->sendprocess,$sendprocess);		
			
		}
    }

    function sendAction() {
        $this->_helper->layout->disableLayout();
        //Lấy parameter
        $param = $this->getRequest()->getParams();
        $id = $param["id"];
        $year = $param["year"];
        $this->view->id = $id;
        $this->view->year = $year;
    }

    function savesendAction() {
        global $auth;
        $user = $auth->getIdentity();

        $param = $this->getRequest()->getParams();
        $id = $param["id"];
        $year = $param["year"];
        $lc = new vbdi_dongluanchuyenModel($year);
        $lc->send($id, $param['ID_U'], $param['NOIDUNG'], $user->ID_U);
        echo "<script>window.parent.loadDivFromUrl('groupcontent" . $id . "','/vbdi/banhanh/way/id/" . $id . "/year/" . $year . "',1);</script>";
        exit;
    }

    function updatedadocAction() {
		global $db;
        $params = $this->_request->getParams();
        $id_vbdi = $params['id_vbdi'];
        $id_u = $params['id_u'];
        $year = $params['year'];
		$reload = $params['reload'];
		if($reload!=1){
			echo vbdi_dongluanchuyenModel::updateDaDoc($year, $id_vbdi, $id_u);
		}else{
			vbdi_dongluanchuyenModel::updateDaDoc($year, $id_vbdi, $id_u);
			$cdb_vbdi = $db->query("
			SELECT vb.ID_VBDI, TRICHYEU, SOKYHIEU, NGAYBANHANH
			FROM ".QLVBDHCommon::Table("vbdi_vanbandi")." vb
			INNER JOIN ".QLVBDHCommon::Table("vbdi_dongluanchuyen")." lc on lc.ID_VBDI=vb.ID_VBDI
			WHERE
				lc.NGUOINHAN = ? AND lc.DA_XEM=0
			GROUP BY vb.ID_VBDI
			LIMIT 0,3
			",$id_u)->fetchAll();
			$count_cdb_vbdi = $db->query("
			SELECT count(*) as C FROM (SELECT vb.ID_VBDI
			FROM ".QLVBDHCommon::Table("vbdi_vanbandi")." vb
			INNER JOIN ".QLVBDHCommon::Table("vbdi_dongluanchuyen")." lc on lc.ID_VBDI=vb.ID_VBDI
			WHERE
				lc.NGUOINHAN = ? AND lc.DA_XEM=0
			GROUP BY vb.ID_VBDI) t
			",$id_u)->fetch();
			$count_cdb_vbdi = $count_cdb_vbdi["C"];
			$html ="
			<div class=\"home-module-title\"><span>Văn bản đi ".($count_cdb_vbdi>0?"(".$count_cdb_vbdi.")":" (0)")."</font></span></div>";
			if(count($cdb_vbdi)>0){
				$html .="
				<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"home-grid\">
					<tr>
						<th nowrap>Số ký hiệu</th>
						<th width=\"100%\">Trích yếu</th>
						<th nowrap>Ngày ban hành</th>
						<th nowrap></th>
					</tr>";
					foreach ($cdb_vbdi as $vbdi_item){
				$html .="
					<tr>
						<td align=\"left\" nowrap><a href=
								\"/vbdi/banhanh/list/CHUA_DOC/1/ID_VBDI/".$vbdi_item['ID_VBDI']."#vbden".$vbdi_item['ID_VBDI']."\">".$vbdi_item["SOKYHIEU"]."</a></td>
						<td align=\"left\" width=\"100%\"><a href=
								\"/vbdi/banhanh/list/CHUA_DOC/1/ID_VBDI/".$vbdi_item['ID_VBDI']."#vbden".$vbdi_item['ID_VBDI']."\">".htmlspecialchars($vbdi_item["TRICHYEU"])."</a></td>
						<td align=\"left\" nowrap>".QLVBDHCOMMON::MysqlDateToVnDate($vbdi_item["NGAYBANHANH"])."</td>
						<td align=\"left\" nowrap><a href=\"#\" onclick=\"loadDivFromUrl('vanbandi','/vbdi/banhanh/updatedadoc?year=".QLVBDHCommon::getYear()."&id_vbdi=".$vbdi_item['ID_VBDI']."&id_u=".$id_u."&reload=1',1); return false;\">Đã xem</a></td>
					</tr>";
						}
					$html .= "</table>";
			}else{
				$html .= "
				<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"home-grid\">
				<tr><th style='text-align:left'>Không có văn bản đi mới</th></tr>
				</table>
				";
			}
			echo $html;
		}
        exit;
    }

    function vbdenlienquanAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $id_vbdi = $params["id"];
        $year = QLVBDHCommon::getYear();
        $this->view->data = VanBanDiModel::getListVanbandenByIdVbdi($id_vbdi, $year);
    }

    function getsvbbycoquanbhAction() {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        //$arr_svb = SoVanBanModel::getDataByCQ(QLVBDHCommon::getYear(), $params["COQUANBH"], 1);
        $arr_svb = SoVanBanModel::selectWithDepAndCQ($params["COQUANBH"],1);
        echo json_encode($arr_svb);
        exit;
    }

//end trunglv


    function callimportAction() {
        //nhan id van ban di vao
        //select noi dung van ban di dua vao id

        $this->_helper->layout->disableLayout();

        $param = $this->getRequest()->getPost();

        require_once('nusoap/nusoap.php');

        $client_import = new nusoap_client("http://www.danangcity.gov.vn/serviceVBCDDH/Service?WSDL", true);
//new SoapClient("http://www.danangcity.gov.vn/serviceVBCDDH/Service?WSDL");

        $array_import = array("p_strDocNoSign" => "15-STTTT", "p_strDocOverview" => "Sở Thông Tin Truyền Thông", "p_strSigner" => "Phạm Kim Sơn", "p_iDocTypeID" => "2", "p_strOfficePublishment" => "800", "p_strDocScope" => "675");
        //echo = $client->getError();

        $result = $client_import->call('importDocument', $array_import);
        print_r($result);
        //echo $client_import->responseData;
        //echo "dd";
        exit;
    }
    
    /**
    * Thu hồi văn bản liên thông
    * @name errorCode : Mã lỗi gửi đến cho trình duyệt
    * @author : Baotq
    */
    function thuhoivanbanlienthongAction() {
        $errorCode = '';
        $params = $this->_request->getParams();        
        $config = Zend_Registry::get('config');
        try {
            $wsdl       = $config->service->lienthong->uri;
            $username   = $config->service->lienthong->username;
            $password   = $config->service->lienthong->password;
            $cliente    = new SoapClient($wsdl);
            $session = $cliente->__call('Login',array("madonvi"=>$username,'password'=>$password));
        
            if($session == '0'){                
                $errorCode = 'ERR_VBDI_BANHANH_THUHOIVANBANLIENTHONG_01';
            }else{
                $maSoLienThong = base64_encode($params['MASOLIENTHONG']);            
                $param = array(
                'session'  =>  $session,
                'service_code' => 'VANBAN',
                'service_name' => 'THUHOIVANBAN',
                'parameter' => $maSoLienThong
                 );
                 
                $result = $cliente->__call('Execute', $param);
                $year = QLVBDHCommon::getYear();
                $model = new VanBanDiModel($year);
                $model->updateThuHoiVanBan($params['MASOLIENTHONG']);
                
                ajax::ship('rsThuHoiVanBan',$result);
                ajax::ship('maVbDi',$params['MAVBDI']);
            }
        }catch (Exception $ex){
            ajax::ship('rsThuHoiVanBan',$ex->__toString());
        }
        ajax::ship('error',$errorCode);
        exit;
    }
    function callattachAction() {
        $this->_helper->layout->disableLayout();

        $param = $this->getRequest()->getPost();
        //	ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
        header("Content-Type: text/xml; charset=utf-8");
        //include('vbdi/models/nusoap.php');
        //$query = isset($HTTP_RAW_POST_DATA)? $HTTP_RAW_POST_DATA : 0 ;

        $client_attach = new SoapClient("http://www.danangcity.gov.vn/serviceVBCDDH/Service?WSDL", array('soap_version' => SOAP_1_2,
                    'encoding' => UTF - 8, 'Content-Type' => text / xml, 'charset' => utf - 8));
        //var_dump($client->__getTypes());
        $array_attach = array("p_iOfficialDocID" => "12", "p_strFileName" => "Tên file", "p_strFileType" => "Word Document", "p_iFileSize" => "12", "p_strFileContent" => "Thử nghiệm", "p_strDocScope" => "675");
        echo $client_attach->attachAttachmentFile($array_attach);
        //echo $client->responseData;
        exit;
    }

}
