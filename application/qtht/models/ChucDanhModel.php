<?php

require_once 'Zend/Db/Table/Abstract.php';
class ChucDanhModel extends Zend_Db_Table_Abstract {
	/**
	 * thuoc tinh
	 */
	var $_name = "qtht_chucdanh";
	
	
	function findByMixed($page,$limit,$name,$active){
		$name='%'.$name.'%';
		$arr = array($name,$active);
		$wherename = 'NAME LIKE ?';
		$whereactive = 'ACTIVE = ?';
		$select = $this->select();
		$select->where($wherename,$name);
		if($page !=0 && $limit !=0){
			$select->limitPage($page,$limit);	
		}
		if($active>=2||$active<0||is_null($active)){
			
		}else{
			$select->where($whereactive,$active);
		}
		
		return $this->fetchAll($select);
	}
	
	function findByName($name){
		$name='%'.$name.'%';
		$where = 'NAME LIKE ?';
		return $this->fetchAll($this->select()->where($where,array($name)));
	
	}
	
	function findByActive($active){
		if((!$active)||$active>=2||$active<0){
			return $this->fetchAll();
		}
		$where = 'ACTIVE=?';
		return $this->fetchAll($this->select()->where($where,array($active)));
	}
	
	static function  toComboFilter($filter_object){
		
		if($filter_object>=2||$filter_object<0||is_null($filter_object) )
			echo '<option value=2  selected>Chọn tất cả</option>';
		else 
			echo '<option value=2 >Chọn tất cả</option>';
		
		if($filter_object==1)
			echo '<option value=1 selected>Đang được sử dụng</option>';
		else 
			echo '<option value=1>Đang được sử dụng</option>';
		
		if($filter_object==0 && !is_null($filter_object))
			echo '<option value=0 selected>Chưa được sử dụng</option>';
		else
			echo '<option value=0>Chưa được sử dụng</option>';
	}
	
	function count($name,$active){
		
		$name='%'.$name.'%';
		$arr = array($name,$active);
		$wherename = 'NAME LIKE ?';
		$whereactive = 'ACTIVE = ?';
		$select = $this->select(); 
        $select->from($this->_name,'COUNT(*) AS num'); 
		$select->where($wherename,$name);
        if($active>=2||$active<0||is_null($active)){
			
		}
		else{
			$select->where($whereactive,$active);
		}
        $row=$this->fetchRow($select); 
        return $row->num; 
	}
	  
}

?>
