<?php

class Qtht_PhuchoiModel extends Zend_Db_Table_Abstract {

    var $_name = 'qtht_restore';
    
    public function getData($limit, $page, $condition) {

        $sql = sprintf("
            SELECT 
                RESTORE_ID,
                RESTORE_DATE,
                RESTORED,
                RESTORED_DATE,
                CONTENT
            FROM qtht_restore 
            WHERE 1=1 %s
            ORDER BY RESTORE_DATE DESC
            LIMIT %d,%d
            
        ", $condition, (($page - 1) * $limit), $limit);
        // return $sql;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $query = $dbAdapter->query($sql);
        return $query->fetchAll();
    }
   

    function count($condition) {
        $sql = sprintf("
            SELECT *
            FROM qtht_restore
            WHERE 1=1 %s
            
        ", $condition);
        // return $sql;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $query = $dbAdapter->query($sql);
        return $query->rowCount();
    }
    
    function findFileName($id){
        $sql = sprintf("
        SELECT FILE_NAME,FILE_SIZE,FILE_EXTENSION,RESTORE_ID
        FROM qtht_restore_file
        WHERE RESTORE_ID = %d
        ",$id);
        //return $sql;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $query = $dbAdapter->query($sql);
        return $query->fetchAll();
    }
    function findID($id){
        $sql = sprintf("
        SELECT RESTORE_ID,RESTORE_DATE,RESTORED,RESTORED_DATE,CONTENT
        FROM qtht_restore
        WHERE RESTORE_ID = %d
        ",$id);
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $query = $dbAdapter->query($sql);
        return $query->fetchAll();
    } 
    
    
}