<?php

/**
 * Qlgv_ykienModel
 *  
 * @author locbc
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class Qlgv_gopyModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'qlgv_journal';
	
	function __construct($year){
		if(!$year)
			$year = QLVBDHCommon::getYear();
		$this->_name = "qlgv_journal_$year";
		$arr = array();
		parent::__construct($arr);
   }
	
	static function getAll($id_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from ".QLVBDHCommon::Table("qlgv_journal")." where ID_WORK=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_work));
			return $qr->fetchAll();	 
		}catch(Exception $ex){
			return array();
		}
	}
	static function getfile($id_journal){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT dk.* FROM ".QLVBDHCommon::Table("gen_filedinhkem")." dk 
	    		WHERE
	    			 ID_OBJECT =?
	    			 and
	    			 TYPE = 12";
	    try{
	    	$qr=$dbAdapter->query($sql,array($id_journal));
	    	return $qr->fetchAll();
	    }catch (Exception  $ex){
	    	return array();
	    }			 
	   
	   // var_dump($r);
	    
	}
	
	
	
	
}
