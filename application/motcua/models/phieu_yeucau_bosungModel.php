<?php
/**
 * @author trunglv
 * @version 1.0
 * Model : Phieu yeu cau bo sung cho ho so mot cua
 */
require_once ('Zend/Db/Table/Abstract.php');
/**
 * Cau truc du lieu the hien thong tin ve phieu yeu cau
 *
 */
class phieu_yeucau_bosung{
	var $_id_yeucau;
	var $_id_hscv;
	var $_sophieu;
	var $_ngay_yeucau;
	var $_ngay_bosung;
	var $_cacghichu;
	var $_nguoiyeucau;
	var $_nguoibosung;
}
/**
 * Lop model cho yeu yeu cau bo sung
 *
 */
class phieu_yeucau_bosungModel extends Zend_Db_Table_Abstract {
	/**
	 * ham contructor tao lop model them nam
	 * @param integer $year : 
	 */
	function __construct($year){
		$this->_name ='hscv_phieu_yeucau_bosung'.'_'.$year;
		$arr = array();
		parent::__construct($arr);
	}
	/**
	 * Them moi mot phieu yeu cau bo sung
	 *
	 * @param phieu_yeu_cau_bosung $phieu_yc_bs
	 */
	function inserOne($phieu_yc_bs){
		$data = array(
		'ID_HSCV'=>$phieu_yc_bs->_id_hscv,
		'SOPHIEU'=>$phieu_yc_bs->_sophieu,
		'NGAY_YEUCAU'=>$phieu_yc_bs->_ngay_yeucau,
		'NGAY_BOSUNG'=>$phieu_yc_bs->_ngay_bosung,
		'CACGHICHU'=>$phieu_yc_bs->_cacghichu,
		'NGUOIYEUCAU'=>$phieu_yc_bs->_nguoiyeucau,
		'NGUOIBOSUNG'=>$phieu_yc_bs->_nguoibosung
		);
		$this->insert($data);
	}
	/**
	 * Lay phieu yeu cau bo sung moi nhat cua ho so mot cua
	 *
	 * @param unknown_type $idHSCV
	 * @return unknown
	 */
	function getNearPhieuYeuCauByIdHSCV($idHSCV){
		$se = $this->select()->where('ID_HSCV=?',$idHSCV)
							->order('NGAY_YEUCAU DESC')	
							->order('ID_YEUCAU DESC');
		$phieu_yc = new phieu_yeucau_bosung();
		$re = $this->fetchRow($se);
		if($re !=false)
		{
			$phieu_yc->_id_yeucau = $re->ID_YEUCAU;
			$phieu_yc->_ngay_yeucau = $re->NGAY_YEUCAU;
			$phieu_yc->_sophieu = $re->SOPHIEU;
			$phieu_yc->_cacghichu = $re->CACGHICHU; 	
		}
		return $phieu_yc;
	}
	/**
	 * cap nhat phieu bo sung khi co thu hien hanh dong bo sung(wf)
	 *
	 * @param unknown_type $phieu_yc
	 */
	function updateWhenBoSung($phieu_yc)
	{
		$where = 'ID_YEUCAU='.$phieu_yc->_id_yeucau;
		$data = array(
			'NGAY_BOSUNG'=>$phieu_yc->_ngay_bosung,
			'CACGHICHU' => $phieu_yc->_cacghichu,
			'NGUOIBOSUNG' => $phieu_yc->_nguoibosung
		);
		$this->update($data,$where);
	}
	function getLastInsertId(){
		$sql = " select max(ID_YEUCAU) as LAST_ID from   " . QLVBDHCommon::Table("hscv_phieu_yeucau_bosung") ."";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql);
			$re =  $qr->fetch();
			return $re["LAST_ID"];
		}catch(Exception $ex){
			return 0;
		}
	}
}

?>
