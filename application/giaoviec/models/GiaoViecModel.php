<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GiaoviecModel
 *
 * @author trilvm
 */
class GiaoViecModel {

    public static function TaoCongViecFromVBLT($id_vblt, $formData) {
        $arrDonvinhan_lienthong = array();
        foreach ($formData["NOIDUNGNV_GIAOVIEC"] as $k => $itemCqLienThong) {
            $tenDate = 'NGAY_KETTHUC_GIAOVIEC' . $k;
            $donvinhan = array();
            foreach ($formData['ID_CQLIENTHONG'][$k] as $g => $itemdv) {
                $dataitemdv = array(
                    "DONVINHAN" => $formData["ID_CQLIENTHONG"][$k][$g],
                    "LADONVICHINH" => $formData[$tenDate . $g] != '' ? 1 : 0,
                    "CHUYENVIENTHEODOI" => (int) $formData["cb_nguoi"][$k] == 0 ? "" : $formData["nguoitheodoi_text"][$k],
                    "ID_CHUYENVIENTHEODOI" => (int) $formData["cb_nguoi"][$k],
                    "NGAYDUKIENHOANTHANH" => QLVBDHCommon::doDateViet2Standard($formData[$tenDate . $g]),
                    "VANBANLIENTHONGID" => $id_vblt
                );
                if($formData['XULY'.$k.$g] != 1)
                {
                    array_push($donvinhan, $dataitemdv);
                }
            }
            $dataitem = array(
                'TENCONGVIEC' => $formData["NOIDUNGNV_GIAOVIEC"][$k],
                'SOKYHIEU' => $formData['SOKYHIEU'],
                'TRICHYEU' => $formData['TRICHYEU'],
                'NGAYBANHANH' => implode("-", array_reverse(explode("/", $formData['NGAYBANHANH']))),
                'MACONGVIECCHA' => "",
                'DONVINHAN' => $donvinhan,
                "ID_NGUOIKY" => (int)$formData['NGUOIKY'],
                "HOTENNGUOIKY" => $formData['NGUOIKY_TEXT'],
                "ID_LOAICONGVIEC" => (int)$formData['LOAICV_GIAOVIEC']
            );
            array_push($arrDonvinhan_lienthong, $dataitem);
        }
        $arrdatagiaoviec = $arrDonvinhan_lienthong;
        $giaoviecservice = new GiaoViecService();
        $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
        $madonvi = $configlienthong->service->lienthong->username;
        $password = $configlienthong->service->lienthong->password;
        $token = $giaoviecservice->login($madonvi, md5($password), "");
        $re2 = $giaoviecservice->createCongViec(
                $token
                , $arrdatagiaoviec
        );
        return json_decode($re2);
    }
    
    static function WriteDonVi($sel)
    {
        global $auth;
        $des = " -- Chọn đơn vị -- ";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            select *,TRIM(BOTH '/' FROM concat(IFNULL(KYHIEU,''),'/',IFNULL(NAME,''))) as NAMEKYHIEU from vb_coquan where ISSYSTEMCQ = 2 order by NAME ASC ");
      
        $data = $result->fetchAll();
        $html = "";
        $html .= "<option value='0'>" . $des . "</option>";
        foreach ($data as $row) {
            $html .= "<option value='" . $row["CODE"] . "' " . ($row["CODE"] == $sel ? "selected" : "") . ">" . $row["NAME"] . "</option>";
        }
        return $html;
    }
    
    public function getFileDinhKem($id) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "select id_vbdi from vbdi_vanbandi_".QLVBDHCommon::getYear()." where ID_VBLIENTHONG = '" . $id . "' ";
        $query = $dbAdapter->query($sql);              
        $return = $query->fetch();
        $sql2 = "
                SELECT dk.* FROM ".QLVBDHCommon::Table("GEN_FILEDINHKEM")." dk 
                WHERE
                         ID_OBJECT = '". $return["id_vbdi"]."'
                         
        ";        
        $r = $dbAdapter->query($sql2);
        $file = $r->fetchAll();
        return $file;
    }
    public static function getdanhsachnhiemvugiao($id,$offset,$limit)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }
        $sql = "select * from nhiemvu_".QLVBDHCommon::getYear()." where ID_NGUOIGIAO = $id $strlimit ";
        $query = $dbAdapter->query($sql);              
        $return = $query->fetchAll(); 
        return $return;
    }
    public static function getdanhsachnhiemvunhan($id,$offset,$limit)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }
        $sql = "select * from nhiemvu_".QLVBDHCommon::getYear()." where ID_NGUOINHAN = $id $strlimit ";
        $query = $dbAdapter->query($sql);              
        $return = $query->fetchAll(); 
        return $return;
    }
    //vuld add func get file dinh kem
    public static function ShowFileDinhKem($session,$ID_FILEDINHKEM,$MASOVANBAN) {
        global $config;
        try {
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'GETFILE',
                'service_name' => 'GETFILEDINHKEM',
                'parameter' => $ID_FILEDINHKEM);
            if ($session == '' || $session == 0) {
                $session = self::LoginService();
            }
            $dinhkiem_info = $client->__call('Execute', $params);
            $data_dk = ServicesCommon::DeseriallizeToArray(base64_decode($dinhkiem_info));

            foreach ($data_dk as $item => $value) {
                $link = '';
                $link .= "http://".$config->service->lienthong->host."/?download=1&session=" . $session;
                $link .= "&masovanban=" . $MASOVANBAN;
                $link .= "&maso=" . $value['MASO'];
                echo "<a href='$link'>" . $value['FILENAME'] . "</a>;";
            }
        } catch (Exception $e) {
            return;
        }
    }

    public static function ShowFileAll($session,$ID_CONGVIECDETAIL,$MASOVANBAN) {
        global $config;
        try {
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'GETFILE',
                'service_name' => 'GETFILEAll',
                'parameter' => $ID_CONGVIECDETAIL);
            if ($session == '' || $session == 0) {
                $session = self::LoginService();
            }
            $dinhkiem_info = $client->__call('Execute', $params);
            
            //$data_dk = ServicesCommon::DeseriallizeToArray(base64_decode($dinhkiem_info));
            $data_dk = json_decode($dinhkiem_info);
            foreach ($data_dk as $item) {
                $link = '';
                $link .= "http://".$config->service->lienthong->host."/?download=1&session=" . $session;
                $link .= "&masovanban=" . $MASOVANBAN;
                $link .= "&maso=" . $item->MASO;
                echo "<a href='$link'>" . $item->FILENAME . "</a>";
                echo "</br>";
            }
        } catch (Exception $e) {
            return;
        }
    }

    public static function LoginService() {
        global $config;
        $client = new SoapClient($config->service->lienthong->uri);
        return $client->__call('Login', array(
            'madonvi' => $config->service->lienthong->username,
            'password' => $config->service->lienthong->password));
    }
    //vuld end
}
