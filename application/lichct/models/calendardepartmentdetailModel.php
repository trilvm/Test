<?php

/**
 * carlendarpersonaldetailModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class calendardepartmentdetailModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'lct_calendar_department_detail_2013';
	public $_fromdate = "";
	public $_todate = "";
	function __construct($year){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='lct_calendar_department_detail_'.$year;
    		}
    	}
		$arr = array();
		parent::__construct($arr);
	}
	function CheckExist($noidung,$begindate,$enddate,$iddep){
		$begindate = explode(":",$begindate);
		$begindate[1] = "00";
		$begindate[2] = "00";
		$begindate = implode(":",$begindate);
		$enddate = explode(":",$enddate);
		$enddate[1] = "59";
		$enddate[2] = "59";
		$enddate = implode(":",$enddate);
		$sql = "
			SELECT
				ID_CDD
			FROM
				lct_calendar_department_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CD = cpd.ID_CD
			WHERE
				ID_DEP = ? and
				BEGINTIME >= ? AND
				ENDTIME <= ? AND
				CONTENT = ?
		";
		//echo $sql;exit;
		$r = $this->getDefaultAdapter()->query($sql,array($iddep,$begindate,$enddate,$noidung));
		if($r->rowCount()==0){
			return 0;
		}else{
			$item = $r->fetch();
			return $item['ID_CDD'];
		}
	}
	function FindById($id,$iddep){
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_department_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CD = cpd.ID_CD
			WHERE
				ID_CDD = ? and
				ID_DEP = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($id,$iddep));
		return $r->fetch();
	}
	public function SelectAllInDate($iddep,$fromdate){
		$todate = $fromdate . " 23:59:59";
		$fromdate .= " 00:00:00";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_department_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CD = cpd.ID_CD
			WHERE
				ID_DEP = ? AND
				BEGINTIME >= ? AND
				ENDTIME <= ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($iddep,$fromdate,$todate));
		return $r->fetchAll();
	}
	public function UpdateInDay($iddep,$id,$begintime,$endtime,$date){
		//Lấy bảng ghi hợp lệ
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_department_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CD = cpd.ID_CD
			WHERE
				ID_DEP = ? AND
				ID_CDD = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($iddep,$id));
		$row = $r->fetch();
		$begintime = floor($begintime/60).":".floor($begintime%60);
		$endtime = floor($endtime/60).":".floor($endtime%60);
		$begintime = $date." ".$begintime;
		$endtime = $date." ".$endtime;
		$this->update(array("BEGINTIME"=>$begintime,"ENDTIME"=>$endtime),"ID_CDD = $id");
	}
	public function SelectAllRange($iddep,$fromdate,$todate){
		$fromdate .= " 00:00:00";
		$todate .= " 23:59:59";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_department_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CD = cpd.ID_CD
			WHERE
				ID_DEP = ? AND
				((? >= BEGINTIME AND ? <= ENDTIME) OR
				(? >= BEGINTIME AND ? <= ENDTIME) OR
				(? <= BEGINTIME AND ? >= ENDTIME)) AND
				DATE_FORMAT(BEGINTIME, '%Y-%m-%d') <> DATE_FORMAT(ENDTIME, '%Y-%m-%d')
			";
		$r = $this->getDefaultAdapter()->query($sql,array($iddep,$fromdate,$fromdate,$todate,$todate,$fromdate,$todate));
		return $r->fetchAll();
	}
}
