<?php
/**
 * @name QLVBDH_ACL
 * Dieu khien quan quyen den cac chuc nang cua he thong
 * @author trunglv
 * @package auth
 * @version 1.0 
 */
require_once ('Zend/Acl.php');
require_once ('qtht/models/ModulesModel.php');
require_once ('qtht/models/fk_users_modulesModel.php');
require_once 'auth/models/ResourceUserModel.php';
require_once ('qtht/models/UsersModel.php');
require_once 'auth/models/ResourceUserModel.php';
class QLVBDH_ACL extends Zend_Acl {

	
	public function __construct(Zend_Auth $auth)
    {
    	if ($auth->hasIdentity()) {
			$id_user = $auth->getIdentity()->ID_U; //lay id user
		 //lay username
			$userTable = new UsersModel();
    		$where = $id_user;
    		$username = $userTable->find($where)->current()->USERNAME;
			$this->addRole(new Zend_Acl_Role($username));
		//	
    		$arr_act = ResourceUserModel::getActionByIdUser($id_user);
			$this->deny($username);
    		foreach($arr_act as $it){
				$this->add(new Zend_Acl_Resource($it->ID_ACT));
				$this->allow($username,$it->ID_ACT);
			}
    	}	     
    }
}
?>
