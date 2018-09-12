<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'taphscv/models/Taphscv_TaphosocongviecModel.php';
require_once 'taphscv/models/Taphscv_phanquyenModel.php';
require_once 'taphscv/models/Taphscv_thumucModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'vbdi/models/VanBanDiModel.php';

class Taphscv_taphscvController extends Zend_Controller_Action {

    private $getParameters;
    private $model;
    var $object;

    function init() {

        $this->_helper->Layout->disableLayout();
        $this->getParameters = $this->getRequest()->getParams();
        $this->model = new Taphscv_TaphosocongviecModel();
        $this->view->title = "Hồ sơ công việc điện tử";

        global $auth;
        $user = $auth->getIdentity();
        $this->object = new Taphscv();
        $this->object->_id_u = $user->ID_U;
        $this->object->_id_g = $user->ID_G;
        $this->object->_id_dept = $user->ID_DEP;
        parent::init();
    }

    function indexAction() {
        $this->_redirect('/taphscv/taphscv/list');
    }
    
    function excelAction() {
        $config = Zend_Registry::get("config");
        $listId = $this->getParameters['idTapHoSo'];
        $listTapHoSo = implode(",", $listId);
        $data = $this->model->SelectAllReportData($listTapHoSo);
        $this->view->config = $config;
        header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=DanhmuctapHSCV.xls;");
		header("Pragma: no-cache");
		header("Expires: 0");
        $this->_helper->Layout->disableLayout();
        $this->view->data = $data;
    }
    function listAction() { 
        $this->view->datathumuc = array();
        // QLVBDHCommon::GetTree(&$this->view->datathumuc, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        Taphscv_thumucModel::GetTree(null,1,&$this->view->datathumuc,"hscvdt_thumuc_taphoso","ID_THUMUC","ID_THUMUC_PARENT",1,1);
        //bật layout
        $this->_helper->Layout->enableLayout();
        //thiết lập title
        $this->view->title = "Danh sách hồ sơ công việc điện tử";
        //bật các button
        QLVBDHButton::EnableAddNew('/taphscv/taphscv/input', "Thêm mới HSCV điện tử");
        QLVBDHButton::AddButton("Thư mục HSCV điện tử", '/taphscv/thumuc/', "AddNewButton", 'AddthumucHSCVdt()');
        
        $this->view->id_thumuc = $this->getParameters['ID_THUMUC'];
		$this->view->id_taphscv = $this->getParameters['id_taphscv'];
		$this->view->yeucau = $this->getParameters['yeucau'];
        //Data phần tập hồ sơ
        $config = Zend_Registry::get('config');
        $limit = $this->getParameters["limit1"];
        $page = $this->getParameters["page"];
        $search = $this->getParameters["search"];

        //Refinde parameters
        if ($limit == 0 || $limit == ""
            )$limit = $config->limit;
        if ($page == 0 || $page == ""
            )$page = 1;

        $this->model->_search = $search;
        $this->model->_idthumuc = $this->getParameters['ID_THUMUC'];
            
        $rowcount = $this->model->Counts();
        //$this->view->data = $this->model->SelectAll(($page - 1) * $limit, $limit, "NGAYTAO");
        //get all hscvdt by status
        $status =(int) $this->getParameters['selStatus'];
        $this->view->status = $status;
        //if($this->getRequest()->isPost() && isset($_POST['selStatus']))
        //{
            $this->view->data = $this->model->SelectAllByStatus(($page - 1) * $limit, $limit, "NGAYTAO", $status);
        // }        
        $this->view->showPage = QLVBDHCommon::Paginator($rowcount, 5, $limit, "frm", $page);

        $this->view->limit = $limit;
        $this->view->search = $search;
        $this->view->page = $page;
        $this->view->ThumucModel = new Taphscv_phanquyenModel();
        $this->view->object = $this->object;  
        global $auth;
        $user=$auth->getIdentity();
        $this->view->user= $auth->getIdentity();
        $db= Zend_Db_Table::getDefaultAdapter();
        $sql="SELECT ID_TAPHOSO FROM hscvdt_muonhoso WHERE NGUOIMUON=$user->ID_U AND IS_DUYET=0 AND IS_YEUCAU <> 1 ";
        $re=$db->query($sql);
        $result=$re->fetchAll();
        $arr=array();
        foreach ($result as $value) {
            $arr[]=$value['ID_TAPHOSO'];
        }
       $this->view->data_muonhs=$arr;
        
        
    }

    function inputAction() {

        $this->_helper->Layout->enableLayout();
        //lấy thư mục
        $this->view->data = array();
        // QLVBDHCommon::GetTree(&$this->view->data, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        Taphscv_thumucModel::GetTree(null,1,&$this->view->data, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        //bật các button
        QLVBDHButton::EnableSave('/taphscv/taphscv/save');
        QLVBDHButton::EnableBack('/taphscv/taphscv/list');

        $id_taphscv = $this->getParameters['ID_TAPHOSO'];
        $this->view->id = $id_taphscv;
        //set subtitle
        if ($id_taphscv == 0)
            $this->view->title = 'Thêm mới hồ sơ công việc điện tử';
        else
            $this->view->title = 'Cập nhật hồ sơ công việc điện tử';

        //lấy dữ liệu cho trường hợp cập nhật
        $this->view->data = $this->model->GetTapHSCVById($id_taphscv);
        $this->view->datathumuc = array();
        // QLVBDHCommon::GetTree(&$this->view->datathumuc, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        Taphscv_thumucModel::GetTree(null,1,&$this->view->datathumuc, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        $this->view->ID_THUMUC_PARENT = $this->view->data['ID_THUMUC'];
    }

    function saveAction() {
        $this->_helper->Layout->enableLayout();
        //lấy CODE mới        
        $code = Common_Maso::getCODEByDep($this->object);
        $lenght = strlen($code);
        $new_code = Common_Maso::MaSoProcess($code, $lenght);

        $id_taphscv = $this->getParameters['ID_TAPHOSO'];
        $id_thumuc = $this->getParameters['choiceLVCha'];
        $ten = $this->getParameters['name'];
        $d = $this->getParameters['NGAYTAO'];
//        $date = date('Y/m/d', strtotime($d));
        $date = Taphscv_taphscvController::display($d);
//        var_dump($date);exit;
//        $date = date('Y-m-d', strtotime($d));

        $numPage = $this->getParameters['numPage'];
        $numDocument = $this->getParameters['numDocument'];
        $numNgan = $this->getParameters['numNgan'];
        $numKe = $this->getParameters['numKe'];
        $numTang = $this->getParameters['numTang'];
        $chkStatus = $this->getParameters['chkStatus'];
        if(isset($_POST['chkStatus']))
        {
            $chkStatus = 1;
        }else {
            $chkStatus = 0;
        }
        //echo (int)$id_taphscv;exit;
        if ((int) $id_taphscv == 0) {
            $this->model->InsertTapHSCV(
                    array(
                        'ID_DEP' => $this->object->_id_dept,
                        'ID_THUMUC' => $id_thumuc,
                        'TEN' => $ten,
                        'NGUOITAO' => $this->object->_id_u,
                        'NGAYTAO' => $date,
                        'SOTRANG' => $numPage,
                        'SOTAILIEU' => $numDocument,
                        'NGAN' => $numNgan,
                        'KE' => $numKe,
                        'TANG' => $numTang,
                        'TRANGTHAI' => $chkStatus,
                        'CODE' => $new_code
                    ), $id_taphscv
            );
        } else {
            $this->model->InsertTapHSCV(
                    array(
                        'ID_THUMUC' => $id_thumuc,
                        'TEN' => $ten,
                        'SOTRANG' => $numPage,
                        'SOTAILIEU' => $numDocument,
                        'NGAN' => $numNgan,
                        'KE' => $numKe,
                        'TANG' => $numTang,
                        'TRANGTHAI' => $chkStatus,
                        'CODE' => $new_code,
                        'NGAYTAO' => $date
                    ), $id_taphscv
            );
        }
        $this->_redirect('/taphscv/taphscv/list');
    }
    
    static function display($param) 
    {
        $arr=explode(" ", $param); 
        $year=substr($arr[0], -4);
        $month=substr($arr[0], 3, 2);
        $day=substr($arr[0],0, 2);
        return $year.'/'.$month.'/'.$day;
    }
    
    function detailAction() {

        $id = $this->getParameters['ID_TAPHOSO'];
        $this->view->data = $this->model->GetTapHSCVById($id);
    }

    function listforaddAction() {        

        $idobject = $this->getParameters['idobject'];
        $loai = $this->getParameters['loai'];

        $this->view->idobject = $idobject;
        $this->view->loai = $loai;

        $data = $this->model->GetTapHSCVForAddVanban($idobject, $loai, $this->object);

        $this->view->data = $data;
    }

    function addvanbantotaphosoAction() {

        $idobject = $this->getParameters['idobject'];
        $loai = $this->getParameters['loai'];
        $idtaphoso = $this->getParameters['idtaphoso'];
        $r = $this->model->AddVanBanToTapHSCV($idtaphoso, $idobject, $loai);
        
        if ($r == 1) {
            if ($loai == 1) {
                echo "<script>window.parent.document.location = '/vbden/vbden/list';</script>";
                exit;
            }
            if ($loai == 2) {
                echo "<script>window.parent.document.location = '/vbdi/banhanh/list';</script>";
                exit;
            }
            if ($loai == 4) {
                echo "<script>window.parent.document.location = '/hscv/hscv/list';</script>";
                exit;
            }
        }
    }

    function deletevbAction() {

        $idvb = $this->getParameters['id_vb'];
        $idtaphoso = $this->getParameters['id_taphoso'];
        $this->model->DeleteVanBan($idvb);
        echo "SwapDiv(" . $idtaphoso . ",'/taphscv/taphscv/vblist/idtaphoso/" . $idtaphoso . "',1);";
        exit;
    }

    function deleteAction() {

        $idtaphoso = $this->getParameters['ID_TAPHOSO'];
        $idthumuc = $this->getParameters['ID_THUMUC'];
        $this->model->DeleteTapHSCV($idtaphoso);
        $this->_redirect("/taphscv/taphscv/list/ID_THUMUC/$idthumuc");
    }

    function vblistAction() {        

        $idtaphoso = $this->getParameters['idtaphoso'];
        $this->view->idtaphoso = $idtaphoso;
        $data = $this->model->GetVanBan($idtaphoso);
        $this->view->data = $data;
        $this->view->yeucau = $this->model->GetYeuCauFromTapHscv($idtaphoso);

        $this->view->ThumucModel = new Taphscv_phanquyenModel();
        $this->view->object = $this->object;
        $this->view->model = $this->model;
    }

    /*
     * Action để test mã số
     */

    function testmasoAction() {

        global $auth;
        $user = $auth->getIdentity();
        $this->object = new Taphscv();
        $this->object->_id_dept = $user->ID_DEP;
        echo Common_Maso::getMaSo(4, $this->object);
        exit;
    }

    function detailvbAction() {
        global $db;

        $id_vb = $this->getParameters['id_vb'];
        $this->view->idvb = $id_vb;
        $vb = $db->query("SELECT * FROM HSCVDT_VANBAN WHERE ID_VB=?", $id_vb)->fetch();
        switch ($vb['LOAI']) {
            case 1:
                $sql = "
                                    SELECT
                                            vb.*, lvb.NAME as LVBNAME, lvvb.NAME as LVVBNAME,
                                            svb.NAME as SVBNAME, cq.NAME as CQNAME,
                                            concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME
                                    FROM
                                            VBD_VANBANDEN_" . $vb['NAM'] . " vb
                                            left join VB_LOAIVANBAN lvb on vb.ID_LVB = lvb.ID_LVB
                                            left join VB_LINHVUCVANBAN lvvb on vb.ID_LVVB =lvvb.ID_LVVB
                                            left join VB_SOVANBAN svb on vb.ID_SVB = svb.ID_SVB
                                            left join VB_COQUAN cq	on vb.ID_CQ = cq.ID_CQ
                                            left join QTHT_USERS u on vb.NGUOITAO = u.ID_U
                                            left join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
                                    WHERE
                                            vb.ID_VBD = ?
                            ";
                $sqldk = "
                                    SELECT
                                            dk.*
                                    FROM GEN_FILEDINHKEM_" . $vb['NAM'] . " dk
                                    WHERE
                                             ID_OBJECT = ?
                                             and
                                             TYPE = 3
                    ";
                $this->view->data = $db->query($sql, $vb['ID_OBJECT'])->fetch();
                $this->view->datadk = $db->query($sqldk, $vb['ID_OBJECT'])->fetchAll();
                break;
            case 2:
                $sql = "
                                    select * from vbdi_vanbandi_" . $vb['NAM'] . " where ID_VBDI=?
                            ";
                $sqldk = "
                                    SELECT
                                            dk.*
                                    FROM GEN_FILEDINHKEM_" . $vb['NAM'] . " dk
                                    WHERE
                                             ID_OBJECT = ?
                                             and
                                             TYPE = 5
                    ";
                $this->view->data = $db->query($sql, $vb['ID_OBJECT'])->fetch();
                $this->view->datadk = $db->query($sqldk, $vb['ID_OBJECT'])->fetchAll();
                break;
            case 3:
                $sqldk = "
                                    SELECT
                                            *
                                    FROM HSCVDT_DINHKEM_TAPHOSO dk
                                    WHERE
                                             ID_DINHKEM = ?
                    ";
                $this->view->data = $vb;
                $this->view->datadk = $db->query($sqldk, $vb['ID_OBJECT'])->fetchAll();
                break;
        }

        $this->view->loai = $vb['LOAI'];
        $this->view->nam = $vb['NAM'];
    }

    function downloadAction() {
        global $db;
        $idvb = $this->getParams['id_vb'];
        $loai = $this->getParameters['loai'];
        $nam = $this->getParameters['nam'];
        $maso = $this->getParameters['maso'];
        $config = Zend_Registry::get('config');
        switch ($loai) {
            case 1:
                $sql = "SELECT * FROM GEN_FILEDINHKEM_" . $nam . " WHERE MASO='" . $maso . "'";
                $re = $db->query($sql)->fetch();
                $pathFile = $config->file->root_dir . DIRECTORY_SEPARATOR . $re['NAM'] . DIRECTORY_SEPARATOR . $re['THANG'] . DIRECTORY_SEPARATOR . $re['MASO'];
                $filename = $re['FILENAME'];
                $mime = $re['MIME'];
                break;
            case 2:
                $sql = "SELECT * FROM GEN_FILEDINHKEM_" . $nam . " WHERE MASO='" . $maso . "'";
                $re = $db->query($sql)->fetch();
                $pathFile = $config->file->root_dir . DIRECTORY_SEPARATOR . $re['NAM'] . DIRECTORY_SEPARATOR . $re['THANG'] . DIRECTORY_SEPARATOR . $re['MASO'];
                $filename = $re['FILENAME'];
                $mime = $re['MIME'];
                break;
            case 3:
                $sql = "select * from hscvdt_dinhkem_taphoso where FILECODE='" . $maso . "'";
                $re = $db->query($sql)->fetch();
                $pathFile = $config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $re['FILECODE'];

                $filename = $re['FILENAME'];
                $mime = $re['FILEMIME'];
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type:" . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo file_get_contents($pathFile);
        exit;
    }

    function chuyenAction() {

        $idvb = $this->getParameters['idvb'];
        $this->view->idvb = $idvb;
        $idtaphoso = $this->getParameters['idtaphoso'];
        $this->view->idtaphoso = $idtaphoso;
        $this->view->IdFrame = $this->getParameters['IdFrame'];
        //get all thu muc
        $this->view->thumuc = array();
        // QLVBDHCommon::GetTree(&$this->view->thumuc,"hscvdt_thumuc_taphoso","ID_THUMUC","ID_THUMUC_PARENT",1,1);
        Taphscv_thumucModel::GetTree(null,1,&$this->view->thumuc,"hscvdt_thumuc_taphoso","ID_THUMUC","ID_THUMUC_PARENT",1,1);
        //load hscv by id thu muc
        $idthumuc = $this->getParameters['thumuc'];        
        if ($idthumuc != 0 && $idtaphoso != 0) {
            $this->view->idthumuc = $idthumuc;
            $dataThumuc = $this->model->GetHSCVByThumuc($idthumuc, $idtaphoso);
            $this->view->data = $dataThumuc;            
        }else{
            $dataThumuc = $this->model->GetAllHSCV();
            $this->view->data = $dataThumuc;
        }

        //save chuyen
        global $db;
        if ($this->getParameters['is_save'] == 1){
            $db->update('hscvdt_vanban', array('ID_TAPHOSO' => $this->getParameters['idthscv_c']), "ID_VB='".$idvb."'");
            echo "<script>window.parent.location = '/taphscv/taphscv/list/ID_THUMUC/".$idthumuc."'</script>";
        }
        
    }
	
	public function danhsachhsyeucaudongAction()
	{
		$this->view->object = $this->object;  
		$this->_helper->Layout->enableLayout();
		$this->view->title = "Danh sách hồ sơ yêu cầu đóng";
		$this->view->data = $this->model->getListHsYeuCau();
		$this->view->ThumucModel = new Taphscv_phanquyenModel();
		$this->view->id = $this->getRequest()->getParam('id');
		$this->view->yeucau = $this->getRequest()->getParam('yeucau');
	}
	
	public function savehsyeucauAction()
	{
		$id= $this->getRequest()->getParam('id');
		$code= $this->getRequest()->getParam('code');
		$yeucau= $this->getRequest()->getParam('yeucau');
		$success = $this->model->saveYeuCau($id,$yeucau);
		if($success == 1)
			if($code == 'old')
				$this->_redirect("/taphscv/taphscv/list");
			$this->_redirect("/taphscv/taphscv/danhsachhsyeucaudong");
	}
}
