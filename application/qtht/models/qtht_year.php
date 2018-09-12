<?php

/**
 * qtht_year
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class qtht_year extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'qtht_year';
	public function ToCombo($name,$action){
		$result = "<select name='$name' id='$name' $action>";
		$data = $this->fetchAll(null,"YEAR");
		foreach($data as $item){
			if($item->YEAR == QLVBDHCommon::getYear()){
				$result .= "<option value=".$item->YEAR." selected>".$item->YEAR."</option>";
			}else{
				$result .= "<option value=".$item->YEAR.">".$item->YEAR."</option>";
			}
		}
		$result .= "</select>";
		return $result;
	}
}
