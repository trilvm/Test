<?php

/**
 * DanhMucNguoiDungController
 *
 * @author
 * @version
 */

require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/nguoidungModel.php';
require_once 'qtht/models/fk_users_groupsModel.php';
require_once 'qtht/models/fk_users_actionsModel.php';
require_once 'config/qtht.php';
require_once 'qtht/models/DepartmentsModel.php';
class Qtht_DanhMucNguoiDungController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated ActivityPoolController::indexAction() default action

		//L?y parameter

		$config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
                 if($this->_request->isPost())
                {
                 $limit=  $parameter["limit"];
                }
                else $limit=40;
                
		$page = $parameter["page"];
		$search = $parameter["search"];
		$sel_dep = $parameter["ID_DEP"];
		$this->view->sel_dep = (int)$parameter["ID_DEP"];
		//Tinh ch?nh parameter
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;

		//New cac model
		$this->nguoidung = new nguoidungModel();
                
                
                
		//Kh?i ??ng cac bi?n cho cac model
		$this->nguoidung->_search = $search;
		$this->nguoidung->_sel_dep = $sel_dep;
		//L?y d? li?u chinh
		$rowcount = $this->nguoidung->Count();
		$this->view->data = $this->nguoidung->SelectAll(($page-1)*$limit,$limit,"DEP.ID_DEP, u.ISLEADER DESC, LASTNAME");
		$this->view->dep = $this->nguoidung->GetDeparment();
                $this->view->data_group=$this->nguoidung->ToDepComboNhomNguoidung();
		//Set bi?n cho view
		$this->view->title = "Danh sách người dùng";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;

		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;

		//Enable button
		QLVBDHButton::EnableDelete("/qtht/danhmucnguoidung/Delete");
		QLVBDHButton::EnableAddNew("/qtht/danhmucnguoidung/Input");
                QLVBDHButton::AddButton("Xuất danh bạ","/qtht/Indanhba/list","PrintButton","inClick()");
	}
	/**
	 * Hi?n th? form input d? li?u
	 */
	public function inputAction()
	{
		$start = (float) array_sum(explode(' ',microtime()));
		//L?y parameter
		$parameter = $this->getRequest()->getParams();
		$limit = $parameter["limit"];
		$page = $parameter["page"];
		$search = $parameter["search"];
		$id = $parameter["id"];
		$error=$this->_request->getParam('error');
		$this->view->error=$error;
		//New cac model
		$this->nguoidung = new nguoidungModel();
		$this->group = new fk_users_groupsModel();
		$this->action = new fk_users_actionsModel();
                $this->view->nguoidungquanly = $this->nguoidung->getNguoiDung();
		//L?y d? li?u
                $this->view->dep2= $this->nguoidung->layDsPhong();
		if($id>0){
			$this->view->data = $this->nguoidung->FindById($id);
//                         var_dump($this->view->data) ;exit;
			$this->view->dep = $this->nguoidung->GetDeparment();
                       
                        $this->view->depMulti = $this->nguoidung->getMultiNguoiDung($id);
                        
			$this->view->title = "Cập nhật người dùng";
			//$this->view->subtitle = "Cập nhật";
			$this->view->id=$id;
		}else{
			$this->view->dep = $this->nguoidung->GetDeparment();
			$this->view->title = "Thêm mới người dùng";
			//$this->view->subtitle = "Thêm mới";
		}
        $this->view->datachucdanh = $this->nguoidung->ChucDanhToCombo();

		//Set bi?n cho view
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->group = $this->group->SelectAllByIDU($id);
		$this->view->action = $this->action->SelectAllByNonCheckIDU($id);
                if($id>0) QLVBDHButton::AddButton("In danh bạ","","PrintButton","indanhba($id)");
		QLVBDHButton::EnableSave("#");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
                
		//QLVBDHButton::EnableHelp("");
	}
	/**
	 * L?u d? li?u.
	 * N?u ?a co thi update
	 * N?u ch?a co thi insert
	 */
	public function saveAction(){
		$this->nguoidung = new nguoidungModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		//var_dump ( $this->getRequest()->getParams()); exit();
		try
		{
			$this->nguoidung->getDefaultAdapter()->beginTransaction();
			if($this->view->parameter["ID_U"]>0)
			{
				if($this->view->parameter["PASSWORD"]!="")
				{
					$this->nguoidung->update(array("USERNAME"=>$this->view->parameter["USERNAME"],"PASSWORD"=>md5($this->view->parameter["PASSWORD"]),"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISLEADER"=>$this->view->parameter["ISLEADER"]		
					),"ID_U=".$this->view->parameter["ID_U"]);
				}
				else
				{
					$this->nguoidung->update(array("USERNAME"=>$this->view->parameter["USERNAME"],"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISLEADER"=>$this->view->parameter["ISLEADER"]),"ID_U=".$this->view->parameter["ID_U"]);
				}
                                                   
				$this->nguoidung->getDefaultAdapter()->update("QTHT_EMPLOYEES",array("ADDRESS"=>$this->view->parameter["ADDRESS"],
                                                                                "PHONE_EXT"=>$this->view->parameter["PHONE_EXT"],
                                                                                "FIRSTNAME"=>$this->view->parameter["FIRSTNAME"],
                                                                                "ID_CD"=>$this->view->parameter["ID_CHUCDANH"],
                                                                                "LASTNAME"=>$this->view->parameter["LASTNAME"],
                                                                                "ID_DEP"=>$this->view->parameter["NGUOIDUNG"],
                                                                                "PHONE" => $this->view->parameter["PHONE"],
                                                                                "EMAIL"=>$this->view->parameter["EMAIL"]),
                                                                                "ID_EMP = ".$this->view->parameter["ID_EMP"]);
                                
                                $this->nguoidung->getDefaultAdapter()->delete("fk_user_dep","ID_U=".(int)$this->view->parameter["ID_U"]);
                                
                                $idDep = $this->_request->getParam('ID_DEP');
                                foreach($idDep as $ID_DEP)
                                {
                                    $this->nguoidung->getDefaultAdapter()->insert("fk_user_dep", array("ID_U" => $this->view->parameter["ID_U"], "ID_D" => $ID_DEP));
                                    // $idUserDep = $this->nguoidung->getDefaultAdapter()->lastInsertId("fk_user_dep");
                                }
                                
				$this->nguoidung->getDefaultAdapter()->delete("fk_users_groups","ID_U=".(int)$this->view->parameter["ID_U"]);
				for($i=0;$i<count($this->view->parameter["SELGROUP"]);$i++)
				{
					$this->nguoidung->getDefaultAdapter()->insert("fk_users_groups",array("ID_U"=>$this->view->parameter["ID_U"],"ID_G"=>$this->view->parameter["SELGROUP"][$i]));
				}
				$this->nguoidung->getDefaultAdapter()->delete("fk_users_actions","ID_U=".(int)$this->view->parameter["ID_U"]);
				for($i=0;$i<count($this->view->parameter["ID_ACT"]);$i++)
				{
					$this->nguoidung->getDefaultAdapter()->insert("fk_users_actions",array("ID_U"=>$this->view->parameter["ID_U"],"ID_ACT"=>$this->view->parameter["ID_ACT"][$i]));
				}
			}
			else
			{
//                            $idD = $this->_request->getParam('ID_DEP');
//                            foreach($idD as $D)
//                            {       
//                                var_dump($D);
//                            }
                                $this->nguoidung->getDefaultAdapter()->insert("QTHT_EMPLOYEES",array("ADDRESS"=>$this->view->parameter["ADDRESS"],
                                                                                                    "PHONE_EXT"=>$this->view->parameter["PHONE_EXT"],
                                                                                                    "ID_CD"=>$this->view->parameter["ID_CHUCDANH"],
                                                                                                    "ID_DEP"=>$this->view->parameter["NGUOIDUNG"],
                                                                                                    "FIRSTNAME"=>$this->view->parameter["FIRSTNAME"],
                                                                                                    "LASTNAME"=>$this->view->parameter["LASTNAME"],
                                                                                                    "PHONE" => $this->view->parameter["PHONE"],
                                                                                                    "EMAIL"=>$this->view->parameter["EMAIL"]));
				$idemp = $this->nguoidung->getDefaultAdapter()->lastInsertId("QTHT_EMPLOYEES");
                                
				try
				{
					$idu = $this->nguoidung->insert(array("ID_EMP"=>$idemp,"USERNAME"=>$this->view->parameter["USERNAME"],"PASSWORD"=>md5($this->view->parameter["PASSWORD"]),"ACTIVE"=>$this->view->parameter["ACTIVE"],"ISLEADER"=>$this->view->parameter["ISLEADER"]	
					));
                                        $idDep = $this->_request->getParam('ID_DEP');
                                        foreach($idDep as $ID_DEP)
                                        {
                                            $this->nguoidung->getDefaultAdapter()->insert("fk_user_dep", array("ID_U" => $idu, "ID_D" => $ID_DEP));
                                           // $idUserDep = $this->nguoidung->getDefaultAdapter()->lastInsertId("fk_user_dep");
                                        }
                                        
					for($i=0;$i<count($this->view->parameter["SELGROUP"]);$i++)
					{
						$this->nguoidung->getDefaultAdapter()->insert("fk_users_groups",array("ID_U"=>$idu,"ID_G"=>$this->view->parameter["SELGROUP"][$i]));
					}
					for($i=0;$i<count($this->view->parameter["ID_ACT"]);$i++)
					{
						$this->nguoidung->getDefaultAdapter()->insert("fk_users_actions",array("ID_U"=>$idu,"ID_ACT"=>$this->view->parameter["ID_ACT"][$i]));
					}
				}
				catch(Exception $e3)
				{
					$this->nguoidung->getDefaultAdapter()->rollBack();
					echo $e3->__toString();
					$this->_redirect("/default/error/error?control=danhmucnguoidung&mod=qtht&id=ERR11001001");
				}
			}
			$this->nguoidung->getDefaultAdapter()->commit();
		}
		catch(Exception $ex)
		{
			$this->nguoidung->getDefaultAdapter()->rollBack();
			echo $ex->__toString();
			exit;
			$this->_redirect("/default/error/error?control=danhmucnguoidung&mod=qtht&id=ERR11001001");
		}
		$this->nguoidung->getDefaultAdapter()->query("UPDATE QTHT_USERS U SET ORDERS=(SELECT MIN(ORDERS) FROM QTHT_GROUPS G INNER JOIN fk_users_groups UG ON G.ID_G = UG.ID_G WHERE UG.ID_U = U.ID_U)");
		$this->_redirect("/qtht/danhmucnguoidung");
	}
	/**
	 * Ki?m tra d? li?u, n?u ok thi tr? v? true.
	 */
	public function checkInput($name,$alias,$link,$id_c){
		$strurl='/default/error/error?control=ActivityPool&mod=wf&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('maxlength=50',$name,"ERR11001003").",";
		$strerr .= ValidateInputData::validateInput('req',$name,"ERR11001004").",";
		$strerr .= ValidateInputData::validateInput('req',$alias,"ERR11001005").",";
		$strerr .= ValidateInputData::validateInput('maxlength=20',$alias,"ERR11001006").",";
		$strerr .= ValidateInputData::validateInput('alnum',$alias,"ERR11001007").",";
		$strerr .= ValidateInputData::validateInput('maxlength=128',$link,"ERR11001009").",";
		$strerr .= ValidateInputData::validateInput('req',$link,"ERR11001010").",";
		$strerr .= ValidateInputData::validateInput('req',$id_c,"ERR11001008").",";
		if(strlen($strerr)!=8){
			$this->_redirect($strurl.$strerr);
		}
	}
	/**
	 * Ki?m tra tr?ng thai s? ky hi?u
	 *
	 */
	function checkAction()
	{
		$this->_helper->layout->disablelayout();
		$checkData =  $this->getRequest()->getParams();
		$userModel=new UsersModel();
		$username = $checkData['username'];
		$id_u=$checkData['id_u'];
		if($username!=null)
		{
			try
			{
				if($id_u>0)
				{
					$tempData=$userModel->fetchRow("USERNAME='".$username."' AND ID_U <> ".$id_u);
				}
				else
				{
					$tempData=$userModel->fetchRow("USERNAME='".$username."'");
				}
					
				if($tempData!=null)
				echo 1;
				else
				echo 2;
			}
			catch (Exception $e2)
			{
				echo $e2;
			}
		}
		else
		{
			echo 0;
		}
		exit;
	}
	/**
	 * Xoa d? li?u
	 */
	public function deleteAction(){
		$this->nguoidung = new nguoidungModel();
		$this->view->parameter =  $this->getRequest()->getParams();
		try
		{
			$this->nguoidung->delete("ID_U IN (".implode(",",$this->view->parameter["DEL"]).")");
		}
		catch(Exception $ex)
		{
			$this->_redirect("/default/error/error?control=danhmucnguoidung&mod=qtht&id=ERR11001001");
		}
		$this->_redirect("/qtht/danhmucnguoidung");
	}

	public function dongboAction(){
		
		global $db;
		$config = new Zend_Config_Ini('../application/config.ini','ldap',true);
	if((int)$config->ldap->enable ==1){	
		$basedn = explode(",",$config->ldap->username);
		$basedn =str_replace("$basedn[0],","",$config->ldap->username);
		$basedn =str_replace(".","=",$basedn);
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
		var_dump($info);exit;
		//get entry uid
		$sruid=ldap_search($ldap->getResource(),$basedn,"uid=*");
		$infouid=ldap_get_entries($ldap->getResource(),$sruid);
		//$userdb= ResourceUserModel::getuserdbo();

		if((int)$config->ldap->user_qd==1)
		{
		for ($i=0; $i<$info["count"]; $i++)
		{   //var_dump($info[$i]["Userpassword"][0]);exit;
		 	if($info[$i]["objectclass"][0]=="organizationalRole") continue;            
		 	$userdb=ResourceUserModel::getuser($info[$i]["cn"][0]);
            if($info[$i]["cn"][0] ==$userdb["USERNAME"])
			{
			}else{ 
				   // var_dump(md5($info[$i]["cn"][0]));exit;
				    $name_Ho="";
				    $name_Ten="";					
					$name=explode(" ",$info[$i]["sn"][0]);
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
				  try {
					$db->insert("qtht_employees",
					               array(
                                            "ID_DEP" => 1,
                                             "FIRSTNAME"=>$name_Ho,
                                             "LASTNAME"=>$name_Ten 
					));
					$id = $db->lastInsertId(qtht_employees);
					$db->insert("qtht_users",
					                array("ID_EMP"=>$id,
                                        "USERNAME"=>$info[$i]["cn"][0],
					                    "PASSWORD"=>md5($info[$i]["cn"][0])
				 					));	
				  }catch (Exception $e){}								
			}
			
		}
	  }	
	  if((int)$config->ldap->user_qd==0)
	  {	
		for ($i=0; $i<$infouid["count"]; $i++)
		{   
			if($infouid[$i]["objectclass"][0]=="organizationalRole") continue;
			$userdb=ResourceUserModel::getuser($infouid[$i]["uid"][0]);
			if($infouid[$i]["uid"][0] ==$userdb["USERNAME"])
			{
			}else{   
				    $name_Ho="";
				    $name_Ten="";
					$name=explode(" ",$infouid[$i]["sn"][0]);
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
					  try{
					$db->insert("qtht_employees",
					      array(
                                             "ID_DEP" => 1,
                                             "FIRSTNAME"=>$name_Ho,
                                             "LASTNAME"=>$name_Ten 
					));
					$id = $db->lastInsertId(qtht_employees);
					$db->insert("qtht_users",
					   array("ID_EMP"=>$id,
                                        "USERNAME"=>$infouid[$i]["uid"][0],
					                    "PASSWORD"=>md5($infouid[$i]["uid"][0])
					));
				 }catch(Exception $e){}
			}
		}
	  }		
		ldap_free_result($sr);
		$ldap->disconnect();
	}
		$this->_redirect("/qtht/danhmucnguoidung");
	}
        
      public function printcontactAction()
      { 
            $id_u=$this->getRequest()->getParam('ID_U');
            $this->_helper->layout->disableLayout();
            $db = Zend_Db_Table::getDefaultAdapter();
            $model_nguoidung= new nguoidungModel();
            $data= $model_nguoidung->FindById($id_u);
            $this->view->data=$data;
           // var_dump($data);exit;
      }
        

}
