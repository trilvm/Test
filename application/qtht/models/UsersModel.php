<?php
/**
 * UsersModel
 *  
 * @author truongvc
 * @version 1.0
 */
require_once 'qtht/models/employeesModel.php';
class UsersModel extends Zend_Db_Table
{
    protected $_name = 'qtht_users';
    public $_id_p = 0;
    /**
     * Count all items in QTHT_USERS table
     * @return integer
     */
	public function Count()
	{
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and USERNAME like ?";
		}
		
		//Thực hiện query
		$result = $this->getDefaultAdapter()->query("
			SELECT
				count(*) as C
			FROM
				$this->_name
			WHERE
				$strwhere
		",$arrwhere)->fetch();
		return $result["C"];
	}
	/**
     * Check exist of instance of users throught 'NameUser'
     *
     * @param  Zend_Db_Table $tableSel
     * @param  String $NameUser
     * @return boolean
     */
	function checkExistUser($NameUser)
	{
		
		$select=$this->select();
		$where=" USERNAME ='".$NameUser."'";
		$select->from($this->_name,'COUNT(*) AS NUM');
		$select->where($where);
		$row=$this->fetchRow($select);
		if($row->NUM >0)
		{
			return true;	
		}
		else return false;
		
		
	}
	/**
     * Select all item ID_U with USERNAME, FIRSTNAME,LASTNAME
     *
     * @return array
     */
	function selectAllUsersJoinEmployees()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			QTHT_USERS.ID_U,USERNAME,CONCAT(FIRSTNAME , ' ' , LASTNAME) as NAME
			FROM
				QTHT_USERS
			LEFT JOIN
				QTHT_EMPLOYEES
			ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP");
		return $r->fetchAll();
	}
        
        /**
     * Select all item ID_U with USERNAME, FIRSTNAME,LASTNAME
     *
     * @return array
     */
	function selectAllUsersJoinEmployees2()
	{       global $db;
		$r = $db->query("
			SELECT
     			QTHT_USERS.ID_U,USERNAME,CONCAT(FIRSTNAME , ' ' , LASTNAME) as NAME
			FROM
				QTHT_USERS
			LEFT JOIN
				QTHT_EMPLOYEES
			ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP");
		return $r->fetchAll();
	}
        
	/**
     * Select all from $offset to $limit with $order arrange
     *
     * @param  integer $offset
     * @param  integer $limit
     * @param  String $order
     * @return boolean
     */
	public function SelectAll($offset,$limit,$order){
		//Build phần where
		$arrwhere = array();
		$strwhere = "(1=1)";		
		if($this->_search != ""){
			$arrwhere[] = "%".$this->_search."%";
			$strwhere .= " and USERNAME like ?";
		}
		
		//Build phần limit
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		//Build order
		$strorder = "";
		if($order>0){
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
     * Get all items in QTHT_MENUS table with pairs : ID_MNU and NAME
     * @return array
     */
	function GetAllNames()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			USERNAME
			FROM
				QTHT_USERS");
		return $r->fetchAll();
	}
	
	static function getEmloyeeNameByIdUser($id_u){
		$model = new UsersModel();
		//$model->getDefaultAdapter()->beginTransaction();

		$query = $model->getDefaultAdapter()->query('Select e.`FIRSTNAME` , e.`LASTNAME`
		from `qtht_users` u JOIN `qtht_employees` e
		where u.`ID_EMP` = e.ID_EMP and u.`ID_U`=?',$id_u);
		//return $query;
		$re = $query->fetchAll();
		//$model->getDefaultAdapter()->commit();
		//return var_dump($re);;
		return $re[0]["FIRSTNAME"].' '.$re[0]["LASTNAME"];
		//retue->FIRSTNAME.' '.$re->LASTNAME;
	}
	
	static function SelectByDep($iddep){
		global $db;
		$sql = 	"SELECT u.ID_U, concat(e.FIRSTNAME,' ',e.LASTNAME) as NAME FROM 
			QTHT_USERS u
			inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP
			WHERE
				e.ID_DEP = ?
		";
		$r = $db->query($sql,$iddep);
		return $r->fetchAll();
	}
	static function SelectByGroup($idg){
		global $db;
		$sql = 	"SELECT u.ID_U, concat(e.FIRSTNAME,' ',e.LASTNAME) as NAME FROM 
			QTHT_USERS u
			inner join QTHT_EMPLOYEES e on u.ID_EMP = e.ID_EMP
			inner join FK_USERS_GROUPS g on g.ID_U = u.ID_U
			WHERE
				g.ID_G = ?
		";
		$r = $db->query($sql,$idg);
		return $r->fetchAll();
	}
	public function getName($id_u)
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT
     			USERNAME AS NGUOITAO,CONCAT(FIRSTNAME,' ',LASTNAME) AS TENNGUOITAO
			FROM
				QTHT_USERS
			INNER JOIN QTHT_EMPLOYEES ON QTHT_EMPLOYEES.ID_EMP=QTHT_USERS.ID_EMP
			WHERE ID_U=?",array($id_u));
		$data= $r->fetchAll();
		if(count($data)>0) return $data[0];
		else return -1;
	}
    
	public function getAllFullName()
	{
		$r = $this->getDefaultAdapter()->query("
			SELECT ID_EMP,CONCAT(FIRSTNAME,' ',LASTNAME) AS TEN FROM QTHT_EMPLOYEES ");
		$data= $r->fetchAll();
		return $data;
	}
	
	static function getAllUserName(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select USERNAME from qtht_users 
		";
		try{
			$qr = $dbAdapter->query($sql);
			return $qr->fetchAll();
		
		}catch (Exception $ex){
			return array();
		}
	}

	static function getAllcoquannoibo(){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select ID_CQ,NAME from vb_coquan where ISSYSTEMCQ = 1
		";
		try{
			$qr = $dbAdapter->query($sql);
			return $qr->fetchAll();
		
		}catch (Exception $ex){
			return array();
		}
	}


	static function getUsersByDepartment($idDepartment){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT u.`ID_U` , concat(e.`FIRSTNAME`, ' ',e.`LASTNAME`) as FULLNAME,d.NAME
			FROM 
				`qtht_users` u 

			inner join
				`qtht_employees` e on e.`ID_EMP` = u.`ID_EMP`
			inner join 
				`qtht_departments` d on d.`ID_DEP`= e.`ID_DEP`
			where
				d.`ID_DEP` = ? 
			order by u.ORDERS
				
		";
		$stm = $dbAdapter->query($sql,array($idDepartment));
		return $stm->fetchAll();
	}
	static function getnamecqnb($id_cq){  
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from vb_coquan where ISSYSTEMCQ = 1 and ID_CQ = ?
		";	
		try{
			$qr = $dbAdapter->query($sql,array($id_cq));			
			$re=$qr->fetch();			
		    return $re['NAME'];
		}catch (Exception $ex){
			return array();
		}
	}
	
	static function getAllNameAndId($is_check_active){
		$users = "";
		if(!$is_check_active)
			$is_check_active = 0;
		if($is_check_active == 0)
			$users = " QTHT_USERS ";
		else 
			$users = " (select * from QTHT_USERS where ACTIVE=1) QTHT_USERS  ";
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//try{
			$r = $dbAdapter->query("
				SELECT
	     			QTHT_USERS.ID_U,USERNAME,CONCAT(FIRSTNAME , ' ' , LASTNAME) as NAME
				FROM".
				$users
				."LEFT JOIN
					QTHT_EMPLOYEES
				ON QTHT_USERS.ID_EMP=QTHT_EMPLOYEES.ID_EMP");
			return $r->fetchAll();
		//}catch (Exception $ex){
			return array();
		//}
	}
	
	static function getUserDepId($id_u){
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$sql = "
			SELECT d.*
			FROM 
				`qtht_users` u 
			inner join
				`qtht_employees` e on e.`ID_EMP` = u.`ID_EMP`
			inner join 
				`qtht_departments` d on d.`ID_DEP`= e.`ID_DEP`
			where
				u.`ID_U` = ?
			";
			try{
				$stm = $dbAdapter->query($sql,array($id_u));
				return $stm->fetch();
			}catch(Exception $ex){
				return array();
			}
	}

	static function getUsernameById($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select USERNAME from qtht_users where ID_U = ?";
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			$re = $qr->fetch();
			return $re["USERNAME"];
		}catch(Exception $ex){
			return "";
		}
	}
	static function getIdEmpById($id_u){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select ID_EMP from qtht_users where ID_U = ?";
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			$re = $qr->fetch();
			return $re["ID_EMP"];

		}catch(Exception $ex){
			return "";
		}
	}
	static function getIdDepById($id_emp){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select ID_DEP from qtht_employees where ID_EMP = ?";
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			$re = $qr->fetch();
			return $re["ID_DEP"];

		}catch(Exception $ex){
			return "";
		}
	}
	static function getNamePBByIdDep($id_dep){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select NAME from qtht_departments where ID_DEP = ?";
		try{
			$qr = $dbAdapter->query($sql,array($id_dep));
			$re = $qr->fetch();
			return $re["NAME"];

		}catch(Exception $ex){
			return "";
		}
	}
	static function getSkhVBD($id_vbd){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select SOKYHIEU from `".QLVBDHCommon::Table("vbd_vanbanden")."` where ID_VBD = ?";
		//echo $sql;
		try{
			$qr = $dbAdapter->query($sql,array($id_vbd));
			$re = $qr->fetch();
			return $re["SOKYHIEU"];

		}catch(Exception $ex){
			return "";
		}
	}
	static function getNhhVBD($id_vbd){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select NGAYHETHAN from `".QLVBDHCommon::Table("vbd_vanbanden")."` where ID_VBD = ?";
		//echo $sql;
		try{
			$qr = $dbAdapter->query($sql,array($id_vbd));
			$re = $qr->fetch();
			return $re["NGAYHETHAN"];

		}catch(Exception $ex){
			return "";
		}
	}
	
	static function getIdByUsetname($username){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select ID_U from qtht_users where USERNAME = ?";
		try{
			$qr = $dbAdapter->query($sql,array($username));
			$re = $qr->fetch();
			return $re["ID_U"];
		}catch(Exception $ex){
			return "";
		}
	}

	static function getUserInfoWitchChucdanh($id_u){

		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select ID_U, 
						concat(FIRSTNAME, ' '  , LASTNAME) as NAME_U,
						cd.NAME as NAME_CD,
						dep.NAME as NAME_DEP

		from qtht_users u
		inner join qtht_employees emp on u.ID_EMP = emp.ID_EMP
		left join qtht_departments dep on dep.ID_DEP = emp.ID_DEP
		left join fk_chucdanh_emp fk on u.ID_EMP = fk.ID_EMP
		left join qtht_chucdanh cd on fk.ID_CD = cd.ID_CD
		where u.ID_U = ? ";
		
		try{
			$qr = $dbAdapter->query($sql,array($id_u));
			$re = $qr->fetch();
			return $re;
		}catch(Exception $ex){
			return "";
		}
	}

        public function GetGroupNameById($id) {

            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = "select NAME from qtht_groups where ID_G=?";
            try {
                $r = $db->query($sql, array($id))->fetch();
            } catch (Exception $ex) {
                die($ex->__toString());
            }
            return $r['NAME'];
        }
        
        public function getIdUserLeaderOfDep($dep){
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql = "select * from qtht_users u
                    inner join qtht_employees e on u.ID_EMP = e.ID_EMP
                    inner join qtht_departments dep on e.ID_DEP = dep.ID_DEP
                    where dep.ID_DEP= ? and u.ISLEADER =1";
            try {
                $r = $db->query($sql, array($dep))->fetch();
            } catch (Exception $ex) {
                die($ex->__toString());
            }
            return $r['ID_U'];
        }
        static function getsokyhieucqnbbyidu($id_cq){  
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from vb_coquan where ISSYSTEMCQ = 1 and ID_CQ = ?
		";	
		try{
			$qr = $dbAdapter->query($sql,array($id_cq));			
			$re=$qr->fetch();			
		    return $re['CODE'];
		}catch (Exception $ex){
			return '';
		}
	}
	static function getnamecqnbbysokyhieu($sokyhieu){  
		
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select * from vb_coquan where ISSYSTEMCQ = 1 and CODE = ?
		";	
		try{
			$qr = $dbAdapter->query($sql,array($sokyhieu));			
			$re=$qr->fetch();			
		    return $re['NAME'];
		}catch (Exception $ex){
			return '';
		}
	}
        static function getAllDep(){
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql="select ID_DEP,NAME from qtht_departments WHERE ID_DEP != 1";
            $result =$db->query($sql);
            $data = $result->fetchAll();
            return $data;
        }
        static function getAllUser(){
            $db = Zend_Db_Table::getDefaultAdapter();
            $sql="SELECT U.ID_U,
                    CONCAT(e.`FIRSTNAME` ,' ',e.`LASTNAME`) AS USERNAME,
                    e.ID_DEP
                    from `qtht_users` u JOIN `qtht_employees` e ON u.ID_EMP=e.ID_EMP;";
            $result =$db->query($sql);
            $data = $result->fetchAll();
            return $data;
        }
}