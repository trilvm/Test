<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ('Zend/Controller/Action.php');



class Hscv_GiahanxulyController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->Layout->DisableLayout();
    }

    function inputAction() {
        global $auth;
        global $db;
        $params = $this->getRequest()->getParams();
        $this->_helper->layout->disableLayout();
        $idhscv = $params["id"];
        $year = QLVBDHCommon::getYear();        
        //Tao object        
        $this->view->sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);
        //var_dump($this->view->sendprocess);
        $this->view->ID_HSCV = $idhscv;        
        $this->view->year = QLVBDHCommon::getYear();                
        
    }
            
   
    function saveAction() {
        global $auth;
        global $db;
        $params = $this->getRequest()->getParams();
        $user = $auth->getIdentity();
        $id_hscv = $params['ID'];
        $id_pl = $params['ID_PL'];
        $hanxuly = $params['HANXULY'];                      
        try {
        $db->update(QLVBDHCommon::Table('WF_PROCESSLOGS'),array('HANXULY' => $hanxuly),'ID_PL='.$id_pl);
        echo "1";
        }
        catch (Zend_Db_Exception $e)
        {echo "0";}
    }

}

