<?php

/**
 * ConfigController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/BussinessDateModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
require_once 'qtht/models/BackupConfigModel.php';

class Qtht_ConfigController extends Zend_Controller_Action {
	
	function init(){
		$this->view->title = 'Quản lý cấu hình';
		//$this->view->subtitle = 'Danh sách thông số';
	}
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ConfigController::indexAction() default action
		//Lay tham so he thong cu
		//$config = new Zend_Config_Ini('../application/config.ini', 'general');
		$config = new Zend_Config_Ini('../application/config.ini','general',true);
		$config_ldap = new Zend_Config_Ini('../application/config.ini','ldap',true);
		$config_crypt = new Zend_Config_Ini('../application/config.ini','crypt',true);
		if($this->_request->isPost()){
			//nhan du lieu du client
			$param = $this->_request->getParams();
			//luu config
			//$config->
			$config->db->params->host = $param['db_host'];
			$config->db->params->username = $param['db_user'];
			$config->db->params->password = $param['db_password'];
			
		}
		
		//$config->S
		//tham so he thong
		$this->view->data_ldap=$config_ldap;
		$this->view->data_crypt=$config_crypt;
		$this->view->data = $config;
		$this->view->config_mail = new Zend_Config_Ini('../application/config.ini', 'mail_client');

		try{
            $this->view->config_chat = new Zend_Config_Ini('../application/config.ini', 'chat');
		}catch(Exception $ex){

        }

		QLVBDHButton::EnableSave("/qtht/DanhMucMaSo/save");
		QLVBDHButton::AddButton("Trang chủ","","BackButton","BackButtonClick();");
		
		/**
		** Nhan va gui van ban qua mail
		*/
		$config_vbmail = new Zend_Config_Ini('../application/config.ini', 'vbmail');
		$email_account;
		$email_account->_email_addr = $config_vbmail->vbmail->email;
		$email_account->_username = $config_vbmail->vbmail->username;
		$email_account->_password = $config_vbmail->vbmail->password;
		$email_account->_incoming_hostname = $config_vbmail->vbmail->incominghost;
		$email_account->_outgoing_hostname = $config_vbmail->vbmail->outgoinghost;
		$email_account->_incoming_port = $config_vbmail->vbmail->incomingport;
		$email_account->_outgoing_port = $config_vbmail->vbmail->outgoingport;
		$email_account->_outgoing_protocol = $config_vbmail->vbmail->outgoingprotocol; 
		$email_account->_incoming_protocol = $config_vbmail->vbmail->incomingprotocol; 
		$email_account->_ssl_in = $config_vbmail->vbmail->is_in_ssl;
		$email_account->_ssl_out = $config_vbmail->vbmail->is_out_ssl;
		$this->view->email_account = $email_account;
                
                $bkcon = $this->dataBackupConfig();
                
                
                $this->view->TYPE = $bkcon[0]['TYPE'];
                $this->view->TIME = $bkcon[0]['TIME'];
                $this->view->TYPE_VALUE = $bkcon[0]['TYPE_VALUE'];
                $this->view->FOLDER = $bkcon[0]['FOLDER'];
		
	}
        
        function dataBackupConfig(){
            $db = new Qtht_BackupConfigModel();
            $a=  $db->fetchAll(NULL, 'CREATE_DATE desc');
            return $a->toArray();
        }
	public function viewbussdateAction(){
		$this->_helper->layout->disableLayout();
		$param = $this->getRequest()->getParams();
		$scope = $param['scope'];
		$day = $param['DAY'];
		$this->view->refresh = false;
		if(is_array($day)){
			if($scope==2){
				//kiểm tra xem ngày được chọn có trong hệ thống chưa
				foreach($day as $oneday){
					$ngay = explode("-",$oneday);
					$ngay = array("mon"=>$ngay[0],"mday"=>$ngay[1],"wday"=>$ngay[2]);
					if(!BussinessDateModel::IsNonWorkingDate($ngay)){
						BussinessDateModel::insertnonworking($ngay);
					}
				}
			}else{
			//kiểm tra xem ngày được chọn có trong hệ thống chưa
				foreach($day as $oneday){
					$ngay = explode("-",$oneday);
					$ngay = array("mon"=>$ngay[0],"mday"=>$ngay[1],"wday"=>$ngay[2]);
					BussinessDateModel::deletenonworking($ngay);
				}
			}
			$this->view->refresh=true;
		}
		$wday = $param['WDAY'];
		if(is_array($wday)){
			if($scope==2){
				//kiểm tra xem ngày được chọn có trong hệ thống chưa
				foreach($wday as $oneday){
					$ngay = array("wday"=>$oneday);
					if(!BussinessDateModel::IsNonWorkingDate($ngay)){
						BussinessDateModel::insertnonworkingwday($ngay['wday']);
					}
				}
			}else{
			//kiểm tra xem ngày được chọn có trong hệ thống chưa
				foreach($wday as $oneday){
					$ngay = array("wday"=>$oneday);
					BussinessDateModel::deletenonworkingwday($ngay['wday']);
				}
			}
			$this->view->refresh=true;
		}
		
		$curmonth = $param['month'];
		if($curmonth == 0){
			$d = getdate();
			$curmonth = $d['mon'];
			if($curmonth==13)$curmonth =1;
			if($curmonth==0)$curmonth =12;
		}else{
			if($curmonth==13)$curmonth =1;
			if($curmonth==0)$curmonth =12;
			$this->view->refresh=true;
		}
		$fromdate = mktime(0,0,0,$curmonth,1,QLVBDHCommon::getYear());
		$fromdate = getdate($fromdate);
		$fromdate = mktime(0,0,0,$curmonth,1-$fromdate['wday']+1,QLVBDHCommon::getYear());
		$fromdate = getdate($fromdate);
		$this->view->fromdate = $fromdate;
		$this->view->curmonth = $curmonth;
                
                
	}
	public function saveAction(){
		$param = $this->getRequest()->getParams();
		//var_dump($param['ldap_AD']);exit;
		$username=  str_replace("=",".",$param['ldap_username']);
		$file = '../application/config.ini';
		$fh = fopen($file, "w");
		$file_contents = "
			[ldap]
			    ldap.host=".$param['ldap_os']."
			    ldap.port=".$param['ldap_port']."
			    ldap.username=".$username."
			    ldap.password=\"".$param['ldap_pass']."\"
			    ldap.enable=".$param['kn_ldap']."
			    ldap.AD=".$param['ldap_AD']."
			    ldap.user_qd=".$param['ldap_user_qd']."	
			[vbmail]
				vbmail.email = ".$param["EMAIL_ADDR"]."
				vbmail.username = ".$param["USERNAME"]."
				vbmail.password = \"".$param["PASSWORD"]."\"
				vbmail.incominghost = ".$param["INCOMING_HOSTNAME"]."
				vbmail.outgoinghost = ".$param["OUTGOING_HOSTNAME"]."
				vbmail.incomingport = ".$param["INCOMING_PORT"]."
				vbmail.outgoingport = ".$param["OUTGOING_PORT"]."
				vbmail.incomingprotocol = ".$param["INCOMING_PROTOCOL"]."
				vbmail.outgoingprotocol = ".$param["OUTGOING_PROTOCOL"]."
				vbmail.is_out_ssl = ".$param["SSL_OUT"]."
				vbmail.is_in_ssl = ".$param["SSL_IN"]."
			[chat]
				services.chung = \"".$param["serviceschung"]."\"
				services.chat = \"".$param["serviceschat"]."\"
				services.ip = \"".$param["ipserverchat"]."\"

			[crypt]
				crypt.default.temp = ".$param["crypt_default_temp"]."
			[mail_client]
				
				mail.default.mailclientstorage = ".$param["mail_default_mailclientstorage"]."
				mail.default.mailclientstorage_inbox= ".$param["mail_default_mailclientstorage"]."\inbox
				mail.default.mailclientstorage_sent= ".$param["mail_default_mailclientstorage"]."\sent
				mail.default.outgoing_server = ".$param['default_outgoing_server']."
				mail.default.incoming_server = ".$param['default_incoming_server']."
				mail.default.incoming_port = 110 
				mail.default.outgoing_port = 25
				mail.default.incoming_protocol = POP3
				mail.default.outgoing_protocol = SMTP
				mail.default.ssl_in = 0
				mail.default.ssl_out = 0
				;mail cua quan tri he thong
				mail.admin.content = Bạn đã cấu hình thành công mail
				;mail quota
				mail.quota.maxsize=".$param["QUOTA_MAXSIZE"]."
			[general]
				;ten he thong
				sys_info.name_system = \"".$param['sysinfo_name']."\"
				sys_info.version = \"".$param['sysinfo_version']."\"
				sys_info.city = \"".$param['sysinfo_city']."\"
				sys_info.company = \"".$param['sysinfo_company']."\"
				sys_info.companyvt = \"".$param['sysinfo_companyvt']."\"
				sys_info.unit = \"".$param['sysinfo_unit']."\"
				sys_info.address = \"".$param['sysinfo_address']."\"
				sys_info.phone = \"".$param['sysinfo_phone']."\"
				sys_info.fax = \"".$param['sysinfo_fax']."\"
				sys_info.kyhieutiepnhan = \"".$param['sysinfo_kyhieutiepnhan']."\"
				sys_info.status = \"".$param['sysinfo_status']."\"
				sys_info.repservice = \"".$param['repservice']."\"
				sys_info.keyservice = \"".$param['keyservice']."\"
				;layout
				appearance.layoutPath = ../application/layouts/scripts/ 
				appearance.layout = Main 
				;database
				db.adapter = PDO_MYSQL
				db.params.host = \"".$param['db_host']."\"
				db.params.username = \"".$param['db_username']."\"
				db.params.password = \"".$param['db_password']."\"
				db.params.dbname = \"".$param['db_dbname']."\"
				db.params.dbdatastorage = \"".$param['db_dbdatastorage']."\"
				;filedinhkem
				file.root_dir = \"".$param['file_root_dir']."\"
				file.temp_path = \"".$param['file_temp_path']."\"
				limit=\"".$param['limit']."\"
				;hienthi=\"".$param['hienthi']."\"
				;backup
				backup.LL_THEO =\"".$param['LL_THEO']."\"
				backup.WEEKLY =\"".$param['WEEKLY']."\"
				backup.MONTHLY =\"".$param['MONTHLY']."\"
				backup.HOUR =\"".$param['HOUR']."\"
				backup.IS_BK_FDK =\"".$param['IS_BK_FDK']."\"
				backup.IS_BK_CSDL =\"".$param['IS_BK_CSDL']."\"
				backup.THUMUC_DICH =\"".$param['THUMUC_DICH']."\"
				backup.DOMAIN =\"".$param['DOMAIN']."\"
				backup.ADMISTRATOR =\"".$param['ADMISTRATOR']."\"
				backup.PASS_ADMISTRATOR =\"".$param['PASS_ADMISTRATOR']."\"
				;servives
				service.lienthong.uri=\"".$param['services']."\";
				service.lienthong.username=\"".$param['user_lienthong']."\";
				service.lienthong.password= \"".$param['pass_lienthong']."\";
				service.tiepnhanvb.maxrows=\"".$param['maxrows_lienthong']."\";
				service.lienthong.host=\"".$param['host_lienthong']."\";
				;motcua
				is_motcua = \"".$param['is_motcua']."\"
				
				
				;phieutrinh
				pt_maldcq = \"".$param['pt_maldcq']."\"
				pt_maldvp = \"".$param['pt_maldvp']."\"
				pt_maldp = \"".$param['pt_maldp']."\"
				;cgi
				cgi.os = win
				cgi.parse_word_exe_win = C:\antiword\antiword.exe
				cgi.parse_word_mapping_win = C:\antiword
				hscv.vtbp = \"".$param['hscv_vtbp']."\"
				lct.dukien = \"".$param['lct_dukien']."\"
				;chi tiet, but phe, phoi hop, filedinhkem, du thao, luan chuyen, bosung
				lct.bandem = \"".$param['lct_bandem']."\"
				lct.noinhan = \"".str_replace(";","\\;",str_replace("\r\n","\\n",$param['lct_noinhan']))."\"
				;autoupdate
				autoupdate.service = \"".$param['autoupdate_service']."\"
				autoupdate.donvi = \"".$param['autoupdate_donvi']."\"
		";
		$HSCV_1 = $param["HSCV_1_0"].",".$param["HSCV_1_1"].",".$param["HSCV_1_2"].",".$param["HSCV_1_3"].",".$param["HSCV_1_4"].",".$param["HSCV_1_5"].",".$param["HSCV_1_6"].",".$param["HSCV_1_7"].",".$param["HSCV_1_8"].",".$param["HSCV_1_9"];
		$HSCV_2 = $param["HSCV_2_0"].",".$param["HSCV_2_1"].",".$param["HSCV_2_2"].",".$param["HSCV_2_3"].",".$param["HSCV_2_4"].",".$param["HSCV_2_5"].",".$param["HSCV_2_6"].",".$param["HSCV_2_7"].",".$param["HSCV_2_8"].",".$param["HSCV_2_9"];
		$HSCV_3 = $param["HSCV_3_0"].",".$param["HSCV_3_1"].",".$param["HSCV_3_2"].",".$param["HSCV_3_3"].",".$param["HSCV_3_4"].",".$param["HSCV_3_5"].",".$param["HSCV_3_6"].",".$param["HSCV_3_7"].",".$param["HSCV_3_8"].",".$param["HSCV_3_9"];
		$file_contents .= "
			HSCV_1 = $HSCV_1
			HSCV_2 = $HSCV_2
			HSCV_3 = $HSCV_3
		";
		fwrite($fh, $file_contents);
		fclose($fh);
		$this->docreatebackupschedule();
                $this->InsertConfigIntoDB();
		$this->_redirect("/qtht/config");
	}
	public function nextyearAction(){
		global $db;
		$sql = "SHOW tables FROM qlvbdh LIKE '%2013'";
		$r = $db->query($sql);
		$tables = $r->fetchAll();
		foreach($tables as $table){
			try{
				$r = $db->query("show create table ".$table['Tables_in_qlvbdh (%2013)']);
				$r = $r->fetch();
				$r['Create Table'] = str_replace("2013","2008",$r['Create Table']);
				$db->query($r['Create Table']);
			}catch(Exception $ex){
				
			}
		}
		exit;
	}
	
	public function checkdatabaseAction(){
		$this->_helper->layout->disablelayout();
		$params = $this->_request->getParams();
		$prams_db = array(
		'host'=>$params['db_host'],
		'username'=>$params['db_username'],
		'password' =>$params['db_password'],
		'dbname' =>$params['db_dbname']
		);
		
		try{
			$new_db = Zend_Db::factory('PDO_MYSQL',$prams_db);
			$new_db->getConnection();
			echo 1; exit;
			
		}catch (Zend_Db_Adapter_Exception $e) {
			//echo $e->__toString();
   			echo 0;
   			exit;
		} catch (Zend_Exception $e) {
			//echo $e->__toString();
    		echo 0;
    		exit;
		}
	}
       public function checkdatabaseldapAction(){
                    $this->_helper->layout->disablelayout();
                    $params = $this->_request->getParams();		
             $options = array(
                   'host' => $params['ldap_os'],
                   'port' => (int)$params['ldap_port'],
                   'username' => $params['ldap_username'],
                   'password' => $params['ldap_pass'],                  

          );                
		
		try{
			$ldap= new Zend_Ldap($options);			
            $ldap->bind();
			echo 1; exit;
			
		}catch (Zend_Ldap_Exception  $e) {
   			var_dump($e);
   			exit;
		}	
		
	}

	public function dobackupnowAction(){
		$this->_helper->layout->disablelayout();
		$params = $this->_request->getParams();
                $data['TYPE'] = $params['LL_THEO'];
                $data['TIME']= $params['HOUR'];
                if($data['TYPE']==0) $data['TYPE_VALUE'] =0;
                else $data['TYPE_VALUE']= $params['TYPE_VALUE'];
                $data['FOLDER'] = $params['FOLDER_BK'];
                $data['CREATE_DATE']=date('Y-m-d H:i:m');
                $db = new Qtht_BackupConfigModel();
                if($db->insert($data))
                {
                    echo 1;exit;
                } else {
                    echo 0;exit;
                }

		
	}
	
	public function docreatebackupschedule(){
		
		
		$config = Zend_Registry::get('config');
		$tools_dir =  dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."html".DIRECTORY_SEPARATOR."tools";
		$path_batchfile = $tools_dir.DIRECTORY_SEPARATOR."set_schedule.bat";
		
		
		///tao file backup
		$config_db = $config->db->params;
		$path_dich = $config->backup->THUMUC_DICH;
		$path_dk = $config->file->root_dir;
		$bk_file_bat = '"' .$tools_dir.DIRECTORY_SEPARATOR."mysqldump". '" -u '. $config_db->username. " -p"."\"".$config_db->password."\" -h  " . $config_db->host ." " .$config_db->dbname . " > " . '"'. $path_dich.DIRECTORY_SEPARATOR."backup_qlvbdh.sql".'"'.
		"";   
		$bk_file_bat .= "\n xcopy $path_dk $path_dich  /E /H /R /Y "  ;
		
		$file = fopen($tools_dir.DIRECTORY_SEPARATOR."backup_schedule.bat",'w');
		file_put_contents($tools_dir.DIRECTORY_SEPARATOR."backup_schedule.bat",$bk_file_bat);
		
		$backup_batchfile = $tools_dir.DIRECTORY_SEPARATOR."backup_schedule.bat";
		//$backup_params = '"/create /tn "backup_qlvbdh" /tr '. $backup_batchfile .'" /sc daily /st 08:00:00 /ru TRUNGMEO\trunglv /rp trung133"';
		
		$backup_params = array();
		$backup_params[] = "\"$backup_batchfile\"";
		$backup_params[] = "\"".$config->backup->LL_THEO."\"";
		if($config->backup->LL_THEO == "WEEKLY")
			$backup_params[] = "\"".$config->backup->WEEKLY."\"";
		if($config->backup->LL_THEO == "MONTHLY")
			$backup_params[] = "\"".$config->backup->MONTHLY."\"";
		
		if($config->backup->LL_THEO == "DAILY")
			$backup_params[] = "\"daily\"";
		$backup_params[] = "\"".$config->backup->HOUR."\""; ;

		//$backup_params[] = "\"".$config->backup->."\""; ;
		
		$param_exe = implode(' ',$backup_params);
		
		$command = "call $path_batchfile $param_exe " ;
		//echo $command ; exit;
		pclose(popen("start /B ".$command , "r"));

		
	}

	public function dowriteBatfileForschedule(){
		
	}

	public function InsertConfigIntoDB() {
        $config = Zend_Registry::get('config');
        $param = $this->getRequest()->getParams();
        $dbadapter = $config->db->adapter;
	$dbhost = $param['db_host'];
	$dbusername = $param['db_username'];
	$dbpassword = $param['db_password'];
	$dbname = $param['db_dbname'];
        $svc_lt_uri = $param['services'];
	$svc_lt_username = $param['user_lienthong'];
	$svc_lt_password = $param['pass_lienthong'];
	$svc_tnvb_maxrows = $param['maxrows_lienthong'];
	$svc_lt_host = $param['host_lienthong'];
        $db_Adapter = Zend_Db_Table::getDefaultAdapter();
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                count(*) as C
            FROM
                info_config            
            ")->fetch();
        if ($result["C"] > 0) {
        $statement = $db_Adapter->prepare("DELETE FROM info_config");
        $statement->execute();
        }
        try {
        $statement = $db_Adapter->prepare("INSERT INTO info_config (name,value) VALUES ('db.adapter',?),('db.params.host',?),('db.params.username',?),('db.params.password',?),('db.params.dbname',?),('service.lienthong.uri',?),('service.lienthong.username',?),('service.lienthong.password',?),('service.tiepnhanvb.maxrows',?),('service.lienthong.host',?)");
        $statement->execute( array($dbadapter, $dbhost, $dbusername, $dbpassword, $dbname, $svc_lt_uri, $svc_lt_username, $svc_lt_password, $svc_tnvb_maxrows, $svc_lt_host) );
        }
        catch (Zend_Exception  $ex)
		{
		return false;
		}
    }

	function taonammoiinputAction(){
		
		$this->view->title = "Tạo mới năm làm việc";
		//$this->view->subtitle = "Tạo mới";
		$db = Zend_Db_Table::getDefaultAdapter();
		//lay nam gan nhat
		//$year_new = QlVBDHCommon::getYear();
		$query = $db->query("select YEAR from qtht_year order by YEAR DESC ");
		$re = $query->fetchAll();
		$this->view->data_year = $re;
		$nam_cuoi = $re[0]["YEAR"];
		$this->view->nam_cuoi = $nam_cuoi ;

		$query = $db->query(" select * from vb_sovanban where YEAR = ?  ORDER BY TYPE", array($nam_cuoi));
		$re = $query->fetchAll();
		$this->view->data_sovb = $re;
		

	}

	function taomoinammoisaveAction(){
		$params = $this->getRequest()->getParams();
		///var_dump($params);
		$db = Zend_Db_Table::getDefaultAdapter();
		//Tao cac bang cho nam moi 
		$config = Zend_Registry::get('config');
		$_auth = Zend_Registry::get('auth');
		
		$year_new = $params["YEAR"];
		
		$d = getdate();

		try{
		$db->insert('qtht_year',array("YEAR"=>$year_new));
		}catch(Exception $ex){
		}

		//Zend_Registry::set("year",$d['year']);
		$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE 'qtht_log_".$year_new."'";
		$r = $db->query($sql);
		$tables = $r->fetchAll();
		if(count($tables)==0){
			$year = new qtht_year();
			$qr = $db->query("select count(*) as DEM  from qtht_year where YEAR=?",array($year_new));
			$re = $qr->fetch();
			
			//tao nam lam viec
			$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE '%".($year_new-1)."'";
			$r = $db->query($sql);
			$tables = $r->fetchAll();
			foreach($tables as $table){
				try{
					$r = $db->query("show create table ".$table['Tables_in_'.($config->db->params->dbname).' (%'.($year_new-1).')']);
					$r = $r->fetch();
					$r['Create Table'] = str_replace($year_new-1,$year_new,$r['Create Table']);
					$db->query($r['Create Table']);
					
				}catch(Exception $ex){
					
				}
			}
			
		}
		
		
		
		//Tao so van ban cho nam moi
		$names = $params["NAME"];
		$id_svb = $params["ID_SVB"];
		
		$stt = 0;

		// get field insert
		$sql = "select group_concat(a.column_name) as COL from (select column_name from INFORMATION_SCHEMA.COLUMNS where table_name='vb_sovanban' and table_schema = SCHEMA() limit 1,99) a";
		$columns = $db->query($sql)->fetch();
		$columns = $columns["COL"];
		foreach($names as $name){
			// get data so cu
			$sql = "
				INSERT INTO vb_sovanban(".$columns.") SELECT ".$columns." FROM vb_sovanban WHERE ID_SVB = ?
			";
			$db->query($sql,$id_svb[$stt]);
			$id_new = $db->lastInsertId();
			$arraydata = array(
				"NAME"	=> $name,
				"YEAR" => $params["YEAR"]
			);

			
			$db->update("vb_sovanban",$arraydata,"ID_SVB=".$id_new);
			$stt++;
		}

		
		$this->_redirect("/qtht/config/taonammoiinput");
		//exit;
	}

}
