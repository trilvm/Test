<?php

/**
 * fk_vbden_hscvsModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class fk_vbden_hscvsModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'vbd_fk_vbden_hscvs_2013';
	function __construct($year){
		if(isset($year))
			$this->_name ='vbd_fk_vbden_hscvs_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
}
