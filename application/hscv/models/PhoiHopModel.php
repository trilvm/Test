<?php
/**
 * PhoiHopModel
 *  
 * @author truongvc
 * @version 1.0
 */

class PhoiHopModel extends Zend_Db_Table
{
    protected $_name = 'hscv_phoihop_2013';
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
	function __construct($year=null){
    	if($year!=null)
    	{
    		if((int)$year>2000 && (int)$year<3000)
    		{
    			$this->_name='hscv_phoihop'.'_'.$year;
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
			$strwhere .= " and NAME like ?";
		}		
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				hscv_hosocongviec_".$this->getYear().
		"
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
	public function findAllUsers($idHscv)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT ".$this->_name.".*,
			  qtht_employees.*,
			  qtht_users.ID_U AS IDU,qtht_users.USERNAME
			FROM
			  ".$this->_name."
			  LEFT OUTER JOIN qtht_users ON (".$this->_name.".ID_U = qtht_users.ID_U)
			  LEFT OUTER JOIN qtht_employees ON (qtht_users.ID_EMP = qtht_employees.ID_EMP)
			WHERE
			  ".$this->_name.".id_hscv =?",array($idHscv));
		return $r->fetchAll();
	}
	/**
     * list all remain not assign user and hscv with firstname and lastname
     *
     * @param  String $idHscv
     * @return array
     */
	public function getRemainUsers($idHscv)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT 
			  qtht_employees.FIRSTNAME,qtht_employees.LASTNAME,
			  qtht_users.ID_U,qtht_users.USERNAME
			FROM
			  qtht_users
			LEFT JOIN qtht_employees ON (qtht_employees.ID_EMP = qtht_users.ID_EMP)
			WHERE qtht_users.ID_U 
			NOT IN(
			SELECT 
			  ".$this->_name.".ID_U			  
			FROM
			  ".$this->_name."
			  LEFT OUTER JOIN qtht_users ON (".$this->getName().".ID_U = qtht_users.ID_U)
			  LEFT OUTER JOIN qtht_employees ON (qtht_users.ID_EMP = qtht_employees.ID_EMP)
			WHERE
			  ".$this->_name.".id_hscv =?
			) ORDER BY qtht_employees.FIRSTNAME ASC ",array($idHscv));
		return $r->fetchAll();
	}
	
	/**
     * Select all from $offset to $limit with $order arrange
     *
     * @param  integer $offset
     * @param  integer $limit
     * @param  String $order
     * @return boolean
     */
	public function SelectAllHscv($offset,$limit,$order)
	{ 
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and (".$this->getName().".NAME like ?) ";
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
			SELECT hscv_hosocongviec_".$this->getYear().".*
			FROM hscv_hosocongviec_".$this->getYear()."				
			WHERE
			$strwhere
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	
	static function getNewPhoiHopByUser($year,$id_u,$offset,$limit){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql = "
			select ph.*, hscv.NAME as TEN_HS, CONCAT(qtht_employees.FIRSTNAME,' ',qtht_employees.LASTNAME) as TEN_NGUOIGUI from hscv_phoihop_$year ph
			inner join hscv_hosocongviec_$year hscv on ph.ID_HSCV = hscv.ID_HSCV 
			LEFT JOIN qtht_users ON ph.ID_U_YC = qtht_users.ID_U
			LEFT JOIN qtht_employees ON qtht_users.ID_EMP = qtht_employees.ID_EMP 
			where ph.ID_U=? and DA_XEM=? $strlimit
		";   
                
		try{
			$query = $dbAdapter->query($sql,array($id_u,0));
			return $query->fetchAll();
		}catch (Exception $ex){
			return array();
		}
		
	}
	
	static function updateDaXem($year,$id_u,$id_hscv){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			update hscv_phoihop_$year set DA_XEM=1 where ID_U=? and  ID_HSCV=? 
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$r = $stm->execute(array($id_u,$id_hscv));
			return $r;
		}catch (Exception $ex){
			return 0;
		}
	}
	static function AddPhoiHop($arridu,$idvbden,$iduyc){
		global $db;
		$sql = "SELECT * FROM 
		".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." vbd
		inner join ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv on vbd.id_hscv = hscv.id_hscv
		where vbd.id_vbden = ?
		";
		$r=$db->query($sql,$idvbden);
		$hscv = $r->fetchAll();
		foreach($hscv as $item){
			foreach($arridu as $u){
				$r=$db->query("SELECT * FROM ".QLVBDHCommon::Table("HSCV_PHOIHOP")." WHERE ID_HSCV = ? and ID_U = ?",array($item['ID_HSCV'],$u));
				if($r->rowCount()==0){
					$db->insert(QLVBDHCommon::Table("HSCV_PHOIHOP"),array("ID_U_YC"=>$iduyc,"NGAYGUI"=>date("Y-m-d H:i:s"),"ID_U"=>$u,"ID_HSCV"=>$item['ID_HSCV']));
					$vbden = new vbdenModel(QLVBDHCommon::getYear());
					$vbden = $vbden->findByHscv($item['ID_HSCV']);
					$db->update(QLVBDHCommon::Table("vbd_dongluanchuyen"),array("DA_XEM"=>1,'ISPHOIHOP'=>1),"ID_VBD=".$vbden['ID_VBDEN']." AND NGUOINHAN=".$u);
				}
			}
		}
	}
}