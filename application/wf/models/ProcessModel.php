<?php

/**
 * ProcessModel
 *  
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class ProcessModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'wf_processes';
	/**
	 * Đếm số bản ghi có trong table
	 */
	public function Count(){
		$r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
		return $r["C"];
	}
}
