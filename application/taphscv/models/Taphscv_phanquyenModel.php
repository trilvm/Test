<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Taphscv_phanquyenModel extends Zend_Db_Table_Abstract {

    var $_name = "hscvdt_phanquyen_taphoso";
    var $_search = "";
    var $_id = 0;

    public function GetAllGroup($idtaphoso, $isPhanquyen) {
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
                               ID_TAPHOSO,ID_G,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_phanquyen_taphoso`
                         WHERE
                              ID_TAPHOSO=?
                         GROUP BY ID_TAPHOSO,ID_G
                         ) PT ON PT.`ID_G` = G.`ID_G` ".$par." ORDER BY NAME";
        
        $r = $this->getDefaultAdapter()->query($sql, array($idtaphoso));
        return $r->fetchAll();
    }

   public function GetAllDep($idtaphoso, $isPhanquyen) {
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
                               ID_TAPHOSO,ID_DEP,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_phanquyen_taphoso`
                         WHERE
                              ID_TAPHOSO=?
                         GROUP BY ID_TAPHOSO,ID_DEP
                         ) PT ON PT.`ID_DEP` = DEP.`ID_DEP` ".$par." 
            AND DEP.ACTIVE=1
            ORDER BY ID_DEP_PARENT,NAME
        ";
        $r = $this->getDefaultAdapter()->query($sql, array($idtaphoso));
        return $r->fetchAll();
    }

   public function GetAllUser($idtaphoso, $isPhanquyen) {
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
                               ID_TAPHOSO,ID_U,max(XEM)  as QUYENXEM,max(CAPNHAT)  as QUYENTHEMMOI,max(PHANQUYEN)  as QUYENPHANQUYEN, 1 AS SEL
                         FROM
                             `hscvdt_phanquyen_taphoso`
                         WHERE
                              ID_TAPHOSO=?
                             GROUP BY ID_TAPHOSO,ID_U
                         ) PT ON PT.`ID_U` = U.`ID_U` ".$par." 
            ORDER BY EMP.LASTNAME
        ";
        $r = $this->getDefaultAdapter()->query($sql, array($idtaphoso));
        return $r->fetchAll();
    }

    function UpdateGroup($xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_G" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_G" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_G" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    function UpdateDep($xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_DEP" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_DEP" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_DEP" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    function UpdateUser($xem, $themmoi, $phanquyen) {
        $db = Zend_Db_Table::getDefaultAdapter();
        
        if (is_array($xem)) {
            foreach ($xem as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_U" => $q, "XEM" => 1));
            }
        }
        if (is_array($themmoi)) {
            foreach ($themmoi as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_U" => $q, "CAPNHAT" => 1));
            }
        }
        if (is_array($phanquyen)) {
            foreach ($phanquyen as $q) {
                $this->getDefaultAdapter()->insert("hscvdt_phanquyen_taphoso", array("ID_TAPHOSO" => $this->_id, "ID_U" => $q, "PHANQUYEN" => 1));
            }
        }
    }

    public function PrePareTableBeforeUpdate(){
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = array();
        $where[] = $db->quoteInto('ID_TAPHOSO = ?', $this->_id);
        try {
            $db->delete($this->_name, $where);
        } catch (Exception $ex) {
            die ($ex->__toString());
        }
    }
     /**
     * @name Kiểm tra người dùng có quyền cập nhật không? (Xóa, sửa tập hscv)
     * @return 1:OK, 0 NOT OK
     * @author Thangtba
     */
    public function CheckUpdatePermissionByUser($user, $id_taphscv) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                select * from `hscvdt_phanquyen_taphoso` pq where pq.`CAPNHAT`=1 and (pq.`ID_U`=? or ID_G IN ($user->_id_g) or ID_DEP=?) and pq.`ID_TAPHOSO`=?
            ";
        try {
            $r = $db->query($sql, array($user->_id_u, $user->_id_dept, $id_taphscv))->fetch();
            if (!$r)
                return 0;
            else
                return 1;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

     public function CheckPhanQuyenPermissionByUser($user, $id_taphscv) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                select * from `hscvdt_phanquyen_taphoso` pq where pq.`PHANQUYEN`=1 and (pq.`ID_U`=? or ID_G IN ($user->_id_g) or ID_DEP=?) and pq.`ID_TAPHOSO`=?
            ";
        try {
            $r = $db->query($sql, array($user->_id_u, $user->_id_dept, $id_taphscv))->fetch();
            if (!$r)
                return 0;
            else
                return 1;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }
	
	public function CheckQuyenXemPermissionByUser($user, $id_taphscv) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                select * from `hscvdt_phanquyen_taphoso` pq where pq.`XEM`=1 and (pq.`ID_U`=? or ID_G IN ($user->_id_g) or ID_DEP=?) and pq.`ID_TAPHOSO`=?
            ";
        try {
            $r = $db->query($sql, array($user->_id_u, $user->_id_dept, $id_taphscv))->fetch();
            if (!$r)
                return 0;
            else
                return 1;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

    public function GetPermissionDetailByTapHscv($idtaphoso) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select * from $this->_name pq
            where pq.`ID_TAPHOSO`= ?
            ";
            
        try {
            $r = $db->query($sql, array($idtaphoso))->fetchAll();
        } catch (Exception $ex) {
            die ($ex->__toString());
        }
        return $r;
    }

    /**
     *  Lấy dữ liệu dựa trên id tập hồ sơ
     * @param int $idtaphoso
     * @return array
     * @author thangtba
     */
    public function GetPermistionOfUsers($idtaphoso) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select pq.*, CONCAT(emps.`FIRSTNAME`,' ',emps.`LASTNAME`) as FULLNAME,
                groups.`NAME` as GROUPNAME, depts.`NAME` as DEPTNAME
            from `hscvdt_phanquyen_taphoso` pq
                left join `qtht_users` users on users.`ID_U`=pq.`ID_U`
                left join `qtht_employees` emps on emps.`ID_EMP`=users.`ID_EMP`
                left join `qtht_groups` groups on groups.`ID_G`=pq.`ID_G`
                left join `qtht_departments` depts on depts.`ID_DEP`=pq.`ID_DEP`
            where pq.`ID_TAPHOSO`=?
            ";
        try {
            $r = $db->query($sql, array($idtaphoso))->fetchAll();
        } catch(Exception $ex) {
            die ($ex->__toString());
        }
        return $r;
    }

}