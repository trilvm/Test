<?php

class DepartmentsModel extends Zend_Db_Table
{
    protected $_name = 'qtht_departments';
    public $_id_p = 0;
	/**
     * Count all items in QTHT_DEPARTMENTS table
     * @return integer
     */
	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";;
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and QTHT_DEPARTMENTS.NAME like ?";
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
     * Get all items in QTHT_MENUS table with pairs : ID_MNU and NAME
     * @return array
     */
	function GetAllDeps()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			ID_DEP,NAME
			FROM
				QTHT_DEPARTMENTS");
		return $r->fetchAll();
	}
	
	static function getAll()
	{
		$r = Zend_Db_Table::getDefaultAdapter()->query("
			SELECT
     			*
			FROM
				QTHT_DEPARTMENTS
			WHERE ID_DEP != 1
				");
		return $r->fetchAll();
	}
	
	public function checkExistDep($tableSel,$idDep)
	{
		$result= $tableSel->fetchRow('ID_DEP='.$idDep,'ID_DEP ASC');
		$returnValue = $result->ID_DEP;			
		if($returnValue!=null)
		{
			return false;
		}
		else 
		{
			return true;
		}
			
	}
	function checkExistNameDep($tableSel,$NameDep)
	{
		$result= $tableSel->fetchRow('NAME="'.$NameDep.'"','ID_DEP ASC');
		$returnValue = $result->NAME;			
		if($returnValue!=null)
		{
			return false;
		}
		else 
		{
			return true;
		}
			
	}
/**
     * Select all from $offset to $limit with $order arrange
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
			$strwhere .= " and NAME like ?";
		}
		$strwhere .= " and QTHT_DEPARTMENTS.ID_DEP <> 1"; 
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
			SELECT
				*
			FROM
				$this->_name
			WHERE
				$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	/**
	 * trunglv : Đưa dữ liệu phòng ban vào combobox
	 * 
	 */
	public static function toComboName(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `CODE_DEP`,`NAME`,`ID_DEP` from `qtht_departments` 
		where `ID_DEP` !=1  AND ACTIVE=1
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['CODE_DEP']."' value=".$row['ID_DEP'].">".$row['NAME']."</option>";
		}
	}
	public static function toComboNameWithSel($sel){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `CODE_DEP`,`NAME`,`ID_DEP` from `qtht_departments` 
		where `ID_DEP` !=1 AND ACTIVE=1
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['CODE_DEP']."' value=".$row['ID_DEP']." ".(in_array($row['ID_DEP'],$sel)?"selected":"").">".$row['NAME']."</option>";
		}
	}
	public static function toComboNamelinhvuc($sel,$owner=false){
	    global $auth;
		$user = $auth->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select mc.* from `motcua_linhvuc` mc
		".($owner?" inner join motcua_linhvuc_quyen mcq on mc.ID_LV_MC = mcq.ID_LV_MC where mcq.ID_U = ".$user->ID_U:"")."
		ORDER BY NAME 
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			$selected = "";
			if(in_array($row['ID_LV_MC'], $sel))$selected = "selected";
			echo "<option id='".$row['ID_LV_MC']."' value=".$row['ID_LV_MC']." $selected>".$row['NAME']."</option>";
		}
	}
	static function getNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME` from `qtht_departments` 
		where `ID_DEP` = ?
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetch();
		return $re["NAME"];
	}
	
        public function nguoiDaiDien()
        {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "SELECT u.ID_U, u.USERNAME 
                        FROM qtht_users u 
                        left join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP 
                        left join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP 
                        left join fk_users_groups fk_u_g on u.ID_U = fk_u_g.ID_U 
                        left join QTHT_GROUPS g on fk_u_g.ID_G = g.ID_G WHERE (1=1) AND u.ISLEADER = 1 
                        GROUP BY ID_U ORDER BY DEP.ID_DEP, u.ISLEADER DESC
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetchAll();
		return $re;
        }
	
}