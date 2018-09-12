<?php

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/hosocongviecModel.php';
//require_once 'traodoi/models/ThongTinModel.php';
//require_once 'vbdi/models/vbdi_dongluanchuyenModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
//require_once 'hscv/models/PhoiHopModel.php';
//require_once 'hscv/models/bosunghosoModel.php';
//require_once 'mailclient/models/mail.php';
//require_once('hscv/models/chuyennoiboModel.php');
//require_once('lichcttext/models/lct.php');
//require_once 'qlcuochop/models/quanlycuochopModel.php';
//require_once 'hscv/models/Hscv_dexuatchoxulyModel.php';
class kiemTraNhanVienModel {

    static function SelectVbByID_U($ID_U) { 
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = "and (wfitem.ID_U IN ({$ID_U}))";
        $sql = "SELECT *,hscv.`ID_HSCV` as MAHSCV 
                FROM ".QLVBDHCommon::table("hscv_hosocongviec")." hscv 
                left join ".QLVBDHCommon::table("hscv_congviecsoanthao")." cv on cv.`ID_HSCV`=hscv.`ID_HSCV` 
                inner join ".QLVBDHCommon::table("wf_processitems")." wfitem on hscv.ID_PI = wfitem.ID_PI 
                INNER JOIN WF_PROCESSES wfp on wfitem.ID_P = wfp.ID_P 
                INNER JOIN WF_CLASSES class on class.ID_C = wfp.ID_C 
                inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV 
                WHERE (1=1) and (IS_THEODOI<>1) and ( hscv.IS_CHOXULY = 0 OR hscv.IS_CHOXULY is NULL ) 
                and IS_DXCHOXL <> 1 and hscv.ID_THUMUC = 1 and wfitem.ID_U IN (?) 
                and class.ALIAS = 'VBD' GROUP BY MAHSCV ORDER BY hscv.ID_HSCV";
        $r = $db->query($sql,$ID_U);
        $r = $r->fetchAll();
//        echo '<pre>';
        //var_dump($r);
        $data = array();
        for ($i = 0; $i < count($r); $i++) {
            $tt = hosocongviecModel::getlastlog($r[$i]['ID_PI']);
            array_push($data, $r[$i]['MAHSCV'], $tt['DATESEND'], $tt['HANXULY']);
            $parentlog = WFEngine::GetStartLogIdByProcessItem($r[$i]['ID_PI'], $r[$i]['ID_T']);
            if (is_array($parentlog)) {
                foreach ($parentlog as $itemparent) {
                    if ($itemparent['ID_PL'] != $tt['ID_PL']) {
                        array_push($data, $r[$i]['MAHSCV'] . $itemparent['DATESEND'] . $itemparent['HANXULY']);
                    }
                }
            }
        }
      //  var_dump($data);
        $counttre = 0;
        $countbt = 0;

        $d = strtotime("last Sunday");
        $date = date('Y-m-d H:i:s');

        for ($i = 0; $i < count($data); $i = $i + 3) {
            $mahscv = $data[$i];
            $datesend = $data[$i + 1]; 
            $hanxuly = $data[$i + 2]; 
            $check = QLVBDHCommon::getTreHan($datesend, $hanxuly); //get tre han                    
            if ($check > 0) {
                if (((strtotime($datesend))+ $hanxuly) < strtotime($date)) {
                    $counttre++;
                }
            }
        }
        
        return $counttre;
    }
    
    static function SelectId_uByIddep($ID_DEP,$ID_U) { 
          $db = Zend_Db_Table::getDefaultAdapter();
          $sql="SELECT ID_U FROM qtht_users u
                INNER JOIN qtht_employees emp ON u.ID_EMP= emp.ID_EMP
                WHERE ID_DEP=$ID_DEP and u.ID_U <> $ID_U ";
          
          return $db->query($sql)->fetchAll();
    }
    static function selectNameDepartByID($ID_DEP)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql= "SELECT NAME FROM qtht_departments WHERE ID_DEP=$ID_DEP";
        $r= $db->query($sql)->fetch();
        return $r['NAME'];
    }


    static function SelectVbByIddep($ID_DEP) { 
        
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT *,hscv.`ID_HSCV` as MAHSCV 
                FROM ".QLVBDHCommon::table("hscv_hosocongviec")." hscv 
                left join ".QLVBDHCommon::table("hscv_congviecsoanthao")." cv on cv.`ID_HSCV`=hscv.`ID_HSCV` 
                inner join ".QLVBDHCommon::table("wf_processitems")." wfitem on hscv.ID_PI = wfitem.ID_PI 
                INNER JOIN WF_PROCESSES wfp on wfitem.ID_P = wfp.ID_P 
                INNER JOIN WF_CLASSES class on class.ID_C = wfp.ID_C 
                inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV 
                WHERE (1=1) and (IS_THEODOI<>1) and ( hscv.IS_CHOXULY = 0 OR hscv.IS_CHOXULY is NULL ) 
                and IS_DXCHOXL <> 1 and hscv.ID_THUMUC = 1 and wfitem.ID_U IN (?) 
                and class.ALIAS = 'VBD' GROUP BY MAHSCV ORDER BY hscv.ID_HSCV";
        $r = $db->query($sql, $ID_DEP)->fetchAll();
       
        $data = array();
        for ($i = 0; $i < count($r); $i++) {
            $tt = hosocongviecModel::getlastlog($r[$i]['ID_PI']);
            array_push($data, $r[$i]['MAHSCV'], $tt['DATESEND'], $tt['HANXULY']);
            $parentlog = WFEngine::GetStartLogIdByProcessItem($r[$i]['ID_PI'], $r[$i]['ID_T']);
            //var_dump($parentlog);
            if (is_array($parentlog)) {
                foreach ($parentlog as $itemparent) {
                    if ($itemparent['ID_PL'] != $tt['ID_PL']) {
                        array_push($data, $r[$i]['MAHSCV'] . $itemparent['DATESEND'] . $itemparent['HANXULY']);
                    }
                }
            }
        }
       
        $counttre = 0;
        $countbt = 0;

        $d = strtotime("last Sunday");
        $date = date('Y-m-d H:i:s');
        $arr_hscv= array();
        for ($i = 0; $i < count($data); $i = $i + 3) {
             
            $mahscv = $data[$i];
            $datesend = $data[$i + 1]; 
            $hanxuly = $data[$i + 2]; 
            $check = QLVBDHCommon::getTreHan($datesend, $hanxuly); //get tre han                    
            if ($check > 0) {
                if (((strtotime($datesend))+ $hanxuly) < strtotime($date)) {
                    $counttre++;
                    $arr_hscv[]= $data[$i];
                }
            }
        }
         
        return array($counttre,$arr_hscv) ;
    }

}