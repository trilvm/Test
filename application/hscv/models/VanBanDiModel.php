<?php

/**
 * VanBanDi
 *  
 * @author nguyennd
 * @version 1.0
 * @deprecated add 14/10/2009 
 */

require_once 'Zend/Db/Table/Abstract.php';



class VanBanDiModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'vbdi_VanBanDi_2013';
	/**
	 * ??m b?n ghi c�c h? s? c�ng vi?c d?a tr�n tham s? ??u v�o.
	 * Danh s�ch tham s? ??u v�o
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
		//Check th? m?c
		if($parameter['ID_THUMUC']>0){
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = $parameter['ID_THUMUC'];
		}
		//Check loai hscv
		if($parameter['ID_LOAIHSCV']>0){
			$where .= " and hscv.ID_LOAIHSCV = ?";
			$param[] = $parameter['ID_LOAIHSCV'];
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
		//Check process
		if($parameter['idp']>0){
			$where .= " and wfitem.ID_P = ?";
			$param[] = $parameter['idp'];
		}
		//Check activity
		if($parameter['TRANGTHAI']>0){
			$where .= " and wfitem.ID_A = ?";
			$param[] = $parameter['TRANGTHAI'];
		}
		//Check user
		if($parameter['ID_U']>0){
			$where .= " and wfitem.ID_U = ?";
			$param[] = $parameter['ID_U'];
		}
		//Check name
		if($parameter['NAME']!=""){
			$where .= " and match(hscv.NAME) against (? IN BOOLEAN MODE)";
			$param[] = $parameter['NAME'];
		}
	
		//L?y t�n table d?a tr�n ng�y b?t ??u
		$arrngaybd = explode("-",$parameter['NGAY_BD']);
		$realyear = $arrngaybd[0];
		if($realyear<=0){
			$d = getdate();
			$realyear = $d['year'];
		}
		$tablename = 'vbdi_VanBanDi_'.$realyear;
		$tablewfitem = 'wf_processitems_'.$realyear;
		
		$sql = "
			SELECT
				count(*) as CNT
			FROM
				$tablename hscv
				inner join $tablewfitem wfitem on hscv.ID_PI = wfitem.ID_PI
			WHERE
				$where
		";
		try{
		$r = $this->getDefaultAdapter()->query($sql,
		$param);
		$result = $r->fetchAll();
		}catch(Exception $ex){
			return 0;
		}
		return $result[0]['CNT'];
	}
	/**
	 * L?y danh s�ch c�c h? s? c�ng vi?c d?a tr�n tham s? ??u v�o.
	 * Danh s�ch tham s? ??u v�o
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
		//Check th? m?c
		if($parameter['ID_THUMUC']>0){
			$where .= " and hscv.ID_THUMUC = ?";
			$param[] = $parameter['ID_THUMUC'];
		}
		//Check loai hscv
		if($parameter['ID_LOAIHSCV']>0){
			$where .= " and hscv.ID_LOAIHSCV = ?";
			$param[] = $parameter['ID_LOAIHSCV'];
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
		if($parameter['ID_U']>0){
			$where .= " and wfitem.ID_U = ?";
			$param[] = $parameter['ID_U'];
		}
		//Check name
		if($parameter['NAME']!=""){
			$where .= " and match(hscv.NAME) against (? IN BOOLEAN MODE)";
			$param[] = $parameter['NAME'];
		}
		
		//Check order
		if($order!=""){
			$order = "ORDER BY $order";
		}
		
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Check limit
		
		//L?y t�n table d?a tr�n ng�y b?t ??u
		$arrngaybd = explode("-",$parameter['NGAY_BD']);
		$realyear = $arrngaybd[0];
		if($realyear<=0){
			$d = getdate();
			$realyear = $d['year'];
		}
		$tablename = 'vbdi_VanBanDi_'.$realyear;
		$tablewfitem = 'wf_processitems_'.$realyear;
		
		$sql = "
			SELECT
				hscv.*
			FROM
				$tablename hscv
				inner join $tablewfitem wfitem on hscv.ID_PI = wfitem.ID_PI
			WHERE
				$where
				$order
				$strlimit
		";
		try{
			$r = $this->getDefaultAdapter()->query($sql,
			$param);
			$result = $r->fetchAll();
		}catch(Exception $ex){
			return null;
		}
		return $result;
	}
}
