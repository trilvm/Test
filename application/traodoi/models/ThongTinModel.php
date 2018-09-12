<?php
/**
 * ThongtinModel
 * 
 * $athor truongvc@unitek.vn
 *
 */
class ThongTinModel extends Zend_Db_Table
{
    protected $_name = 'td_thongtin_2013';
    public $_id_chude= 0;
    public $_search="";
    public $_extsearch="";
    public $_danhan=null;
    public $_id_u="";
    protected $_year='2013';
    /**
     * get name of table
     *
     * @return string
     */
    function getName()
    {
    	return $this->_name;
    }
    /**
     * set Year variable
     *
     * return null
     */
    function setYear($year)
    {
    	$this->_year=$year;
    }
    /**
     * get Year
     *
     * @return String
     */
    function getYear()
    {
    	return $this->_year;
    }
    /**
     * set _danhan value
     *
     * @param int $value
     */
    public function setDaNhan($value)
    {
    	$this->_danhan=$value;	
    }
    /**
     * return _danhan
     *
     * @return int
     */
    public function getDaNhan()
    {
    	return $this->_danhan;
    }
    /**
     * _construct
     *
     * @param int $year
     */
    function __construct($year=null)
	{
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='td_thongtin'.'_'.$year;
    			$this->setYear($year);
    		}
    		else
    		{
    			$this->setYear(QLVBDHCommon::getYear());    			
    		}
    		
    	}
    	$arr = array();
		parent::__construct($arr);
		global $auth;
		$user = $auth->getIdentity();
		$this->_id_u=$user->ID_U;		
	}
	/**
	 * get data for td_chude table
	 *
	 * @param $id_chude
	 * @return array
	 */
    public function getData($id_chude)
    {
    	$r = $this->getDefaultAdapter()->query("
    			SELECT id_thongtin,id_chude,thongtinlienquan,nguoitao,tieude,noidung,hienthi, date_format(ngaytao, '%h:%i') as ngaytao 
				FROM 
				$this->_name 
				WHERE id_chude = ?",array($id_chude));
    	return $r->fetchAll();   	
    }
    /**
     * set IdChude
     *
     * @param int $value
     */
    public function setValueIdChuDe($value)
    {
    	$this->_id_chude=$value;
    }
    /**
     * get IdChuDe
     *
     * @return int
     */
    public function getValueIdChuDe()
    {
    	return $this->_id_chude;
    }
    /**
     * get number of inbox items
     *
     * @return int
     */
    public function getNumberOfInbox()
    {
    	//Thực hiện query
    	$arrwhere=array(0);
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				$this->_name
			WHERE draft=? AND ".$this->getName().".hienthi=1",$arrwhere)->fetch();
		return $result["C"];
    }
    /**
     * Count all items in td_thongtin table
     * @return integer
     */
	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != "")
		{
			$arrwhere[] = "%".$this->_search."%";
			$strwhere 	.= " and ".$this->getName().".tieude like ?";
		}
		if($this->_extsearch != "")
		{
			$arrwhere[] = $this->_extsearch;
			$strwhere 	.= " and ".$this->getName().".draft like ?";
		}
		if($this->getValueIdChuDe() > 0)
		{
            $arrwhere[]	= $this->getValueIdChuDe();
            $strwhere 	.= " and ".$this->getName().".id_chude = ?";
        }
		$strwhere 	.= " and ".$this->getName().".hienthi = 1";
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
			$strwhere .= " and ".$this->getName().".tieude like ?";
		}
		if($this->_extsearch != "")
		{
			$arrwhere[] = $this->_extsearch;
			$strwhere 	.= " and ".$this->getName().".draft = ?";
		}
		if($this->getValueIdChuDe()> 0){
            $arrwhere[] = $this->getValueIdChuDe();
            $strwhere .= " and ".$this->_name.".id_chude = ?";
        }	
       
        //Lay nguoitao
		
		if($this->_id_u> 0)
		{
            $arrwhere[] = $this->_id_u;
            $strwhere .= " and ".$this->_name.".nguoitao = ?";
        }
        //Append for where sentence with hienthi condition
        $strwhere .= " and ".$this->_name.".hienthi = 1";
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order!=""){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*,CONCAT(FIRSTNAME , ' ' , LASTNAME) AS nguoitao,QTHT_USERS.USERNAME as username
			FROM ".$this->getName()."			
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoitao
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP
			WHERE
			$strwhere
			$strorder
			$strlimit
		",$arrwhere);

		return $result->fetchAll();		
	}
	/**
     * Count all inbox td_thongtin table
     * @return integer
     */
	public function CountInbox()
	{
		//Build phần where
		$arrwhere = array($this->_id_u);
		$strwhere = "(1=1) AND td_nhan_".$this->getYear().".nguoinhan=? AND td_nhan_".$this->getYear().".hienthi=1";		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(".$this->getName().".id_thongtin) AS C,
  				sum(td_nhan_".$this->getYear().".danhan) AS UNREAD		
			FROM
				td_nhan_".$this->getYear()."
			INNER JOIN ".$this->getName()." ON ".$this->getName().".id_thongtin=td_nhan_".$this->getYear().".id_thongtin			
			WHERE
				$strwhere
		",$arrwhere)->fetch();	
		return $result;
	}
	/**
     * Count all draft items td_thongtin table
     * @return integer
     */
	public function CountDraft()
	{
		//Build phần where
		$arrwhere = array($this->_id_u);
		$strwhere = "(1=1)";		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				".$this->getName()."
			WHERE 
				$strwhere
			AND ".$this->getName().".draft=1 AND ".$this->getName().".nguoitao=? AND ".$this->getName().".hienthi=1",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
     * Count all sentitems in td_thongtin table
     * @return integer
     */
	public function CountSentItems()
	{
		//Build phần where
		$arrwhere = array($this->_id_u);
		$strwhere = "(1=1)";		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				".$this->getName()."
			WHERE 
				$strwhere
			AND ".$this->getName().".draft=0 AND ".$this->getName().".nguoitao=? AND ".$this->getName().".hienthi=1",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
     * Count all in inbox td_thongtin table
     * @return integer
     */
	public function CountForInbox()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and ".$this->getName().".tieude like ?";
		}		
		if($this->getValueIdChuDe()> 0){
            $arrwhere[] = $this->getValueIdChuDe();
            $strwhere .= " and ".$this->getName().".id_chude = ?";
        }
        //Lay nguoitao		
		if($this->_id_u> 0){
            $arrwhere[] = $this->_id_u;
            $strwhere .= " and td_nhan_".$this->getYear().".nguoinhan = ?";
        }
        $strwhere .= " and td_nhan_".$this->getYear().".hienthi = 1";
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				td_nhan_".$this->getYear()."
			INNER JOIN ".$this->getName()." ON ".$this->getName().".id_thongtin=td_nhan_".$this->getYear().".id_thongtin			
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
	public function SelectAllForInbox($offset,$limit,$order,$all=0){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and ".$this->getName().".tieude like ?";
		}		
		if($this->getValueIdChuDe()> 0){
            $arrwhere[] = $this->getValueIdChuDe();
            $strwhere .= " and ".$this->getName().".id_chude = ?";
        }else if($all==0){
			$strwhere .= " and (".$this->getName().".id_chude IS NULL OR ".$this->getName().".id_chude = 0)";
		}else{

		}
        if(!is_null($this->_danhan))
        {
        	$arrwhere[] = $this->_danhan;
            $strwhere .= " and td_nhan_".$this->getYear().".danhan = ?";
        }
        if($this->_id_u> 0){
            $arrwhere[] = $this->_id_u;
            $strwhere .= " and td_nhan_".$this->getYear().".nguoinhan = ?";
        }
        $strwhere .= " and td_nhan_".$this->getYear().".hienthi = 1";
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		//Lay nguoitao
		
		
		//Build order
		$strorder = "";
		if($order!=""){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*,
				CONCAT(FIRSTNAME , ' ' , LASTNAME) AS nguoitao,
				QTHT_USERS.USERNAME as username,
				DATE_FORMAT(td_nhan_".$this->getYear().".ngaygui, '%d/%m/%Y %H:%i') as ngaytao,				
				td_nhan_".$this->getYear().".danhan,
				td_nhan_".$this->getYear().".id_nhan
			FROM td_nhan_".$this->getYear()."
			INNER JOIN ".$this->getName()." ON ".$this->getName().".id_thongtin=td_nhan_".$this->getYear().".id_thongtin
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoitao
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP
			WHERE
			$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();		
	}
	/**
	 * Get nguoigui data field
	 *
	 * @param int $id_thongtin
	 * @return array
	 */
	public function getNguoiGui($id_thongtin)
	{
		$result = $this->getDefaultAdapter()->query("
			SELECT QTHT_USERS.USERNAME as nguoigui,CONCAT(FIRSTNAME , ' ' , LASTNAME) AS tennguoigui
			FROM ".$this->getName()."
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoitao	
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP		
			WHERE
			".$this->getName().".id_thongtin=?",array($id_thongtin))->fetch();		
		return $result;		
	}
	/**
	 * get thongtinlienquan of id_thongtin
	 *
	 * @param array $dataLienQuan
	 * @param int $id_thongtin
	 * @return array
	 */
	public function getThongTinLienQuan($dataLienQuan,$id_thongtin,$nguoinhan=null)
	{
		$where_nguoinhan="";
		if($nguoinhan>0)
		{
			$where_nguoinhan=" AND (td_nhan_".$this->getYear().".nguoinhan=".$nguoinhan." OR ".$this->getName().".nguoitao=".$nguoinhan.")";
		}
		$result=$this->getDefaultAdapter()->query("
			SELECT CONCAT(FIRSTNAME,' ',LASTNAME) AS tennguoigui,".$this->getName().".*
			FROM ".$this->getName()."
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoitao	
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP
			INNER JOIN td_nhan_".$this->getYear()." ON ".$this->getName().".id_thongtin=td_nhan_".$this->getYear().".id_thongtin
			WHERE
			".$this->getName().".id_thongtin=?".$where_nguoinhan,array($id_thongtin));
		$rows = $result->fetchAll();	
		if(count($rows)>0) $dataLienQuan[0]=$rows[0];	
		$result->closeCursor();					
		for($i=0;$i<count($rows);$i++)
		{
			if(is_null($rows[$i]['thongtinlienquan']))
			{
				
			}
			else 
			{
				
				$dataLienQuan[count($dataLienQuan)] = $rows[$i];
				ThongTinModel::getThongTinLienQuan(&$dataLienQuan,$rows[$i]['thongtinlienquan'],$nguoinhan);				
			}
		}		
		return $result->fetchAll();		
	}
	public function getMoiNhan($where,$id_u)
	{
		$whereext="";
		if($this->getValueIdChuDe()>0)
		$whereext=" and ".$this->getName().".id_chude = ".$this->getValueIdChuDe();
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->getName().".*,CONCAT(FIRSTNAME , ' ' , LASTNAME) AS nguoitao,
			QTHT_USERS.USERNAME as username,td_nhan_".$this->getYear().".ngaygui as ngaytao,td_nhan_".$this->getYear().".danhan,td_nhan_".$this->getYear().".id_nhan
			FROM td_nhan_".$this->getYear()."
			INNER JOIN ".$this->getName()." ON ".$this->getName().".id_thongtin=td_nhan_".$this->getYear().".id_thongtin
			INNER JOIN QTHT_USERS ON QTHT_USERS.ID_U=".$this->getName().".nguoitao
			INNER JOIN QTHT_EMPLOYEES ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP
			WHERE
			$where
			AND  td_nhan_".$this->getYear().".nguoinhan = ? AND td_nhan_".$this->getYear().".hienthi=1".$whereext,array($id_u));		
		return $result->fetchAll();
	}
}