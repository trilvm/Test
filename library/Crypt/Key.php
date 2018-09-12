<?php
class Crypt_Key{
	static function createUserKey(){
		$user = Zend_Registry::get('auth')->getIdentity();
		$key  = md5($user->USERNAME.$user->USERNAME.$user->USERNAME."ddd".$user->USERNAME."@lamquarua");
		return $key;
	}
}
?>