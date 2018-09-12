<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');
class vbdennhan{
	var $_id_lvb;
	var $_id_cq; 
	var $_domat;
	var $_dokhan;
	var $_soden;
}

class vbdennhanModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'vbd_nhannoibo';
    public static function GetAllHSCV($idvbdnb){
		global $db;
		$result = $db->query("
            SELECT
                hscv.*
            FROM
            	".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")." fk
				inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on fk.ID_HSCV = hscv.ID_HSCV
            WHERE
                fk.ID_VBDEN = ?
        ",$idvbdnb);
       	$re = $result->fetchAll();
       	return $re;
	}

    /**
     * Lấy văn bản đến thông qua hồ sơ công việc
     */
    public function findByHscv($id_hscv){        
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
                ID_VBDEN AS ID_VBD,ID_VBDEN
            FROM
            	".QLVBDHCommon::Table("VBD_FK_VBDEN_HSCVS")."
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetch();
       	return $re;
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
                HSCV_HOSOCONGVIEC_".QLVBDHCommon::getYear()."
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetch();
       	return $re;
    }
	static function CanEdit($idvbdnb){
		global $auth;
		$user = $auth->getIdentity();
		$sql = "select count(*) as CNT from vbd_nhannoibo"." vbd inner join VB_SOVANBAN svb on svb.ID_SVB = vbd.ID_SVB where svb.ID_DEP=? AND vbd.ID_VBD = ?";
		//echo $sql;
		$result = Zend_Db_Table::getDefaultAdapter()->query($sql,array($user->ID_DEP,$idvbdnb));
		$result = $result->fetch();
		if($result['CNT']==1)return true;
		return false;
	}
    /**
     * Chuyển dữ liệu sổ văn bản tới combobox
     */
	static function WriteSVB($sel,$des,$onlyowner=false){
		global $auth;
		$user = $auth->getIdentity();
		//var_dump($user);exit;
        if(!$des){
        	$des = " -- Chọn sổ văn bản -- ";
        }
    	$arrwhere = array();
        $strwhere = "(1=1) and TYPE=1 and YEAR=".QLVBDHCommon::getYear();
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_sovanban
            WHERE
                $strwhere          
 				".(($onlyowner && $user->DEPLEADER==0)?" AND ID_DEP=".$user->ID_DEP:"")."
       ",$arrwhere);
        $data = $result->fetchAll();
        
        $html="";
        $html .= "<option value='0'>" . $des . "</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_SVB"]."' ".($row["ID_SVB"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
    static function GetDataSVB($sel){
    	global $auth;
		$user = $auth->getIdentity();
    	$arrwhere = array();
        $strwhere = "(1=1) and TYPE=1 and YEAR=".QLVBDHCommon::getYear()." AND ID_DEP=".$user->ID_DEP;
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_sovanban
            WHERE
                $strwhere          
        ",$arrwhere);
        $data = $result->fetchAll();
        return $data;
    }
	static function GetDataNguoiKy(){
    	
    	$arrwhere = array();
        //$strwhere = "(1=1) and TYPE=1 and YEAR=".QLVBDHCommon::getYear();
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                NGUOIKY as NGUOIKY_ID,NGUOIKY
            FROM
                vbd_nhannoibo"."
            GROUP BY NGUOIKY  
            ORDER BY count(NGUOIKY) DESC
        ");
        $data = $result->fetchAll();
        return $data;
    }
    /**
     * Chuyển dữ liệu lĩnh vực văn bản văn bản tới combobox
     */
    static function WriteLVVB($sel){
        $arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_linhvucvanban 
            WHERE
                $strwhere and ACTIVE=1  
        ",$arrwhere);
        $data = $result->fetchAll();
        
        $html="";
        $html .= "<option value='0'>" . " -- Chọn lĩnh vực văn bản -- " . "</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_LVVB"]."' ".($row["ID_LVVB"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }   
    /**
     * Chuyển dữ liệu danh mục cơ quan tới combobox
     */
    static function WriteCQ($sel){
        $arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_coquan
            WHERE
                $strwhere
                and 
                ISSYSTEMCQ = 0          
        ",$arrwhere);
        $data = $result->fetchAll();
        
        $html="";
        $html .= "<option value='0'>" . " -- Chọn cơ quan -- " . "</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_CQ"]."' ".($row["ID_CQ"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
 static function WriteCQBH($sel){
        $arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_coquan
            WHERE
                $strwhere
                and 
                ISSYSTEMCQ = 1          
        ",$arrwhere);
        $data = $result->fetchAll();
        
        $html="";
        $html .= "<option value='0'>" . " -- Chọn cơ quan -- " . "</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_CQ"]."' ".($row["ID_CQ"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
	static function GetDataCQ($sel){
    	$arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_coquan
            WHERE
                $strwhere   
        ",$arrwhere);
        $data = $result->fetchAll();
        return $data;
    }
 static function GetDataCQN($sel,$owner=false){
		global $auth;
		$user = $auth->getIdentity();
    	$arrwhere = array();
        $strwhere = "(1=1)";
		if($owner){
			$innerjoin="inner join VB_SOVANBAN svb on svb.ID_CQ = cq.ID_CQ";
			$strwhere .= " and svb.ID_DEP=".$user->ID_DEP;
		}
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                distinct cq.*
            FROM
                vb_coquan cq
				$innerjoin
            WHERE
                $strwhere
                and 
                ISSYSTEMCQ = 1
        ",$arrwhere);
        $data = $result->fetchAll();
        return $data;
    }
    /**
     * Chuyển dữ liệu loại văn bản văn bản tới combobox
     */
    static function WriteLVB($sel,$des){
        if(!$des){
        	$des = " -- Chọn loại văn bản -- ";
        }
    	$arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_loaivanban
            WHERE
                $strwhere ORDER BY NAME
        ",$arrwhere);
        $data = $result->fetchAll();
        
        $html="";
        $html .= "<option value='0'>" . $des . "</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_LVB"]."' ".($row["ID_LVB"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
	static function GetDataLVB($sel){
    	$arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                vb_loaivanban
            WHERE
                $strwhere          
        ",$arrwhere);
        $data = $result->fetchAll();
        return $data;
    }
    /**
     * Check dữ liệu số đến
     */
    
    
    
    function check_soden($soden,$arr_value){
		
		
		if($soden == 0){
			return 0;
		}
    	$year =  QLVBDHCommon::getYear();
		$is_exists =  Common_Sovanban::checkExistsSoden($year,$arr_value,$soden);
		if($is_exists == 1)
			return 0;
		return 1;
		/*$cur_soden = Common_Sovanban::getCurrentSoden($year,$arr_value);
		
		if($soden <= $cur_soden )
			return false;
		return true;*/
	 
	 /* $arrwhere = array();
        $strwhere = "(1=1)";
        if($soden != ""){
            $arrwhere[] = $soden;
            $strwhere .= " and SODEN = ?";
        }
        if($id_svb != ""){
            $arrwhere[] = $id_svb;
            $strwhere .= " and ID_SVB = ?";
        }
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *
            FROM
                $this->_name
            WHERE
                $strwhere          
        ",$arrwhere);
                
        if($result->rowCount()>0)
        	return false;
        return true;*/
    }
    /**
     * Lấy số đến tăng tự động thông qua sổ văn bản và năm
     */
    public function get_soden($array){        
       /* //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";  
        if($id_svb != ""){
            $arrwhere[] = $id_svb;
            $strwhere .= " and ID_SVB = ?";
        }      
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                MAX(SODEN) AS MAXSODEN
            FROM
                $this->_name
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetchAll();
       	if(isset($re)) return (int)$re[0]["MAXSODEN"] + 1;
       	return 1;*/
	//return 10;
	$year=QLVBDHCommon::getYear();
	$result = Common_Sovanban::getCurrentSoden($year,$array);
	if($result=="")
		return 1;
	else
		return (int)($result+1);
    }
    /**
     * Check dữ liệu số ký hiệu
     */
    function check_sokyhieu($sokyhieu,$ngaybanhanh,$coquanbanhanh){
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($sokyhieu != ""){
            $arrwhere[] = $sokyhieu;
            $strwhere .= " and SOKYHIEU = ?";
        }
        if($ngaybanhanh != ""){
            $arrwhere[] = $ngaybanhanh;
            $strwhere .= " and NGAYBANHANH = ?";
        }
        if($coquanbanhanh != ""){
            $arrwhere[] = $coquanbanhanh;
            $strwhere .= " and ID_CQ = ?";
        }
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *
            FROM
                $this->_name
            WHERE
                $strwhere
        ",$arrwhere);
        if($result->rowCount()>0)
        	return false;
        return true;
    }
    function getdetail($idvbd){
    	$result = $this->getDefaultAdapter()->query("
            SELECT
                vb.*, lvb.NAME as LVBNAME, lvvb.NAME as LVVBNAME,
                svb.NAME as SVBNAME, cq.NAME as CQNAME,
                concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME
            FROM
                vbd_nhannoibo vb
                inner join VB_LOAIVANBAN lvb on vb.ID_LVB = lvb.ID_LVB
                left join VB_LINHVUCVANBAN lvvb on vb.ID_LVVB =lvvb.ID_LVVB
                inner join VB_SOVANBAN svb on vb.ID_SVB = svb.ID_SVB
                left join VB_COQUAN cq	on vb.ID_CQ = cq.ID_CQ
                inner join QTHT_USERS u on vb.NGUOITAO = u.ID_U
                inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
            WHERE
                vb.ID_VBD = ?          
        ",array($idvbd));
         
        return $result->fetch();
    }
    
    static function isExists($year,$id_vbd){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select `ID_VBD` from vbd_nhannoibo where `ID_VBD`=?";
    	try{
    		$stm = $dbAdapter->query($sql,array($id_vbd));
    		$re = $stm->fetchAll();
    	}catch (Exception $ex){
    		return 0;
    	}
    	
    	return count($re) ;
    		
    }
    
    static function getDetails($year,$id_vbd){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select * from vbd_nhannoibo where `ID_VBD`=?";
        try{
    		$stm = $dbAdapter->query($sql,array($id_vbd));
    		$re = $stm->fetch();
    		return $re;
    	}catch (Exception $ex){
    		return array();
    	}
    	
    }
    /**
     * Enter description here...
     *
     * @param unknown_type $year
     * @param array $arr_value key('SOKYHIEU','NGAYBANHAN','ID_CQ')
     * @param unknown_type $so_den
     * @return unknown
     */
    static function checkSoKyHieu($year,$arr_value){
    	//dinh dang arr_value
    	
    	$arr_colum = array(
    	'SOKYHIEU','NGAYBANHANH','ID_CQ'
    	);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_nhannoibo` 
			".$having."";
		$qr = $dbAdapter->query($sql);
		$re = $qr->fetch();
		//return $re["DEM"];
		if($re["DEM"] >0 )
			return 0;
		return 1;
    	
    	//$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	
    }
    
    static function checkAllData($year,$arr_value){
    	//dinh dang arr_value
    	
    	$arr_colum = Common_Sovanban::getColumNameGroup(1);
    	array_push($arr_colum,'SODEN');
    	$arr_colum2 = array(
    	'SOKYHIEU','NGAYBANHANH','ID_CQ');
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE (" .$having.implode(' and ',$arr_where).")";
			$arr_where = array();
			foreach($arr_colum2 as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having =$having. " OR (" .implode(' and ',$arr_where).")";
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_nhannoibo` 
			".$having."";
		
		$qr = $dbAdapter->query($sql);
		$re = $qr->fetch();
		
		if($re["DEM"] >0 )
			return 0;
		return 1;
    	
    	
    	
    }
    
    static function checkAllDataSQL($year,$arr_value){
    	//dinh dang arr_value
    	
    	$arr_colum = Common_Sovanban::getColumNameGroup(1);
    	array_push($arr_colum,'SODEN');
    	$arr_colum2 = array(
    	'SOKYHIEU','NGAYBANHANH','ID_CQ');
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE (" .$having.implode(' and ',$arr_where).")";
			$arr_where = array();
			foreach($arr_colum2 as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having =$having. " OR (" .implode(' and ',$arr_where).")";
		}
		
		
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_nhannoibo` 
			".$having."";
		
		return $sql;
    	
    	
    }
    
    static function checkSoKyHieuSql($year,$arr_value){
    	$arr_colum = array(
    	'SOKYHIEU','NGAYBANHANH','ID_CQ'
    	);
		$having = "";
		$str_gr = " GROUP BY ";
		if(count($arr_colum) >0 ){ // co it nhat mot cot
			$str_gr = $str_gr . implode(',',$arr_colum);
			$arr_where = array();
			foreach($arr_colum as $col_item){
					$arr_i = $col_item."="."'$arr_value[$col_item]'";
					array_push($arr_where,$arr_i);
			}
			$having = " WHERE " .$having.implode(' and ',$arr_where);
		}
		$sql = "
			SELECT COUNT(*) as DEM
			FROM `vbd_nhannoibo` 
			".$having."";
		return $sql;
    }
    
    static function getListVanbandiByIdVbden($id_vbden,$year){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$sql = "select * from 
    		`vbdi_nhannoibo` vbdi
    		inner join `vbd_fk_vbden_hscvs_$year` fk_vbden_hscvs on vbdi.`ID_HSCV`=fk_vbden_hscvs.`ID_HSCV`
    		where fk_vbden_hscvs.`ID_VBDEN`=?	
    	";
    	
    	try{
    		$stm = $dbAdapter->query($sql,array($id_vbden));
    		$re = $stm->fetchAll();
    		return $re;
    	}catch (Exception $ex){
    		return array();
    	}
    	
    	
    }
 static function getcq($cqn){
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$sql = "select NAME from vb_coquan where `ID_CQ`=?";
    	
        try{
    		$stm = $dbAdapter->query($sql,array($cqn));
    		$re = $stm->fetch();
    		return $re["NAME"];
    	}catch (Exception $ex){
    		return array();
    	}
    	
    }
 static function GetDataCQN_ID(){
    	 $dbAdapter = Zend_Db_Table::getDefaultAdapter();      
       
       $sql="SELECT
                ID_CQ
            FROM
                vb_coquan
            WHERE
               
                ISSYSTEMCQ = 1          
        ";
      
     try{
    		$stm = $dbAdapter->query($sql);
    		$re = $stm->fetch();
    		
    		return $re["ID_CQ"];
    	}catch (Exception $ex){
    		return array();
    	}
    	
    }   

	static function GetSVBByCoQuanNhan($id_cq){
		global $auth;
		$user = $auth->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();      
		$sql="SELECT
					ID_SVB , NAME
				FROM
					vb_sovanban
				WHERE
				   TYPE=1 and YEAR=? and (ID_CQ = ? or ID_CQ is NULL or ID_CQ=0 ) and ID_DEP = ".$user->ID_DEP."
			";
		  
		 //try{
				$stm = $dbAdapter->query($sql,array(QLVBDHCommon::getYear(),$id_cq));
				return $stm->fetchAll();
			//}catch (Exception $ex){
			//	return array();
			//}
			
    }   
	static function GetLastBP($id,$idu){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();      
		$sql="SELECT
					*
				FROM
					".QLVBDHCommon::Table("HSCV_BUTPHE")."
				WHERE
				   ID_VBD = ? AND NGUOIKY = ?
				ORDER BY ID_BP DESC
			";
		$stm = $dbAdapter->query($sql,array($id,$idu));
		return $stm->fetch();
	}

}