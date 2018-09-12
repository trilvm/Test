<?php
require_once 'Zend/Db/Table/Abstract.php';
require_once 'nusoap/nusoap.php';

class LoaiCongViecGiaoViecModel extends Zend_Db_Table_Abstract {

    var $_name = "gscv_loaicongviec";
    
    public static function LoginService() {
        global $config;
        $client = new SoapClient($config->service->lienthong->uri);
        return $client->__call('Login', array(
            'madonvi' => $config->service->lienthong->username,
            'password' => $config->service->lienthong->password));
    }
    function checkExitsLoaiCongViec($code){
        global $db;
        $sqlselect ="SELECT * from gscv_loaicongviec";
        $query=$db->query($sqlselect);
        $r = $query->fetchAll();
        $is_dup_code =false;
        foreach ($r as $item) {            
            if ($code == $item['CODE']) {
                $is_dup_code = true;
            }
        }
        return $is_dup_code;
    }
    
    public static function DongBoDanhmuc($session) {
        global $config;
        global $db;
        try{
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'TRAODOIGSCV',
                'service_name' => 'GETALLLOAICONGVIEC',
                'parameter' => ''
            );
            $dmloaicongviec = $client->__call('Execute', $params);
            $arrayDanhMucLoaiCongViec =  ServicesCommon::DeseriallizeToArray(base64_decode($dmloaicongviec));
            
            if(is_array($arrayDanhMucLoaiCongViec) && count($arrayDanhMucLoaiCongViec) >0){
                $sqlselect ="UPDATE gscv_loaicongviec SET ACTIVE = 0";
                $db->query($sqlselect);    
                foreach($arrayDanhMucLoaiCongViec as $itemdata){
                    if(LoaiCongViecGiaoViecModel::checkExitsLoaiCongViec($itemdata['CODE'])){
                        $params=array(
                            'NAME'=>$itemdata['NAME'],
                            'CODE'=>$itemdata['CODE'],
                            'ACTIVE'=>1
                        );
                        $db->update('gscv_loaicongviec',$params,'CODE = "'.$itemdata['CODE'].'"');
                    }else{
                        $params=array(
                            'NAME'=>$itemdata['NAME'],
                            'CODE'=>$itemdata['CODE'],
                            'ACTIVE'=>1
                        );
                        $db->insert('gscv_loaicongviec',$params);
                    }
                }
            }
            
            $status = 1;            
        } catch (Exception $e) {
            $status = 0;
            echo $ex->__toString();exit;
        }
    }

    public static function SelectAll($offset=0,$limit=0,$order="") {
        global $db;
        
        //Build phần limit
        $strlimit = "";
        if($limit>0){
                $strlimit = " LIMIT $offset,$limit";
        }

        //Build order
        $strorder = "";
        if($order!="" || $order){
            $strorder = " ORDER BY $order";
        }
        
        $sql ="SELECT * from gscv_loaicongviec where ACTIVE =1 ".$strlimit." ".$strorder ;
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
    
    public static function getLoaiCongViecGiao(){
        global $db;
        $sql ="SELECT * from gscv_loaicongviec where ACTIVE =1";
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
}
