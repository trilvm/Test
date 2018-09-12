<?php

/**
 * carlendarpersonaldetailModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class calendarcorporationdetailModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'lct_calendar_corporation_detail_2013';
	public $_fromdate = "";
	public $_todate = "";
	function __construct($year){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='lct_calendar_corporation_detail_'.$year;
    		}
    	}
		$arr = array();
		parent::__construct($arr);
	}
	function CheckExist($noidung,$begindate,$enddate){
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
				ID_CCD
			FROM
				lct_calendar_corporation_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CC = cpd.ID_CC
			WHERE
				BEGINTIME >= ? AND
				ENDTIME <= ? AND
				CONTENT = ?
		";
		//echo $sql;exit;
		$r = $this->getDefaultAdapter()->query($sql,array($begindate,$enddate,$noidung));
		if($r->rowCount()==0){
			return 0;
		}else{
			$item = $r->fetch();
			return $item['ID_CCD'];
		}
	}
	function FindById($id,$iddep){
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_corporation_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CC = cpd.ID_CC
			WHERE
				ID_CCD = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($id));
		return $r->fetch();
	}
	public function SelectAllInDate($nothing,$fromdate){
		$todate = $fromdate . " 23:59:59";
		$fromdate .= " 00:00:00";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_corporation_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CC = cpd.ID_CC
			WHERE
				BEGINTIME >= ? AND
				ENDTIME <= ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($fromdate,$todate));
		return $r->fetchAll();
	}
	public function UpdateInDay($iddep,$id,$begintime,$endtime,$date){
		//Lấy bảng ghi hợp lệ
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_corporation_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CC = cpd.ID_CC
			WHERE
				ID_CCD = ?
		";
		$r = $this->getDefaultAdapter()->query($sql,array($id));
		$row = $r->fetch();
		$begintime = floor($begintime/60).":".floor($begintime%60);
		$endtime = floor($endtime/60).":".floor($endtime%60);
		$begintime = $date." ".$begintime;
		$endtime = $date." ".$endtime;
		$this->update(array("BEGINTIME"=>$begintime,"ENDTIME"=>$endtime),"ID_CCD = $id");
	}
	public function SelectAllRange($iddep,$fromdate,$todate){
		$fromdate .= " 00:00:00";
		$todate .= " 23:59:59";
		$sql = "
			SELECT
				*
			FROM
				lct_calendar_corporation_".QLVBDHCommon::getYear()." cp
				inner join ".$this->_name." cpd on cp.ID_CC = cpd.ID_CC
			WHERE
				((? >= BEGINTIME AND ? <= ENDTIME) OR
				(? >= BEGINTIME AND ? <= ENDTIME) OR
				(? <= BEGINTIME AND ? >= ENDTIME)) AND
				DATE_FORMAT(BEGINTIME, '%Y-%m-%d') <> DATE_FORMAT(ENDTIME, '%Y-%m-%d')
			";
		$r = $this->getDefaultAdapter()->query($sql,array($fromdate,$fromdate,$todate,$todate,$fromdate,$todate));
		return $r->fetchAll();
	}
}
