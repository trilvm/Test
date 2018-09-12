<?php
/**
 * @author trunglv
 * @version 1.0
 * Tao lop ke thua tu lop Zend_File_Tranfer_Adapter_HTTP de lay mine
 */
require_once ('Zend/File/Transfer/Adapter/HTTP.php');

class FileTransfer extends Zend_File_Transfer_Adapter_HTTP {
	function getMine($file_upload){
		
		return  $this->_files[$file_upload]["type"];
	}
}

?>
