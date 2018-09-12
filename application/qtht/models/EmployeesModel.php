<?php

class EmployeesModel extends Zend_Db_Table
{
    protected $_name = 'qtht_employees';
    public $_id_p = 0;
	/**
     * Count all items in qtht_employees table
     * @return integer
     */
	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and QTHT_EMPLOYEES.FIRSTNAME like ?";
		}
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				$this->_name
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
     * Select all menus from $offset to $limit with $order arrange
     *
     * @param  integer $offset
     * @param  integer $limit
     * @param  String $order
     * @return boolean
     */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (QTHT_EMPLOYEES.FIRSTNAME like ? OR QTHT_EMPLOYEES.LASTNAME like ?) ";
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
			SELECT QTHT_EMPLOYEES.*,QTHT_DEPARTMENTS.ID_DEP AS IDDEP,QTHT_DEPARTMENTS.NAME AS NAMEDEP
			FROM QTHT_EMPLOYEES				
			LEFT JOIN QTHT_DEPARTMENTS
			ON QTHT_DEPARTMENTS.ID_DEP=QTHT_EMPLOYEES.ID_DEP
			WHERE
				$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	/**
     * Get all items in QTHT_MENUS table with pairs : ID_MNU and NAME
     * @return array
     */
	function GetAllFreeEmployees($idCurrent)
	{
		$where='';
		if($idCurrent!=null)
		$where ="WHERE QTHT_USERS.ID_U <>".$idCurrent;
		$r = $this->getDefaultAdapter()->query("
			SELECT qtht_employees.*
			FROM
			   `qtht_employees`
			WHERE 
				qtht_employees.ID_EMP 
			NOT IN
			(SELECT
			  qtht_employees.ID_EMP
			FROM
			  qtht_employees
			  INNER JOIN qtht_users ON (qtht_employees.ID_EMP = qtht_users.ID_EMP)
			
			".$where.")");
		return $r->fetchAll();
	}
}