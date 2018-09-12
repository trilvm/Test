<?php

/*
 * Quản lý tập hồ sơ công việc
 * @author: thangtba
 * 
 */
require_once 'qtht/models/nguoidungModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'taphscv/models/Taphscv_thumucModel.php';
include_once 'taphscv/models/Taphscv_dinhkemModel.php';
require_once 'taphscv/models/Taphscv_phanquyenModel.php';
require_once('qtht/models/CoQuanModel.php');
require_once 'vbdi/models/VanBanDiModel.php';
require_once 'hscv/models/hosocongviecModel.php';

class Taphscv {

    var $_id_u;
    var $_id_g;
    var $_id_dept;

}

class Taphscv_TaphosocongviecModel extends Zend_Db_Table_Abstract {

    public $_name = 'hscvdt_taphoso';
    var $_search = "";
    var $_idthumuc = "";
    var $_ID_U;
    var $_ID_G;
    var $_ID_DEP;
    var $_modelVbdi;

    public function __construct() {

        global $auth;
        $user = $auth->getIdentity();
        $this->_ID_U = $user->ID_U;
        $this->_ID_G = $user->ID_G;
        $this->_ID_DEP = $user->ID_DEP;

        $this->_modelVbdi = new VanBanDiModel(QLVBDHCommon::getYear());
    }

    function Counts() {

        $arrwhere = array();
        $strwhere = "(1=1)";
        if ($this->_search != "") {
            $arrwhere[] = "%" . $this->_search . "%";
            $strwhere .= " and TEN like ?";
        }

        if ($this->_idthumuc != "") {
            $arrwhere[] = $this->_idthumuc;
            $strwhere .= " and thscv.ID_THUMUC=?";
        }
        $strwhere .= " and pq.`XEM` = 1";

        $arrwhere[] = $this->_ID_U;
        $strwhere .= " and (pq.`ID_U`=?";

        //$arrwhere[] = $this->_ID_G;
        $strwhere .= " or pq.`ID_G` in (" . $this->_ID_G . ")";

        $arrwhere[] = $this->_ID_DEP;
        $strwhere .= " or pq.`ID_DEP`=?)";
        //Thực hiện query
        $sql = "
                select count(DISTINCT thscv.ID_TAPHOSO, thscv.`ID_DEP`,thscv.`ID_THUMUC`,
                    thscv.`CODE`,thscv.`NGAYTAO`,thscv.`NGUOITAO`, thscv.`TEN`) as C
                from `hscvdt_taphoso` thscv
                left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.ID_TAPHOSO
                where $strwhere
                ";
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query($sql, $arrwhere)->fetch();
        return $result["C"];
    }

    function SelectAll($offset, $limit, $order) {

        $arrwhere = array();
        $strwhere = "(1=1) AND thscv.TRANGTHAI = 1 ";
        if ($this->_search != "") {
            $arrwhere[] = "%" . $this->_search . "%";
            $strwhere .= " and TEN like ?";
        }

        if ($this->_idthumuc != "") {
            $arrwhere[] = $this->_idthumuc;
            $strwhere .= " and thscv.ID_THUMUC=?";
        }
        if (hosocongviecModel::isVanthuLuutru($this->_ID_U) != TRUE) {
            $strwhere .= " and pq.`XEM` = 1";

            $arrwhere[] = $this->_ID_U;
            $strwhere .= " and (pq.`ID_U`=?";

            //$arrwhere[] = $this->_ID_G;
            $strwhere .= " or pq.`ID_G` in (" . $this->_ID_G . ")";

            $arrwhere[] = $this->_ID_DEP;
            $strwhere .= " or pq.`ID_DEP`=?)";
        } else {
            $strwhere .= " AND (thscv.YEUCAU = 2 || pq.`ID_U`= ?)";
            $arrwhere[] = $this->_ID_U;
        }

        //Build phần limit
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }

        //Build order
        $strorder = "";
        if ($order != "") {
            $strorder = " ORDER BY $order";
        }
        //Thực hiện query
        $sql = "
                select DISTINCT thscv.IS_YEUCAU, thscv.ID_TAPHOSO, thscv.`ID_DEP`,thscv.`ID_THUMUC`,
                    thscv.`CODE`,thscv.`NGAYTAO`,thscv.`NGUOITAO`, thscv.`TEN`, thscv.YEUCAU
                from `hscvdt_taphoso` thscv
                left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.ID_TAPHOSO
                where $strwhere $strorder $strlimit
                ";
        $result = $this->getDefaultAdapter()->query($sql, $arrwhere);
        return $result->fetchAll();
    }

    function SelectAllReportData($listId) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select *,taphscv.TEN as TEN_TAPHSCV from hscvdt_vanban as vb left join hscvdt_taphoso taphscv on taphscv.ID_TAPHOSO = vb.ID_TAPHOSO where vb.ID_TAPHOSO IN ($listId) order by vb.ID_TAPHOSO desc , vb.NGAYBANHANH ";
        try {
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $ex) {
            die($ex->__toString());
        }
        return $r;
    }

    function SelectAllByStatus($offset, $limit, $order, $status) {

        $arrwhere = array();
        $strwhere = "(1=1)";
        if ($this->_search != "") {
            $arrwhere[] = "%" . $this->_search . "%";
            $strwhere .= " and TEN like ?";
        }

        if ($this->_idthumuc != "") {
            $arrwhere[] = $this->_idthumuc;
            $strwhere .= " and thscv.ID_THUMUC=?";
        }
        $strwhere .= " and pq.`XEM` = 1";

        $arrwhere[] = $this->_ID_U;
        $strwhere .= " and (pq.`ID_U`=?";

        //$arrwhere[] = $this->_ID_G;
        $strwhere .= " or pq.`ID_G` in (" . $this->_ID_G . ")";

        $arrwhere[] = $this->_ID_DEP;
        $strwhere .= " or pq.`ID_DEP`=?)";

        $strwhere .= " AND TRANGTHAI = $status";

        //Build phần limit
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }

        //Build order
        $strorder = "";
        if ($order != "") {
            $strorder = " ORDER BY $order";
        }
        //Thực hiện query
        $sql = "
                select DISTINCT thscv.IS_YEUCAU, thscv.ID_TAPHOSO, thscv.`ID_DEP`,thscv.`ID_THUMUC`,
                    thscv.`CODE`,thscv.`NGAYTAO`,thscv.`NGUOITAO`, thscv.`TEN`
                from `hscvdt_taphoso` thscv
                left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.ID_TAPHOSO
                where $strwhere $strorder $strlimit
                ";

        $result = $this->getDefaultAdapter()->query($sql, $arrwhere);
        return $result->fetchAll();
    }

    /**
     *
     * @param <type> $data
     * @param <type> $id_parent
     * @param <type> $name_tree
     * @param <type> $html
     * @param <type> $sel
     * @return string
     */
    static function ToTree($data, $id_parent, $name_tree, &$html, $sel) {
        $_color = array(0 => "#000", 1 => "#0000ff", 2 => "#ff0066");
        $isFirst = false;
        foreach ($data as $row) {
            if ($row["ID_THUMUC_PARENT"] == $id_parent) {
                if (!$isFirst) {
                    $isFirst = true;
                    if ($id_parent == 0)
                        $html .= "<ul>";
                    else
                        $html .= "<ul>";
                }
                $html .= "<li style='color:" . $_color[$row["DOQUANTRONG"]] . " !important'>";
                //detect has child
                $haschild = false;
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[$i]["ID_THUMUC_PARENT"] == $row["ID_THUMUC"]) {
                        $haschild = true;
                        break;
                    }
                }
                if ($haschild) {
                    //$html .= "<a href='/taphscv/taphscv/list/ID_THUMUC/".$row["ID_THUMUC"]."'>".$row["NAME"]."</a>";
                    $html .= "<a style='color:" . $_color[$row["DOQUANTRONG"]] . " !important' class='folder " . ($sel == $row["ID_THUMUC"] ? "selected" : "") . "' href='javascript:
                            document.location.href=\"/taphscv/taphscv/list/ID_THUMUC/" . $row["ID_THUMUC"] . "\";'>" . $row["NAME"] . "</a>";
                } else {
                    //$html .= "<a href='/taphscv/taphscv/list/ID_THUMUC/".$row["ID_THUMUC"]."'><b>".$row["NAME"]."</b></a>";
                    $html .= "<a style='color:" . $_color[$row["DOQUANTRONG"]] . " !important' class='file " . ($sel == $row["ID_THUMUC"] ? "selected" : "") . "' href='javascript:document.location.href=\"/taphscv/taphscv/list/ID_THUMUC/" . $row["ID_THUMUC"] . "\";'>
                                " . $row["NAME"] . "
                              </a>";
                }
                Taphscv_TaphosocongviecModel::ToTree($data, $row["ID_THUMUC"], $name_tree, &$html, $sel);
                $html .= "</li>";
            }
        }
        if ($isFirst)
            $html .= "</ul>";
        return $html;
    }

    /**
     *
     * @param <type> $arr
     * @param <type> $id_taphscv 
     */
    public function InsertTapHSCV($arr, $id_taphscv) {

        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            if ((int) $id_taphscv == 0) {
                $db->insert($this->_name, $arr);
                //thiết lập full quyền cho tập hscv đc tạo
                $id = $this->getPrimarykeyWhenInsert('ID_TAPHOSO', $this->_name);
                $db->insert('hscvdt_phanquyen_taphoso', array(
                    'ID_U' => $this->_ID_U,
                    'XEM' => 1,
                    'CAPNHAT' => 1,
                    'PHANQUYEN' => 1,
                    'ID_TAPHOSO' => $id
                        )
                );
            } else
                $db->update($this->_name, $arr, "ID_TAPHOSO=$id_taphscv");
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

    /**
     *
     * @param <type> $id
     * @return <type> 
     */
    public function GetTapHSCVById($id) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select * from $this->_name where ID_TAPHOSO=?";
        $data = array();
        $nguoidung_model = new nguoidungModel();
        try {
            $data = $db->query($sql, array($id))->fetch();
            $user = $nguoidung_model->FindById($data['NGUOITAO']);
            $fullname = $user['FIRSTNAME'] . " " . $user['LASTNAME'];
            $data['FULLNAME'] .= $fullname;
            $data['TENPHONG'] .= DepartmentsModel::getNameById($data['ID_DEP']);
            $data['THUMUC'] .= Taphscv_thumucModel::getNameById($data['ID_THUMUC']);
            $data['KYHIEU_PB'] .= Common_Maso::getKyhieuDeptById($data['ID_DEP']);
//            var_dump($data);exit;
        } catch (Exception $ex) {
            die($ex->__toString());
        }
        return $data;
    }

    /**
     * Lấy danh sách tập hồ sơ
     * @author : Baotq
     * @createDate : 07/21/2012 12:52 PM
     */
    public function getTapHSCV($userinfo) {
        $year = QLVBDHCommon::getYear();
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select thscv.ID_TAPHOSO,
						   thscv.`ID_DEP`,
						   thscv.`ID_THUMUC`,
						   thscv.`CODE`,
						   thscv.`NGAYTAO`,
						   thscv.`NGUOITAO`,
						   thscv.`TEN`
					from `hscvdt_taphoso` thscv
						 inner join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
					where pq.`CAPNHAT` = 1 and
						(pq.`ID_U` = '" . $userinfo->_id_u . "' or pq.`ID_G` in (" . $userinfo->_id_g . ") or pq.`ID_DEP` = '" . $userinfo->_id_dep . "')";
        $r = $db->query($sql)->fetchAll();
        return $r;
    }

    /**
     *
     * @param <type> $id_u
     * @param <type> $idvbd
     * @param <type> $idvbdi
     * @return <type> 
     */
    public function GetTapHSCVForAddVanban($idobject, $loai, $userinfo) {

        $year = QLVBDHCommon::getYear();
        $db = Zend_Db_Table::getDefaultAdapter();
        switch ($loai) {
            case 1:
                $sql = "
					select thscv.ID_TAPHOSO,
						   thscv.`ID_DEP`,
						   thscv.`ID_THUMUC`,
						   thscv.`CODE`,
						   thscv.`NGAYTAO`,
						   thscv.`NGUOITAO`,
						   thscv.`TEN`
					from `hscvdt_taphoso` thscv
						 inner join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
					where pq.`CAPNHAT` = 1 and
						(pq.`ID_U` = '" . $userinfo->_id_u . "' or pq.`ID_G` in (" . $userinfo->_id_g . ") or pq.`ID_DEP` = '" . $userinfo->_id_dep . "') and
						thscv.ID_TAPHOSO not in (SELECT ID_TAPHOSO FROM HSCVDT_VANBAN vb WHERE
						vb.LOAI = 1 AND
						vb.ID_OBJECT = " . $idobject . " AND
						vb.NAM=" . $year . ")
						"
                ;
                break;
            case 2:
                $sql = "
					select thscv.ID_TAPHOSO,
						   thscv.`ID_DEP`,
						   thscv.`ID_THUMUC`,
						   thscv.`CODE`,
						   thscv.`NGAYTAO`,
						   thscv.`NGUOITAO`,
						   thscv.`TEN`
					from `hscvdt_taphoso` thscv
						 inner join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
					where pq.`CAPNHAT` = 1 and
						  (pq.`ID_U` = '" . $userinfo->_id_u . "' or pq.`ID_G` in (" . $userinfo->_id_g . ") or pq.`ID_DEP` = '" . $userinfo->_id_dep . "') and
						  thscv.ID_TAPHOSO not IN (select a.`ID_TAPHOSO` from `hscvdt_vanban` a
						   where a.LOAI = 2 and a.`ID_OBJECT`=$idobject and a.nam=$year)";
                break;
            case 4:
                $sql = "
					select thscv.ID_TAPHOSO,
						   thscv.`ID_DEP`,
						   thscv.`ID_THUMUC`,
						   thscv.`CODE`,
						   thscv.`NGAYTAO`,
						   thscv.`NGUOITAO`,
						   thscv.`TEN`
					from `hscvdt_taphoso` thscv
						 inner join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
					where pq.`CAPNHAT` = 1 and
						  (pq.`ID_U` = '" . $userinfo->_id_u . "' or pq.`ID_G` in (" . $userinfo->_id_g . ") or pq.`ID_DEP` = '" . $userinfo->_id_dep . "') and
						  thscv.ID_TAPHOSO not IN (select a.`ID_TAPHOSO` from `hscvdt_vanban` a
						   where a.LOAI = 4 and a.`ID_OBJECT`=$idobject and a.nam=$year)";
                break;
        }
        //echo $sql;
        $r = $db->query($sql)->fetchAll();
//        $sql_vbd = "
//            select thscv.ID_TAPHOSO,
//                   thscv.`ID_DEP`,
//                   thscv.`ID_THUMUC`,
//                   thscv.`CODE`,
//                   thscv.`NGAYTAO`,
//                   thscv.`NGUOITAO`,
//                   thscv.`TEN`
//            from `hscvdt_taphoso` thscv
//                 left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
//            where pq.`XEM` = 1 and
//                  pq.`CAPNHAT` = 1 and
//                  pq.`ID_U` = $id_u and
//                  thscv.ID_TAPHOSO not IN (select a.`ID_TAPHOSO` from `hscvdt_vanban` a
//                   where a.LOAI = 1 and a.`ID_OBJECT`=$idvbd)
//        ";
//        $sql_vbdi = "
//            select thscv.ID_TAPHOSO,
//                   thscv.`ID_DEP`,
//                   thscv.`ID_THUMUC`,
//                   thscv.`CODE`,
//                   thscv.`NGAYTAO`,
//                   thscv.`NGUOITAO`,
//                   thscv.`TEN`
//            from `hscvdt_taphoso` thscv
//                 left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.`ID_TAPHOSO`
//            where pq.`XEM` = 1 and
//                  pq.`CAPNHAT` = 1 and
//                  pq.`ID_U` = $id_u and
//                  thscv.ID_TAPHOSO not IN (select a.`ID_TAPHOSO` from `hscvdt_vanban` a
//                   where a.LOAI = 1 and a.`ID_OBJECT`=$idvbdi)
//        ";
//        try{
//            if ($idvbd > 0)
//                $r = $db->query($sql_vbd)->fetchAll();
//            if ($idvbdi > 0)
//                $r = $db->query($sql_vbdi)->fetchAll();
//        }catch (Exception $ex) {
//            die($ex->__toString());
//        }
        return $r;
    }

    /**
     *
     * @param <type> $idtaphoso
     * @param <type> $idvbd
     * @param <type> $idvbdi
     * @return <type>
     */
    public function AddVanBanToTapHSCV($idtaphoso, $idobject, $loai) {
        if ($idtaphoso > 0) {
            $year = QLVBDHCommon::getYear();

            $db = Zend_Db_Table::getDefaultAdapter();
            $detaivb = $this->GetVanBanDetail($idobject, $loai);
            try {
                if ($loai == 1)//vbd
                    $db->insert('hscvdt_vanban', array(
                        'ID_TAPHOSO' => (int) $idtaphoso,
                        'SOKYHIEU' => $detaivb['SOKYHIEU'],
                        'ID_OBJECT' => $detaivb['ID_VBD'],
                        'NGAYBANHANH' => $detaivb['NGAYBANHANH'],
                        'COQUANBANHANH' => $detaivb['COQUANBANHANH_TEXT'],
                        'TRICHYEU' => $detaivb['TRICHYEU'],
                        'LOAI' => 1,
                        'NAM' => $year
                    ));
                if ($loai == 2) {//vbdi
                    $db->insert('hscvdt_vanban', array(
                        'ID_TAPHOSO' => $idtaphoso,
                        'SOKYHIEU' => $detaivb['SOKYHIEU'],
                        'ID_OBJECT' => $detaivb['ID_VBDI'],
                        'NGAYBANHANH' => $detaivb['NGAYBANHANH'],
                        'COQUANBANHANH' => CoQuanModel::getNameById($detaivb['ID_CQ']),
                        'TRICHYEU' => $detaivb['TRICHYEU'],
                        'LOAI' => 2,
                        'NAM' => $year
                    ));
                }
                if ($loai == 4) {//hscv
                    $db->insert('hscvdt_vanban', array(
                        'ID_TAPHOSO' => $idtaphoso,
                        'SOKYHIEU' => $detaivb['SOKYHIEU'],
                        'ID_OBJECT' => $detaivb['ID_HSCV'],
                        'NGAYBANHANH' => $detaivb['NGAYBANHANH'],
                        'COQUANBANHANH' => CoQuanModel::getNameById($detaivb['ID_CQ']),
                        'TRICHYEU' => $detaivb['NAME'],
                        'LOAI' => 4,
                        'NAM' => $year
                    ));
                }
            } catch (Exception $ex) {
                die($ex->__toString());
            }
            return 1;
        }
        return 0;
    }

    public function GetVanBanDetail($idobject, $loai) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql_vbd = "select * from vbd_vanbanden_" . QLVBDHCommon::getYear() . " where ID_VBD=$idobject";
        $sql_vbdi = "select * from vbdi_vanbandi_" . QLVBDHCommon::getYear() . " where ID_VBDI=$idobject";
        $sql_hscv = "select * from hscv_hosocongviec_" . QLVBDHCommon::getYear() . " where ID_HSCV=$idobject";
        if ($loai == 1)
            $r = $db->query($sql_vbd)->fetch();
        if ($loai == 2)
            $r = $db->query($sql_vbdi)->fetch();
        if ($loai == 4)
            $r = $db->query($sql_hscv)->fetch();
        return $r;
    }

    public function DeleteVanBan($idvb) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->delete('hscvdt_vanban', "ID_VB=$idvb");
    }

    public function CountForVanBan($id, $is_vbd, $offset, $limit, $order) {

        $year = QLVBDHCommon::getYear();

        $table_vbd = 'vbd_vanbanden_' . $year;
        $table_vbdi = 'vbdi_vanbandi_' . $year;

        $db = Zend_Db_Table::getDefaultAdapter();

        $sql_vbd = "select count(*) as C from $table_vbd where ID_TAPHOSO=$id limit $offset, $limit $order";
        $sql_vbdi = "select count(*) as C from $table_vbdi where ID_TAPHOSO=$id limit $offset, $limit $order";

        if ($is_vbd == 1)
            $r = $db->query($sql_vbd)->fetch();
        if ($is_vbd == 0)
            $r = $db->query($sql_vbdi)->fetch();
        return $r['C'];
    }

    public function GetVanBanByIDTaphoso($id, $is_vbd) {

        $year = QLVBDHCommon::getYear();

        $table_vbd = 'vbd_vanbanden_' . $year;
        $table_vbdi = 'vbdi_vanbandi_' . $year;

        $db = Zend_Db_Table::getDefaultAdapter();

        $sql_vbd = "select * from $table_vbd where ID_TAPHOSO=$id";
        $sql_vbdi = "select * from $table_vbdi where ID_TAPHOSO=$id";

        if ($is_vbd == 1)
            $r = $db->query($sql_vbd)->fetchAll();
        if ($is_vbd == 0)
            $r = $db->query($sql_vbdi)->fetchAll();
        return $r;
    }

    //lấy id vừa insert
    /**
     *
     * @param <type> $id
     * @param <type> $table
     * @return <type> 
     */
    function getPrimarykeyWhenInsert($id, $table) {
        $sql = " select max($id) as ID from $table";
        try {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $qr = $dbAdapter->query($sql);
            $re = $qr->fetch();
            return $re["ID"];
        } catch (Exception $ex) {
            return 0;
        }
    }

    public function DeleteTapHSCV($id) {

        $this->config = Zend_Registry::get('config');
        $db = Zend_Db_Table::getDefaultAdapter();
        $dinhkemModel = new Taphscv_dinhkemModel();
        $phanquyenModel = new Taphscv_phanquyenModel();

        //sql get dinhkem by idtaphoso
        $sql = "select * from $dinhkemModel->_name where ID_TAPHOSO=$id";
        $arr_File = array();
        try {

            //detele van ban
            $db->delete('hscvdt_vanban', "ID_TAPHOSO = $id");
            //xoa file dinh kem
            $arr_File = $db->query($sql)->fetchAll();
            for ($i = 0; $i <= count($arr_File); $i++) {
                $data = $dinhkemModel->find($arr_File[$i]['ID_DINHKEM'])->current();
                unlink($this->config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $data->FILECODE);
            }
            $db->delete($dinhkemModel->_name, "ID_TAPHOSO = $id");
            //xoa phan quyen
            $db->delete($phanquyenModel->_name, "ID_TAPHOSO = $id");
            //xoa taphoso
            $db->delete($this->_name, "ID_TAPHOSO = $id");
        } catch (Exception $ex) {
            die($ex->__toString());
        }
    }

    public function GetVanBan($idtaphoso) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select * from hscvdt_vanban where ID_TAPHOSO=$idtaphoso";

        try {
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $ex) {
            die($ex->__toString());
        }
        return $r;
    }

    public function GetYeuCauFromTapHscv($idtaphoso) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select YEUCAU from hscvdt_taphoso where ID_TAPHOSO=$idtaphoso";

        try {
            $r = $db->query($sql)->fetch();
        } catch (Exception $ex) {
            die($ex->__toString());
        }
        return $r['YEUCAU'];
    }

    public function GetHSCVByThumuc($idthumuc, $idtaphoso) {

        $arrwhere[] = $idthumuc;
        $strwhere .= "thscv.ID_THUMUC=?";

        $strwhere .= " and pq.`XEM` = 1";

        $arrwhere[] = $this->_ID_U;
        $strwhere .= " and (pq.`ID_U`=?";

        //$arrwhere[] = $this->_ID_G;
        $strwhere .= " or pq.`ID_G` in (" . $this->_ID_G . ")";

        $arrwhere[] = $this->_ID_DEP;
        $strwhere .= " or pq.`ID_DEP`=?)";
        //Thực hiện query
        $sql = "
                select DISTINCT thscv.ID_TAPHOSO, thscv.`ID_DEP`,thscv.`ID_THUMUC`,
                    thscv.`CODE`,thscv.`NGAYTAO`,thscv.`NGUOITAO`, thscv.`TEN`
                from `hscvdt_taphoso` thscv
                left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.ID_TAPHOSO
                where $strwhere and thscv.ID_TAPHOSO <> $idtaphoso
                ";
        //echo $sql;exit;
        return $this->getDefaultAdapter()->query($sql, $arrwhere)->fetchAll();
    }
    public function GetAllHSCV() {

        $strwhere .= " pq.`XEM` = 1";

        $arrwhere[] = $this->_ID_U;
        $strwhere .= " and (pq.`ID_U`=?";

        //$arrwhere[] = $this->_ID_G;
        $strwhere .= " or pq.`ID_G` in (" . $this->_ID_G . ")";

        $arrwhere[] = $this->_ID_DEP;
        $strwhere .= " or pq.`ID_DEP`=?)";
        //Thực hiện query
        $sql = "
                select DISTINCT thscv.ID_TAPHOSO, thscv.`ID_DEP`,thscv.`ID_THUMUC`,
                    thscv.`CODE`,thscv.`NGAYTAO`,thscv.`NGUOITAO`, thscv.`TEN`
                from `hscvdt_taphoso` thscv
                left join `hscvdt_phanquyen_taphoso` pq on pq.ID_TAPHOSO=thscv.ID_TAPHOSO
                where $strwhere and thscv.ID_TAPHOSO 
                ";
        //echo $sql;exit;
        return $this->getDefaultAdapter()->query($sql, $arrwhere)->fetchAll();
    }
    public function CheckVbdIsExistInHscv($id_vbd) {

        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            select fk.`ID_HSCV`, hscv.`ID_HSCV` as HSCV
            from " . QLVBDHCommon::Table('vbd_fk_vbden_hscvs') . " fk
            left join " . QLVBDHCommon::Table("hscv_hosocongviec") . " hscv on hscv.`ID_HSCV`=fk.`ID_HSCV`
            where fk.`ID_VBDEN` = '" . $id_vbd . "'
        ";
        //echo $sql;exit;
        $r = $db->query($sql)->fetch();
        if (count($r) == 0)
            return 0;
        else
            return $r['ID_HSCV'];
    }

    public function isExist($type, $id_object) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select COUNT(*) as COUNT from hscvdt_vanban where LOAI = " . $type . " AND ID_OBJECT = " . $id_object;
        $r = $db->query($sql)->fetch();
        return $r['COUNT'];
    }

    public function demYeuCauMuon($id_taphscv) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select COUNT(*) as COUNT from hscvdt_muonhoso where ID_TAPHOSO =$id_taphscv AND IS_DUYET =0 AND IS_YEUCAU=0 ";
        $r = $db->query($sql)->fetch();
        return $r['COUNT'];
    }

    public function xacnhannguoimuon($id_taphscv, $id_u) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT ID_TAPHOSO FROM hscvdt_muonhoso WHERE ID_TAPHOSO=? AND NGUOIMUON=? AND  IS_YEUCAU=0 AND IS_DUYET=1";
        $r = $db->query($sql, array($id_taphscv, $id_u))->fetchAll();
        $arr = array();
        foreach ($r as $value) {
            $arr = $value['ID_TAPHOSO'];
        }
        return $arr;
    }

    public function xacnhannguoiduocdong($id_taphscv, $user) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT ID_PQ FROM hscvdt_phanquyen_taphoso WHERE ID_TAPHOSO=? AND  (ID_G=? OR ID_DEP=? OR ID_U=?)";
        $r = $db->query($sql, array($id_taphscv, $user->ID_G, $user->ID_DEP, $user->ID_U))->fetchAll();
        return $r;
    }

    public function getListHsYeuCau() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * FROM hscvdt_taphoso WHERE YEUCAU = 1";
        $r = $db->query($sql)->fetchAll();
        return $r;
    }

    public function saveYeuCau($id, $yeucau) {
        $db = Zend_Db_Table::getDefaultAdapter();
        //Yêu cầu = 1: đóng hs SET ID_TAPHOSO = 2, Yêu cầu = 2: Hủy HS SET ID_TAPHOSO = 3
        if ($yeucau == 1) {
            $sql = "UPDATE hscvdt_taphoso SET YEUCAU = 2 WHERE ID_TAPHOSO = " . $id;
        } else if ($yeucau == 2) {
            $sql = "UPDATE hscvdt_taphoso SET YEUCAU = 0 WHERE ID_TAPHOSO = " . $id;
        } else if ($yeucau == 3) {
            $sql = "UPDATE hscvdt_taphoso SET YEUCAU = 1 WHERE ID_TAPHOSO = " . $id;
        }
        $r = $db->query($sql);
        if ($r)
            return 1;
    }

    public function countHSYeuCau() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT COUNT(*) AS CNT FROM hscvdt_taphoso WHERE YEUCAU = 1";
        $r = $db->query($sql)->fetch();
        return $r['CNT'];
    }

    public function checkVanBanToTapHSCV($idtaphoso, $idobject, $loai) {
        if ($idtaphoso > 0) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $detaivb = $this->GetVanBanDetail($idobject, $loai);
            try {
                if ($loai == 1){//vbd
                    $sql = "select COUNT(*) AS CNT from hscvdt_vanban where LOAI = " . $loai . " AND ID_OBJECT = " . $detaivb['ID_VBD']."  and ID_TAPHOSO=".$idtaphoso;
                    $r = $db->query($sql)->fetch();
                }elseif ($loai == 2) {//vbdi
                    $sql = "select COUNT(*) AS CNT from hscvdt_vanban where LOAI = " . $loai . " AND ID_OBJECT = " . $detaivb['ID_VBDI']."  and ID_TAPHOSO=".$idtaphoso;
                    $r = $db->query($sql)->fetch();
                }elseif ($loai == 3) {//du thao
                    $r=array('CTN'=>'0');
                }elseif ($loai == 4) {//hscv
                    $sql = "select COUNT(*) AS CNT from hscvdt_vanban where LOAI = " . $loai . " AND ID_OBJECT = " . $detaivb['ID_HSCV']."  and ID_TAPHOSO=".$idtaphoso;
                    $r = $db->query($sql)->fetch();
                }
                if((int)$r['CNT']>0){
                    return 'false';
                }else{
                    return 'true';
                }
            } catch (Exception $ex) {                
                die($ex->__toString());
                return 'false';
            }
        }
        return 'false';
    }
}
