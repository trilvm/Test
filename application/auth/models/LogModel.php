<?php

/**
 * LogModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class LogModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'qtht_log_2013';
	var $_type = 0;
	var $_fromdate = "";
	var $_todate = "";
	static function Logging($module,$controller,$action,$error){
		$config = Zend_Registry::get('config');
		$_auth = Zend_Registry::get('auth');
		global $db;
		/*$d = getdate();
		Zend_Registry::set("year",$d['year']);
		$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE 'qtht_log_".$d['year']."'";
		$r = $db->query($sql);
		$tables = $r->fetchAll();
		if(count($tables)==0){
			$year = new qtht_year();
			$qr = $db->query("select count(*) as DEM  from qtht_year where YEAR=?",array($d['year']));
			$re = $qr->fetch();
			if((int)$re["DEM"]==0){
				$db->insert('qtht_year',array("YEAR"=>$d['year']));
			}
			//tao nam lam viec
			$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE '%".($d['year']-1)."'";
			$r = $db->query($sql);
			$tables = $r->fetchAll();
			foreach($tables as $table){
				try{
					$r = $db->query("show create table ".$table['Tables_in_'.($config->db->params->dbname).' (%'.($d['year']-1).')']);
					$r = $r->fetch();
					$r['Create Table'] = str_replace($d['year']-1,$d['year'],$r['Create Table']);
					$db->query($r['Create Table']);
					
				}catch(Exception $ex){
					
				}
			}
			
		}*/
	
		try{
			$db->insert(QLVBDHCommon::Table('qtht_log'),
				array(
					"ACTION"=>$module."\\".$controller."\\".$action,
					"EMP_NAME"=>$_auth->getIdentity()->USERNAME,
					"IP"=>$_SERVER['REMOTE_ADDR'],
					"ERROR"=>$error,
					"DATE"=>date("Y-m-d H:i:s")
				)
			);
		}catch(Exception $ex){
		
		}
		//}
	}
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		
		if($this->_fromdate != ""){
			if($this->_todate != ""){
				$arrwhere[] = $this->_fromdate;
				$arrwhere[] = $this->_todate;
				$strwhere .= " and DATE >= ? and DATE <= ?";
			}else{
				$arrwhere[] = $this->_fromdate;
				$strwhere .= " and DATE >= ?";
			}
		}
		
		if($this->_type==1){
			$arrwhere[] = $this->_type;
			$strwhere .= " and ERROR = ?";
		}
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$arrwhere[] = "%".$this->_search."%";
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (EMP_NAME like ? or IP like ? or ACTION like ?)";
		}
		
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order!=""){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				*
			FROM
				".QLVBDHCommon::Table('qtht_log')."
			WHERE
				$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	/**
	 * Đếm số bản ghi có trong table
	 */
	public function Count(){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_fromdate != ""){
			if($this->_todate != ""){
				$arrwhere[] = $this->_fromdate;
				$arrwhere[] = $this->_todate;
				$strwhere .= " and DATE >= ? and DATE <= ?";
			}else{
				$arrwhere[] = $this->_fromdate;
				$strwhere .= " and DATE >= ?";
			}
		}
		if($this->_type==1){
			$arrwhere[] = $this->_type;
			$strwhere .= " and ERROR = ?";
		}
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$arrwhere[] = "%".$this->_search."%";
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (EMP_NAME like ? or IP like ? or ACTION like ?)";
		}
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				".QLVBDHCommon::Table('qtht_log')."
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	
  static function getuser($username){
  	   
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select count(*) as counter from `qtht_users` where USERNAME=?
		";
		
		try{
			$qr = $dbAdapter->query($sql,array($username));
			$row=$qr->fetch();
			
			return $row["counter"];
		}catch(Exception $ex){
			return array();
		}
	}	

	static function deleteOne($id_log){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " delete from  ".QLVBDHCommon::Table('qtht_log')." where ID_LOG = ?  ";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_log));
		}catch(Exception $ex ){
		
		}
	}

	static function deleteAll(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " delete from  ".QLVBDHCommon::Table('qtht_log')."  ";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_log));
		}catch(Exception $ex ){
		
		}
	}
}
