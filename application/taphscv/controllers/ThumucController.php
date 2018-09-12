<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'taphscv/models/Taphscv_thumucModel.php';
require_once 'Common/ValidateInputData.php';

class Taphscv_thumucController extends Zend_Controller_Action {

    private $mld;
    public function init() {

        $this->mld = new Taphscv_thumucModel();
        $this->_helper->Layout->EnableLayout();
        $this->view->title = 'Thư mục HSCV điện tử';
		$this->view->arrDoQuanTrong = array(
			0=>"Bình thường",1=>"Quan trọng",2=>"Rất quan trọng"	
		);
    }

    function indexAction() {
        global $auth;
        $user = $auth->getIdentity();

        $this->view->title="Danh sách thư mục HSCV điện tử'";
        QLVBDHButton::EnableAddNew("/taphscv/thumuc/input");
        QLVBDHButton::EnableDelete("/taphscv/thumuc/delete");
        QLVBDHButton::EnableBack('');
        //Doc du lieu de hien thi len view
        $config = Zend_Registry::get('config');
        $page = $this->_request->getParam('page');
        $limit = $this->_request->getParam('limit');
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;
        
        $filter_object = $this->_request->getParam('filter_object');
        $this->view->filter_object = $filter_object;
        $search = $this->_request->getParam("search");
        $this->view->search = $search;
        $this->view->ThumucModel = $this->mld;
        $this->view->user = $user;

        $this->view->data = array();
        $search = "%".$search."%";

        // QLVBDHCommon::GetTreeByName($search,'NAME',&$this->view->data,"hscvdt_thumuc_taphoso","ID_THUMUC","ID_THUMUC_PARENT",1,1);
        Taphscv_thumucModel::GetTreeByName($user,$filter_object,$search,'NAME',&$this->view->data,"hscvdt_thumuc_taphoso","ID_THUMUC","ID_THUMUC_PARENT",1,1);

        $n_rows = count($this->view->data);
        $this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
        $this->view->limit=$limit;
        $this->view->page=$page;
    }

    function inputAction() {

        $this->view->data = array();
        // QLVBDHCommon::GetTree(&$this->view->data, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        Taphscv_thumucModel::GetTree(null,1,&$this->view->data, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);

        //Tao phan button
        QLVBDHButton::EnableSave("/taphscv/thumuc/save");
        QLVBDHButton::AddButton("Trở lại", "", "BackButton", "BackButtonClick();");
        //Giu cac tham so cua trang danh sach
        $this->view->page = $this->_request->getParam('page');
        $this->view->limit = $this->_request->getParam('limit');
        $this->view->filter_object = $this->_request->getParam('filter_object');
        $this->view->search = $this->_request->getParam("search");
        global $db;
        if ($this->_request->isPost()) {
            // Lay id cua linh vuc van ban can cap nhat
            $idCapNhat = $this->_request->getParam('idTHUMUC');
            $this->view->subtitle = "Thêm mới thư mục HSCV điện tử'";
            if ($idCapNhat > 0) {
                //Truong hop cap nhat
                $this->view->subtitle = "Cập nhật thư mục HSCV điện tử ";
                //Lay thong tin ve linh vuc van ban can cap nhat
                $rowcn = $this->mld->find($idCapNhat);

                //Kiem tra id cua linh vuc van ban can cap nhat co nam tron csdl

                if ($rowcn->count() == 0) {
                    //loi khong tim thay id cua linh vuc van ban can cap nhat
                    $this->_redirect('/default/error/error?control=DanhMuclinhvucvanban&mod=qtht&id=ERR11004002');
                } else {

                    $this->view->namebefore = $rowcn->current()->NAME;
                    $this->view->activeselect = $rowcn->current()->ACTIVE;
                    $r = $db->query("SELECT * FROM hscvdt_thumuc_taphoso WHERE ID_THUMUC = " . $idCapNhat);
                    $this->view->ID_THUMUC_PARENT = $rowcn->current()->ID_THUMUC_PARENT;
                    $this->view->uq = $r->fetch();
                    $this->view->id = $idCapNhat;
                    $this->view->DOQUANTRONG = $rowcn->current()->DOQUANTRONG;
                }
            }
        }
        $this->renderScript("thumuc/InputData.phtml");
    }

    function saveAction() {
        global $auth;
        $user = $auth->getIdentity();
        Taphscv_thumucModel::GetTree(null,1,&$this->view->data, "hscvdt_thumuc_taphoso", "ID_THUMUC", "ID_THUMUC_PARENT", 1, 1);
        if ($this->_request->isPost()) {
            //var_dump($this->_request->getParam("choiceLVCha"));exit;
            //Lay du lieu tu form nhap lieu
            $active = $this->_request->getParam("active");
            $parent = $this->_request->getParam("choiceLVCha");
            $doquantrong = $this->_request->getParam("choiceDoQuanTrong");
            if (!$active)
                $active = 0;
            $name = $this->_request->getParam("name");
            $id = $this->_request->getParam("InputidTHUMUC");
            //Kiem tra du lieu nhap
            $this->checkInputData($name, $active);
            if ($id > 0) {

                // Thuc hien cap nhat du lieu
                $where = 'ID_THUMUC=' . $id;
                
                try {
                    $this->mld->update(array('NAME' => $name, 'ID_THUMUC_PARENT' => $parent, 'ACTIVE' => $active, "ID_U" => $id_u,'DOQUANTRONG'=>$doquantrong), $where);
                } catch (Exception $e) {
                    //Thong bao loi khong the cap nhat linh vuc van ban
                    $this->_redirect('/default/error/error?control=DanhMuclinhvucvanban&mod=qtht&id=ERR11004006');
                }
                $this->title = "Cập nhật Danh mục Lĩnh Vực Văn Bản";

                //Cập nhật XEM cho cha mới
            if (Taphscv_thumucModel::HasParent("hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id)) {
                $ID_THUMUC_PARENT = array();
                Taphscv_thumucModel::GetParent(&$ID_THUMUC_PARENT,"hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id);
                foreach ($ID_THUMUC_PARENT as $key => $value) {                    
                    $this->mld->UpdateUser($value["ID_THUMUC_PARENT"],array($user->ID_U));
                }
            }
            } else {


                //Them moi linh vuc van ban vao co so du lieu
                $arr_newdata = array("NAME" => $name, 'ID_THUMUC_PARENT' => $parent, "ACTIVE" => $active,'DOQUANTRONG'=>$doquantrong);

                try {
                    $id = $this->mld->insert($arr_newdata);
                } catch (Exception $ex) {
                    // loi khi insert du lieu
                    $this->_redirect('/default/error/error?control=DanhMuclinhvucvanban&mod=qtht&id=ERR11004007');
                }

                //Phan quyen thu muc hscvdt - chỉ trong trường hợp thêm mới 
                try {
                $this->mld->DeleteUser($id,array($user->ID_U),array($user->ID_U),array($user->ID_U));
                $this->mld->UpdateUser($id,array($user->ID_U), null, null);
                $this->mld->UpdateUser($id,null, array($user->ID_U), null);
                $this->mld->UpdateUser($id,null, null, array($user->ID_U));
                } catch (Exception $ex) {
                echo $ex->__toString();
                exit;
                $this->_redirect("/default/error/error?control=taphscv&mod=taphscv&id=ERR11003004");
                }

                //Cập nhật XEM cho cha mới
            if (Taphscv_thumucModel::HasParent("hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id)) {
                $ID_THUMUC_PARENT = array();
                Taphscv_thumucModel::GetParent(&$ID_THUMUC_PARENT,"hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id);
                foreach ($ID_THUMUC_PARENT as $key => $value) {
                    $this->mld->UpdateUser($value["ID_THUMUC_PARENT"],array($user->ID_U));
                }
            }

                $this->title = "Thêm mới Danh mục Lĩnh Vực Văn Bản";
            }
            //chuyen den trang xem danh sach cac linh vuc van ban
            $this->_redirect('/taphscv/thumuc');
        } else {
            $this->_redirect('/taphscv/thumuc');
        }
    }

    function deleteAction() {

        if ($this->_request->isPost()) {
            //Lay id cua cac linh vuc van ban can xoa
            $idarray = $this->_request->getParam('DEL');

            //Thuc hien xoa cac linh vuc van ban duoc chon boi user
            $where = 'ID_THUMUC in (' . implode(',', $idarray) . ')';

            try {
                for($i=0;$i<=count($idarray);$i++) {
                    $isCheck = $this->mld->CheckBeforeDelete($idarray[$i]);
                    if ($isCheck == 1) $this->_redirect('/default/error/error?control=taphscv&mod=taphscv&id=ERR11003001');
                    if (Taphscv_thumucModel::HasChild("hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$idarray[$i])) {
                        $this->_redirect('/default/error/error?control=taphscv&mod=taphscv&id=ERR11003003');
                    }
                }                
                if (!$this->mld->delete($where)) {
                    $this->_redirect('/default/error/error?control=taphscv&mod=taphscv&id=ERR11003002');
                }

                //xóa phan quyen thu muc hscvdt
                foreach ($idarray as $ID_THUMUC) {
                    try {
                        $this->mld->PrePareTableBeforeUpdate($ID_THUMUC);                        
                        } catch (Exception $ex) {
                        echo $ex->__toString();
                        exit;
                        $this->_redirect("/default/error/error?control=taphscv&mod=taphscv&id=ERR11003004");
                    }
                }

            } catch (Exception $ex) {
                //loi xay ra khi xoa du lieu
                $this->_redirect('/default/error/error?control=taphscv&mod=taphscv&id=ERR11003002');
            }

            //Hien thi trang danh sach cac loai van ban
            $this->_redirect('/taphscv/thumuc');
        } else {
            $this->view->title = "Quản trị danh mục lĩnh vực văn bản_ Xóa";
        }

        $this->_redirect('/taphscv/thumuc');
    }

    private function checkInputData($name, $active) {

        $strurl = '/default/error/error?control=danhmuclinhvucvanban&mod=qtht&id=';
        $strerr = "";
        $strerr .= ValidateInputData::validateInput('text128_re', $name, 'ERR11004001') . ",";
        $strerr .= ValidateInputData::validateInput('boolean', $active, "ERR11004005") . ",";
        if (strlen($strerr) != 2) {
            $this->_redirect($strurl . $strerr);
        }
        return true;
    }

    public function phanquyenAction() {
        $this->params = $this->getRequest()->getParams();
        $this->_helper->Layout->enableLayout();
        //Lấy parameter
        $id = $this->params["ID_THUMUC"];        
        $this->view->ID_THUMUC = $id;

        $this->view->group = $this->mld->GetAllGroup($id,1);
        $this->view->dep = $this->mld->GetAllDep($id,1);
        $this->view->user = $this->mld->GetAllUser($id,1);        

        $this->view->title = 'Phân quyền thư mục tập hồ sơ công việc';
        QLVBDHButton::EnableSave("#");
        QLVBDHButton::EnableBack('');
    }

    public function savephanquyenAction() {
        $this->params = $this->getRequest()->getParams();
        $id = $this->params["ID_THUMUC"];        
        //Check data        
        try {
            $this->mld->PrePareTableBeforeUpdate($id);
            $this->mld->UpdateGroup($id,$this->params["SEL_GROUP_XEM"], $this->params["SEL_GROUP_THEMMOI"], $this->params["SEL_GROUP_PHANQUYEN"]);
            $this->mld->UpdateDep($id,$this->params["SEL_DEP_XEM"], $this->params["SEL_DEP_THEMMOI"], $this->params["SEL_DEP_PHANQUYEN"]);
            $this->mld->UpdateUser($id,$this->params["SEL_USER_XEM"], $this->params["SEL_USER_THEMMOI"], $this->params["SEL_USER_PHANQUYEN"]);
        

        
        //Check có ID_THUMUC Cha không , nếu có thì Set Permission XEM cho các thư mục cha
            if (Taphscv_thumucModel::HasParent("hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id)) {
                $ID_THUMUC_PARENT = array();
                Taphscv_thumucModel::GetParent(&$ID_THUMUC_PARENT,"hscvdt_thumuc_taphoso","ID_THUMUC_PARENT","ID_THUMUC",$id);
                foreach ($ID_THUMUC_PARENT as $key => $value) {
                    //Xóa rồi insert lại nhóm
                    $this->mld->DeleteGroup($value["ID_THUMUC_PARENT"],$this->params["SEL_GROUP_XEM"]);
                    $this->mld->DeleteDep($value["ID_THUMUC_PARENT"],$this->params["SEL_DEP_XEM"]);
                    $this->mld->DeleteUser($value["ID_THUMUC_PARENT"],$this->params["SEL_USER_XEM"]);
                    //Cập nhật lại XEM
                    $this->mld->UpdateGroup($value["ID_THUMUC_PARENT"],$this->params["SEL_GROUP_XEM"]);
                    $this->mld->UpdateDep($value["ID_THUMUC_PARENT"],$this->params["SEL_DEP_XEM"]);
                    $this->mld->UpdateUser($value["ID_THUMUC_PARENT"],$this->params["SEL_USER_XEM"]);
                }
            }



        } catch (Exception $ex) {
            echo $ex->__toString();
            exit;
            $this->_redirect("/default/error/error?control=taphscv&mod=taphscv&id=ERR11003004");
        }
        $this->_redirect("/taphscv/thumuc/index");
    }
}