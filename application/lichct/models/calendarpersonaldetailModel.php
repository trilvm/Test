<?php

/**
 * carlendarpersonaldetailModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class calendarpersonaldetailModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'lct_calendar_personal_detail_2013';
	public $_fromdate = "";
	public $_todate = "";
	function __construct($year){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='lct_calendar_personal_detail_'.$year;
    		}
    	}
		$arr = array();
		parent::__construct($arr);
	}
	function FindById($id,$idu){
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_personal_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CP = cpd.ID_CP
			WHERE
				ID_CPD = ? and
				ID_U = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($id,$idu));
		return $r->fetch();
	}
	public function SelectAllInDate($idu,$fromdate){
		$todate = $fromdate . " 23:59:59";
		$fromdate .= " 00:00:00";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_personal_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CP = cpd.ID_CP
			WHERE
				ID_U = ? AND
				BEGINTIME >= ? AND
				ENDTIME <= ?
			ORDER BY
				BEGINTIME
		";
		$r = $this->getDefaultAdapter()->query($sql,array($idu,$fromdate,$todate));
		return $r->fetchAll();
	}
	public function UpdateInDay($idu,$id,$begintime,$endtime,$date){
		//Lấy bảng ghi hợp lệ
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_personal_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CP = cpd.ID_CP
			WHERE
				ID_U = ? AND
				ID_CPD = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($idu,$id));
		$row = $r->fetch();
		$begintime = floor($begintime/60).":".floor($begintime%60);
		$endtime = floor($endtime/60).":".floor($endtime%60);
		$begintime = $date." ".$begintime;
		$endtime = $date." ".$endtime;
		$this->update(array("BEGINTIME"=>$begintime,"ENDTIME"=>$endtime),"ID_CPD = $id");
	}
	public function SelectAllRange($idu,$fromdate,$todate){
		$fromdate .= " 00:00:00";
		$todate .= " 23:59:59";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_personal_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CP = cpd.ID_CP
			WHERE
				ID_U = ? AND
				((? >= BEGINTIME AND ? <= ENDTIME) OR
				(? >= BEGINTIME AND ? <= ENDTIME) OR
				(? <= BEGINTIME AND ? >= ENDTIME)) AND
				DATE_FORMAT(BEGINTIME, '%Y-%m-%d') <> DATE_FORMAT(ENDTIME, '%Y-%m-%d')
			ORDER BY
				BEGINTIME
			";
		$r = $this->getDefaultAdapter()->query($sql,array($idu,$fromdate,$fromdate,$todate,$todate,$fromdate,$todate));
		return $r->fetchAll();
	}
}
