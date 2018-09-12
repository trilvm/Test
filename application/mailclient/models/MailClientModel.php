<?php 
require_once('mailclient/models/Email_account.php');

class MailClientModel {
/**
 * 
 *
 * @param unknown_type $id_eac : id cua trong csdl
 * @param unknown_type $arr_values : bang hash : Tencot => gia_tri
 */
static function saveconfig($id_eac,$arr_values){
	
	$e_a = Email_account::arrayToObject($arr_values);
	if(!is_null($e_a)){
		
		$id_e_a = $e_a->savetoDatabase($id_eac);
		
		if($id_e_a > 0){ // da luu email account thanh cong
			if($id_eac == 0){
			//luu bang ket noi
			$auth = Zend_Registry::get('auth');
			$id_u = $auth->getIdentity()->ID_U;
			$id_fk = MailClientModel::saveUser_EAccounts(0,$id_u,$id_e_a);
			if($id_fk>0)
				return $id_e_a;
			}
		}
		
		return $id_e_a;
	}
		
	return 0;
}

static function saveUser_EAccounts($id_fk,$id_u,$id_e_a){
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$sql="";
	if($id_fk >0){ //truong hop cap nhat
		//chua nghi ra cai gi su dung truong cap nhat 
	}else {
		//truong hop them moi
	$sql =" 
	insert into `fk_user_emailaccounts` (`ID_U`,`ID_E_A`) values (?,?)
	";	
	}
	try{
	$stm = $dbAdapter->prepare($sql);
	$stm->execute(array($id_u,$id_e_a));
	return $dbAdapter->lastInsertId('fk_user_emailaccounts','ID_FK_U_EAS'); 
	}catch ( Exception  $ex){
		return 0;
	}
	
}

static function getEmailAccountByUser($id_u){
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$sql="
	select em.* from 
	`email_account` em
	inner join 
	(select * from `fk_user_emailaccounts` where `ID_U`=?) fk  
	on fk.`ID_E_A` = em.`ID_EAC`
	order by em.`IS_MAIN` DESC
	";
	//try{
	$stm = $dbAdapter->query($sql,array($id_u));
	return $stm->fetchAll();
	//}catch ( Exception  $ex){
		return array();
	//}
}
	
}
?>