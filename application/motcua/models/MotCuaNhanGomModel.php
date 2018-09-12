<?php
/**
 * PhoiHopModel
 *  
 * @author truongvc
 * @version 1.0
 */

class MotCuaNhanGomModel extends Zend_Db_Table
{
    protected $_name = 'motcua_nhan_gom_2013';
    public $_id_tailieu_nhan = 0;
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
	function __construct($year=null){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='motcua_nhan_gom_'.$year;
    			$this->setYear($year);
    		}
    		else $this->setYear('2013');
    	}
    	$arr = array();
		parent::__construct($arr);
	}
    /**
     * Count all items in hscv_phoihop_2009 table
     * @return integer
     */
    public function Count()
	{
		//Build phan where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and TEN_THUTUC like ?";
		}		
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				".$this->getName().
		"
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}	
	/**
     * Select all from $offset to $limit with $order arrange
     *
     * @param  integer $offset
     * @param  integer $limit
     * @param  String $order
     * @return boolean
     */
	public function SelectAll($offset,$limit,$order)
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (".$this->getName().".TEN_THUTUC like ?) ";
		}		 
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order>0){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*
			FROM 
				".$this->getName()."				
			WHERE
			$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
}