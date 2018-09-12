<?php

class NguonGocNhiemVuGiaoViecService{

	function login($madonvi, $password, $hashtype){
		if($hashtype == "md5"){
			$password = md5($password);
		}
		return $this->post("login",array("madonvi"=>$madonvi,"password"=>$password));
	}
        function post($action, $parameter){
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $giaoviec_host = $configlienthong->domainlienthong;
		$curl = curl_init();
		$post = http_build_query($parameter);
		curl_setopt ($curl, CURLOPT_URL, $giaoviec_host . "/giaoviec/".$action.".php");
		curl_setopt ($curl, CURLOPT_USERAGENT, sprintf("Mozilla/%d.0",rand(4,5)));
		curl_setopt ($curl, CURLOPT_HEADER, 0);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($curl, CURLOPT_POST, 1);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		$html = curl_exec ($curl);
		if($html === false){
			//error
			$html = "";
		}
		curl_close ($curl);
		return $html;
	}
	function selectDanhMucNguonGocNhiemVu($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectDanhMucNguonGocNhiemVu"
					,"data"=>$data
				)))
			)
		);
	}
        function listDanhMucNguonGocNhiemVu($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"listDanhMucNguonGocNhiemVu"
					,"data"=>$data
				)))
			)
		);
	}
        function insert($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"insertDanhMucNguonGocNhiemVu"
					,"data"=>$data
				)))
			)
		);
	}
        
        function update($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"updateDanhMucNguonGocNhiemVu"
					,"data"=>$data
				)))
			)
		);
	}
        
        function delete($token,$idlcv){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"deleteDanhMucNguonGocNhiemVu"
					,"data"=>array("IDLCV"=>$idlcv)
				)))
			)
		);
	}
        
        function findDanhMucNguonGocNhiemVu($token,$id_ngnv){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"findDanhMucNguonGocNhiemVu"
					,"data"=>array("ID_NGNV"=>$id_ngnv)
				)))
			)
		);
	}
       
        function createDanhMucNguonGocNhiemVu($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createDanhMucNguonGocNhiemVu"
					,"data"=>$data
				)))
			)
		);
	}
}