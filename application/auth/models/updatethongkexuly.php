<?php 
class updatethongkexuly{
	
	function runscript(){
		$db = Zend_Db_Table::getDefaultAdapter();
		//lay dong luan chuyen cuoi cung 
		$sql = "
			select wl.ID_PL , 
			wl.ID_PI  , 
			wi.`ID_O` , 
			wl.DATESEND , 
			wl.ID_U_SEND , 
			wl.ID_U_RECEIVE,
			wl.TRE,
			wl.DATEEND,
			wi.IS_FINISH,
			wi.IS_TRE
			from
				`wf_processlogs_2009` wl
			inner join `wf_processitems_2009` wi on wl.ID_PI = wi.`ID_PI`
			
		";

		$qr = $db->query($sql);
		$re = $qr->fetchAll();
		foreach($re as $it){
			/* Cap nhat cac bang ho so cong viec dang xu ly, da xu ly, tre */

				
				$id_hscv = $it["ID_O"];
				
				// kiem tra hscv da co trong ho so dang xu ly chua
				$ref = $db->query('select count(*) as DEM from '.QLVBDHCommon::Table('hscv_dangxuly'). '  where ID_HSCV = ?', array($id_hscv));
				$ref = $ref->fetch();
				$count = $ref["DEM"];
				if((int)$count >0){
					//cap nhat lai nguoi nhan
					$db->update(QLVBDHCommon::Table("hscv_dangxuly"),array('ID_U'=>$it["ID_U_RECEIVE"],
																			'DATE_RECEIVE' => $it["DATESEND"],
																			'DATEEND' => $it["DATEEND"]	
					),"ID_HSCV=".$id_hscv);
				}else{
					$db->insert(QLVBDHCommon::Table("hscv_dangxuly"),array(
						'ID_HSCV'=> $id_hscv,
						'ID_U'=> $it["ID_U_RECEIVE"],
						'DATE_RECEIVE' => $it["DATESEND"],
						'DATEEND'=>$it["DATEEND"]
					));
				}
				
				
				//xoa trong bang da xu ly ma nguoi nhan co ho so cong viec
				$db->delete(QLVBDHCommon::Table('hscv_daxuly'),' ID_HSCV = '. $id_hscv . ' and ID_U = '.$it["ID_U_RECEIVE"]);
				
			
				//them moi ho so da xu ly nguoi chuyen
				$ref = $db->query('select count(*) as DEM from '.QLVBDHCommon::Table('hscv_daxuly'). '  where ID_HSCV = ?  and ID_U = ? ',array($id_hscv,$it["ID_U_SEND"]));
				$ref = $ref->fetch();
				if($ref["DEM"] > 0){
					
					$db->update(QLVBDHCommon::Table("hscv_daxuly"),array(
					'ID_U'=>$it["ID_U_SEND"],
					'DATE_RECEIVE'=>$it["DATESEND"]
					),
						
					"ID_HSCV=".$id_hscv);
					
				}else{
					$db->insert(QLVBDHCommon::Table("hscv_daxuly"),array(
						'ID_HSCV'=>$id_hscv,
						'ID_U'=> $it["ID_U_SEND"],
						'DATE_RECEIVE' => $it["DATESEND"]
						
					));
				}
				$tre_last = (int)$it["TRE"];

				
				if($tre_last != 0){
					
					$ref = $db->query('select *  from '.QLVBDHCommon::Table('hscv_tre'). '  where ID_HSCV = ?  and ID_U = ? ',array($id_hscv,$it["ID_U_SEND"]));
					$ref = $ref->fetch();
					//$count = count($ref);
					//var_dump($ref) ; exit;//$count;
					if(!$ref){
						$db->update(QLVBDHCommon::Table("hscv_tre"),array(
							'TRE' => 	( $ref["TRE"]+ $tre_last),
							'TRECOUNT' => ( $ref["TRECOUNT"]+ 1)
						),"ID_HSCV = ". $id_hscv." and ID_U = ".$it["ID_U_SEND"] );
					}else{
						$db->insert(QLVBDHCommon::Table("hscv_tre"),array(
							'ID_HSCV'=>$id_hscv,
							'ID_U'=> $it["ID_U_SEND"],
							'DATE_RECEIVE' => date("Y-m-d H:i:s"),
							'TRE'=>$tre_last,
							'TRECOUNT'=>1	
						));
					}
					
				}
		}
		
	}

	function zend_year($year){
		global $db;
		global $config;
		
		$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE 'qtht_log_".$year."'";
		$r = $db->query($sql);
		$tables = $r->fetchAll();
		if(count($tables)==0){
			$qr = $db->query("select count(*) as DEM  from qtht_year where YEAR=?",array($year));
			$re = $qr->fetch();
			if((int)$re["DEM"]==0){
				$db->insert('qtht_year',array("YEAR"=>$year));
			}
			//tao nam lam viec
			$sql = "SHOW tables FROM ".($config->db->params->dbname)." LIKE '%".($year-1)."'";
			$r = $db->query($sql);
			$tables = $r->fetchAll();
			foreach($tables as $table){
				try{
					$r = $db->query("show create table ".$table['Tables_in_'.($config->db->params->dbname).' (%'.($year-1).')']);
					$r = $r->fetch();
					$r['Create Table'] = str_replace($year-1,$year,$r['Create Table']);
					$db->query($r['Create Table']);
					
				}catch(Exception $ex){
					
				}
			}
			
			
		}
		
		
	}

}
?>