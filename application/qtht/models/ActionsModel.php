<?php
/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class ActionsModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'qtht_actions';
    var $_id_m = 0;
    var $_search = "";    
    
    public function SelectAll($offset,$limit,$order){
        // Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($this->_id_m > 0){
            $arrwhere[] = $this->_id_m;
            $strwhere .= " and ID_MOD = ?";
        }
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and NAME like ?";
        }
        
        // Build phần limit
        $strlimit = "";
        if($limit>0){
            $strlimit = " LIMIT $offset,$limit";
        }
        
        // Build order
        $strorder = "";
        if($order!=""){
            $strorder = " ORDER BY $order";
        }
        // Thực hiện query
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
     * Chuyển dữ liệu tới combobox
     */
    static function ToCombo($data,$sel){
        $html="";
        $html .= "<option value='0'>".MSG11001006."</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_ACT"]."' ".($row["ID_ACT"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }
    /**
     * Đếm số bản ghi có trong table
     */
    public function Count(){
        // Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($this->_id_m > 0){
            $arrwhere[] = $this->_ID_MOD;
            $strwhere .= " and ID_MOD = ?";
        }
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and NAME like ?";
        }
        
        // Thực hiện query
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
}
