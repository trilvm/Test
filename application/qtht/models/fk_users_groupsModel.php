<?php

/**
 * fk_users_groupsModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class fk_users_groupsModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'fk_users_groups';
	public function SelectAllByIDU($idu){
		$r = $this->getDefaultAdapter()->query("
			SELECT
			      g.`NAME`,
			      g.ID_G,
			      (g.ID_U=u.ID_U) as SEL
			FROM
				(
					SELECT
						ug.ID_U,g.NAME,g.ID_G
					FROM
						QTHT_GROUPS g
						left join (
							SELECT 
								ID_U,ID_G 
							from
								`fk_users_groups`
							where ID_U=?) ug on g.ID_G = ug.ID_G
				) g
				left join (
					SELECT
						ID_U
					FROM
						`qtht_users`
					WHERE
						ID_U=?
			) u on g.ID_U = u.ID_U
		",array($idu,$idu));
		return $r->fetchAll();	
	}

	function insertOneGroupMultiEmp($idg , $arr_IdEmp){
		foreach($arr_IdEmp as $ele){
			
			$this->insertOne($ele,$idg);
		}
	
	}
	
	function insertOne($idu,$idg){
		try{
			$data = array("ID_U" => $idu , "ID_G"=>$idg);
			
			$this->insert($data);
		}catch (Exception $ex){
			echo $ex->__toString();
		}
	}

	function deleteByIdG($idg){
		try{
			$where = "ID_G=".$idg;
			$this->delete($where);
		}catch(Exception $ex){
			
		}
		 
	}


}
