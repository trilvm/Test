<?php

/**
 * ClassModel
 *  
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class ClassModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'wf_classes';
	var $_search = "";
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and NAME like ?";
		}
		
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order>0){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				*
			FROM
				$this->_name
			WHERE
				$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	/**
	 * Đếm số bản ghi có trong table
	 */
	public function Count(){
		$r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
		return $r["C"];
	}
	/**
	 * 
	 */
	static function ToCombo($data,$sel){
		$html="";
		$html .= "<option value='0'>".MSG11004001."</option>";
		foreach($data as $row){
			$html .= "<option value='".$row["ID_C"]."' ".($row["ID_C"]==$sel?"selected":"").">".$row["NAME"]."</option>";
		}
		return $html;
	}
}
