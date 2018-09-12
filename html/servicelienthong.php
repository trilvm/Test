<?php
class QLVBDHDBLienthong {

    public function PostPage($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, sprintf("Mozilla/%d.0", rand(4, 5)));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        $html = curl_exec($curl);
        curl_close($curl);
        return $html;
    }
    public function CapNhapTrangThai($id, $url1, $url2, $state, $user_id, $password) {
        $checksession = $url1 . '?madonvi=' . $user_id . '&password=' . $password;
        $session = QLVBDHDBLienthong::PostPage($checksession);
        $checkdata = $url2 . '?session=' . $session . '&state=' . $state . '&id_vblienthong=' . $id;
        $data =  QLVBDHDBLienthong::PostPage($checkdata);
        return $data;
    }
}