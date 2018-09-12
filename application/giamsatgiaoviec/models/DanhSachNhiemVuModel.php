<?php

require_once 'Zend/Db/Table/Abstract.php';

class DanhSachNhiemVuModel extends Zend_Db_Table_Abstract {

    public static function SelectAll($parameter, $offset, $limit, $order, $isseeall = 0) {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $id_u = $user->ID_U;
        //lay so van ban den tuong ung voi co quan nguoi dung
        //$svbs = SovanbanModel::getData(QLVBDHCommon::getYear(),0);
        //$svbs = SovanbanModel::selectWithDep(0);
        //if($isseeall==0){
        $svbs = SovanbanModel::getData(QLVBDHCommon::getYear(), 0);
        $arr_svb = array();
        foreach ($svbs as $it_svb) {
            $arr_svb[] = $it_svb["ID_SVB"];
        }
        if (count($arr_svb)) {
            $str_insvb = implode($arr_svb, ',');
            $str_insvb = " and ID_SVB in (" . $str_insvb . ")";
        } else {
            $str_insvb = " and ID_SVB in (0)";
        }
        //}else{
        //$str_insvb=" ";
        //}
        //if($isseeall && !$str_insvb)
        //return array();
        //$isseeall = (hosocongviecModel::isVanthu($id_u) || (hosocongviecModel::isAlowSeeAllVbDen() && ($parameter['IS_SEE_ALL']==1)));
        //Build query
        $sql = "";
        $where = "(1=1)";
        $join = "";
        $select = "";
        
         //check loại nhiệm vụ nội bộ
       // if($parameter['IS_LIENTHONG'] == 1 AND $parameter['IS_NOIBO'] == 1){
                //$where .= " ";
       // }
        
        //check loại nhiệm vụ nội bộ
       // else if($parameter['IS_NOIBO'] == 1){
               // $where .= " and vbd.IS_LIENTHONG IS NULL";
       // }

        //check loại nhiệm vụ nội bộ
       // else if ($parameter['IS_LIENTHONG'] == 1){
                $where .= " and vbd.IS_LIENTHONG = 1 and vbd.IS_GIAOVIEC =1";
       // }
        
   
                
        //Check cơ quan
        if ($parameter['ID_CQ'] > 0 && $parameter['ID_CQ'] != "") {
            $where .= " and vbd.ID_CQ = ?";
            $param[] = $parameter['ID_CQ'];
        } else if ($parameter['COQUANBANHANH_TEXT'] != "") {
            $where .= " and vbd.COQUANBANHANH_TEXT = ?";
            $param[] = $parameter['COQUANBANHANH_TEXT'];
        }

        //check sổ văn bản
        if ($parameter['ID_SVB'] > 0 && $parameter['ID_SVB'] != "") {
            $where .= " and vbd.ID_SVB = ?";
            $param[] = $parameter['ID_SVB'];
        } else {
            if (count($arr_svb)) {
                $str_insvb = implode($arr_svb, ',');
                $str_insvb = " and vbd.ID_SVB in (" . $str_insvb . ")";
                //if($isseeall)
                //$where .= $str_insvb;
            }
        }
        //check loại văn bản
        if ($parameter['ID_LVB'] > 0 && $parameter['ID_LVB'] != "") {

            $where .= " and vbd.ID_LVB = ?";
            $param[] = $parameter['ID_LVB'];
        }

        //check loại công việc
        if ($parameter['LOAICONGVIEC'] != "") {


            $where .= " and hscv.LOAICV_GIAOVIEC = ?";
            $param[] = $parameter['LOAICONGVIEC'];
        }


        //check linh vuc van ban
        if ($parameter['ID_LVVB'] > 0 && $parameter['ID_LVVB'] != "") {
            $arr_temp = array();
            hosocongviecModel::selectlvvb($parameter['ID_LVVB'], $arr_temp);
            if (count($arr_temp)) {
                $where .= " and ( vbd.ID_LVVB in " . "( " . implode(",", $arr_temp) . "  )" . ")";
                //$param[] = $parameter['ID_LVVB'];
                //$param[] = "( ". implode(",",$arr_temp) ."  )";
            }
        }

        //Check ngày đến bd
        if ($parameter['NGAYDEN_BD'] != "") {
            $ngayden_bd = $parameter['NGAYDEN_BD'] . " 00:00:00";
            $where .= " and vbd.NGAYDEN >= ?";
            $param[] = $ngayden_bd;
        }

        //Check ngày đến kt
        if ($parameter['NGAYDEN_KT'] != "") {
            $ngayden_kt = $parameter['NGAYDEN_KT'] . " 23:59:59";
            $where .= " and vbd.NGAYDEN <= ?";
            $param[] = $ngayden_kt;
        }



        //Check so den		
        if ($parameter['SODEN'] != "") {

            if ($parameter['SODEN_ISLIKE']) {
                $where .= " and ( vbd.SODEN REGEXP '" . $parameter['SODEN'] . "*' ) ";
                //$param[] = $parameter['SODEN'];
            } else {

                $soden_in = (int) $parameter['SODEN'];
                if ((String) $soden_in != $parameter['SODEN']) {

                    $where .= " and vbd.SODEN = ?  ";
                    $param[] = $parameter['SODEN'];
                } else {
                    $where .= " and (vbd.SODEN = ? or vbd.SODEN_IN = ?) ";
                    $param[] = $parameter['SODEN'];
                    $param[] = $soden_in;
                }
            }
        }

        //check so ky hieu
        if ($parameter['SOKYHIEU'] != "") {
            $sokyhieu_in = (int) $parameter['SOKYHIEU'];
            if ($sokyhieu_in > 0) {
                $where .= " and vbd.SOKYHIEU like ? and (vbd.SOKYHIEU like ? OR vbd.SOKYHIEU_IN = ?)";
                $param[] = '%' . $parameter['SOKYHIEU'] . '%';
                $parameter['SOKYHIEU'] = $sokyhieu_in . '/%';
                $param[] = $parameter['SOKYHIEU'];
                $param[] = $sokyhieu_in;
            } else if ($sokyhieu_in == 0) {
                $where .= " and vbd.SOKYHIEU like ? ";
                $parameter['SOKYHIEU'] = '%' . $parameter['SOKYHIEU'] . '%';
                $param[] = $parameter['SOKYHIEU'];
            }
        }
        $strlimit = "";
        if ($limit > 0) {
            $strlimit = " LIMIT $offset,$limit";
        }
        if ($order != "") {
            $order = "$order";
        }

        //Fulltext
        if ($parameter['TRICHYEU'] != "") {
            if ((int) $parameter['TRICHYEU_ISLIKE'] > 0) {
                if ($parameter['INFILE'] == 1) {
                    //Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
                    $select .=",match(dk.CONTENT) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "') AS `INFILE`";
                    $join .= " left join " . QLVBDHCommon::Table('gen_filedinhkem') . " dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3";
                    $where .= " and " . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? "(" : "") . "match(dk.CONTENT) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "' IN BOOLEAN MODE) ";
                    if ($isseeall) {
                        $order .= " ,match(dk.CONTENT) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "') DESC" . $parameter['INNAME'] == 1 ? "," : "";
                    } else {
                        //$order .= " ,`INFILE` DESC";
                    }
                }
                if ($parameter['INNAME'] == 1) {
                    //Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_VANBANDEN'));
                    $select .=",match(vbd.TRICHYEU) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "') AS `INNAME`";
                    $where .= " " . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? "or" : "and") . " match(vbd.TRICHYEU) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "' IN BOOLEAN MODE)" . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? ")" : "") . " ";
                    if ($isseeall) {
                        $order .= " ,match(vbd.TRICHYEU) against ('" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "') DESC";
                    } else {
                        //$order .= " ,`INNAME` DESC";
                    }
                }
            } else {
                if ($parameter['INFILE'] == 1) {
                    $join .= " left join " . QLVBDHCommon::Table('gen_filedinhkem') . " dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3";
                    $where .= " and " . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? "(" : "") . "dk.CONTENT like '%" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "%' ";
                }
                if ($parameter['INNAME'] == 1) {
                    $where .= " " . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? "or" : "and") . " vbd.TRICHYEU like '%" . str_replace("\\", "\\\\", str_replace("'", "''", $parameter['TRICHYEU'])) . "%' " . (($parameter['INFILE'] == 1 && $parameter['INNAME'] == 1) ? ")" : "") . " ";
                }
            }
        }

        if ($parameter['NGUOIXULY'] > 0) {
            $wherenguoixuly = "and exists(select * from " . QLVBDHCommon::Table('vbd_fk_vbden_hscvs') . " fkvbd1
 inner join " . QLVBDHCommon::Table('wf_processitems') . " pi1 on fkvbd1.ID_HSCV = pi1.`ID_O`
inner join " . QLVBDHCommon::Table('wf_processlogs') . " pl1 on pi1.ID_PI = pl1.ID_PI
where (pl1.ID_U_SEND = " . (int) $parameter['NGUOIXULY'] . " or pl1.ID_U_RECEIVE = " . (int) $parameter['NGUOIXULY'] . ")
AND fkvbd1.ID_VBDEN = vbd.ID_VBD
 )";
        } else {
            
        }


        //lay cac van ban duoc chuyen den cho nguoi tuong ung

        if ($isseeall) {

            $sql = "
				SELECT 
					`ID_VBD`,
					`ID_SVB`,
					`NGUOITAO`,
					`ID_LVVB`,
					`ID_HSCV`,
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
                    IS_LIENTHONG,
                    MASOLIENTHONG,
					1 as DA_XEM ,
					vbd.`NGAYHETHAN`,
					vbd.`HANXULYTOANBO`,
					vbd.`TRE`,
					vbd.`IS_FINISH`
				FROM
					" . QLVBDHCommon::Table('VBD_VANBANDEN') . " vbd
					$join
				WHERE
					$where $str_insvb 
				ORDER BY $order,NGAYDEN DESC
				$strlimit
			";
        } else {


            $sql = "
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
					min(DA_XEM) as DA_XEM,
					vbd.`NGAYHETHAN`,
					vbd.`HANXULYTOANBO`,
					vbd.`TRE`,
					vbd.`IS_FINISH`,
                                        vbd.`NHIEMVU`,
                                        vbd.`TIENDO_GIAOVIEC`,
                                        vbd.`NOIDUNG_GIAOVIEC`,
                                        vbd.`HANXULY_GIAOVIEC`,
                                        vbd.`ID_U_XULYGIAOVIEC`,
                                        vbd.`ID_U_PHOIHOPGIAOVIEC`,
                                        vbd.`IS_THEODOI`,
                                        vbd.`ID_PI`
				FROM
				(
					SELECT 
						pl.DATESEND as NGAYGUI , 
						vbd.`ID_VBD`,
					   `ID_SVB`,
					   `NGUOITAO`,
					   `ID_LVVB`,
					   hscv.`ID_HSCV`,
                                           vbd.`NHIEMVU`,
                                            hscv.`TIENDO_GIAOVIEC`,
                                            hscv.`NOIDUNG_GIAOVIEC`,
                                            hscv.`HANXULY_GIAOVIEC`,
											hscv.`ID_U_XULY` as ID_U_XULYGIAOVIEC,
                                            hscv.`ID_U_PHOIHOPGIAOVIEC`,
                                            hscv.`ID_PI`,
                                            hscv.`IS_THEODOI`,
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
					   `COQUANNHAN_TEXT`
						$select
						
						, 1 AS DA_XEM,
					vbd.`NGAYHETHAN`,
					vbd.`HANXULYTOANBO`,
					vbd.`TRE`,
					vbd.`IS_FINISH`
					FROM
						" . QLVBDHCommon::Table('VBD_VANBANDEN') . " vbd
						 join " . QLVBDHCommon::Table('vbd_fk_vbden_hscvs') . " fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
						 join " . QLVBDHCommon::table("HSCV_HOSOCONGVIEC") . " hscv  on hscv.ID_HSCV = fk_v_h.ID_HSCV
						 join " . QLVBDHCommon::table("WF_PROCESSITEMS") . "  pi on pi.ID_O = hscv.ID_HSCV
						 join " . QLVBDHCommon::table("WF_PROCESSLOGS") . " pl  on pi.ID_PI = pl.ID_PI
						 $join
						 WHERE
						 $where and (  pl.ID_U_RECEIVE = $id_u ) and vbd.IS_LIENTHONG = 1 $wherenguoixuly
						 GROUP BY vbd.ID_VBD
					
					UNION
					
					SELECT 
						lc.NGAYCHUYEN as NGAYGUI, 
						vbd.`ID_VBD`,
					   `ID_SVB`,
					   `NGUOITAO`,
					   `ID_LVVB`,
					    hscv.`ID_HSCV`,
                                           vbd.`NHIEMVU`,
                                            hscv.`TIENDO_GIAOVIEC`,
                                            hscv.`NOIDUNG_GIAOVIEC`,
                                            hscv.`HANXULY_GIAOVIEC`,
                                            hscv.`ID_U_XULY` as ID_U_XULYGIAOVIEC,
                                            hscv.`ID_U_PHOIHOPGIAOVIEC`,
                                            hscv.`ID_PI`,
                                             hscv.`IS_THEODOI`,
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
					   `COQUANNHAN_TEXT`
						$select , lc.DA_XEM as DA_XEM,
					vbd.`NGAYHETHAN`,
					vbd.`HANXULYTOANBO`,
					vbd.`TRE`,
					vbd.`IS_FINISH`
					FROM
						" . QLVBDHCommon::Table('VBD_VANBANDEN') . " vbd
						inner join " . QLVBDHCommon::Table('VBD_DONGLUANCHUYEN') . " lc on lc.ID_VBD = vbd.ID_VBD
						JOIN vbd_fk_vbden_hscvs_2016 fk_v_h ON fk_v_h.ID_VBDEN = vbd.ID_VBD
                                                JOIN HSCV_HOSOCONGVIEC_2016 hscv ON hscv.ID_HSCV = fk_v_h.ID_HSCV
                                                $join
					WHERE
						$where and vbd.IS_LIENTHONG = 1 $wherenguoixuly
				) vbd
				GROUP BY ID_VBD
				ORDER BY $order , NGAYGUI DESC
				
				$strlimit
			";
        }

        if (!$isseeall) {
            $param = array_merge($param, $param);
        }
        $row = $db->query($sql, $param);
       //echo $sql;var_dump($param);
        return $row->fetchAll();
    }
    
    
    /**
	 * Đếm bản ghi các hồ sơ công việc dựa trên tham số đầu vào.
	 * Danh sách tham số đầu vào
	 * NGAY_BD
	 * NGAY_KT
	 * NAME
	 * ID_U
	 * @param array $parameter
	 */
	function Count_vbd($parameter,$isseeall){
		
		
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;
		
		//lay so van ban den tuong ung voi co quan nguoi dung
		//if($isseeall==0){
		$svbs = SovanbanModel::getData(QLVBDHCommon::getYear(),0);
		$arr_svb = array();
		foreach($svbs as $it_svb){
			$arr_svb[] = $it_svb["ID_SVB"];
		}
		if(count($arr_svb)){
			$str_insvb = implode($arr_svb,',');
			$str_insvb = " and ID_SVB in (".$str_insvb.")";
		}else{
			$str_insvb = " and ID_SVB in (0)";
		}
		//}else{
			//$str_insvb=" ";
		//}
		
		if($isseeall && !$str_insvb)
				return 0;
		
		//$isseeall = (hosocongviecModel::isVanthu($id_u) || (hosocongviecModel::isAlowSeeAllVbDen() && ($parameter['IS_SEE_ALL']==1)));
		
		//Build query
		$sql = "";
		$where = "(1=1)";
		$join = "";
		$select = "";
		
                //check loại nhiệm vụ nội bộ
               // if($parameter['IS_LIENTHONG'] == 1 AND $parameter['IS_NOIBO'] == 1){
                        //$where .= " ";
               // }

                //check loại nhiệm vụ nội bộ
                //else if($parameter['IS_NOIBO'] == 1){
                        //$where .= " and vbd.IS_LIENTHONG IS NULL";
               // }

                //check loại nhiệm vụ nội bộ
               // else if ($parameter['IS_LIENTHONG'] == 1){
                       $where .= " and vbd.IS_LIENTHONG = 1 and vbd.IS_GIAOVIEC =1";
               // }
                
		//Check cơ quan
		if($parameter['ID_CQ']>0 && $parameter['ID_CQ']!=""){
			$where .= " and vbd.ID_CQ = ?";
			$param[] = $parameter['ID_CQ'];
		}
		else if($parameter['COQUANBANHANH_TEXT']!="") 
		{
			$where .= " and vbd.COQUANBANHANH_TEXT = ?";
			$param[] = $parameter['COQUANBANHANH_TEXT'];
		}
		
		//check sổ văn bản
	    if($parameter['ID_SVB']>0 && $parameter['ID_SVB']!=""){
			$where .= " and vbd.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}else{
			if(count($arr_svb)){
				$str_insvb = implode($arr_svb,',');
				$str_insvb = " and vbd.ID_SVB in (".$str_insvb.")";
			
			}
			
		}
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		//check linh vuc van ban
		if($parameter['ID_LVVB']>0 && $parameter['ID_LVVB']!=""){
			$arr_temp = array();
			hosocongviecModel::selectlvvb($parameter['ID_LVVB'],$arr_temp);
			if(count($arr_temp)){
				$where .= " and ( vbd.ID_LVVB in ". "( ". implode(",",$arr_temp) ."  )".")";
				//$param[] = $parameter['ID_LVVB'];
				//$param[] = "( ". implode(",",$arr_temp) ."  )";
			}
			
		}
	    //Check ngày đến bd
		if($parameter['NGAYDEN_BD']!=""){
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:00";
			$where .= " and vbd.NGAYDEN >= ?";
			$param[] = $ngayden_bd;
		}
		
		//Check ngày đến kt
		if($parameter['NGAYDEN_KT']!=""){
			$ngayden_kt = $parameter['NGAYDEN_KT']." 23:59:59";
			$where .= " and vbd.NGAYDEN <= ?";
			$param[] = $ngayden_kt;
		}
	    
		//Check ngày ban hành bd
		if($parameter['NGAYBANHANH_BD']!=""){
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:00";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}
		
		//Check ngày ban hành kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}

		//Check so den		
		if($parameter['SODEN']!=""){
			
			if($parameter['SODEN_ISLIKE'])
			{
				$where .= " and ( vbd.SODEN REGEXP '".$parameter['SODEN']."*' ) ";
				//$param[] = $parameter['SODEN'];

			}else{

				$soden_in = (int)$parameter['SODEN']; 
				if((String)$soden_in != $parameter['SODEN'])
				{	
					
					$where .= " and vbd.SODEN = ?  ";
					$param[] = $parameter['SODEN'];
				}else{
					$where .= " and (vbd.SODEN = ? or vbd.SODEN_IN = ?) ";
					$param[] = $parameter['SODEN'];
					$param[] = $soden_in;
				}
			}
		}
		
		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$sokyhieu_in = (int)$parameter['SOKYHIEU'];
			if($sokyhieu_in >0){
				$where .= " and vbd.SOKYHIEU like ? and (vbd.SOKYHIEU like ? OR vbd.SOKYHIEU_IN = ?)";
				$param[] = '%'.$parameter['SOKYHIEU'].'%';
				$parameter['SOKYHIEU'] =$sokyhieu_in.'/%';
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbd.SOKYHIEU like ? ";
				$parameter['SOKYHIEU'] ='%'.$parameter['SOKYHIEU'].'%';
				$param[] = $parameter['SOKYHIEU'];
				
			}
		}

		if($parameter['NGUOIXULY']>0){
			$wherenguoixuly = "and exists(select * from ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fkvbd1
 inner join ".QLVBDHCommon::Table('wf_processitems')." pi1 on fkvbd1.ID_HSCV = pi1.`ID_O`
inner join ".QLVBDHCommon::Table('wf_processlogs')." pl1 on pi1.ID_PI = pl1.ID_PI
where (pl1.ID_U_SEND = ".(int)$parameter['NGUOIXULY']." or pl1.ID_U_RECEIVE = ".(int)$parameter['NGUOIXULY'].")
AND fkvbd1.ID_VBDEN = vbd.ID_VBD
 )";
		}else{
			}


		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}
		
		//Fulltext
		if($parameter['TRICHYEU']!=""){
			if((int)$parameter['TRICHYEU_ISLIKE']>0){
				if($parameter['INFILE']==1){
					//Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
					$select .=",match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INFILE`";
					$join .= " left join ".QLVBDHCommon::Table('gen_filedinhkem')." dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3";
					$where .= " and ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"(":"")."match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE) ";
					if($isseeall){
						$order .= " ,match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
					}else{
						//$order .= " ,`INFILE` DESC";
					}
				}
				if($parameter['INNAME']==1){
					//Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_VANBANDEN'));
					$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
					$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
					if($isseeall){
						$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
					}else{
						//$order .= " ,`INNAME` DESC";
					}
				}
			}else{
				if($parameter['INFILE']==1){
					$join .= " left join ".QLVBDHCommon::Table('gen_filedinhkem')." dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3";
					$where .= " and ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"(":"")."dk.CONTENT like '%".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."%' ";
				}
				if($parameter['INNAME']==1){
					$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." vbd.TRICHYEU like '%".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."%' ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				}
			}
		}
		

		if($parameter['IS_PHOBIEN'] == 1){
			
			$sql = "
			SELECT count(distinct vbd.ID_VBD) as CNT
					
					
				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd  where $where and IS_PHOBIEN =1 
						$str_insvb  $wherenguoixuly
						";
			$row = $db->query($sql,$param);
			$row = $row->fetch();
			return $row["CNT"];
			
		}

		if($parameter['CHUA_DOC'] == 1)
		{
			
			
			$sql = "
			SELECT count(distinct vbd.ID_VBD) as CNT
					 
				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on vbd.ID_VBD = lc.ID_VBD 
					where  $where and lc.DA_XEM = 0  $wherenguoixuly
					 
					
			";
			
			$row = $db->query($sql,$param);
			$row = $row->fetch();
			return $row["CNT"];

		}
		
		//lay cac van ban duoc chuyen den cho nguoi tuong ung
		
		if($isseeall){
			
			$sql = "
				SELECT 
					count(*) as CNT
					
				FROM
					".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					$join
				WHERE
					$where $str_insvb 
				ORDER BY NGAYDEN DESC
				$strlimit
			";
		}else{
			$sql = "
				SELECT 
					count( distinct `ID_VBD` ) as CNT
					
				FROM
				(
					SELECT 
					
						vbd.`ID_VBD`
					  
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						 join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
						 join ". QLVBDHCommon::table("HSCV_HOSOCONGVIEC") ." hscv  on hscv.ID_HSCV = fk_v_h.ID_HSCV
						 join ". QLVBDHCommon::table("WF_PROCESSITEMS") ."  pi on pi.ID_O = hscv.ID_HSCV
						 join ".QLVBDHCommon::table("WF_PROCESSLOGS")." pl  on pi.ID_PI = pl.ID_PI
						 $join
						 WHERE
						 $where and (  pl.ID_U_RECEIVE = $id_u ) and vbd.IS_LIENTHONG = 1 $wherenguoixuly
						 GROUP BY vbd.ID_VBD
					
					UNION
					
					SELECT 
						vbd.`ID_VBD`
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD
						$join
					WHERE
						$where  and vbd.IS_LIENTHONG = 1 $wherenguoixuly
				) vbd
				
			";
		}
		
		if(!$isseeall){
			$param = array_merge($param,$param);
		}
		//echo $sql;exit;
		$row = $db->query($sql,$param);
		
		$row = $row->fetch();
		
		return $row["CNT"];
	}

    static function getIDLoaiCV() {
        $sql = " select ID_LCV from gscv_loaicongviec";
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $qr = $dbAdapter->query($sql);
        $re = $qr->fetchAll();
        $arr = array();
        foreach ($re as $it_re) {
            $arr[] = $it_re[ID_LCV];
        }
        return $arr;
    }

    static function getTenLoai($idloai) {
        if ($idloai > 0) {
            $sql = " select NAME from gscv_loaicongviec where ID_LCV=?";
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $qr = $dbAdapter->query($sql, array($idloai));
            $re = $qr->fetch();
            return $re["NAME"];
        } else {
            return "";
        }
    }

    static function getTenLoaibyCode($code) {
        if ($code !=="") {
            $sql = " select NAME from gscv_loaicongviec where CODE=?";
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $qr = $dbAdapter->query($sql, array($code));
            $re = $qr->fetch();
            return $re["NAME"];
        } else {
            return "";
        }
    }

    /**
     * Lấy danh sách các hồ sơ công việc dựa trên tham số đầu vào.
     * NGAY_BD
     * NGAY_KT
     * NAME
     * ID_U
     * @param array $parameter
     */
    function getDanhSachNhiemVu() {
        global $auth;
        global $db;
        $user = $auth->getIdentity();
        $id_u = $user->ID_U;

        $sql = " 
                        SELECT `ID_VBD`,
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
                               min(DA_XEM) AS DA_XEM,
                               vbd.`NGAYHETHAN`,
                               vbd.`HANXULYTOANBO`,
                               vbd.`TRE`,
                               vbd.`IS_FINISH`
                        FROM
                          (SELECT pl.DATESEND AS NGAYGUI,
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
                                                 `COQUANNHAN_TEXT`,
                                                 1 AS DA_XEM,
                                                      vbd.`NGAYHETHAN`,
                                                      vbd.`HANXULYTOANBO`,
                                                      vbd.`TRE`,
                                                      vbd.`IS_FINISH`
                           FROM `".QLVBDHCommon::Table("VBD_VANBANDEN")."` vbd
                           JOIN `".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."` fk_v_h ON fk_v_h.ID_VBDEN = vbd.ID_VBD
                           JOIN `".QLVBDHCommon::Table("HSCV_HOSOCONGVIE")."`  hscv ON hscv.ID_HSCV = fk_v_h.ID_HSCV
                           JOIN `".QLVBDHCommon::Table("WF_PROCESSITEMS")."`  pi ON pi.ID_O = hscv.ID_HSCV
                           JOIN `".QLVBDHCommon::Table("WF_PROCESSLOGS")."`  pl ON pi.ID_PI = pl.ID_PI
                           WHERE (1=1)
                             AND (pl.ID_U_RECEIVE = $id_u)
                           GROUP BY vbd.ID_VBD
                           UNION SELECT lc.NGAYCHUYEN AS NGAYGUI,
                                                         vbd.`ID_VBD`,
                                                         `ID_SVB`,
                                                         `NGUOITAO`,
                                                         `ID_LVVB`,
                                                         `ID_HSCV`,
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
                                                         lc.DA_XEM AS DA_XEM,
                                                                      vbd.`NGAYHETHAN`,
                                                                      vbd.`HANXULYTOANBO`,
                                                                      vbd.`TRE`,
                                                                      vbd.`IS_FINISH`
                           FROM `".QLVBDHCommon::Table("VBD_VANBANDEN")."` vbd
                           INNER JOIN `".QLVBDHCommon::Table("VBD_DONGLUANCHUYEN")."` lc ON lc.ID_VBD = vbd.ID_VBD
                           WHERE (1=1)
                             AND lc.`NGUOINHAN`= $id_u) vbd
                        GROUP BY ID_VBD
                        ORDER BY NGAYDEN DESC,
                                 NGAYGUI DESC $strlimit";
        //echo $sql; var_dump($param);
        $row = $db->query($sql);
        //echo $sql;var_dump($param);
        return $row->fetchAll();
    }

    public function FindUserById($id_u) {
        global $auth;
        global $db;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "
			SELECT
				u.*,emp.FIRSTNAME,emp.LASTNAME,dep.KYHIEU_PB as DEPNAME,dep.ID_DEP,emp.ID_EMP
			FROM
				qtht_users u
				inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
				inner join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			WHERE
				u.ID_U = ?
		";
        $qr = $dbAdapter->query($sql, array($id_u));

        return $qr->fetch();
    }

    /**
     * Chuyển dữ liệu loại công việc tới combobox
     */
    static function WriteLOAICV($sel, $des) {
        if (!$des) {
            $des = " -- Chọn loại công việc -- ";
        }
        $arrwhere = array();
        $strwhere = "(1=1)";
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                gscv_loaicongviec
            WHERE
                $strwhere and ACTIVE=1 ORDER BY NAME     
        ", $arrwhere);
        $data = $result->fetchAll();

        $html = "";
        $html .= "<option value=''>" . $des . "</option>";
        foreach ($data as $row) {
            $html .= "<option value='" . $row["CODE"] . "' " . ($row["CODE"] == $sel ? "selected" : "") . ">" . $row["NAME"] . "</option>";
        }
        return $html;
    }
    
    
    public function GetIsFinish($id_pi) {
        global $auth;
        global $db;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "
			SELECT
				IS_FINISH
			FROM
				`".QLVBDHCommon::Table("wf_processitems")."`
			WHERE
				ID_PI= ?
		";
        $qr = $dbAdapter->query($sql, array($id_pi));

        return $qr->fetch();
    }
    

}
