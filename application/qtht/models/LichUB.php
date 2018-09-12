<?php
require_once ('Zend/Db/Table/Abstract.php');
require_once ('Zend/Db/Table.php');
/**
 * ThongtinbiadoModel
 *  
 * @author longtv
 * @version 1.0
 */

class LichUB extends Zend_Db_Table_Abstract
{
    protected $_name = 'lct_calendar_ub_2013';
	var $_search = "";
   function __construct($year){
		if($year=="")$year=QLVBDHCommon::getYear();
		$this->_name = 'lct_calendar_ub_'.$year;
		$config = array();
		parent::__construct($config);
	}
   public function SelectAll(){
		//Thuc hien query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				*
			FROM
				$this->_name		
		");
		return $result->fetchAll();
	}
	
	/**
	 * Đếm số bản ghi có trong table
	 */
	public function Count(){
		$r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
		
		return $r["C"];
	}
	
}