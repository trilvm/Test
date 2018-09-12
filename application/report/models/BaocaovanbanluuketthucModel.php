<?php

    /**
     * User: HOANGNM
     * Date: 2016-07-08
     */
    class BaocaovanbanluuketthucModel
    {
        protected $year = '2016';

        public function __construct($year)
        {
            $this->year = $year;
        }

        public function selectAll($params)
        {
            global $db;
            $where = " WHERE (1=1)";

            // build where
            // setting date
            $fromdate = QLVBDHCommon::doDateViet2Standard($params->fromdate.'/'.$this->year);
            $todate = QLVBDHCommon::doDateViet2Standard($params->todate.'/'.$this->year);
            $where .= " AND DATE_CREATE >= '". $fromdate ."' AND DATE_CREATE <= '". $todate ."'";

            // setting phòng ban or cá nhân
            if((int)$params->thongke == 0){
                $strWhere = ' WHERE (1=1)';
                $itemget = "dep.NAME AS NAME, dep.ID_DEP, CONCAT(emp.FIRSTNAME, SPACE(1), emp.LASTNAME) AS NGUOILUU";
                if(count($params->sel_pb) > 0 && (int)$params->sel_pb[0] != 0){
                    $phongban = implode(',', $params->sel_pb);
                    $strWhere .= " AND ID_DEP IN (". $phongban .")";
                }
                $joinItemltd = "
                        INNER JOIN qtht_users AS u ON u.ID_U = hsltd.U_OWN
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        INNER JOIN (
                            SELECT ID_DEP, NAME
                            FROM qtht_departments
                            $strWhere
                        ) AS dep ON dep.ID_DEP = emp.ID_DEP";

                $joinItemlcxl = "
                        INNER JOIN qtht_users AS u ON u.ID_U = hslcxl.ID_U
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        INNER JOIN (
                            SELECT ID_DEP, NAME
                            FROM qtht_departments
                            $strWhere
                        ) AS dep ON dep.ID_DEP = emp.ID_DEP";
            }else{
                $strWhere = ' WHERE (1=1)';
                $itemget = "u.NAME AS NAME, u.ID_U";
                if($params->sel_dep_canhan != 0){
                    $strWhere .= " AND emp.ID_DEP = '". $params->sel_dep_canhan ."'";
                }

                if(count($params->sel_canhan) > 0 && (int)$params->sel_canhan[0] != 1){
                    $canhan = implode(',', $params->sel_canhan);
                    $strWhere .= " AND u.ID_U IN (". $canhan .")";
                }

                $joinItemltd = "
                    INNER JOIN (
                        SELECT
                            CONCAT(emp.FIRSTNAME, SPACE(1), emp.LASTNAME) AS NAME, u.ID_U
                        FROM
                            qtht_users AS u
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        $strWhere
                    ) AS u ON u.ID_U = hsltd.U_OWN
                ";

                $joinItemlcxl = "
                    INNER JOIN (
                        SELECT
                            CONCAT(emp.FIRSTNAME, SPACE(1), emp.LASTNAME) AS NAME, u.ID_U
                        FROM
                            qtht_users AS u
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        $strWhere
                    ) AS u ON u.ID_U = hslcxl.ID_U
                ";
            }

            // setting sổ văn bản
            if((int)$params->sel_svb != 0){
                $where .= " AND vbd.ID_SVB = '". $params->sel_svb ."'";
            }

            // setting loại văn bản
            if((int)$params->sel_lvb != 0){
                $where .= " AND vbd.ID_LVB = '". $params->sel_lvb ."'";
            }

            $sql = "
                SELECT
                    hsltd.ID_LUUTD AS ID_SAVE,
                    hsltd.U_OWN AS ID_U_SAVE,
                    hscv.ID_HSCV,
                    hsltd.COMMENT AS GHICHU,
                    hsltd.DATE_CREATE AS NGAYLUU,
                    vbd.SOKYHIEU,
                    vbd.NGAYBANHANH,
                    vbd.TRICHYEU,
                    cq.`NAME` AS COQUANBANHANH,
                    1 AS TYPESAVE,
                    $itemget
                FROM
	                hscv_hosoluutheodoi_". $this->year ." AS hsltd
                INNER JOIN hscv_hosocongviec_". $this->year ." AS hscv ON hscv.ID_HSCV = hsltd.ID_HSCV
                INNER JOIN vbd_fk_vbden_hscvs_". $this->year ." AS fkvbd ON fkvbd.ID_HSCV = hscv.ID_HSCV
                INNER JOIN vbd_vanbanden_". $this->year ." AS vbd ON vbd.ID_VBD = fkvbd.ID_VBDEN
                INNER JOIN vb_coquan AS cq ON cq.ID_CQ = vbd.ID_CQ
                $joinItemltd
                $where
                UNION
                SELECT
                    hslcxl.ID_LUUCXL AS ID_SAVE,
                    hslcxl.ID_U AS ID_U_SAVE,
                    hscv.ID_HSCV,
                    hslcxl.`COMMENT` AS GHICHU,
                    hslcxl.DATE_CREATE AS NGAYLUU,
                    vbd.SOKYHIEU,
                    vbd.NGAYBANHANH,
                    vbd.TRICHYEU,
                    cq.`NAME` AS COQUANBANHANH,
                    2 AS TYPESAVE,
                    $itemget
                FROM
                    hscv_hosoluuchoxuly_". $this->year ." AS hslcxl
                INNER JOIN hscv_hosocongviec_". $this->year ." AS hscv ON hscv.ID_HSCV = hslcxl.ID_HSCV
                INNER JOIN vbd_fk_vbden_hscvs_". $this->year ." AS fkvbd ON fkvbd.ID_HSCV = hscv.ID_HSCV
                INNER JOIN vbd_vanbanden_". $this->year ." AS vbd ON vbd.ID_VBD = fkvbd.ID_VBDEN
                INNER JOIN vb_coquan AS cq ON cq.ID_CQ = vbd.ID_CQ
                $joinItemlcxl
                $where
            ";

            $result = $db->query($sql)->fetchAll();
            return $result;
        }

        public function selectAllByLoaiSave($params)
        {
            global $db;
            $where = " WHERE (1=1)";
            $table = "hscv_hosoluutheodoi_". $this->year;
            $itemget = " hsltd.U_OWN AS ID_U_SAVE, hsltd.ID_LUUTD AS ID_SAVE,";
            $whereJoin = "hsltd.U_OWN";

            if($params->TYPE_LUUKT == 1){
                $table = 'hscv_hosoluuchoxuly_'. $this->year;
                $itemget = " hsltd.ID_U AS ID_U_SAVE,hsltd.ID_LUUCXL AS ID_SAVE,";
                $whereJoin = "hsltd.ID_U";
            }

            // build where
            // setting date
            $fromdate = QLVBDHCommon::doDateViet2Standard($params->fromdate.'/'.$this->year);
            $todate = QLVBDHCommon::doDateViet2Standard($params->todate.'/'.$this->year);
            $where .= " AND DATE_CREATE >= '". $fromdate ."' AND DATE_CREATE <= '". $todate ."'";

            // setting phòng ban or cá nhân
            if((int)$params->thongke == 0){
                $strWhere = ' WHERE (1=1)';
                $itemget .= "
                    dep.NAME AS NAME,
                    dep.ID_DEP,
                    CONCAT(emp.FIRSTNAME, SPACE(1), emp.LASTNAME) AS NGUOILUU,";
                if(count($params->sel_pb) > 0 && (int)$params->sel_pb[0] != 0){
                    $phongban = implode(',', $params->sel_pb);
                    $strWhere .= " AND ID_DEP IN (". $phongban .")";
                }
                $joinItem = "
                        INNER JOIN qtht_users AS u ON u.ID_U = ". $whereJoin ."
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        INNER JOIN (
                            SELECT ID_DEP, NAME
                            FROM qtht_departments
                            $strWhere
                        ) AS dep ON dep.ID_DEP = emp.ID_DEP";
            }else{
                $strWhere = ' WHERE (1=1)';
                $itemget .= "
                    u.NAME AS NAME,
                    u.ID_U,";
                if($params->sel_dep_canhan != 0){
                    $strWhere .= " AND emp.ID_DEP = '". $params->sel_dep_canhan ."'";
                }

                if(count($params->sel_canhan) > 0 && (int)$params->sel_canhan[0] != 1){
                    $canhan = implode(',', $params->sel_canhan);
                    $strWhere .= " AND u.ID_U IN (". $canhan .")";
                }


                $joinItem = "
                    INNER JOIN (
                        SELECT
                            CONCAT(emp.FIRSTNAME, SPACE(1), emp.LASTNAME) AS NAME, u.ID_U
                        FROM
                            qtht_users AS u
                        INNER JOIN qtht_employees AS emp ON emp.ID_EMP = u.ID_EMP
                        $strWhere
                    ) AS u ON u.ID_U = ".$whereJoin;
            }

            // setting sổ văn bản
            if((int)$params->sel_svb != 0){
                $where .= " AND vbd.ID_SVB = '". $params->sel_svb ."'";
            }

            // setting loại văn bản
            if((int)$params->sel_lvb != 0){
                $where .= " AND vbd.ID_LVB = '". $params->sel_lvb ."'";
            }

            $sql = "
                SELECT
                    $itemget
                    hscv.ID_HSCV,
                    vbd.SOKYHIEU,
                    vbd.NGAYBANHANH,
                    vbd.TRICHYEU,
                    cq.`NAME` AS COQUANBANHANH,
                    hsltd.COMMENT AS GHICHU,
                    hsltd.DATE_CREATE AS NGAYLUU
                FROM $table AS hsltd
                INNER JOIN hscv_hosocongviec_". $this->year ." AS hscv ON hscv.ID_HSCV = hsltd.ID_HSCV
                INNER JOIN vbd_fk_vbden_hscvs_". $this->year ." AS fkvbd ON fkvbd.ID_HSCV = hscv.ID_HSCV
                INNER JOIN vbd_vanbanden_". $this->year ." AS vbd ON vbd.ID_VBD = fkvbd.ID_VBDEN
                INNER JOIN vb_coquan AS cq ON cq.ID_CQ = vbd.ID_CQ
                $joinItem
                $where
            ";

            $result = $db->query($sql)->fetchAll();
            return $result;
        }
    }