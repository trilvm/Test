<?php

/**
 * phanquyenhscvModel
 *  
 * @author hieuvt
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';

class phanquyenhscvModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'HSCV_PHANQUYEN_HSCV_2013';
	var $_id_hscv = 0;
	/**
	 * construct
	 * @param $year
	 */
	function __construct($year){
		if(isset($year))
			$this->_name ='HSCV_PHANQUYEN_HSCV_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
    /**
	 * Lấy danh sách Group có quyền Đọc
	 *
	 * @return dataset
	 */
	function GetAllGroups(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			PQHSCV.SEL,SUM(PQHSCV.truyxuat) AS TRUYXUAT,SUM(PQHSCV.chinhsua) AS CHINHSUA,G.ID_G,G.NAME
			FROM
				QTHT_GROUPS G
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'chinhsua') as chinhsua
				     FROM
				         ".QLVBDHCommon::Table("HSCV_PHANQUYEN_HSCV")."
				     WHERE
				          ID_HSCV=?
				     ) PQHSCV ON PQHSCV.ID_G = G.ID_G
		    GROUP BY
				PQHSCV.SEL,G.ID_G,G.NAME
			ORDER BY 
			    G.NAME
		",array($this->_id_hscv));
		return $r->fetchAll();
	}
	/**
	 * Lấy danh sách user
	 *
	 * @return dataset
	 */
	function GetAllUsers(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			PQHSCV.SEL,SUM(PQHSCV.truyxuat) AS TRUYXUAT,SUM(PQHSCV.chinhsua) AS CHINHSUA,U.ID_U,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME,' (',U.USERNAME,')') AS NAME 
			FROM
				QTHT_USERS U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'chinhsua') as chinhsua
				     FROM
				         ".QLVBDHCommon::Table("HSCV_PHANQUYEN_HSCV")."
				     WHERE
				          ID_HSCV=?
				     ) PQHSCV ON PQHSCV.ID_U = U.ID_U
		    GROUP BY
				PQHSCV.SEL,U.ID_U,NAME
			ORDER BY 
			    NAME
		",array($this->_id_hscv));
		return $r->fetchAll();
	}
    /**
	 * Lấy danh sách phòng ban
	 *
	 * @return dataset
	 */
	function GetAllDepartments(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			PQHSCV.SEL,SUM(PQHSCV.truyxuat) AS TRUYXUAT,SUM(PQHSCV.chinhsua) AS CHINHSUA,DEP.ID_DEP,DEP.NAME
			FROM
				QTHT_DEPARTMENTS DEP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'chinhsua') as chinhsua
				     FROM
				         ".QLVBDHCommon::Table("HSCV_PHANQUYEN_HSCV")."
				     WHERE
				          ID_HSCV=?
				     ) PQHSCV ON PQHSCV.ID_DEP = DEP.ID_DEP
                        WHERE ACTIVE=1
		    GROUP BY
				PQHSCV.SEL,DEP.ID_DEP,DEP.NAME
			ORDER BY 
			    DEP.NAME
		",array($this->_id_hscv));		
		return $r->fetchAll();
	}
    /**
     * Lấy toàn bộ hồ sơ công việc thuộc thư mục cho trước và năm xác định
     */
    public function GetAllHscvByYear($id_thumuc,$year){    	
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($id_thumuc != ""){
            $arrwhere[] = $id_thumuc;
            $strwhere .= " and ID_THUMUC = ?";
        }
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *
            FROM
                hscv_hosocongviec_ . $year
            WHERE
                $strwhere
        ",$arrwhere);
        return $result->fetchAll();
    }
    /**
	 * lấy tất cả các quyền của thư mục này
	 *
	 * @return dataset
	 */
	function getAllPermission($id_thumuc){
		if(!isset($id_thumuc))
			$id_thumuc = $this->_id_thumuc;
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			* 
			FROM
				HSCV_PHANQUYEN_THUMUC PQTM
			WHERE
				PQTM.ID_THUMUC = ?
		",array($id_thumuc));
		return $r->fetchAll();
	}
    /**
	 * sao chép tất cả quyền của thư mục nguồn sang hồ sơ công việc
	 *
	 * @return 
	 */
	function copyPermission($id_thumuc_nguon,$id_hscv){
		try{
			// lấy tất cả các quyền của thư mục nguồn
			$re = $this->getAllPermission($id_thumuc_nguon);
			// xóa tất cả các quyền nếu đã tồn tại của thư mục đích
			$this->delete("ID_HSCV=".(int)$id_hscv);
			foreach ($re as $row){			
				// thêm mới quyền vào thư mục đích được lấy từ thư mục nguồn
				$this->insert(array("ID_THUMUC"=>$id_thumuc_dich,"ID_G"=>$row['ID_G'],"ID_DEP"=>$row['ID_DEP'],"ID_U"=>$row['ID_U'],"QUYEN"=>$row['QUYEN']));
			}
		}
		catch (Exception $ex){
		    return false;
		}
		return true;
	}
    /**
	 * hợp tất cả quyền của thư mục nguồn sang hồ sơ công việc
	 * các quyền cũ của hồ sơ công việc vẫn giữ nguyên và thêm vào các quyền của thư mục nguồn nếu không có
	 * @return 
	 */
	function mergePermission($id_thumuc_nguon,$id_hscv){
		try{
			// lấy tất cả các quyền của thư mục nguồn
			$re = $this->getAllPermission($id_thumuc_nguon);			
			foreach ($re as $row){			
				try{
					//
					if($row['QUYEN']=='taomoi')
					    $quyen = 'chinhsua';
					        else
					    		$quyen = $row['QUYEN'];         
					// thêm mới quyền vào thư mục đích được lấy từ thư mục nguồn
					$this->insert(array("ID_HSCV"=>$id_hscv,"ID_G"=>$row['ID_G'],"ID_DEP"=>$row['ID_DEP'],"ID_U"=>$row['ID_U'],$quyen));
				}
				catch(Exception $ex){
				}
			}
		}
		catch (Exception $ex){
		    return false;
		}
		return true;
	}
}
