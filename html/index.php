<?php
date_default_timezone_set('Asia/Jakarta');
require_once("instance.php");

/**
 * My new Zend Framework project
 * 
 * @author  
 * @version 
 */
$rootPath = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR .  
                 $rootPath . '/application' . PATH_SEPARATOR .  
                 $rootPath . '/library' . PATH_SEPARATOR .  
                 $rootPath . '/html');
                 
define('APPLICATION')|| define ('APPLICATION',realpath($rootPath . '/' .APP_NAME));
define('LAYOUT_PATH',APPLICATION.'/layouts/scripts/');

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();
$config = new Zend_Config_Ini('../application/config.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);
$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);
$controller = Zend_Controller_Front::getInstance();
$controller->setControllerDirectory('../application/default/controllers');
$controller->addControllerDirectory('../application/vbden/controllers', 'vbden');
$controller->addControllerDirectory('../application/vbdi/controllers', 'vbdi');
$controller->addControllerDirectory('../application/motcua_ub/controllers', 'motcua');
$controller->addControllerDirectory('../application/congviec/controllers', 'congviec');
$controller->addControllerDirectory('../application/report/controllers', 'report');
$controller->addControllerDirectory('../application/static/controllers', 'static');
$controller->addControllerDirectory('../application/hscv/controllers', 'hscv');
$controller->addControllerDirectory('../application/wf/controllers', 'wf');
$controller->addControllerDirectory('../application/qtht/controllers', 'qtht');
$controller->addControllerDirectory('../application/demo/controllers', 'demo');
$controller->addControllerDirectory('../application/auth/controllers', 'auth');
$controller->addControllerDirectory('../application/nguoidungModel/controllers', 'nguoidungModel');
$controller->addControllerDirectory('../application/lichct/controllers', 'lichct');
$controller->addControllerDirectory('../application/traodoi/controllers', 'traodoi');
$controller->addControllerDirectory('../application/mailclient/controllers', 'mailclient');
$controller->addControllerDirectory('../application/service/controllers', 'service');
$controller->addControllerDirectory('../application/iso/controllers', 'iso');
$controller->addControllerDirectory('../application/lichcttext/controllers', 'lichcttext');
$controller->addControllerDirectory('../application/thongbao/controllers', 'thongbao');
$controller->addControllerDirectory('../application/vbmail/controllers', 'vbmail');
$controller->addControllerDirectory('../application/iso/controllers', 'iso');
$controller->addControllerDirectory('../application/taphscv/controllers', 'taphscv');
$controller->addControllerDirectory('../application/qlcuochop/controllers', 'qlcuochop');
$controller->addControllerDirectory('../application/vbden/controllers', 'chotiepnhan');
$controller->addControllerDirectory('../application/version/controllers', 'version');
$controller->addControllerDirectory('../application/vanbansaoy/controllers', 'vanbansaoy');
$controller->addControllerDirectory('../application/dongbolencong/controllers', 'dongbolencong');
$controller->addControllerDirectory('../application/giaoviec/controllers', 'giaoviec');
$controller->addControllerDirectory('../application/chukyso/controllers', 'chukyso');

require_once 'Zend/Layout.php'; 
$layout = Zend_Layout::startMvc($config->appearance);
$layout->getView()->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
require_once 'Common/button.php';
require_once 'Common/common.php';
require_once 'Common/ServicesCommon.php';
require_once 'Common/Convert.php';
require_once 'Common/ajax.php';

require_once 'wf/wfengine.php';
require_once 'auth/models/QLVBDH_ACL.php'; 
require_once 'default/controllers/ErrorController.php';
require_once 'fckeditor/fckeditor_php5.php' ;
require_once 'qtht/models/qtht_year.php';

$auth = Zend_Auth::getInstance();
Zend_Registry::set('auth', $auth);
$acl = new QLVBDH_ACL($auth);
Zend_Registry::set('acl', $acl);
$url = 'http://www.qlvbdh.vn/';
Zend_Registry::set('url', $url);
require_once 'auth/controllers/Auth_Plugin_Controller.php';
$controller->registerPlugin(new Auth_Plugin_Controller($auth,$acl));
$controller->throwExceptions(true); // should be turned on in development time 
try{
$controller->dispatch();
}catch (Exception  $ex){
	echo $ex->__toString();
}