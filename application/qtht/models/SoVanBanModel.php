<?php

require_once 'Zend/Db/Table/Abstract.php';

class SoVanBanModel extends Zend_Db_Table_Abstract {

    var $_name = "vb_sovanban";

    function findByMixed($page, $limit, $name, $type) {
        $name = '%' . $name . '%';
        $arr = array($name, $type);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'TYPE = ?';
        $select = $this->select();
        $select->where($wherename, $name);
        $select->order("YEAR DESC");
        $select->order("NAME");
        if ($page != 0 && $limit != 0) {
            $select->limitPage($page, $limit);
        }

        if ($type <= 0 || is_null($type)) {
            
        } else {
            $select->where($whereactive, $type);
        }

        return $this->fetchAll($select);
    }

    function findByMixedWithDep($page, $limit, $name, $type, $dep) {
        global $auth;
        $user = $auth->getIdentity();


        $name = '%' . $name . '%';
        $arr = array($name, $type);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'TYPE = ?';

        $select = $this->select();
        $select->where($wherename, $name);
        if ($user->USERNAME != 'administrator') {
            $wheredep = 'ID_DEP = ?';
            $select->where($wheredep, $dep);
        } else {
            $wheredep = '';
        }

        $select->order("YEAR DESC");
        $select->order("NAME");
        if ($page != 0 && $limit != 0) {
            $select->limitPage($page, $limit);
        }

        if ($type <= 0 || is_null($type)) {
            
        } else {
            $select->where($whereactive, $type);
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

    static function toComboName($is_di) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();

        $type = 1;
        if ($is_di == 1)
            $type = 2;
        $sql = "select `NAME`,`ID_SVB` from `vb_sovanban` where TYPE=? AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type));
        $re = $query->fetchAll();
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

    static function toComboNameWithCQ($year, $type, $id_cq, $is_di) {


        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "
			select ID_SVB , NAME from `vb_sovanban` 
			where YEAR=? and TYPE=? and (ID_CQ = ? or ID_CQ is NULL or ID_CQ=0 )
		";
        $type = 1;
        if ($is_di == 1)
            $type = 2;
        try {
            $stm = $dbAdapter->query($sql, array($year, $type, $id_cq));
            $re = $stm->fetchAll();
            foreach ($re as $row) {
                echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
            }
        } catch (Exception $ex) {
            
        }
    }

    static function toComboFilter($filter_object) {

        if ($filter_object > 2 || $filter_object <= 0 || is_null($filter_object))
            echo '<option value=0  selected>Chọn tất cả</option>';
        else
            echo '<option value=0 >Chọn tất cả</option>';

        if ($filter_object == 1)
            echo '<option value=1 selected>Văn bản đến</option>';
        else
            echo '<option value=1>Văn bản đến</option>';

        if ($filter_object == 2)
            echo '<option value=2 selected>Văn bản đi</option>';
        else
            echo '<option value=2>Văn bản đi</option>';
    }

    function count($name, $type) {

        $name = '%' . $name . '%';
        $arr = array($name, $active);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'TYPE = ?';
        $select = $this->select();
        $select->from($this->_name, 'COUNT(*) AS num');
        $select->where($wherename, $name);
        if ($type <= 0 || is_null($type)) {
            
        } else {
            $select->where($whereactive, $type);
        }
        $row = $this->fetchRow($select);

        return $row->num;
    }

    function countwithdep($name, $type, $dep) {

        $name = '%' . $name . '%';
        $arr = array($name, $active);
        $wherename = 'NAME LIKE ?';
        $whereactive = 'TYPE = ?';
        $wheredep = 'ID_DEP = ?';
        $select = $this->select();
        $select->from($this->_name, 'COUNT(*) AS num');
        $select->where($wherename, $name);
        $select->where($wheredep, $dep);
        if ($type <= 0 || is_null($type)) {
            
        } else {
            $select->where($whereactive, $type);
        }
        $row = $this->fetchRow($select);

        return $row->num;
    }

    static function getNameById($id_svb) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "
			select NAME from `vb_sovanban` where `ID_SVB`=?
		";
        try {
            $stm = $dbAdapter->query($sql, array($id_svb));
            $re = $stm->fetch();
            return $re["NAME"];
        } catch (Exception $ex) {
            return "";
        }
    }

    static function getData($year, $is_di) {
        $user = Zend_Registry::get('auth')->getIdentity();
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $id_cq = SoVanBanModel::getIdCoquanNoiBoHienTaiForSVB();
        $sql = "
			select * from `vb_sovanban` 
			where YEAR=? and TYPE=? and ID_CQ in (" . $id_cq . ")
		";

        $type = 1;
        if ($is_di == 1)
            $type = 2;
        try {
            $stm = $dbAdapter->query($sql, array($year, $type));
            $re = $stm->fetchAll();
            return $re;
        } catch (Exception $ex) {
            echo $ex;
            return array();
        }
    }

    static function getDataByCQ($year, $id_cq, $is_di) {

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $checkID_CQ = explode(",", $id_cq);
        $dk = "ID_CQ = ? ";
        if (count($checkID_CQ) > 1) {
            $dk = "ID_CQ IN (?)";
        }

        $sql = "
			select ID_SVB , NAME from `vb_sovanban` 
			where YEAR=? and TYPE=? and (" . $dk . " or ID_CQ is NULL or ID_CQ=0 )
		";
        $type = 1;
        if ($is_di == 1)
            $type = 2;
        try {
            $stm = $dbAdapter->query($sql, array($year, $type, $id_cq));
            $re = $stm->fetchAll();
            return $re;
        } catch (Exception $ex) {
            return array();
        }
    }

    static function getSoMotCua($year) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "
			select ID_SVB , NAME from `vb_sovanban` 
			where YEAR=? and TYPE=? and ACTIVE = 1 
		";
        $type = 3;

        try {
            $stm = $dbAdapter->query($sql, array($year, $type));
            $re = $stm->fetchAll();
            return $re;
        } catch (Exception $ex) {
            return array();
        }
    }

    static function toComboNameWithDep($is_di) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        if ($user->DEPLEADER == 1)
            return SoVanBanModel::toComboName($is_di);
        $type = 1;
        if ($is_di == 1)
            $type = 2;
        $sql = "select `NAME`,`ID_SVB` from `vb_sovanban` where TYPE=? and ( ID_DEP = ?  ) AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type, $user->ID_DEP));
        $re = $query->fetchAll();
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

    static function selectWithDep($is_di) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        $type = 1;
        if ($is_di == 1)
            $type = 2;
        $sql = "select `ID_SVB`, `NAME` from `vb_sovanban` where TYPE=? and ( ID_DEP = ?  ) AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type, $user->ID_DEP));
        $re = $query->fetchAll();
        return $re;
    }

    static function selectWithDepAndCQ($ID_CQ, $is_di) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        $type = 1;
        if ($is_di == 1)
            $type = 2;
        $sql = "select `ID_SVB`, `NAME` from `vb_sovanban` where TYPE=? and ID_DEP = ? AND ID_CQ=? AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type, $user->ID_DEP, $ID_CQ));
        $re = $query->fetchAll();
        return $re;
    }

    /*
      function getIdCoquanNoiBoHienTai($id_dep)
      {
      global $db;
      $sql= "SELECT * FROM qtht_departments WHERE ID_DEP=".$id_dep."";
      $r = $db->query($sql)->fetch();
      if(!$r["ID_CQ"]){
      if(count($r)){
      if($r["ID_DEP_PARENT"]){
      SoVanBanModel::getIdCoquanNoiBoHienTai($r["ID_DEP_PARENT"]);
      }
      }else{
      return 0;
      }
      }
      return $r["ID_CQ"];
      }
     * 
     */

    //Note:Tao action ao truoc khi su dung
    function getIdCoquanNoiBoHienTaiForSVB() {
        global $db;
        $user = Zend_Registry::get('auth')->getIdentity();
        $sql = "SELECT * FROM vb_coquan vbcq
                        inner join `qtht_departments` dep on vbcq.`ID_CQ`=dep.`ID_CQ`
                        inner join `qtht_employees` e on dep.`ID_DEP`=e.`ID_DEP`
                        inner join `qtht_users` u on e.`ID_EMP`= u.`ID_EMP`
                        WHERE vbcq.ISSYSTEMCQ=1 and u.`ID_U`= ? ";
        $r = $db->query($sql, array($user->ID_U))->fetchAll();

        $actid = ResourceUserModel::getActionByUrl('hscv', 'hscv', 'isallsvbup');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            $sql = "SELECT * FROM vb_coquan vbcq WHERE ISSYSTEMCQ=1 ";
            $r = $db->query($sql)->fetchAll();
        }
        $coquan = array();
        foreach ($r as $row) {
            if ((int) $row["ID_CQ"] > 0) {
                array_push($coquan, (int)$row['ID_CQ']);
            }
        }
        if (count($coquan) > 0 && is_array($coquan)) {
            $return=implode(',', $coquan);
        } else {
            $return='0';
        }
        
        //Kiem tra phong ban co quan noi bo này có hay không còn thuộc 1 cơ quan nội bộ khác và đưa những phòng ban đó vào array
         $sql = "select * from `vb_sovanban` svb  
                        inner join `vb_coquan` vbcq on vbcq.`ID_CQ`=svb.`ID_CQ`
                        WHERE vbcq.ISSYSTEMCQ=1 AND svb.ID_DEP= ? AND vbcq.`ID_CQ` NOT IN (".$return.") group by vbcq.`ID_CQ`";
        $r = $db->query($sql, array($user->ID_DEP))->fetchAll();
        foreach ($r as $row) {
            if ((int) $row["ID_CQ"] > 0) {
                array_push($coquan, (int)$row['ID_CQ']);
            }
        }
        if (count($coquan) > 0 && is_array($coquan)) {
            $return=implode(',', $coquan);
        } else {
            $return='0';
        }
        return $return;        
    }
    function getSVBByUserCqDepSmart($is_di = 0) {
        $user = Zend_Registry::get('auth')->getIdentity();
        $actid = ResourceUserModel::getActionByUrl('hscv', 'hscv', 'isallsvbup');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            $re = SoVanBanModel::getSVBByUserCq($is_di);
        }else{
            $re = SoVanBanModel::getSVBByUserCqAndDep($is_di);
        }
        return $re;
    }
    function getSVBByUserCqDepSmartToCombo($is_di = 0) {
        $user = Zend_Registry::get('auth')->getIdentity();
        $actid = ResourceUserModel::getActionByUrl('hscv', 'hscv', 'isallsvbup');
        if (ResourceUserModel::isAcionAlowed($user->USERNAME, $actid[0])) {
            $re = SoVanBanModel::getSVBByUserCq($is_di);
        }else{
            $re = SoVanBanModel::getSVBByUserCqAndDep($is_di);
        }
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

    function getSVBByUserCqAndDep($is_di = 0) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        $type = 1;
        if ($is_di == 1) {
            $type = 2;
        }
        $sql = "select `ID_SVB`, `NAME` from `vb_sovanban` where TYPE=? and ( ID_DEP = ?  ) 
                    and ID_CQ in (" . SoVanBanModel::getIdCoquanNoiBoHienTaiForSVB() . ") 
                    AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type, $user->ID_DEP));
        $re = $query->fetchAll();
        return $re;
    }

    function getSVBByUserCqAndDepToCombo($is_di = 0) {
        $re = SoVanBanModel::getSVBByUserCqAndDep($is_di);
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

    function getSVBByUserCq($is_di = 0) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        $type = 1;
        if ($is_di == 1) {
            $type = 2;
        }
        $sql = "select `ID_SVB`, `NAME` from `vb_sovanban` where TYPE=? 
                    and ID_CQ in (" . SoVanBanModel::getIdCoquanNoiBoHienTaiForSVB() . ") 
                    AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type));
        $re = $query->fetchAll();
        return $re;
    }

    function getSVBByUserCqToCombo($is_di = 0) {
        $re = SoVanBanModel::getSVBByUserCq($is_di);
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

    function getSVBByUserDep($is_di = 0) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $user = Zend_Registry::get('auth')->getIdentity();
        $type = 1;
        if ($is_di == 1) {
            $type = 2;
        }
        $sql = "select `ID_SVB`, `NAME` from `vb_sovanban` where TYPE=? and ( ID_DEP = ?  )
                    AND YEAR=" . QLVBDHCommon::getYear();
        $query = $dbAdapter->query($sql, array($type, $user->ID_DEP));
        $re = $query->fetchAll();
        return $re;
    }

    function getSVBByUserDepToCombo($is_di = 0) {
        $re = SoVanBanModel::getSVBByUserDep($is_di);
        foreach ($re as $row) {
            echo "<option id='svbcombo" . $row['ID_SVB'] . "' value=" . $row['ID_SVB'] . ">" . $row['NAME'] . "</option>";
        }
    }

}
