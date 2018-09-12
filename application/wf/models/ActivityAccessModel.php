<?php

/**
 * ActivityAccessModel
 *  
 * @author nguyennd
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';

class ActivityAccessModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'wf_activityaccesses';
	var $_id_a = 0;
	/**
	 * Lấy danh sách Group
	 *
	 * @return dataset
	 */
	function GetAllGroup(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			AC.SEL,G.`ID_G`,G.NAME
			FROM
				`QTHT_GROUPS` G
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL
				     FROM
				         `WF_ACTIVITYACCESSES`
				     WHERE
				          ID_A=?
				     ) AC ON AC.`ID_G` = G.`ID_G`
		",array($this->_id_a));
		return $r->fetchAll();
	}
	function GetAllDep(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			AC.SEL,DEP.`ID_DEP`,DEP.NAME
			FROM
				`QTHT_DEPARTMENTS` DEP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL
				     FROM
				         `WF_ACTIVITYACCESSES`
				     WHERE
				          ID_A=?
				     ) AC ON AC.`ID_DEP` = DEP.`ID_DEP`
                                     WHERE DEP.ACTIVE=1
		",array($this->_id_a));
		return $r->fetchAll();
	}
	/**
	 * Lấy danh sách user
	 *
	 * @return dataset
	 */
	function GetAllUser(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			AC.SEL,U.`ID_U`,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME,' (',U.USERNAME,')') AS NAME,KYHIEU_PB
			FROM
				`QTHT_USERS` U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
				INNER JOIN QTHT_DEPARTMENTS DEP ON EMP.ID_DEP = DEP.ID_DEP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL
				     FROM
				         `WF_ACTIVITYACCESSES`
				     WHERE
				          ID_A=?
				     ) AC ON AC.`ID_U` = U.`ID_U`
			ORDER BY DEP.ID_DEP, NAME
		",array($this->_id_a));
		return $r->fetchAll();
	}
}
