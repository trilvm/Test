<?php

/**
 * nguoidungModel
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class nguoidungModel extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'qtht_users';
	var $_search = "";
	var $_sel_dep = 0;
        var $_group = 0;
	/**
	 * Select toàn bộ dữ liệu
	 */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and LASTNAME like ?";
		}
		
                if($this->_group > 0 ){
			$arrwhere[] = $this->_group;
			$strwhere .= " and g.ID_G = ?";
		}
                
		if($this->_sel_dep > 0 ){
			$arrwhere[] = $this->_sel_dep;
			$strwhere .= " and dep.ID_DEP = ?";
		}
                
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order!=""){
			$strorder = " ORDER BY $order";
		}
		//Thực hiện query
                $sql = "SELECT
				u.*,emp.FIRSTNAME,emp.LASTNAME,emp.PHONE,emp.EMAIL,dep.NAME as DEPNAME , group_concat(g.NAME,'\n') as GNAME
			FROM
				$this->_name u
				left join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
				left join fk_users_groups fk_u_g on u.ID_U = fk_u_g.ID_U
				left join QTHT_GROUPS g on fk_u_g.ID_G = g.ID_G
			WHERE
				$strwhere
			GROUP BY ID_U 
			$strorder
			$strlimit
                        ";
//                var_dump($arrwhere);
//                var_dump($sql);exit;
                $result = $this->getDefaultAdapter()->query($sql, $arrwhere);
//		$result = $this->getDefaultAdapter()->query("
//			SELECT
//				u.*,emp.FIRSTNAME,emp.LASTNAME,emp.PHONE,emp.EMAIL,dep.NAME as DEPNAME , group_concat(g.NAME,'\n') as GNAME
//			FROM
//				$this->_name u
//				left join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
//				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
//				left join fk_users_groups fk_u_g on u.ID_U = fk_u_g.ID_U
//				left join QTHT_GROUPS g on fk_u_g.ID_G = g.ID_G
//			WHERE
//				$strwhere
//			GROUP BY ID_U 
//			$strorder
//			$strlimit
//                        ",$arrwhere);
		return $result->fetchAll();
	}
        
	public function FindById($id_u){
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				u.*,emp.FIRSTNAME,emp.PHONE,emp.EMAIL,emp.LASTNAME,dep.NAME as DEPNAME,dep.ID_DEP,emp.ID_EMP,emp.ADDRESS,emp.PHONE_EXT,emp.ID_CD
			FROM
				$this->_name u
				inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
				inner join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			WHERE
				u.ID_U = ?
		",$id_u);
		return $result->fetch();
	}
  public function nguoidunglinhvuc(){
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				u.ID_U,dep.NAME as DEPNAME,dep.ID_DEP as DEPID,emp.ID_EMP,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as EMPU,vb_lvvb.ID_LVVB as ID_LVVB
			FROM
				$this->_name u
				inner join vb_linhvucvanban vb_lvvb on vb_lvvb.ID_U = u.ID_U
				inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
				inner join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			
		     ");
		return $result->fetchAll();
	}


	/**
	 * Chuyển dữ liệu tới combobox
	 */
	public function Count(){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and LASTNAME like ?";
		}
		if($this->_sel_dep > 0 ){
			$arrwhere[] = $this->_sel_dep;
			$strwhere .= " and ID_DEP = ?";
		}
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				$this->_name u
				inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
	 * Lấy phòng ban
	 */
	function GetDeparment(){
		$tree = array();
		QLVBDHCommon::GetTree(&$tree,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 and ID_DEP_PARENT",1,1);
		return $tree;
	}
        function layDsPhong()
        {
            $result = $this->getDefaultAdapter()->query("
			SELECT
				ID_DEP,NAME
			FROM
				qtht_departments
                                WHERE ACTIVE=1")->fetchAll();
		return $result;
                
        }
	static function ToDepCombo($data,$sel){
		$html="";
		foreach($data as $row){
			$html .= "<option value='".$row["ID_DEP"]."' ".($row["ID_DEP"]==$sel?"selected":"").">".(str_repeat("--",$row['LEVEL']).
$row["NAME"])."</option>";
			
		}
		return $html;
	}
         public function ChucDanhToCombo(){
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT ID_CD,NAME 
                        FROM qtht_chucdanh
                        WHERE ACTIVE=1
			
		     ");
		return $result->fetchAll();
	}
        
        public function ToDepComboNhomNguoidung(){

                $result = $this->getDefaultAdapter()->query("
			SELECT ID_G,NAME 
                        FROM qtht_groups
                        WHERE ACTIVE=1
			
		     ");
		return $result->fetchAll();
	}
   
   
        
    public function FindAllById($id_u){ 
           $db = Zend_Db_Table::getDefaultAdapter();
		//Thực hiện query
           $sql="
               SELECT
				u.*,emp.FIRSTNAME,emp.PHONE,emp.EMAIL,emp.LASTNAME,dep.NAME as DEPNAME,dep.ID_DEP,emp.ID_EMP,emp.ADDRESS,emp.PHONE_EXT,emp.ID_CD
			FROM
				$this->_name u
				inner join QTHT_EMPLOYEES emp on emp.ID_EMP = u.ID_EMP
				inner join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
			WHERE   u.ID_U IN 
				 (".implode(',',$id_u).") "; 
           $r = $db->query($sql);  
           return $r->fetchAll();
	}    
        
        public function getNguoiDung()
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT u.ID_U, emp.ID_EMP, group_concat(emp.FIRSTNAME, ' ', emp.LASTNAME) AS NGUOIDUNG
                    FROM $this->_name u
                    INNER JOIN QTHT_EMPLOYEES emp ON emp.ID_EMP = u.ID_EMP
                    GROUP BY emp.ID_EMP";
            $r = $db->query($sql);
            return $r->fetchAll();
        }
        
        public function getMultiNguoiDung($id)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = "SELECT ID_D FROM fk_user_dep WHERE ID_U = ?";
            $r = $db->query($sql, $id);
            return $r->fetchAll();
        }
}
