<?php

class Backup_SaoluuModel extends Zend_Db_Table_Abstract{
    
    var $_name = 'qtht_backup';
    
    public function getAll($limit,$page){
        
            $sql = sprintf("
                SELECT 
                    BACKUP_ID,
                    FILE_NAME,
                    FILE_SIZE,
                    DESCRIPTION,
                    BACKUP_DATE,
                    RESTORE_REQUEST,
                    RESTORE_REQUEST_DATE,
                    RESTORED,
                    RESTORED_DATE
                FROM qtht_backup
            ");
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $query = $dbAdapter->query($sql);
            return $query->fetchAll();
    }
}
