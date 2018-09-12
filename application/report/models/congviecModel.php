<?php
require_once 'qtht/models/UsersModel.php';
class congviecModel{
	function SelectAll($param){
		global $db;
		global $auth;
		$user = $auth->getIdentity();
		$str_id_u = "";
		$where = "(1=1)";
		if($param['fromdate']!=""){
			if($param['todate']!=""){
				$where .= " AND cv.NGAYTAO>='".QLVBDHCommon::doDateViet2Standard($param['fromdate']."/".QLVBDHCommon::getYear())."'
				AND cv.NGAYTAO<='".QLVBDHCommon::doDateViet2Standard($param['todate']."/".QLVBDHCommon::getYear())."'
				";
			}else{
				$where .= " AND cv.NGAYTAO>='".QLVBDHCommon::doDateViet2Standard($param['fromdate']."/".QLVBDHCommon::getYear())."'";
			}
		}else if($param['todate']!=""){
			$where .= " AND cv.NGAYTAO<='".QLVBDHCommon::doDateViet2Standard($param['todate']."/".QLVBDHCommon::getYear())."'";
		}
		if(is_array($param['sel_pb']) && $param['sel_pb'][0]>0){
			$where .= " AND (empnyc.ID_DEP in (".implode(",",$param['sel_pb']).")";
			$where .= " OR empnxl.ID_DEP in (".implode(",",$param['sel_pb'])."))";
		}
		if (is_array($param['ID_U']) && $param['ID_U'][0] != 1 )
		{
			foreach($param['ID_U'] as $uname)
			{
				$id_u = UsersModel::getIdByUsetname($uname);
				$str_id_u .= $id_u.",";
			}
			$where .= " AND cv.NGUOIXULY IN (".substr($str_id_u,0,-1).")";
		}
		$sql="
			SELECT 
				cv.*,
				CONCAT(empnyc.FIRSTNAME,' ',empnyc.LASTNAME) as NAMENGUOIYEUCAU,
				CONCAT(empnxl.FIRSTNAME,' ',empnxl.LASTNAME) as NAMENGUOIXULY,
				pl.HANXULY,
				pi.IS_FINISH,
				pl.DATESEND,
				hscv.IS_THEODOI
			FROM 
				".QLVBDHCommon::Table("hscv_congviecsoanthao")." cv
				inner join ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv on cv.ID_HSCV=hscv.ID_HSCV
				inner join QTHT_USERS unyc on cv.NGUOIYEUCAU=unyc.ID_U
				inner join QTHT_EMPLOYEES empnyc on unyc.ID_EMP = empnyc.ID_EMP
				inner join QTHT_USERS unxl on cv.NGUOIXULY=unxl.ID_U
				inner join QTHT_EMPLOYEES empnxl on unxl.ID_EMP = empnxl.ID_EMP
				inner join ".QLVBDHCommon::Table("wf_processitems")." pi on pi.ID_O=cv.ID_HSCV
				inner join ( select * from ".QLVBDHCommon::Table("wf_processlogs")." ORDER BY ID_PL DESC) pl on pl.ID_PI = pi.ID_PI  
			WHERE
				$where			
                                
			GROUP BY
				pl.ID_PI

			";
			//echo $sql;exit;
		$r = $db->query($sql);
		//var_dump($r->fetchAll());
		return $r->fetchAll();
	}
	
   
	function counttkedxl($fromdate,$todate,$sel_svb){	
			if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
		if($sel_svb > 0){							
				$where_svb = " AND vbd.ID_SVB = '".$sel_svb."'" ;			
						
		}else{ 
			$sel_cqnb = congviecModel::laycoquannoibo();
			//$where_svb = " AND svb.`ID_CQ` = '".$sel_cqnb."'" ;	
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT hsdxl.ID_U,COUNT(hsdxl.ID_HSCV) as TONG
					FROM  ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
						 INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
						 INNER join
						".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
						 inner join
                        ".QLVBDHCommon::Table("hscv_daxuly")."  hsdxl  on hsdxl.ID_HSCV= hscv.ID_HSCV 
						inner join vb_sovanban svb on svb.`ID_SVB`= vbd.`ID_SVB`
					WHERE (1 = 1) $where_fromdate  $where_todate $where_svb group by hsdxl.ID_U 
			";
			//echo $sql;exit;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		$arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;

	}

	function counttkecxl($fromdate,$todate,$sel_svb){	
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
		if($sel_svb > 0){							
				$where_svb = " AND vbd.ID_SVB = '".$sel_svb."'" ;				
		}else{ 
			$sel_cqnb = congviecModel::laycoquannoibo();			 
			    //$where_svb = " AND svb.`ID_CQ` = '".$sel_cqnb."'" ;	
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
						 INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
						 INNER join
						".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV			inner join vb_sovanban svb on svb.`ID_SVB`= vbd.`ID_SVB`	 
					WHERE (1 = 1) and (hscv.IS_CHOXULY != 1 or hscv.IS_CHOXULY is null) $where_fromdate $where_todate 
					$where_svb
					group by hsdgxl.ID_U

			";
			//echo $sql;
		$query = $dbAdapter->query($sql);
		 $re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}

	function counttkechoxl($fromdate,$todate,$sel_svb){	
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
		if($sel_svb > 0){							
				$where_svb = " AND vbd.ID_SVB = '".$sel_svb."'" ;				
		}else{ 
			$sel_cqnb = congviecModel::laycoquannoibo();			 
			    //$where_svb = " AND svb.`ID_CQ` = '".$sel_cqnb."'" ;	
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
				
					SELECT hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
						 INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
						 INNER join
						".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV			inner join vb_sovanban svb on svb.`ID_SVB`= vbd.`ID_SVB`	 
					WHERE (1 = 1) and  hscv.IS_CHOXULY = 1  $where_fromdate $where_todate $where_svb
					group by hsdgxl.ID_U

			";
			//echo $sql;exit;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		$arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}
    function countvbd($fromdate,$todate,$sel_pb) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        if ($sel_pb[0]!=0&&count($sel_pb)>1) {
        $listdep = implode(",",$sel_pb);}
        if ($sel_pb[0]==0&&count($sel_pb)==1) { 
        $sql="SELECT GROUP_CONCAT(ID_DEP) as LISTDEP FROM qtht_departments dep                         
			";       
//		     echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
                $listdep = $re[0]['LISTDEP'];
        }
        if ($sel_pb[0]!=0&&count($sel_pb)==1) {        
                $listdep = $sel_pb[0];
        }
//        UsersModel::getAllUserName();
        //print_r($listdep);exit;
        $sql="SELECT GROUP_CONCAT(ID_U) as LISTIDU FROM qtht_users users
              INNER JOIN `qtht_employees` emp on emp.`ID_EMP`=users.`ID_EMP`
              LEFT JOIN `qtht_departments` dep on dep.`ID_DEP`=emp.`ID_DEP` 
              WHERE dep.`ID_DEP` in (".$listdep.");           
			";       
//		     echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
//                echo "<pre>";print_r($re);exit;
        $listidu = $re[0]['LISTIDU'];
        if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
        if($listidu!=''){       						
				$where_svb = " AND (wfpl.`ID_U_RECEIVE` in (".$listidu.") or wfpl.`ID_U_SEND` in (".$listidu.")) ";				
		}
		// else{ 						 
			    // $where_svb = " AND (wfpl.`ID_U_RECEIVE` = ".$listidu." or wfpl.`ID_U_SEND` = ".$listidu.") ";	
			    // //$where_svb = " AND (wfpl.`ID_U_RECEIVE` in (".$listidu.") or wfpl.`ID_U_SEND` in (".$listidu.")) ";	
		// }
         	
		$sql="SELECT DISTINCT vbd.`ID_VBD` 
                      FROM ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
                      INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
                      INNER join ".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
                      INNER join ".QLVBDHCommon::Table("wf_processitems")." wfpi on hscv.`ID_PI` = wfpi.`ID_PI` 
                      INNER join ".QLVBDHCommon::Table("wf_processlogs")." wfpl on wfpl.`ID_PI` = wfpi.`ID_PI` 
                      WHERE (1=1) $where_fromdate  $where_todate $where_svb                    
			";
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();//                print_r($re);exit;
		return count($re);       
                
           
    }
    
    function countcv($fromdate,$todate,$sel_pb) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        if ($sel_pb[0]!=0&&count($sel_pb)>1) {
        $listdep = implode(",",$sel_pb);}
        if ($sel_pb[0]==0&&count($sel_pb)==1) { 
        $sql="SELECT GROUP_CONCAT(ID_DEP) as LISTDEP FROM qtht_departments dep                         
			";       
//		     echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
                $listdep = $re[0]['LISTDEP'];
        }
        if ($sel_pb[0]!=0&&count($sel_pb)==1) {        
                $listdep = $sel_pb[0];
        }
//        UsersModel::getAllUserName();
        //print_r($listdep);exit;
        $sql="SELECT GROUP_CONCAT(ID_U) as LISTIDU FROM qtht_users users
              INNER JOIN `qtht_employees` emp on emp.`ID_EMP`=users.`ID_EMP`
              LEFT JOIN `qtht_departments` dep on dep.`ID_DEP`=emp.`ID_DEP` 
              WHERE dep.`ID_DEP` in (".$listdep.");           
			";       
//		     echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
//                echo "<pre>";print_r($re);exit;
        $listidu = $re[0]['LISTIDU'];
        if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 

			}
        if($listidu!=''){       						
				$where_svb = " AND (wfpl.`ID_U_RECEIVE` in (".$listidu.") or wfpl.`ID_U_SEND` in (".$listidu.")) ";				
		}else{ 						 
			    $where_svb = " AND (wfpl.`ID_U_RECEIVE` in (".$listidu.") or wfpl.`ID_U_SEND` in (".$listidu.")) ";	
		}
         	
		$sql="SELECT DISTINCT cv.`ID_VBDI_CVST` 
                      FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv                       
                      INNER join ".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on cv.`ID_HSCV` = hscv.`ID_HSCV`
                      INNER join ".QLVBDHCommon::Table("wf_processitems")." wfpi on hscv.`ID_PI` = wfpi.`ID_PI` 
                      INNER join ".QLVBDHCommon::Table("wf_processlogs")." wfpl on wfpl.`ID_PI` = wfpi.`ID_PI` 
                      WHERE (1=1) $where_fromdate  $where_todate $where_svb                    
			";
		     //echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();//                print_r($re);exit;
		return count($re);       
                
           
    }
    
    function counttketre1($fromdate,$todate,$sel_svb){
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
		if($sel_svb > 0){							
				$where_svb = " AND vbd.ID_SVB = '".$sel_svb."'" ;				
		}else{ 
			$sel_cqnb = congviecModel::laycoquannoibo();			 
			    //$where_svb = " AND svb.`ID_CQ` = '".$sel_cqnb."'" ;	
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT  hstre.ID_U,COUNT(hstre.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
						 INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
						 INNER join
						".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
						 inner join
						 ".QLVBDHCommon::Table("hscv_tre")."  hstre on hstre.ID_HSCV = hscv.ID_HSCV 
						inner join vb_sovanban svb on svb.`ID_SVB`= vbd.`ID_SVB`
					where (1=1)  and hstre.TRE >0  $where_fromdate  $where_todate $where_svb
                    group by hstre.ID_U
			";
		     //echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		$arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}

	  function counttketre2($fromdate,$todate,$sel_svb){
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and vbd.NGAYDEN >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and vbd.NGAYDEN<= '".$todate."'" ; 

			}
		if($sel_svb > 0){							
				$where_svb = " AND vbd.ID_SVB = '".$sel_svb."'" ;				
		}else{ 
			$sel_cqnb = congviecModel::laycoquannoibo();			 
			    //$where_svb = " AND svb.`ID_CQ` = '".$sel_cqnb."'" ;	
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
				SELECT hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("vbd_vanbanden")."  vbd
						 INNER join ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")."  vbd_fk on vbd.`ID_VBD` = vbd_fk.`ID_VBDEN`
						 INNER join
						".QLVBDHCommon::Table("hscv_hosocongviec")."   hscv on vbd_fk.`ID_HSCV` = hscv.`ID_HSCV`
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV	
						 inner join vb_sovanban svb on svb.`ID_SVB`= vbd.`ID_SVB`
					where (1=1)  and hsdgxl.DATEEND  > '".date("Y-m-d")."'   $where_fromdate  $where_todate $where_svb
                     group by hsdgxl.ID_U
			";
		    // echo $sql;exit;
			 //echo $idu;
		$query = $dbAdapter->query($sql);
	    $re = $query->fetchAll();
		$arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}


//////////////
////


	function counttkecvdxl($fromdate,$todate){	
			if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 

			}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT hsdxl.ID_U,COUNT(hsdxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv					
					 INNER join
					  ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on cv.ID_HSCV = hscv.ID_HSCV
						 inner join
                        ".QLVBDHCommon::Table("hscv_daxuly")."  hsdxl  on hsdxl.ID_HSCV= hscv.ID_HSCV 					
					WHERE (1 = 1)   $where_fromdate  $where_todate group by hsdxl.ID_U
			";
			//echo $sql;
		$query = $dbAdapter->query($sql);
		 $re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}

	function counttkecvcxl($fromdate,$todate){	
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO <= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO <= '".$todate."'" ; 

			}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv					
					 INNER join
					  ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on cv.ID_HSCV = hscv.ID_HSCV					 
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV				 
					WHERE (1 = 1)  and (hscv.IS_CHOXULY != 1 or hscv.IS_CHOXULY is null ) $where_fromdate $where_todate 
					group by hsdgxl.ID_U

			";
			//echo $sql;
		$query = $dbAdapter->query($sql);
	     $re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}

	function counttkecvchoxl($fromdate,$todate){	
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO <= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO <= '".$todate."'" ; 

			}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
				SELECT hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv					
					 INNER join
					  ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on cv.ID_HSCV = hscv.ID_HSCV					 
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV				 
					WHERE (1 = 1)  and hscv.IS_CHOXULY = 1 $where_fromdate $where_todate 
					group by hsdgxl.ID_U


			";
			//echo $sql;exit;
		$query = $dbAdapter->query($sql);
	    $re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}
    function counttkecvtre1($fromdate,$todate){
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 

			}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT  hstre.ID_U,COUNT(hstre.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv					
					 INNER join
					  ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on cv.ID_HSCV = hscv.ID_HSCV
						  inner join
						 ".QLVBDHCommon::Table("hscv_tre")."  hstre on hstre.ID_HSCV = hscv.ID_HSCV 
						
					where (1=1) and hstre.TRE >0  $where_fromdate  $where_todate
                    group by hstre.ID_U

			";
			//echo $sql;exit;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	}

	 function counttkecvtre2($fromdate,$todate){
		if($fromdate || $fromdate != ""){
			
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			  $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;
			 
			}else{   $fromdate=	"1/1";
			         $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			         $where_fromdate = " and cv.NGAYTAO >= '".$fromdate."'" ;

			}
	   if($todate || $todate != ""){
             $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 
		    }else{
			 $todate= "31/12";
			 $todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			 $where_todate = " and cv.NGAYTAO<= '".$todate."'" ; 

			}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();	
		$sql="
					SELECT  hsdgxl.ID_U,COUNT(hsdgxl.ID_HSCV) as TONG
					FROM ".QLVBDHCommon::Table("hscv_congviecsoanthao")."  cv					
					 INNER join
					  ".QLVBDHCommon::Table("hscv_hosocongviec")."  hscv on cv.ID_HSCV = hscv.ID_HSCV
						 inner join ".QLVBDHCommon::Table("hscv_dangxuly")." hsdgxl on  hsdgxl.ID_HSCV = hscv.ID_HSCV	
						
					where (1=1) and hsdgxl.DATEEND  > '".date("Y-m-d")."'   $where_fromdate  $where_todate
                     group by hsdgxl.ID_U

			";
			//echo $sql;exit;
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		 $arr = array();
		foreach($re as $it){
			$arr[$it['ID_U'].""] = $it["TONG"]; 
		}
		return $arr;
	} 

	function layidu($id_dep){
		//var_dump($id_dep[0]);exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		  if($id_dep!=0){
            $sql=" select *,CONCAT(FIRSTNAME,' ',LASTNAME) AS HOTEN from `qtht_users` u  
                INNER join `qtht_employees` emp on u.`ID_EMP`= emp.`ID_EMP`  where u.ACTIVE = 1 AND emp.ID_DEP = $id_dep          	

			";
		  }else{
			$sql=" select *,CONCAT(FIRSTNAME,' ',LASTNAME) AS HOTEN from `qtht_users` u WHERE u.ACTIVE = 1
                INNER join `qtht_employees` emp on u.`ID_EMP`= emp.`ID_EMP`  where u.ACTIVE = 1             	

			";
		  }

		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
	function laytenphong($id_dep){
		//var_dump($id_dep[0]);exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		if($id_dep[0] !=0){
            $sql=" select * from qtht_departments where ID_DEP in (".implode(",",$id_dep).")        	

			";
		}else{
			$sql= " select * from qtht_departments";
		}
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}
        
        
   function laycoquannoibo(){		
        global $auth;
		$user = $auth->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();		
            $sql=" select depart.ID_CQ from qtht_users u 
			                inner join qtht_employees emp on u.ID_EMP = emp.ID_EMP
							inner join qtht_departments depart on depart.ID_DEP = emp.ID_DEP
							where u.ID_U='".$user->ID_U."'
							";	
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re["ID_CQ"];
	}

}
