<?php

require_once('qtht/models/fk_users_actionsModel.php');
require_once 'qtht/models/ModulesModel.php';
require_once 'qtht/models/ActionsModel.php';
require_once 'qtht/models/UsersModel.php';

class ResourceUserModel {
	
	/**
	 * return array id action
	 */
	static function getActionByIdUser($id_user)
	{
		 $fk_user_actionsModel = new fk_users_actionsModel();
		 $where = "ID_U=?"; 
		 $select = $fk_user_actionsModel->select()
		 ->from($fk_user_actionsModel->_name,'ID_ACT')
		 ->where($where,$id_user);
		 return $fk_user_actionsModel->fetchAll($select);
	}
	/**
	 * return array(module,controller,action)
	 */
	
	/**
	 * return array action id 
	 */
	static function getActionByUrl($module,$controller,$action){
		global $db;
		
		$arrAction = array();
		$sql = "SELECT * FROM QTHT_MODULES WHERE URL = ?";
		$strurl = '/'.$module.'/'.$controller.'/';
		$r = $db->query($sql,$strurl);
		$mos = $r->fetchAll();
		foreach ($mos as $it){
			$sql = "SELECT * FROM QTHT_ACTIONS WHERE ID_MOD=? AND NAME=?";
			$r = $db->query($sql,array($it['ID_MOD'],$action));
			$ac = $r->fetchAll();
			foreach($ac as $itac){
				$arrAction[] = $itac['ID_ACT'];
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
		global $db;
		$r = $db->query('select ISPUBLIC from qtht_actions where ID_ACT=?',$id_ACT);
		$re = $r->fetch();
		if($re['ISPUBLIC'] == 1)
			return true;
		return false;
		
	}
	public static function isAcionAlowed($username,$actionid){
		global $db;
		$ln = false;
		$_acl = Zend_Registry::get('acl');
		$resource = $actionid;
		if (!$_acl->has($resource)) {  
				$resource = null;
				//return false;
   		}
   		if($actionid=="")return false;
		if(ResourceUserModel::ActionIsPublic($actionid))
		 $ln = true;
		$r = $db->query('
			select
				*
			from
				QTHT_USERS u
				inner join fk_users_groups ug on ug.ID_U = u.ID_U
				inner join fk_groups_actions ga on ga.ID_G = ug.ID_G
			where
				u.USERNAME = ? and ga.ID_ACT = ?
		',array($username,$actionid));
		
		if($r->rowCount()>0)return true;

		$r = $db->query('
			select
				*
			from
				QTHT_USERS u
				inner join fk_users_actions ug on ug.ID_U = u.ID_U
			where
				u.USERNAME = ? and ug.ID_ACT = ?
		',array($username,$actionid));
		
		if($r->rowCount()>0)return true;

		//echo $username;
		try{
		if ($_acl->isAllowed($username, $resource))
		 		$ln = true;
		}catch(Exception $ex){
			return false;
		}
		return $ln;
		return false;
		 	
	}
	
	public static function isAcionAlowedByIDU($ID_U,$actionid){
		$username = UsersModel::getUsernameById($ID_U);
		return ResourceUserModel::isAcionAlowed($username,$actionid);
	}

	public function getActionForUser($username,$password)
	{
		$usermodel=new UsersModel();
		try 
		{
			if($username!=null & $password!=null)
			{
				$r = $usermodel->getDefaultAdapter()->query('
					select
						ACTIVE
					from
						QTHT_USERS u				
					where
						u.USERNAME = ?
						AND u.PASSWORD = ?
				',array($username,$password));
				$result=$r->fetchAll();
				if(count($result)>0)
				{
					if($result[0]['ACTIVE']==1) return true;
					else return false;
				}				
				else return false;
			}
		}
		catch (Exception $e2){return false;}
		return false;
	}
	public function GetUserInfo($idu){
		global $db;
		$sql = "
			SELECT u.USERNAME,u.PASSWORD, concat(FIRSTNAME,' ',LASTNAME) as FULLNAME,dep.ID_DEP,fkug.ID_G,u.ISLEADER as ISLEADER,dep.ISLEADER as DEPLEADER , dep.ID_CQ FROM
			QTHT_USERS u
			INNER JOIN FK_USERS_GROUPS fkug on fkug.ID_U = u.ID_U
			INNER JOIN QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
			INNER JOIN QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			WHERE u.ID_U = ?
		";
		
		$r = $db->query($sql,$idu);
		$row = $r->fetchAll();
		$result = Array();
		$ID_U = $idu;
		$FULLNAME = "";
		foreach($row as $item){
			$result["ID_U"] = $item['ID_U'];
			$result["ID_G"] .= $item['ID_G'].",";
			$result["ID_CQ"] = $item['ID_CQ'];
			$result["ID_DEP"] = $item['ID_DEP'];
			$result["FULLNAME"] = $item['FULLNAME'];
			$result["ISLEADER"] = $item['ISLEADER'];
			$result["DEPLEADER"] = $item['DEPLEADER'];
			$result["USERNAME"] = $item['USERNAME'];
			$result["PASSWORD"] = $item['PASSWORD'];
			$FULLNAME = $item['FULLNAME'];
			
		}
		$result["ID_G"] = substr($result["ID_G"], 0, strlen($result["ID_G"])-1);
		$sql = "
			SELECT concat(FIRSTNAME,' ',LASTNAME) as FULLNAME,u.ID_U FROM
			QTHT_USERS u
			inner join QTHT_MULTIACCOUNT uuq on uuq.ID_U = u.ID_U
			INNER JOIN QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
			WHERE uuq.ID_U_UQ = ?
			ORDER BY FULLNAME
		";
		$r = $db->query($sql,$idu);
		$row = $r->fetchAll();
		$row[] = array("FULLNAME"=>$FULLNAME,"ID_U"=>$ID_U);
		$result["UQ"] = $row;
		return $result;
	}
	static function getuser($username){
  	   
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select * from `qtht_users` where USERNAME=?
		";
		
		try{
			$qr = $dbAdapter->query($sql,array($username));
			$row=$qr->fetch();
			
			return $row;
		}catch(Exception $ex){
			return array();
		}
	}
	static function getuserdbo(){
  	   
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select * from `qtht_users` 
		";
		
		try{
			$qr = $dbAdapter->query($sql);
			$row=$qr->fetchAll();
			
			return $row;
		}catch(Exception $ex){
			return array();
		}
	}	
}

?>
