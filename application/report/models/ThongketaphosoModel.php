<?php 
class ThongketaphosoModel{
	static function getReportDataHoso($fromdate,$todate){

		$where_arr =  array();
		if($fromdate || $fromdate != ""){
		 $fromdate = implode("-",array_reverse(explode("/",$fromdate)))." 00:00:00";
		 $where_fromdate = "NGAYTAO >= '".$fromdate."'" ; 
		 array_push($where_arr,$where_fromdate);
		 
		}
		if($todate || $todate != ""){
		$todate = implode("-",array_reverse(explode("/",$todate)))." 23:59:59";
		$where_todate = "NGAYTAO <= '".$todate."'" ; array_push($where_arr,$where_todate);
		}
		
		$where ="";
		if(count($where_arr) > 0)
			$where = " where " . implode(' and ',$where_arr)." ";


		$sql="select * from hscvdt_taphoso ".$where." ";

		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;       
		
	}

  public function getName($id_u)
	{
		$sql="
			SELECT
     			CONCAT(FIRSTNAME,' ',LASTNAME) AS TENNGUOITAO
			FROM
				QTHT_USERS
			INNER JOIN QTHT_EMPLOYEES ON QTHT_EMPLOYEES.ID_EMP=QTHT_USERS.ID_EMP
			WHERE ID_U=?";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($id_u));
		$data= $query->fetch();	
		return $data['TENNGUOITAO'];
		
	}
  
   	static function Vanbantaphoso($id_taphoso){
		$sql="select * from hscvdt_vanban where ID_TAPHOSO = ?";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($id_taphoso));
		$re = $query->fetchAll();
		return $re;	
	}

	static function dinhkemtaphoso($id_taphoso){

		$sql="select * from hscvdt_dinhkem_taphoso dk
		inner join hscvdt_vanban hs_vb on hs_vb.ID_OBJECT = dk.ID_DINHKEM
		where dk.ID_TAPHOSO = ? and hs_vb.LOAI =3" ;
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql,array($id_taphoso));
		$re = $query->fetchAll();
		return $re;	
	}
 
  static function ToTreethumuc($data,$id_thumuc,&$name_tree){    
	  
        $isFirst = false; 		
		//echo $id_thumuc;
        foreach($data as $row){
			if($row["ID_THUMUC"]==$id_thumuc){
				//echo "bang";
				if($id_thumuc==1){
                      $isFirst=true;
					}else{						
						//echo $row['NAME'];
						$name_tree.="/".$row['NAME'];
						ThongketaphosoModel::ToTreethumuc($data,$row["ID_THUMUC_PARENT"],&$name_tree);					
					}
			}			
        }
		//var_dump($name_tree);
        if($isFirst){		  
			echo implode(" \ ",array_reverse(explode("/",$name_tree)));
		}
    }

  	static function selectthucmuc(){
		$sql="select * from hscvdt_thumuc_taphoso ";
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$query = $dbAdapter->query($sql);
		$re = $query->fetchAll();
		return $re;	
	}
 
}
?>