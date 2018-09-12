<?php
/**
 * GopYModel
 *  
 * @author truongvc
 * @version 1.0
 */

class GopYModel extends Zend_Db_Table
{
    protected $_name = 'hscv_gopy_2013';
    public $_id_gopy = 0;
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
    function __construct($year){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='hscv_gopy'.'_'.$year;
    			$this->setYear($year);	
    		}
    	}
		$arr = array();
		parent::__construct($arr);
	}
    /**
     * Count all items in hscv_gopy table
     * @return integer
     */
   	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and ID_GOPY like ?";
		}		
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				hscv_hosocongviec_".$this->getYear()."
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
     * Select all from $offset to $limit with $order arrange
     *
     * @param  String $idHscv
     * @return array
     */
	public function findAllGopYs($idHscv,$idU,$idPhoiHop)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT 
			  ".$this->getName().".ID_GOPY,".$this->getName().".NOIDUNG
			FROM
			  ".$this->getName()."
			WHERE
			  ".$this->getName().".id_hscv =?
			  AND ".$this->getName().".id_u = ?
			  AND ".$this->getName().".id_phoihop=?
			  ORDER BY ".$this->getName().".ID_GOPY DESC",array($idHscv,$idU,$idPhoiHop));
		return $r->fetchAll();
	}
	public function SelectAllByIdhscv($idhscv){
		$r = $this->getDefaultAdapter()->query("
			SELECT 
				gy.ID_GOPY,gy.NOIDUNG, concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME, concat(empyc.FIRSTNAME,' ',empyc.LASTNAME) as EMPNAMEYC
			FROM
			  ".$this->getName()." gy
			  left JOIN ".QLVBDHCommon::Table("HSCV_PHOIHOP")." ph on ph.ID_PHOIHOP = gy.ID_PHOIHOP
			  left JOIN QTHT_USERS u on u.ID_U = ph.ID_U
			  left JOIN QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
			  left JOIN QTHT_USERS uyc on uyc.ID_U = ph.ID_U_YC
			  left JOIN QTHT_EMPLOYEES empyc on empyc.ID_EMP = uyc.ID_EMP
			WHERE
			  ph.ID_HSCV = ?
			ORDER BY
				gy.ID_GOPY DESC
			",array($idhscv));
		
		return $r->fetchAll();
	}
	public function getIDPhoiHopByIdhscv($idhscv,$idu){
		$r = $this->getDefaultAdapter()->query("
			SELECT 
				ph.ID_PHOIHOP
			FROM
			  ".QLVBDHCommon::Table("HSCV_PHOIHOP")." ph
			WHERE
			  ph.ID_HSCV = ?
			  and ph.ID_U = ?
			",array($idhscv,$idu));
		$result = $r->fetch();
		return $result['ID_PHOIHOP'];
	}
	public function SelectAllUser($idhscv){
		$r = $this->getDefaultAdapter()->query("
			SELECT 
				ph.ID_PHOIHOP,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME, concat(empyc.FIRSTNAME,' ',empyc.LASTNAME) as EMPNAMEYC
			FROM
			  ".QLVBDHCommon::Table("HSCV_PHOIHOP")." ph
			  INNER JOIN QTHT_USERS u on u.ID_U = ph.ID_U
			  INNER JOIN QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
			  INNER JOIN QTHT_USERS uyc on uyc.ID_U = ph.ID_U_YC
			  INNER JOIN QTHT_EMPLOYEES empyc on empyc.ID_EMP = uyc.ID_EMP
			WHERE
			  ph.ID_HSCV = ?
			",array($idhscv));
		return $r->fetchAll();
	}
    public function SelectAllByIdgopy($idgopy){
		$r = $this->getDefaultAdapter()->query("
			SELECT 
				phoi.ID_GOPY,phoi.NOIDUNG, concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME,phoi.NGAY_PH
			FROM
			  ".QLVBDHCommon::Table("hscv_phoihop_phanhoi")." phoi
			  left JOIN ".QLVBDHCommon::Table("HSCV_GOPY")." gy on gy.ID_GOPY = phoi.ID_GOPY
			  left JOIN QTHT_USERS u on u.ID_U = phoi.ID_U
			  left JOIN QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP		  
			WHERE
			  phoi.ID_GOPY = ?
			ORDER BY
				phoi.ID_GOPY DESC
			",array($idgopy));
		$this->getDefaultAdapter()->update(QLVBDHCommon::Table('hscv_phoihop_phanhoi'),array("IS_NEW"=>0),"ID_GOPY='".$idgopy."'");
		return $r->fetchAll();
	}
	 
}