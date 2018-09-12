<?php

require_once ('Zend/Db/Table/Abstract.php');

class gen_tempModel extends Zend_Db_Table_Abstract {
	var $_name = 'gen_temp';
	function getIdTemp(){
		return $this->insert(array());
	}
}

?>
