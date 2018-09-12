<?php

class LoaiCongViecGiaoViecService{

	function login($madonvi, $password, $hashtype){
		if($hashtype == "md5"){
			$password = md5($password);
		}
		return $this->post("login",array("madonvi"=>$madonvi,"password"=>$password));
	}
        function getdonvi($madonvi){
		return $this->post("getdonvi",array("madonvi"=>$madonvi));
	}
        function post($action, $parameter){
                $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                $giaoviec_host = $configlienthong->service->lienthong->host;
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
	function selectDanhMucLoaiCongViec($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectDanhMucLoaiCongViec"
					,"data"=>$data
				)))
			)
		);
	}
        function listDanhMucLoaiCongViec($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"listDanhMucLoaiCongViec"
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
					"method"=>"insertDanhMucLoaiCongViec"
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
					"method"=>"updateDanhMucLoaiCongViec"
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
					"method"=>"deleteDanhMucLoaiCongViec"
					,"data"=>array("IDLCV"=>$idlcv)
				)))
			)
		);
	}
        
        function findDanhMucLoaiCongViec($token,$id_lcv){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"findDanhMucLoaiCongViec"
					,"data"=>array("ID_LCV"=>$id_lcv)
				)))
			)
		);
	}
       
        function createDanhMucLoaiCongViec($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createDanhMucLoaiCongViec"
					,"data"=>$data
				)))
			)
		);
	}
}