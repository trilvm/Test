<?php

class MenusModel extends Zend_Db_Table
{
    protected $_name = 'qtht_menus';
    public $_id_mnu = 0;
	/**
     * Count all items in QTHT_MENUS table
     * @return integer
     */
	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and QTHT_MENUS.NAME like ?";
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
	function GetAllMenus()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			ID_MNU,NAME
			FROM
				QTHT_MENUS");
		return $r->fetchAll();
	}
	/**
     * Get all items in QTHT_ACTIONS table with pairs : ID_ACT and NAME
     * @return array
     */
	function GetAllActions()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			ID_ACT,ac.DESCRIPTION as NAME,md.NAME as MODNAME,md.ID_MOD
			FROM
				QTHT_ACTIONS ac
				inner join QTHT_MODULES md on md.ID_MOD = ac.ID_MOD
			ORDER BY
				md.ID_MOD
			");
		return $r->fetchAll();
	}
	/**
     * Check the exist menu name
     * @param  Zend_Db_Table $tableSel
     * @param  String $nameMenu
     * @return boolean
     */
	function checkExistMenu($tableSel,$nameMenu)
	{
		$result= $tableSel->fetchRow('ID_MNU='.$nameMenu,'ID_MNU ASC');
		$returnValue = $result->ID_MNU;			
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
			$strwhere .= " and QTHT_MENUS.NAME like ?";
		}
		$strwhere .= " and QTHT_MENUS.ID_MNU <> 1"; 
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
			SELECT QTHT_MENUS.*,QTHT_ACTIONS.NAME AS NAMEACTION
			FROM QTHT_MENUS				
			LEFT JOIN QTHT_ACTIONS
			ON QTHT_ACTIONS.ID_ACT=QTHT_MENUS.ACTID
			WHERE
				$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	/**
     * Get name action with id_act
     *
     * @param  integer $id_act
     * @return String
     */
	function getNameAction($id_act)
	{
		
	}
}