<?php

require_once 'qlcuochop/models/DMdiadiemModel.php';

class qlcuochop_DMdiadiemController extends Zend_Controller_Action {

    public function init() {

        $this->getParams = $this->getRequest()->getParams();
        $this->model = new qlcuochop_DMdiadiemModel();
        $this->view->title = "Danh mục địa điểm họp";
    }

    public function indexAction() {
        $this->view->subtitle = "Danh sách";
        $data = $this->model->SelectAll();
        $this->view->data = $data;        
        QLVBDHButton::EnableAddNew("/qlcuochop/DMdiadiem/input");
        QLVBDHButton::EnableDelete("/qlcuochop/DMdiadiem/delete");
    }

    public function inputAction() {
        $this->view->subtitle = "Thêm mới";
        $id = $this->getParams['id'];
        if ($id > 0) {
            $this->view->subtitle = "Cập nhật";
            $data = $this->model->find($id)->current();
            $this->view->data = $data;
            $this->view->id = $id;
        }
        if ($this->getParams['err'] == '1') {
            $this->view->err_exist = " Địa điểm họp này đã tồn tại!";
        }
        //thêm nút
        QLVBDHButton::EnableSave('/qlcuochop/DMdiadiem/save');
        QLVBDHButton::EnableBack('/qlcuochop/DMdiadiem/');
    }

    public function saveAction() {
        $id = $this->getParams['id'];
        $ten = $this->getParams['diadiem'];

//        $isValid = $this->model->checkInput($ten);
//
//        if ($isValid == 1 && $id == 0) {
//            $this->_redirect('qlcuochop/DMdiadiem/input/err/1');
//        }
//        else {
        $this->model->save($id, $ten);
        $this->_redirect('qlcuochop/DMdiadiem/index');
        //}
    }
    
    function deleteAction() {
        $params = $this->getRequest()->getParams();
        $db = Zend_Db_Table::getDefaultAdapter();
        $ids = $params["DEL"];

       // $arrdelete = $this->model->checkDelete($ids);
      //  $db->delete($this->model->_name, "ID_DONVICHUYEN IN (" . implode(',', $arrdelete) . ")");
        foreach($ids as $t){
                $db->delete("dm_diadiem","ID_DIADIEM=".(int)$t);
        }
//        $strUndelete = "";
//        foreach ($ids as $arrID) {
//            list ($id_dd, $tendd) = explode("~", $arrID);
//            $isDelete = 0;
//            $stt = 0;
//            for ($i = 0; $i < count($arrdelete); $i++) {
//                if ($id_dd == $arrdelete[$stt]) {
//                    $isDelete = 1;
//                    break;
//                }
//                $stt++;
//            }
//            if ($isDelete == 0) {
//                $strUndelete .= $tendd . '-';
//            }
//        }
//        if ($strUndelete != "") {
//            $this->_redirect('/qlcuochop/DMdiadiem/index/err/1/ids/' . $strUndelete);
//        }

        $this->_redirect("/qlcuochop/DMdiadiem/index");
    }
}
