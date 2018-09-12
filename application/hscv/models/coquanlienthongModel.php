<?php
class coquanlienthongModel {

    function getDataCQLienThong(){
        /*
          $db = Zend_Db_Table::getDefaultAdapter();
          $auth = Zend_Registry::get('auth');
          $user = $auth->getIdentity();
		$sql = "select vb.*,vbparent.NAME as NAME_PARENT 
                        from vb_coquan vb 
                        left join vb_coquan vbparent on vb.CODE_PARENT =vbparent.CODE 
                        where vb.LIENTHONG = 1";
          try{
          $qr = $db->query($sql);
          return $qr->fetchAll();
          }catch(Exception $ex){

          }
        */
        $config = new Zend_Config_Ini('../application/config.ini', 'general', true);
        $arrLogin = array(
            "madonvi" => $config->service->lienthong->username,
            "password" => $config->service->lienthong->password
        );

        $url = $config->service->lienthong->uri;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 404) {
                $ketnoi = 0;
            } else {
                $ketnoi = 1;
            }
        } else {
            $ketnoi = 0;
        }
        if ($ketnoi) {
            $client = new SoapClient($config->service->lienthong->uri);
            $session = $client->__call('Login', $arrLogin);

            $param = array(
                'session' => $session,
                'service_code' => 'DONGBODANHMUC',
                'service_name' => 'DV',
                'parameter' => 'DV'
            );

            $result = $client->__call('Execute', $param);

            $rs = ServicesCommon::DeseriallizeToArray(base64_decode($result));
            $arrayReturn = array();
            foreach ($rs as $itemdonvi) {
                $arrayItem = array('ID_DONVI' => $itemdonvi['ID_DONVI'], 
                    'CODE' => $itemdonvi['MADONVI'], 
                    'NAME' => $itemdonvi['NAME'],
                    'NHOM' => $itemdonvi['NHOMDONVI'],
                    'CODE_PARENT' => $itemdonvi['CODE_PARENT'],
                    'IS_DVTT' => $itemdonvi['IS_DVTT'],
                    'NAME_PARENT' => $itemdonvi['NAME_PARENT']                        
                );
                array_push($arrayReturn, $arrayItem);
            }
            
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = "
                            select * from vpcp_agency order by Name ASC
                    ";
            $qr = $db->query($sql);
            $iteamvpcp = $qr->fetchAll();
            foreach ($iteamvpcp as $itemdonvivpcp) {
                if($itemdonvivpcp['Name'] != NULL && $itemdonvivpcp['Name'] != '')
                {
                    $arrayItemvpcp = array('ID_DONVI' => $itemdonvivpcp['Id'], 
                        'CODE' => $itemdonvivpcp['Code'], 
                        'NAME' => $itemdonvivpcp['Name'],
                        'NHOM' => 4,
                        'CODE_PARENT' => $itemdonvivpcp['Pid'],
                        'IS_DVTT' => 2,
                        'NAME_PARENT' => $itemdonvivpcp['ParrentName']                        
                    );
                    array_push($arrayReturn, $arrayItemvpcp);
                }
            }
            return $arrayReturn;
        } else {
            return array();
        }
    }
    public static function  getNameLienThongByCode($code,$arraydonvi){
        $name="";
        foreach($arraydonvi as $item){
            if($item['CODE']==$code){
                $name=$item['NAME'];
            }
        }
        return $name;
    }
    public function getCQlienthongVpcpByCode($stringCode) {
        $r = $this->getDefaultAdapter()->query(" SELECT * FROM vpcp_agency where Code = ? ", array($stringCode));
        $row = $r->fetch();
        return $row['Name'];
    }
}
