<?php

require_once 'Zend/Db/Table.php';
class BatBuocXuLyModel extends Zend_Db_Table {
	
	var $_name = "gscv_dmbatbuoc";
	function findByMixed($page,$limit,$name,$active){
		$name='%'.$name.'%';
		$arr = array($name,$active);
		$wherename = 'LIKE_NAME LIKE ?';
		$whereactive = 'ACTIVE = ?';
		$select = $this->select();
		$select->where($wherename,$name);
		if($page !=0 && $limit !=0){
			$select->order('LIKE_NAME')->limitPage($page,$limit);	
		}
		if($active>=2||$active<0||is_null($active)){
			
		}else{
			$select->where($whereactive,$active)->order('LIKE_NAME');
		}
		
		return $this->fetchAll($select);
	}
	
	function findByName($name){
		$name='%'.$name.'%';
		$where = 'LIKE_NAME LIKE ?';
		return $this->fetchAll($this->select()->where($where,array($name)));
	
	}
	function checkmaso($maso,$loaitru){
		$check = Zend_Db_Table::getDefaultAdapter()->query("
                        Select CODE 
                        FROM gscv_dmbatbuoc 
                        WHERE CODE='".$maso."'
                        AND ID_DMNB not in ('".$loaitru."')")->fetchAll();	                        
            if(count($check)>0) {
                return true;                                    
                }
            else {return false;}
	
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
		$wherename = 'LIKE_NAME LIKE ?';
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
	/**
     * Get all items in VB_LOAIVANBAN table with pairs : ID_BB and NAME
     * @return array
     */
	function GetAllBatBuocXuLys()
	{
        $rs = array();
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			ID_BB
			FROM
				gscv_dmbatbuoc");
		while ($row = $r->fetch()) {
            $rs[] = $row['ID_BB'];
        }
        return $rs;
	}
	static function toComboName(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `LIKE_NAME`,`ID_BB` from `gscv_dmbatbuoc` ORDER BY LIKE_NAME";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['ID_BB']."' value=".$row['ID_BB'].">".$row['LIKE_NAME']."</option>";
		}
	}
	
	static function getLoaiBatBuoc(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `LIKE_NAME`,`ID_BB` from `gscv_dmbatbuoc` ORDER BY LIKE_NAME";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}

	
	static function getNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `LIKE_NAME` from `gscv_dmbatbuoc` 
		where `ID_BB` = ? ORDER BY LIKE_NAME
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetch();
		return $re["LIKE_NAME"];
	}
	
	static function getArrNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$where = "";
		$name = "";
		$sql = "select `LIKE_NAME` from `gscv_dmbatbuoc`";
		if($id > 0)
		{
			$where .= " where `ID_BB` = ?";
		}
		$sql = $sql.$where." ORDER BY LIKE_NAME";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetchAll();
		foreach($re as $names)
		{
			$name .= $names['LIKE_NAME'].',';
		}
		$namestr = substr($name, 0, -1);
		return $namestr;
	} 
}


