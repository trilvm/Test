<?php

header('Content-Type: text/html; charset=utf-8');
$rootPath = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . '/library');
require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();
$config = new Zend_Config_Ini('../application/config.ini', 'general');
$registry = Zend_Registry::getInstance();
$registry->set('config', $config);
$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);
try{	
		$year = date('Y');
		$fromdate = $_GET['fromdate'] !=null ? $_GET['fromdate'] : "1/1";
		$todate = $_GET['todate'] !=null ? $_GET['todate'] : "31/12";
        $where_arr_vbd =  array();
		$where_arr_vbdi =  array();
		if($fromdate != "" && $fromdate != null ){
			$fromdate = implode("-",array_reverse(explode("/",$fromdate."/".$year)))." 00:00:00";
			$where_fromdate_vbd = "vbd.`NGAYDEN` >= '".$fromdate."'" ; 
			$where_fromdate_vbdi = "vbdi.`NGAYBANHANH` >= '".$fromdate."'" ;
			array_push($where_arr_vbd,$where_fromdate_vbd);
			array_push($where_arr_vbdi,$where_fromdate_vbdi);
		}
		if($todate != "" && $todate != null){
			$todate = implode("-",array_reverse(explode("/",$todate."/".$year)))." 23:59:59";
			$where_todate_vbd = "vbd.`NGAYDEN` <= '".$todate."'" ;
			$where_todate_vbdi = "vbdi.`NGAYBANHANH` <= '".$todate."'" ;			
			array_push($where_arr_vbd,$where_todate_vbd);
			array_push($where_arr_vbdi,$where_todate_vbdi);
		}
		$where_vbd ="";
		$where_vbdi ="";
		if(count($where_arr_vbd) > 0)
			$where_vbd = " where " . implode(' and ',$where_arr_vbd)." ";
		if(count($where_arr_vbdi) > 0)
			$where_vbdi = " where " . implode(' and ',$where_arr_vbdi)." ";
		$is_lienthong = $_GET['is_lienthong'];
		if($is_lienthong == 1){
			$where_vbd .= ' and IS_LIENTHONG = 1';	
			$where_vbdi .= ' and MASOLIENTHONG > 0';
		}
		
		$return = array();
		$sqlvbd = sprintf(" select count(*) AS VANBANDEN,count(FILE_VBD) AS FILE_VBD from (select temp.`ID_VBD`,temp.ID_HSCV,temp.MASOVANBAN,temp.SD,temp.ID_CQ,temp.SODEN_IN,
					 temp.ID_LVB,temp.TRICHYEU,temp.GHICHU, temp.NGAYDEN,temp.SOKYHIEU,temp.NGUOITAO,temp.NGAYBANHANH,
					 temp.SOBAN,temp.ID_SVB,temp.IS_KHONGXULY,temp.COQUANBANHANH_TEXT,temp.KQ_DEXUAT,
					 GROUP_CONCAT(DISTINCT wfl.`ID_U_RECEIVE` ORDER BY wfl.`ID_PL` DESC SEPARATOR ',') as NAME_U,
					 GROUP_CONCAT(DISTINCT ph.`ID_U` ORDER BY ph.`ID_PHOIHOP` DESC SEPARATOR ',') as NAME_PH,
					 GROUP_CONCAT(DISTINCT dlc.`NGUOINHAN` ORDER BY dlc.`ID_DLC` DESC SEPARATOR ',') as NAME_DB,					
					 GROUP_CONCAT(DISTINCT concat(dk.`MASO`, '~', vbdi.SOKYHIEU, '~', dk.FILENAME) ORDER BY dk.`ID_DK` DESC SEPARATOR ',') as FILE_MASO,
					 vbdi.`TRICHYEU` as TRICHYEU_FILE,dk.`NAM`,temp.SODEN,dk1.ID_OBJECT AS FILE_VBD
					  from
					 ( select vbd.`ID_VBD` , hs.`ID_HSCV` , vbd.`MASOVANBAN` , vbd.`SODEN` as SD,
					 vbd.`SODEN`, vbd.`SODEN_IN`, vbd.`ID_CQ`,vbd.`ID_LVB` ,vbd.`TRICHYEU` , vbd.GHICHU,
					 vbd.`NGAYDEN` , vbd.`SOKYHIEU`, vbd.`NGUOITAO`,vbd.`NGAYBANHANH`, vbd.`SOBAN`,
					 vbd.ID_SVB,vbd.`IS_KHONGXULY` , vbd.`COQUANBANHANH_TEXT` , hsltd.COMMENT as
					 KQ_DEXUAT,vb_svb.ID_DEP,hs.`ID_PI` from  vbd_vanbanden_".$year." vbd
					 left join vb_sovanban vb_svb on vbd.ID_SVB = vb_svb.ID_SVB
					 left join  vbd_fk_vbden_hscvs_".$year." fk_vbd_hs on fk_vbd_hs.ID_VBDEN = vbd.ID_VBD
					 left join  hscv_hosocongviec_".$year." hs on hs.`ID_HSCV` = fk_vbd_hs.`ID_HSCV`
					 left join  hscv_hosoluutheodoi_".$year." hsltd on hs.ID_HSCV = hsltd.ID_HSCV
					 ".$where_vbd.") temp
					 left join  hscv_phoihop_".$year." ph on ph.`ID_HSCV`= temp.ID_HSCV
					 left join  vbd_dongluanchuyen_".$year." dlc on dlc.`ID_VBD`= temp.ID_VBD
					 left join wf_processlogs_".$year." wfl on temp.`ID_PI` = wfl.`ID_PI`				
					 left join vbdi_vanbandi_".$year." vbdi on vbdi.`ID_HSCV` = temp.ID_HSCV
					 left join gen_filedinhkem_".$year." dk on dk.`ID_OBJECT`=vbdi.`ID_VBDI` and dk.`TYPE` =5
					 left join ( 
								SELECT ID_OBJECT FROM
									gen_filedinhkem_".$year."
								WHERE TYPE = 3 
								GROUP BY ID_OBJECT
							) dk1 ON dk1.ID_OBJECT = temp.ID_VBD 
					 group by temp.`ID_VBD` order by temp.SODEN_IN ASC) SLVBD
					");
		$arrayvbd = $db->query($sqlvbd)->fetch();			
		array_push($return,$arrayvbd);
		
		$sqlvbdi = sprintf("Select count(*) AS VANBANDI, COUNT(dk.ID_OBJECT) AS FILE_VBDI from vbdi_vanbandi_".$year." vbdi 
							left join QTHT_USERS unyc on vbdi.NGUOISOAN=unyc.ID_U 
							left join QTHT_EMPLOYEES empnyc on unyc.ID_EMP = empnyc.ID_EMP
							left join ( 
								SELECT ID_OBJECT FROM
									gen_filedinhkem_".$year."
								WHERE TYPE = 5 
								GROUP BY ID_OBJECT
							) dk ON dk.ID_OBJECT = vbdi.ID_VBDI
							".$where_vbdi." ORDER BY SODI_IN, NGUOIKY ");
		$arrayvbdi = $db->query($sqlvbdi)->fetch();
		array_push($return,$arrayvbdi);
		
        echo json_encode($return);
    } 	catch (Exception $e) {
        echo $e;
        echo "ERROR";
    }










