<?php
/**
 * @author trunglv
 * @version 1.0
 * Lop model cho bang motcua_hoso_...
 */
require_once ('Zend/Db/Table/Abstract.php');

class hosomotcua {
	var $_id_loaihoso; //id loai ho so
	var $_sothutu; //so thu tu
	var $_id_phongban;//phong ban xu ly ho so (khong co trong)
}
class motcua_hosoModel extends Zend_Db_Table_Abstract {
	protected $_name = 'motcua_hoso_2013';
    public $_id_hoso = 0;
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
    		else
    		{
    			$this->setYear(QLVBDHCommon::getYear());    			
    		}
    		
    	}
    	$arr = array();
		parent::__construct($arr);		
	}
	/**
	 * Cap nhat ho so mot cua sau khi tra ho so 
	 */
	function updateAfterTraHoSo($idHSCV,$ngay_tra,$luc_tra,$is_khongxuly,$lydokhongxuly){
		//cap nhat ngay tra , luc tra , co xu ly va trang thai da tra ho so
		$where = 'ID_HSCV='.$idHSCV;
		$data = array(
		'TRA_NGAY'=>$ngay_tra,
		'TRA_LUC'=>$luc_tra,
		'KHONGXULY'=>$is_khongxuly,
		'TRANGTHAI'=> 2,
		'LYDOKHONGXULY'=>$lydokhongxuly
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
	/**
	 * Lay HSMC voi tham so $idHSCV
	 *
	 * @param int $idHSCV
	 * @return object
	 */
	function getHSMCByIdHSCV($idHSCV){
		$se = $this->select()->where('ID_HSCV=?',$idHSCV);
		return $this->fetchRow($se);
	}
	/**
     * Lấy hồ sơ công việc
     */ 
	public function findHscv($id_hscv){        
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($id_hscv != ""){
            $arrwhere[] = $id_hscv;
            $strwhere .= " and ID_HSCV = ?";
        }
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *
            FROM
                HSCV_HOSOCONGVIEC_".$this->getYear()."
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetch();
       	return $re;
    }
	/**
	 * Lay ten loai ho so cong viec theo id loai ho so
	 */
	function getTenLoaiHoSoById($id_loaihoso){
		$query = $this->getDefaultAdapter()->query("
			select `TENLOAI` from `motcua_loai_hoso`
			where `ID_LOAIHOSO`=?
		",$id_loaihoso);
		$re = $query->fetch();
		return $re['TENLOAI'];
	}
	static function getNextSo(){
		global $db;
		$query = $db->query("
			select max(SO) as SO FROM ".QLVBDHCommon::Table("MOTCUA_HOSO")."
		");
		$re = $query->fetch();
		return $re['SO']+1;
	}
	static function getNextSoHoso($id_stn){
		global $db;
		$where = "";
		if($id_stn > 0)
		$where = "  where ID_STN = ? group by ID_STN ";
		$query = $db->query("
			select max(SO) as SO FROM ".QLVBDHCommon::Table("MOTCUA_HOSO")."
			$where
			
		", array($id_stn));
		$re = $query->fetch();
		return $re['SO']+1;
	}
	public static function getyeucaubosung($id_yeucau){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "select * from ".QLVBDHCommon::Table("hscv_phieu_yeucau_bosung")."		       
		   where ID_YEUCAU= $id_yeucau
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re;   
	}
	public static function getid_pl($id_o){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();		
		$sql = " select wfpl.ID_PL from ".QLVBDHCommon::Table("wf_processlogs")." wfpl
		          INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." wfpi On wfpl.ID_PI = wfpi.ID_PI
		          where wfpi.ID_O = $id_o and wfpl.HANXULY >0 AND wfpl.`TRE`is NULL
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re['ID_PL'];   
	}
	public static function ws_getphong($id_u){
        
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select NAME from `qtht_departments` dp 
		       INNER JOIN qtht_employees emp  ON emp.ID_DEP = dp.ID_DEP
		       INNER JOIN qtht_users ON qtht_users.ID_EMP = emp.ID_EMP
		   where ID_U = $id_u
		";
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re['NAME'];
	}
	static function Customfields($id_loaihoso,$is_all=1,$is_tiepnhan=2,$is_ketqua=2,$is_baocao=2){
		
		
		$pr = array();
		$pr[] = $id_loaihoso;
		if(!$is_all){
			$where = " ". ( ($is_tiepnhan==0||$is_tiepnhan==1)?" AND IS_TIEPNHAN = ? ":"" ). (($is_ketqua==0||$is_ketqua==1)?" AND IS_KETQUA = ?":"") . (($is_baocao==0||$is_baocao==1)?"AND IS_BAOCAO = ? ": "");
			
			if($is_tiepnhan==0||$is_tiepnhan==1)
				$pr[] = $is_tiepnhan;
			if($is_ketqua==0||$is_ketqua==1)
				$pr[] = $is_ketqua;
			if($is_baocao==0||$is_baocao==1)
				$pr[] = $is_baocao;
		}
		$sql = "  select mc_cf.* from motcua_custom_field mc_cf 
		inner join motcua_loai_hoso loaihs on mc_cf.LOAIHOSO_CODE = loaihs.CODE 
		where ID_LOAIHOSO = ?  $where
		";
		
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,$pr);
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	static function CustomfieldsInBN($id_loaihoso){
		$sql = "  select mc_cf.* from motcua_custom_field mc_cf 
		inner join motcua_loai_hoso loaihs on mc_cf.LOAIHOSO_CODE = loaihs.CODE 
		where ID_LOAIHOSO = ? and IS_INCHOCD=1
		";
		//try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_loaihoso));
			return $qr->fetchAll();
		//}catch(Exception $ex){
			return array();
		//}
	}
}

?>
