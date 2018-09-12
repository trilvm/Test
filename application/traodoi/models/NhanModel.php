<?php

require_once ('Zend/Db/Table/Abstract.php');

class NhanModel extends Zend_Db_Table_Abstract 
{
	/**
	 * The default table name 
	 */
	protected $_name = 'td_nhan_2013';
	protected $_year='2013';
    function getName()
    {
    	return $this->_name;
    }
    function setYear($year)
    {
    	$this->_year=$year;
    }
    function getYear()
    {
    	return $this->_year;
    }
	function __construct($year=null)
	{
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='td_nhan_'.$year;
    			$this->setYear($year);
    		}
    		else QLVBDHCommon::getYear();
    	}
    	$arr = array();
		parent::__construct($arr);
	}
	function getNguoiNhan($id_thongtin)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*,QTHT_USERS.USERNAME,CONCAT(QTHT_EMPLOYEES.FIRSTNAME , ' ' , QTHT_EMPLOYEES.LASTNAME) AS TENNGUOI,
						".$this->getName().".danhan
			FROM
			   ".$this->getName()."
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoinhan
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP		
			WHERE 
			".$this->getName().".id_thongtin=?	",array($id_thongtin));		
		return $r->fetchAll();
	}
		
}

?>
