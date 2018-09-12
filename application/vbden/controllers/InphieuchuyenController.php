<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'config/vbden.php';
// Dùng bên listAction
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'vbden/models/fk_vbden_hscvsModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once('vbden/models/vbdenModel.php');
require_once 'vbmail/models/vbmail_vanbannhanModel.php';
include_once 'hscv/models/PhoiHopModel.php';
include_once 'hscv/models/HosoluutheodoiModel.php';
include_once 'vbdi/models/VanBanDiModel.php';
include_once 'qtht/models/nguoidungModel.php';
include_once 'hscv/models/PhienBanDuThaoModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once('hscv/models/chuyennoiboModel.php');
require_once 'hscv/models/gen_tempModel.php';
class vbden_InphieuchuyenController extends Zend_Controller_Action {
    
    public function inphieuAction()
    {   
        $this->_helper->layout->disableLayout();
        $db = Zend_Db_Table::getDefaultAdapter();
        $idvbd = $this->_request->getParam('idvbd');
        $tablename = QLVBDHCommon::Table("vbd_vanbanden");
        
        global $auth;
        $user = $auth->getIdentity();

        $sql1="select cd.NAME from qtht_chucdanh cd,qtht_employees em,qtht_users us
              where  em.ID_CD=cd.ID_CD and us.ID_EMP=em.ID_EMP and us.ID_U=?";
        $r1 = $db->query($sql1, $user->ID_U);
        $data2 = ($r1->fetchAll());
      //  var_dump($data2);
        $this->view->chucvu=$data2[0]['NAME'];
        $this->view->ten=$user->FULLNAME;
        $sql = "
            SELECT
                vb.*, lvb.NAME as LVBNAME, lvvb.NAME as LVVBNAME,
                svb.NAME as SVBNAME, cq.NAME as CQNAME,
                concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPNAME
				, thumuc.NAME AS TEN_THUMUC , thumuc.ID_THUMUC as TM_ID
            FROM
                " . QLVBDHCommon::Table("VBD_VANBANDEN") . "  vb
		left join " . QLVBDHCommon::Table("vbd_fk_vbden_hscvs") . " fkhscv on fkhscv.ID_VBDEN = vb.ID_VBD
	    	left join " . QLVBDHCommon::Table("HSCV_HOSOCONGVIEC") . " hscv on hscv.ID_HSCV = fkhscv.ID_HSCV
	    	left join HSCV_THUMUC thumuc on hscv.ID_THUMUC_HSCV = thumuc.ID_THUMUC
                left join VB_LOAIVANBAN lvb on vb.ID_LVB = lvb.ID_LVB
                left join VB_LINHVUCVANBAN lvvb on vb.ID_LVVB =lvvb.ID_LVVB
                left join VB_SOVANBAN svb on vb.ID_SVB = svb.ID_SVB
                left join VB_COQUAN cq	on vb.ID_CQ = cq.ID_CQ
                left join QTHT_USERS u on vb.NGUOITAO = u.ID_U
                left join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
            WHERE
                vb.ID_VBD = ?
              "; 

        $r = $db->query($sql, $idvbd);
        $data = ($r->fetchAll());
        $this->view->data = $data;
       // var_dump($data)  ;
        if ($data[0]['DOMAT']==1) $this->view->dokhan='MẬT';
        global $config;
        $this->view->donvi= $config->sys_info->unit;
        
        $hscv = vbdenModel::GetAllHSCV($idvbd); 
        foreach ($hscv as $itemhscv) {
            $arr_id[] = $itemhscv['ID_HSCV'];
        }
        $this->view->sendprocess = array();
        foreach ($arr_id as $idhscv) {

            $sendprocess = WFEngine::GetProcessLogByObjectId($idhscv);
            array_push($this->view->sendprocess, $sendprocess);
        }
        
   //   var_dump($this->view->sendprocess)  ;
        //header("Content-type: application/x-ms-download");
        //header("Content-Disposition: attachment; filename=phieuxuly_$idvbd.doc;");
        //header("Pragma: no-cache");
        //header("Expires: 0");
        $this->renderScript("Inphieuchuyen/inphieu.phtml");
    }
    public function invbsaoyAction()
    {
        $this->_helper->layout->disableLayout();
        $db = Zend_Db_Table::getDefaultAdapter();
        $params=$this->_request->getParams();
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $id_u = $user->ID_U;
        // var_dump($params);exit;
        $sql="
       SELECT 
       `ID_VBD`, 
       `ID_SVB`, 
       `NGUOITAO`,
       `ID_LVVB`, 
       `ID_CQ`, 
       `ID_LVB`, 
       `MASOVANBAN`,
       `SOKYHIEU`, 
       `SODEN`, 
       `NGAYDEN`,
       `NGAYBANHANH`,
       `NGAYTAO`,
       `TRICHYEU`,
       `SOBAN`,
       `SOTO`,
       `DOMAT`, 
       `DOKHAN`,
       `NGUOIKY`, 
       `SODEN_IN`, 
       `SOKYHIEU_IN`,
       `IS_KHONGXULY`,
       `OLD`, 
       `COQUANNHAN`,
       `COQUANNHAN_TEXT`,
       min(DA_XEM) as DA_XEM 
           FROM ( SELECT pl.DATESEND as NGAYGUI , 
                vbd.`ID_VBD`, 
                `ID_SVB`,
                `NGUOITAO`,
                `ID_LVVB`,
                hscv.`ID_HSCV`,
                `ID_CQ`,
                `ID_LVB`,
                `MASOVANBAN`, 
                `SOKYHIEU`, 
                `SODEN`, 
                `NGAYDEN`, 
                `NGAYBANHANH`,
                `NGAYTAO`,
                `TRICHYEU`,
                `SOBAN`,
                `SOTO`, 
                `DOMAT`, 
                `DOKHAN`, 
                `NGUOIKY`,
                `SODEN_IN`,
                `SOKYHIEU_IN`,
                `IS_KHONGXULY`,
                `OLD`, 
                `COQUANNHAN`,
                `COQUANNHAN_TEXT` , 1 AS DA_XEM
                FROM 
                ".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd join  ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD 
                join ".QLVBDHCommon::Table('HSCV_HOSOCONGVIEC')." hscv on hscv.ID_HSCV = fk_v_h.ID_HSCV 
                join ".QLVBDHCommon::Table('WF_PROCESSITEMS')." pi on pi.ID_O = hscv.ID_HSCV
                join ".QLVBDHCommon::Table('WF_PROCESSLOGS')." pl on pi.ID_PI = pl.ID_PI
                WHERE (1=1) and ( pl.ID_U_RECEIVE = $id_u and vbd.ID_LVB=33 ) 
                GROUP BY vbd.ID_VBD UNION 
                    SELECT lc.NGAYCHUYEN as NGAYGUI, 
                    vbd.`ID_VBD`, `ID_SVB`, `NGUOITAO`,
                    `ID_LVVB`, `ID_HSCV`, `ID_CQ`, 
                    `ID_LVB`, `MASOVANBAN`, 
                    `SOKYHIEU`, `SODEN`, `NGAYDEN`,
                    `NGAYBANHANH`, `NGAYTAO`, `TRICHYEU`,
                    `SOBAN`, `SOTO`, `DOMAT`, `DOKHAN`, 
                    `NGUOIKY`, `SODEN_IN`, `SOKYHIEU_IN`, 
                    `IS_KHONGXULY`, `OLD`, `COQUANNHAN`, 
                    `COQUANNHAN_TEXT` , lc.DA_XEM as DA_XEM 
                    FROM ".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd 
                    inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD 
                    WHERE (1=1) and lc.`NGUOINHAN`= $id_u and vbd.ID_LVB=33) vbd 
                    GROUP BY ID_VBD ORDER BY NGAYDEN DESC , NGAYGUI DESC LIMIT 0,40"; 
         $r = $db->query($sql);
         $data= ($r->fetchAll()) ;
         $this->view->data=$data;
        // var_dump($data);exit;
        //header("Content-type: application/x-ms-download");
        //header("Content-Disposition: attachment; filename=dsvbsaoy.doc;");
        //header("Pragma: no-cache");
        //header("Expires: 0");
         $this->renderScript("Inphieuxuly/invbsaoy.phtml");
    }
}
?>
