<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Taphscv_thumucModel extends Zend_Db_Table_Abstract {

    var $_name = "hscvdt_thumuc_taphoso";

    function findByMixed($page, $limit, $name, $active) {
        $name = '%' . $name . '%';
        $arr = array($name, $active);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'ACTIVE = ?';
        $select = $this->select();
        $select->where($wherename, $name);
        if ($page != 0 && $limit != 0) {
            $select->limitPage($page, $limit);
        }
        if ($active >= 2 || $active < 0 || is_null($active)) {

        } else {
            $select->where($whereactive, $active);
        }
        return $this->fetchAll($select);
    }

    function findByName($name) {
        $name = '%' . $name . '%';
        $where = 'NAME LIKE ?';
        return $this->fetchAll($this->select()->where($where, array($name)));
    }

    function findByActive($active) {
        if ((!$active) || $active >= 2 || $active < 0) {
            return $this->fetchAll();
        }
        $where = 'ACTIVE=?';
        return $this->fetchAll($this->select()->where($where, array($active)));
    }

    function count($name, $active) {

        $name = '%' . $name . '%';
        $arr = array($name, $active);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'ACTIVE = ?';
        $select = $this->select();
        $select->from($this->_name, 'COUNT(*) AS num');
        $select->where($wherename, $name);
        if ($active >= 2 || $active < 0 || is_null($active)) {

        } else {
            $select->where($whereactive, $active);
        }
        $row = $this->fetchRow($select);
        return $row->num;
    }

    static function toComboFilter($filter_object) {
        if ($filter_object >= 2 || $filter_object < 0 || is_null($filter_object))
            echo '<option value=2  selected>Chọn tất cả</option>';
        else
            echo '<option value=2 >Chọn tất cả</option>';

        if ($filter_object == 1)
            echo '<option value=1 selected>Đang được sử dụng</option>';
        else
            echo '<option value=1>Đang được sử dụng</option>';

        if ($filter_object == 0 && !is_null($filter_object))
            echo '<option value=0 selected>Chưa được sử dụng</option>';
        else
            echo '<option value=0>Chưa được sử dụng</option>';
    }

    public function getNameById($id) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "select `NAME` from hscvdt_thumuc_taphoso
		where `ID_THUMUC` = ?
		";
        $query = $dbAdapter->query($sql, array($id));
        $re = $query->fetch();
        return $re["NAME"];
    }

    public function CheckBeforeDelete($id) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select tm1.* from `hscvdt_thumuc_taphoso` tm1
            left join `hscvdt_thumuc_taphoso` tm2 on tm2.`ID_THUMUC`=tm1.`ID_THUMUC_PARENT`
            left join `hscvdt_taphoso` ths on ths.`ID_THUMUC`=tm1.`ID_THUMUC`
            where ths.`ID_THUMUC`=$id
        ";
        $result = 0;
        try {
            if ($id > 0){
                $r = $db->query($sql)->fetchAll();
                if (count($r) > 0) $result = 1;
            }
        } catch (Exception $ex) {
            die ($ex->__toString());
        }
        return $result;
    }

    public function GetAllGroup($ID_THUMUC, $isPhanquyen) {
        $par = '';
        if ((int)$isPhanquyen == 0) {
            $par='where SEL=1';
        }
        $sql = "
            SELECT
            PT.SEL,G.`ID_G`,G.NAME,QUYENXEM,QUYENTHEMMOI,QUYENPHANQUYEN
            FROM
                    `QTHT_GROUPS` G
                    LEFT JOIN (
                         SELECT
                               ID_THUMUC,ID_G,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_thumuc_phanquyen`
                         WHERE
                              ID_THUMUC=?
                         GROUP BY ID_THUMUC,ID_G
                         ) PT ON PT.`ID_G` = G.`ID_G` ".$par." ORDER BY NAME";
        
        $r = $this->getDefaultAdapter()->query($sql, array($ID_THUMUC));
        return $r->fetchAll();
    }

   public function GetAllDep($ID_THUMUC, $isPhanquyen) {
       $par = '';
        if ((int)$isPhanquyen == 0) {
            $par='where SEL=1';
        }
        $sql="
            SELECT
            PT.SEL,DEP.`ID_DEP`,DEP.NAME,QUYENXEM,QUYENTHEMMOI,QUYENPHANQUYEN
            FROM
                    `QTHT_DEPARTMENTS` DEP
                    LEFT JOIN (
                         SELECT
                               ID_THUMUC,ID_DEP,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_thumuc_phanquyen`
                         WHERE
                              ID_THUMUC=?
                         GROUP BY ID_THUMUC,ID_DEP
                         ) PT ON PT.`ID_DEP` = DEP.`ID_DEP` ".$par." 
            AND DEP.ACTIVE=1
            ORDER BY ID_DEP_PARENT,NAME
        ";
        $r = $this->getDefaultAdapter()->query($sql, array($ID_THUMUC));
        return $r->fetchAll();
    }

   public function GetAllUser($ID_THUMUC, $isPhanquyen) {
       $par = '';
        if ((int)$isPhanquyen == 0) {
            $par='where SEL=1';
        }
        $sql = "
            SELECT
            PT.SEL,U.`ID_U`,concat(EMP.FIRSTNAME,' ',EMP.LASTNAME) as NAME,QUYENXEM,QUYENTHEMMOI,QUYENPHANQUYEN
            FROM
                    `QTHT_USERS` U
                    INNER JOIN QTHT_EMPLOYEES EMP on U.ID_EMP = EMP.ID_EMP
                    LEFT JOIN (
                         SELECT
                               ID_THUMUC,ID_U,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_thumuc_phanquyen`
                         WHERE
                              ID_THUMUC=?
                             GROUP BY ID_THUMUC,ID_U
                         ) PT ON PT.`ID_U` = U.`ID_U` ".$par." 
            ORDER BY EMP.LASTNAME
        ";
        $r = $this->getDefaultAdapter()->query($sql, array($ID_THUMUC));
        return $r->fetchAll();
    }

    public function PrePareTableBeforeUpdate($id){
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = array();
        $where[] = $db->quoteInto('ID_THUMUC = ?', $id);
        try {
            $db->delete("hscvdt_thumuc_phanquyen", $where);
        } catch (Exception $ex) {
            die ($ex->__toString());
        }
    }

    public function DeleteGroup($ID_THUMUC,$xem, $themmoi, $phanquyen){
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_G = ".$q." and XEM = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_G = ".$q." and CAPNHAT = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_G = ".$q." and PHANQUYEN = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }        
    }

    public function DeleteDep($ID_THUMUC,$xem, $themmoi, $phanquyen){
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_DEP = ".$q." and XEM = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_DEP = ".$q." and CAPNHAT = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_DEP = ".$q." and PHANQUYEN = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }        
    }

    public function DeleteUser($ID_THUMUC,$xem, $themmoi, $phanquyen){
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                try {                                        
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_U = ".$q." and XEM = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_U = ".$q." and CAPNHAT = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                try {
                    $db->delete("hscvdt_thumuc_phanquyen","ID_THUMUC = ".$ID_THUMUC." and ID_U = ".$q." and PHANQUYEN = 1");
                } catch (Exception $ex) {die ($ex->__toString());}
            }
        }        
    }

    function UpdateGroup($ID_THUMUC,$xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_G" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_G" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_G" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    function UpdateDep($ID_THUMUC,$xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_DEP" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_DEP" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_DEP" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    function UpdateUser($ID_THUMUC,$xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();        
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_U" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_U" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_thumuc_phanquyen", array("ID_THUMUC" => $ID_THUMUC, "ID_U" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    /**
     * @name Kiểm tra người dùng có quyền cập nhật thư mục không? (Xóa, sửa thư mục hscv)
     * @return 1:OK, 0 NOT OK
     */
    public function CheckUpdatePermissionByUser($user, $ID_THUMUC) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                select * from `hscvdt_thumuc_phanquyen` pq where pq.`CAPNHAT`=1 and (pq.`ID_U`=? or ID_G=? or ID_DEP=?) and pq.`ID_THUMUC`=?
            ";
        try {
            $r = $db->query($sql, array($user->ID_U, $user->ID_G, $user->ID_DEP, $ID_THUMUC))->fetch();
            if (!$r)
                return 0;
            else
                return 1;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

     public function CheckPhanQuyenPermissionByUser($user, $ID_THUMUC) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                select * from `hscvdt_thumuc_phanquyen` pq where pq.`PHANQUYEN`=1 and (pq.`ID_U`=? or ID_G=? or ID_DEP=?) and pq.`ID_THUMUC`=?
            ";
        try {
            $r = $db->query($sql, array($user->ID_U, $user->ID_G, $user->ID_DEP, $ID_THUMUC))->fetch();
            if (!$r)
                return 0;
            else
                return 1;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

    public function GetPermissionDetailByThumuc($ID_THUMUC) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select * from hscvdt_thumuc_phanquyen pq
            where pq.`ID_THUMUC`= ?
            ";
            
        try {
            $r = $db->query($sql, array($ID_THUMUC))->fetchAll();
        } catch (Exception $ex) {
            die ($ex->__toString());
        }
        return $r;
    }

    /**
     *  Lấy dữ liệu dựa trên id thư mục
     * @param int $ID_THUMUC
     * @return array
     */
    public function GetPermistionOfUsers($ID_THUMUC) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select pq.*, CONCAT(emps.`FIRSTNAME`,' ',emps.`LASTNAME`) as FULLNAME,
                groups.`NAME` as GROUPNAME, depts.`NAME` as DEPTNAME
            from `hscvdt_thumuc_phanquyen` pq
                left join `qtht_users` users on users.`ID_U`=pq.`ID_U`
                left join `qtht_employees` emps on emps.`ID_EMP`=users.`ID_EMP`
                left join `qtht_groups` groups on groups.`ID_G`=pq.`ID_G`
                left join `qtht_departments` depts on depts.`ID_DEP`=pq.`ID_DEP`
            where pq.`ID_THUMUC`=?
            ";
        try {
            $r = $db->query($sql, array($ID_THUMUC))->fetchAll();
        } catch(Exception $ex) {
            die ($ex->__toString());
        }
        return $r;
    }
    /**
    * Kiểm tra xem thư mục HSCVDT có quyền xem không
    * kiểm tra theo user,nhóm,dep
    **/
    public function isThuMucAllowedToView() {
        return true;
    }

    public function isThuMucAllowedToUpdate() {
        return true;
    }

    public function isThuMucAllowedToPermit() {
        return true;
    }

    public function GetParent(&$arrParent,$tablename,$FieldParentID,$FieldID,$FieldIDValue) {
        global $db;        
        $sql = "SELECT $FieldParentID 
                FROM $tablename 
                WHERE $FieldID = ?";        

        $r = $db->query($sql, array($FieldIDValue));
        $rows = $r->fetchAll();
        if ($rows[0][$FieldParentID]==0) return true;
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {            
            $arrParent[count($arrParent)] = $rows[$i];
            Taphscv_thumucModel::GetParent(&$arrParent,$tablename,$FieldParentID,$FieldID,$rows[$i][$FieldParentID]);
        }
    }

    public function HasParent($tablename,$FieldParentID,$FieldID,$FieldIDValue) {
        global $db;        
        $sql = "SELECT $FieldParentID 
                FROM $tablename 
                WHERE $FieldID = ?";        

        $r = $db->query($sql, array($FieldIDValue));
        $rows = $r->fetchAll();
        $r->closeCursor();
        if ($rows[0][$FieldParentID]==0) {return false;}
        else {return true;}
    }

    public function HasChild($tablename,$FieldParentID,$FieldID,$FieldIDValue) {
        global $db;        
        $sql = "SELECT $FieldID 
                FROM $tablename 
                WHERE $FieldParentID = ?";        

        $r = $db->query($sql, array($FieldIDValue));
        $rows = $r->fetchAll();
        $r->closeCursor();
        if ($rows[0][$FieldID]==0) {return false;}
        else {return true;}
    }

    /**
     * Lấy cây thư mục hscvdt theo người dùng
     * tạm thời chưa sử dụng
     */
    static function GetTreeByName($user,$active,$value, $fieldname, $tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level) {
        global $db;      
        if ($user == null) {
            global $auth;
            $user = $auth->GetIdentity();
        }

        //$value = '%b%';
        $arrparam = array($value, $parentvalue , $user->ID_U, $user->ID_DEP);
        $sql = "SELECT tm.*,$level as LEVEL from $tablename tm
                        INNER JOIN hscvdt_thumuc_phanquyen pq on pq.`ID_THUMUC` = tm.`ID_THUMUC` 
                        WHERE $fieldname like ? 
                        AND  $fieldidparent=?                         
                        AND ( pq.`ID_U` = ? or pq.`ID_DEP` = ? or pq.`ID_G` in (".$user->ID_G."))
                        AND pq.XEM = 1";                        
        if ($active >= 2 || $active < 0 || is_null($active)) {            
        } else { $sql .= " AND tm.ACTIVE = ".$active; }
        $sql .= " GROUP BY tm.ID_THUMUC";

        $r = $db->query($sql, $arrparam);
        $rows = $r->fetchAll();
        $r->closeCursor();        
        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            Taphscv_thumucModel::GetTreeByName($user,$active, $value, $fieldname,&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }

    static function GetTree($user,$active,$tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level) {
        global $db;      
        if ($user == null) {
            global $auth;
            $user = $auth->GetIdentity();
        }

        $arrparam = array($parentvalue , $user->ID_U, $user->ID_DEP);
        $sql = "SELECT tm.*,$level as LEVEL from $tablename tm
                        INNER JOIN hscvdt_thumuc_phanquyen pq on pq.`ID_THUMUC` = tm.`ID_THUMUC` 
                        WHERE $fieldidparent=?                         
                        AND ( pq.`ID_U` = ? or pq.`ID_DEP` = ? or pq.`ID_G` in (".$user->ID_G."))
                        AND pq.XEM = 1";                        
        if ($active >= 2 || $active < 0 || is_null($active)) {            
        } else { $sql .= " AND tm.ACTIVE = ".$active; }
        $sql .= " GROUP BY tm.ID_THUMUC";
        
        $r = $db->query($sql, $arrparam);
        $rows = $r->fetchAll();
        $r->closeCursor();        
        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            Taphscv_thumucModel::GetTree($user,$active, &$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }
}