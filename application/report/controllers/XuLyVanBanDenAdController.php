<?php 
	class Report_XuLyVanBanDenAdController extends Zend_Controller_Action{
		function init(){
		}

		function indexAction(){
			$dom = new DOMDocument();
			$dom = DOMDocument::load("data_adreport/xulyvanbanden.xml");
			$tagstable = $dom->getElementsByTagName('table');
			$arr_tbl = array();
			$arr_col = array();
			for($i=0 ; $i<$tagstable->length;$i++){
				$arr = array();
				$item = $tagstable->item($i);
				$arr["VALUE"] = $item->getAttribute('name');
				$arr["NAME"] =	$item->getAttribute('des');
				$arr_tbl[] = $arr;
				$childnodes = $item->getElementsByTagName('column');
				$arr_col_i = array();
				for($j = 0 ; $j < $childnodes->length ; $j++){
					$arr_j = array();
					$node = $childnodes->item($j);
					$arr_j["NAME"] = $node->nodeValue;
					$arr_j["VALUE"] = $node->getAttribute('name');
					$arr_j["REQUIRE"] = (string)$node->getAttribute('require_column');
					$arr_col_i[] =$arr_j; 
				}
				$arr_col[$arr["VALUE"]] = $arr_col_i;
			}
			//var_dump($arr_tbl);
			$this->view->arr_tbl = $arr_tbl;
			$this->view->arr_col = $arr_col;
			//var_dump($arr_col["vbd_vanbanden"]);exit;
			
			
			//exit;
			/*$tags = $dom->getElementsByTagName('column');
			$tag = $dom->getElementById('VBDEN_SOKYHIEU');
			$tag = $tags->item(0); 
			var_dump($tag->nodeValue);exit;*/
		}

		function reportviewAction(){
			$this->_helper->layout->disableLayout();
			$params = $this->_request->getParams();
			$dom = new DOMDocument();
			$dom = DOMDocument::load("data_adreport/xulyvanbanden.xml");
			
			//parse join table
			$join_tbls = $dom->getElementsByTagName('join_table');
			$table_joins = array();

			for($tbjdem =0 ; $tbjdem < $join_tbls->length ; $tbjdem++ ){
				$item_tbljoin = $join_tbls->item($tbjdem);
				
				$arr_att_table_join = array();
				$arr_att_table_join["name"] = $item_tbljoin->getAttribute('name');
				$arr_att_table_join["table_f"] = $item_tbljoin->getAttribute('table_f');
				$arr_att_table_join["table_a"] = $item_tbljoin->getAttribute('table_a');
				$arr_att_table_join["f_col"] = $item_tbljoin->getAttribute('f_col');
				$arr_att_table_join["f_col_join"] = $item_tbljoin->getAttribute('f_col_join');
				$arr_att_table_join["af_col"] = $item_tbljoin->getAttribute('af_col');
				$arr_att_table_join["af_col_join"] = $item_tbljoin->getAttribute('af_col_join');
				$arr_att_table_join["name_des"] = $item_tbljoin->getAttribute('name_des');
				$arr_att_table_join["name_tbl"] = $item_tbljoin->getAttribute('name_tbl');
				
				$arr_att_table_join["hasyear"] = $item_tbljoin->getAttribute('hasyear');
				$table_joins[] = $arr_att_table_join;
				
			}
			
			//var_dump($table_joins); exit;
			//name_des="fk_vbden_hscv" name_tbl="vbd_fk_vbden_hscvs" hasyear="YES" 
			//parse table
			$tagstable = $dom->getElementsByTagName('table');
			$tables = array();
			$columns = array();
			$table_year = array();
			$table_namedes = array();
			$table_type = array();
			$table_mulitple = array();
			$table_j_table = array();
			$table_j_col = array();
			$table_j_o_col = array();
			
			$arr_col_name = array();
			$arr_col_display = array();
			
			for($i=0 ; $i<$tagstable->length;$i++){
				$item = $tagstable->item($i);
				$ln_choice_tbl = false;
				foreach($params['tbl_choice'] as $pa_tble_choice){
					if($pa_tble_choice == $item->getAttribute('name') ){
							$ln_choice_tbl = true;
							break;
					}
				}
				if($ln_choice_tbl == true){
					
					if(count($params[$item->getAttribute('name')])){
						$name_tbl = $item->getAttribute('name');
						$tables[] = $name_tbl;
						$table_year[$name_tbl] = $item->getAttribute('hasyear');
						$table_namedes[$name_tbl] = $item->getAttribute('name_des');
						$table_type[$name_tbl] = $item->getAttribute('type');
						$table_j_table[$name_tbl] = $item->getAttribute('j_table');
						$table_j_col[$name_tbl] = $item->getAttribute('j_col');
						$table_j_o_col[$name_tbl] = $item->getAttribute('j_o_col');
						$table_j_col_value[$name_tbl] = $item->getAttribute('j_col_value');
						$table_j_col_des[$name_tbl] = $item->getAttribute('j_col_des');
						$table_j_o_col_inner[$name_tbl] = $item->getAttribute('j_o_col_inner');
						if($table_type[$name_tbl] == "multiple"){
							$tbl_entry = array();
							$tbl_etags = $item->getElementsByTagName('table_entry');
							//var_dump($tbl_etags);
							//echo count($tbl_etags); exit;
							for($tbl_tg_i = 0 ; $tbl_tg_i < $tbl_etags->length ; $tbl_tg_i++){
								$tbl_etag = $tbl_etags->item($tbl_tg_i);
								$tbl_entry_att = array();
								$tbl_entry_att['name'] = $tbl_etag->getAttribute('name');	
								$tbl_entry_att['on_join'] = $tbl_etag->getAttribute('on_join');
								$tbl_entry_att['on_join_before'] = $tbl_etag->getAttribute('on_join_before');
								$tbl_entry_att['name_des'] = $tbl_etag->getAttribute('name_des');
								$tbl_entry_att['tbl_join'] = $tbl_etag->getAttribute('tbl_join');
								$tbl_entry_att['hasyear'] = $tbl_etag->getAttribute('hasyear');
								$tbl_entry[] = $tbl_entry_att;
							}
							$table_mulitple[$name_tbl] =$tbl_entry;
						}
						 
						$childnodes = $item->getElementsByTagName('column');
						$cols_c = $params[$item->getAttribute('name')];
						$cols_d = $params[$item->getAttribute('name')."_display"];
						$col_mask = 0;
						
						for($j = 0 ; $j < $childnodes->length ; $j++){
							$node = $childnodes->item($j);
							if($node->getAttribute('type') == "JOIN_REQUIRE"){
										$arr_value_col[$dem_col] = array();
										$arr_temp = array();
										$arr_temp["name_join"]= $node->getAttribute('name_join');
										$arr_temp["value_column"]= $node->getAttribute('value_column');
										$arr_temp["name_des"]= $node->getAttribute('name_des');
										$arr_temp["inner_value"]= $node->getAttribute('inner_value');
										$arr_value_col[$dem_col] = $arr_temp;
										$columns[$name_tbl]["JOIN_REQUIRE"][] = $arr_value_col;
										//echo "chay qua may lan";
							}
						}
						foreach($cols_c as $col){
						for($j = 0 ; $j < $childnodes->length ; $j++){
							
							$node = $childnodes->item($j);
							
							
							$arr_value_col = array();
							$dem_col = 0;
								
								
							
								if($col  == $node->getAttribute('name')){
									$arr_col_name[] = $node->getAttribute('name_des');
									$arr_col_display[] = $cols_d[$col_mask];
									$type = $node->getAttribute('type');
									$col_mask++;
									//echo $type;
									if($type == "VALUE_COLUMN"){
										$arr_value_col[$dem_col] = array();
										$arr_temp = array();
										$arr_temp["value_column"]= $node->getAttribute('value_column');
										$arr_temp["name_des"]= $node->getAttribute('name_des');
										$arr_temp["inner_value"]= $node->getAttribute('inner_value');
										$arr_value_col[$dem_col] = $arr_temp;
										$columns[$name_tbl]["VALUE_COLUMN"][] = $arr_value_col;
										//inner_value
									}
									if($type == "GROUP_BY"){
										$arr_value_col[$dem_col] = array();
										$arr_temp = array();
										$arr_temp["value_column"]= $node->getAttribute('value_column');
										$arr_temp["name_des"]= $node->getAttribute('name_des');
										$arr_temp["function"]= $node->getAttribute('function');
										$childparams = $item->getElementsByTagName('params');
										$pa_arr = array();
										for($q = 0 ; $q < $childparams->length ; $q++){
												$paramnode =$childparams->item($q);
												if($paramnode->getAttribute('name') == 'param_'.$node->getAttribute('name'))
													$pa_arr[] = $paramnode->nodeValue;
										}
										$arr_temp["params"] = $pa_arr;
										$arr_value_col[$dem_col] = $arr_temp;
										$columns[$name_tbl]["GROUP_BY"][] = $arr_value_col;
									}
									if($type == "VALUE_REFER"){
										$arr_value_col[$dem_col] = array();
										$arr_temp = array();
										$arr_temp["value_column"]= $node->getAttribute('value_column');
										$arr_temp["name_des"]= $node->getAttribute('name_des');
										$arr_temp["join_name_tbl"] = $node->getAttribute("join_name_tbl");
										$arr_temp["join_name_tbl_hasyear"] = $node->getAttribute("join_name_tbl_hasyear");
										$arr_temp["join_name_tbl_value"] = $node->getAttribute("join_name_tbl_value");
										$arr_temp["join_name_tbl_on"] = $node->getAttribute("join_name_tbl_on");
										
										$arr_temp["type_join"] = $node->getAttribute("type_join");
										$arr_value_col[$dem_col] = $arr_temp;
										$columns[$name_tbl]["VALUE_REFER"][] = $arr_value_col;
									}
									if($type == "VALUE_FUNCTION"){
										
									}

									//$dem_col++;
									



								}
								
								
							}
							
						}
					}
				}
			}
			
			$group_by ="";
			$sel = "";
			$join_refer ="";
			$arr_sel_value = array();
			$arr_groupby = array();
			$arr_join_refer = array();
			$join = "";
			$arr_join = array();
			/*if(count($tables) == 1){
				if($table_year[$tables[0]] == "YES")
					$join = QLVBDHCommon::Table($tables[0])." ".$table_namedes[$tables[0]];
			}*/
			
			
			//$table_year = array();
			//$table_namedes = array();
			//$table_type = array();
			//var_dump($table_namedes);
			//var_dump($table_type);
			//exit;
			
			//chon join table can co
			$sel_table_joins = array();
			foreach($table_joins as $tbl_join)
			{
					$ok_f = 0 ;
					$ok_a = 0;
					foreach($tables as $table){
						if($tbl_join['table_f'] == $table){
							$ok_f = 1;
							//break;
						}
						if($tbl_join['table_a'] == $table){
							$ok_a = 1;
							//break;
						}
					}
					//echo $ok_f."c�ch".$ok_a;
					if($ok_f == 1 && $ok_a == 1)
						$sel_table_joins[] = $tbl_join;

			}
			
			
			//var_dump($table_joins); exit;

			foreach($tables as $table){
				
				//parse tables
				$col_exs ="";
				$on_ext="";
				foreach($sel_table_joins as $tbl_join)
				{
						if($tbl_join['table_a'] == $table){
							$on_ext =  "  on ".$table_namedes[$table].".".$tbl_join['af_col_join']."=".$tbl_join['name_des'].".".$tbl_join["af_col"];;
						}
				}
				
				//echo $on_ext; exit;
				//join khong table trung gian
				if($table_j_table[$table] != ""){
					foreach($tables as $t){
						if($t == $table_j_table[$table]){ // co ban can join
							$on_ext =  "  on ".$table_namedes[$t].".".$table_j_o_col[$table]."=".$table_namedes[$table].".".$table_j_col[$table];
							$col_exs = $table_j_col_value[$table] . " as " .$table_j_col_des[$table];
							break;
						}
					}
				}else{
					foreach($table_j_table as $key=>$t){
						if($table == $t){
							$col_exs = $table_j_o_col_inner[$key] ;
							break;
						}
					}
				}
				
				//$table_j_table[$name_tbl] = $item->getAttribute('j_table');
				//$table_j_col[$name_tbl] = $item->getAttribute('j_col');
				//$table_j_o_col[$name_tbl] = $item->getAttribute('j_o_col');

				if($table_type[$table] == "single") 
				{
					if($table_year[$tables[0]] == "YES")
						$arr_join[] = QLVBDHCommon::Table($table)." ".$table_namedes[$table];
				}
				
				if($table_type[$table] == "multiple") 
				{
						$child_tblarr = array();
						
						$arr_sel_inner  = array();
						
						foreach($table_mulitple[$table] as $tbl){
							$str_from = "";
							$tbl_name = $tbl["name"];
							if($tbl["hasyear"] == "YES"){
								$tbl_name = QLVBDHCommon::Table($tbl_name);
							}
							if($tbl["on_join"] != "" && $tbl["on_join_before"] != "")
							$str_from = $tbl_name. " " . $tbl["name_des"]. " on ".$tbl["tbl_join"].".".$tbl["on_join_before"]."=".$tbl["name_des"].".".$tbl["on_join"]; 	
							else
								$str_from = $tbl_name . " ". $tbl["name_des"] ." " ;
							$child_tblarr[] = $str_from;
						}
						
						//$sel_table_joins[]
						


						foreach($columns[$table]["VALUE_COLUMN"] as $arr_col){
								$arr_sel_inner[] = " ".$arr_col[0]["inner_value"]." as  ".$arr_col[0]["name_des"];
						}
						
						//chon them cac cot can thiet de join
						
						//var_dump($columns[$table]["JOIN_REQUIRE"]);exit;
						foreach($columns[$table]["JOIN_REQUIRE"] as $arr_col){
								foreach($sel_table_joins as $tbl_join){
									if($arr_col[0]["name_join"] == $tbl_join["name"]){
										$arr_sel_inner[] = " ".$arr_col[0]["inner_value"]." as  ".$arr_col[0]["name_des"];
										break;
									}
								}
								
						}
						//var_dump($columns[$table]["JOIN_REQUIRE"]); exit;
						
						if($col_exs!="")
							array_push($arr_sel_inner,$col_exs);
						$sel_inner = implode(" , " , $arr_sel_inner );
						$child_tblstr = implode(" inner join ",$child_tblarr);
						
						
						$arr_join[]	= "( select $sel_inner from $child_tblstr) ". $table_namedes[$table].$on_ext;
						
				}
				
				
				//var_dump($table_joins); exit;
				foreach($sel_table_joins as $tbl_join)
				{
					
					if($tbl_join['table_f'] == $table){
						$str = " ".$tbl_join['name_tbl'] ." " . $tbl_join['name_des'];
						if($tbl_join["hasyear"] == "YES")
							$str = " ".QLVBDHCommon::Table($tbl_join['name_tbl']) ." " . $tbl_join['name_des'];
						$str.= "  on ".$table_namedes[$table].".".$tbl_join['f_col_join']."=".$tbl_join['name_des'].".".$tbl_join["f_col"];
						$arr_join[] = $str;
					}

				}

				$i_value_refer = 0;
				foreach($columns[$table]["VALUE_REFER"] as $arr_col){
					$tbl_des = "join_refer_".$i_value_refer;
					$arr_sel_value[] = " "."join_refer_".$i_value_refer.".".$arr_col[0]["join_name_tbl_value"]     ." as  ".$arr_col[0]["name_des"];
					
					$type_join = (string)$arr_col[0]["type_join"];
					if($type_join == "")
						$type_join = "left";
					$name_table_join = $arr_col[0]["join_name_tbl"];
					if($arr_col[0]["join_name_tbl_hasyear"] == "YES" ){
						$name_table_join = QLVBDHCommon::Table($name_table_join);
					}
					$arr_join_refer[] = " $type_join join ".$arr_col[0]["join_name_tbl"]." $tbl_des on ".$table_namedes[$table].".".$arr_col[0]["value_column"]."=".$tbl_des.".".$arr_col[0]["join_name_tbl_on"]; 
					$i_value_refer++;
				}
				
				
				foreach($columns[$table]["VALUE_COLUMN"] as $arr_col){
					
					$arr_sel_value[] = " ".$table_namedes[$table].".".$arr_col[0]["value_column"]     ." as  ".$arr_col[0]["name_des"];
				}
				
				foreach($columns[$table]["GROUP_BY"] as $arr_col){
					
					
					foreach($arr_col[0]["params"] as $pr)
						$arr_groupby[] = $table_namedes[$table].".".$pr;
					$arr_sel_value[] = " ".$arr_col[0]["function"]."(".$table_namedes[$table].".".$arr_col[0]["params"][0]     .") as  ".$arr_col[0]["name_des"];
				}
			}//end foreach tables
			
			if(count($arr_join) > 0)
				$join = implode(" inner join ",$arr_join);
			if(count($arr_groupby) >0)
				$group_by.= " group by  " .implode(",",$arr_groupby);
			if(count($arr_sel_value)>0)
				$sel .= implode(",",$arr_sel_value);
			if(count($arr_join_refer) >0)
				$join_refer .= implode(" ",$arr_join_refer);
			
			$sql = "
				select $sel
				from 
				$join
				$join_refer
				$group_by
			";
			
			//var_dump($table_mulitple);
			//echo $sql; exit;
			$this->view->data = array();
			try{
				$dbAdapter = Zend_Db_Table::getDefaultAdapter();
				$qr = $dbAdapter->query($sql);
				$this->view->data = $qr->fetchAll();
				
			}catch(Exception $ex){
			
			}

			$this->view->arr_col_name = $arr_col_name;
			$this->view->arr_col_display = $arr_col_display;
			//var_dump($arr_col_name);
			//var_dump($arr_col_display);
			
			//var_dump($columns["vbd_vanbanden"]["VALUE_REFER"]);
			//echo "table ne";
			//var_dump($tables);
			//echo "column ne";
			//var_dump($columns["vbd_vanbanden"]);
			//exit;
			//buil sql
			//var_dump($params); exit;
		}
	}
?>