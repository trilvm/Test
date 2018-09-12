<?php
/**
* Các hàm query sử dụng cho danh sách văn bản đến
*/

class vbd_vanbandenModel{

	function is_UserOnGroup($id_u,$code_g){
		$sql = "
			select count(*) as CNT
			from qtht_users u
			inner join fk_users_groups fk on u.ID_U = fk.ID_U
			inner join qtht_groups g on fk.ID_G = g.ID_G
			where u.ID_U = ?  and g.CODE = ?
		";
		try{
			$db =  Zend_Db_Table::getDefaultAdapter();
			$qr = $db->query($sql,array($id_u,$code_g));
			$row = $qr->fetch();
			return (int)$row['CNT'];
		}catch(Exception $ex){
			return 0;
		}
	}

	function doFilterParams($parameter){

		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;

		//lay so van ban den tuong ung voi co quan nguoi dung
		//$svbs = SovanbanModel::getDataByCQ(QLVBDHCommon::getYear(),$user->ID_CQ,0);
		$svbs = SovanbanModel::selectWithDep(0);
		$arr_svb = array();
		foreach($svbs as $it_svb){
			$arr_svb[] = $it_svb["ID_SVB"];
		}
		if(count($arr_svb)){
			$str_insvb = implode($arr_svb,',');
			$str_insvb = " and ID_SVB in (".$str_insvb.")";
		}

		//var_dump($arr_svb);
		if($isseeall && !$str_insvb)
			return array();
		//$isseeall = (hosocongviecModel::isVanthu($id_u) || (hosocongviecModel::isAlowSeeAllVbDen() && ($parameter['IS_SEE_ALL']==1)));

		//Build query
		$sql = "";
		$where = "(1=1)";
		$join = "";
		$select = "";

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
				//if($isseeall)
					//$where .= $str_insvb;
			}

		}
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}

	    //Check ngày đến bd
		if($parameter['NGAYDEN_BD']!=""){
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:01";
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
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}

		//Check ngày ban hành kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}


		//check ten co quan ban hanh

		if($parameter['COQUANBANHANH_TEXT']!=""){
			$select .=",match(vbd.COQUANBANHANH_TEXT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['COQUANBANHANH_TEXT']))."') AS `COQUANBANHANH_TEXT`";
			$where .= " and match(vbd.COQUANBANHANH_TEXT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['COQUANBANHANH_TEXT']))."' IN BOOLEAN MODE ) ";
			if($isseeall){
					$order .= " ,match(vbd.COQUANBANHANH_TEXT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['COQUANBANHANH_TEXT']))."') DESC";
			}else{
					$order .= " ,`COQUANBANHANH_TEXT` DESC";
			}
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
				$where .= " and (vbd.SOKYHIEU = ? OR vbd.SOKYHIEU_IN = ?)";
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbd.SOKYHIEU = ? ";
				$param[] = $parameter['SOKYHIEU'];

			}
		}


		//Fulltext
		if($parameter['TRICHYEU']!=""){
			if($parameter['INFILE']==1){
				//Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
				$select .=",match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INFILE`";
				$join .= " left join ".QLVBDHCommon::Table('gen_filedinhkem')." dk on dk.ID_OBJECT = vbd.ID_VBD and dk.TYPE=3";
				$where .= " and ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"(":"")."match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE) ";
				if($isseeall){
					$order .= " ,match(dk.CONTENT) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INFILE` DESC";
				}
			}
			if($parameter['INNAME']==1){
				//Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_VANBANDEN'));
				$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
				$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				if($isseeall){
					$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INNAME` DESC";
				}
			}
		}

		$arr_result = array();
		$arr_result["WHERE"] = $where;
		$arr_result["PARAMS"] = $param;
		$arr_result["JOIN"] = $join;
		$arr_result["ORDER"] = $order;
		$arr_result["SELECT"] = $select;
		$arr_result["STRSVB"] = $str_insvb;
		return $arr_result;
	}

	function count($parameter){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;
		$isseeall = (hosocongviecModel::isVanthu($id_u) ||(hosocongviecModel::isAlowSeeAllVbDen()&&($parameter['IS_SEE_ALL']==1)));


		$arr_re = vbd_vanbandenModel::doFilterParams($parameter);
		$where = $arr_re["WHERE"];
		$param = $arr_re["PARAMS"];
		$join = $arr_re["JOIN"];
		$order = $arr_re["ORDER"];
		if($isseeall)
			$str_insvb = $arr_re["STRSVB"] ;
		if($parameter['IS_PHOBIEN'] == 1){

			$sql = "
			SELECT count(distinct vbd.ID_VBD) as CNT
				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd  where $where and IS_PHOBIEN =1
						$str_insvb
						";
			$qr = $db->query($sql,$param);
			$row = $qr->fetch();
			return $row["CNT"];


		}

		if($parameter['CHUA_DOC'] == 1)
		{


			$sql = "
			SELECT count(distinct vbd.ID_VBD) as CNT
				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on vbd.ID_VBD = lc.ID_VBD
					where  $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $user->ID_U ."


			";
			$qr = $db->query($sql,$param);
			$row = $qr->fetch();
			return $row["CNT"];
		}

		//lay cac van ban duoc chuyen den cho nguoi tuong ung
		//kiem tra nếu là trưởng phòng
		$is_depleader = vbd_vanbandenModel::is_UserOnGroup($id_u,'LDP');
		$join_depleader_lc = "";
		$where_depleader_lc = "";
		$join_depleader_cxl = "";
		$where_depleader_cxl = "";
		if($is_depleader){
			$join_depleader_lc =
			"	join qtht_users u on lc.NGUOINHAN = u.ID_U
				join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			";

			$where_depleader_lc = "
				and emp.ID_DEP= ". $user->ID_DEP ."
			";

			$join_depleader_cxl = "

				join qtht_users u on pl.ID_U_RECEIVE = u.ID_U
				join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			";

			$where_depleader_cxl = "
				and emp.ID_DEP= ". $user->ID_DEP ."
			";
		}else{

			$where_depleader_lc = "
				and lc.NGUOINHAN= ". $user->ID_U ."
			";

			$where_depleader_cxl = "and (  pl.ID_U_RECEIVE = $id_u ) " ;
		}

		if($isseeall){
			$sql = "
				SELECT
					count(distinct ID_VBD) as CNT
				FROM
					".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					$join
				WHERE
					$where $str_insvb

			";
		}else{
			$sql = "
				select count(distinct ID_VBD) as CNT

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
						 $join_depleader_cxl
						 $join
						 WHERE
						 $where $where_depleader_cxl
						 GROUP BY vbd.ID_VBD

					UNION

					SELECT

						vbd.`ID_VBD`
					FROM
							".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD
						$join_depleader_lc
						$join
					WHERE
						$where $where_depleader_lc
				) vbd





			";
		}
		if(!$isseeall){
			$param = array_merge($param,$param);
		}

		$qr = $db->query($sql,$param);
			$row = $qr->fetch();
			return $row["CNT"];

	}

	function selectAll($parameter,$offset,$limit,$order,$isseeall=0){


		$isseeall = 1;
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;
		$isseeall = ((hosocongviecModel::isVanthu($id_u) ||(hosocongviecModel::isAlowSeeAllVbDen())));
		$isseeall = $isseeall&&($parameter['IS_SEE_ALL']==1);

		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}

		$arr_re = vbd_vanbandenModel::doFilterParams($parameter);
		$where = $arr_re["WHERE"];
		$param = $arr_re["PARAMS"];
		$join = $arr_re["JOIN"];
		$order .= $arr_re["ORDER"];
		$select .= $arr_re["SELECT"];
		if($isseeall)
			$str_insvb = $arr_re["STRSVB"] ;
		//echo $order; exit;



		if($parameter['IS_PHOBIEN'] == 1){

			$sql = "
			SELECT distinct
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
					`COQUANBANHANH_TEXT`,
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

				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd  where $where and IS_PHOBIEN =1
						$str_insvb
						";
			$row = $db->query($sql,$param);
			return $row->fetchAll();

		}

		if($parameter['CHUA_DOC'] == 1)
		{


			$sql = "
			SELECT distinct
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
					 lc.DA_XEM as DA_XEM
					 $select
				FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on vbd.ID_VBD = lc.ID_VBD
					where  $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $user->ID_U ."

					$order
					$strlimit
			";
			//echo $sql;
			//exit;
			$row = $db->query($sql,$param);
			return $row->fetchAll();
		}

		//trường hợp xem danh sách văn bản đến của phòng

		//kiem tra nếu là trưởng phòng
		$is_depleader = vbd_vanbandenModel::is_UserOnGroup($id_u,'LDP');
		$join_depleader_lc = "";
		$where_depleader_lc = "";
		$join_depleader_cxl = "";
		$where_depleader_cxl = "";
		if($is_depleader){
			$join_depleader_lc =
			"	join qtht_users u on lc.NGUOINHAN = u.ID_U
				join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			";

			$where_depleader_lc = "
				and emp.ID_DEP= ". $user->ID_DEP ."
			";

			$join_depleader_cxl = "

				join qtht_users u on pl.ID_U_RECEIVE = u.ID_U
				join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			";

			$where_depleader_cxl = "
				and emp.ID_DEP= ". $user->ID_DEP ."
			";
		}else{

			$where_depleader_lc = "
				and lc.NGUOINHAN= ". $user->ID_U ."
			";

			$where_depleader_cxl = "and (  pl.ID_U_RECEIVE = $id_u ) " ;
		}

		//lay cac van ban duoc chuyen den cho nguoi tuong ung

		//echo $str_insvb;
		if($isseeall){
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
					1 as DA_XEM
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
				FROM
				(
					SELECT
						pl.DATESEND as NGAYGUI ,
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
					   `COQUANNHAN_TEXT`
						$select

						, 1 AS DA_XEM

					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						 join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
						 join ". QLVBDHCommon::table("HSCV_HOSOCONGVIEC") ." hscv  on hscv.ID_HSCV = fk_v_h.ID_HSCV
						 join ". QLVBDHCommon::table("WF_PROCESSITEMS") ."  pi on pi.ID_O = hscv.ID_HSCV
						 join ".QLVBDHCommon::table("WF_PROCESSLOGS")." pl  on pi.ID_PI = pl.ID_PI
						 $join_depleader_cxl
						 $join
						 WHERE
						 $where $where_depleader_cxl
						 GROUP BY vbd.ID_VBD

					UNION

					SELECT
						lc.NGAYCHUYEN as NGAYGUI,
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
					   `COQUANNHAN_TEXT`
						$select ,
						(
						CASE lc.NGUOINHAN
						WHEN $id_u THEN lc.DA_XEM
						ELSE 1
						END
						)
						as DA_XEM

					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD
						$join_depleader_lc
						$join
					WHERE
						$where $where_depleader_lc
				) vbd



				GROUP BY vbd.ID_VBD
				$order , NGAYGUI DESC
				$strlimit
			";
		}
		//echo $order;
		if(!$isseeall){
			$param = array_merge($param,$param);
		}
		//echo $sql; //exit;
		$row = $db->query($sql,$param);
		return $row->fetchAll();
	}
	
	//phuongpt
	public function CountVBDangXulyBT($idUser, $idDep, $dateBegin, $dateEnd)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND >= NOW() AND cl.`ALIAS` = 'VBD'";
		$params = array();
		$sql = "SELECT COUNT(hscv.ID_HSCV) AS CNT FROM ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv
				INNER JOIN ".QLVBDHCommon::Table('wf_processitems')." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.ID_P
				INNER JOIN wf_classes cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result['CNT'];
		
	}
	
	public function CountVBDangXulyTH($idUser, $idDep, $dateBegin, $dateEnd)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE hscv.IS_THEODOI = 0 AND hscv.IS_CHOXULY = 0 AND psi.IS_FINISH = 0 
				   AND psi.DATEEND < NOW() AND cl.`ALIAS` = 'VBD'";
		$inner = "";
		$params = array();
		$sql = "SELECT COUNT(hscv.ID_HSCV) AS CNT FROM ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv
				INNER JOIN ".QLVBDHCommon::Table('wf_processitems')." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.ID_P
				INNER JOIN wf_classes cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result['CNT'];
	}
	
	public function CountVBDaXulyBT($idUser, $idDep, $dateBegin, $dateEnd)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 0 
				   AND cl.`ALIAS` = 'VBD'";
		$inner = "";
		$params = array();
		$sql = "SELECT COUNT(hscv.ID_HSCV) AS CNT FROM ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv
				INNER JOIN ".QLVBDHCommon::Table('wf_processitems')." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.ID_P
				INNER JOIN wf_classes cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result['CNT'];
	}
	
	public function CountVBDaXulyTH($idUser, $idDep, $dateBegin, $dateEnd)
	{
		$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$where = " WHERE psi.IS_FINISH = 1 AND psi.IS_TRE = 1 
				   AND cl.`ALIAS` = 'VBD'";
		$inner = "";
		$params = array();
		$sql = "SELECT COUNT(hscv.ID_HSCV) AS CNT FROM ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv
				INNER JOIN ".QLVBDHCommon::Table('wf_processitems')." psi ON hscv.ID_PI = psi.ID_PI
				INNER JOIN wf_processes pc ON psi.ID_P = pc.ID_P
				INNER JOIN wf_classes cl ON pc.ID_C = cl.ID_C
				INNER JOIN qtht_users u ON psi.ID_U = u.ID_U
				INNER JOIN qtht_employees e On u.ID_EMP = e.ID_EMP
				INNER JOIN qtht_departments d ON e.ID_DEP = d.ID_DEP
				";
		if($idUser > 0)
		{
			$where .= " AND psi.ID_U = ?";
			$params = $idUser;
		}
		if($idDep > 0) 
		{
			$where .= " AND d.ID_DEP = ?";
			$params = $idDep;
		}
		if($dateBegin > 0 && $dateEnd > 0)
		{
			$where .= " AND LASTCHANGE >= ? AND LASTCHANGE <= ?";
			$params = $dateBegin;
			$params = $dateEnd;
		}
		$sql .= $where;
		$result = $dbAdapter->query($sql, $params)->fetch();
		return $result['CNT'];
	}
}