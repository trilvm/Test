<?php

class ThongkevanbandiModel {

    static function writeSelectDepartmentMultiUserH($DName, $UName) {
        global $db;
        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
            SELECT
                    DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
            FROM				
                    QTHT_USERS U 
                    INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
                    INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
            ORDER BY
                    U.ORDERS, E.LASTNAME
	");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<div style='float:left'><select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select><br><select name='".$UName."[]' id=$UName multiple size=5 ></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp1(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    public function getPhongBan($id_dep) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        if ($id_dep[0] != 0) {
            $sql = " select * from qtht_departments where ID_DEP in (" . implode(",", $id_dep) . ")";
        } else {
            $sql = " select * from qtht_departments";
        }
        $query = $dbAdapter->query($sql);
        $re = $query->fetchAll();
        return $re;
    }

    public function getCaNhan($id_dep, $id_canhan) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        if ($id_canhan[0] != 0) {
            $sql = " select u.ID_U, e.FIRSTNAME, e.LASTNAME, dep.NAME from qtht_users u
                    inner join qtht_employees e on u.ID_EMP=e.ID_EMP
                    inner join qtht_departments dep on dep.ID_DEP = e.ID_DEP
                    WHERE u.ID_U in (" . implode(",", $id_canhan) . ")";
        } elseif ((int) $id_dep > 0) {
            $sql = " select u.ID_U, e.FIRSTNAME, e.LASTNAME, dep.NAME from qtht_users u
                    inner join qtht_employees e on u.ID_EMP=e.ID_EMP
                    inner join qtht_departments dep on dep.ID_DEP = e.ID_DEP
                    WHERE dep.ID_DEP='" . $id_dep . "'";
        } else {
            $sql = " select u.ID_U, e.FIRSTNAME, e.LASTNAME, dep.NAME from qtht_users u
                    inner join qtht_employees e on u.ID_EMP=e.ID_EMP
                    inner join qtht_departments dep on dep.ID_DEP = e.ID_DEP";
        }
        $query = $dbAdapter->query($sql);
        $re = $query->fetchAll();
        return $re;
    }

    public function getDataThongKeVanBanDi($params) {
        $fromdate = $params['fromdate'];
        $todate = $params['todate'];
        $year = $params['year'];
        $id_dep = $params['id_dep'];
        $id_svb = $params['id_svb'];
        $id_lvb = $params['id_lvb'];
        $id_u = $params['id_u'];
        if ((int) $year < 2013 || (int) $year > QLVBDHCommon::getYear()) {
            $year = QLVBDHCommon::getYear();
        }

        $where_arr = array();
        if ($fromdate || $fromdate != "") {
            $fromdate = implode("-", array_reverse(explode("/", $fromdate . "/" . $year))) . " 00:00:00";
            $where_fromdate = "vbdi.`NGAYBANHANH` >= '" . $fromdate . "'";
            array_push($where_arr, $where_fromdate);
        }
        if ($todate || $todate != "") {
            $todate = implode("-", array_reverse(explode("/", $todate . "/" . $year))) . " 23:59:59";
            $where_todate = "vbdi.`NGAYBANHANH` <= '" . $todate . "'";
            array_push($where_arr, $where_todate);
        }
        if (($id_dep || $id_dep != "") && (int) $id_dep > 0) {
            $where_dep = "dep.ID_DEP = '" . $id_dep . "'";
            array_push($where_arr, $where_dep);
        }
        if (($id_svb || $id_svb != "") && (int) $id_svb > 0) {
            $where_svb = "svb.ID_SVB = '" . $id_svb . "'";
            array_push($where_arr, $where_svb);
        }
        if (($id_lvb || $id_lvb != "") && (int) $id_lvb > 0) {
            $where_lvb = "lvb.ID_LVB = '" . $id_lvb . "'";
            array_push($where_arr, $where_lvb);
        }
        if (($id_u || $id_u != "") && (int) $id_u > 0) {
            $where_u = "u.ID_U = '" . $id_u . "'";
            array_push($where_arr, $where_u);
        }

        $where = "";
        if (count($where_arr) > 0) {
            $where = " where " . implode(' and ', $where_arr) . " ";
        }

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = 'select vbdi.*,cq.NAME as NAME_COQUANBANHANH, lvb.NAME as NAME_LOAIVANBAN, svb.NAME as NAME_SOVANBAN,
                vbden.NAME_COQUANBANHANH_VBDEN,vbden.NAME_LOAIVANBAN_VBDEN,vbden.NAME_SOVANBAN_VBDEN,vbden.SOKYHIEU_VBDEN,
                vbden.NGAYBANHANH_VBDEN,vbden.NGAYHETHAN_VBDEN,e.FIRSTNAME,e.LASTNAME,dep.NAME
                from vbdi_vanbandi_' . $year . ' vbdi
                inner join vb_loaivanban lvb on vbdi.ID_LVB=lvb.ID_LVB
                inner join vb_sovanban svb on vbdi.ID_SVB = svb.ID_SVB
                inner join vb_coquan cq on vbdi.ID_CQ = cq.ID_CQ
                inner join qtht_users u on u.ID_U=vbdi.NGUOISOAN
                inner join qtht_employees e on u.ID_EMP = e.ID_EMP
                inner join qtht_departments dep on dep.ID_DEP = e.ID_DEP
                left join (
                        select hscv.ID_HSCV,vbd.SOKYHIEU as SOKYHIEU_VBDEN,vbd.NGAYBANHANH as NGAYBANHANH_VBDEN,
                        vbd.COQUANBANHANH_TEXT as NAME_COQUANBANHANH_VBDEN, lvbu.NAME as NAME_LOAIVANBAN_VBDEN, svbu.NAME as NAME_SOVANBAN_VBDEN,
                        vbd.NGAYHETHAN as NGAYHETHAN_VBDEN
                        from hscv_hosocongviec_' . $year . ' hscv
                        inner join vbd_fk_vbden_hscvs_' . $year . ' fk on hscv.ID_HSCV =fk.ID_HSCV
                        inner join vbd_vanbanden_' . $year . ' vbd on vbd.ID_VBD =fk.ID_VBDEN
                        inner join vb_loaivanban lvbu on vbd.ID_LVB=lvbu.ID_LVB
                        inner join vb_sovanban svbu on vbd.ID_SVB = svbu.ID_SVB
                ) vbden on vbdi.ID_HSCV=vbden.ID_HSCV' . $where;
        $query = $dbAdapter->query($sql);
        $re = $query->fetchAll();
        return $re;
    }

}
