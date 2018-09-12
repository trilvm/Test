<?php
require_once("auth/models/ResourceUserModel.php");
class BanHanhVanBanModel {
	static function getAllListBanHanhVanBan($year){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select hscv.`ID_HSCV` , ID_DUTHAO , TENDUTHAO , NGUOIKY , NGUOISOAN , loai.ID_LOAIHSCV ,loai.NAME as TENCV from 
		`hscv_duthao_".$year."` duthao 
		inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV`
		inner join `hscv_loaihosocongviec` loai on loai.`ID_LOAIHSCV` = hscv.`ID_LOAIHSCV` 
		where duthao.`TRANGTHAI`=1
		";
		$qery = $dbAdapter->query($sql);
		return $qery->fetchAll();
	}
	
	
	static function countDuthao($year,$serch_ten,$theo_loai, $NGUOISOAN){
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();

		// Check la van thu
		$isvanthu = 0;
		$actid = ResourceUserModel::getActionByUrl('vbden','vbden','input');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			$isvanthu = 1;
		}
		$arr_where = array();
		$where = "";
		//echo $serch_ten."------".$theo_loai;
		if($serch_ten != ""){	
			$serch_ten="'%".$serch_ten."%'";
			$where_ten = 'TENDUTHAO LIKE '.$serch_ten;
			array_push($arr_where,$where_ten);   
		}
		array_push($arr_where, 'TRANGTHAI = 1');
		
		if($theo_loai != "0" && $theo_loai !=""){
		
			array_push($arr_where, "cl.ALIAS='$theo_loai'");
		}
		
		if($NGUOISOAN > 0)
		{
			$where_ns = " NGUOISOAN = ".$NGUOISOAN;
			array_push($arr_where,$where_ns); 
		}
		if(count($arr_where) > 0 ) { //ton tai it nhat mot dieu kien can tim kiem
			$where = "  and ".implode(' and ',$arr_where)." ";
			
		}
		$strlimit =" ";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select count(*) as DEM from 
		`hscv_duthao_".$year."` duthao 
		inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV`
		inner join `hscv_loaihosocongviec` loai on loai.`ID_LOAIHSCV` = hscv.`ID_LOAIHSCV` 
		inner join WF_PROCESSES pr on loai.MASOQUYTRINH = pr.ALIAS
		inner join WF_CLASSES cl on pr.ID_C = cl.ID_C
		inner join ".QLVBDHCommon::table("WF_PROCESSITEMS")." prs on prs.ID_O = hscv.ID_HSCV
		inner join (select ID_PI,ID_U_RECEIVE from (select ID_PL,ID_PI,ID_U_RECEIVE
		from ".QLVBDHCommon::table("WF_PROCESSLOGS")."
		";
		if($isvanthu==1){
			$sql.="
			inner join qtht_users u on ID_U_RECEIVE = u.ID_U
			inner join qtht_employees emp on emp.ID_EMP = u.ID_EMP
			where emp.ID_DEP=".$user->ID_DEP."
			";
		}else{
		$sql .= "
		where ID_U_RECEIVE=".$user->ID_U."
		";
		}
		$sql .= "
		order by ID_PL desc) pl
		group by ID_PI
		) pl on prs.ID_PI = pl.ID_PI
		where 1=1 ".$where."ORDER BY TRANGTHAI ".$strlimit;
		//echo $sql;
		$qery = $dbAdapter->query($sql);
		$r= $qery->fetch();
		return $r["DEM"];
	}
	
	static function getAllListBanHanhVanBanMixed($year,$serch_ten,$theo_loai,$offset,$limit,$NGUOISOAN){
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();

		// Check la van thu
		$isvanthu = 0;
		$actid = ResourceUserModel::getActionByUrl('vbden','vbden','input');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
			$isvanthu = 1;
		}

		$serch_ten1="'%".$serch_ten."%'";
		$arr_where = array();
		$where = "";
		//echo $serch_ten."------".$theo_loai;
		if($serch_ten != ""){	
			$where_ten = 'TENDUTHAO LIKE '.$serch_ten1;
			array_push($arr_where,$where_ten);   
		}
		array_push($arr_where, 'TRANGTHAI = 1');
		
		if($theo_loai != "0" && $theo_loai !=""){
		
			array_push($arr_where, "cl.ALIAS='$theo_loai'");
		}
		if($NGUOISOAN > 0)
		{
			$where_ns = " NGUOISOAN = ".$NGUOISOAN;
			array_push($arr_where,$where_ns); 
		}
		if(count($arr_where) > 0 ) { //ton tai it nhat mot dieu kien can tim kiem
			$where = "  and ".implode(' and ',$arr_where)." ";
			
		}
		$strlimit =" ";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
		select hscv.`ID_HSCV` , ID_DUTHAO , TRANGTHAI,TENDUTHAO , NGUOIKY , NGUOISOAN , loai.ID_LOAIHSCV ,loai.NAME as TENCV,cl.ALIAS from 
		`hscv_duthao_".$year."` duthao 
		inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV`
		inner join `hscv_loaihosocongviec` loai on loai.`ID_LOAIHSCV` = hscv.`ID_LOAIHSCV` 
		inner join WF_PROCESSES pr on loai.MASOQUYTRINH = pr.ALIAS
		inner join WF_CLASSES cl on pr.ID_C = cl.ID_C
		inner join ".QLVBDHCommon::table("WF_PROCESSITEMS")." prs on prs.ID_O = hscv.ID_HSCV
		inner join (select ID_PI,ID_U_RECEIVE from (select ID_PL,ID_PI,ID_U_RECEIVE
		from ".QLVBDHCommon::table("WF_PROCESSLOGS")."
		";
		if($isvanthu==1){
			$sql.="
			inner join qtht_users u on ID_U_RECEIVE = u.ID_U
			inner join qtht_employees emp on emp.ID_EMP = u.ID_EMP
			where emp.ID_DEP=".$user->ID_DEP."
			";
		}else{
		$sql .= "
		where ID_U_RECEIVE=".$user->ID_U."
		";
		}
		$sql .= "
		order by ID_PL desc) pl
		group by ID_PI
		) pl on prs.ID_PI = pl.ID_PI
		where 1=1 ".$where."ORDER BY prs.LASTCHANGE DESC ".$strlimit;
		//echo $sql;exit;
		$qery = $dbAdapter->query($sql);
		return $qery->fetchAll();
	}
	
	
	
	static function getDetailDuThao($year,$id_loai,$idDuthao){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		if($id_loai == "VBD")
		{
			//xu ly van ban den
			$sql = "select  NAME , vbd.`MASOVANBAN` as MASO, vbd.`TRICHYEU` , duthao.`NGUOISOAN`,duthao.`NGUOIKY`,
			ID_LOAIHSCV,vbd.`SOKYHIEU`, vbd.`NGAYBANHANH`,vbd.`COQUANBANHANH_TEXT`
			from `hscv_duthao_".$year."` duthao 
			inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV` and duthao.`ID_DUTHAO` = ?
			inner join `vbd_fk_vbden_hscvs_$year` fk on fk.`ID_HSCV` = hscv.`ID_HSCV` 
			inner join `vbd_vanbanden_".$year."` vbd on vbd.`ID_VBD`= fk.`ID_VBDEN` 
			
			";
			
		}
		if($id_loai == "VBSOANTHAO")
		{
		//soan thao van ban
		$sql = "select  hscv.NAME as NAME, cv.`NAME` as TRICHYEU, duthao.`NGUOISOAN`,duthao.`NGUOIKY`
			
			from `hscv_duthao_".$year."` duthao 
			inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV` and duthao.`ID_DUTHAO` = ?
			inner join `hscv_congviecsoanthao_".$year."` cv on cv.`ID_HSCV`= hscv.`ID_HSCV` 
			 
			";
		}
		if($id_loai  == "MOTCUA"){
			$sql = "select  hscv.NAME as NAME, cv.`TRICHYEU`, duthao.`NGUOISOAN`,duthao.`NGUOIKY`
			
			from `hscv_duthao_".$year."` duthao 
			inner join `hscv_hosocongviec_".$year."` hscv on duthao.`ID_HSCV` = hscv.`ID_HSCV` and duthao.`ID_DUTHAO` = ?
			inner join `motcua_hoso_".$year."` cv on cv.`ID_HSCV`= hscv.`ID_HSCV` 
			 
			";
		}
		
		$qery = $dbAdapter->query($sql,array($idDuthao));
		return $qery->fetch();
	}
	function GetLastPhienBan($idDuthao){
		global $db;
		$arrdata = array($idDuthao);
		$query = $db->query('
		select * from
  		`gen_filedinhkem_'.QLVBDHCommon::getYear().'` fdk  inner join (select*  from `hscv_phienbanduthao_'.QLVBDHCommon::getYear().'` where
  		`ID_DUTHAO`=? ) pb
			where pb.`ID_PB_DUTHAO` = fdk.`ID_OBJECT` and fdk.`TYPE`=2
		order by pb.`ID_PB_DUTHAO` DESC
		',$arrdata);
		return $query->fetchAll();
	}
static function getkyhieu($name){
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select KYHIEU  from ".QLVBDHCommon::Table("vb_loaivanban")." where NAME= $name
		
		";
		$qery = $dbAdapter->query($sql);
		$return= $qery->fetch();
		return $return["KYHIEU"];
	}
}

?>
