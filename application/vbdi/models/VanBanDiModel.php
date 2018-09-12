<?php

/**
 * VanBanDi
 *  
 * @author nguyennd
 * @version 1.0
 * @deprecated add 14/10/2009 
 */

require_once 'Zend/Db/Table/Abstract.php';

class VanBanDiModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'vbdi_vanbandi_2013';
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
	function __construct($year=null)
	{

		$year = QLVBDHCommon::getYear();
		if((int)$year>2000 && (int)$year<3000)
		{
			$this->_name='vbdi_vanbandi'.'_'.$year;
			$this->setYear($year);
		}

    	$arr = array();
		parent::__construct($arr);
	}
	/**
     * Count all items in vbdi_VanBanDi_2009 table
     * @return integer
     */
	function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and ".$this->_name.".TRICHYEU like ?";
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
	 * Danh sách văn bản đi
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
			$strwhere .= " and (".$this->_name.".TRICHYEU like ?) ";
		}		 
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order!="" || $order){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT ".$this->_name.".*,vb_coquan.NAME AS TENCOQUAN, SODI,
			vb_linhvucvanban.NAME AS LINHVUCVANBAN,vb_loaivanban.NAME AS LOAIVB,
			qtht_users.USERNAME AS NGUOITAO
			FROM ".$this->_name."				
			LEFT JOIN qtht_users ON qtht_users.ID_U=".$this->_name.".NGUOITAO
			LEFT JOIN vb_coquan ON vb_coquan.ID_CQ=".$this->_name.".ID_CQ
			LEFT JOIN vb_linhvucvanban ON vb_linhvucvanban.ID_LVVB=".$this->_name.".ID_LVVB
			LEFT JOIN vb_loaivanban ON vb_loaivanban.ID_LVB=".$this->_name.".ID_LVB
			WHERE
				$strwhere			
			$strorder
			$strlimit
		",$arrwhere);
		return $result->fetchAll();
	}
	
	function findBySoKyHieuAndTrichYeu($year , $sokyhieu , $trichyeu){
		$sql = 'select vbdi.`ID_HSCV` , vbdi.`ID_VBDI` as IDVB,vbdi.`TRICHYEU` , vbdi.`SOKYHIEU` from
		`'.QLVBDHCommon::getYear("vbdi_vanbandi").'` vbdi where vbdi.`SOKYHIEU`= ? or vbdi.`TRICHYEU` like ?';
		$arrdata = array($sokyhieu,'%'.$trichyeu.'%');
		$query = $this->getDefaultAdapter()->query($sql,$arrdata);
		return  $query->fetchAll();
	}
	/**
     * Lấy cha của thư mục này
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
                $this->_name
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetchAll();
       	if(isset($re))
       		return $this->find($re[0]["ID_VBDI"])->current();
    }
    
	/**
     * Lấy mã số liên thông của văn bản đến
     */
    public function findMaSoLienThongByHscv($id_hscv){ 
      
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($id_hscv != ""){
            $arrwhere[] = $id_hscv;
            $strwhere .= " and ID_HSCV = ?";
        }
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                ID_VBDEN
            FROM
            	".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")."
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetch();
        
        $arrwhere1 = array();
        $strwhere1 = "(1=1)";
        if($re['ID_VBDEN'] != ""){
            $arrwhere1[] = $re['ID_VBDEN'];
            $strwhere1 .= " and ID_VBD = ?";
        }
        $result = $this->getDefaultAdapter()->query("
            SELECT
                MASOLIENTHONG
            FROM
            	".QLVBDHCommon::Table("vbd_vanbanden")."
            WHERE
                $strwhere1
        ",$arrwhere1);
       	$maSoLienThong = $result->fetch();
        
        return $maSoLienThong['MASOLIENTHONG'];
    }
    
    
    function send($idvbd,$arridu,$noidung,$idnguoichuyen){
    	global $db;
		$this->getDefaultAdapter()->beginTransaction();
		try{
			$r = $db->query("SELECT TRICHYEU FROM ".QLVBDHCommon::Table("VBDI_VANBANDI")." WHERE ID_VBDI=?",$idvbd);
			$r = $r->fetch();
			foreach($arridu as $idu){
				$this->insert(array("NGUOICHUYEN"=>$idnguoichuyen,"NGUOINHAN"=>$idu,"ID_VBD"=>$idvbd,"NGAYCHUYEN"=>date("Y-m-d H:i:s"),"GHICHU"=>$noidung,"TRANGTHAI"=>0));
			}
			QLVBDHCommon::SendMessage(
				$idnguoichuyen,
				$idu,
				$r["TRICHYEU"],
				"vbdi/banhanh/list"
			);
			$this->getDefaultAdapter()->commit();
		}catch(Exception $ex){
			echo $ex->__toString();
			$this->getDefaultAdapter()->rollBack();
		}
	}
	function way($idvbd){
		$r = $this->getDefaultAdapter()->query("
			SELECT
				lc.*,
				concat(nc.FIRSTNAME,' ',nc.LASTNAME) as EMPNC,
				concat(nn.FIRSTNAME,' ',nn.LASTNAME) as EMPNN
			FROM
				$this->_name lc
				inner join QTHT_USERS unc on lc.NGUOICHUYEN = unc.ID_U
				inner join QTHT_USERS unn on lc.NGUOINHAN = unn.ID_U
				inner join QTHT_EMPLOYEES nc on nc.ID_EMP = unc.ID_EMP
				inner join QTHT_EMPLOYEES nn on nn.ID_EMP = unn.ID_EMP
			WHERE
				ID_VBD = ?
			ORDER BY
				ID_DLC DESC
		",array($idvbd));
		return $r->fetchAll();
	}
	/**
	 * Get max SODI field.
	 *
	 * @return int
	 */
     
     
	function getMaxSoDi()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
				MAX(SODI) AS SODI
			FROM
				$this->_name
				");
		$result=$r->fetchAll();
		if(count($result)>0)
			return $result[0]['SODI'];
		else 
			return null;
	}
    
	public function updateThuHoiVanBan($masolienthong)
	{
        $array = array(
        "IS_THUHOI" => 1
        );
        $this->update($array," MASOLIENTHONG = '".$masolienthong."'");
	}
	/**
	 * Get NOIDUNG field with $id
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	function getNoidung($id)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
				GHICHU AS NOIDUNG
			FROM
				vbdi_dongluanchuyen_".$this->getYear()."
			WHERE ID_VBDI=?",array($id));
		$result=$r->fetchAll();
		if(count($result)>0)
			return $result[0]['NOIDUNG'];
		else 
			return null;
	}
	
	static function getDetail($idvbdi,$year){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from vbdi_vanbandi_".$year." where ID_VBDI=?
		";
		try{
			$query =  $dbAdapter->query($sql, array($idvbdi));
			return $query->fetch();
		}catch (Exception $ex){
			return array();
		}
		
	}
	 static function getListVanbandenByIdVbdi($id_vbdi,$year){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select distinct vbden.* from 
    		`vbdi_vanbandi_$year` vbdi
    		inner join `vbd_fk_vbden_hscvs_$year` fk_vbden_hscvs on vbdi.`ID_HSCV`=fk_vbden_hscvs.`ID_HSCV`
    		inner join `vbd_vanbanden_$year` vbden on vbden.`ID_VBD` = fk_vbden_hscvs.`ID_VBDEN`
    		where vbdi.`ID_VBDI` = ? 
    	";
    	
    	try{
    		//echo $sql;
    		$stm = $dbAdapter->query($sql,array($id_vbdi));
    		$re = $stm->fetchAll();
    		return $re;
    	}catch (Exception $ex){
    		echo $ex;
    		return array();
    	}
    	
    	
    }
	static function getListVanbandenByIdVbdi_2($id_vbdi,$year){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select distinct vbden.*,fk_vbden_hscvs.`ID_HSCV` as ID_HSCV_LC  from 
    		`vbdi_vanbandi_$year` vbdi
    		inner join `vbd_fk_vbden_hscvs_$year` fk_vbden_hscvs on vbdi.`ID_HSCV`=fk_vbden_hscvs.`ID_HSCV`
    		inner join `vbd_vanbanden_$year` vbden on vbden.`ID_VBD` = fk_vbden_hscvs.`ID_VBDEN`
    		where vbdi.`ID_VBDI` = ? 
    	";
    	
    	try{
    		
    		$stm = $dbAdapter->query($sql,array($id_vbdi));
    		$re = $stm->fetch();
    		return $re;
    	}catch (Exception $ex){
    		echo $ex;
    		return array();
    	}
    	
    	
    }
	static function CanTiepNhan($idvbdi){
		return false;
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$sql = "
			SELECT * from ".QLVBDHCommon::table("vbdi_dongluanchuyen")." where
			ID_VBDI = $idvbdi and NGUOINHAN=".$user->ID_U." AND DA_TIEPNHAN=0
		";
		$r = $db->query($sql);
		if($r->rowcount()==1)return true;
		return false;
	}
	public static function CheckExistsVBDen($id_vbdi){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select count(*) as CNT from 
    		".QLVBDHCommon::table("vbdi_vanbandi")." vbdi
    		inner join ".QLVBDHCommon::table("vbd_fk_vbden_hscvs")." fk_vbden_hscvs on vbdi.`ID_HSCV`=fk_vbden_hscvs.`ID_HSCV`
    		inner join ".QLVBDHCommon::table("vbd_vanbanden")." vbden on vbden.`ID_VBD` = fk_vbden_hscvs.`ID_VBDEN`
    		where vbdi.ID_VBDI = ? 
    	";
    	
    	try{
    		//echo $sql;
    		$stm = $dbAdapter->query($sql,array($id_vbdi));
    		$re = $stm->fetch();
    		return $re["CNT"];
    	}catch (Exception $ex){
    		return 0;
    	}
	}
	public static function GetAllHSCV($idvbdi){
		global $db;
		$result = $db->query("
            SELECT
                vbdi.*
            FROM            	
				".QLVBDHCommon::Table("VBDI_VANBANDI")." vbdi
            WHERE
                vbdi.ID_VBDI = ?
        ",$idvbdi);
       	$re = $result->fetchAll();
       	return $re;
	}
         public function findMaDLCLienThongByHscv($id_hscv){ 
      
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($id_hscv != ""){
            $arrwhere[] = $id_hscv;
            $strwhere .= " and ID_HSCV = ?";
        }
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                ID_VBDEN
            FROM
            	".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")."
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetch();
        
        $arrwhere1 = array();
        $strwhere1 = "(1=1)";
        if($re['ID_VBDEN'] != ""){
            $arrwhere1[] = $re['ID_VBDEN'];
            $strwhere1 .= " and ID_VBD = ?";
        }
        $result = $this->getDefaultAdapter()->query("
            SELECT
                DLCLIENTHONG
            FROM
            	".QLVBDHCommon::Table("vbd_vanbanden")."
            WHERE
                $strwhere1
        ",$arrwhere1);
       	$maSoLienThong = $result->fetch();
        
        return $maSoLienThong['DLCLIENTHONG'];
    }
}
