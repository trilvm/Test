<?php

/**
 * loaihosocongviecModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class loaihosocongviecModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'hscv_loaihosocongviec';
	function SelectAll(){
		$sql = "
			SELECT * FROM HSCV_LOAIHOSOCONGVIEC
		";
		$r = $this->getDefaultAdapter()->query($sql,array());
		return $r->fetchAll();
	}
	static function ToCombo($data,$sel){
		$html="";
		foreach($data as $row){
			$html .= "<option value='".$row["ID_LOAIHSCV"]."' ".($row["ID_LOAIHSCV"]==$sel?"selected":"").">".$row["NAME"]."</option>";
		}
		return $html;
	} 
	static function getTenLoaiCVById($id){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT NAME FROM HSCV_LOAIHOSOCONGVIEC where ID_LOAIHSCV=?
		";
		$r = $dbAdapter->query($sql,array($id));
		$row =  $r->fetch();
		return $row["NAME"];
	}
}
