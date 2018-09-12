<?php

require_once ('Zend/Db/Table/Abstract.php');
require_once ('DepartmentsModel.php');

class fk_chucdanh_empModel extends Zend_Db_Table_Abstract {
	var $_name = 'fk_chucdanh_emp';
	
	function getListEmp($idChucDanh){
		$dep_tbl = new DepartmentsModel();
		$arr_dep = $dep_tbl->fetchAll();
		$arr_emp = array();
		foreach($arr_dep as $it_dep){
			$arr_it_dep = $this->getListEmpByIdChucDanhAndDepart($idChucDanh,$it_dep->ID_DEP);
			if(count($arr_it_dep)){
				$arr_it_dep["DEP_NAME"] = $it_dep->NAME;
				array_push($arr_emp,$arr_it_dep);
			}
		}
		return $arr_emp;
	}
	
	function getListEmpByIdChucDanhAndDepart($idChucDanh,$id_dep){
		
		try{
			$stmt = $this->getDefaultAdapter()->query('select
			t.ID_EMP,
			t.`LASTNAME`,
			t.`FIRSTNAME`,
			t.`ID_DEP`,
			max(SEL) as SEL
			from (SELECT
			  em.ID_EMP,
			  em.`LASTNAME`,
			  em.`FIRSTNAME`,
			  em.`ID_DEP`,
			  fk.`ID_CD`,
			  (fk.ID_CD = ? and fk.ID_CD is not null) AS SEL
			FROM
			  qtht_employees em
			  LEFT JOIN fk_chucdanh_emp fk ON (em.ID_EMP = fk.ID_EMP)
			) t
			LEFT JOIN qtht_chucdanh cd ON (t.ID_CD = Cd.ID_CD)
			where t.`ID_DEP` = ? 
			group by t.ID_EMP 
			order by SEL DESC,LASTNAME ' ,array($idChucDanh,$id_dep));
				
			$re = $stmt->fetchAll();
			return $re;
		}
		catch (Exception $ex){
			//echo $ex->__toString();
			return array();
			
		}	
	}
	
	function deleteByIdChucDanh($idChucDanh){
		try{
			$where = "ID_CD=".$idChucDanh;
			$this->delete($where);
		}catch(Exception $ex){
			//loi
		}
		 
	}
	
	function insertOne($idChucDanh,$idEmp){
		try{
			$data = array("ID_CD" => $idChucDanh , "ID_EMP"=>$idEmp);
			$this->insert($data);
		}catch (Exception $ex){
			//loi
		}
	}
	
	function insertOneChucDanhMultiEmp($idChucDanh , $arr_IdEmp){
		foreach($arr_IdEmp as $ele){
			$this->insertOne($idChucDanh,$ele);
		}
	
	}
}

?>
