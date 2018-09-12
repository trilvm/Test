
<?php

/**
 * Qlgv_ykienModel
 *  
 * @author locbc
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class Qlgv_ykienModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'qlgv_remark';
	
	function __construct($year){
		if(!$year)
			$year = QLVBDHCommon::getYear();
		$this->_name = "qlgv_remark_$year";
		$arr = array();
		parent::__construct($arr);
   }
	
	static function getAll($id_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from ".QLVBDHCommon::Table("qlgv_remark")." where ID_WORK=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_work));
			return $qr->fetchAll();	 
		}catch(Exception $ex){
			return array();
		}
	}
	static function getfile($id_remark){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT dk.* FROM ".QLVBDHCommon::Table("gen_filedinhkem")." dk 
	    		WHERE
	    			 ID_OBJECT =?
	    			 and
	    			 TYPE = 10";
	    try{
	    	$qr=$dbAdapter->query($sql,array($id_remark));
	    	return $qr->fetchAll();
	    }catch (Exception  $ex){
	    	return array();
	    }			 
	   
	   // var_dump($r);
	    
	}
	
	
	
	
}
