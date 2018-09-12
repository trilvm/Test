<?php

class Lichcttext_TableModel extends Zend_Db_Table_Abstract{
    //var $_name = 'lct2_lead_manage_2012';
    function addCtManage($data){
            $year = QLVBDHCommon::getYear();
           
            //$table = new Zend_Db_Table_Row_Abstract();
            
            $set = $this->getAdapter()->prepare('INSERT INTO lct2_lead_manage_'.$year.' (NCT_NAME) VALUES(?)');
            
            for($i=0;$i<count($data);$i++){
                if($data[$i]!=''){
                    
                    try {
                         $set->execute(array($data[$i])); 

                        } catch (Exception $exc) {
                            echo $exc->getTraceAsString();
                        }

                }
            }
            
        }
}