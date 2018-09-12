<?php

/**
 * ActivityPoolModel
 *  
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';

class ActivityPoolModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'wf_activitypools';
	var $_id_c=0;
	var $_search = "";
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_id_c > 0){
			$arrwhere[] = $this->_id_c;
			$strwhere .= " and ID_C = ?";
		}
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
	static function ToCombo($data,$sel){
		$html="";
		foreach($data as $row){
			$html .= "<option value='".$row["IP_AP"]."' ".($row["ID_AP"]==$sel?"selected":"").">".$row["NAME"]."</option>";
		}
		return $html;
	}
	/**
	 * Chuyển dữ liệu tới combobox
	 */
	public function Count(){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_id_c > 0){
			$arrwhere[] = $this->_id_c;
			$strwhere .= " and ID_C = ?";
		}
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and NAME like ?";
		}
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				$this->_name
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
	 * Lấy danh sách các dữ liệu bị checked
	*/
	public function SelectCheckedByClass(){
		//Build phần where
		$arrwhere = array();
		$arrwhere[] = $this->_id_c;
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				ap.*, c.NAME as CLASS_NAME, (c.id_c = ?) as SEL
			FROM
				$this->_name ap
				left join wf_classes c on ap.ID_C = c.ID_C
		",$arrwhere);
		return $result->fetchAll();
	}
}
