<?php

/**
 * linhvucmotcua
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class linhvucmotcuaModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'MOTCUA_LINHVUC';
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll(){
		return  $this->getDefaultAdapter()->fetchAll("select * from ".$this->_name);
	}
	public function SelectAllByUID($uid){
		//echo "select lv.* from ".$this->_name." lv inner join MOTCUA_LINHVUC_QUYEN lvq on lv.ID_LV_MC = lvq.ID_LV_MC WHERE lvq.ID_U=$idu";exit;
		return  $this->getDefaultAdapter()->fetchAll("select lv.* from ".$this->_name." lv inner join MOTCUA_LINHVUC_QUYEN lvq on lv.ID_LV_MC = lvq.ID_LV_MC WHERE lvq.ID_U=$uid");
	}
	/**
	 * Đếm số bản ghi có trong table
	 */
	public function Count(){
		$r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
		return $r["C"];
	}
	/**
	 * Chuyển dữ liệu tới combobox
	 */
	static function ToCombo($data,$sel){
		
		$html="";		
		foreach($data as $row){
			$html .= "<option value='".$row["ID_LV_MC"]."' ".($row["ID_LV_MC"]==$sel?"selected":"").">".$row["NAME"]."</option>";
		}
		return $html;
	}

	

    static function ToComboloaihoso($id_lv,$sel){
		$html="";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select * from `motcua_loai_hoso` where ID_LV_MC = $id_lv ";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach($re as $row){
			$html .= "<option value='".$row["ID_LOAIHOSO"]."' ".($row["ID_LOAIHOSO"]==$sel?"selected":"").">".$row["TENLOAI"]."</option>";
		}
		return $html;
	}
	function GetUserById($idlv){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			MCLV.SEL,U.`ID_U`,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME) AS NAME,u.ORDERS
			FROM
				`QTHT_USERS` U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
				INNER JOIN (
				     SELECT
				           *, 1 AS SEL
				     FROM
				         `MOTCUA_LINHVUC_QUYEN`
				     WHERE
				          ID_LV_MC=?
				     ) MCLV ON MCLV.`ID_U` = U.`ID_U`
			ORDER BY ORDERS
		",array($idlv));
		return $r->fetchAll();
	}
	function GetAllUser($idlv){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			MCLV.SEL,U.`ID_U`,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME) AS NAME,u.ORDERS
			FROM
				`QTHT_USERS` U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL
				     FROM
				         `MOTCUA_LINHVUC_QUYEN`
				     WHERE
				          ID_LV_MC=?
				     ) MCLV ON MCLV.`ID_U` = U.`ID_U`
			ORDER BY ORDERS
		",array($idlv));
		return $r->fetchAll();
	}
}
