<?php

require_once ('Zend/Db/Table/Abstract.php');
/**
 * Lop chua thong tin ve mot item trong chuoi ma so
 * kieu : 1 nam, 2 ma co quan noi bo, 3 ma tinh thanh noi bo, 4 so co dinh, 5 so khong co dinh , 6chuoi co dinh, 7 chuoi khong co dinh  
 */
class gen_maso {
	var $_id_maso;
	var $_loai ; //1,2,3 fit
	var $_length;
	var $_order;//thu tu
	var $_field;//ten truong
	var $_table; // ten ban vbd_vanbanden , vdi_vanbandi , motcua_hoso
	var $_kieu; //
	var $_value;
	var $_php_func;
	var $_is_php_func;
	var $_ten;
}
class gen_masoModel extends Zend_Db_Table_Abstract {
	var $_name = 'gen_maso';
	
	function getListAll(){
		return $this->fetchAll();
	}
	
	function getDetail(){
		//In ra cac mang chua cac dong tuong ung voi moi loai` 
		$arrRowByLoai = new ArrayObject();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "Select * 
		from `gen_maso` ms
		where ms.`LOAI` = ?
		order by ms.`ORDER`  ASC
		";
		for($i = 1 ; $i<=4 ; $i++ ){//fit 4 loai
			$qr = $dbAdapter->query($sql,array($i));
			$re = $qr->fetchAll();
			$arrRowByLoai->append($re);
		}
		
		return $arrRowByLoai;
	}
	
	
	
	function insertOne(gen_maso  $o_maso){
		
		$this->insert(array(
			'LOAI' => $o_maso->_loai,
			'TEN' =>$o_maso->_ten,
			'KIEU'=>$o_maso->_kieu,
			'LENGTH'=> $o_maso->_length,
			'ORDER'=> $o_maso->_order,
			'FIELD' => $o_maso->_field,
			'TABLE' => $o_maso->_table,
			'VALUE' => $o_maso->_value,
			'PHP_FUNC'=>$o_maso->_php_func,
			'IS_PHP_FUNC'=>$o_maso->_is_php_func
		));
	}
	
	function updateOne( gen_maso $o_maso){
		$data = array(
			'LOAI' => $o_maso->_loai,
			'TEN' =>$o_maso->_ten,
			'KIEU'=>$o_maso->_kieu,
			'LENGTH'=> $o_maso->_length,
			'ORDER'=> $o_maso->_order,
			'FIELD' => $o_maso->_field,
			'TABLE' => $o_maso->_table,
			'VALUE' => $o_maso->_value,
			'PHP_FUNC'=>$o_maso->_php_func,
			'IS_PHP_FUNC'=>$o_maso->_is_php_func
		);
		$where = "ID_MASO=".$o_maso->_id_maso ;
		$this->update($data,$where);
	}
	
	function countLoai($loai){
		$arr = array($loai);
		$whereloai = 'LOAI = ?';
		$select = $this->select(); 
        $select->from($this->_name,'COUNT(*) AS num'); 
		$select->where($whereloai,$loai);
		$row=$this->fetchRow($select); 
        return $row->num; 
	}
}

?>
