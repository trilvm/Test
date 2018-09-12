<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'vbden/models/vbdenModel.php';

class Hscv_DexuatchoxulyModel extends Zend_Db_Table_Abstract {

    protected $_name = 'hscv_hosocongviec_2013';

    function __construct($year) {
        if ($year == "")
            $year = QLVBDHCommon::getYear();
        $this->_name = 'hscv_hosocongviec_' . $year;
        $config = array();
        parent::__construct($config);
    }

    static function Count($parameter) {
        global $db;
        $param = Array();
        $sql = "
			SELECT count(*) as CNT FROM
				" . QLVBDHCommon::Table("HSCV_HOSOCONGVIEC") . " hscv
				inner join " . QLVBDHCommon::Table("hscv_dexuat_choxuly") . " luu on hscv.ID_HSCV = luu.ID_HSCV_CHOXL
			where
				hscv.IS_THEODOI <> 1
                                and hscv.IS_DXCHOXL =1
                                and luu.IS_DONGY <> 1
                                and hscv.ID_U_NN = luu.NGUOIPHEDUYET
		";
        $param[] = $parameter['ID_U'];
        try {
            //echo $sql;
            $r = $db->query($sql, $param);
            $cnt = $r->fetch();
        } catch (Exception $ex) {
            echo $ex->__toString();
            return 0;
        }
        return $cnt["CNT"];
    }

    static function SelectAll($parameter, $offset, $limit, $order) {
        global $db;
        $param = Array();
        //Check ten cong viec
        if ($parameter['NAME'] != "") {
            $where .= " and hscv.NAME LIKE ?";
            $param[] = "%" . $parameter['NAME'] . "%";
        }
        //Check ngay bd
        if ($parameter['NGAY_BD'] != "") {
            $ngaybd = $parameter['NGAY_BD'] . " 00:00:01";
            $where .= " and hscv.NGAY_BD >= ?";
            $param[] = $parameter['NGAY_BD'];
        }
        //Check ngay kt
        if ($parameter['NGAY_KT'] != "") {
            $ngaykt = $parameter['NGAY_KT'] . " 23:59:59";
            $where .= " and hscv.NGAY_BD <= ?";
            $param[] = $parameter['NGAY_KT'];
        }
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }
        $sql = "
			SELECT hscv.*,luu.ID_HSCV_CHOXL, luu.DADOC, class1.ALIAS FROM
			" . QLVBDHCommon::Table("HSCV_HOSOCONGVIEC") . " hscv
			inner join " . QLVBDHCommon::Table("wf_processitems") . " wfitem on hscv.ID_PI = wfitem.ID_PI
			inner join " . QLVBDHCommon::Table("hscv_dexuat_choxuly") . " luu on hscv.ID_HSCV = luu.ID_HSCV_CHOXL
			inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV
				inner join WF_PROCESSES wfp1 on wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class1 on class1.ID_C = wfp1.ID_C
			where
				hscv.IS_THEODOI <> 1
                                and hscv.IS_DXCHOXL =1
                                and luu.IS_DONGY <> 1
				$where
			ORDER BY luu.NGAY DESC
			$strlimit
		";
        $param[] = $parameter['ID_U'];
        try {
            //echo $sql;
            $r = $db->query($sql, $param);
            $result = $r->fetchAll();
            //var_dump($param);
        } catch (Exception $ex) {
            echo $ex->__toString();
            ;
            return null;
        }
        return $result;
    }

//Begin: phần code cho các iframe
    function getNguoiDuyetYeuCau($idhscv) {
        $db = Zend_Db_Table::getDefaultAdapter();

        if (WFEngine::GetClassNameFromObjectId($idhscv) == "VBD") {
            $sql = 'select NGUOIBUTPHE from ' . QLVBDHCommon::Table('vbd_vanbanden') . ' where ID_HSCV=?';
            $r = $db->query($sql, array($idhscv))->fetch();
            return $r['NGUOIBUTPHE'];
        }
        if (WFEngine::GetClassNameFromObjectId($idhscv) == "VBSOANTHAO") {
            $sql = 'select NGUOIYEUCAU from ' . QLVBDHCommon::Table('hscv_congviecsoanthao') . ' where ID_HSCV=?';
            $r = $db->query($sql, array($idhscv))->fetch();
            return $r['NGUOIYEUCAU'];
        }
    }

    function saveDeXuatChoXuLy($data) {
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $db->insert(QLVBDHCommon::Table('hscv_dexuat_choxuly'), $data);
        } catch (Exception $e) {
            $e->__toString();
            exit;
        }
        return 1;
    }

    /**
     * Cập nhập DB khi lãnh đạo đồng ý hoặc bác bỏ đề xuất chờ xử lý
     * @param <int> $id_hscv
     * @param <int> $kdongy
     * @param <string> $ykien
     * @return 1 nếu update OK
     */
    function updateDongY($id_hscv, $kdongy, $ykien) {

        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            //nếu không đồng ý
            if ($kdongy == 1) {
                //cập nhật field IS_DONGY cho table hscv_dexuat_choxuly
                $dataKdongY1 = array(
                    'IS_DONGY' => 0,
                    'YKIEN' => $ykien
                );
                $db->update(QLVBDHCommon::Table('hscv_dexuat_choxuly'), $dataKdongY1, 'ID_HSCV_CHOXL=' . $id_hscv);
                //cập nhật IS_CHOXULY cho table hscv_hosocongviec
                $dataKdongY2 = array(
                    'IS_CHOXULY' => 0,
                    'IS_DXCHOXL' => 0
                );
                $db->update(QLVBDHCommon::Table('HSCV_HOSOCONGVIEC'), $dataKdongY2, 'ID_HSCV=' . $id_hscv);
            }
            //nếu đồng ý
            else {
                //lấy dòng luân chuyển hiện tại
                $data_wl = WFEngine::GetCurrentTransitionInfoByIdHscv($id_hscv);
                //var_dump($data_wl);exit;
                $dataDongY1 = array(
                    'IS_DONGY' => 1,
                    'YKIEN' => $ykien,
                    'HANXULY_THUC' => $data_wl['HANXULY'],
                    'ID_PL' => $data_wl['ID_PL']
                );
                
                //cập nhật field IS_DONGY cho table hscv_dexuat_choxuly
                $db->update(QLVBDHCommon::Table('hscv_dexuat_choxuly'), $dataDongY1, 'ID_HSCV_CHOXL=' . $id_hscv);
                //cập nhật IS_CHOXULY cho table hscv_hosocongviec
                $dataDongY2 = array(
                    'IS_CHOXULY' => 1,
                    'IS_DXCHOXL' => 0
                );
                $db->update(QLVBDHCommon::Table('HSCV_HOSOCONGVIEC'), $dataDongY2, 'ID_HSCV=' . $id_hscv);
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            exit;
        }
        return 1;
    }

    /**
     * Lấy lý do đề xuất chờ xử lý
     * @param <int> $id_hscv
     * @return <array>
     */
    function getNoiDungDeXuat($id_hscv) {

        $sql = "select
                dexuat.`NOIDUNG`,dexuat.`NGAY`,e.`FIRSTNAME`,e.`LASTNAME`
                 from ".QLVBDHCommon::Table('hscv_dexuat_choxuly')." dexuat
                 left join `qtht_users` users on users.`ID_U`=dexuat.`NGUOIGUI`
                 left join `qtht_employees` e on e.`ID_EMP`=users.`ID_EMP`
                where dexuat.`ID_HSCV_CHOXL`=?";

        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql,array($id_hscv));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $r->fetch();
    }

    /**
     * Lấy ý kiến của lãnh đạo sau khi nhận được đề xuất.
     * Điều kiện: YKIEN # null, IS_DONGY=0
     */
    function getYKienLD() {
        $sql = "
            select * from ".QLVBDHCommon::Table('hscv_dexuat_choxuly')." dexuat
            where dexuat.`YKIEN`<> '' or dexuat.`YKIEN`<>null
            ";
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $r->fetchAll();
    }

    function getYKienLdByIdHscv($id_hscv) {
        $sql = "
            select dexuat.`YKIEN`,dexuat.`NOIDUNG` from ".QLVBDHCommon::Table('hscv_dexuat_choxuly')." dexuat
            where dexuat.`ID_HSCV_CHOXL`=?
            ";
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql, array($id_hscv))->fetch();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $r;
    }
    function CountDeXuatChoXuLyByUser($id_u) {

        $sql = "select count(*) as CNT from ".QLVBDHCommon::Table('hscv_dexuat_choxuly')." where YKIEN is null and NGUOIPHEDUYET=?";
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql, array($id_u))->fetch();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $r['CNT'];
    }

//End: kết thúc các đoạn code cho iframe
}
