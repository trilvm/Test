<?php
class quanlycuochopModel extends Zend_Db_Table {

    public $_name = 'ql_cuochop';
    var $_table2 = 'ql_cuochop_congviec';

    public function SelectAll($parameter,$offset,$limit,$order,$id_u) {
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $where = "(1=1)";
        $param = array();
      //var_dump($parameter['NAME']);exit;
        if($parameter['NAME'] !=""){
            $ten=$parameter['NAME'];
            $where .= " and CH.TEN LIKE '%".$ten."%'";
			
		}

        if($parameter['fromdate']!=""){
                $ngaybatdau = $parameter['fromdate'];
                $where .= " and CH.NGAY >= '$ngaybatdau'";
        }
		
		if($parameter['phamvi']!="" && $parameter['phamvi'] != "2"){
                $phamvi = $parameter['phamvi'];
                $where .= " and CH.PHAMVI = '$phamvi'";
        }


        if($parameter['todate']!=""){
			$ngayketthuc = $parameter['todate'];
			$where .= " and CH.NGAY <= '$ngayketthuc'";
		}
          // echo $where; exit;
                
		$strlimit = "";
		if((int)$limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = $order;
		}
        $sql = "select * from ".$this->_name." CH WHERE $where                
                group by ID_CUOCHOP ORDER BY $order $strlimit
                ";
        //echo $sql; exit;
		
        $r = $db->query($sql,$parameter)->fetchAll();
        //print_r($r);exit;
        return $r;
    }

    public function save($id,$ten,$phamvi,$ngay,$gio,$diadiem,$nguoichutri,$nguoithamgia,$thuky,$id_nguoitao,$nguoi_ncq,$dstg,$idObject,$check_bb,$ld_thuchien,$thuky2) {
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            if ($id == 0) {
                $id_cuochop = $db->insert($this->_name, array(
                            'TEN' => $ten,
                            'PHAMVI' => $phamvi,
                            'NGAY' => $ngay,
                            'GIO' => $gio,
                            'DIADIEM' => $diadiem,
                            //'BOPHAN' => $bophan,
                            //'PHONG' => $phong,
                            'NGUOICHUTRI' => $nguoichutri,
                            'LD_THUCHIEN' => $ld_thuchien,
                            'NGUOITHAMGIA' => $nguoithamgia,
                            'THUKY'=>$thuky,
                            'THUKY2'=>$thuky2,
                            'ID_DINHKEM' => $idObject,
                            'ID_NGUOITAO'=> $id_nguoitao,
                            'NGUOITHAMGIA_NCQ'=> $nguoi_ncq,
                            'NGUOITHAMGIA'=> $dstg,
                            'CHECK_BB' => $check_bb
                        ));
                return $db->lastInsertId();
            } else {
                $db->update($this->_name, array(
                    'TEN' => $ten,
                    'PHAMVI' => $phamvi,
                    'NGAY' => $ngay,
                    'GIO' => $gio,
                    'DIADIEM' => $diadiem,
                    //'BOPHAN' => $bophan,
                    //'PHONG' => $phong,
                    'NGUOICHUTRI' => $nguoichutri,
                    'LD_THUCHIEN' => $ld_thuchien,
                    'NGUOITHAMGIA' => $nguoithamgia,
                    'THUKY'=>$thuky,
                    'THUKY2'=>$thuky2,
                    'ID_DINHKEM' => $idObject,
                    'ID_NGUOITAO'=> $id_nguoitao,
                    'NGUOITHAMGIA_NCQ'=> $nguoi_ncq,
                    'NGUOITHAMGIA'=> $dstg,
                    'CHECK_BB' => $check_bb
                        ), "ID_CUOCHOP='" . $id . "'");
                return $id;
            }
        } catch (Exception $e) {
            echo $e->__toString();
            exit;
        }
    }

    public function savengoaicoquan($id, $ten, $phamvi, $ngay, $gio, $diadiem,$nguoichutri,$noidung, $id_nguoitao, $dstd, $idObject, $check_bb,$ids_nguoithamgia) {
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            if ($id == 0) {
                $id_cuochop = $db->insert($this->_name, array(
                            'TEN' => $ten,
                            'PHAMVI' => $phamvi,
                            'NGAY' => $ngay,
                            'GIO' => $gio,
                            'DIADIEM_NCQ' => $diadiem,
                            'NGUOICHUTRI' => $nguoichutri,                            
                            'NOIDUNG' => $noidung,
                            //'NGUOITHAMGIA' => $nguoithamgia,
                            'ID_NGUOITAO'=> $id_nguoitao,
                            'NGUOITHAMGIA'=> $dstd,
                            'ID_DINHKEM' => $idObject,
                            'CHECK_BB'  => $check_bb,
                            //'IS_FINISH' => $is_finish,
                            'IDS_NGUOITHAMGIA' => $ids_nguoithamgia
                        ));
                return $db->lastInsertId();
            } else {
                $db->update($this->_name, array(
                    'TEN' => $ten,
                    'PHAMVI' => $phamvi,
                    'NGAY' => $ngay,
                    'GIO' => $gio,
                    'DIADIEM' => $diadiem,                    
                    'NGUOICHUTRI' => $nguoichutri,
                    'NOIDUNG' => $noidung,
                    //'NGUOITHAMGIA' => $nguoithamgia,
                    'ID_NGUOITAO'=> $id_nguoitao,
                    'NGUOITHAMGIA'=> $dstd,
                    'ID_DINHKEM' => $idObject,
                    'CHECK_BB'  => $check_bb,
                    'IDS_NGUOITHAMGIA' => $ids_nguoithamgia
                        ), "ID_CUOCHOP='" . $id . "'");
                return $id;
            }
        } catch (Exception $e) {
            echo $e->__toString();
            exit;
        }
    }

    public function NewCV($id_cuochop,$idcv,$data) {
        $db = Zend_Db_Table::getDefaultAdapter();
       // echo (int)$id_congviec;exit;
        try {            
                if(((int)$id_cuochop == 0 )|| ((int)$idcv == 0)){                    
                    $db->insert('ql_cuochop_congviec',$data);
                }else
                {
                    $db->update('ql_cuochop_congviec',$data,"ID_CONGVIEC = " .$idcv);                    
                }
            
        } catch (Exception $e) {
            die ($e->__toString());
        }
    }
	public function UpdateCV($idcv,$data) {
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->update('ql_cuochop_congviec',$data,"ID_CONGVIEC = " .$idcv);
    }
    public function FindById($id) {
        //Thực hiện query
        $result = $this->getDefaultAdapter()->query("
			SELECT
				*
			FROM
				$this->_name
			WHERE
				ID = ?
		", $id);
        return $result->fetch();
    }

    public function toCombo($formname, $select, $idOption = "OptionCV0") {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT
			U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
                    FROM
                            QTHT_USERS U
                            INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
                            INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
                            INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
                            INNER JOIN QTHT_GROUPS K ON K.ID_G=G.ID_G
                    WHERE K.CODE='LDP'";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='NGUOI[]' value='' style='width:80%;' id='" . $idOption . "' onChange='setSELECTEDAttribute(this.value,this.id)'>";
        echo "<option value=''>-- Chọn --</option>";
        $stt = 0;
        foreach ($r as $item) {
            $stt++;
            echo "<option value='" . $item['ID_U'] . "'>" . $item['NAME'] . "</option>";
        }
        echo "</select>";
        echo "<script>var soOption = " . $stt . "</script>";
    }

    public function GetCONGVIECBYIDCUOCHOP($idch) {
        $db = $this->getDefaultAdapter();
        $sql = "select * from $this->_table2 where ID_CUOCHOP=$idch";
        try{
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $e){
            die ($e->__toString());
        }
        return $r;
    }
    public function GetFileDinhKem($id_object) {
        $db = $this->getDefaultAdapter();
        $sql = "select * from gen_filedinhkem_2011 where ID_OBJECT=$id_object";
        $r = $db->query($sql)->fetchAll();
        return $r;
    }
     public function GetCVById($id_object) {
        $db = $this->getDefaultAdapter();
        $sql = "select * from ql_cuochop_congviec where ID_OBJECT=$id_object";
        try{
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $e){
            die ($e->__toString());
        }
        return $r;
    }

     public function GetCVByIdCV($idcv) {
        $db = $this->getDefaultAdapter();
        $sql = "select * from ql_cuochop_congviec where ID_CONGVIEC=$idcv";
        try{
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $e){
            die ($e->__toString());
        }
        return $r;
    }

    public function GetIDCONGVIECBYIDCUOCHOP($idch) {
        $db = $this->getDefaultAdapter();
        $sql = "select ID_CONGVIEC from $this->_table2 where ID_CUOCHOP=$idch";
        try{
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $e){
            die ($e->__toString());
        }
        return $r;
    }

    public static function GetCVIDCUOCHOP($idch) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select * from ql_cuochop_congviec CV where CV.ID_CUOCHOP=".$idch." AND CV.IS_FINISH = 0";
        //echo $sql; exit;
        $r = $db->query($sql)->fetchAll();
        return $r;
    }

    public function getData($ID_U){      
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql="select CH.TEN,CH.ID_CUOCHOP from ql_cuochop as CH
                inner join ql_cuochop_congviec as CV
                ON CH.ID_CUOCHOP = CV.ID_CUOCHOP
                WHERE CV.NGUOIXULY = ".$ID_U." AND CV.IS_FINISH = 0
                GROUP BY CH.TEN
                 ";
        try{
        $qr=$db->query($sql)->fetchAll();
         } catch (Exception $e){
            die ($e->__toString());
        }
        return $qr;
    }
    public function FinishCuocHop($id_cuochop,$data) {
        $db = Zend_Db_Table::getDefaultAdapter();
		$db->update($this->_name,$data,"ID_CUOCHOP = " .$id_cuochop);
    }
    function checkfinishCuocHop($id_ch){
		$sql = "select count(*) as CNT
				from `ql_cuochop_congviec`
				where ID_CUOCHOP = $id_ch and
				IS_FINISH IN (0, 1)";
		$r = $this->getDefaultAdapter()->query($sql);
        $r=$r->fetch();
		return  $r['CNT'];
	}
    static  function CountDataCV($ID_U){
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql="select count(*) as T from ql_cuochop_congviec as CV
                 inner join ql_cuochop as CH on CV.ID_CUOCHOP = CH.ID_CUOCHOP
                 where CH.IS_PUBLIC=1 and CH.IS_FINISH = 0
                 AND CV.NGUOIXULY = $ID_U";
        try{
        $qr=$db->query($sql)->fetch();
         } catch (Exception $e){
            die ($e->__toString());
        }
        return $qr['T'];
    }
    public function getDataCV($ID_U){
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql="select (*) from ql_cuochop_congviec as CV
                 inner join ql_cuochop as CH on CV.ID_CUOCHOP = CH.ID_CUOCHOP
                where CH.IS_PUBLIC=1 and CH.IS_FINISH = 0
                AND (CV.IS_FINISH=0 OR CV.IS_FINISH=1) OR CV.NGUOIXULY= $ID_U";
        try{
        $qr=$db->query($sql)->fetchAll();
         } catch (Exception $e){
            die ($e->__toString());
        }
        return $qr;
    }

    function findByMixed($page,$limit,$name,$order){
        //$name="'%".$name."%'";
        $offset = ($page-1)*$limit;
        $auth = Zend_Registry::get('auth');
        $data_session = $auth->getIdentity();
        $id_u =$data_session->ID_U;      

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $wheretemp = " match() against (? IN BOOLEAN MODE)";
                if($name !=""){
                   $sql="select * from ql_cuochop CH left join ql_cuochop_congviec CV
                    on CV.ID_CUOCHOP = CH.ID_CUOCHOP
                    where NGUOIXULY=$id_u or ID_NGUOITAO = $id_u and $wheretemp  and CH.IS_PUBLIC = 1
                    group by CH.ID_CUOCHOP limit $offset,$limit
                    ";
                }else{
                    $sql="select * from ql_cuochop CH left join ql_cuochop_congviec CV 
                    on CV.ID_CUOCHOP = CH.ID_CUOCHOP
                    where NGUOIXULY=$id_u or ID_NGUOITAO = $id_u  and CH.IS_PUBLIC = 1
                    group by CH.ID_CUOCHOP limit $offset,$limit
                    ";
                }
                
//        $sql = sprintf("select * from %s ch
//        left join ql_cuochop_congviec cv on cv.ID_CUOCHOP=ch.ID_CUOCHOP
//        WHERE (ch.ID_NGUOITAO = %s ) OR (cv.NGUOIXULY = %s and ch.IS_PUBLIC = 1)
//        group by ch.ID_CUOCHOP
//        ",$this->_name,$id_u,$id_u);
//        //echo $sql;exit;
//        $r = $db->query($sql)->fetchAll();
//        return $r;

          $qr = $dbAdapter->query($sql,array($name));

          return $qr->fetchAll();
    }

    function count($parameter,$id_u){
        ////////////////////////////////////////////////////////////
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = "";
        $param = array();

        if($parameter['NAME'] !=""){
                        $ten=$parameter['NAME'];
			$where .= " and CH.TEN = '$ten'";

		}
        if($parameter['fromdate']!=""){
                $ngaybatdau = $parameter['fromdate'];
                $where .= " and CH.NGAY >= '$ngaybatdau'";
        }

        if($parameter['todate']!=""){
			$ngayketthuc = $parameter['todate'];
			$where .= " and CH.NGAY <= '$ngayketthuc'";
		}
          // echo $where; exit;
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = $order;
		}

        $sql = sprintf("SELECT COUNT(*) AS CNT FROM (select CV.ID_CUOCHOP from %s ch
                left join ql_cuochop_congviec cv on cv.ID_CUOCHOP=ch.ID_CUOCHOP
                WHERE (ch.ID_NGUOITAO = %s $where) OR (cv.NGUOIXULY = %s $where)
                group by ch.ID_CUOCHOP) t
                ",$this->_name,$id_u,$id_u);
       // echo $sql;exit;
        $r = $db->query($sql)->fetch();
        return $r['CNT'];
    }

    /**
	 * Lấy phòng ban
	 */
	function GetDeparment(){
		$tree = array();
		QLVBDHCommon::GetTree(&$tree,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
		return $tree;
	}

        static function ToDepCombo($data,$sel){
		$html="";
		foreach($data as $row){
			$html .= "<option value='".$row["ID_DEP"]."' ".($row["ID_DEP"]==$sel?"selected":"").">".(str_repeat("--",$row['LEVEL']).$row["NAME"])."</option>";

		}
		return $html;
	}

    public function Combonguoithamgia($formname, $select, $idOption) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT
			U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
                    FROM
                                QTHT_USERS U
                                INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='NGUOITHAMGIA' value='' style='width:30%;' id='" . $idOption . "' >";
        echo "<option value=''>-- Chọn --</option>";
        $stt = 0;
        foreach ($r as $item) {
            $stt++;
            echo "<option value='" .$item['ID_U'] . "'>" . $item['NAME'] . "</option>";
        }
        echo "</select>";

        echo "<script>var soOption = " . $stt . "</script>";
    }
    public function Combonguoithamgia2($formname, $select, $idOption) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT
			U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
                    FROM
                                QTHT_USERS U
                                INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select style='width:45%;' name='NGUOITHAMGIA2' value='' style='width:30%;' id='" . $idOption . "' >";
        echo "<option value=''>-- Chọn --</option>";
        $stt = 0;
        foreach ($r as $item) {
            $stt++;
            echo "<option value='" . $item['ID_U'] . "'>" . $item['NAME'] . "</option>";
        }
        echo "</select>";

        echo "<script>var soOption = " . $stt . "</script>";
    }
    
        public function Combonguoichutri($formname, $select, $idOption) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT
			U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
                    FROM
                                QTHT_USERS U
                                INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='NGUOICHUTRI' value='' style='width:15%;' id='" . $idOption . "' onChange='setSELECTEDAttribute(this.value,this.id)'>";
        echo "<option value=''>-- Chọn --</option>";
        $stt = 0;
        foreach ($r as $item) {
            $stt++;
            echo "<option value='" . $item['ID_U'] . "'>" . $item['NAME'] . "</option>";
        }
        echo "</select>";

        echo "<script>var soOption = " . $stt . "</script>";
    }

    static  function GetDiaDiemById($id) {
        $sql = "select TEN from dm_diadiem where ID_DIADIEM = " . $id;
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql)->fetch();
        } catch (Exception $ex) {
            $ex->__toString();
        }
        return $r['TEN'];
    }
	public function toComboNguoiXuLy($formname, $select, $idOption = "OptionCV0") {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT
			U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
                    FROM
                                QTHT_USERS U
                                INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME";
        $query = $db->query($sql);
        $r = $query->fetchAll();
        echo "<select name='NGUOI[]' value='' style='width:80%;' id='" . $idOption . "' onChange='setSELECTEDAttribute(this.value,this.id)'>";
        echo "<option value=''>-- Chọn --</option>";
        $stt = 0;
        foreach ($r as $item) {
            $stt++;
            echo "<option value='" . $item['ID_U'] . "'>" . $item['NAME'] . "</option>";
        }
        echo "</select>";
        echo "<script>var soOption = " . $stt . "</script>";
    }

    public function SelectIdUFinish($id_ch) {
        $sql = "select NGUOIXULY from ql_cuochop_congviec cv left join ql_cuochop ch
            ON ch.ID_CUOCHOP = cv.ID_CUOCHOP where ch.ID_CUOCHOP = " . $id_ch. " and cv.IS_FINISH = 1";
        //echo $sql; exit;
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $ex) {
            $ex->__toString();
        }
        return $r;
    }
    public function SelectIdUNotFinish($id_ch) {
        $sql = "select NGUOIXULY from ql_cuochop_congviec cv left join ql_cuochop ch
            ON ch.ID_CUOCHOP = cv.ID_CUOCHOP where ch.ID_CUOCHOP = " . $id_ch. " and cv.IS_FINISH = 0";
        //echo $sql; exit;
        $db = Zend_Db_Table::getDefaultAdapter();
        try {
            $r = $db->query($sql)->fetchAll();
        } catch (Exception $ex) {
            $ex->__toString();
        }
        return $r;
    }

    public function getReportDataCV($id_ch){
		$sql = "
                        select CV.* from `ql_cuochop_congviec` CV
                        LEFT JOIN ql_cuochop CH
                        ON CV.`ID_CUOCHOP` = CH.`ID_CUOCHOP`
                        WHERE CV.`ID_CUOCHOP` = '".$id_ch. "'";
               // 	echo  $sql;exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}        
}