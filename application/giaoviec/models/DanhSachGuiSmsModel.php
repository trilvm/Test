<?php
require_once 'Zend/Db/Table/Abstract.php';

class DanhSachGuiSmsModel extends Zend_Db_Table_Abstract {

    var $_name = "gscv_danhsachguisms";

    public static function SelectAll( $limit=0,$order="",$donvi) {
        global $db;
        
        //Build phần limit
        $strlimit = "";
        if($donvi>0){
                $where = " ID_DONVI = $donvi";
        }else{
            $where = " 1=1 ";
        }
        
        if($limit>0){
                $strlimit = " LIMIT $offset,$limit";
        }
        //Build order
        $order = ' ID DESC ';
        $strorder = "";
        if($order!="" || $order){
            $strorder = " ORDER BY $order";
        }
        $sql ="SELECT * from gscv_danhsachguisms WHERE ".$where." ".$strorder." ".$strlimit ;
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
    
    static function WriteDonVi($sel)
    {
        global $auth;
        $des = " -- Chọn đơn vị -- ";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                gscv_danhsachguimail ");
      
        $data = $result->fetchAll();

        $html = "";
        $html .= "<option value='0'>" . $des . "</option>";
        foreach ($data as $row) {
            $html .= "<option value='" . $row["ID"] . "' " . ($row["ID"] == $sel ? "selected" : "") . ">" . $row["TENDONVI"] . "</option>";
        }
        return $html;
    }

}
