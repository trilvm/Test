<?php

/**
 * vanbansaoyModel
 *
 * @author tuanpp
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once 'hscv/models/hosocongviecModel.php';

class vanbansaoyModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'vbsaoy_vanbansaoy';	

	static function getDetail($year,$ID_VBSY){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select * from `vbsaoy_vanbansaoy` where ID_VBSY=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($ID_VBSY));

			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}

	static function isUserReceive($ID_VBSY,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="
			select ISNEW from 
			vbsaoy_pheduyet leu
			where ID_VBSY = ? and ID_U = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($ID_VBSY,$id_u));
			$row = $qr->fetch();
			return (int)$row["ISNEW"]==1?0:1;
		}catch(Exception $ex){
			return 0;
		}

	}  
	static function updateUserReceive($ID_VBSY,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="
			update  
			vbsaoy_pheduyet  leu
			set ISNEW=0
			where ID_VBSY = ? and ID_U = ?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($ID_VBSY,$id_u));
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}
	static function getnguoixuly($ID_VBSY){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from vbsaoy_pheduyet where ID_VBSY=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($ID_VBSY));

			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	function getDetail1($ID_VBSY){
		 
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select nn.* ,e.ID_DEP,concat(e.FIRSTNAME,' ',e.LASTNAME) as TENNG  from vbsaoy_pheduyet nn
		left join qtht_users u on nn.ID_U =u.ID_U
		left join qtht_employees e on u.ID_EMP=e.ID_EMP
		where ID_VBSY= $ID_VBSY
		";
		try{
			$qr = $dbAdapter->query($sql);
				
			return $qr->fetchAll();
				
		}catch(Exception $ex){
			return array();
		}
	}
	/*   function cvcount(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="select count(*) as DEM from vbsaoy_vanbansaoy_2009";
		try {
		$qr = $dbAdapter->query($sql);
		$row=$qr->fetch();
		return $row["DEM"];
		}catch (Exception $ex){
		return 0;
			
		}
		}*/
	function count($name,$active,$ISLEADER){
		// $name="'%".$name."%'";
		 
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
	 //Count
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from vbsaoy_vanbansaoy  where ( ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) and $wheretemp
			";
			}else{
				$sql="select count(*) as DEM from vbsaoy_vanbansaoy  where  (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))
			";
			    }
		  
		try {
			$qr = $dbAdapter->query($sql,array($name));
			$row=$qr->fetch();
			 
			return $row["DEM"];
		}catch (Exception $ex){
			return 0;
				
		}
 
	}
	/*	function getAll1($page,$limit){
		$offset = ($page-1)*$limit;
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;

		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

		$sql="select * from `vbsaoy_vanbansaoy_2009`  where ID_VBSY in (select ID_VBSY from `vbsaoy_pheduyet_2009` where ID_U=$id_u) or NGUOITAO = $id_u limit $offset,$limit

		";

		$qr = $dbAdapter->query($sql);
			
		return $qr->fetchAll();

		//return array();
		//}
		}*/
	function findByMixed($page,$limit,$name,$ISLEADER,$order){

		//$name="'%".$name."%'";
		$offset = ($page-1)*$limit;
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			if(hosocongviecModel::isVanthu($id_u)==true) {
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) or ISFINISHED = 1 or NGUOIDENGHI=$id_u)) and $wheretemp ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from vbsaoy_vanbansaoy where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) or ISFINISHED = 1 or NGUOIDENGHI=$id_u)) ORDER BY $order limit $offset,$limit
			";
			    }

                            }
                         else {
                         Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) or NGUOIDENGHI=$id_u)) and $wheretemp ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from vbsaoy_vanbansaoy where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) or NGUOIDENGHI=$id_u)) ORDER BY $order limit $offset,$limit
			";
			    }
                         }
                            
		$qr = $dbAdapter->query($sql,array($name));
			
		return $qr->fetchAll();
	}

        function findByMixed2($page,$limit,$name,$active,$activecv,$ISLEADER,$order){                		
		$offset = ($page-1)*$limit;
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

	   if($active == 0 && ($activecv==0)){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where  (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where  (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u ) $where)  and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	  if($active == 0 && ($activecv==1)){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u $where) and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}

		//echo $sql;
	 if($active == 0 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u  or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u  or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}

	   if($active == 1 && $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u) $where) and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	  if($active == 1 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u $where)   and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}


	if($active == 1 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where)  and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}


	  if($active==2&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where  ((ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) $where) and $wheretemp  and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where  (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u) $where ) and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";

			    }
				//echo $sql;exit;
		}

	 if($active==2 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where ( NGUOITAO = $id_u $where)   and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";

			    }

		}
	   if($active==2&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where) and $wheretemp and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select count(*) as C from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) $where)  and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";

			    }

		}
		//echo $sql;
	   if(($active>=3||$active<0||is_null($active))&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from vbsaoy_vanbansaoy  where  ((ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u)) )  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from vbsaoy_vanbansaoy  where  (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u) ) ORDER BY $order limit  $offset,$limit
			";
			    }
		}

	   if(($active>=3||$active<0||is_null($active))&& $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u ) and $wheretemp  ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from vbsaoy_vanbansaoy  where (NGUOITAO = $id_u )ORDER BY $order limit  $offset,$limit
			";
			    }
		}


	  if(($active>=3||$active<0||is_null($active))&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch("vbsaoy_vanbansaoy");
			$wheretemp = " match(KYHIEUVANBANSAOY) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where)  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from vbsaoy_vanbansaoy  where ((NGUOITAO = $id_u or (ID_VBSY in (select ID_VBSY from vbsaoy_pheduyet where ID_U=$id_u))) $where) ORDER BY $order limit  $offset,$limit
			";
			    }
		}
//                echo $sql;exit;
		$qr = $dbAdapter->query($sql,array($name));

		return $qr->fetchAll();
	}        

	static function toComboFilter($filter_object){                
		if($filter_object==0 && !is_null($filter_object))

		echo '<option value=0 selected>Chưa hoàn thành</option>';
		else
		echo '<option value=0>Chưa hoàn thành</option>';
			
		if($filter_object==1)
		echo '<option value=1 selected>Đã hoàn thành</option>';
		else
		echo '<option value=1>Đã hoàn thành</option>';
      		
		if($filter_object==2) 
       echo  '<option value=2 selected>Công việc trễ hạn</option>';	
       else 
       echo  '<option value=2>Công việc trễ hạn </option>';
			
		if($filter_object>=3||$filter_object<0||is_null($filter_object) )
		echo '<option value=3  selected>Chọn tất cả</option>';
		else
		echo '<option value=3 >Chọn tất cả</option>';

	}
	
	static function toComboFilterCV($combo_object){
       if($combo_object==0 && !is_null($combo_object))
        echo '<option value=0 selected>Công việc được giao</option>'; 
        else 
        echo '<option value =0>Công việc được giao</option>';	
       if($combo_object==1) 
       echo  '<option value=1 selected>Công việc đã giao</option>';	
       else 
       echo  '<option value=1>Công việc đã giao</option>';
       
       
       if($combo_object>=2 ||$combo_object<0||is_null($combo_object))
       echo  '<option value=2  selected>Chọn tất cả</option>';
       	else
		echo '<option value=2 >Chọn tất cả</option>';
       
	}
	static function updateisnew($ID_VBSY){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			update vbsaoy_vanbansaoy set ISNEW=0 where ID_VBSY = $ID_VBSY
		";
		//echo 	$sql;

		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute();
				
		}catch(Exception $ex){
				
		}

	}


     static function GetDataSKH(){
    	global $auth;
		$user = $auth->getIdentity();        
        $result = Zend_Db_Table::getDefaultAdapter()->query("
            SELECT
                *
            FROM
                ".QLVBDHCommon::Table("vbd_vanbanden")."
        ");
        $data = $result->fetchAll();
        return $data;
    }

    static function GetTrichYeu($ID_VBD) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT
					`TRICHYEU`
				FROM
					".QLVBDHCommon::Table("vbd_vanbanden")."
				WHERE
				   ID_VBD = ?
			";
		$stm = $dbAdapter->query($sql,array($ID_VBD));
		$re = $stm->fetch();
                return $re["TRICHYEU"];
    }

    static function GetAllUsers() {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
			";
		$stm = $dbAdapter->query($sql);
		$re = $stm->fetchAll();
                return $re;
    }
    
    static function GetDetailSaoYById($IDSY) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT
					G.ID_G,DEP.ID_DEP,DEP.NAME as TENPHONG,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.USERNAME,SY.*
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
                                        INNER JOIN vbsaoy_vanbansaoy SY ON  SY.NGUOITAO=U.ID_U
                      WHERE SY.ID_VBSY=?";
		$stm = $dbAdapter->query($sql,$IDSY);
		$re = $stm->fetchAll();
                return $re;
    }
    
    static function GetDetailSaoYByArrayId($IDSY) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT
					G.ID_G,DEP.ID_DEP,DEP.NAME as TENPHONG,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.USERNAME,SY.*
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
                                        INNER JOIN vbsaoy_vanbansaoy SY ON  SY.NGUOITAO=U.ID_U
                      WHERE SY.ID_VBSY IN (". implode($IDSY, ',').")";
		$stm = $dbAdapter->query($sql,$IDSY);
		$re = $stm->fetchAll();
                return $re;
    }


}
