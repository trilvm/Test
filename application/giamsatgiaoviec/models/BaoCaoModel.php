<?php
require_once 'Zend/Db/Table/Abstract.php';
require_once 'giamsatgiaoviec/models/LoaiCongViecGiaoViecModel.php';
require_once 'nusoap/nusoap.php';

class BaoCaoModel extends Zend_Db_Table_Abstract {

    var $_name = "gscv_loaicongviec";
    
    public static function getAllUserByDep($id_dep =0){
        global $db;
        $where="";
        if($id_dep!=0){
            $where.=" and e.ID_DEP = ".$id_dep;
        }
        $sql = "SELECT u.ID_U,u.USERNAME, concat(e.FIRSTNAME,' ',e.LASTNAME) as NAME FROM 
           QTHT_USERS u
           inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP
           WHERE (1=1) ".$where;
        $r = $db->query($sql)->fetchAll();
        $arr=array();
        foreach ($r as $item){
            array_push($arr, $item['USERNAME']);
        }
        return $arr;
    }
    public static function getFullNameOfUserByUserName($username){
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT u.ID_U,u.USERNAME, concat(e.FIRSTNAME,' ',e.LASTNAME) as FULLNAME 
                FROM QTHT_USERS u 
                inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP 
                WHERE u.USERNAME =?";
        try{
                $qr = $dbAdapter->query($sql,array($username));
                $re = $qr->fetch();
                return $re["FULLNAME"];
        }catch(Exception $ex){
                return "";
        }
    }
    
    public static  function baoCaoChiTietDonViNgoai($id_u,$fromdate,$todate){        
        global $config;
        $fromdate = QLVBDHCommon::doDateViet2Standard($fromdate);
        $todate = QLVBDHCommon::doDateViet2Standard($todate);
        $arr = array(
            'fromdate' =>$fromdate,
            'todate' =>$todate,
            'id_u' =>$id_u,
        );
        $model = new LoaiCongViecGiaoViecModel();
        $session = $model->LoginService();
        $client = new SoapClient($config->service->lienthong->uri);
        $parameter = json_encode($arr);        
        $params = array(
            'session' => $session,
            'service_code' => 'TRAODOIGSCV',
            'service_name' => 'GETBAOCAOCHITIETGIAOVIEC',
            'parameter' => $parameter
        );
        $result = $client->__call('Execute', $params);
        //return base64_decode($result);//exit;
        $arrayresult =  ServicesCommon::DeseriallizeToArray(base64_decode($result));
        return $arrayresult;
    }   
    
    public static  function countThongKeDonViNgoai($id_u,$trangthai,$fromdate,$todate){
        
        global $config;
        $fromdate = QLVBDHCommon::doDateViet2Standard($fromdate);
        $todate = QLVBDHCommon::doDateViet2Standard($todate);
        $arr = array(
            base64_encode($fromdate),
            base64_encode($todate),
            base64_encode($id_u),
            base64_encode((int)$trangthai)
        );
        $model = new LoaiCongViecGiaoViecModel();
        $session = $model->LoginService();
        $client = new SoapClient($config->service->lienthong->uri);
        $parameter = implode('~', $arr);        
        $params = array(
            'session' => $session,
            'service_code' => 'TRAODOIGSCV',
            'service_name' => 'GETTHONGKEGIAOVIEC',
            'parameter' => $parameter
        );
        $result = $client->__call('Execute', $params);        
        return base64_decode($result);
    }
    
    public static  function baoCaoChiTietDonVi($loai, $id_u,$tinhhinh,$trangthai,$fromdate,$todate){
        global $db;
        $where ="";
        $fromdate = QLVBDHCommon::doDateViet2Standard($fromdate);
        $todate = QLVBDHCommon::doDateViet2Standard($todate);
        if((int)$tinhhinh!=0){
            $where.="and hscv.TIENDO_GIAOVIEC =100 ";
        }else{
            $where.="and (hscv.TIENDO_GIAOVIEC !=100 or hscv.TIENDO_GIAOVIEC is null)";
        }
        
        $sql ="select hscv.*,emp.*,lcv.NAME as LOAICVNAME_GIAOVIEC,vbd.MASOLIENTHONG
                from ".QLVBDHCommon::Table('wf_processlogs')." pl
                inner join ".QLVBDHCommon::Table('wf_processitems')." pi on pl.ID_PI = pi.ID_PI
                inner join ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv on pi.ID_PI =hscv.ID_PI
                inner join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk ON hscv.ID_HSCV = fk.ID_HSCV
                inner join ".QLVBDHCommon::Table('vbd_vanbanden')." vbd ON vbd.ID_VBD  = fk.ID_VBDEN
                inner join qtht_users u on u.ID_U =pl.ID_U_RECEIVE
                inner join qtht_employees emp on emp.ID_EMP =u.ID_EMP
                inner join gscv_loaicongviec lcv on lcv.CODE = hscv.LOAICV_GIAOVIEC
                where pl.ID_U_RECEIVE !=0 
                and vbd.IS_LIENTHONG = 1 
                and (vbd.MASOLIENTHONG is not null or vbd.MASOLIENTHONG!='')
                AND u.USERNAME in ('".implode("','", $id_u)."')
                and hscv.LOAICV_GIAOVIEC in ('".implode("','", $loai)."')              
                and hscv.NGAY_BD > ? AND hscv.NGAY_BD <= ? 
                ".$where."
                group by pi.ID_PI;";
        
        //echo $sql;exit;
        $return =$db->query($sql,array($fromdate,$todate))->fetchAll();
        return $return;  
    }
    
    public static  function getMoTaTienDo($idHSCV){
       global $db; 
        $sql ="select *
                from ".QLVBDHCommon::Table('gscv_tiendo_log')." where ID_HSCV  = ?
               ;";        
        //echo $sql;exit;
        $return =$db->query($sql,array($idHSCV))->fetchAll();
        return $return;  
    }
    public static  function getDepOfNguoiXuLy($id_u){
        global $db;
        $query = $db->query('Select dep.NAME
        from `qtht_users` u 
        JOIN `qtht_employees` e
        on u.`ID_EMP` = e.ID_EMP 
        join qtht_departments dep on dep.ID_DEP =e.ID_DEP where u.`ID_U`=?',$id_u);
        $re = $query->fetch();
        return $re[0]["NAME"];
    }
}