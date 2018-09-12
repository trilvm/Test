<?php

/**
 * carlendarpersonalModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class calendarcorporationModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'lct_calendar_corporation_2013';
	public $_fromdate = "";
	public $_todate = "";
	function __construct($year){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='lct_calendar_corporation_'.$year;
    		}
    	}
		$arr = array();
		parent::__construct($arr);
	}
}
