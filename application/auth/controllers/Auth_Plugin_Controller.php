<?php
/**
 * @name Auth_Plugin_controller
 * @author  trunglv
 * @package  auth
 * @version 1.0
 */
require_once ('Zend/Controller/Plugin/Abstract.php');
require_once ('auth/models/LogModel.php');
require_once 'auth/models/ResourceUserModel.php';
/**
 * Controller plugin :
 * 		Thuc hien kiem tra cac request tu nguoi dung
 *		Kiem tra quyen cua nguoi dung truoc khi cho thuc hien yeu cau
 */

class Auth_Plugin_Controller extends Zend_Controller_Plugin_Abstract {

	 	
		private $_auth; //Bien authentication
    	
	 	private $_acl; //Bien access control  list

   	 	private $_noauth = array('module' => 'auth',
                             'controller' => 'login',
                            'action' => 'index');

 	  	private $_noacl = array('module' => 'auth',
                           'controller' => 'login',
                           'action' => 'erracl');
   
    
 	/**
 	 * Ham khoi tao 
 	 */  	
 	public function __construct($auth, $acl)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;
    }
	
    /**
     * Ham thuc hien kiem tra request cua nguoi dung
     */
	public function preDispatch($request)
	{
        $config = Zend_Registry::get('config');
        $params = $this->_request->getParams();
        if($config->sys_info->status == 3 && $request->module !="service"){
            $request->setModuleName("service");
			$request->setControllerName("index");
			$request->setActionName("notice");
			return;
        }
		if($request->controller == "capnhatbctksol")
		{
			include_once('auth/models/updatethongkexuly.php');
			updatethongkexuly::runscript();
			echo "Cập nhật báo cáo thông kê số lượng";
			exit;
			return;
		}
		if($request->controller == "zendyear")
		{
			include_once('auth/models/updatethongkexuly.php');
			updatethongkexuly::zend_year($request->action);
			echo "Cập nhật báo cáo thông kê số lượng";
			exit;
			return;
		}
		
		if($request->module=="service")
		{
			$request->setModuleName($module);
			$request->setControllerName($controller);
			$request->setActionName($action);
			return;
		}
                if($request->module=="chukyso")
		{
			$request->setModuleName($module);
			$request->setControllerName($controller);
			$request->setActionName($action);
			return;
		}
		if ($this->_auth->hasIdentity()) //Neu nguoi dung da dang nhap thanh cong 
		{
			$username = $this->_auth->getIdentity()->USERNAME;
			$menu = new Zend_Session_Namespace('menu');
			if(!isset($menu->menu)){
				
				$menuData=array(); 
				QLVBDHCommon::GetTree(&$menuData,"view_menus","ID_MNU","ID_MNU_PARENT",1,1);
				for($i=0;$i<count($menuData);$i++){
					if($menuData[$i]['URL']==""){
						if(ResourceUserModel::isAcionAlowed($username,$menuData[$i]['ACTID'])==false){
							$menuData[$i]['ACTID'] = 0;
						}
					}else{
						$actid = explode("/",$menuData[$i]['URL']);
						//$menuData[$i]['URL'] = $actid[1].".".$actid[2].".".$actid[3];
						if($actid[1]!="" && $actid[2]!="" && $actid[3]!=""){
							
							$actid = ResourceUserModel::getActionByUrl($actid[1],$actid[2],$actid[3]);
							//$menuData[$i]['URL'] = $actid; 
							if(ResourceUserModel::isAcionAlowed($username,$actid[0])==false){
								$menuData[$i]['ACTID'] = 0;
							}else{
								$menuData[$i]['ACTID'] = $actid;
							}
						}
					}
				}
				$menuData = array_values($menuData);
				$menu->menu = QLVBDHCommon::buildMenuUL_LI(&$menuData);
			}
			$freedate = new Zend_Session_Namespace('freedate');
			if(!isset($freedate->free)){
				$freedate->free = QLVBDHCommon::getFreeDate();
			}
			//return;
		 	$controller = $request->controller; 
    		$action = $request->action;
    		$module = $request->module;
		}
		//Kiem tra quyen 
		//Lay cac action tuong ung url
		$actions = ResourceUserModel::getActionByUrl($module,$controller,$action);
		//Kiem tra user co duoc phep truy cap vap action nay khon
		$ln = false;
		foreach($actions as $acit){
		 	if(ResourceUserModel::isAcionAlowed($username,$acit)== true)
		 		$ln = true; 	
		 }
		 
		if($ln == false) //Khong co quyen truy cap vao action
		{
		 	  
			if(!$this->_auth->hasIdentity()) // neu chua dang nhap, chuyen den trang dang nhap
            {
		 			$module = $this->_noauth['module'];
                	$controller = $this->_noauth['controller'];
                	$action = $this->_noauth['action'];
            }
            else // Neu da dang nhap chuyen den trang thong bao loi
            {
                $module = $this->_noacl['module'];
                $controller = $this->_noacl['controller'];
                $action = $this->_noacl['action']; 
                      	
             }
		 }
		 
		 
        //Chuyen yeu cau den trang tuong ung
        //$action = 'index';
		$request->setModuleName($module);
		$request->setControllerName($controller);
		$request->setActionName($action);
		LogModel::Logging($module,$controller,$action,"");
	}
}