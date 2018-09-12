<?php
require_once('qtht/models/fk_users_actionsModel.php');
require_once('qtht/models/fk_groups_actionsModel.php');
require_once 'qtht/models/ModulesModel.php';
require_once 'qtht/models/ActionsModel.php';

class ResourceGroupModel {
	
	/**
	 * return array id action
	 */
	static function getActionByIdGroup($id_g)
	{
		 $fk_groups_actionsModel = new fk_groups_actionsModel();
		 $where = "ID_G=?"; 
		 $select = $fk_groups_actionsModel->select()
		 ->from($fk_groups_actionsModel->_name,'ID_ACT')
		 ->where($where,$id_g);
		 return $fk_groups_actionsModel->fetchAll($select);
	}	
	/**
	 * return array action id 
	 */
	static function getActionByUrl($module,$controller,$action){
		$arrAction = array();
		$modules = new ModulesModel();
		$actions = new ActionsModel();
		$strurl = '/'.$module.'/'.$controller.'/';
		$select_modules = $modules->select()->where('URL=?',$strurl);
		$mos = $modules->fetchAll($select_modules);
		foreach ($mos as $it){
			$select_actions = $actions->select()->where('ID_MOD=?',$it->ID_MOD)->where('NAME=?',$action);
			$ac = $actions->fetchAll($select_actions);
			foreach($ac as $itac){
				array_push($arrAction,$itac->ID_ACT);
			}
		}
		return $arrAction;
	}
	static function getUrlByAction(){
		
	}
	static function getAllActionID(){
		$actions = new ActionsModel();
		$actions->getDefaultAdapter()->query('select ID_ACT from qtht_actions');
		return $actions->fetchAll();	
	}
	static function getActionPublic(){
		$actions = new ActionsModel();
		$actions->getDefaultAdapter()->query('select ID_ACT from qtht_actions');
		return $actions->fetchAll();	
	}
	static function ActionIsPublic($id_ACT){
		$actions = new ActionsModel();
		$actions->getDefaultAdapter()->query('select ISPUBLIC from qtht_actions where ID_ACT=?',$id_ACT);
		$re = $actions->fetchRow();
		return $re;
		
	}
	public static function isAcionAlowed($username,$actionid){		
		$ln = false;
		$_acl = Zend_Registry::get('acl');
		$resource = $actionid;
		if (!$_acl->has($resource)) {  
				$resource = null;	
   		}
		if(ResourceUserModel::ActionIsPublic($actionid))
		 		$ln = true;
		if ($_acl->isAllowed($username, $resource))
		 		$ln = true;
		return $ln; 	
	}
}

?>
