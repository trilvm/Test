<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tinhhinhxulyModel
 *
 * @author phuongpt
 */
class tinhhinhxulyModel extends Zend_Db_Table_Abstract {
    
    protected $_name = "hscv_hosocongviec_2013";
    
    static function SelectAllChoxuly($OBJECT_CXL, $id_hscv_trehan,$page,$limit,$order){
		global $db;
                global $auth;
		$user = $auth->getIdentity();
                $in = " AND hscv.ID_HSCV IN (".$id_hscv_trehan.")";
                $limit=" limit $page, $limit ";
                $order = " ORDER BY hscv.ID_HSCV";
                $sql = "SELECT *,hscv.`ID_HSCV` as MAHSCV 
                        FROM ".QLVBDHCommon::table("hscv_hosocongviec")." hscv 
                        left join ".QLVBDHCommon::table("hscv_congviecsoanthao")." cv on cv.`ID_HSCV`=hscv.`ID_HSCV` 
                        inner join ".QLVBDHCommon::table("wf_processitems")." wfitem on hscv.ID_PI = wfitem.ID_PI 
                        INNER JOIN WF_PROCESSES wfp on wfitem.ID_P = wfp.ID_P 
                        INNER JOIN WF_CLASSES class on class.ID_C = wfp.ID_C 
                        inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV 
                        WHERE (1=1) and (IS_THEODOI<>1) and ( hscv.IS_CHOXULY = 0 OR hscv.IS_CHOXULY is NULL ) 
                        and IS_DXCHOXL <> 1 and hscv.ID_THUMUC = 1 
                        ";
                if($OBJECT_CXL == "VBD")
                {
                    $sql .= " AND class.ALIAS = 'VBD'";
                } else {
                    $sql .= " AND class.ALIAS = 'VBSOANTHAO'";
                }
                $sql = $sql.$in.$order.$limit;
               
		try{
			
			$r = $db->query($sql,$param);
			$result = $r->fetchAll();
			
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
    }
    
	public function Count_SelectAllDangXulyBT($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND >= NOW()";
		$params = array();
		$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($limit>0){
                $limit = " LIMIT $offset,$limit";
        }
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result["CNT"];
		
	}

	public function SelectAllDangXulyBT($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND >= NOW()";
		$order = " ORDER BY hscv.ID_HSCV";
		$params = array();
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($limit>0){
                $limit = " LIMIT $offset,$limit";
        }
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where.$order.$limit;
		$result = $dbAdapter->query($sql, $params)->fetchAll();
		return $result;
		
	}
	
	public function SelectAllDangXulyTH($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND < NOW()";
		$order = " ORDER BY hscv.ID_HSCV";
		$inner = "";
		$params = array();
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($limit>0){
                $limit = " LIMIT $offset,$limit";
        }
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where.$order.$limit;
		$result = $dbAdapter->query($sql, $params)->fetchAll();
		return $result;
	}

	public function Count_SelectAllDangXulyTH($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND < NOW()";
		$inner = "";
		$params = array();
		$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}

		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result["CNT"];
	}
	
	public function Count_SelectAllDaXulyBT($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 0 ";
		$inner = "";
		$params = array();
		$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result["CNT"];
	}

	public function SelectAllDaXulyBT($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 0 ";
		$order = " ORDER BY hscv.ID_HSCV";
		$inner = "";
		$params = array();
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($limit>0){
			$limit = " LIMIT $offset,$limit";
        }
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where.$order.$limit;
		$result = $dbAdapter->query($sql, $params)->fetchAll();
		return $result;
	}
	
	public function Count_SelectAllDaXulyTH($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 1 ";
		$inner = "";
		$params = array();
		$sql = "SELECT count(*) as CNT FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result["CNT"];
	}

	public function SelectAllDaXulyTH($idUser, $idDep, $dateBegin, $dateEnd, $object_xl, $offset,$limit)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 1 ";
		$order = " ORDER BY hscv.ID_HSCV";
		$inner = "";
		$params = array();
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
				INNER JOIN ".QLVBDHCommon::Table("wf_processitems")." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.`ID_P`
				INNER JOIN `wf_classes` cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		if($limit>0){
            $limit = " LIMIT $offset,$limit";
        }
		if($object_xl == 'VBD') {
			$where .= " AND cl.`ALIAS` = 'VBD'";
		} else if($object_xl == 'VBSOANTHAO') {
			$where .= " AND cl.`ALIAS` = 'VBSOANTHAO'";
		}
		$sql .= $where.$order.$limit;
		$result = $dbAdapter->query($sql, $params)->fetchAll();
		return $result;
	}
}

?>
