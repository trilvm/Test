<?php 
class Mail_Quota {
	
	static function checkQuotaUser(){
		$user =  Zend_Registry::get('auth')->getIdentity();
		$config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');
		$quota_size = $config_mail->mail->quota->maxsize*1024*1024;
		$dir_inbox	= $config_mail->mail->default->mailclientstorage_inbox.DIRECTORY_SEPARATOR.$user->ID_U;
		$dir_sent	= $config_mail->mail->default->mailclientstorage_sent.DIRECTORY_SEPARATOR.$user->ID_U;
		$current_size = Mail_Quota::dirsize($dir_inbox) + Mail_Quota::dirsize($dir_sent);
		
		return $current_size>=$quota_size?0:1;
		//echo $current_size." --- ";
		//echo $quota_size;
		//exit;
	}

	static function dirsize($dirname) {
    
		if (!is_dir($dirname) || !is_readable($dirname)) {
			return false;
		}
		$dirname_stack[] = $dirname;
		$size = 0;
		do {
			$dirname = array_shift($dirname_stack);
			$handle = opendir($dirname);
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..' && is_readable($dirname . DIRECTORY_SEPARATOR . $file)) {
					if (is_dir($dirname . DIRECTORY_SEPARATOR . $file)) {
						$dirname_stack[] = $dirname . DIRECTORY_SEPARATOR . $file;
					}
					$size += filesize($dirname . DIRECTORY_SEPARATOR . $file);
				}
			}
			closedir($handle);
		} while (count($dirname_stack) > 0);

		return $size;
	}
}
?>