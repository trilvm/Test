<?php

/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');
require_once ('DepartmentsModel.php');
class GroupsModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'qtht_groups';
    var $_search = "";
    /**
     * Select toàn bộ dữ liệu
     */
    public function SelectAll($offset,$limit,$order){
        //Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and NAME like ?";
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
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *
            FROM
                $this->_name
            WHERE
                $strwhere
            $strorder
            $strlimit
        ",$arrwhere);
        return $result->fetchAll();
    }
    /**
     * Đếm số bản ghi có trong table
     */
    public function Count(){
        $r = $this->getDefaultAdapter()->query("select count(*) as C from ".$this->_name)->fetch();
        return $r["C"];
    }
    /**
     * Chuyển dữ liệu tới combobox
     */
    static function ToCombo($data,$sel){
        $html="";
        $html .= "<option value='0'>".MSG11009004."</option>";
        foreach($data as $row){
            $html .= "<option value='".$row["ID_G"]."' ".($row["ID_G"]==$sel?"selected":"").">".$row["NAME"]."</option>";
        }
        return $html;
    }


	function getListEmpByIdGroup($idgr,$id_dep){
		
		try{
			$stmt = $this->getDefaultAdapter()->query('select
			
			t.ID_EMP,
			t.`LASTNAME`,
			t.`FIRSTNAME`,
			t.`ID_DEP`,
			t.ID_U,
			max(SEL) as SEL
 			from (SELECT
			  em.ID_EMP,
			  em.`LASTNAME`,
			  em.`FIRSTNAME`,
			  em.`ID_DEP`,
			  gr.`ID_G`,
			  u.`ID_U`,
			  (gr.`ID_G` = ? and gr.`ID_G` is not null) AS SEL
			FROM
			  qtht_employees em
			  inner join `qtht_users` u on em.`ID_EMP` = u.`ID_EMP`
			  LEFT JOIN `fk_users_groups` gr ON  u.`ID_U` = gr.`ID_U`
			 )t
			
			where t.`ID_DEP` = ?
			group by t.ID_EMP
			order by SEL DESC,LASTNAME ' ,array($idgr,$id_dep));
				
			$re = $stmt->fetchAll();
			return $re;
		}
		catch (Exception $ex){
			return array();
			
		}	
	}

	function getListGr($idgr){
		$dep_tbl = new DepartmentsModel();
		$arr_dep = $dep_tbl->fetchAll();
		$arr_emp = array();
		foreach($arr_dep as $it_dep){
			$arr_it_dep = $this->getListEmpByIdGroup($idgr,$it_dep->ID_DEP);
			if(count($arr_it_dep)){
				$arr_it_dep["DEP_NAME"] = $it_dep->NAME;
				array_push($arr_emp,$arr_it_dep);
				
			}
		}
		return $arr_emp;
	}

	
}
