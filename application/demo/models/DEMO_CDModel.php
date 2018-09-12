<?php

/**
 * DEMO_CDModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class DEMO_CDModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'demo_cd';
	
	public function SelectAllItem($process_id,$activity_id,$user_id){
		$r = $this->getDefaultAdapter()->query("
			select * from
			DEMO_CD cd
			inner join `wf_processitems_2009` pii on cd.`ID_PI`=pii.`ID_PI`
			WHERE
				 pii.ID_A = ? and
			     pii.`ID_U` = ? and
			     pii.ID_P = ?
		",array($activity_id,$user_id,$process_id));
		return $r->fetchAll();
	}
}
