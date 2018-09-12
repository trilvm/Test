<?php

require_once 'nusoap/nusoap.php';

class ChoTiepNhanModel {

    public static function ShowFileAttackment($session, $masovanban) {
        global $config;
        try {
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'GETFILE',
                'service_name' => '',
                'parameter' => $masovanban);
            if ($session == '' || $session == 0) {
                $session = self::LoginService();
            }
            $dinhkiem_info = $client->__call('Execute', $params);
            $data_dk = ServicesCommon::DeseriallizeToArray(base64_decode($dinhkiem_info));

            foreach ($data_dk as $item => $value) {
                $link = '';
                $link .= "http://".$config->service->lienthong->host."/?download=1&session=" . $session;
                $link .= "&masovanban=" . $masovanban;
                $link .= "&maso=" . $value['MASO'];
                echo "<a href='$link'>" . $value['FILENAME'] . "</a>;";
            }
        } catch (Exception $e) {
            return;
        }
    }
    //vuld add func show file  30/8/2018
    public static function ShowFileDinhKem($session,$ID_FILEDINHKEM) {
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
                $link .= "&masovanban=" . $masovanban;
                $link .= "&maso=" . $value['MASO'];
                echo "<a href='$link'>" . $value['FILENAME'] . "</a>;";
            }
        } catch (Exception $e) {
            return;
        }
    }
    //vuld end 
    public static function LoginService() {
        global $config;
        $client = new SoapClient($config->service->lienthong->uri);
        return $client->__call('Login', array(
            'madonvi' => $config->service->lienthong->username,
            'password' => $config->service->lienthong->password));
    }

    public static function Counts($session) {
        global $auth; 
        $user = $auth->getIdentity();
        $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
        global $config;
        try {
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
				'session'  =>  $session,
				'service_code' => 'VANBAN',
				'service_name' => 'VANBANCANTIEPNHAN',
				'parameter' => base64_encode(0) . '~' . base64_encode(999). '~' . base64_encode($code)
			);
            if ($session == '' || $session == 0) {
                $session = self::LoginService();
            }
            $response = $client->__call('Execute', $params);
			$data = array_reverse(ServicesCommon::DeseriallizeToArray(base64_decode($response)));
			return count($data);
        } catch (Exception $e) {
            return;
        }
    }

    public static function ShowTrangThai($key) {
        switch ($key) {
            case '1':
                return 'Chưa sẵng sàng';
            case '2':
                return 'Chưa tiếp nhận';
            case '3':
                return 'Đã tiếp nhận';
            case '4':
                return 'Không tiếp nhận';
            case '5':
                return 'Đã phản hồi';
            case '6':
                return 'Đã thu hồi';
            case '7':
                return 'Vô hiệu hóa';
            default:
                return;
        }
    }

    public static function DongLuanChuyen($masovanban, $sokyhieu, $session) {

        global $config;
        try{
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'LUANCHUYEN',
                'service_name' => '',
                'parameter' => base64_encode($masovanban)
            );

            $dlc = $client->__call('Execute', $params);
			return ServicesCommon::DeseriallizeToArray(base64_decode($dlc));

        } catch (Exception $e) {
            return;
        }
    }

    public static function GetFileByMaSoVanBan($masovanban, $session) {
        global $config;
        try{
            $client = new SoapClient($config->service->lienthong->uri);
            $params = array(
                'session' => $session,
                'service_code' => 'GETFILE',
                'service_name' => '',
                'parameter' => $masovanban
            );
            if ($session == '' || $session == 0) {
                $session = self::LoginService();
            }
            $data = $client->__call('Execute', $params);
            return ServicesCommon::DeseriallizeToArray(base64_decode($data));

        } catch (Exception $e) {
            return;
        }
    }

}