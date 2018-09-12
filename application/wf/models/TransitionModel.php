<?php

/**
 * TransitionModel
 *  
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';

class TransitionModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'wf_transitions';
	
	public $_id_p=0;
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll(){
		return  $this->getDefaultAdapter()->fetchAll("
		select tr.* from 
		".$this->_name." tr
		where 
		tr.ID_P=? order by tr.ISFIRST DESC,tr.ORDERS,tr.ID_A_BEGIN,tr.ID_A_END,tr.NAME",array($this->_id_p));
	}
	static function ToComboEnd($data,$sel){
		$html="";
		foreach($data as $row){
			$html .= "<option value='".$row["ID_T"]."' ".($row["ID_T"]==$sel?"selected":"").">".$row["NAME"]."</option>";
		}
		return $html;
	}

	static function deletetrash(){
		global $db;
		$db->query("delete from hscv_loaihosocongviec where MASOQUYTRINH NOT IN (select ALIAS from wf_processes)");
		$db->query("delete from wf_transitions where ID_P not in (select ID_P from wf_processes)");
		$db->query("delete from wf_transitions where ID_P not in (select ID_P from wf_processes)");
		$db->query("delete from wf_activities where ID_P not in (select ID_P from wf_processes)");
		$db->query("delete from wf_activityaccesses where ID_A not in (select ID_A from wf_activities)");
	}

	static function gettransition($idt){
		global $db;
		$sql = "
			SELECT
				t.*
				, ab.NAME as ABNAME
				, ae.NAME as AENAME
				, tp.NAME as TPNAME
				, p.ID_C
			FROM
				wf_transitions t
				INNER JOIN wf_activities ab ON t.ID_A_BEGIN = ab.ID_A
				INNER JOIN wf_activities ae ON t.ID_A_END = ae.ID_A
				INNER JOIN wf_transitionpools tp ON t.ID_TP = tp.ID_TP
				INNER JOIN wf_processes p ON t.ID_P = p.ID_P
			WHERE
				t.ID_T = ?
		";
		return $db->query($sql,$idt)->fetch();
	}
}
