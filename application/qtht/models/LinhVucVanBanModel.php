<?php
/**
 * @name Danh muc linh vuc van ban model
 * @author trunglv
 * @package qtht
 * @version 1.0
 */
require_once 'Zend/Db/Table/Abstract.php';
class LinhVucVanBanModel extends Zend_Db_Table_Abstract {

	var $_name = "vb_linhvucvanban";
	
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
	  
	
	
	static function toComboFilter($filter_object){
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
	
	static function getNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME` from `vb_linhvucvanban` 
		where `ID_LVVB` = ?
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetch();
		return $re["NAME"];
	}

   static function getidulinhvuc($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select u.*,emp.*,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPU from 
		                     `qtht_users` u 							  
		                      inner join `qtht_employees` emp on u.ID_EMP = emp.ID_EMP
		where u.ID_U = ?
		";
		$query = $dbAdapter->query($sql,array($id_u));
		$re = $query->fetch();
		return $re['EMPU'];
	 }
 }
?>
