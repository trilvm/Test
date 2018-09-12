<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class qlcuochop_DMdiadiemModel extends Zend_Db_Table{
    public $_name = 'dm_diadiem';

    public function SelectAll(){
        $db= Zend_Db_Table::getDefaultAdapter();

        $sql = "select * from " . $this->_name;
        $r = $db->query($sql)->fetchAll();
        return $r;
    }

    public function save($id,$ten){
        $db= Zend_Db_Table::getDefaultAdapter();
        try{
            if ($id == 0) {
                $db ->insert($this->_name,array(
                    'TEN' =>$ten
                ));
            }  else {
                $db->update($this->_name, array(
                    'TEN'=>$ten
                ), "ID_DIADIEM='".$id."'");
            }
        }  catch (Exception $e){
            echo $e->__toString();
        }
    }

    static function toCombo($formname,$select){
        $db=Zend_Db_Table::getDefaultAdapter();
        $sql="select * from dm_diadiem";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='diadiem' style='width: 225px;'>";
        echo "<option value='0'>-- Chọn địa điểm họp--</option>";
        foreach ($r as $item){
            echo "<option value='".$item['ID_DIADIEM']."'>".$item['TEN']."</option>";
        }
        echo "</select>";
        echo "<script>document.$formname.diadiem.value='".$select."'</script>";
    }
    static function toCombo2($formname,$select){
        $db=Zend_Db_Table::getDefaultAdapter();
        $sql="select * from dm_diadiem";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='diadiem2' style='width: 225px;'>";
        echo "<option value=''>-- Chọn địa điểm họp--</option>";
        foreach ($r as $item){
            echo "<option value='".$item['ID_DIADIEM']."'>".$item['TEN']."</option>";
        }
        echo "</select>";
        echo "<script>document.$formname.diadiem2.value='".$select."'</script>";
    }

     public function checkInput($name) {
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = "select * from $this->_name where TEN='".$name."'" ;
        $stmt = $db->query($sql);
        $r = $stmt->fetch();
        if ($r)
            return 1;
        else return 0;
    }    
}
?>
