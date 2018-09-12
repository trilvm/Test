<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');
require_once 'qtht/models/SoVanBanModel.php';
class vbdendraftModel extends Zend_Db_Table_Abstract {
	var $_name = 'vbd_vanbanden_draft_2013';

	function __construct($year){
		$year = QLVBDHCommon::getYear();
		if(isset($year))
			$this->_name ='vbd_vanbanden_draft_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
	
	function SelectAll($id_svb){
		global $db;
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$param = array();
		$where = " WHERE NGUOITAO = ? ";
		$param[] = $user->ID_U;
		if($id_svb > 0)
		{
			$where .= " AND ID_SVB = ?";
			$param[] = $id_svb;
		} else {
			$idSvb_arr = array();
			$svbs = SoVanBanModel::selectWithDep(0);
			if(count($svbs) > 0)
			{
				foreach($svbs as $svb)
				{
					$idSvb_arr[] = $svb['ID_SVB'];
				}
				$where .= " AND `ID_SVB` IN( ".implode(', ', $idSvb_arr).")" ; 
			}
		}
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("vbd_vanbanden_draft").$where." ORDER BY ID_VBD DESC ";
		$r = $db->query($sql,$param);
		return $r->fetchAll();
	}
	function SelectOne($id){
		global $db;
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("vbd_vanbanden_draft")." WHERE ID_VBD=?";
		$r = $db->query($sql,$id);
		return $r->fetch();
	}
	function DeleteOne($id){
		global $db;
		$sql = "DELETE FROM ".QLVBDHCommon::Table("vbd_vanbanden_draft")." WHERE ID_VBD=?";
		$db->query($sql,$id);
	}
}