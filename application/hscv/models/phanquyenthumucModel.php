<?php

/**
 * phanquyenthumucModel
 *  
 * @author hieuvt
 * @version 1.0
 */

require_once 'Zend/Db/Table/Abstract.php';

class phanquyenthumucModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	var $_name = 'hscv_phanquyen_thumuc';
	var $_id_thumuc = 0;
	/**
	 * Lấy danh sách Group có quyền Đọc
	 *
	 * @return dataset
	 */
	function GetAllGroups(){
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			PQTM.SEL,SUM(PQTM.truyxuat) AS TRUYXUAT,SUM(PQTM.taomoi) AS TAOMOI,SUM(PQTM.phanquyen) AS PHANQUYEN,G.ID_G,G.NAME
			FROM
				QTHT_GROUPS G
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'taomoi') as taomoi, (QUYEN = 'phanquyen') as phanquyen
				     FROM
				         HSCV_PHANQUYEN_THUMUC
				     WHERE
				          ID_THUMUC=?
				     ) PQTM ON PQTM.ID_G = G.ID_G
		    GROUP BY
				PQTM.SEL,G.ID_G,G.NAME
			ORDER BY 
			    G.NAME
		",array($this->_id_thumuc));		
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
     			PQTM.SEL,SUM(PQTM.truyxuat) AS TRUYXUAT,SUM(PQTM.taomoi) AS TAOMOI,SUM(PQTM.phanquyen) AS PHANQUYEN,U.ID_U,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME,' (',U.USERNAME,')') AS NAME 
			FROM
				QTHT_USERS U
				INNER JOIN QTHT_EMPLOYEES EMP ON U.ID_EMP = EMP.ID_EMP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'taomoi') as taomoi, (QUYEN = 'phanquyen') as phanquyen
				     FROM
				         HSCV_PHANQUYEN_THUMUC
				     WHERE
				          ID_THUMUC=?
				     ) PQTM ON PQTM.ID_U = U.ID_U
		    GROUP BY
				PQTM.SEL,U.ID_U,NAME
			ORDER BY 
			    NAME
		",array($this->_id_thumuc));
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
     			PQTM.SEL,SUM(PQTM.truyxuat) AS TRUYXUAT,SUM(PQTM.taomoi) AS TAOMOI,SUM(PQTM.phanquyen) AS PHANQUYEN,DEP.ID_DEP,DEP.NAME
			FROM
				QTHT_DEPARTMENTS DEP
				LEFT JOIN (
				     SELECT
				           *, 1 AS SEL, (QUYEN = 'truyxuat') as truyxuat, (QUYEN = 'taomoi') as taomoi, (QUYEN = 'phanquyen') as phanquyen
				     FROM
				         HSCV_PHANQUYEN_THUMUC
				     WHERE
				          ID_THUMUC=?
				     ) PQTM ON PQTM.ID_DEP = DEP.ID_DEP
                        WHERE ACTIVE=1
		    GROUP BY
				PQTM.SEL,DEP.ID_DEP,DEP.NAME
			ORDER BY 
			    DEP.NAME
		",array($this->_id_thumuc));		
		return $r->fetchAll();
	}
    /**
	 * kiểm tra user, group hoặc phòng ban có quyền truy cập, thêm mới hay phân quyền cho thư mục này hay không
	 *
	 * @return dataset
	 */
	function checkPermission($id=NULL,$type_id=NULL,$id_thumuc=NULL,$type_quyen=NULL){
		$isPermission=FALSE;
		if($type_id==NULL)
		    $type_id='user';
		if($id==NULL)
		    if($type_id=='user')
		        $id=Zend_Registry::get('auth')->getIdentity()->ID_U;
		            else if($type_id=='group')
		                $id='id of default group';
		                    else if($type_id=='department')
		                        $id='id of default department';
		if($id_thumuc==NULL)
		    $id_thumuc=$this->_id_thumuc;
		if($type_quyen==NULL)
		    $type_quyen='truycap';
		
		$str_where="ID_THUMUC=? AND QUYEN=?";
		 if($type_id=='user')
		     $str_where.='AND ID_U=?';
		         else if($type_id=='group')
		             $str_where.='AND ID_G=?';
		                 else if($type_id=='department')
		                     $str_where.='AND ID_DEP=?';	
        $strlimit = "LIMIT 1";
		$sql="
			SELECT
			     *, 1 AS SEL
		    FROM
			     HSCV_PHANQUYEN_THUMUC
			WHERE
			     $str_where
			$strlimit	     
		";     
		
		$r = $this->getDefaultAdapter()->query($sql,array($id_thumuc,$type_quyen,$id))->fetch();		
		return $isPermission=$r["SEL"];
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
	 * sao chép tất cả quyền của thư mục nguồn sang thư mục đích
	 *
	 * @return 
	 */
	function copyPermission($id_thumuc_nguon,$id_thumuc_dich){
		try{
			// lấy tất cả các quyền của thư mục nguồn
			$re = $this->getAllPermission($id_thumuc_nguon);
			// xóa tất cả các quyền nếu đã tồn tại của thư mục đích
			$this->delete("ID_THUMUC=".(int)$id_thumuc_dich);
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
	 * hợp tất cả quyền của thư mục nguồn sang thư mục đích
	 * các quyền cũ của thư mục đích vẫn giữ nguyên và thêm vào các quyền của thư mục nguồn nếu không có
	 * @return 
	 */
	function mergePermission($id_thumuc_nguon,$id_thumuc_dich){
		try{
			// lấy tất cả các quyền của thư mục nguồn
			$re = $this->getAllPermission($id_thumuc_nguon);			
			foreach ($re as $row){			
				try{
					// thêm mới quyền vào thư mục đích được lấy từ thư mục nguồn
					$this->insert(array("ID_THUMUC"=>$id_thumuc_dich,"ID_G"=>$row['ID_G'],"ID_DEP"=>$row['ID_DEP'],"ID_U"=>$row['ID_U'],"QUYEN"=>$row['QUYEN']));
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
