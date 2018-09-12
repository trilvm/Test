<?php

class SentMailAttachment extends Zend_Mime_Part {
	/**
	 * tao doi tuong att tu file
	 * @return SentMailAttachment
	*/	
	static function fileToObject($path_file){
		
	}
	
}

class SentMailInfo{
	var $_to_addr;
	var $_bcc;
	var $_cc;
	var $_content;
	var $_arr_atts; // mang cac doi tuong SentMailAttachment
	/**
	 * Tao cac thong tin thuong co
	 *
	 * @param unknown_type $arr_data to_addr,_bcc,_cc
	 */
	function addCommonInfo($arr_data){
		
	}
	
	function addContent(){
		
	}
	
	function addAttactment(){
		
	}
}
?>