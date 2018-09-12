<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Zend/Db/Table/Abstract.php';

class TraoDoiCongViecModel extends Zend_Db_Table_Abstract {

    protected $_name = 'hscv_hosocongviec_2013';

    function __construct($year) {
        if ($year == ""){
            $year = QLVBDHCommon::getYear();
        }
        $this->_name = 'hscv_hosocongviec_' . $year;
        $config = array();
        parent::__construct($config);
    }

    static function getDetailInfoHSGiaoViec($ID_HSCV) {

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT hscv.*
                FROM " . QLVBDHCommon::Table('hscv_hosocongviec') . " hscv 
                where hscv.ID_HSCV = ?";
        $query = $dbAdapter->query($sql, array($ID_HSCV))->fetch();
        return $query;
    }
    
    public function getMaSoDongLuanChuyenLt($id_hscv) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * 
                                FROM  " . QLVBDHCommon::Table('vbd_vanbanden') . " vbd
                                inner join " . QLVBDHCommon::Table('vbd_fk_vbden_hscvs') . " vbdfk on vbd.ID_VBD = vbdfk.ID_VBDEN
                                inner join " . QLVBDHCommon::Table('hscv_hosocongviec') . " hscv on vbdfk.ID_HSCV = hscv.ID_HSCV
                                where hscv.ID_HSCV = ?";
        $result = $dbAdapter->query($sql, array($id_hscv))->fetch();       
        if ((int) $result['DLCLIENTHONG'] > 0) {
            return (int) $result['DLCLIENTHONG'];
        } else {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT * 
                                FROM ". QLVBDHCommon::Table('hscv_hosocongviec') . " hscv                                
                                left join ". QLVBDHCommon::Table('vbdi_vanbandi') . " vbdi on vbdi.ID_HSCV = hscv.ID_HSCV
                                where hscv.ID_HSCV = ?";
            $result = $dbAdapter->query($sql, array($id_hscv))->fetch();
            return (int) $result['DLCLIENTHONG'];            
        }
    }
    
    public function getName($id_u) {
        $r = $this->getDefaultAdapter()->query("
			SELECT
     			USERNAME AS NGUOITAO,CONCAT(FIRSTNAME,' ',LASTNAME) AS TENNGUOITAO
			FROM
				QTHT_USERS
			INNER JOIN QTHT_EMPLOYEES ON QTHT_EMPLOYEES.ID_EMP=QTHT_USERS.ID_EMP
			WHERE ID_U=?", array($id_u));
        $data = $r->fetch();
        if ($data){
            return $data['TENNGUOITAO'];
        }else{
            return "";
        }
    }
}
