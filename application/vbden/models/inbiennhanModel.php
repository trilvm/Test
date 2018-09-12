<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of inphieu
 *
 * @author phuongpt
 */
require_once 'Zend/Db/Table/Abstract.php';

class inbiennhanModel extends Zend_Db_Table_Abstract {
    
    static function InBienNhan($idvb)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
            $sql= "
                SELECT
                vb.*, 
                unc.ID_U as ID_U_NC,
                unn.ID_U as ID_U_NN,
                unc.USERNAME as USERNAME_NC,
                unn.USERNAME as USERNAME_NN,
                concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,
                concat(empnn.FIRSTNAME,' ',empnn.LASTNAME) as EMPNNNAME,
                g.NAME as GROUPNAME,
                dep.NAME as DEPNAME,
                tr.NAME,
                pl.DATESEND,
                pl.HANXULY,
                pl.TRE,
                pl.NOIDUNG,
                hscv.`NAME` as HSCV_NAME,
                hscv.`EXTRA`,
                hscv.IS_THEODOI,
                hscv.IS_CHOXULY,
                pl.ID_PL,
                cd.NAME as CHUCDANH
                FROM
                ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
                inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on pl.ID_PI = hscv.ID_PI
                inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
                inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
                left join QTHT_USERS unn on unn.ID_U = pl.ID_U_RECEIVE
                left join QTHT_EMPLOYEES empnn on unn.ID_EMP = empnn.ID_EMP
                left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
                left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
                inner join WF_TRANSITIONS tr on tr.ID_T = pl.ID_T
                inner join WF_TRANSITIONPOOLS tp on tr.ID_TP = tp.ID_TP
                left join qtht_chucdanh cd ON cd.ID_CD = empnn.ID_CD
                inner join ".QLVBDHCommon::Table("VBD_VANBANDEN")." vb ON vb.NGUOITAO = unn.ID_U
                WHERE vb.ID_VBD in (".implode(',', $idvb).")
                GROUP BY (vb.ID_VBD)
            ";
           $r = $db->query($sql);
           $result = $r->fetchAll();
           return $result;
    }
}

?>
