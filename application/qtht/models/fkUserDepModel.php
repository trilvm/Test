<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fkUserDepModel
 *
 * @author phuongpt
 */
class fkUserDepModel {
    
    protected $_name = "fk_user_dep";
    
    public function updateData($id, $where)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "UPDATE fk_user_dep
                SET ID_U = {$id}
                WHERE ID_D = {$where}
                ";
        $dbAdapter->query($sql);
    }
    
    public function insertData($id_u, $id_d)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "INSERT INTO fk_user_dep(ID_U, ID_D) VALUES(?,?)";
        $dbAdapter->query($sql,array($id_u,$id_d));
    }
    
    public function selData()
    {
        
    }
}

?>
