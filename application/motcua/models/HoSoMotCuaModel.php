<?php

require_once ('Zend/Db/Table/Abstract.php');
class HoSoMotCuaModel extends Zend_Db_Table_Abstract 
{
	protected $_name = 'motcua_hoso_2013';
    public $_id_phoihop = 0;
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
    /**
     * Khoi tao model cho Ho So Mot Cua
     *
     * @param int $year
     */
	function __construct($year)
	{
		if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='motcua_hoso'.'_'.$year;
    			$this->setYear($year);
    		}
    		else $this->setYear('2013');
    	}
    	$arr = array();
		parent::__construct($arr);		
	}
	/**
	 * Enter description here...
	 *
	 * @param int $idHSCV
	 * @param unknown_type $ngay_tra
	 * @param unknown_type $luc_tra
	 * @param unknown_type $is_khongxuly
	 */
	function updateAfterTraHoSo(int $idHSCV,$ngay_tra,$luc_tra,$is_khongxuly){
		//cap nhat ngay tra , luc tra , co xu ly va trang thai da tra ho so
		$where = 'ID_HSCV='.$idHSCV;
		$data = array(
		'NHANLAI_NGAY'=>$ngay_tra,
		'NHANLAI_LUC'=>$luc_tra,
		'KHONGXULY'=>$is_khongxuly,
		'TRANGTHAI'=> 2
		);
		$this->update($data,$where);
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
		if($this->_search != "")
		{
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (".$this->getName().".MAHOSO like ?";
		}		
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				".$this->getName()."
		 	WHERE
				$strwhere
		 ",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
	 * Danh sách Hồ sơ một cửa
	 * @param  integer $offset
     * @param  integer $limit
     * @param  String $order
     * @return boolean
	 */
	function SelectAll($offset,$limit,$search,$filter_object,$order){
		
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (".$this->getName().".MAHOSO like ?) ";
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
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*,motcua_loai_hoso.TENLOAI AS TENLOAI,
			qtht_users.USERNAME AS NGUOINHAN
			FROM ".$this->getName()."				
			LEFT JOIN motcua_loai_hoso ON motcua_loai_hoso.ID_LOAIHOSO=".$this->getName().".ID_LOAIHOSO
			LEFT JOIN qtht_users ON qtht_users.ID_U=".$this->getName().".NGUOINHAN
			WHERE
			$strwhere			
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
}

?>
