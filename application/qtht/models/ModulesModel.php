<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class ModulesModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'qtht_modules';
    var $_search = "";
    /**
     * Select toàn bộ dữ liệu
     */
    public function SelectAll($offset,$limit,$order){
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and (NAME like ?";
			$arrwhere[] = "%".$this->_search."%";
            $strwhere .= " or URL like ?)";
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
        $html .= "<option value='0'>".MSG11011004."</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_MOD"]."' ".($row["ID_MOD"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
}