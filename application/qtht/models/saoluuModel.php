<?php

class Qtht_SaoluuModel extends Zend_Db_Table_Abstract {

    var $_name = 'qtht_backup';

    function getData($limit,$page,$condition){
        
            //build query
            $sql = sprintf("
            SELECT
                ID,
                FILE_NAME,
                FILE_SIZE,
                BACKUP_DATE,
                CONTENT,
                FOLDER,
                IS_OK
            FROM qtht_backup
            WHERE 0=0 %s
            ORDER BY BACKUP_DATE DESC
            LIMIT %d,%d

        ", $condition, (($page - 1) * $limit), $limit);
        $result = $this->getAdapter()->query($sql);
        return $result->fetchAll();
    }
    
    function count($condition){
        
        
            //build query
        $sql = sprintf("
        SELECT *
        FROM qtht_backup
        WHERE 0=0 %s
        ORDER BY BACKUP_DATE DESC
        ", $condition);
        $result = $this->getAdapter()->query($sql);
        return $result->rowCount();
    }
    
    function saveBU($parameters) {
        $params = explode('~', $parameters);
        $BACKUP_IDS = $params[0];
        $DESRS = $params[1];
        $bk_ids = explode(',', $BACKUP_IDS);
        $desrs = explode('#', $DESRS);
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter()->prepare('INSERT INTO qtht_restore (QTHT_BACKUP_ID, RESTORE_DATE, DESCRIPTION) VALUES (?, ?, ?)');
        
        $i = 0;
        foreach ($bk_ids as $bk_id) {
            $dbAdapter->execute(array($bk_id,date('Y-m-d H:i:s'),$desrs[$i]));
            $i++;
        }
        return 1;
       
    }
    
    function checkMiss() {
        $sql = sprintf("
            SELECT *
            FROM qtht_backup_request
            WHERE DISMISS = 0
        ");
        // return $sql;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $query = $dbAdapter->query($sql);
        return $query->rowCount();
    }
    function dismiss($id){
        $sql = sprintf("
        UPDATE qtht_backup_request  
        SET DISMISS = 1
        WHERE ID = %d
        ",$id);
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        return $dbAdapter->query($sql);
    }

}
