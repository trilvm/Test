<?php 
class Mail_Auth {
	
	static function encryptPassword($str_pass){
		$key = Crypt_Key::createUserKey();
		$crypt = new Crypt_Rijndael($key);
		$pass_enc = $crypt->doEncryptBlock($str_pass);
		//$pass_enc = Crypt_Util::pkcs5_pad($pass_enc,$crypt->getBlockSize());
		return base64_encode($pass_enc);
	}
	
	
	static function decryptPassword($str_pass){
		$key = Crypt_Key::createUserKey();
		$crypt = new Crypt_Rijndael($key);
		$pass_enc = base64_decode($str_pass); 
		//$pass_enc = Crypt_Util::pkcs5_unpad($pass_enc);
		return trim($crypt->doDecryptBlock($pass_enc),"\0");
		 
	}
}
?>