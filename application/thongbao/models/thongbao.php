<?php

/**
 * iso
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class thongbao extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'tb_thongbao';
	/**
     * Select toàn bộ dữ liệu
     */
    public function SelectAll($offset,$limit,$order){
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and TIEUDE like ?";
        }
        
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
            SELECT
                *,CONCAT(FIRSTNAME,' ',LASTNAME) as NGUOITAONAME
            FROM
                $this->_name
				INNER JOIN
					qtht_users u on tb_thongbao.nguoitao = u.id_u
				INNER JOIN
					qtht_employees e on u.id_emp = e.id_emp
            WHERE
                $strwhere
            $strorder
            $strlimit
        ",$arrwhere);
        return $result->fetchAll();
    }
	public function SelectAllVisible($offset,$limit,$order){
        //Build phần where
        $arrwhere = array();
        $strwhere = "NGAYKETTHUC >= CURDATE()";
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and TIEUDE like ?";
        }
        
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
            SELECT
                *,CONCAT(FIRSTNAME,' ',LASTNAME) as NGUOITAONAME
            FROM
                $this->_name
				INNER JOIN
					qtht_users u on tb_thongbao.nguoitao = u.id_u
				INNER JOIN
					qtht_employees e on u.id_emp = e.id_emp
            WHERE
                $strwhere
            $strorder
            $strlimit
        ",$arrwhere);
        return $result->fetchAll();
    }
    /**
     * Đếm số bản ghi có trong table
     */
	 public function SelectById($id){
		$result = $this->getDefaultAdapter()->query("
            SELECT
                *,CONCAT(FIRSTNAME,' ',LASTNAME) as NGUOITAONAME
            FROM
                $this->_name
				INNER JOIN
					qtht_users u on tb_thongbao.nguoitao = u.id_u
				INNER JOIN
					qtht_employees e on u.id_emp = e.id_emp
            WHERE
                ID_TB = ?
        ",$id);
        return $result->fetch();
	}

    public function Count(){
        $r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
        return $r["C"];
    }
}
