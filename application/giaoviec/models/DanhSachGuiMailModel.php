<?php
require_once 'Zend/Db/Table/Abstract.php';

class DanhSachGuiMailModel extends Zend_Db_Table_Abstract {

    var $_name = "gscv_danhsachguimail";
    
    
    function checkExitsLoaiCongViec($code){
        global $db;
        $sqlselect ="SELECT * from gscv_danhsachguimail";
        $query=$db->query($sqlselect);
        $r = $query->fetchAll();
        $is_dup_code =false;
        foreach ($r as $item) {            
            if ($code == $item['MADONVI']) {
                $is_dup_code = true;
            }
        }
        return $is_dup_code;
    }
    
   

    public static function SelectAll($offset=0,$limit=0,$order="") {
        global $db;
        
        //Build phần limit
        $strlimit = "";
        if($limit>0){
                $strlimit = " LIMIT $offset,$limit";
        }

        //Build order
        $strorder = "";
        if($order!="" || $order){
            $strorder = " ORDER BY $order";
        }
        
        $sql ="SELECT * from gscv_danhsachguimail ".$strlimit." ".$strorder ;
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
    
    public static function getLoaiCongViecGiao(){
        global $db;
        $sql ="SELECT * from gscv_danhsachguimail where ACTIVE =1";
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
    
    public static function getDonVi($madonvi){
        global $db;
        $sql ="SELECT * from gscv_danhsachguimail where MADONVI = '" .$madonvi. "' ";
        $query = $db->query($sql);
        $r = $query->fetchAll();       
        return $r;
    }
    static function WriteDonVi($sel)
    {
        global $auth;
        $des = " -- Chọn đơn vị -- ";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            select *,TRIM(BOTH '/' FROM concat(IFNULL(KYHIEU,''),'/',IFNULL(NAME,''))) as NAMEKYHIEU from vb_coquan where ISSYSTEMCQ = 2 order by NAME ASC ");
      
        $data = $result->fetchAll();

        $html = "";
        $html .= "<option value='0'>" . $des . "</option>";
        foreach ($data as $row) {
            $html .= "<option value='" . $row["CODE"].'|'.$row["NAME"] . "' " . ($row["CODE"] == $sel ? "selected" : "") . ">" . $row["NAME"] . "</option>";
        }
        return $html;
    }
    
}
