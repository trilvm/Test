<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class congviecsoanthaoModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'hscv_congviecsoanthao_2013';
    /**
	 * construct
	 * @param $year
	 */
	function __construct($year){
		if(isset($year))
			$this->_name ='hscv_congviecsoanthao_'.$year;			
		$arr = array();
		parent::__construct($arr);
	}
    /**
     * Lấy văn bản đi thông qua hồ sơ công việc
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
                *
            FROM
                $this->_name
            WHERE
                $strwhere
        ",$arrwhere);
       	$re = $result->fetchAll();
       	if(isset($re))
       		return $this->find($re[0]["ID_VBDI_CVST"])->current();
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
	
	static function getDetailByHSCV($year,$id_hscv){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select st.* , hscv.`NGAY_BD` ,hscv.`NGAY_KT` from 
		hscv_congviecsoanthao_$year st
		inner join hscv_hosocongviec_$year hscv on st.`ID_HSCV` = hscv.`ID_HSCV`
		where hscv.`ID_HSCV` =?
		";
		try{
			$stm = $dbAdapter->query($sql,array($id_hscv));
			return $stm->fetch();
		}catch(Exception $ex){
			return array();
		}
	}
}