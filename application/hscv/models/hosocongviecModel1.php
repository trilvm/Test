<?php

/**
 * hosocongviec
 *  
 * @author nguyennd
 * @version 1.0
 * @deprecated add 14/10/2009 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'hscv/models/VanBanLienQuanModel.php';
require_once 'auth/models/ResourceUserModel.php';

class hosocongviecModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'hscv_hosocongviec_2013';
	function __construct($year){
		if($year=="")$year=QLVBDHCommon::getYear();
		$this->_name = 'hscv_hosocongviec_'.$year;
		$config = array();
		parent::__construct($config);
	}
	/**
	 * Đếm bản ghi các hồ sơ công việc dựa trên tham số đầu vào.
	 * Danh sách tham số đầu vào
	 * ID_THUMUC
	 * ID_LOAIHSCV
	 * NGAY_BD
	 * NGAY_KT
	 * NAME
	 * TRANGTHAI
	 * ID_P
	 * ID_A
	 * ID_U
	 * @param array $parameter
	 */
	function Count($parameter){
	$where = "(1=1)";
		$param = array();
		$innerjoin = "";
		
		//Lấy tên table dựa trên ngày bắt đầu
		$realyear = QLVBDHCommon::getYear();
		$tablename = 'hscv_hosocongviec_'.$realyear;
		if($parameter['CODE']=='' || strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and IS_THEODOI<>1 ";
			$tablewfitem = 'wf_processitems_'.$realyear;
			$where .= "  and ( hscv.IS_CHOXULY = 0 OR hscv.IS_CHOXULY is NULL ) ";
		}else if(strtoupper($parameter['CODE'])=='PRE'){
			$tablewfitem = '
				(SELECT ID_U_SEND as ID_U, ID_A_BEGIN as ID_A,pl.ID_PI,DATESEND as LASTCHANGE,pl.ID_P,t.CNTPL
					FROM
					wf_processlogs_'.$realyear.' pl
					INNER JOIN (SELECT ID_PL as ID_PL,count(ID_PL) as CNTPL FROM wf_processlogs_'.$realyear.' GROUP BY ID_PI HAVING count(ID_PL)>1) t on t.ID_PL = pl.ID_PL
				WHERE
					ID_U_SEND = ?
				)
			';
			$param[] = $parameter['ID_U'];
		}else if(strtoupper($parameter['CODE'])=='OLD'){
			$tablewfitem = '
				(SELECT ID_U_SEND as ID_U, ID_A_BEGIN as ID_A,pl.ID_PI,DATESEND as LASTCHANGE,pl.ID_P,t.CNTPL
					FROM
					wf_processlogs_'.$realyear.' pl
					INNER JOIN (SELECT ID_PL as ID_PL,count(ID_PL) as CNTPL FROM wf_processlogs_'.$realyear.' WHERE ID_U_SEND = ? GROUP BY ID_PI) t on t.ID_PL = pl.ID_PL
				WHERE
					ID_U_SEND = ?
				)
			';
			$param[] = $parameter['ID_U'];
			$param[] = $parameter['ID_U'];
		}
		if(strtoupper($parameter['CODE'])=='PHOIHOP'){
			$tablewfitem = 'wf_processitems_'.$realyear;
			$innerjoin .= "
				INNER JOIN (SELECT ID_HSCV FROM ".QLVBDHCommon::Table("HSCV_PHOIHOP")." WHERE ID_U = ?) ph on ph.ID_HSCV = hscv.ID_HSCV
			";
			$param[] = $parameter['ID_U'];
		}
		//Check thư mục
		if($parameter['ID_THUMUC']>1 && strtoupper($parameter['CODE'])!='OLD' && strtoupper($parameter['CODE'])!='PRE' && strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and (hscv.ID_THUMUC = ? OR hscv.ID_THUMUC_HSCV = ?)";
			$param[] = $parameter['ID_THUMUC'];
			$param[] = $parameter['ID_THUMUC'];
		}else if(strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = -1;
		}else if(strtoupper($parameter['CODE'])=='OLD'){

		}else{
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = 1;
		}
		//check thu muc ho so cong viec
		if($parameter['IS_THUMUC_HSCV'] == 1){
			$where .= " and ( hscv.ID_THUMUC_HSCV = ?)";
			$param[] = $parameter['ID_THUMUC'];
			
		}
		//Check loai hscv
		if($parameter['ID_LOAIHSCV']>0){
			$where .= " and hscv.ID_LOAIHSCV = ?";
			$param[] = $parameter['ID_LOAIHSCV'];
		}
		
		//Check mã số hồ sơ một cửa
		$is_ser_mc = 0;
		if($parameter['MASOHOSO'] !=""){
			$is_ser_mc = 1;
			$mshs = substr($parameter['MASOHOSO'],0,12);
			$where .= " and mc.MAHOSO REGEXP '^".$mshs."' ";
			//$param[] = $parameter['MASOHOSO'];
		}

		if($parameter['TENTOCHUCCANHAN'] !=""){
			$is_ser_mc = 1;
			$mshs = trim($parameter['TENTOCHUCCANHAN']);
			$where .= " and mc.TENTOCHUCCANHAN LIKE '%".$mshs."%' ";
			//$param[] = $parameter['MASOHOSO'];
		}
		
		if($parameter['NHAN_NGAY_BD']!=""){
			$is_ser_mc = 1;
			$ngayden_bd = $parameter['NHAN_NGAY_BD']." 00:00:01";
			
			$where .= " and mc.NHAN_NGAY >= ?";
			$param[] = $ngayden_bd;
		}

		if($parameter['NHAN_NGAY_KT']!=""){
			$is_ser_mc = 1;
			$ngayden_bd = $parameter['NHAN_NGAY_KT']." 23:59:59";
			
			$where .= " and mc.NHAN_NGAY <= ?";
			$param[] = $ngayden_bd;
		}
		if($is_ser_mc == 1){
			$innerjoin .= "
				INNER JOIN ". QLVBDHCommon::Table("MOTCUA_HOSO") ." mc on hscv.ID_HSCV = mc.ID_HSCV ";
		}

		// Tim kiem kem theo van ban den
		$is_ser_vbd = 0;
		if((int)$parameter['ID_SVB']){
			$is_ser_vbd = 1;
			
			$where .= " and vbd.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		if((int)$parameter['ID_LVB']){
			$is_ser_vbd = 1;
			
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		
		if($parameter['SODEN']!=""){
			$is_ser_vbd = 1;
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

		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$is_ser_vbd = 1;
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
		
		//Check ngÃ y Ä‘áº¿n bd
		if($parameter['NGAYDEN_BD']!=""){
			$is_ser_vbd = 1;
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:01";
			
			$where .= " and vbd.NGAYDEN >= ?";
			$param[] = $ngayden_bd;
		}
		//Check ngÃ y Ä‘áº¿n kt
		if($parameter['NGAYDEN_KT']!=""){
			$is_ser_vbd = 1;
			$ngayden_kt = $parameter['NGAYDEN_KT']." 23:59:59";
			$where .= " and vbd.NGAYDEN <= ?";
			$param[] = $ngayden_kt;
		}

		 //Check ngÃ y ban hÃ nh bd
		if($parameter['NGAYBANHANH_BD']!=""){
			$is_ser_vbd = 1;
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}
		//Check ngÃ y ban hÃ nh kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$is_ser_vbd = 1;
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}


		if($is_ser_vbd==1){
			$innerjoin .=  " INNER JOIN  
			".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_vbden_hsc on hscv.ID_HSCV = fk_vbden_hsc.ID_HSCV  
			
			INNER JOIN ".QLVBDHCommon::Table('vbd_vanbanden')." vbd  on fk_vbden_hsc.ID_VBDEN = vbd.ID_VBD ";
		}

		
	
		
		//Check ngay bd
		if($parameter['NGAY_BD']!=""){
			$ngaybd = $parameter['NGAY_BD']." 00:00:01";
			$where .= " and hscv.NGAY_BD >= ?";
			$param[] = $ngaybd;
		}
		//Check ngay kt
		if($parameter['NGAY_KT']!=""){
			$ngaykt = $parameter['NGAY_KT']." 23:59:59";
			$where .= " and hscv.NGAY_BD <= ?";
			$param[] = $ngaykt;
		}
		//Check activity
		if($parameter['TRANGTHAI']>0){
			$where .= " and wfitem.ID_A = ?";
			$param[] = $parameter['TRANGTHAI'];
		}
		//Check user
		if($parameter['ID_U']>0 && strtoupper($parameter['CODE'])=="" && $parameter['ID_THUMUC']==1){
			$where .= " and (wfitem.ID_U = ? or wfitem.ID_DEP = ? or wfitem.ID_G in (".$parameter['ID_G']."))";
			$param[] = $parameter['ID_U'];
			$param[] = $parameter['ID_DEP'];
			//$param[] = "(1,2,6)";//$parameter['ID_G'];
			//echo $parameter['ID_G'];
		}
		//Check name
		if($parameter['NAME']!=""){
			$wheretemp = "";
			if($parameter['INNAME']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('HSCV_HOSOCONGVIEC'));
				$wheretemp = " match(hscv.NAME) against (? IN BOOLEAN MODE)";
				$order = " match(hscv.NAME) against ('".str_replace("'","''",$parameter['NAME'])."') DESC";
				$param[] = $parameter['NAME'];
				$where .= " and (".$wheretemp.($parameter['INNAME']==1 && $parameter['INFILE']==1?" OR ":")");
			}
			if($parameter['INFILE']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('GEL_FILEDINHKEM'));
				$innerjoin .= " left join hscv_duthao_".$realyear." dt on dt.ID_HSCV = hscv.ID_HSCV";
				$innerjoin .= " left join hscv_phienbanduthao_".$realyear." pbdt on pbdt.ID_DUTHAO = dt.ID_DUTHAO";
				$innerjoin .= " left join gen_filedinhkem_".$realyear." dk on dk.ID_OBJECT = pbdt.ID_PB_DUTHAO and dk.TYPE=2";
				$wheretemp = " match(dk.CONTENT) against (? IN BOOLEAN MODE)";
				$order = " match(dk.CONTENT) against ('".str_replace("'","''",$parameter['NAME'])."') DESC";
				$param[] = $parameter['NAME'];
				$where .= ($parameter['INNAME']==1 && $parameter['INFILE']==1?"":" and (").$wheretemp.")";
			}
		}
		//check object
		if($parameter['OBJECT']!=""){
			$innerjoin .= "
				INNER JOIN WF_PROCESSES wfp on wfitem.ID_P = wfp.ID_P
				INNER JOIN WF_CLASSES class on class.ID_C = wfp.ID_C
			";
			$where .= " and class.ALIAS = ?";
			$param[] = $parameter['OBJECT'];
		}
		$sql = "
			SELECT
				count(*) as CNT
			FROM
				$tablename hscv
				inner join $tablewfitem wfitem on hscv.ID_PI = wfitem.ID_PI
				$innerjoin
			WHERE
				$where
			GROUP BY
				hscv.ID_HSCV
		";
	
		try{
			
		$r = $this->getDefaultAdapter()->query($sql,$param);
		}catch(Exception $ex){
			echo $ex->__toString();
			return 0;
		}
		return $r->rowCount();
	}
	/**
	 * Lấy danh sách các hồ sơ công việc dựa trên tham số đầu vào.
	 * Danh sách tham số đầu vào
	 * ID_THUMUC
	 * ID_LOAIHSCV
	 * NGAY_BD
	 * NGAY_KT
	 * NAME
	 * TRANGTHAI
	 * ID_P
	 * ID_A
	 * ID_U
	 * @param array $parameter
	 */
	function SelectAll($parameter,$offset,$limit,$order){
	$where = "(1=1)";
		$param = array();
		$innerjoin = "";
		//var_dump($parameter);
		//Lấy tên table dựa trên ngày bắt đầu
		$realyear = QLVBDHCommon::getYear();
		$tablename = 'hscv_hosocongviec_'.$realyear;
		//$where .= "  and IS_CHOXULY <> 1";
		if($parameter['CODE']=='' || strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and (IS_THEODOI<>1) ";
			$tablewfitem = 'wf_processitems_'.$realyear;
			$where .= "  and ( hscv.IS_CHOXULY = 0 OR hscv.IS_CHOXULY is NULL ) ";
		}else if(strtoupper($parameter['CODE'])=='PRE'){
			$tablewfitem = '
				(SELECT ID_U_SEND as ID_U, ID_A_BEGIN as ID_A,pl.ID_PI,DATESEND as LASTCHANGE,pl.ID_P,t.CNTPL
					FROM
					wf_processlogs_'.$realyear.' pl
					INNER JOIN (SELECT ID_PL as ID_PL,count(ID_PL) as CNTPL FROM wf_processlogs_'.$realyear.' GROUP BY ID_PI HAVING count(ID_PL)>1) t on t.ID_PL = pl.ID_PL
				WHERE
					ID_U_SEND = ?
				)
			';
			
			$param[] = $parameter['ID_U'];
		}else if(strtoupper($parameter['CODE'])=='OLD'){
			$tablewfitem = '
				(SELECT ID_U_SEND as ID_U, ID_A_BEGIN as ID_A,pl.ID_PI,DATESEND as LASTCHANGE,pl.ID_P,t.CNTPL
					FROM
					wf_processlogs_'.$realyear.' pl
					INNER JOIN (SELECT ID_PL as ID_PL,(SELECT COUNT(*) FROM wf_processlogs_'.$realyear.' temp WHERE temp.ID_PI = temp1.ID_PI) as CNTPL FROM wf_processlogs_'.$realyear.' temp1) t on t.ID_PL = pl.ID_PL
				WHERE
					ID_U_SEND = ?
				)
			';
			$param[] = $parameter['ID_U'];
			//$param[] = $parameter['ID_U'];
		}
		if(strtoupper($parameter['CODE'])=='PHOIHOP'){
			$tablewfitem = 'wf_processitems_'.$realyear;
			$innerjoin .= "
				INNER JOIN (SELECT * FROM ".QLVBDHCommon::Table("HSCV_PHOIHOP")."  WHERE ID_U = ?) ph on ph.ID_HSCV = hscv.ID_HSCV
			";
			$param[] = $parameter['ID_U'];
		}
		
		//Check thư mục
		if($parameter['ID_THUMUC']>1 && strtoupper($parameter['CODE'])!='OLD' && strtoupper($parameter['CODE'])!='PRE' && strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and (hscv.ID_THUMUC = ? OR hscv.ID_THUMUC_HSCV = ?)";
			$param[] = $parameter['ID_THUMUC'];
			$param[] = $parameter['ID_THUMUC'];
		}else if(strtoupper($parameter['CODE'])=='ZIP'){
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = -1;
		}else if(strtoupper($parameter['CODE'])=='OLD'){

		}else{
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = 1;
		}
		
		//check thu muc ho so cong viec
		if($parameter['IS_THUMUC_HSCV'] == 1){
			$where .= " and ( hscv.ID_THUMUC_HSCV = ?)";
			$param[] = $parameter['ID_THUMUC'];
			
		}
		


		//Check loai hscv
		if($parameter['ID_LOAIHSCV']>0){
			$where .= " and hscv.ID_LOAIHSCV = ?";
			$param[] = $parameter['ID_LOAIHSCV'];
		}
		
		
		//Check mã số hồ sơ một cửa
		$is_ser_mc = 0;
		if($parameter['MASOHOSO'] !=""){
			$is_ser_mc = 1;
			$mshs = substr($parameter['MASOHOSO'],0,12);
			$where .= " and mc.MAHOSO REGEXP '^".$mshs."' ";
			//$param[] = $parameter['MASOHOSO'];
		}

		if($parameter['TENTOCHUCCANHAN'] !=""){
			$is_ser_mc = 1;
			$mshs = trim($parameter['TENTOCHUCCANHAN']);
			$where .= " and mc.TENTOCHUCCANHAN LIKE '%".$mshs."%' ";
			//$param[] = $parameter['MASOHOSO'];
		}
		
		if($parameter['NHAN_NGAY_BD']!=""){
			$is_ser_mc = 1;
			$ngayden_bd = $parameter['NHAN_NGAY_BD']." 00:00:01";
			
			$where .= " and mc.NHAN_NGAY >= ?";
			$param[] = $ngayden_bd;
		}

		if($parameter['NHAN_NGAY_KT']!=""){
			$is_ser_mc = 1;
			$ngayden_bd = $parameter['NHAN_NGAY_KT']." 23:59:59";
			
			$where .= " and mc.NHAN_NGAY <= ?";
			$param[] = $ngayden_bd;
		}
		if($is_ser_mc == 1){
			$innerjoin .= "
				INNER JOIN ". QLVBDHCommon::Table("MOTCUA_HOSO") ." mc on hscv.ID_HSCV = mc.ID_HSCV ";
		}

		// Tim kiem kem theo van ban den
		$is_ser_vbd = 0;
		if((int)$parameter['ID_SVB']){
			$is_ser_vbd = 1;
			
			$where .= " and vbd.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		if((int)$parameter['ID_LVB']){
			$is_ser_vbd = 1;
			
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		
		if($parameter['SODEN']!=""){
			$is_ser_vbd = 1;
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

		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$is_ser_vbd = 1;
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
		
		//Check ngÃ y Ä‘áº¿n bd
		if($parameter['NGAYDEN_BD']!=""){
			$is_ser_vbd = 1;
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:01";
			
			$where .= " and vbd.NGAYDEN >= ?";
			$param[] = $ngayden_bd;
		}
		//Check ngÃ y Ä‘áº¿n kt
		if($parameter['NGAYDEN_KT']!=""){
			$is_ser_vbd = 1;
			$ngayden_kt = $parameter['NGAYDEN_KT']." 23:59:59";
			$where .= " and vbd.NGAYDEN <= ?";
			$param[] = $ngayden_kt;
		}

		 //Check ngÃ y ban hÃ nh bd
		if($parameter['NGAYBANHANH_BD']!=""){
			$is_ser_vbd = 1;
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}
		//Check ngÃ y ban hÃ nh kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$is_ser_vbd = 1;
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}


		if($is_ser_vbd==1){
			$innerjoin .=  " INNER JOIN  
			".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_vbden_hsc on hscv.ID_HSCV = fk_vbden_hsc.ID_HSCV  
			
			INNER JOIN ".QLVBDHCommon::Table('vbd_vanbanden')." vbd  on fk_vbden_hsc.ID_VBDEN = vbd.ID_VBD ";
		}

		
	
		
		//Check ngay bd
		if($parameter['NGAY_BD']!=""){
			$ngaybd = $parameter['NGAY_BD']." 00:00:01";
			$where .= " and hscv.NGAY_BD >= ?";
			$param[] = $ngaybd;
		}
		//Check ngay kt
		if($parameter['NGAY_KT']!=""){
			$ngaykt = $parameter['NGAY_KT']." 23:59:59";
			$where .= " and hscv.NGAY_BD <= ?";
			$param[] = $ngaykt;
		}
		//Check activity
		if($parameter['TRANGTHAI']>0){
			$where .= " and wfitem.ID_A = ?";
			$param[] = $parameter['TRANGTHAI'];
		}
		//Check user
		if($parameter['ID_U']>0 && strtoupper($parameter['CODE'])=="" && $parameter['ID_THUMUC']==1){
			$where .= " and (wfitem.ID_U = ? or wfitem.ID_DEP = ? or wfitem.ID_G in (".$parameter['ID_G']."))";
			$param[] = $parameter['ID_U'];
			$param[] = $parameter['ID_DEP'];
			//$param[] = "(1,2,6)";//$parameter['ID_G'];
			//echo $parameter['ID_G'];
		}
		//Check name
		if($parameter['NAME']!=""){
			$wheretemp = "";
			if($parameter['INNAME']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('HSCV_HOSOCONGVIEC'));
				$wheretemp = " match(hscv.NAME) against (? IN BOOLEAN MODE)";
				$order = " match(hscv.NAME) against ('".str_replace("'","''",$parameter['NAME'])."') DESC";
				$param[] = $parameter['NAME'];
				$where .= " and (".$wheretemp.($parameter['INNAME']==1 && $parameter['INFILE']==1?" OR ":")");
			}
			if($parameter['INFILE']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('GEL_FILEDINHKEM'));
				$innerjoin .= " left join hscv_duthao_".$realyear." dt on dt.ID_HSCV = hscv.ID_HSCV";
				$innerjoin .= " left join hscv_phienbanduthao_".$realyear." pbdt on pbdt.ID_DUTHAO = dt.ID_DUTHAO";
				$innerjoin .= " left join gen_filedinhkem_".$realyear." dk on dk.ID_OBJECT = pbdt.ID_PB_DUTHAO and dk.TYPE=2";
				$wheretemp = " match(dk.CONTENT) against (? IN BOOLEAN MODE)";
				$order = " match(dk.CONTENT) against ('".str_replace("'","''",$parameter['NAME'])."') DESC";
				$param[] = $parameter['NAME'];
				$where .= ($parameter['INNAME']==1 && $parameter['INFILE']==1?"":" and (").$wheretemp.")";
			}
		}
		
		//Check order
		if($order!=""){
			$order = "ORDER BY $order";
		}else{
			$order = "ORDER BY LASTCHANGE DESC";
		}
		
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//check object
		if($parameter['OBJECT']!=""){
			$innerjoin .= "
				INNER JOIN WF_PROCESSES wfp on wfitem.ID_P = wfp.ID_P
				INNER JOIN WF_CLASSES class on class.ID_C = wfp.ID_C
			";
			$where .= " and class.ALIAS = ?";
			$param[] = $parameter['OBJECT'];
		}
		
		$sql = "
			SELECT
				distinct hscv.*".(strtoupper($parameter['CODE'])=="OLD"?",wfitem.CNTPL":"").(strtoupper($parameter['CODE'])!="OLD"?",wfitem.ID_A":"").(strtoupper($parameter['CODE'])=="PRE"?",wfitem.CNTPL":"").
				(strtoupper($parameter['CODE'])=="PHOIHOP"?",ph.DA_XEM as IS_NEW_PH ":"").
				"
				, class1.ALIAS
			FROM
				$tablename hscv
				inner join $tablewfitem wfitem on hscv.ID_PI = wfitem.ID_PI
				$innerjoin
				inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV
				inner join WF_PROCESSES wfp1 on wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class1 on class1.ID_C = wfp1.ID_C
			WHERE
				$where
				$order
				$strlimit
		";
		try{
				//echo $sql;exit;
			//var_dump($param);
			$r = $this->getDefaultAdapter()->query($sql,
			$param);
			$result = $r->fetchAll();
			//var_dump($param);
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
	}
	/**
	 * Tạo mới hồ sơ công việc. Chú ý: các tham số đầu vào mặc định là đúng. No transaction.
	 * @param string $name
	 * @param int $idthumuc
	 * @param int $idloaihscv
	 * @param yyyy-mm-dd $ngaybd
	 * @param yyyy-mm-dd $ngaykt
	 * @param int $processalias
	 * @param int $usercreate
	 * @param int $userceceive
	 * @return int $idhscv,-1: lỗi hệ thống,-2: lỗi không tìm thấy quy trình,-3: lỗi không thêm mới được hscv,-4: lỗi không tạo process
	 * @author nguyennd 
	 */
	function CreateHSCV($name,$idthumuc,$idloaihscv,$ngaybd,$ngaykt,$usercreate,$userceceive,$name1,$hanxuly,$before,$sms,$email,$idunn){
		 //Lấy tên table dựa trên ngày bắt đầu
		 $tablename = 'hscv_hosocongviec_'.QLVBDHCommon::getYear();
		 
		 try{
		 	//Lấy mã số quy trình
		 	$r = $this->getDefaultAdapter()->query("
		 		select MASOQUYTRINH from hscv_loaihosocongviec where id_loaihscv=?
		 	",array($idloaihscv));
		 	
		 	//Nếu tìm thấy quy trình
		 	if($r->rowCount()==1){
				
		 		//Lấy mã số quy trình
		 		$masoquytrinh = $r->fetchColumn(0);
				$r->closeCursor();
				$sql="SELECT concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP WHERE u.ID_U = ?";
				$r = $this->getDefaultAdapter()->query($sql,$userceceive);
				$u = $r->fetch();
				$extra = $u['FULLNAME'];
				//Thêm mới HSCV
				$idhscv = $this->getDefaultAdapter()->insert($tablename,array(
					"ID_THUMUC"=>$idthumuc,
					"ID_LOAIHSCV"=>$idloaihscv,
					"NGAY_BD"=>$ngaybd,
					"NGAY_KT"=>$ngaykt,
					"NAME"=>$name,
					"BEFORE"=>$before,
					"SMS"=>$sms,
					"EMAIL"=>$email,
					"ID_U_NN"=>$idunn
				));
				//QLVBDHCommon::UpdateIndex("HSCV_HOSOCONGVIEC",$name,$idhscv);
				if($idhscv==1){
					$idhscv = $this->getDefaultAdapter()->lastInsertId($tablename);
					//Thêm mới process
					$idpi = WFEngine::CreateProcess($masoquytrinh,$idhscv,$name,$usercreate,$userceceive,$name1,$hanxuly);
					//echo $idpi; exit;
					//Cập nhật HSCV theo process item mới nếu insert thành công quy trình
					if($idpi<=0){
						return -4;
					}else{
						$this->getDefaultAdapter()->update($tablename,array("ID_PI"=>$idpi),"ID_HSCV=".$idhscv);
						return $idhscv;
					}
				}else{
					return -3;
				}
			}else{
				return -2;
			}
		 }catch(Exception $ex){
		 	echo $ex->__toString();
		 	return -1;
		 }
	}
/**
	 * Tạo mới hồ sơ công việc. Chú ý: các tham số đầu vào mặc định là đúng. No transaction.
	 * @param string $name
	 * @param int $idthumuc
	 * @param int $idloaihscv
	 * @param yyyy-mm-dd $ngaybd
	 * @param yyyy-mm-dd $ngaykt
	 * @param int $processalias
	 * @param int $usercreate
	 * @param int $userceceive
	 * @return int $idhscv,-1: lỗi hệ thống,-2: lỗi không tìm thấy quy trình,-3: lỗi không thêm mới được hscv,-4: lỗi không tạo process
	 * @author nguyennd 
	 */
	function CreateHSCV2($name,$idthumuc,$idloaihscv,$ngaybd,$ngaykt,$usercreate,$idreceive,$type){
		 //Lấy tên table dựa trên ngày bắt đầu
		 $tablename = 'hscv_hosocongviec_'.QLVBDHCommon::getYear();
		 
		 try{
		 	//Lấy mã số quy trình
		 	$r = $this->getDefaultAdapter()->query("
		 		select MASOQUYTRINH from hscv_loaihosocongviec where id_loaihscv=?
		 	",array($idloaihscv));
		 	//var_dump($r->fetchAll());
		 	//Nếu tìm thấy quy trình
		 	if($r->rowCount()==1){
				
		 		//Lấy mã số quy trình
		 		$masoquytrinh = $r->fetchColumn(0);
				$r->closeCursor();
				$sql="SELECT concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP WHERE u.ID_U = ?";
				$r = $this->getDefaultAdapter()->query($sql,$idreceive);
				$u = $r->fetch();
				$extra = $u['FULLNAME'];
				//Thêm mới HSCV
				$idhscv = $this->getDefaultAdapter()->insert($tablename,array(
					"ID_THUMUC"=>$idthumuc,
					"ID_LOAIHSCV"=>$idloaihscv,
					"NGAY_BD"=>$ngaybd,
					"NGAY_KT"=>$ngaykt,
					"NAME"=>$name
				));
				if($idhscv==1){
					$idhscv = $this->getDefaultAdapter()->lastInsertId($tablename);
					//Thêm mới process
					$idpi = WFEngine::CreateProcess2(
						$masoquytrinh,
						$idhscv,
						$name,
						$usercreate,
						$idreceive,
						$type
					);
					echo $idpi;
					//Cập nhật HSCV theo process item mới nếu insert thành công quy trình
					if($idpi<=0){
						return -4;
					}else{
						$this->getDefaultAdapter()->update($tablename,array("ID_PI"=>$idpi),"ID_HSCV=".$idhscv);
						return $idhscv;
					}
				}else{
					return -3;
				}
			}else{
				return -2;
			}
		 }catch(Exception $ex){
		 	return -1;
		 }
	}
	function rollback($idhscv,$userid){
		$realyear = QLVBDHCommon::getYear();
		$tablename = 'hscv_hosocongviec_'.$realyear;
		$r = $this->getDefaultAdapter()->query("SELECT ID_PI,ID_HSCV FROM $tablename WHERE ID_HSCV = ?",$idhscv);
		$hscv = $r->fetch();
		$id_pi = $hscv['ID_PI'];
		try{
			$this->getDefaultAdapter()->beginTransaction();
			$ok = WFEngine::RollBack($idhscv,$userid);
			if($ok>0){
				//$this->getDefaultAdapter()->update($tablename,array("ID_THUMUC"=>1),"ID_HSCV=".$hscv['ID_HSCV']);
				$this->getDefaultAdapter()->commit();
				return $ok;
			}else{
				$this->getDefaultAdapter()->rollback();
				return $ok;
			}
		}catch(Exception $ex){
			echo $ex->__toString();
			$this->getDefaultAdapter()->rollBack();
			return 0;
		}
	}
	/**
	 * Ho so cong viẹc co duoc luu tru chua
	 * return bool 
	 */
	static  function isLuutru($idHSCV,$year){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `ID_THUMUC`
		from `hscv_hosocongviec_".$year."` 
		where `ID_HSCV` = ?
		";
		$query = $dbAdapter->query($sql,array($idHSCV));
		$re = $query->fetch();
		if($re["ID_THUMUC"] >1)
			return true;
		return false;
	}
	function bosung($idhscv){
		$r = $this->getDefaultAdapter()->query("
			SELECT
				pyb.*,concat(empyc.FIRSTNAME , ' ' , empyc.LASTNAME) as UYCNAME,
				concat(empbs.FIRSTNAME , ' ' , empbs.LASTNAME) as UBSNAME
			FROM
				HSCV_PHIEU_YEUCAU_BOSUNG_".QLVBDHCommon::getYear()." pyb
				INNER JOIN QTHT_USERS uyc on uyc.ID_U=pyb.NGUOIYEUCAU
				INNER JOIN QTHT_EMPLOYEES empyc on uyc.ID_EMP = empyc.ID_EMP
				LEFT JOIN QTHT_USERS ubs on ubs.ID_U=pyb.NGUOIBOSUNG
				LEFT JOIN QTHT_EMPLOYEES empbs on ubs.ID_EMP = empbs.ID_EMP
			WHERE
				ID_HSCV = ?
			ORDER BY
				ID_YEUCAU DESC
		",$idhscv);
		return $r->fetchAll();
	}
	static function getlastphieutrinh($idhscv){
		global $db;
		$sql = "
			SELECT *
			FROM
				".QLVBDHCommon::Table("HSCV_TOTRINH")."
			WHERE
				ID_HSCV = ?
			ORDER BY
				ID_TT DESC
		";
		$r = $db->query($sql,$idhscv);
		return $r->fetch();
	}
	static function getlastlog($idpi){
		global $db;
		$sql = "
			SELECT *
			FROM
				".QLVBDHCommon::Table("WF_PROCESSLOGS")."
			WHERE
				ID_PI = ?
			ORDER BY
				ID_PL DESC
		";
		$r = $db->query($sql,$idpi);
		return $r->fetch();
	}

	/**
	 * check văn thư
	 * return bool 
	 */
	static function isVanthu($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "SELECT 
					u.id_u, u.username, g.id_g, g.name 
				FROM 
					`qtht_users` u 
				left join `fk_users_groups` ug on ug.id_u=u.id_u 
				left join qtht_groups g on ug.id_g = g.id_g 
				WHERE 
					g.id_g=7 and u.id_u=?
		";
		$query = $dbAdapter->query($sql,array($id_u));
		$re = $query->fetch();
		if($re["id_u"] >0)
			return true;
		return false;
	}
/**
*	Kiem tra nguoi dung co the xem van ban toan co quan hay khong
*	return true/false
**/
	static function isAlowSeeAllVbDi(){
		$user = Zend_Registry::get("auth")->getIdentity();
		$id_acs = ResourceUserModel::getActionByUrl("vbdi","banhanh","listall");
		foreach ($id_acs as $id_ac){
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$id_ac)){
				return true;
			}
		}
		return false;
	}
	static function isAlowBanhanhVbDi(){
		$user = Zend_Registry::get("auth")->getIdentity();
		$id_acs = ResourceUserModel::getActionByUrl("vbdi","banhanh","save");
		foreach ($id_acs as $id_ac){
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$id_ac)){
				return true;
			}
		}
		return false;
	}
/**
*	Kiem tra nguoi dung co the xem van ban toan co quan hay khong
**/
	static function isAlowSeeAllVbDen(){
		$user = Zend_Registry::get("auth")->getIdentity();
		$id_acs = ResourceUserModel::getActionByUrl("vbden","vbden","listall");
		foreach ($id_acs as $id_ac){
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$id_ac)){
				return true;
			}
		}
		return false;
	}
	
	static function isAlowSeeAllVbDenOld(){
		$user = Zend_Registry::get("auth")->getIdentity();
		$id_acs = ResourceUserModel::getActionByUrl("vbden","vbden","showold");
		foreach ($id_acs as $id_ac){
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$id_ac)){
				return true;
			}
		}
		return false;
	}
static function isAlowSeeAllVbDiOld(){
		$user = Zend_Registry::get("auth")->getIdentity();
		$id_acs = ResourceUserModel::getActionByUrl("vbdi","banhanh","showold");
		foreach ($id_acs as $id_ac){
			if(ResourceUserModel::isAcionAlowed($user->USERNAME,$id_ac)){
				return true;
			}
		}
		return false;
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
	function Count_vbd($parameter){
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;
		
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
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd where IS_PHOBIEN =1";
			$row = $db->query($sql);
			//var_dump($row->fetchAll());
			return $row->rowcount();
			//echo $sql;
			//return array();
		}
		
		$isseeall = (hosocongviecModel::isVanthu($id_u) || (hosocongviecModel::isAlowSeeAllVbDen() && ($parameter['IS_SEE_ALL']==1)));
		//Build query
		$sql = "";
		$where = "(1=1)";
		$join = "";
		$select = "";
		//where
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
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}
		//Fulltext
		if($parameter['TRICHYEU']!=""){
			if($parameter['INFILE']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
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
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_VANBANDEN'));
				$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
				$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				if($isseeall){
					$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INNAME` DESC";
				}
			}
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
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on vbd.ID_VBD = lc.ID_VBD 
				where $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $user->ID_U ." 
				
			";
			
			$row = $db->query($sql,$param);
			//echo $sql;
			//var_dump($param);
			$row = $row->fetchAll();
			return count($row);
		}
		
		if($isseeall){
			$sql = "
				SELECT 
					vbd.ID_VBD as CNT
				FROM
					".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					$join
				WHERE
					$where
				GROUP BY vbd.ID_VBD
			";
		}else{
			$sql = "
				SELECT
					vbd.ID_VBD as CNT
				FROM
				(
					SELECT 
						vbd.* $select
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
						inner join
							(SELECT distinct hscv.ID_HSCV FROM ".QLVBDHCommon::table("HSCV_HOSOCONGVIEC")." hscv inner join
							".QLVBDHCommon::table("WF_PROCESSITEMS")." pi on pi.ID_O = hscv.ID_HSCV inner join
							".QLVBDHCommon::table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI where (pl.ID_U_SEND = $id_u or
							pl.ID_U_RECEIVE = $id_u)) hscv on hscv.ID_HSCV = fk_v_h.ID_HSCV
						$join
					WHERE
						$where
					UNION ALL
					SELECT 
						vbd.* $select
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD
						$join
					WHERE
						$where and lc.`NGUOINHAN`= ". $user->ID_U ."
				) vbd
				GROUP BY vbd.ID_VBD
			";
		}
		if(!$isseeall){
		$param = array_merge($param,$param);
		}
		$row = $db->query($sql,$param);
		//echo $sql;
		//var_dump($param);
		$row = $row->fetchAll();
		return count($row);
	}
	/**
	 * Lấy danh sách các hồ sơ công việc dựa trên tham số đầu vào.
	 * NGAY_BD
	 * NGAY_KT
	 * NAME
	 * ID_U
	 * @param array $parameter
	 */
	function SelectAll_vbd($parameter,$offset,$limit,$order){
            global $auth;
		global $db;
		$user = $auth->getIdentity();
		$id_u = $user->ID_U;
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
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd  where IS_PHOBIEN =1";
			$row = $db->query($sql);
			//var_dump($row->fetchAll());
			return $row->fetchAll();
			//echo $sql;
			//return array();
		}
		
		$isseeall = (hosocongviecModel::isVanthu($id_u) || (hosocongviecModel::isAlowSeeAllVbDen() && ($parameter['IS_SEE_ALL']==1)));
		//Build query
		$sql = "";
		$where = "(1=1)";
		$join = "";
		$select = "";
		//where
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
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}
		//Fulltext
		if($parameter['TRICHYEU']!=""){
			if($parameter['INFILE']==1){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
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
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_VANBANDEN'));
				$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
				$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				if($isseeall){
					$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INNAME` DESC";
				}
			}
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
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on vbd.ID_VBD = lc.ID_VBD 
					where  $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $user->ID_U ." 
					 
					$order 
					$strlimit
			";
			$row = $db->query($sql,$param);
			return $row->fetchAll();

		}
		
		
		if($isseeall){
			$sql = "
				SELECT 
					distinct vbd.*
				FROM
					".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
					$join
				WHERE
					$where
				ORDER BY NGAYDEN DESC
				$strlimit
			";
		}else{
			$sql = "
				SELECT distinct 
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
				(
					SELECT 
						hscv.DATESEND as NGAYGUI , vbd.* $select, '1' AS DA_XEM
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('vbd_fk_vbden_hscvs')." fk_v_h on fk_v_h.ID_VBDEN = vbd.ID_VBD
						inner join
							(SELECT distinct hscv.ID_HSCV,pl.DATESEND FROM ".QLVBDHCommon::table("HSCV_HOSOCONGVIEC")." hscv inner join
							".QLVBDHCommon::table("WF_PROCESSITEMS")." pi on pi.ID_O = hscv.ID_HSCV inner join
							".QLVBDHCommon::table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI where (pl.ID_U_SEND = $id_u or
							pl.ID_U_RECEIVE = $id_u)) hscv on hscv.ID_HSCV = fk_v_h.ID_HSCV
						$join
					WHERE
						$where
					UNION 
					SELECT 
					lc.NGAYCHUYEN as NGAYGUI, vbd.* $select , lc.DA_XEM+0 as DA_XEM
					FROM
						".QLVBDHCommon::Table('VBD_VANBANDEN')." vbd
						inner join ".QLVBDHCommon::Table('VBD_DONGLUANCHUYEN')." lc on lc.ID_VBD = vbd.ID_VBD
						$join
					WHERE
						$where and lc.`NGUOINHAN`= ". $user->ID_U ."
				) vbd
				$order , NGAYGUI DESC
				$strlimit
			";
		}
      // echo $sql;exit;
		if(!$isseeall){
		$param = array_merge($param,$param);
		}
                
		$row = $db->query($sql,$param);
		
		//var_dump($param);
		return $row->fetchAll();
	}
	static function selectUser_HSCV($type,$id_u){
		
		$arr_id_hscv = array();
		$year = QLVBDHCommon::getYear();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select hscv.`ID_HSCV` 
 from
(select ID_HSCV , ID_PI from `hscv_hosocongviec_$year` hscv1 where  hscv1.`ID_LOAIHSCV` =?) hscv
inner join  (SELECT * from `wf_processlogs_$year` where `ID_U_SEND` =? or `ID_U_RECEIVE`=?) wfl
on hscv.`ID_PI` = wfl.`ID_PI`
group by hscv.`ID_HSCV`
		";
		
		//try{
		$stm = $dbAdapter->query($sql,array($type,$id_u,$id_u));
		$re = $stm->fetchAll();
		//tra ve mang gom cac id HSCV
		
		foreach ($re as $it_re){
			array_push($arr_id_hscv,$it_re["ID_HSCV"]);
		}
		//}catch(Exception $ex){
				
		//}
		
		
		return $arr_id_hscv;
	}
	
	static function selectUser_LCVBDEN($id_u){
		
		$year = QLVBDHCommon::getYear();
		$arr_id_hscv = array();
		$year = QLVBDHCommon::getYear();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT
				vbd.ID_VBD
			FROM
				vbd_vanbanden_$year vbd
				inner JOIN vbd_dongluanchuyen_$year dongluanchuyen on vbd.ID_VBD = dongluanchuyen.ID_VBD
			where 
				dongluanchuyen.NGUOINHAN = ?
			group by vbd.ID_VBD
			
		";
		
		try{
		$stm = $dbAdapter->query($sql,array($id_u));
		$re = $stm->fetchAll();
		//tra ve mang gom cac id HSCV
		
		foreach ($re as $it_re){
			array_push($arr_id_hscv,$it_re["ID_VBD"]);
		}
		}catch(Exception $ex){
				
		}
		return $arr_id_hscv;
	}
	
	static function selectUser_LCVBDI($id_u){
		
		$year = QLVBDHCommon::getYear();
		$arr_id_hscv = array();
		$year = QLVBDHCommon::getYear();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT
				vbd.ID_VBDI
			FROM
				vbdi_vanbandi_$year vbd
				inner JOIN vbdi_dongluanchuyen_$year dongluanchuyen on vbd.ID_VBDI = dongluanchuyen.ID_VBDI
			where
				dongluanchuyen.NGUOINHAN = ?
			group by vbd.ID_VBDI
			
		";
		
		try{
		$stm = $dbAdapter->query($sql,array($id_u));
		$re = $stm->fetchAll();
		//tra ve mang gom cac id HSCV
		
		foreach ($re as $it_re){
			array_push($arr_id_hscv,$it_re["ID_VBDI"]);
		}
		}catch(Exception $ex){
				
		}
		
		return $arr_id_hscv;
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
	function Count_vbdi($parameter){
	    $where = "(1=1)";
	    $join = "";
		$param = array();
		//Lấy tên table dựa trên ngày bắt đầu
		$realyear = QLVBDHCommon::getYear();		
	    //Check id
		if($parameter['ID_VBDI']!=""){
			$where .= " and vbdi.id_vbdi = ?";
			$param[] = $parameter['ID_VBDI'];
		}
		//Check trich yeu
		if($parameter['TRICHYEU']!=""){
			if($parameter['INNAME']){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBDI_VANBANDI'));
				$where .= " and ".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?"(":"")."match(vbdi.TRICHYEU) against ('".str_replace("'","''",$parameter['TRICHYEU'])."' IN BOOLEAN MODE)";
				$order .= " ,match(vbdi.TRICHYEU) against ('".str_replace("'","''",$parameter['TRICHYEU'])."') DESC";
			}
			if($parameter['INFILE']){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
				$join .= " left join ".QLVBDHCommon::Table('gen_filedinhkem')." dk on dk.ID_OBJECT = vbdi.ID_VBDI and dk.TYPE=5";
				$where .= " ".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?" OR ":" AND ")." match(dk.CONTENT) against ('".str_replace("'","''",$parameter['TRICHYEU'])."' IN BOOLEAN MODE)".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?")":"");
				$order .= " ,match(dk.CONTENT) against ('".str_replace("'","''",$parameter['TRICHYEU'])."') DESC";
			}
		}
	  
	    //Check cơ quan
		if($parameter['ID_CQ']>0 && $parameter['ID_CQ']!=""){
			$where .= " and vbdi.ID_CQ = ?";
			$param[] = $parameter['ID_CQ'];
		}
		else if($parameter['COQUANBANHANH_TEXT']!="") 
		{
			$where .= " and vbdi.COQUANBANHANH_TEXT = ?";
			$param[] = $parameter['COQUANBANHANH_TEXT'];
		}
		//check sổ văn bản
	    if($parameter['ID_SVB']>0 && $parameter['ID_SVB']!=""){
			$where .= " and vbdi.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		//check lĩnh vực văn bản
		if($parameter['ID_LVVB']>0 && $parameter['ID_LVVB']!=""){
			$where .= " and vbdi.ID_LVVB = ?";
			$param[] = $parameter['ID_LVVB'];
		}
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbdi.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		//check độ mật
	    if($parameter['DOMAT']>0 && $parameter['DOMAT']!=""){
			$where .= " and vbdi.DOMAT = ?";
			$param[] = $parameter['DOMAT'];
		}
		//check độ khẩn
	    if($parameter['DOKHAN']>0 && $parameter['DOKHAN']!=""){
			$where .= " and vbdi.DOKHAN = ?";
			$param[] = $parameter['DOKHAN'];
		}
		 //Check ngày ban hanh
		if($parameter['NGAYBANHANH_BD']!=""){
			$ngaytao_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbdi.NGAYBANHANH >= ?";
			$param[] = $ngaytao_bd;
		}
		//Check ngày tạo kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaytao_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbdi.NGAYBANHANH <= ?";
			$param[] = $ngaytao_kt;
		}
		
		// check so di
		if($parameter['SODI']!=""){
			$sodi_in = (int)$parameter['SODI'];
			if((String)$sodi_in != $parameter['SODI']){
				$where .= " and ( vbdi.SODI = ? ) ";
				$param[] = $parameter['SODI'];
			}else{
				$where .= " and (vbdi.SODI = ? or vbdi.SODI_IN = ?) ";
				$param[] = $parameter['SODI'];
				$param[] = $sodi_in;
			}
			
			
		}
		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$sokyhieu_in = (int)$parameter['SOKYHIEU'];
			if($sokyhieu_in >0){
				$where .= " and (vbdi.SOKYHIEU = ? OR vbdi.SOKYHIEU_IN = ?)";
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbdi.SOKYHIEU = ? ";
				$param[] = $parameter['SOKYHIEU'];
				
			}
		}
		
		$auth = Zend_Registry::get('auth');
		$id_u = $auth->getIdentity()->ID_U;
		
		if($parameter['CHUA_DOC'] == 1)
		{
			
			
			$sql = "
			SELECT distinct 
					vbdi.*
					
				FROM
						".QLVBDHCommon::Table('VBDI_VANBANDI')." vbdi
					inner join ".QLVBDHCommon::Table('VBDI_DONGLUANCHUYEN')." lc on vbdi.ID_VBDI = lc.ID_VBDI 
				where  $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $id_u ." 
				
			";
			
			$r = $this->getDefaultAdapter()->query($sql,$param);
			return $r->rowCount();
			
		}
		if($id_u>0){
		//check co trong dong luan chuyen hay khong
		$is_see_all = false;
		if(hosocongviecModel::isVanthu($id_u))
			$is_see_all = true;
		elseif ($parameter['IS_SEE_ALL'] > 0)
			if(hosocongviecModel::isAlowSeeAllVbDi())
				$is_see_all = true;
		if(hosocongviecModel::isAlowSeeAllVbDiOld()){
			$is_see_old = true;
		}
		if(!$is_see_all)
		{	
			//$join .= "inner JOIN vbd_dongluanchuyen_$realyear dongluanchuyen on vbd.ID_VBD = dongluanchuyen.ID_VBD";	
			$arr_vbdi = hosocongviecModel::selectUser_LCVBDI($id_u);
			$is_check_process = true;
			if(count($arr_vbdi) >0)
			{
				
				$str_vbdi = "(".implode(',',$arr_vbdi).")";
				$where .= "  and vbdi.ID_VBDI in $str_vbdi " ;
				
				//echo $where;
			}else if($is_see_old && QLVBDHCommon::getYear()==2008 ){
			$where .= "  and vbdi.OLD = 1" ;
		}else{
				$where .= " and 1=0"	;
			}
		}
		
		} 
		
			$sql = "
			SELECT
				count(*) as CNT
			FROM
				vbdi_vanbandi_$realyear vbdi			
			$join
			WHERE
				$where
			GROUP BY
			    vbdi.ID_VBDI
		";
		try{
			//echo $sql;exit;
			$r = $this->getDefaultAdapter()->query($sql,$param);
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}	
		return $r->rowCount();
	}
	/**
	 * Lấy danh sách các hồ sơ công việc dựa trên tham số đầu vào.
	 * NGAY_BD
	 * NGAY_KT
	 * NAME
	 * ID_U
	 * @param array $parameter
	 */
	function SelectAll_vbdi($parameter,$offset,$limit,$order){	
		$where = "(1=1)";
		$join = "";
		$param = array();
		//Lấy tên table dựa trên ngày bắt đầu
		$realyear = QLVBDHCommon::getYear();
		global $db;
		if($parameter['IS_PHOBIEN'] == 1){
			
			$sql = "
			SELECT distinct 
					vbdi.`ID_VBDI`,
					`ID_LVB`,
					`ID_HSCV`,
					`ID_CQ`,
					`NGUOITAO`,
					`NGUOIKY`,
					`ID_LVVB`,
					`ID_SVB`,
					`SOKYHIEU`,
					`SODI`,
					`NGAYTAO`,
					`COQUANBANHANH_TEXT`,
					`TRICHYEU`,
					`SOBAN`,
					`SOTO`,
					`DOMAT`,
					`DOKHAN`,
					`NOIDEN`,
					`NGAYBANHANH`,
					`MASOVANBAN`,
					`SODI_IN`,
					`SOKYHIEU_IN`,
					`NGUOISOAN`,
					`NGUOI_THEMMOI`						
				FROM
						".QLVBDHCommon::Table('VBDI_VANBANDI')." vbdi  where IS_PHOBIEN =1";
			$row = $db->query($sql);
			//var_dump($row->fetchAll());
			return $row->fetchAll();
			//echo $sql;
			//return array();
		}

	    //Check id
		if($parameter['ID_VBDI']!=""){
			$where .= " and vbdi.id_vbdi = ?";
			$param[] = $parameter['ID_VBDI'];
		}
		//Check trich yeu
		if($parameter['TRICHYEU']!=""){
			if($parameter['INNAME']){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBDI_VANBANDI'));
				$where .= " and ".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?"(":"")."match(vbdi.TRICHYEU) against ('".str_replace("'","''",$parameter['TRICHYEU'])."' IN BOOLEAN MODE)";
				$order .= " ,match(vbdi.TRICHYEU) against ('".str_replace("'","''",$parameter['TRICHYEU'])."') DESC";
			}
			if($parameter['INFILE']){
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('gen_filedinhkem'));
				$join .= " left join ".QLVBDHCommon::Table('gen_filedinhkem')." dk on dk.ID_OBJECT = vbdi.ID_VBDI and dk.TYPE=5";
				$where .= " ".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?" OR ":" AND ")." match(dk.CONTENT) against ('".str_replace("'","''",$parameter['TRICHYEU'])."' IN BOOLEAN MODE)".(($parameter['INNAME']==1 && $parameter['INFILE']==1)?")":"");
				$order .= " ,match(dk.CONTENT) against ('".str_replace("'","''",$parameter['TRICHYEU'])."') DESC";
			}
		}
	    
		
		//Check cơ quan
		if($parameter['ID_CQ']>0 && $parameter['ID_CQ']!=""){
			$where .= " and vbdi.ID_CQ = ?";
			$param[] = $parameter['ID_CQ'];
		}
		else if($parameter['COQUANBANHANH_TEXT']!="") 
		{
			$where .= " and vbdi.COQUANBANHANH_TEXT = ?";
			$param[] = $parameter['COQUANBANHANH_TEXT'];
		}
		//check sổ văn bản
	    if($parameter['ID_SVB']>0 && $parameter['ID_SVB']!=""){
			$where .= " and vbdi.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		//check lĩnh vực văn bản
		if($parameter['ID_LVVB']>0 && $parameter['ID_LVVB']!=""){
			$where .= " and vbdi.ID_LVVB = ?";
			$param[] = $parameter['ID_LVVB'];
		}
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbdi.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		//check độ mật
	    if($parameter['DOMAT']>0 && $parameter['DOMAT']!=""){
			$where .= " and vbdi.DOMAT = ?";
			$param[] = $parameter['DOMAT'];
		}
		//check độ khẩn
	    if($parameter['DOKHAN']>0 && $parameter['DOKHAN']!=""){
			$where .= " and vbdi.DOKHAN = ?";
			$param[] = $parameter['DOKHAN'];
		}
	    //Check ngày ban hanh
		if($parameter['NGAYBANHANH_BD']!=""){
			$ngaytao_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbdi.NGAYBANHANH >= ?";
			$param[] = $ngaytao_bd;
		}
		//Check ngày tạo kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaytao_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbdi.NGAYBANHANH <= ?";
			$param[] = $ngaytao_kt;
		}
		
		// check so di
		if($parameter['SODI']!=""){
			$sodi_in = (int)$parameter['SODI'];
			if((String)$sodi_in != $parameter['SODI']){
				$where .= " and ( vbdi.SODI = ? ) ";
				$param[] = $parameter['SODI'];
			}else{
				$where .= " and (vbdi.SODI = ? or vbdi.SODI_IN = ?) ";
				$param[] = $parameter['SODI'];
				$param[] = $sodi_in;
			}
			
			
		}
		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$sokyhieu_in = (int)$parameter['SOKYHIEU'];
			if($sokyhieu_in >0){
				$where .= " and (vbdi.SOKYHIEU = ? OR vbdi.SOKYHIEU_IN = ?)";
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbdi.SOKYHIEU = ? ";
				$param[] = $parameter['SOKYHIEU'];
				
			}
		}
		
		$auth = Zend_Registry::get('auth');
		$id_u = $auth->getIdentity()->ID_U;
		if($parameter['CHUA_DOC'] == 1)
		{
			
			
			$sql = "
			SELECT distinct 
					vbdi.*
					
				FROM
						".QLVBDHCommon::Table('VBDI_VANBANDI')." vbdi
					inner join ".QLVBDHCommon::Table('VBDI_DONGLUANCHUYEN')." lc on vbdi.ID_VBDI = lc.ID_VBDI 
				where  $where and lc.DA_XEM = 0 and `NGUOINHAN`= ". $id_u ." 
				
				ORDER BY $order
				$strlimit 
			";
			//echo $sql; exit;
			$r = $this->getDefaultAdapter()->query($sql,$param);
			return $r->fetchAll();
			
		}
		
		//if(!hosocongviecModel::isVanthu($parameter['ID_U']))
			//$join .= "INNER JOIN vbdi_dongluanchuyen_$realyear dongluanchuyen on vbdi.ID_VBDI = dongluanchuyen.ID_VBDI";    
		//Check order
		if($id_u>0){
		//check co trong dong luan chuyen hay khong
		$is_see_all = false;
		$is_see_old = false;
		if(hosocongviecModel::isVanthu($id_u))
			$is_see_all = true;
		elseif ($parameter['IS_SEE_ALL'] > 0)
			if(hosocongviecModel::isAlowSeeAllVbDi())
				$is_see_all = true;
		if(hosocongviecModel::isAlowSeeAllVbDiOld()){
			$is_see_old = true;
		}
		if($is_see_all ){
			$join  = "";
		}
		
		if(!$is_see_all)
		{	
			//$join .= "inner JOIN vbd_dongluanchuyen_$realyear dongluanchuyen on vbd.ID_VBD = dongluanchuyen.ID_VBD";	
			$arr_vbdi = hosocongviecModel::selectUser_LCVBDI($id_u);
			$is_check_process = true;
			if(count($arr_vbdi) >0)
			{
				
				$str_vbdi = "(".implode(',',$arr_vbdi).")";
				$where .= "  and vbdi.ID_VBDI in $str_vbdi " ;
				
				//echo $where;
			}else if($is_see_old && QLVBDHCommon::getYear()==2008 ){
			$where .= "  and vbdi.OLD = 1" ;
		}else{
				$where .= " and 1=0"	;
			}
		}
		
		} 
		if($order!=""){
			$order = "ORDER BY $order";
		}
		//Check limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql = "
			SELECT
				distinct vbdi.*
			FROM
				vbdi_vanbandi_$realyear vbdi
			$join
			WHERE
				$where
				$order
				$strlimit 
			
		";
		//echo $sql; exit;

		try{
			//echo $sql;
			$r = $this->getDefaultAdapter()->query($sql,$param);
			$result = $r->fetchAll();
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
	}
// end hieuvt
	static function SendAll($idhscv,$usersend,$wf_nexttransition,$wf_nextg,$wf_name_g,$wf_hanxuly_g,$wf_nextdep,$wf_name_dep,$wf_hanxuly_dep,$wf_nextuser,$wf_name_user,$wf_hanxuly_user,$idfk,$filedfk,$tablefk,$sms,$email){
		//var_dump($sms);exit;
			global $db;
			$first=false;
			$i=0;
			$haveextra = count($wf_nextg)+count($wf_nextdep)+count($wf_nextuser);
        	for($i=0;$i<count($wf_nextg)-(count($wf_nextdep)+count($wf_nextuser)>0?0:1);$i++){
       			$idhscvnew = WFEngine::CopyProcess($idhscv,$wf_name_g[$i],$wf_nextg[$i],WFEngine::$WFTYPE_GROUP);
       			WFEngine::SendNextUserByObjectId2($idhscvnew,$wf_nexttransition,$usersend,$wf_nextg[$i],WFEngine::$WFTYPE_GROUP,$wf_name_g[$i],$wf_hanxuly_g[$i]);
       			WFEngine::UpdateAfterCopy($idhscvnew);
        		// insert van ban den
        		$db->insert(QLVBDHCommon::Table($tablefk),array($filedfk=>$idfk,"ID_HSCV"=>$idhscvnew));
        	}
        	if($i==count($wf_nextg)){
	         	for($i=0;$i<count($wf_nextdep)-(count($wf_nextuser)>0?0:1);$i++){
	        		$idhscvnew = WFEngine::CopyProcess($idhscv,$wf_name_dep[$i],$wf_nextdep[$i],WFEngine::$WFTYPE_DEP);
	        		WFEngine::SendNextUserByObjectId2($idhscvnew,$wf_nexttransition,$usersend,$wf_nextdep[$i],WFEngine::$WFTYPE_DEP,$wf_name_dep[$i],$wf_hanxuly_dep[$i]);
	        		WFEngine::UpdateAfterCopy($idhscvnew);
	         		// insert van ban den
	         		$db->insert(QLVBDHCommon::Table($tablefk),array($filedfk=>$idfk,"ID_HSCV"=>$idhscvnew));
	         	}
         		if($i==count($wf_nextdep)){
		          	for($i=0;$i<count($wf_nextuser)-1;$i++){
		         		$idhscvnew = WFEngine::CopyProcess($idhscv,$wf_name_user[$i],$wf_nextuser[$i],WFEngine::$WFTYPE_USER);
		         		WFEngine::SendNextUserByObjectId2($idhscvnew,$wf_nexttransition,$usersend,$wf_nextuser[$i],WFEngine::$WFTYPE_USER,$wf_name_user[$i],$wf_hanxuly_user[$i],$sms[$i],$email[$i]);
		         		WFEngine::UpdateAfterCopy($idhscvnew);
		          		// insert van ban den
		          		$db->insert(QLVBDHCommon::Table($tablefk),array($filedfk=>$idfk,"ID_HSCV"=>$idhscvnew));
		          	}
		          	WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$usersend,$wf_nextuser[count($wf_nextuser)-1],WFEngine::$WFTYPE_USER,$wf_name_user[count($wf_name_user)-1],$wf_hanxuly_user[count($wf_hanxuly_user)-1],$sms[count($wf_nextuser)-1],$email[count($wf_nextuser)-1]);
		          	if($haveextra>1){
		          		WFEngine::UpdateExtra($idhscv,$wf_nextuser[count($wf_nextuser)-1],WFEngine::$WFTYPE_USER);
		          	}
	         	}else{
	         		WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$usersend,$wf_nextdep[count($wf_nextdep)-1],WFEngine::$WFTYPE_DEP,$wf_name_dep[count($wf_name_dep)-1],$wf_hanxuly_dep[count($wf_hanxuly_dep)-1]);
	         		if($haveextra>1){
	         		WFEngine::UpdateExtra($idhscv,$wf_nextdep[count($wf_nextdep)-1],WFEngine::$WFTYPE_DEP);
	         		}
	         	}
        	}else{
        		WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$usersend,$wf_nextg[count($wf_nextg)-1],WFEngine::$WFTYPE_GROUP,$wf_name_g[count($wf_name_g)-1],$wf_hanxuly_g[count($wf_hanxuly_g)-1]);
        		if($haveextra>1){
        			WFEngine::UpdateExtra($idhscv,$wf_nextg[count($wf_nextg)-1],WFEngine::$WFTYPE_GROUP);
        		}
        	}
	}
	
	static function getDetail($year,$id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select * from `hscv_hosocongviec_$year` where `ID_HSCV`=?";
		try{
		$stm = $dbAdapter->query($sql,array($id));
		return $stm->fetch();
		} catch (Exception $ex){
			return array();
		}
	}

	static function getDetailById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select * from ".QLVBDHCommon::Table('hscv_hosocongviec')."  where `ID_HSCV`=?";
		try{
		$stm = $dbAdapter->query($sql,array($id));
		return $stm->fetch();
		} catch (Exception $ex){
			return array();
		}
	}

	static function deleteOneNoAffectOthers($year,$id,$type){
		//lay loai ho 
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		if($type == 1){ // neu la van ban den
			//xoa thong tin trong ban vb_fk_vbden_hscvs
			$sql_fk = "delete from `vbd_fk_vbden_hscvs_$year` where `ID_HSCV` = ?";
			try{
				$stm = $dbAdapter->prepare($sql_fk);
				$stm->execute(array($id));
				
			}catch(Exception $ex){
				//loi 
				
				return -1;
			}
		}
		try{
			$sql = "delete from `hscv_hosocongviec_$year` where `ID_HSCV` = ? and `ID_LOAIHSCV` =? ";
			$stm = $dbAdapter->prepare($sql,$type);
			$stm->execute(array($id));
		}catch(Exception $ex){
			//xay ra loi
			
			return -1;
		}
		//neu thanh cong tra ve ket qua la 1
		return 1;
	}
	function deletehscvforvtbp($idhscv){
		global $db;
		$currentprocess = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
		//xóa dòng lc wf
		$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PI=".$currentprocess["ID_PI"]);
		//xoa wf
		$db->delete(QLVBDHCommon::Table("WF_PROCESSITEMS"),"ID_PI=".$currentprocess["ID_PI"]);
		//xóa hscv
		$db->delete(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),"ID_HSCV=".$idhscv);
		//xóa fk vbd
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." where id_hscv=?";
		$r = $db->query($sql,$idhscv);
		$vbd = $r->fetch();
		$db->delete(QLVBDHCommon::Table("vbd_fk_vbden_hscvs"),"ID_HSCV=".$idhscv);
		//xoa van ban du va van ban lien quan theo tuong ung voi ho so cong viec
		$vbduthao = new VanBanDuThaoModel(QLVBDHCommon::getYear());
		$vbduthao->deleteByIdHSCV($idhscv,QLVBDHCommon::getYear());
		$vblq = new VanBanLienQuanModel(QLVBDHCommon::getYear());
		$vblq->deleteByHscv($idhscv);
		filedinhkemModel::deleteFileByObject(QLVBDHCommon::getYear(),$idhscv,1);
		
		$r = $db->query("SELECT COUNT(*) as CNT FROM ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." WHERE ID_VBDEN=?",$vbd["ID_VBDEN"]);
		$row = $r->fetch();
		if($row["CNT"]==0){
			$db->delete(QLVBDHCommon::Table("VBD_VANBANDEN"),"ID_VBD=".$vbd['ID_VBDEN']);
			$db->delete(QLVBDHCommon::Table("VBD_DONGLUANCHUYEN"),"ID_VBD=".$vbd['ID_VBDEN']);
			filedinhkemModel::deleteFileByObject(QLVBDHCommon::getYear(),$vbd['ID_VBDEN'],3);
		}
	}
	function deletehscv($idhscv,$idu,$type){
		global $db;
		//lấy id wf
		$currentprocess = WFEngine::GetCurrentTransitionInfoByIdHscv($idhscv);
		//check quyền xóa
		$id_object = 0;
		$type_dk = 0;
		if($currentprocess["ID_U_NC"]==$idu){
			//xóa dòng lc wf
			$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PI=".$currentprocess["ID_PI"]);
			//xoa wf
			$db->delete(QLVBDHCommon::Table("WF_PROCESSITEMS"),"ID_PI=".$currentprocess["ID_PI"]);
			//xóa hscv
			$db->delete(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),"ID_HSCV=".$idhscv);
			//xoa vbden, motcua, soanthaovb
			if($type==1){
				$sql = "SELECT * FROM ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." where id_hscv=?";
				$r = $db->query($sql,$idhscv);
				$vbd = $r->fetch();
				$db->delete(QLVBDHCommon::Table("VBD_VANBANDEN"),"ID_VBD=".$vbd['ID_VBDEN']);
				$db->delete(QLVBDHCommon::Table("VBD_DONGLUANCHUYEN"),"ID_VBD=".$vbd['ID_VBDEN']);
				//lay id cua van ban den
				//$vbden_model = new vbdenModel($year);
				//$vbden_detail = $vbden_model->findByHscv($idhscv);
				$id_object = $vbd['ID_VBDEN'];
				$type_dk = 3;
			}else if($type==2){
				$db->delete(QLVBDHCommon::Table("HSCV_CONGVIECSOANTHAO"),"ID_HSCV=".$idhscv);
				$id_object = $idhscv;
				$type_dk = 1;
			}else if($type>=3){
				$sql = "SELECT * FROM ".QLVBDHCommon::Table("MOTCUA_HOSO")." where id_hscv=?";
				$r = $db->query($sql,$idhscv);
				$motcua = $r->fetch();
				$db->delete(QLVBDHCommon::Table("MOTCUA_HOSO"),"ID_HSCV=".$idhscv);
				$db->delete(QLVBDHCommon::Table("MOTCUA_NHAN_GOM"),"ID_HOSO=".$motcua['ID_HOSO']);
				$id_object = $idhscv;
				$type_dk = 1;
			}
			//xoa file dinh kem
			$year = QLVBDHCommon::getYear();
			if($id_object>0){
				
				$year = QLVBDHCommon::getYear();
				filedinhkemModel::deleteFileByObject($year,$id_object,$type_dk);
			}
			//xoa van ban du va van ban lien quan theo tuong ung voi ho so cong viec
			$vbduthao = new VanBanDuThaoModel($year);
			$vbduthao->deleteByIdHSCV($idhscv,$year);
			$vblq = new VanBanLienQuanModel($year);
			$vblq->deleteByHscv($idhscv);
		}
	}

	function getTranspollCodeByIdTrans($id_t){
		$sql = "
			select * from wf_transitions trs
			inner join wf_transitionpools trpools on trs.ID_TP = trpools.ID_TP
			where trs.ID_T = ? 
		";
		
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$stm = $dbAdapter->query($sql,array($id_t));
			$re = $stm->fetch();
			return $re["ALIAS"];
		}catch(Exception $ex){
			return "";
		}
	}
	function isfinishhosocv($idhscv) {
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql=" select wf_pro.IS_FINISH from hscv_hosocongviec_".QLVBDHCommon::getYear()."  hscv inner join wf_processitems_".QLVBDHCommon::getYear()." wf_pro on  hscv.ID_PI = wf_pro.ID_PI where hscv.ID_HSCV =?";
		try{ 
          $stm = $dbAdapter->query($sql,array($idhscv));
		  $re = $stm->fetch();
		  if($re["IS_FINISH"] == 1)
			 
			  return 1;
		else 
			return 0;
			
		}catch (Exception $ex){
		    return 0;
		}

	}
	
	static function SelectAllChoxuly($parameter,$offset,$limit,$order){
		global $db;
		$param = Array();
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		$sql = "
			SELECT hscv.*,luu.ID_LUUCXL, class1.ALIAS FROM 
			".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv 
			inner join ".QLVBDHCommon::Table("wf_processitems")." wfitem on hscv.ID_PI = wfitem.ID_PI
			inner join ".QLVBDHCommon::Table("HSCV_HOSOLUUCHOXULY")." luu on hscv.ID_HSCV = luu.ID_HSCV
			inner join HSCV_LOAIHOSOCONGVIEC lhs on lhs.ID_LOAIHSCV = hscv.ID_LOAIHSCV
				inner join WF_PROCESSES wfp1 on wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class1 on class1.ID_C = wfp1.ID_C
			where
				hscv.IS_CHOXULY = 1
				and luu.ID_U = ?
			ORDER BY DATE_CREATE DESC
			$strlimit
		";
		$param[] = $parameter['ID_U'];
		try{
			
			$r = $db->query($sql,$param);
			$result = $r->fetchAll();
			
		}catch(Exception $ex){
			echo $ex->__toString();;
			return null;
		}
		return $result;
	}
	
	static function luuChoxuly($id_hscv,$comment,$id_u){
		//var_dump($comment); exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//cap nhat co luu theo hoi cua ho so cong viec
		//kiem tra ho so cong viec co ton tai hay khong
		$sql = "
		select count(*) as DEM  from ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv where ID_HSCV=?
		";
		
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetch();
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		$c_hscv = $re["DEM"];
		
		
		if($c_hscv == 0){
			//ho so cong viec khong ton tai
			return -1;
		}
		
		$sql = "
		select IS_CHOXULY  from ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv where ID_HSCV=?
		";
		
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_hscv));
			$re = $query->fetch();
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		
		
		$is_theodoi = $re["IS_CHOXULY"];
		
		//if($is_theodoi == 1){
			//ho so cong viec da o trang thai dang theo doi
			//return -2;
		//}
		
		$sql ="
			Update ".QLVBDHCommon::Table("hscv_hosocongviec")." set `IS_CHOXULY`=1 where ID_HSCV=?
		";
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_hscv));
			$vbd = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbd->findByHscv($id_hscv);
			if($vbden['ID_VBDEN']>0){
				$dbAdapter->update("VBD_VANBANDEN_".QLVBDHCommon::getYear(),array("TRE"=>QLVBDHCommon::getTreHan($vbden['NGAYTAO'],$vbden['HANXULYTOANBO']),"IS_FINISH"=>1),"ID_VBD=".$vbden['ID_VBDEN']);
			}

		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
		
		//them moi vao bang ho so luu theo doi
		
		//check ton tai
		
		$sql="
			SELECT * FROM ".QLVBDHCommon::Table("hscv_hosoluuchoxuly")." WHERE ID_HSCV=?
		";
		$re = $dbAdapter->query($sql,$id_hscv);
		$luutd = $re->fetch();
		//Lay dong luan chuyen hien tai
		$data_wl = WFEngine::GetCurrentTransitionInfoByIdHscv($id_hscv);
		
		$arrdata_cxl = array();
		if($luutd['ID_HSCV']>0){
			$sql="
				UPDATE ".QLVBDHCommon::Table("hscv_hosoluuchoxuly")." SET `COMMENT`=? 
				
				where `ID_HSCV` = ".$luutd['ID_HSCV']."
			";
			$arrdata_cxl = array($comment);
		}else{
			$sql="
				Insert into ".QLVBDHCommon::Table("hscv_hosoluuchoxuly")." (`ID_U`,`ID_HSCV`,`COMMENT`,`DATE_CREATE`,ID_PL_CUR,HANXULY) values (?,?,?,'".date("Y-m-d H:i:s")."',?,?) 
			";
			$arrdata_cxl = array($id_u,$id_hscv,$comment,$data_wl["ID_PL"],$data_wl["HANXULY"]);
		}
		
		

		try{
			if($data_wl["ID_PL"]){
				
				$stm = $dbAdapter->prepare($sql);
				$stm->execute($arrdata_cxl);
				/*
					Cập nhật lại wf_poccess log
				*/
				$sql = "  update ". QLVBDHCommon::Table("wf_processlogs"). " 
						  set
							  HANXULY = 0
						  where ID_PL = ?
				
				";
				$stm = $dbAdapter->prepare($sql);
				$stm->execute(array($data_wl["ID_PL"]));

				
				return $dbAdapter->lastInsertId(QLVBDHCommon::Table("hscv_hosoluuchoxuly"),'ID_LUUCXL');
			}
		}catch(Exception $ex){
			return 0;
		}
	}

	function getLuuxulyInfoByHSCVId($id_hscv){
		
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql="
			SELECT * FROM ".QLVBDHCommon::Table("hscv_hosoluuchoxuly")." WHERE ID_HSCV=?
			";
			$re = $dbAdapter->query($sql,$id_hscv);
			
			return $re->fetch();
		}catch(Exception $ex){
			return array();
		}
	}
	
	static function phuchoiluuChoxuly($id_luucxl,$idu){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//lay thong tin ve ho so luu theo doi
		$sql ="
			select hs.* from ".QLVBDHCommon::Table("HSCV_HOSOLUUCHOXULY")." hs where ID_LUUCXL=? and ID_U=?
		";
		$re = array();
		try{
			$query = $dbAdapter->query($sql,array($id_luucxl,$idu));
			$re = $query->fetch();
			
		}catch (Exception $ex){
			//loi sql 
			return -3;
		}
		if(count($re) == 0){
			//khong tim thay ho so luu theo doi
			return -1;
		}
		if(!($re["ID_HSCV"] > 0)){
			//khong tim thay ho so cong viec tuong ung
			return -2;
		}
		
		try{
			$dbAdapter->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("IS_CHOXULY"=>0),"ID_HSCV=".$re["ID_HSCV"]);
			
		}catch(Exception $ex){
			//echo $ex;
			return -3;
		}
		
		//xoa ho so luu theo doi trong danh sach
		try{
			
			if($re["ID_PL_CUR"]){
				$sql = "  update ". QLVBDHCommon::Table("wf_processlogs"). " 
							  set
								  HANXULY = ?
							  where ID_PL = ?
					
				";
				//Tinh han xu ly moi
				$ngay = strtotime($re["DATE_CREATE"]);
				$freedate = new Zend_Session_Namespace('freedate');
				$free = $freedate->free;
				//var_dump($free); exit;
				$delay = QLVBDHCommon::countdate($ngay,$free);
				//echo $delay; exit;
				$hxl_moi =  ($delay/8) + $re["HANXULY"];  
				$stm = $dbAdapter->prepare($sql);
				$stm->execute(array($hxl_moi,$re["ID_PL_CUR"]));
			}
			$dbAdapter->delete(QLVBDHCommon::Table("HSCV_HOSOLUUCHOXULY"),"ID_LUUCXL=".$re["ID_LUUCXL"]);
		}catch(Exception $ex){
			//loi co so du lieu
			return -3;
		}
	}
}
