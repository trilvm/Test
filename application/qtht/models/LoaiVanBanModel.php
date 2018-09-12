<?php

require_once 'Zend/Db/Table.php';
class LoaiVanBanModel extends Zend_Db_Table {
	
	var $_name = "vb_loaivanban";
	function findByMixed($page,$limit,$name,$active){
		$name='%'.$name.'%';
		$arr = array($name,$active);
		$wherename = 'NAME LIKE ?';
		$whereactive = 'ACTIVE = ?';
		$select = $this->select();
		$select->where($wherename,$name);
		if($page !=0 && $limit !=0){
			$select->order('NAME')->limitPage($page,$limit);	
		}
		if($active>=2||$active<0||is_null($active)){
			
		}else{
			$select->where($whereactive,$active)->order('NAME');
		}
		
		return $this->fetchAll($select);
	}
	
	function findByName($name){
		$name='%'.$name.'%';
		$where = 'NAME LIKE ?';
		return $this->fetchAll($this->select()->where($where,array($name)));
	
	}
	function checkmaso($maso,$loaitru){
		$check = Zend_Db_Table::getDefaultAdapter()->query("
                        Select CODE 
                        FROM vb_loaivanban 
                        WHERE CODE='".$maso."'
                        AND ID_LVB not in ('".$loaitru."')")->fetchAll();	                        
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
	/**
     * Get all items in VB_LOAIVANBAN table with pairs : ID_LVB and NAME
     * @return array
     */
	function GetAllLoaiVanBans()
	{
        $rs = array();
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			CODE
			FROM
				vb_loaivanban");
		while ($row = $r->fetch()) {
            $rs[] = $row['CODE'];
        }
        return $rs;
	}
	static function toComboName(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `CODE`,`NAME`,`ID_LVB` from `vb_loaivanban` ORDER BY NAME";
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		foreach ($re as $row){
			echo "<option id='".$row['CODE']."' value=".$row['ID_LVB'].">".$row['NAME']."</option>";
		}
	}
	
	static function getNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "select `NAME` from `vb_loaivanban` 
		where `ID_LVB` = ? ORDER BY NAME
		";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetch();
		return $re["NAME"];
	}
	
	static function getArrNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$where = "";
		$name = "";
		$sql = "select `NAME` from `vb_loaivanban`";
		if($id > 0)
		{
			$where .= " where `ID_LVB` = ?";
		}
		$sql = $sql.$where." ORDER BY NAME";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetchAll();
		foreach($re as $names)
		{
			$name .= $names['NAME'].',';
		}
		$namestr = substr($name, 0, -1);
		return $namestr;
	} 
	static function getArrNameSVBById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$where = "";
		$name = "";
		$sql = "select `NAME` from `vb_sovanban`";
		if($id > 0)
		{
			$where .= " where `ID_SVB` = ?";
		}
		$sql = $sql.$where." ORDER BY NAME";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetchAll();
		foreach($re as $names)
		{
			$name .= $names['NAME'].',';
		}
		$namestr = substr($name, 0, -1);
		return $namestr;
	}
	static function getCQNameById($id){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$where = "";
		$name = "";
		$sql = "select `NAME` from `vb_coquan`";
		if($id > 0)
		{
			$where .= " where `ID_CQ` = ?";
		}
		$sql = $sql.$where." ORDER BY NAME";
		$query = $dbAdapter->query($sql,array($id));
		$re = $query->fetchAll();
		foreach($re as $names)
		{
			$name .= $names['NAME'].',';
		}
		$namestr = substr($name, 0, -1);
		return $namestr;
	} 
}


