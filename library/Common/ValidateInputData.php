<?php
/**
 * @name lop ValidateInputData : Kiem tra dinh dang du lieu nhap ben server
 * @package : library
 * @version : 1.0
 */
require_once ('Zend/Validate/Abstract.php');
require_once ('Zend/Validate/StringLength.php');
require_once ('Zend/Validate/NotEmpty.php');
require_once 'Zend/Validate/Int.php';
require_once ('Zend/Validate/Between.php');

/**
 * Lop ValidateInputData cung cap cac cac ham kiem tra du lieu nhap tai server 
 *
 */
class ValidateInputData {
	/**
	 * Ham kiem tra do dai cua string co nam trong khoang max hay min 
	 *  
	 */
	static function checkStringLength($str,$min_len , $max_len)
	{
			$val = new Zend_Validate_StringLength();
			$val->setMax($max_len);
			$val->setMin($min_len);
			return $val->isValid($str);
	}
	
	/**
	 * Ham kiem tra doi tuong co rong hay khong
	 */
	static function checkObjectEmptry($obj)
	{
		$val = new Zend_Validate_NotEmpty();
		return $val->isValid($obj);
	}
	/**
	 * Ham kiem tra co phai la so nguyen hay khong
	 */
	static function checkIsInteger($integer){
		$val = new Zend_Validate_Int();
		return $val->isValid($integer);
	}
	/**
	 * Ham kiem tra co dung dinh dang email khong
	 */
	static function checkEmail($str_email){
		$val = new Zend_Validate_EmailAddress();
		return $val->isValid($str_email);
	}

	/**
	 * Ham kiem tra co dung dinh dang nam khong
	 */
	static function checkYearFormat($year_value){
		//year la mot so nguyen co 4 chu so (tu 1000 den 9999)
		return ValidateInputData::checkBetweenInteger($year_value,1000,9999);
	}
	/**
	 * Ham kiem tra co dung dinh dang ngay dd/mm/yyyy
	 */
	static function checkDateFormat($date){
		$val = new Zend_Validate_Date();
		$val->setFormat('dd/mm/yyyy');
		return $val->isValid($date);
	}
	
	/**
	 * Ham kiem tra gia tri integer co nam trong khoang da qui dinh truoc hay khong
	 */
	static function checkBetweenInteger($value,$min_value,$max_value,$inclusive){
		if(!ValidateInputData::checkIsInteger($value))
			return false;
		
		$val_be = new Zend_Validate_Between($min_value,$max_value);
		$val_be->setInclusive($inclusive);
		return $val_be->isValid($value);
	}
 	
	/**
	 * Ham kiem tra cac chuoi text 128 (>=0 , <=128)
	 * Dau vao la mot mang chua cac chuoi can xu ly
	 * Ket qua tra ve la false neu bat cu chuoi nao sai dinh dang
	 */
	
	static function checkTextInput128($str){
		
		return ValidateInputData::checkStringLength($str,0,128);
	}
	
	static function checkTextInput256($str){
		
		return ValidateInputData::checkStringLength($str,0,256);
	}
	
	static function checkTextInput1024($str){
		
		return ValidateInputData::checkStringLength($str,0,1024);
	}
	
	static function checkTextInput2048($str){
		
		return ValidateInputData::checkStringLength($str,0,1024);
	}
	
	/**
	 * Ham kiem tra mot chuoi text khong duoc rong va co do dai doi da la 128
	 */
	static function checkTextInput128_require($str){
		if(!ValidateInputData::checkObjectEmptry($str))
		 return false;
		
		return ValidateInputData::checkStringLength($str,1,128);
	}
	
	static function checkTextInput256_require($str){
		if(!ValidateInputData::checkObjectEmptry($str))
			return false;
		return ValidateInputData::checkStringLength($str,1,256);
	}
	
	static function checkTextInput1024_require($str){
		if(!ValidateInputData::checkObjectEmptry($str))
			return false;
		return ValidateInputData::checkStringLength($str,1,1024);
	}
	
	static function checkTextInput2048_require($str){
		if(!ValidateInputData::checkObjectEmptry($str))
			return false;
		return ValidateInputData::checkStringLength($str,1,1024);
	}
	
	/**
	 * Ham kiem tra ky tu nhap vao co phai la cac ky tu a,...z,0...9
	 */
	static function checkAlnum($str,$alowSpace){
		$val = new Zend_Validate_Alnum();
		$val->allowWhiteSpace = $alowSpace;
		return $val->isValid($str);
	}
	/**
	 * Ham kiem tra ky tu nhap vao co phai la ky tu alpha  hay khong
	 */
	static function checkAlpha($str,$alowSpace){
		$val = new Zend_Validate_Alpha();
		$val->allowWhiteSpace = $alowSpace;
		return $val->isValid($str);
	}
	
	/**
	 * Ham kiem tra chuoi nhap vao co phai la so thap phan hay khong
	 */
	static function checkDecimal($value){
		$val = new Zend_Validate_Float();
		return $val->isValid($value);
	}
	/**
	 * Ham kiem tra chuoi ky tu nhap vao co phai toan la chu so hay khong
	 */
	static function checkDigit($value){
		$val = new Zend_Validate_Digits();
		$val->isValid($value);
	}
	/**
	 * Ham kiem tra chuoi nhap vao co theo dinh dang ve thoi gian hh:mmm:SS
	 */
	static function checkTime(){
		//ham nay chua viet, ai co nhu cau thi viet
		return true;
	}
	/**
	 * Ham kiem tra chuoi nhap vao co phai co kieu boolean hay khong
	 */
	static function checkBoolean($value){
		
		return ValidateInputData::checkBetweenInteger($value,0,1,true);
	}
	
	/**
	 * Ham tong quat
	 * Cach su dung
	 * command :
	 * 		+require(req) : khong duoc rong 
	 * 		+maxlength=30 : chieu dai khong qua 30
	 * 		+str_bet_len=2,10 : chieu dai nam trong khoang 2 den 10
	 */
	static function validateInput($command , $value , $err ){
		
		$arr= explode('=',$command);
		$cmd = $arr[0];
		$cmd_value = $arr[1];
		$arrvalues = explode(',',$cmd_value);
		//return true;
		switch($cmd){
			case "req":
			case 'require':
					if(!ValidateInputData::checkObjectEmptry($value))
						return $err;
			break;		
			case 'maxlength':
					$max =  (int)$cmd_value;	
					if(!ValidateInputData::checkStringLength($value,0,$max))
						return $err;
			break;		
			case 'stringbetweenlength':
					$min =  (int)$arrvalues[0];
					$max =  (int)$arrvalues[1];	
					if(!ValidateInputData::checkStringLength($value,$min,$max))
						return $err;
			break;
			case 'int':
				if(!ValidateInputData::checkIsInteger($value))
					return $err;
			break;	
			case 'date':
				if(!ValidateInputData::checkDateFormat($value))
					return $err;
			break;	
			case 'time':
			break;
			case 'lessthan':
			break;
			case 'greaterthan':
			break;
			case 'email':
				if(!ValidateInputData::checkEmail($value))
					return $err;
			break;	
			case 'password':
			break;	
			case 'text128':
				if(!ValidateInputData::checkTextInput128($value))
					return $err;
			break;	
			case 'text1024':
				if(!ValidateInputData::checkTextInput1024($value))
					return $err;
			break;	
			case 'text2048':
				if(!ValidateInputData::checkTextInput2048($value))
					return $err;
			break;	
			case 'text256':
				if(!ValidateInputData::checkTextInput256($value))
					return $err;
			break;
			case 'text128_re':
				if(!ValidateInputData::checkTextInput128_require($value))
					return $err;
			break;	
			case 'text1024_re':
				if(!ValidateInputData::checkTextInput1024_require($value))
					return $err;
			break;	
			case 'text2048_re':
				if(!ValidateInputData::checkTextInput2048_require($value))
					return $err;
			break;	
			case 'text256_re':
				if(!ValidateInputData::checkTextInput256_require($value))
					return $err;
			break;	
			case 'boolean':
				if(!ValidateInputData::checkBoolean($value))
					return $err;
			break;	
			case 'int_between_inclusive': //[0..5]
				if(!ValidateInputData::checkBetweenInteger($value,$arrvalues[0],$arrvalues[1],true))
					return $err;
			break;	
			case 'int_between_no_inclusive': //(0..5)
				if(!ValidateInputData::checkBetweenInteger($value,$arrvalues[0],$arrvalues[1],false))
					return $err;
			break;	
			case 'decimal':
				if(!ValidateInputData::checkDecimal($value))
					return $err;
			break;	
			case 'alnum':
				if(!ValidateInputData::checkAlnum($value,false))
					return $err;
			break;	
			case 'alnum_s':
				if(!ValidateInputData::checkAlnum($value,true))
					return $err;
			break;	
			case 'alpha':
				if(ValidateInputData::checkAlpha($value,false))
					return $err;
			break;
			case 'alpha_s':
				if(!ValidateInputData::checkAlpha($value,true))
					return $err;
			break;
			case 'year':
				if(!ValidateInputData::checkYearFormat($value))
					return $err;	
			break;
			default:
				if(!Zend_Validate::is($value,$cmd))
					return $err;
			break;
				
		}
		
		return "";
		
	}
	
}


