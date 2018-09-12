<?php
require_once 'Zend/Db/Table/Abstract.php';

class GiamSatGiaoViecModel extends Zend_Db_Table_Abstract {

    var $_name = "gscv_loaicongviec";
    public static  function getInfoHSCVById($id_hscv){
        
        global $db;
        $sql ="select hscv.* from ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv where hscv.ID_HSCV =?";
        $return =$db->query($sql,array($id_hscv))->fetch();
        return $return;        
    }
    
    public static  function getGiaoViecChuyenDonViNgoai($id_duthao){
        
        global $db;
        $sql ="select dv.* from ".QLVBDHCommon::Table('gscv_giaoviec_dvngoai')." dv where dv.ID_DUTHAO =?";
        $return =$db->query($sql,array($id_duthao))->fetchAll();
        $data =array();        
        foreach($return as $item){
            array_push($data, $item['DONVI']);
        }
        return $data;        
    }
    public static  function getGiaoViecChuyenDonViNgoai2($id_duthao){
        
        global $db;
        $sql ="select dv.* from ".QLVBDHCommon::Table('gscv_giaoviec_dvngoai')." dv where dv.ID_DUTHAO =?";
        $return =$db->query($sql,array($id_duthao))->fetchAll();        
        return $return;        
    }
    public static function checkIsGiaoViec($id_hscv){
        if((int)$id_hscv ==0){
            return 0;
        }else{
            global $db;
            $sql ="select cvst.ID_HSCV FROM ".QLVBDHCommon::Table('hscv_congviecsoanthao')." cvst where cvst.ID_HSCV = ?
                    union
                    select hscv.ID_HSCV from ".QLVBDHCommon::Table('vbd_vanbanden')." vbd
                    inner join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk on fk.ID_VBDEN = vbd.ID_VBD
                    inner join ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv on hscv.ID_HSCV = fk.ID_HSCV
                    where hscv.ID_HSCV  =  ? and vbd.IS_LIENTHONG = 1 AND vbd.MASOLIENTHONG IS NOT NULL and vbd.DLCLIENTHONG IS NOT NULL
                    group by ID_HSCV";
            $return =$db->query($sql,array($id_hscv,$id_hscv))->fetch();
            if((int)$return['ID_HSCV']==$id_hscv){
                return 1;
            }else{
                return 0;
            }
        }
    }
}