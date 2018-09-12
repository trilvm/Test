<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class GoiyModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'qtht_goiy';
    var $_search = "";
    /**
     * Select toàn bộ dữ liệu
     */
    public function SelectAll($offset,$limit,$order){
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        
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
                *
            FROM
                $this->_name
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
    public function Count(){
        $r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
        return $r["C"];
    }
    /**
     * Chuyển dữ liệu tới combobox
     */
    static function ToCombo($data,$sel){
        $html="";
        $html .= "<option value=''>--Chọn nội dung bút phê--</option>";
        foreach($data as $row){
            $html .= "<option value='".str_replace("'","\\'",htmlspecialchars($row["NOIDUNG"]))."' ".($row["ID_GOIY"]==$sel?"selected":"").">".htmlspecialchars($row["NOIDUNG"])."</option>";
        }
        return $html;
    }
	public function FindById($id_goiy){
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				*
			FROM
				$this->_name
			WHERE
				ID_GOIY = ?
		",$id_goiy);
		return $result->fetch();
	}
}
