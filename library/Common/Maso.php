<?php

class Common_Maso {
	
	static function getLastSubStr($str,$length){
		$len_str = strlen($str);
		
		if($len_str < $length){//truong hop length lon hon chieun dai chuoi -> them so khong dau chuoi
			$d = $length- $len_str;
			$str_add = str_repeat('0',$d);
			return $str_add.$str;
		}
		if($len_str > $length){//truong hop length dai hon->lay cac ky tu ben phai
			$l = 0 - $length;
			return substr($str,-2);
		}
		return $str;
		//return substr($str,-);
	}
	static function getYear($length){
		
		$year = QLVBDHCommon::getYear();
		//return $year;
		return Common_Maso::getLastSubStr($year,$length);
		
	}
	static function getYearbanhanh($length,$object){
		if($object->_ngaybanhanh){
		$year= explode("/",$object->_ngaybanhanh);	
		 return Common_Maso::getLastSubStr($year[2],$length);
		}else{
			$year = QLVBDHCommon::getYear();
			return Common_Maso::getLastSubStr($year,$length);
		}		
	}
	static function getdaybanhanh($length,$object){
		if($object->_ngaybanhanh){
		$day= explode("/",$object->_ngaybanhanh);	
		 return Common_Maso::getLastSubStr($day[0],$length);
		}	
	}
	static function getmonthbanhanh($length,$object){
		if($object->_ngaybanhanh){
		$month= explode("/",$object->_ngaybanhanh);	
		 return Common_Maso::getLastSubStr($month[1],$length);
		}
	}
	static function getMaCoQuanNoiBo($length){
		
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `vb_coquan` where `ISSYSTEMCQ` = 1
		";
		$qr = $dbApdapter->query($sql);
		$re = $qr->fetch();
		
		return Common_Maso::getLastSubStr($re["CODE"],$length) ;
	}
	static function getTinhThanhNoiBo($length){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `qtht_tinhthanh` where `ISLOCAL` = 1
		";
		$qr = $dbApdapter->query($sql);
		$re = $qr->fetch();
		return Common_Maso::getLastSubStr($re["CODE"],$length);
	}
	
	static function getMaLoaiHoSoMotCua($idLoaiHSMC,$length){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `motcua_loai_hoso` where `ID_LOAIHOSO` = ?
		";
		$qr = $dbApdapter->query($sql,array($idLoaiHSMC));
		$re = $qr->fetch();
		//echo $idLoaiHSMC;
		return Common_Maso::getLastSubStr($re["CODE"],$length);
	}

	static function getMaLVHoSoMotCua($idLoaiHSMC,$length){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select mc_lv.CODE from `motcua_loai_hoso` mc_loai 
			inner join `motcua_linhvuc` mc_lv  on mc_loai.ID_LV_MC = mc_lv.ID_LV_MC
			where `ID_LOAIHOSO` = ?
		";
		$qr = $dbApdapter->query($sql,array($idLoaiHSMC));
		$re = $qr->fetch();
		//echo $idLoaiHSMC;
		return Common_Maso::getLastSubStr($re["CODE"],$length);
	}
	static function getMaPhongBan($idPhongBan,$length){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE_DEP from `qtht_departments` where `ID_DEP` = ?
		";
		$qr = $dbApdapter->query($sql,array($idPhongBan));
		$re = $qr->fetch();
		return Common_Maso::getLastSubStr($re["CODE_DEP"],$length);
	}
	static function getSoCoDinh($value,$length){
		return Common_Maso::getLastSubStr($value,$length);
	}
	static function getSoKhongCoDinh($func_tr,$length){
		//chua lam
		return Common_Maso::getLastSubStr("999",$length);
	}
	static function getChuoiCoDinh($value,$length){
		return Common_Maso::getLastSubStr($value,$length);
	}
	static function getChuoiKhongCoDinh($func_tr,$length){
		//chua lam
		return Common_Maso::getLastSubStr("CODE",$length);
	}
	static function getFieldOnDb($loai,$field,$length,$object){
		
		switch ($loai)
		{
				case 1: // van ban den  
					{
						
						switch($field){
							case 1: //ma co quan ban hanh
								return  Common_Maso::getMaCoQuan($object->_id_cq,$length);
								break;
							case 2: //ma loai van ban
								return  Common_Maso::getMaLoaiVanBan($object->_id_lvb,$length);
								break;
							case 3: //do mat
								return Common_Maso::getLastSubStr($object->_domat,$length);
								break;
							case 4: //do khan
								return Common_Maso::getLastSubStr($object->_dokhan,$length);
								break;
							case 5: // so den
								return Common_Maso::getLastSubStr($object->_soden,$length);
								break;
 							default:
								break;
						}
					}
					break;
				case 2: //van ban di
				{
					switch($field){
							
							case 1: //ma loai van ban
								return  Common_Maso::getMaLoaiVanBan($object->_id_lvb,$length);
								break;
							case 2: //do mat
								return Common_Maso::getLastSubStr($object->_domat,$length);
								break;
							case 3: //do khan
								return Common_Maso::getLastSubStr($object->_dokhan,$length);
								break;
							case 4: // so den
								return Common_Maso::getLastSubStr($object->_sodi,$length);
								break;
 							default:
								break;
						}		
				}
					break;
				case 3: // ho so mot cua
				switch($field){
							
							case 1: //ma loai ho so 
								return  Common_Maso::getMaLoaiHoSoMotCua($object->_id_loaihoso,$length);
								break;
							case 2: //ma phong ban
								return Common_Maso::getMaPhongBan($object->_id_phongban,$length);
								break;
							case 3: //so thu tu
								return Common_Maso::getLastSubStr($object->_sothutu,$length);
								break;
							case 4: //so thu tu
								return Common_Maso::getMaLVHoSoMotCua($object->_id_loaihoso,$length);
								break;
							
 							default:
								break;
						}		
					break;
                                case 4://tập hồ sơ
                                    return Common_Maso::getMasoTapHSCV($object, $length);
                                    break;
				default: //khong co
					break;
			}
	}
	static function getMaSo($loai , $object){
	//lay thong tin ve cac truong trong ma so
		$dpapAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "Select * 
		from `gen_maso` ms
		where ms.`LOAI` = ?
		order by ms.`ORDER`  ASC
		";
		$qr = $dpapAdapter->query($sql,array($loai));
		$re = $qr->fetchAll();
		
		$str_maso = "";
		//var_dump($object);
		//echo count($re);
		$dem =1;
		
		foreach ($re as $row){
			//echo "<br>";
			//var_dump($row);
			//echo "</br>";
			$length = $row['LENGTH'];
			//echo $length;
			$str_item="";
			switch($row['KIEU']){
				case 1 : //nam
					$str_item = Common_Maso::getYear($length);
					continue;
				case 2 : // ma co quan noi bo
					$str_item = Common_Maso::getMaCoQuanNoiBo($length);
					continue;
				case 3 : // ma tinh thành noi bo
					$str_item = Common_Maso::getTinhThanhNoiBo($length);
					continue;
				case 4 : // so co dinh
					$str_item = Common_Maso::getSoCoDinh($row['VALUE'],$length);
					continue;
				case 5 : // so khong co dinh
					$str_item = Common_Maso::getSoKhongCoDinh($row['PHP_FUNC'],$length);
					continue;
				case 6 : //chuoi co dinh
					$str_item = Common_Maso::getChuoiCoDinh($row['VALUE'],$length);
					continue;
				case 7 : // chuoi khong co dinh
					$str_item = Common_Maso::getChuoiKhongCoDinh($row['PHP_FUNC'],$length);
					continue;
				case 8 : // truong co trong co so du lieu
				{
					//Them cac truong co trong co so du lieu
					$str_item = Common_Maso::getFieldOnDb($row['LOAI'],$row['FIELD'],$length,$object);
					//echo $dem;	
				}
				continue;
				case 9 : // 
				$str_item = Common_Maso::getYearbanhanh($length,$object);
					continue;
                case 10 : // 
				$str_item = Common_Maso::getdaybanhanh($length,$object);
					continue;
                case 11 : // 
				$str_item = Common_Maso::getmonthbanhanh($length,$object);					
				continue;
				
			}
			
			//echo "----";
			//echo $str_item;
			$str_maso .=$str_item;
		}
		return $str_maso;
	}
/**
 * lay cac truong co trong co so du lieu
 */
        static function getKyhieuDeptById($id) {
            
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = 'select * from `qtht_departments` where `ID_DEP` = ?';
            try{
                $r = $db->query($sql, array($id))->fetch();
            }catch (Exception $ex) {
                die($ex->__toString());
            }
            return $r['KYHIEU_PB'];
        }

        static function getCODEByDep($object) {

            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = 'select CODE from `hscvdt_taphoso` where `ID_DEP` = ?  order by CODE DESC limit 1';
            $sql2 = "select LENGTH from gen_maso where LOAI=4";
            $str_arr = array();
            try{
                $r = $db->query($sql, array($object->_id_dept))->fetch();
                if (!$r) {
                    $c = $db->query($sql2)->fetch();
                    $lenght = $c['LENGTH'];
                    return Common_Maso::MaSoProcess("", $lenght);
                }
            }catch (Exception $ex) {
                die($ex->__toString());
            }
            return $r['CODE'];
        }
        static function MaSoProcess($maso, $lenght) {

            $maso = substr($maso, -$lenght);
            $ketqua = $maso + 1;
            //echo strlen($chuoi);
            while ($lenght > strlen($ketqua)) {
                $ketqua = '0' . $ketqua;
            }
            return $ketqua;
        }
        // static function MaSoProcess($maso, $lenght) {
//
//            $code = 0;
//            $str_arr = array();
//            if ($lenght > 0) {
//                for ($i = 0; $i < $lenght - 1; $i++) {
//                    $str_arr[] = '0';
//                }
//            }
//            $code = (int) $maso + 1;
//            $str_maso = implode('', $str_arr);
//            return $str_maso.$code;
//        }
        /*
         * Lấy mã số tập hồ sơ
         */
        static function getMasoTapHSCV($object, $lenght) {
            
            //get CODE by DEPT
            $code = Common_Maso::getCODEByDep($object);
            $maso = $code;
            //ký hiệu phòng
            $dept = Common_Maso::getKyhieuDeptById($object->_id_dept);
            $maso_new = Common_Maso::MaSoProcess($maso, $lenght);
            return $dept.$maso_new;
        }
//van ban den
	
	static function getMaLoaiVanBan($id_lvb,$length){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `vb_loaivanban` where `ID_LVB` = ?
		";
		$qr = $dbApdapter->query($sql,array($id_lvb));
		$re = $qr->fetch();
		return Common_Maso::getLastSubStr($re["CODE"],$length)  ;
	}
	
	static function getMaCoQuan($id_cq,$length){
		
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `vb_coquan` where `ID_CQ` = ?
		";
		$qr = $dbApdapter->query($sql,array($id_cq));
		$re = $qr->fetch();
		
		return Common_Maso::getLastSubStr($re["CODE"],$length) ;
	}
	
	static function getIdDepByUser($id_u){
		$dbApdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select d.`ID_DEP` from
			(`qtht_users` u inner join `qtht_employees` e on u.`ID_EMP` = e.`ID_EMP`)
			join `qtht_departments` d
			where e.`ID_DEP` = d.`ID_DEP` and u.`ID_U` = ?
		";
		$qr = $dbApdapter->query($sql,array($id_u));
		$re = $qr->fetch();
		
		return $re["ID_DEP"] ;
	}
	
}


?>
