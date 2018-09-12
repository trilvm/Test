<?php

require_once ('Zend/Controller/Action.php');
require_once 'auth/models/ResourceUserModel.php';
require_once 'auth/models/LogModel.php';

class Auth_loginController extends Zend_Controller_Action {


	protected function _getAuthAdapter($formData)
	{
		global $config;
		global $db;
		global $auth;

		$authAdapter = new Zend_Auth_Adapter_DbTable($db);
		$authAdapter->setTableName('qtht_users')->setIdentityColumn('USERNAME')
		->setCredentialColumn('PASSWORD');
			
		// get "salt" for better security      |#3
		// $config = Zend_Registry::get('config');     |
		/// $salt = $config->auth->salt;      |
		/// $password = sha1($salt.$formData['password']);   |
			
		$authAdapter->setIdentity($formData['username']);
		if($formData['nomd5']=="yes"){
			$authAdapter->setCredential($formData['password']);
		}else{
			$authAdapter->setCredential(md5($formData['password']));
		}
		//echo $formData['username'].$formData['password'];
		return $authAdapter;
	}

	function init()
	{

	}

	function indexAction()
	{    global $db;
	$formdata = $this->_request->getParams();
	//var_dump(ResourceUserModel::getActionByIdUser(1));
	//var_dump(ResourceUserModel::getActionByUrl('qtht','demo'));
	//var_dump(ResourceUserModel::getAllActionID());

	//Lay authentication va acl
	$_auth = Zend_Registry::get('auth');
	$_acl = Zend_Registry::get('acl');
	$config = new Zend_Config_Ini(APPLICATION.'/config.ini','ldap',true);
	$basedn = explode(",",$config->ldap->username);
	$basedn =str_replace("$basedn[0],","",$config->ldap->username);

	//var_dump($basedn );exit;
	//$basedn = strstr($config->ldap->username,"dc");
	$basedn =str_replace(".","=",$basedn);

	if((int)$config->ldap->enable==1)
	{
		if($formdata["username"]!=null & $formdata["password"]!=null)
		{
			$options = array(
               'host' => $config->ldap->host,
               'port' => (int)$config->ldap->port,
               'username' => str_replace(".","=",$config->ldap->username),
               'password' => $config->ldap->password,
			);
			$ldap = new Zend_Ldap($options);
			$ldap->bind();
			//get entry cn
			$sr=ldap_search($ldap->getResource(),$basedn,"cn=*");
			$info=ldap_get_entries($ldap->getResource(),$sr);

			//get entry uid
			$sruid=ldap_search($ldap->getResource(),$basedn,"uid=*");
			$infouid=ldap_get_entries($ldap->getResource(),$sruid);
			
			////get user
			$user=null;
			$useruid=null;
			for ($i=0; $i<$info["count"]; $i++)
			{
				if($info[$i]["cn"][0]==$formdata["username"])
				{
					$user=$info[$i];break;
				}
			}
			//var_dump($user["cn"][0]); exit;
			for ($i=0; $i<$infouid["count"]; $i++)
			{
				if($infouid[$i]["uid"][0]==$formdata["username"])
				{
					$useruid=$infouid[$i];break;
				}
			}
            
			if($user !=null)
			{  
				$this->nguoidung = new ResourceUserModel();
				$userdb= ResourceUserModel::getuser($formdata["username"]);
				//$userkt=$formdata["username"];
				if((int)$config->ldap->user_qd==1){
				//var_dump("cn=".$formdata["username"]."".",".$basedn);exit;
				$optionskiemtra = array(
                                 'host' => $config->ldap->host,
                                 'port' => (int)$config->ldap->port,
                                 'username' =>$user["dn"],
                                 'password' => $formdata["password"],
				);
				try{						
					$ldapkt = new Zend_Ldap($optionskiemtra);
					$ldapkt->bind();					
					if($userdb["USERNAME"]==$user["cn"][0])
					{
						$where="USERNAME='".$user["cn"][0]."'";
						//	var_dump($where);exit;
						$db->update("qtht_users",
						      array(
                                    "PASSWORD"=>md5($formdata["password"])     
						),$where);
                     
					}else
					{		
							$name_Ho="";
							$name_Ten="";
							$name=explode(" ",$user["sn"][0]);
			  		if(count($name)>2)
					  { for($k=0;$k<count($name)-1;$k++)
					  {
					  	$name_Ho=$name_Ho." ".$name[$k];
					  }
					  $name_Ten=$name[count($name)-1];

					  }else
					  {
					  	$name_Ho=$name[0];
					  	$name_Ten=$name[1];
					  }
					  $db->insert("qtht_employees",
					  array(
                                             "ID_DEP" => 1,
                                             "FIRSTNAME"=>$name_Ho,
                                             "LASTNAME"=>$name_Ten 
					  ));
					  $id = $db->lastInsertId(qtht_employees);
					  $db->insert("qtht_users",
					  array("ID_EMP"=>$id,
                                        "USERNAME"=>$user["cn"][0],
                                        "PASSWORD"=>md5($formdata["password"])       
					  ));
					  $this->view->loginerror =  "<span class=error>Liên hệ admin để kích hoạt tài khoản</span>";
					  return;
					
					}
						
				}catch (Zend_Ldap_Exception  $e) {
					$this->view->loginerror =  "<span class=error>Tên đăng nhập và mật mã không đúng </span>";
					return;
				}
					
				///
			}
			}
		 
			////uid
			if($useruid!=null)
			{    
				$this->nguoidung = new ResourceUserModel();
				$userdb= ResourceUserModel::getuser($formdata["username"]);
				//var_dump($useruid["dn"]);exit;
				if((int)$config->ldap->user_qd==0){
				// var_dump("uid=".$formdata["username"]."".",".$basedn);exit;	
				$optionskiemtra = array(
                                 'host' => $config->ldap->host,
                                 'port' => (int)$config->ldap->port,
                                 'username' =>$useruid["dn"],
                                 'password' => $formdata["password"],
				);

				try{
						
					$ldapkt = new Zend_Ldap($optionskiemtra);
					$ldapkt->bind();
				
					if($userdb["USERNAME"]==$useruid["uid"][0])
			          {
			         	$where="USERNAME='".$useruid["uid"][0]."'";
						//	var_dump($where);exit;
						$db->update("qtht_users",
						array(
                                  "PASSWORD"=>md5($formdata["password"])     
						),$where);
							
			  }else
			  {
			   			$name_Ho="";
			 			$name_Ten="";
			 			$name=explode(" ",$useruid["sn"][0]);
			 			if(count($name)>2)
					  { for($k=0;$k<count($name)-1;$k++)
					  {
					  	$name_Ho=$name_Ho." ".$name[$k];
					  }
					  $name_Ten=$name[count($name)-1];

					  }else
					  {
					  	$name_Ho=$name[0];
					  	$name_Ten=$name[1];
					  }
					  $db->insert("qtht_employees",
					  array(
                                             "ID_DEP" => 1,
                                             "FIRSTNAME"=>$name_Ho,
                                             "LASTNAME"=>$name_Ten 
					  ));
					  $id = $db->lastInsertId(qtht_employees);
					  $db->insert("qtht_users",
					  array("ID_EMP"=>$id,
                                       "USERNAME"=>$useruid["uid"][0],
                                       "PASSWORD"=>md5($formdata["password"])       
					  ));
					  $this->view->loginerror =  "<span class=error>Liên hệ admin để kích hoạt tài khoản</span>";
					  return;			  	
			  }
				}catch (Zend_Ldap_Exception  $e) {
					$this->view->loginerror =  "<span class=error>Tên đăng nhập và mật mã không đúng </span>";
					return;
				}

			}		
			}
				
			ldap_free_result($sr);
			$ldap->disconnect();
		}

	}
	//echo $this->_request->getParam("code");
	if($this->_request->isPost() || $this->_request->getParam("code")=="mini")
	{		//Lay du lieu du nguoi dung
		if($formdata["username"]!=null & $formdata["password"]!=null)
		{
			$resource4User=new ResourceUserModel();
			if($resource4User->getActionForUser($formdata["username"],$this->_request->getParam("nomd5")=="yes"?$formdata["password"]:md5($formdata["password"])))
			{
				$authAdapter = $this->_getAuthAdapter($formdata);
				//Kiem tra dang nhap
				$result = $_auth->authenticate($authAdapter);
				if($result->isValid()) //dang nhap thanh cong
				{
					$year = new Zend_Session_Namespace('year');
					$year->year = $formdata['currentyear'];
					//Tao session va luu du lieu xuong session
					$datasession = $authAdapter->getResultRowObject(array('ID_U','USERNAME','PASSWORD'));
					//lay nhan vien tuong ung voi username
					//var_dump($datasession->ID_U);
					$userinfo = $resource4User->GetUserInfo($datasession->ID_U);
					//var_dump($userinfo);exit;
					$datasession->FULLNAME = $userinfo['FULLNAME'];
					$datasession->ID_DEP = $userinfo['ID_DEP'];
					$datasession->ID_CQ = $userinfo['ID_CQ'];
					$datasession->ID_G = $userinfo['ID_G'];
					$datasession->ISLEADER = $userinfo['ISLEADER'];
					$datasession->DEPLEADER = $userinfo['DEPLEADER'];
					$datasession->ID_U_PARENT = $datasession->ID_U;
					$datasession->UQ = $userinfo['UQ'];
					$_auth->getStorage()->write($datasession);
					if($this->_request->getParam("code")=="mini"){
						if($this->_request->getParam("url")!=""){
							$this->_redirect($this->_request->getParam("url"));
						}else{
							$this->_redirect('/auth/user/alarm');
						}
					}else{
						$this->_redirect('/default');
					}
				}
				else
				{//var_dump($this->_request->getParams());
					$this->view->loginerror =  "<span class=error>Tên đăng nhập và mật mã không đúng</span>";
					LogModel::Logging('auth','login','index',"1");
				}
			}
			else
			{
				$this->view->loginerror =  "<span class=error>Tên đăng nhập và mật mã không đúng</span>";
				LogModel::Logging('auth','login','index',"1");
				//Zend_Registry::set('auth',null);
			}
		}
		else
		{
			$this->view->loginerror =  "<span class=error>Nhập tên và mật khẩu</span>";
		}
	}else{
		$param = $this->_request->getParams();
		if($param['code']=="mini"){
			$this->_helper->layout->disableLayout();
		}
		$this->view->code = $param['code'];
	}
	}
	function registerAction()
	{

	}

	function erraclAction()
	{
		$this->_helper->layout->disableLayout();

	}
}

?>
