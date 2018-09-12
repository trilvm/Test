<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'taphscv/models/Taphscv_phanquyenModel.php';

class Taphscv_phanquyenController extends Zend_Controller_Action {

    var $params;
    var $thumuc;
    function init() {
        $this->_helper->Layout->disableLayout();
        $this->params = $this->getRequest()->getParams();
        $this->view->title = 'Quản lý tập hồ sơ công việc';
        $this->thumuc = new Taphscv_phanquyenModel();
    }

    function listAction() {

        $idtaphoso = $this->params['id_ths'];

        $permission = $this->thumuc->GetPermistionOfUsers($idtaphoso);
        $this->view->permission = $permission;
        //var_dump($permission);exit;
        //sent models to view
        $this->view->model = $this->thumuc;
        $this->view->idtaphoso = $idtaphoso;
    }

    function phanquyenAction() {
        $this->_helper->Layout->enableLayout();
        //Lấy parameter
        $id = $this->params["id_ths"];
        $iframe = $this->params["id_frame"];
        
        $this->thumuc->_id = $id;
        $this->view->idtaphoso = $id;

        $this->view->group = $this->thumuc->GetAllGroup($id,1);
        $this->view->dep = $this->thumuc->GetAllDep($id,1);
        $this->view->user = $this->thumuc->GetAllUser($id,1);
        $this->view->IdFrame = $iframe;

        $this->view->title = 'Phân quyền quản lý tập hồ sơ công việc';
        QLVBDHButton::EnableSave("#");
        QLVBDHButton::EnableBack('');
    }

    function saveAction() {

        $id = $this->params["idtaphoso"];
        $this->thumuc->_id = $id;
        //Check data
        try {
            $this->thumuc->PrePareTableBeforeUpdate();
            $this->thumuc->UpdateGroup($this->params["SEL_GROUP_XEM"], $this->params["SEL_GROUP_THEMMOI"], $this->params["SEL_GROUP_PHANQUYEN"]);
            $this->thumuc->UpdateDep($this->params["SEL_DEP_XEM"], $this->params["SEL_DEP_THEMMOI"], $this->params["SEL_DEP_PHANQUYEN"]);
            $this->thumuc->UpdateUser($this->params["SEL_USER_XEM"], $this->params["SEL_USER_THEMMOI"], $this->params["SEL_USER_PHANQUYEN"]);
        } catch (Exception $ex) {
            echo $ex->__toString();
            exit;
            $this->_redirect("/default/error/error?control=ThuMuc&mod=hscv&id=ERR11001001");
        }
        if ($this->params["ISCOQUAN"] == 1) {
            $this->_redirect("/taphscv/taphscv/list");
        } else {
            $this->_redirect("/taphscv/taphscv/list");
        }
    }

}
