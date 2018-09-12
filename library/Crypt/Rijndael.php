<?php 
class Crypt_Rijndael{
	
	var $_key;
	function __construct($key){
		$this->_key = $key;
	}
	public function doEncryptDataUtf8ToFile($data,$tranfer_ecoding,$file_crypt){
		//echo $tranfer_ecoding; exit;
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'crypt');
		$filename_temp =  $config_mail->crypt->default->temp.DIRECTORY_SEPARATOR.date('H_i_s');
		
		$fp = @fopen($filename_temp, 'w');
		//echo $tranfer_ecoding; exit;
		if($tranfer_ecoding == "quoted-printable")
				stream_filter_append($fp, "convert.quoted-printable-decode");
		if($tranfer_ecoding == "base64")
				stream_filter_append($fp, "convert.base64-decode");
		
		@fwrite($fp, $data);
		@fclose($fp);
		//echo $tranfer_ecoding; 
		$data_after_save = file_get_contents($filename_temp);
		//echo $data_after_save; exit;
		$data_after_save = Crypt_Util::pkcs5_pad($data_after_save,$this->getBlockSize());
		$encrypted_data = mcrypt_ecb (MCRYPT_RIJNDAEL_256, $this->_key, $data_after_save, MCRYPT_ENCRYPT);
		
		
		//$encrypted_base64_data = base64_encode($encrypted_data);
		$fp = @fopen($file_crypt, 'w');
		@fwrite($fp, $encrypted_data);
		@fclose($fp);
		//$data = file_get_contents($file_crypt);
		//$decrypted_data = mcrypt_ecb (MCRYPT_RIJNDAEL_256, $this->_key, $encrypted_data, MCRYPT_DECRYPT);
		//echo $decrypted_data; exit;
		//Xóa file tạm
		unlink($filename_temp);
		return 1;
	}

	

	public function doGetDecryptDataUtf8FromFile($file_crypt){
		$data = file_get_contents($file_crypt);
		//return $data."ddd";
		//$data = base64_decode($data);
		//return $data;
		$decrypted_data = mcrypt_ecb (MCRYPT_RIJNDAEL_256, $this->_key, $data, MCRYPT_DECRYPT);
		//return $decrypted_data;
		$decrypted_data = Crypt_Util::pkcs5_unpad($decrypted_data);
		///$decrypted_data; 
		return $decrypted_data;
		//$fp = @fopen($file_derypt, 'w');
		//@fwrite($fp, $decrypted_data);
		//@fclose($fp);
	}

	public function doEncryptBlock($data){
		$data = Crypt_Util::pkcs5_pad($data,$this->getBlockSize());
		$encrypted_data = mcrypt_ecb (MCRYPT_RIJNDAEL_256, $this->_key, $data, MCRYPT_ENCRYPT);
		
		return $encrypted_data;

	}
	
	public function doDecryptBlock($encrypted_data){
		
		$decrypted_data = mcrypt_ecb (MCRYPT_RIJNDAEL_256, $this->_key, $encrypted_data, MCRYPT_DECRYPT);
		$decrypted_data = Crypt_Util::pkcs5_unpad($decrypted_data);
		return $decrypted_data;
	}

	public function getBlockSize(){
		return 32;
	}
}
?>