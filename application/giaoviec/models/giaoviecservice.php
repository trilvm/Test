<?php

class GiaoViecService{

	function login($madonvi, $password, $hashtype){
		if($hashtype == "md5"){
			$password = md5($password);
		}
		return $this->post("login",array("madonvi"=>$madonvi,"password"=>$password));
	}

	function acceptCongViec($token,$macongviec,$idnguoidung, $hotennguoidung, $phongbannguoidung){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"acceptCongViec"
					,"data"=>array("MACONGVIEC"=>$macongviec,"HOTENNGUOINHAN"=>$hotennguoidung,"ID_NGUOINHAN"=>$idnguoidung,"PHONGBANNGUOINHAN"=>$phongbannguoidung)
				)))
			)
		);
	}
	function theoDoiGiaoViecNhan($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"theoDoiGiaoViecNhan"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi)
				)))
			)
		);
	}
        function theoDoiChiTietGiaoViecNhan($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi,$donvinhan,$reporttype){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"theoDoiChiTietGiaoViecNhan"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVIGIAO"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi,"DONVINHAN"=>$donvinhan,"REPORTTYPE"=>$reporttype)
				)))
			)
		);
	}
	function rejectCongViec($token,$macongviec,$idnguoituchoi, $hotennguoituchoi, $phongbannguoituchoi, $lydotuchoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"rejectCongViec"
					,"data"=>array("MACONGVIEC"=>$macongviec,"HOTENNGUOITUCHOI"=>$hotennguoituchoi,"ID_NGUOITUCHOI"=>$idnguoituchoi,"PHONGBANNGUOITUCHOI"=>$phongbannguoituchoi,"LYDOTUCHOI"=>$lydotuchoi)
				)))
			)
		);
	}
        function xacnhanTrangThaiCongViec($token,$macongviec,$donvinhan,$trangthai){

		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"xacnhanTrangThaiCongViec"
					,"data"=>array("MACONGVIEC"=>$macongviec,"DONVINHAN"=>$donvinhan,"TRANGTHAI"=>$trangthai)
				)))
			)
		);
	}
        function giahanxulyCongViec($token,$macongviec,$donvinhan,$ngaydukienhoanthanhmoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"giahanxulyCongViec"
					,"data"=>array("MACONGVIEC"=>$macongviec,"DONVINHAN"=>$donvinhan,"NGAYDUKIENHOATHANHHIENTAI"=>$ngaydukienhoanthanhmoi)
				)))
			)
		);
	}
	function selectCongViec($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$nguoithuchien,$idnguoixuly,$offset,$limit){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectCongViec"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec
					,"OFFSET"=>$offset,"LIMIT"=>$limit,"ID_NGUOINHAN"=>$idnguoixuly,"NGUOITHUCHIEN"=>$nguoithuchien)
				)))
			)
		);
	}
        
        function countCongViec($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$nguoithuchien,$idnguoixuly){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"countCongViec"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec
					,"ID_NGUOINHAN"=>$idnguoixuly,"NGUOITHUCHIEN"=>$nguoithuchien)
				)))
			)
		);
	}
        function selectCongViecKhongVB($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoixuly,$offset,$limit){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectCongViecKhongVB"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_NGUOINHAN"=>$idnguoixuly,"OFFSET"=>$offset,"LIMIT"=>$limit)
				)))
			)
		);
	}
        
        function countCongViecKhongVB($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoixuly){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"countCongViecKhongVB"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_NGUOINHAN"=>$idnguoixuly)
				)))
			)
		);
	}
        function selectCongViecGiao($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoitheodoi,$offset,$limit){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectCongViecGiao"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_CHUYENVIENTHEODOI"=>$idnguoitheodoi,"OFFSET"=>$offset,"LIMIT"=>$limit)
				)))
			)
		);
	}
        
        function countCongViecGiao($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"countCongViecGiao"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_CHUYENVIENTHEODOI"=>$idnguoitheodoi)
				)))
			)
		);
	}
        function selectCongViecGiaoKhongVB($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoitheodoi,$offset,$limit){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectCongViecGiaoKhongVB"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_CHUYENVIENTHEODOI"=>$idnguoitheodoi,"OFFSET"=>$offset,"LIMIT"=>$limit)
				)))
			)
		);
	}
        
        function countCongViecGiaoKhongVB($token,$trangthai=-1,$cqlienthong,$sokyhieu,$tencongviec,$idnguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"countCongViecGiaoKhongVB"
					,"data"=>array("TRANGTHAI"=>$trangthai,"CQLIENTHONG"=>$cqlienthong,"SOKYHIEU"=>$sokyhieu,"TENCONGVIEC"=>$tencongviec,"ID_CHUYENVIENTHEODOI"=>$idnguoitheodoi)
				)))
			)
		);
	}
        
	function selectThamGiaXuLy($token,$macongviec){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"selectThamGiaXuLy"
					,"data"=>array("MACONGVIEC"=>$macongviec)
				)))
			)
		);
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
    function createCongViec($token,$data){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createCongViec"
					,"data"=>$data
				)))
			)
		);
	}
	function SelectCongViecByIdlt($token,$idlt){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectCongViecByIdlt"
					,"data"=>array("ID_VBLIENTHONG_VAO"=>$idlt)
				)))
			)
		);
	}
        function SelectTienDoCongViecByID($token,$id){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectTienDoCongViecByID"
					,"data"=>array("ID_CONGVIECDETAIL"=>$id)
				)))
			)
		);
	}
        
        function SelectCongViecByIdltRa($token,$idlt){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectCongViecByIdltRa"
					,"data"=>array("ID_VBLIENTHONG_RA"=>$idlt)
				)))
			)
		);
	}
	function SelectCongViecByIdltVao($token,$idlt){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectCongViecByIdltVao"
					,"data"=>array("ID_VBLIENTHONG_VAO"=>$idlt)
				)))
			)
		);
	}
	function getListDonVi($token){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"getListDonVi"
					,"data"=>array()
				)))
			)
		);
	}

	function thongKeGiaoViec($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"thongKeGiaoViec"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi)
				)))
			)
		);
	}
        function theoDoiGiaoViec($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"theoDoiGiaoViec"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi)
				)))
			)
		);
	}
        function ketDuThangTruoc($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"ketDuThangTruoc"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi)
				)))
			)
		);
	}
        
        function theoDoiChiTietGiaoViec($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi,$donvinhan,$reporttype){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"theoDoiChiTietGiaoViec"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi,"DONVINHAN"=>$donvinhan,"REPORTTYPE"=>$reporttype)
				)))
			)
		);
	}
	function chiTietGiaoViec($token,$fromdate,$todate,$listdonvi,$nguoiky,$nguoitheodoi,$donvinhan,$reporttype){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"chiTietGiaoViec"
					,"data"=>array("FROMDATE"=>$fromdate,"TODATE"=>$todate,"DONVINHAN"=>$listdonvi,"NGUOIKY"=>$nguoiky,"NGUOITHEODOI"=>$nguoitheodoi,"DONVINHAN"=>$donvinhan,"REPORTTYPE"=>$reporttype)
				)))
			)
		);
	}
	function SelectCongViecByMacongviec($token,$macongviec){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectCongViecByMacongviec"
					,"data"=>array("MACONGVIEC"=>$macongviec)
				)))
			)
		);
	}
        function SelectNhatKyCongViecByMacongviec($token,$macongviec){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectNhatKyCongViecByMacongviec"
					,"data"=>array("MACONGVIEC"=>$macongviec)
				)))
			)
		);
	}
        function UpdateTrangThaiCongViec($token,$idlt,$trangthai,$userid,$username,$macongviec='',$lydotuchoi=''){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateTrangThaiCongViec"
					,"data"=>array("ID_VBLIENTHONG"=>(int)$idlt,"TRANGTHAI" => $trangthai,"ID_U"=>$userid,"USERNAME" => $username,"MACONGVIEC"=>$macongviec,"LYDOTUCHOI" => $lydotuchoi)
				)))
			)
		);
	}
        function UpdateNguoiXLCongViec($token,$macongviec,$userid,$username,$tiendo){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateNguoiXLCongViec"
					,"data"=>array("MACONGVIEC"=>$macongviec,"ID_U"=>$userid,"USERNAME" => $username,"TIENDO"=>$tiendo)
				)))
			)
		);
	}
        function createNhatKy($token,$macongviec,$userid,$username,$tiendo,$motatiendo,$user_dep,$bosungchitiet){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createNhatKy"
					,"data"=>array("MACONGVIEC"=>$macongviec,"ID_U"=>$userid,"USERNAME" => $username,"TIENDO"=>$tiendo
					,"MOTATIENDO"=>$motatiendo,"PHONG_NGUOIXL"=>$user_dep,"BOSUNGCHITIET"=>$bosungchitiet)
				)))
			)
		);
        }
		function createNhatKyGiao($token,$macongviec,$userid,$username,$tiendo,$motatiendo,$user_dep){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createNhatKyGiao"
					,"data"=>array("MACONGVIEC"=>$macongviec,"ID_U"=>$userid,"USERNAME" => $username,"TIENDO"=>$tiendo
					,"MOTATIENDO"=>$motatiendo,"PHONG_NGUOIXL"=>$user_dep)
				)))
			)
		);
        }
        function sendSMS($token,$phone,$content){
		
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SendSMS"
					,"data"=>array("Phone"=>$phone,"Content"=>$content)
				)))
			)
		);
	}
        function UpdateLyDoTreHan($token,$macongviec,$userid,$username,$lydotrehan){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateLyDoTreHan"
					,"data"=>array("MACONGVIEC"=>$macongviec,"ID_U"=>$userid,"USERNAME" => $username,"LYDOTREHAN"=>$lydotrehan)
				)))
			)
		);
	}
        function SelectCongViecByIdUser($token,$id_user){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectCongViecByIdUser"
					,"data"=>array("ID_NGUOINHAN"=>$id_user)
				)))
			)
		);
	}
	//vuld add func select tien do cong viec 20/8/2018
	function SelectTienDoCongViecByIDSTT($token,$id){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"SelectTienDoCongViecByIDSTT"
					,"data"=>array("ID_CONGVIECDETAIL"=>$id)
				)))
			)
		);
	}
	// func update tien do,trang thai CV
	function UpdateTrangThaiCV($token,$idlt,$trangthai,$macongviec='',$lydotuchoi=''){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateTrangThaiCV"
					,"data"=>array("ID_VBLIENTHONG"=>(int)$idlt,"TRANGTHAI" => $trangthai,"MACONGVIEC"=>$macongviec,"LYDOTUCHOI" => $lydotuchoi)
				)))
			)
		);
	}
    function UpdateTienDoCV($token,$macongviec,$tiendo){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateTienDoCV"
					,"data"=>array("MACONGVIEC"=>$macongviec,"TIENDO"=>$tiendo)
				)))
			)
		);
	}
	//func update ly do tre han 
	function UpdateLyDoTreHanSTT($token,$macongviec,$lydotrehan){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"UpdateLyDoTreHan"
					,"data"=>array("MACONGVIEC"=>$macongviec,"LYDOTREHAN"=>$lydotrehan)
				)))
			)
		);
	}
	//xac nhan trang thai cong viec STTTT
	function xacnhanTrangThaiCongViecSTT($token,$macongviec,$donvinhan,$trangthai){
		
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"xacnhanTrangThaiCongViecSTT"
					,"data"=>array("MACONGVIEC"=>$macongviec,"DONVINHAN"=>$donvinhan,"TRANGTHAI"=>$trangthai)
				)))
			)
		);
	}
	//create nhat ky dinh kem file
	function createNhatKyDinhKem($token,$macongviec,$userid,$username,$tiendo,$motatiendo,$user_dep,$bosungchitiet,$id_dk){
		return $this->post("api",
			array(
				"token"=>$token
				,"data"=>base64_encode(json_encode(array(
					"method"=>"createNhatKyDinhKem"
					,"data"=>array("MACONGVIEC"=>$macongviec,"ID_U"=>$userid,"USERNAME" => $username,"TIENDO"=>$tiendo
					,"MOTATIENDO"=>$motatiendo,"PHONG_NGUOIXL"=>$user_dep,"BOSUNGCHITIET"=>$bosungchitiet,"ID_DK"=>$id_dk)
				)))
			)
		);
	}
	//vuld end 
}