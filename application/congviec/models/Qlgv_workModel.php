<?php

/**
 * Qlgv_workModel
 *
 * @author locbc
 * @version
 */

require_once 'Zend/Db/Table/Abstract.php';

class Qlgv_workModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name
	 */
	protected $_name = 'qlgv_work';

	function __construct($year){
		if(!$year)
		$year = QLVBDHCommon::getYear();
		$this->_name = "qlgv_work_$year";
		$arr = array();
		parent::__construct($arr);
	}


	static function getDetail($year,$id_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select * from `qlgv_work_$year` where ID_WORK=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_work));

			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}

	static function isUserReceive($id_work,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="
			select ISNEW from 
			".QLVBDHCommon::Table('qlgv_listexecuser')." leu
			where ID_WORK = ? and ID_U = ?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_work,$id_u));
			$row = $qr->fetch();
			return (int)$row["ISNEW"]==1?0:1;
		}catch(Exception $ex){
			return 0;
		}

	}
  static function istiendo($id_work,$id_u){
  	   $dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="
			select count(*) as DEM  from 
			".QLVBDHCommon::Table('qlgv_listexecuser')." leu
			where ID_WORK = ? and ID_U = ? and ISMAIN=1
		";
     try{
			$qr = $dbAdapter->query($sql,array($id_work,$id_u));
			$row = $qr->fetch();
			return $row["DEM"];
		}catch(Exception $ex){
			return 0;
		}
		
  }
	static function updateUserReceive($id_work,$id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="
			update  
			".QLVBDHCommon::Table('qlgv_listexecuser')."  leu
			set ISNEW=0
			where ID_WORK = ? and ID_U = ? 
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_work,$id_u));
			return 1;
		}catch(Exception $ex){
			return 0;
		}
	}
	static function getnguoixuly($id_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_WORK=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_work));

			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	function getDetail1($id_work){
		 
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select nn.* ,e.ID_DEP,concat(e.FIRSTNAME,' ',e.LASTNAME) as TENNG  from ".QLVBDHCommon::Table("qlgv_listexecuser")." nn 
		left join qtht_users u on nn.ID_U =u.ID_U
		left join qtht_employees e on u.ID_EMP=e.ID_EMP
		where ID_WORK= $id_work
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
		$sql="select count(*) as DEM from qlgv_work_2009";
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
		
	 if($active == 0 && ($activecv==0)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ( ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where)  and $wheretemp and ISFINISHED = 0  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where)  and ISFINISHED = 0 
			";
			    }
		}
	  if($active == 0 && ($activecv==1)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and $wheretemp and ISFINISHED = 0  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where ) and ISFINISHED = 0  
			";
			    }
		}
	
		//echo $sql;
	 if($active == 0 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp and ISFINISHED = 0  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and ISFINISHED = 0  
			";
			    }
		}
		
	   if($active == 1 && $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where)  and $wheretemp  and ISFINISHED = 1 
			";
			}else{
				$sql="select count(*) as DEMfrom ".QLVBDHCommon::Table("qlgv_work")."  where ( ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and ISFINISHED = 1 
			";
			    }	
		}
	  if($active == 1 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)   and $wheretemp  and ISFINISHED = 1 
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and ISFINISHED = 1 
			";
			    }	
		}
	
		
	if($active == 1 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and $wheretemp  and ISFINISHED = 1 
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and ISFINISHED = 1 
			";
			    }	
		}
		
	
	
		
	
	  if($active==2&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where) and $wheretemp and ISFINISHED = 0 and TREDATE >0  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and TREDATE >0 and ISFINISHED = 0  
			";
			    }
		}
		
	 if($active==2 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)   and $wheretemp and TREDATE >0 and ISFINISHED = 0 
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ( NGUOITAO = $id_u $where) and TREDATE >0 and ISFINISHED = 0
			";
			    }	
		}
	   if($active==2&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and $wheretemp and TREDATE >0 and ISFINISHED = 0
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where ) and TREDATE >0 and ISFINISHED = 0
			";
			    }
		}	
		//echo $sql;
	   if(($active>=3||$active<0||is_null($active))&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where )  and $wheretemp  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) 
			";
			    }
		}
		
	   if(($active>=3||$active<0||is_null($active))&& $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)  and $wheretemp 
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) 
			";
			    }
		}
	
		
	  if(($active>=3||$active<0||is_null($active))&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select  count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and $wheretemp  
			";
			}else{
				$sql="select count(*) as DEM from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  
			";
			    }
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

		$sql="select * from `qlgv_work_2009`  where ID_WORK in (select ID_WORK from `qlgv_listexecuser_2009` where ID_U=$id_u) or NGUOITAO = $id_u limit $offset,$limit

		";

		$qr = $dbAdapter->query($sql);
			
		return $qr->fetchAll();

		//return array();
		//}
		}*/
	function findByMixed($page,$limit,$name,$active,$activecv,$ISLEADER,$order){

		//$name="'%".$name."%'";
		$offset = ($page-1)*$limit;
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
	   if($active == 0 && ($activecv==0)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u ) $where)  and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	  if($active == 0 && ($activecv==1)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	
		//echo $sql;
	 if($active == 0 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}
		
	   if($active == 1 && $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }	
		}
	  if($active == 1 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)   and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }	
		}
	
		
	if($active == 1 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }	
		}
	
	
	  if($active==2&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where) and $wheretemp  and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where ) and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit				
			";
		
			    }
				//echo $sql;exit;
		}
		
	 if($active==2 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ( NGUOITAO = $id_u $where)   and $wheretemp and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			
			    }	
				
		}
	   if($active==2&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and $wheretemp and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where) and TREDATE >0  and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			
			    }
				
		}	
		//echo $sql;
	   if(($active>=3||$active<0||is_null($active))&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where)  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) ORDER BY $order limit  $offset,$limit
			";
			    }
		}
		
	   if(($active>=3||$active<0||is_null($active))&& $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and $wheretemp  ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)ORDER BY $order limit  $offset,$limit
			";
			    }
		}
	
		
	  if(($active>=3||$active<0||is_null($active))&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) ORDER BY $order limit  $offset,$limit
			";
			    }
		}     
		$qr = $dbAdapter->query($sql,array($name));
			
		return $qr->fetchAll();
	}

        function findByMixed2($page,$limit,$name,$active,$activecv,$ISLEADER,$order, $id_nguoigiao){
                
		//$name="'%".$name."%'";
                //active == 0 => Chưa hoàn thành
                //active == 1 => Đã hoàn thành
                //active == 2 => Công việc trễ hạn
		$wherenguoigiao = "";
		$offset = ($page-1)*$limit;
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

	   if($active == 0 && ($activecv==0)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u ) $where)  and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	  if($active == 0 && ($activecv==1)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}

		//echo $sql;
	 if($active == 0 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u  or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			    }
		}

	   if($active == 1 && $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}
	  if($active == 1 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where)   and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}


	if($active == 1 && ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where)  and $wheretemp  and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and ISFINISHED = 1 ORDER BY $order limit $offset,$limit
			";
			    }
		}


	  if($active==2&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where) and $wheretemp  and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) $where ) and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";

			    }
				//echo $sql;exit;
		}

	 if($active==2 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ( NGUOITAO = $id_u $where)   and $wheretemp and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $where) and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit $offset,$limit
			";

			    }

		}
	   if($active==2&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))) $where) and $wheretemp and TREDATE >0 and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $where) and TREDATE >0  and ISFINISHED = 0 ORDER BY $order limit  $offset,$limit
			";

			    }

		}
		//echo $sql;
	   if(($active>=3||$active<0||is_null($active))&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  ((ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) )  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u) ) ORDER BY $order limit  $offset,$limit
			";
			    }
		}

	   if(($active>=3||$active<0||is_null($active))&& $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wherenguoigiao .= " OR NGUOIGIAO = $id_nguoigiao";
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $wherenguoigiao) and $wheretemp  ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u $wherenguoigiao) ORDER BY $order limit  $offset,$limit
			";
			    }
		}


	  if(($active>=3||$active<0||is_null($active))&& ($activecv>=2||$activecv<0 ||is_null($activecv))){
		
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$wherenguoigiao .= " OR NGUOIGIAO = $id_nguoigiao";
			$wheretemp = " match(NAME) against (? IN BOOLEAN MODE)";
			if($name !="")
			{
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $wherenguoigiao) $where)  and $wheretemp ORDER BY $order limit  $offset,$limit
			";
			}else{
				$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where ((NGUOITAO = $id_u or (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) $wherenguoigiao) $where) ORDER BY $order limit  $offset,$limit
			";
			    }
		}
		$qr = $dbAdapter->query($sql,array($name));
		return $qr->fetchAll();
	}

        function count2($active,$activecv){

		//$name="'%".$name."%'";
                //active == 0 => Chưa hoàn thành
                //active == 1 => Đã hoàn thành
                //active == 2 => Công việc trễ hạn
                //activecv == 0 => công việc được giao
                //activecv == 1 => công việc đã giao
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

	   if($active == 0 && ($activecv==0)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));			
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u ))  and ISFINISHED = 0";			    
		}
	  if($active == 0 && ($activecv==1)){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));			
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u) and ISFINISHED = 0";			    
		}
		//echo $sql;
	   if($active == 1 && $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) and ISFINISHED = 1
			";			    
		}
	  if($active == 1 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));			
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u) and ISFINISHED = 1";			    
		}


	  if($active==2&& $activecv==0){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));			
			$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) and TREDATE >0 and ISFINISHED = 0	";			    
				//echo $sql;exit;
		}
                
	 if($active==2 && $activecv==1){
			Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('qlgv_work'));						
				$sql="select count(*) as C from ".QLVBDHCommon::Table("qlgv_work")."  where (NGUOITAO = $id_u) and TREDATE >0 and ISFINISHED = 0";			    
		}			   
	  
//                echo $sql;exit;
		$qr = $dbAdapter->query($sql);
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
	static function updateisnew($is_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			update ".QLVBDHCommon::Table("qlgv_work")." set ISNEW=0 where ID_WORK = $is_work
		";
		//echo 	$sql;

		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute();
				
		}catch(Exception $ex){
				
		}

	}

  static function getfile($id_work){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="SELECT dk.* FROM ".QLVBDHCommon::Table("gen_filedinhkem")." dk 
	    		WHERE
	    			 ID_OBJECT =?
	    			 and
	    			 TYPE = 11";
	    try{
	    	$qr=$dbAdapter->query($sql,array($id_work));
	    	return $qr->fetchAll();
	    }catch (Exception  $ex){
	    	return array();
	    }			 
	   
	   // var_dump($r);
	    
	 }
 static function updatetrehan($is_work,$delay){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			update ".QLVBDHCommon::Table("qlgv_work")." set TREDATE=$delay where ID_WORK = $is_work
		";
		//echo 	$sql;

		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute();
				
		}catch(Exception $ex){
				
		}

	}
	
static function countnoidung($idwork){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select count(*) as DEM  from ".QLVBDHCommon::Table("qlgv_journal")." where ID_WORK = $idwork
			
		";	

     try {
		 $qr = $dbAdapter->query($sql);
		 $row=$qr->fetch();			 
			return $row["DEM"];
		}catch (Exception $ex){
			return 0;				
		}
	}
 static function countnoidungyk($idwork){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select count(*) as DEM  from ".QLVBDHCommon::Table("qlgv_remark")." where ID_WORK = $idwork			
		";	
     try {
			$qr = $dbAdapter->query($sql);
			$row=$qr->fetch();			 
			return $row["DEM"];
		}catch (Exception $ex){
			return 0;				
		}
	}
 static function countcongviecduocgiao(){
	    $auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;	
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select count(*) as DEM  from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u and ISNEW=1			
		";
   
     try {
			$qr = $dbAdapter->query($sql);
			$row=$qr->fetch();
			return $row["DEM"];
		}catch (Exception $ex){
			return 0;
				
		}

	}	
 static function congviecduocgiao(){
	    $auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
	
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select* from ".QLVBDHCommon::Table("qlgv_work")." where ID_WORK in (select ID_WORK  from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u and ISNEW=1)
			
		";
		
       
     try {
			$qr = $dbAdapter->query($sql);
			
			return $qr->fetchAll();
		}catch (Exception $ex){
			return 0;
				
		}

	}		
	
	function getCongViecChuaXuLy($activecv,$ISLEADER){
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();

	   if($activecv==0){
			$sql="select * from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u)) and ISFINISHED = 0 ORDER BY BEGINDATE DESC LIMIT 0,3
			";
		}
		$qr = $dbAdapter->query($sql,array($name));
		return $qr->fetchAll();
	}
	
	function countAllCongViec(){
		$auth = Zend_Registry::get('auth');
		$data_session = $auth->getIdentity();
		$id_u =$data_session->ID_U;
		if($ISLEADER ==1){
			$where=" or (1=1)";
		}
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql="select count(*) AS CNT from ".QLVBDHCommon::Table("qlgv_work")."  where  (ID_WORK in (select ID_WORK from ".QLVBDHCommon::Table("qlgv_listexecuser")." where ID_U=$id_u))";
		$qr = $dbAdapter->query($sql);
		$result = $qr->fetch();
		return $result['CNT'];
	}
	
	static function getAllUser()
	{
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "SELECT *, concat(FIRSTNAME, ' ', LASTNAME) AS FULLNAME FROM qtht_users u
				INNER JOIN qtht_employees e ON u.ID_EMP = e.ID_EMP ";
		$qr = $dbAdapter->query($sql);
		$result = $qr->fetchAll();
		return $result;
	}
	static function getNguoiGiaoViec($idGiaoViec)
	{
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "SELECT CONCAT(e.FIRSTNAME, ' ', e.LASTNAME) AS FULLNAME FROM ".QLVBDHCommon::Table("qlgv_work")." w
				INNER JOIN qtht_users u ON w.NGUOIGIAO = u.ID_U
				INNER JOIN qtht_employees e ON u.ID_EMP = e.ID_EMP
				WHERE w.NGUOIGIAO = ?";
				
		$qr = $dbAdapter->query($sql, $idGiaoViec);
		$result = $qr->fetch();
		return $result['FULLNAME'];
	}
}
