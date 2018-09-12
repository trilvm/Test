<?php

require_once 'Zend/Db/Table/Abstract.php';

class VersionModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'version_detail';
        
        public function insertfile($data)
        {    
            
           $this->insert($data);
        }
        
         public function deletefile($id)
        {    
         
           $this->delete("ID_VERSION=$id");
        }
        public function selectversion($id_ver,$id_donvi)
        {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $select = $dbAdapter->select('ID_VERSION, IS_UPDATE, FILE_MASO, PATH','FILE_NAME')
                    ->from($this->_name)
                    ->where('ID_VERSION=?',$id_ver)
                    ->where('ID_DONVI=?',$id_donvi);
            $stmt = $select->query();
            $result = $stmt->fetchAll();
            return $result;
        }
        
        public function deletefileversion($id_ver,$id_donvi,$masofile)
        {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $where=sprintf("ID_DONVI='%s' and ID_VERSION= %s and MASO_FILE='%s' and IS_UPDATE=0",$id_donvi,$id_ver,$masofile);
            $delete=$dbAdapter->delete($this->_name, $where) ;
        }
        
        public function updateversion($id_ver,$id_donvi)
        {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $where=sprintf("ID_DONVI='%s' and ID_VERSION= %s ",$id_donvi,$id_ver);
            $update=$dbAdapter->update($this->_name, array('IS_UPDATE'=>0), $where);
        }
        
         public function selectmasofile($id_ver,$id_donvi)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $select = new Zend_Db_Select($db);
            $select->from($this->_name, array('MASO_FILE','PHAN_HE'))
                    ->where('ID_VERSION=?',$id_ver)
                    ->where('ID_DONVI=?',$id_donvi);
            $result = $db->query($select)->fetchAll();
            return $result;
        }
}

?>
