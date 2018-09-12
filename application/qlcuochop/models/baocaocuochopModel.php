<?php
class baocaocuochopModel extends Zend_Db_Table{
    public function getReportData($fromdate,$todate){
		$user = Zend_Registry::get('auth')->getIdentity();
                $where_arr =  array();
		if($fromdate || $fromdate != ""){
                     $fromdate = implode("-",array_reverse(explode("/",$fromdate)));
                     $where_fromdate = "NGAY >= '".$fromdate."'" ;
                     array_push($where_arr,$where_fromdate);

		}
		if($todate || $todate != ""){
                    $todate = implode("-",array_reverse(explode("/",$todate)));
                    $where_todate = "NGAY <= '".$todate."'" ;
                    array_push($where_arr,$where_todate);
		}
               // var_dump($where_arr);exit;
                $where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";
		
		$sql = "
                        select * from `ql_cuochop` CH 
                        $where ";
                	//echo  $sql;exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}

        public function getReportDataCV($id_ch){
		$sql = "
                        select * from `ql_cuochop_congviec` CV
                        LEFT JOIN ql_cuochop CH
                        ON CV.`ID_CUOCHOP` = CH.`ID_CUOCHOP`
                        WHERE CV.`ID_CUOCHOP` = '".$id_ch. "'";
               // 	echo  $sql;exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;
	}

        static function CountgetReportData($fromdate,$todate,$i){
		$user = Zend_Registry::get('auth')->getIdentity();
                $where_arr =  array();
		if($fromdate || $fromdate != ""){
                     $fromdate = implode("-",array_reverse(explode("/",$fromdate)));
                     $where_fromdate = "NGAY >= '".$fromdate."'" ;
                     array_push($where_arr,$where_fromdate);

		}
		if($todate || $todate != ""){
                    $todate = implode("-",array_reverse(explode("/",$todate)));
                    $where_todate = "NGAY <= '".$todate."'" ;
                    array_push($where_arr,$where_todate);
		}
		//var_dump($where_arr);exit;
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";
                if($i==1){
                    $sql = "
                    select count(*) as TONG from `ql_cuochop` CH
                    right join `ql_cuochop_congviec` CV on CH.`ID_CUOCHOP` = CV.`ID_CUOCHOP`
                            $where
                    ";
                }
                 elseif($i==2) {
                    $sql=" select count(*) as TONG FROM ql_cuochop as CH
                        $where";
                }
                elseif ($i==3) {
                    if($where != ""){
                    $sql= "select count(*) as TONG From ql_cuochop as CH
                              right join `ql_cuochop_congviec` CV on CH.`ID_CUOCHOP` = CV.`ID_CUOCHOP`
                              $where and (CV.IS_FINISH=1 or CV.IS_FINISH=2)
                    ";
                    }
                    else
                    {
                       $sql= "select count(*) as TONG From ql_cuochop as CH
                              right join `ql_cuochop_congviec` CV on CH.`ID_CUOCHOP` = CV.`ID_CUOCHOP`
                              where CV.IS_FINISH=1 or CV.IS_FINISH=2
                              ";
                    }
                    //echo $sql;
                }
                elseif ($i==4) {
                    if($where != ""){
                    $sql= "select count(*) as TONG From ql_cuochop as CH
                              right join `ql_cuochop_congviec` CV on CH.`ID_CUOCHOP` = CV.`ID_CUOCHOP`
                              $where and CV.IS_FINISH=0
                    ";
                    }
                    else
                    {
                       $sql= "select count(*) as TONG From ql_cuochop as CH
                              right join `ql_cuochop_congviec` CV on CH.`ID_CUOCHOP` = CV.`ID_CUOCHOP`
                              where CV.IS_FINISH=0
                              ";
                    }
                }
		//echo  $sql;exit;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetch();
		return $re['TONG'];
        }
}
?>
