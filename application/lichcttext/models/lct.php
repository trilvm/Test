<?php

class lct{
        function addCtManage($data){
            global $db;
            $year = QLVBDHCommon::getYear();
            for($i=0;$i<count($data);$i++){
                if($data[$i]!=''){
                    
                    try {
                         $db->query('INSERT INTO lct2_lead_manage_'.$year.' VALUES("'.$data[$i].'")');

                        } catch (Exception $exc) {
                            echo $exc->getTraceAsString();
                        }

                }
            }
            
        }
        
        function getDataCt(){
            global $db;
            $year = QLVBDHCommon::getYear();
            $r = $db->query('SELECT * FROM lct2_lead_manage_'.$year.' ');
            return $r->fetchAll();
        }
	public static function getPersonal($ngay, $buoi, $idu){
		global $db;
		$sql = "";
		if($buoi==0){
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_PERSONAL")." WHERE NGAY=? and ID_U=? ORDER BY BUOI,ORDERS";
			$r = $db->query($sql,array($ngay,$idu));
		}else{
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_PERSONAL")." WHERE NGAY=? and BUOI=? and ID_U=? ORDER BY ORDERS";
			$r = $db->query($sql,array($ngay,$buoi,$idu));
		}
		//var_dump($r->fetchAll());
		return $r->fetchAll();
	}
	public static function getDepartment($ngay, $buoi, $iddep, $lock){
		global $db;
		if($lock==1){
			if($buoi==0){
				$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE IS_TONGHOP=1 AND NGAY=? and ID_DEP=? ORDER BY BUOI,ORDERS";
				$r = $db->query($sql,array($ngay,$iddep));
			}else{
				$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE IS_TONGHOP=1 AND NGAY=? and BUOI=? and ID_DEP=? ORDER BY ORDERS";
				$r = $db->query($sql,array($ngay,$buoi,$iddep));
			}
		}else{
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE NGAY=? and BUOI=? and ID_DEP=? ORDER BY ORDERS";
			$r = $db->query($sql,array($ngay,$buoi,$iddep));
		}
		//var_dump($r->fetchAll());
		return $r->fetchAll();
	}
	public static function getCorporation($ngay, $buoi, $lock){
		global $db;
		//$lock = 0;
		if($lock==1){
			if($buoi==0){
				$sql = "SELECT THOIGIAN,NOIDUNG,BUOI FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE IS_TONGHOP=1 AND NGAY=? ORDER BY BUOI,ORDERS";
				$r = $db->query($sql,array($ngay));
			}else{
				$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE IS_TONGHOP=1 AND NGAY=? and BUOI=? ORDER BY ORDERS";
				$r = $db->query($sql,array($ngay,$buoi));
			}
		}else{
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE NGAY=? and BUOI=? ORDER BY ORDERS";
			$r = $db->query($sql,array($ngay,$buoi));
		}
		//var_dump($r->fetchAll());
		return $r->fetchAll();
	}
        
        public static function SearchData($LCTTYPE,$noidung,$TYPE,$fromdate,$todate, $buoi,$id_dep,$id_u,$id_dep2,$sorttype,$offset,$limit){
		global $db;
                $arr_where_all = array();
                $where = '';
                $strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
                
                //init
                if ($TYPE=='noidung') $table='NOIDUNG';
                else if ($TYPE=='thanhphan') $table='THANHPHAN';
                else if ($TYPE=='diadiem') $table='DIADIEM';
                
                if ($noidung!='') {
                $where_noidung = "AND MATCH(".$table.") AGAINST('$noidung' IN BOOLEAN MODE) ";                
                $arr_where_all[] = $noidung;                
                }
                
                if($fromdate != ""){
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			 $where_fromdate = " AND NGAY >= '$fromdate' " ; 
			 $arr_where_all[] = $fromdate;                          			 
			}
			
		if($todate != ""){
			$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			$where_todate = " AND NGAY <= '$todate' " ; 			
			$arr_where_all[] = $todate;                        
		}
                if($buoi==0) {$where.= ""; }
                else { $where_buoi = "AND BUOI = $buoi "; 
                        $arr_where_all[] = $buoi;                        
                     }
                if ($id_u) {            
                $where_idu = "AND ID_U=$id_u ";          //ID_U
                $arr_where_all[] = $id_u;                                                            
                }
                
                if ($id_dep2) {            
                $where_iddep2 = "AND ID_DEP=$id_dep2 ";
                $arr_where_all[] = $id_dep2;
                }
                
                if ($sorttype) {            
                $order = "ORDER BY NGAY $sorttype ";
                $arr_where_all[] = $sorttype;
                }
                
		//nếu tìm cá nhân
                if($LCTTYPE=='personaltype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi.$where_idu;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_PERSONAL'));                                                                   
                    $sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_PERSONAL")." 
                            WHERE (1=1) 
                            ".$where."                                                        
                            ".$order."
                            ".$strlimit;
                      $r = $db->query($sql);                 
                }
                else if ($LCTTYPE=='deptype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi.$where_iddep2;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_DEPARTMENT'));                                                                   
                    $sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." 
                            WHERE (1=1) 
                            ".$where."
                            ".$order."
                            ".$strlimit;
                    $r = $db->query($sql);
                    
                }			                
                else if ($LCTTYPE=='corptype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_CORPORATION'));
                    $sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." 
                            WHERE (1=1) 
                            ".$where."
                            ".$order."
                            ".$strlimit;
                    
                    $r = $db->query($sql);
                }
                return $r->fetchAll();              
	}
        
        public static function CountSearchData($LCTTYPE,$noidung,$TYPE,$fromdate,$todate, $buoi,$id_dep,$id_u,$id_dep2,$sorttype){
		global $db;
                $arr_where_all = array();
                $where = '';
                $strlimit = "";		
                
                //init
                if ($TYPE=='noidung') $table='NOIDUNG';
                else if ($TYPE=='thanhphan') $table='THANHPHAN';
                else if ($TYPE=='diadiem') $table='DIADIEM';
                
                if ($noidung!='') {
                $noidung = mysql_escape_string($noidung);
                $where_noidung = "AND MATCH(".$table.") AGAINST('$noidung' IN BOOLEAN MODE) ";                
                $arr_where_all[] = $noidung;                
                }
                
                if($fromdate != ""){
			 $fromdate = implode("-",array_reverse(explode("/",$fromdate."/".QLVBDHCommon::getYear())))." 00:00:00";
			 $where_fromdate = " AND NGAY >= '$fromdate'" ; 
			 $arr_where_all[] = $fromdate;                          			 
			}
			
		if($todate != ""){
			$todate = implode("-",array_reverse(explode("/",$todate."/".QLVBDHCommon::getYear())))." 23:59:59";
			$where_todate = " AND NGAY <= '$todate'" ; 			
			$arr_where_all[] = $todate;                        
		}
                if($buoi==0) {$where.= ""; }
                else { $where_buoi = " AND BUOI = $buoi"; 
                        $arr_where_all[] = $buoi;                        
                     }
                if ($id_u) {            
                $where_idu = " AND ID_U=$id_u";          //ID_U
                $arr_where_all[] = $id_u;                                                            
                }
                
                if ($id_dep2) {            
                $where_iddep2 = " AND ID_DEP=$id_dep2";
                $arr_where_all[] = $id_dep2;
                }
                
                if ($sorttype) {            
                $order = " ORDER BY NGAY $sorttype";
                $arr_where_all[] = $sorttype;
                }
                
		//nếu tìm cá nhân
                if($LCTTYPE=='personaltype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi.$where_idu;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_PERSONAL'));                                                                   
                    $sql = "SELECT count(*) as C FROM ".QLVBDHCommon::Table("LCT2_PERSONAL")." 
                            WHERE (1=1) 
                            ".$where."                                                        
                            ".$order."
                            ".$strlimit;
                      $r = $db->query($sql)->fetch();                 
                }
                else if ($LCTTYPE=='deptype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi.$where_iddep2;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_DEPARTMENT'));                                                                   
                    $sql = "SELECT count(*) as C FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." 
                            WHERE (1=1) 
                            ".$where."
                            ".$order."
                            ".$strlimit;
                    $r = $db->query($sql)->fetch();
                    
                }			                
                else if ($LCTTYPE=='corptype') {
                    $where .= $where_noidung.$where_fromdate.$where_todate.$where_buoi;
                    Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('LCT2_CORPORATION'));
                    $sql = "SELECT count(*) as C FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." 
                            WHERE (1=1) 
                            ".$where."
                            ".$order."
                            ".$strlimit;
                    $r = $db->query($sql)->fetch();
                }
                return $r["C"];            
	}
        
	public static function updatePersonal($data, $idu, $iddep){
		global $db;
		global $auth;
		//var_dump($data);exit;
		$user = $auth->getIdentity();
		for($i=0;$i<count($data['ID_LCT_P']);$i++){
			//kiểm tra trùng db
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_PERSONAL")." WHERE ID_LCT_P=?";
			$r = $db->query($sql,$data['ID_LCT_P'][$i]);
			if($r->rowCount()==1){
				$lct = $r->fetch();
				if(1==2 && $lct['NOIDUNG']==$data['NOIDUNG'][$i] && 
				$lct['THANHPHAN']==$data['THANHPHAN'][$i] && 
				$lct['DIADIEM']==$data['DIADIEM'][$i]){
					//update thu tu
					$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("ORDERS"=>$i),"ID_LCT_P=".$lct['ID_LCT_P']);
				}else{
					//update noi dung
					$db->update(
					QLVBDHCommon::Table("LCT2_PERSONAL"),
						array(
							"IS_GET"=>0,
							"ORDERS"=>$i,
							"NOIDUNG"=>$data['NOIDUNG'][$i],
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null),
							"GHICHU"=>$data['GHICHU'][$i]
						),
						"ID_LCT_P=".$lct['ID_LCT_P']
					);
					//check lich phong da tong hop chua
					$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE ID_LCT_P = ?";
					$r = $db->query($sql,$lct['ID_LCT_P']);
					$id_lct_d = 0;
					if($r->rowCount()==1){
						$dep = $r->fetch();
						if($dep['IS_TONGHOP']==1){
							//khong lam chi het
						}else{
							$id_lct_d = $dep['ID_LCT_D'];
							//cap nhat lai lich
							$db->update(QLVBDHCommon::Table("LCT2_DEPARTMENT"),
							array(
								"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
								"THANHPHAN"=>$data['THANHPHAN'][$i],
								"DIADIEM"=>$data['DIADIEM'][$i],
								"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null)/*,
								"GHICHU"=>$data['GHICHU'][$i]*/
							),
							"ID_LCT_P=".$lct['ID_LCT_P']
							);
						}
					}else{
						//check xem có ma nào đưa lên phòng chứ
						$r = $db->query("
							SELECT 
								* 
							FROM 
								".QLVBDHCommon::Table("LCT2_DEPARTMENT")." 
							WHERE 
								NOIDUNG='".$data['NOIDUNG'][$i]."'
								AND BUOI = ".$data['BUOI'][$i]."
								AND ID_DEP = $iddep
								AND NGAY = '".date("Y-m-d",$data['NGAY'][$i])."'
							"
						);
						if($r->rowCount()==1){
							$lctdep = $r->fetch();
							$db->update(
							QLVBDHCommon::Table("LCT2_DEPARTMENT"),
							array(
								"ID_U_UP"=>$lctdep["ID_U_UP"].",".$idu,
								"THANHPHAN"=>$lctdep["THANHPHAN"].",".$data['THANHPHAN'][$i],
								"NOIDUNG"=>$data['NOIDUNG'][$i].lct::GetUserUp($lctdep["ID_U_UP"].",".(int)$idu)/*,
								"GHICHU"=>$data['GHICHU'][$i]*/
							),
							"ID_LCT_D=".$lctdep["ID_LCT_D"]
							);
							$id_lct_d = $lctdep["ID_LCT_D"];
						}else{
							$db->insert(QLVBDHCommon::Table("LCT2_DEPARTMENT"),array(
							"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
							"ID_U"=>$idu,
							"BUOI"=>$data['BUOI'][$i],
							"ID_LCT_P"=>$lct['ID_LCT_P'],
							"ID_DEP"=>$iddep,
							"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null)/*,
							"GHICHU"=>$data['GHICHU'][$i]*/
							));
							$id_lct_d = $db->lastInsertId(QLVBDHCommon::Table("LCT2_DEPARTMENT"));
						}
					}
					//exit;
					//check tren lich cq
					if($id_lct_d>0){
						$actid = ResourceUserModel::getActionByUrl('lichcttext','index','uptocq');
						if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
							if($data['CHECK'][$i]=="1"){
								$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_D = ?";
								$r = $db->query($sql,$id_lct_d);
								if($r->rowCount()==1){
									$cor = $r->fetch();
									if($cor['IS_TONGHOP']==1){
										//khong lam chi het
									}else{
										//cap nhat lai lich
										$db->update(QLVBDHCommon::Table("LCT2_CORPORATION"),
										array(
											"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
											"THANHPHAN"=>$data['THANHPHAN'][$i],
											"DIADIEM"=>$data['DIADIEM'][$i]/*,
											"GHICHU"=>$data['GHICHU'][$i]	*/
										),
										"ID_LCT_D=".$id_lct_d
										);
									}
								}else{
									$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION"),array(
									"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
									"THANHPHAN"=>$data['THANHPHAN'][$i],
									"DIADIEM"=>$data['DIADIEM'][$i],
									"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
									"ID_U"=>$idu,
									"BUOI"=>$data['BUOI'][$i],
									"ID_LCT_D"=>$id_lct_d/*,
									"GHICHU"=>$data['GHICHU'][$i]*/
									));
								}
								$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("IS_UPTOCQ"=>1),"ID_LCT_P=".$data['ID_LCT_P'][$i]);
							}else{
								$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_D = ?";
								$r = $db->query($sql,$id_lct_d);
								if($r->rowCount()==1){
									$cor = $r->fetch();
									if($cor['IS_TONGHOP']==1){
										//khong lam chi het
										$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("IS_UPTOCQ"=>0),"ID_LCT_P=".$data['ID_LCT_P'][$i]);
									}else{
										//cap nhat lai lich
										$db->delete(QLVBDHCommon::Table("LCT2_CORPORATION"),"ID_LCT_C=".$cor["ID_LCT_C"]);
										$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("IS_UPTOCQ"=>0),"ID_LCT_P=".$data['ID_LCT_P'][$i]);
									}
								}
							}
						}
					}
				}
			}else{
				if($data['THANHPHAN'][$i]==""){
					$data['THANHPHAN'][$i] = $user->FULLNAME;
				}
				//echo $data['NOIDUNG'][$i];exit;
				if(trim($data['NOIDUNG'][$i])!=""){
					$db->insert(QLVBDHCommon::Table("LCT2_PERSONAL"),
						array(
							"NOIDUNG"=>$data['NOIDUNG'][$i],
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"BUOI"=>$data['BUOI'][$i],
							"ORDERS"=>$i,
							"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
							"ID_U"=>$idu,
							"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null),
							"GHICHU"=>$data['GHICHU'][$i]
						)
					);
					//check xem có ma nào đưa lên phòng chứ
					$r = $db->query("
						SELECT 
							* 
						FROM 
							".QLVBDHCommon::Table("LCT2_DEPARTMENT")." 
						WHERE 
							NOIDUNG='".$data['NOIDUNG'][$i]."'
							AND BUOI = ".$data['BUOI'][$i]."
							AND ID_DEP = $iddep
							AND NGAY = '".date("Y-m-d",$data['NGAY'][$i])."'
						"
					);
					if($r->rowCount()==1){
						$lctdep = $r->fetch();
						$db->update(
						QLVBDHCommon::Table("LCT2_DEPARTMENT"),
						array(
							"ID_U_UP"=>$lctdep["ID_U_UP"].",".$idu,
							"THANHPHAN"=>$lctdep["THANHPHAN"].",".$data['THANHPHAN'][$i],
							"NOIDUNG"=>$data['NOIDUNG'][$i].lct::GetUserUp($lctdep["ID_U_UP"].",".$idu)/*,
							"GHICHU"=>$data['GHICHU'][$i]*/
						),
						"ID_LCT_D=".$lctdep["ID_LCT_D"]
						);
					}else{
						$db->insert(QLVBDHCommon::Table("LCT2_DEPARTMENT"),array(
							"NOIDUNG"=>$data['NOIDUNG'][$i],
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
							"ID_U"=>$idu,
							"BUOI"=>$data['BUOI'][$i],
							"ID_DEP"=>$iddep,
							"ID_LCT_P"=>$db->lastInsertId(QLVBDHCommon::Table("LCT2_PERSONAL")),
							"ID_U_UP"=>$idu,
							"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null)/*,
							"GHICHU"=>$data['GHICHU'][$i]*/
						));
					}
					$actid = ResourceUserModel::getActionByUrl('lichcttext','index','uptocq');
					if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){
						if($data['CHECK'][$i]=="1"){
							$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION"),array(
								"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
								"THANHPHAN"=>$data['THANHPHAN'][$i],
								"DIADIEM"=>$data['DIADIEM'][$i],
								"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
								"ID_U"=>$idu,
								"BUOI"=>$data['BUOI'][$i],
								"ID_LCT_D"=>$db->lastInsertId(QLVBDHCommon::Table("LCT2_DEPARTMENT"))/*,
								"GHICHU"=>$data['GHICHU'][$i]*/
								
							));
						}
					}
				}
			}
		}
	}
	public static function deletePersonal($data, $idu){
		global $db;
		//lay lich phong
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE ID_LCT_P = ?";
		$r = $db->query($sql,$data['ID_LCT_P']);
		if($r->rowCount()==1){
			//lay lich cq
			$dep = $r->fetch();
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_D = ?";
			$r = $db->query($sql,$dep['ID_LCT_D']);
			if($r->rowCount()==1){
				$cor = $r->fetch();
				if($dep['IS_TONGHOP']!=1){
					$db->delete(QLVBDHCommon::Table("LCT2_DEPARTMENT"),"ID_LCT_P=".$data['ID_LCT_P']);
				}
				if($cor['IS_TONGHOP']!=1){
					$db->delete(QLVBDHCommon::Table("LCT2_CORPORATION"),"ID_LCT_D=".$dep['ID_LCT_D']);
				}
				$db->delete(QLVBDHCommon::Table("LCT2_PERSONAL"),"ID_LCT_P=".$data['ID_LCT_P']." and ID_U = ".$idu);
			}else{
				if($dep['IS_TONGHOP']!=1){
					$db->delete(QLVBDHCommon::Table("LCT2_DEPARTMENT"),"ID_LCT_P=".$data['ID_LCT_P']);
				}
				$db->delete(QLVBDHCommon::Table("LCT2_PERSONAL"),"ID_LCT_P=".$data['ID_LCT_P']." and ID_U = ".$idu);
			}
		}else{
			$db->delete(QLVBDHCommon::Table("LCT2_PERSONAL"),"ID_LCT_P=".$data['ID_LCT_P']." and ID_U = ".$idu);
		}
	}
	public static function updateDepartment($data,$idu,$iddep,$istonghop){
		global $db;
		global $auth;
		$user = $auth->getIdentity();
		
		//check begindate
		$t = strtotime(implode("-",array_reverse(explode("/",$data["BEGINDATE"]."/".QLVBDHCommon::getYear()))));
		$W = date("W",$t);
		$db->update(QLVBDHCommon::Table("LCT2_DEPARTMENT_HSCV"),array("NOIDUNG"=>$data["BEFOREWEEK"],"ID_DEP"=>$iddep),"WEEK='".$W."'"); //echo $W;exit;
		//var_dump($data['CHECK']);exit;
		for($i=0;$i<count($data['ID_LCT_D']);$i++){
			//kiểm tra trùng db
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT")." WHERE ID_LCT_D=?";
			$r = $db->query($sql,$data['ID_LCT_D'][$i]);
			if($r->rowCount()==1){
				$lct = $r->fetch();
				$tonghop = $istonghop==0?$lct['IS_TONGHOP']:$istonghop;

					//update noi dung
					$db->update(QLVBDHCommon::Table("LCT2_DEPARTMENT"),
						array(
							"ORDERS"=>$i,"IS_TONGHOP"=>$tonghop,
							"NOIDUNG"=>$data['NOIDUNG'][$i],
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null),
							"GHICHU"=>$data["GHICHU"][$i]
						),
						"ID_LCT_D=".$lct['ID_LCT_D']
					);
					//check tren lich cq
					$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_D = ?";
					$r = $db->query($sql,$lct['ID_LCT_D']);
					if($r->rowCount()==1){
						$cor = $r->fetch();
						if($cor['IS_TONGHOP']==1){
							//khong lam chi het
						}else{
							if($data['CHECK'][$i]==1){
								//cap nhat lai lich
								$db->update(QLVBDHCommon::Table("LCT2_CORPORATION"),
								array(
									"NOIDUNG"=>$data['NOIDUNG'][$i],
									"THANHPHAN"=>$data['THANHPHAN'][$i],
									"DIADIEM"=>$data['DIADIEM'][$i],
									"GHICHU"=>$data["GHICHU"][$i]
								),
								"ID_LCT_D=".$lct['ID_LCT_D']
								);
							}else{
								$db->delete(QLVBDHCommon::Table("LCT2_CORPORATION"),"ID_LCT_D=".$lct['ID_LCT_D']);
							}
						}
					}else{
						if($data['CHECK'][$i]==1){
							$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION"),array(
							"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
							"ID_U"=>$idu,
							"BUOI"=>$data['BUOI'][$i],
							"ID_LCT_D"=>$lct['ID_LCT_D'],
							"GHICHU"=>$data["GHICHU"][$i]
							));
						}
					
				}
			}else{
				if(trim($data['NOIDUNG'][$i])!=""){
					$db->insert(QLVBDHCommon::Table("LCT2_DEPARTMENT"),array(
						"NOIDUNG"=>$data['NOIDUNG'][$i],
						"THANHPHAN"=>$data['THANHPHAN'][$i],
						"DIADIEM"=>$data['DIADIEM'][$i],
						"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
						"ID_U"=>$idu,
						"BUOI"=>$data['BUOI'][$i],
						"ID_DEP"=>$iddep,
						"ORDERS"=>$i,"IS_TONGHOP"=>$istonghop,
						"IS_UPTOCQ"=>($data['CHECK'][$i]=="1"?1:null),
						"GHICHU"=>$data["GHICHU"][$i]
					));
					if($data['CHECK'][$i]==1){
						$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION"),array(
							"NOIDUNG"=>$data['NOIDUNG'][$i]." (".$user->FULLNAME.")",
							"THANHPHAN"=>$data['THANHPHAN'][$i],
							"DIADIEM"=>$data['DIADIEM'][$i],
							"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
							"ID_U"=>$idu,
							"BUOI"=>$data['BUOI'][$i],
							"ID_LCT_D"=>$db->lastInsertId(QLVBDHCommon::Table("LCT2_DEPARTMENT")),
							"GHICHU"=>$data["GHICHU"][$i]
						));
					}
				}
			}
		}
	}
	public static function deleteDepartment($data, $iddep){
		global $db;
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_D = ?";
			$r = $db->query($sql,$data['ID_LCT_D']);
			if($r->rowCount()==1){
				$cor = $r->fetch();
				$db->delete(QLVBDHCommon::Table("LCT2_DEPARTMENT"),"ID_LCT_D=".$data['ID_LCT_D']);
				if($cor['IS_TONGHOP']!=1){
					$db->delete(QLVBDHCommon::Table("LCT2_CORPORATION"),"ID_LCT_D=".$data['ID_LCT_D']);
				}
			}else{
				$db->delete(QLVBDHCommon::Table("LCT2_DEPARTMENT"),"ID_LCT_D=".$data['ID_LCT_D']);
			}
	}
	public static function updateCorporation($data,$idu,$istonghop){
		global $db;	
	//	var_dump($data["THOIGIAN"]);exit;
		for($i=0;$i<count($data['ID_LCT_C']);$i++){
			//kiểm tra trùng db
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION")." WHERE ID_LCT_C=?";
			$r = $db->query($sql,$data['ID_LCT_C'][$i]);
			if($r->rowCount()==1){
				$lct = $r->fetch();
				$tonghop = $istonghop==0?$lct['IS_TONGHOP']:$istonghop;
				/*if($lct['NOIDUNG']==$data['NOIDUNG'][$i] && 
				$lct['THANHPHAN']==$data['THANHPHAN'][$i] && 
				$lct['DIADIEM']==$data['DIADIEM'][$i] &&
				$lct['THOIGIAN']==$data['THOIGIAN'][$i] &&
				$lct['CHUTRI']==$data['CHUTRI'][$i]){
					//update thu tu
					$db->update(QLVBDHCommon::Table("LCT2_CORPORATION"),array("ORDERS"=>$i,"IS_TONGHOP"=>$tonghop),"ID_LCT_C=".$lct['ID_LCT_C']);
				}else{*/
					//update noi dung
					$db->update(QLVBDHCommon::Table("LCT2_CORPORATION"),array("ORDERS"=>$i,"IS_TONGHOP"=>$tonghop,"NOIDUNG"=>$data['NOIDUNG'][$i],"THANHPHAN"=>$data['THANHPHAN'][$i],"DIADIEM"=>$data['DIADIEM'][$i],"GHICHU"=>$data['GHICHU'][$i],"THOIGIAN"=>$data['THOIGIAN'][$i],"CHUTRI"=>$data['CHUTRI'][$i]),"ID_LCT_C=".$lct['ID_LCT_C']);
				/*}*/
			}else{
				if(trim($data['NOIDUNG'][$i])!=""){
					$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION"),array(
						"NOIDUNG"=>$data['NOIDUNG'][$i],
						"THANHPHAN"=>$data['THANHPHAN'][$i],
						"DIADIEM"=>$data['DIADIEM'][$i],
						"THOIGIAN"=>$data['THOIGIAN'][$i],
						"NGAY"=>date("Y-m-d",$data['NGAY'][$i]),
						"CHUTRI"=>$data['CHUTRI'][$i],
						"ID_U"=>$idu,
						"BUOI"=>$data['BUOI'][$i],
						"ORDERS"=>$i,"IS_TONGHOP"=>$istonghop,"GHICHU"=>$data['GHICHU'][$i]
					));
				}
			}
		}
	}
        
        
        public static function deleteCorporation($data){
		global $db;
		$db->delete(QLVBDHCommon::Table("LCT2_CORPORATION"),"ID_LCT_C=".$data['ID_LCT_C']);
	}
	public static function setAlarm($data,$idu){
		global $db;
		$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("NHACNHO"=>implode("-",array_reverse(explode("/",$data["NHACNHO"])))." ".(int)$data["NHACNHO_TIME_HOUR"].":".(int)$data["NHACNHO_TIME_MINUTE"].":00","IS_GET"=>0,"BEFORE"=>$data["BEFORE"],"SMS"=>$data["SMS"],"EMAIL"=>$data["EMAIL"],"IS_ALERT"=>1),"ID_LCT_P=".$data["ID_LCT_P_ALARM"]." AND ID_U=".$idu);
	}
	public static function disAlarm($data,$idu){
		global $db;
		$db->update(QLVBDHCommon::Table("LCT2_PERSONAL"),array("NHACNHO"=>null,"IS_GET"=>1),"ID_LCT_P=".$data["ID_LCT_P_ALARM"]." AND ID_U=".$idu);
	}
	public static function GetUserUp($id){
		global $db;
		if($id!=""){
			$r = $db->query("
			SELECT 
				concat(emp.FIRSTNAME,' ',emp.LASTNAME) as NAME 
			FROM
				QTHT_USERS u
				INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
			WHERE
				ID_U in (".$id.")
			");
	
			$data = $r->fetchAll();
			$name = "";
			foreach($data as $item){
				if($name==""){
					$name .= $item["NAME"];
				}else{
					$name .= ",".$item["NAME"];
				}
			}
			return " (".$name.")";
		}else{
			return "";
		}
	}
	public static function GetDuKien($begindate,$enddate){
	global $db;

		$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION_DUKIEN")." WHERE FROMDATE=? AND TODATE=?";
		//echo $begindate.$enddate.$sql;exit;
		$r = $db->query($sql,array($begindate,$enddate));
		if($r->rowCount()==1){
			//update
			$row = $r->fetch();
			return $row['NOIDUNG'];
		}else{
			return "";
		}
	}
	public static function UpdateDuKien($begindate,$enddate,$noidung){
		global $db;
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_CORPORATION_DUKIEN")." WHERE FROMDATE=? AND TODATE=?";
		$r = $db->query($sql,array($begindate,$enddate));
		if($r->rowCount()==1){
			//update
			$row = $r->fetch();
			$db->update(QLVBDHCommon::Table("LCT2_CORPORATION_DUKIEN"),array("NOIDUNG"=>$noidung),"ID_LCT_C_DK=".$row["ID_LCT_C_DK"]);
		}else{
			//insert
			$db->insert(QLVBDHCommon::Table("LCT2_CORPORATION_DUKIEN"),array("FROMDATE"=>$begindate,"TODATE"=>$enddate,"NOIDUNG"=>$noidung));
		}
	}
    
	public static function GetHSCV($begindate){
		global $db;
		global $auth;
		$user = $auth->getIdentity();
		$week = date("W",$begindate-86400);
		$sql = "SELECT * FROM ".QLVBDHCommon::Table("LCT2_DEPARTMENT_HSCV")." WHERE WEEK=? and ID_DEP = ?";
		
		$hscv = $db->query($sql,array($week,$user->ID_DEP))->fetch();
		if(!$hscv['ID_D_HSCV']){
			if(date("W")==$week){
				$sql = "
					select hscv.NAME, pl.*,concat(emp.FIRSTNAME,' ',emp.LASTNAME) as UNAME
					from 
						 ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv
						 inner join ".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi on hscv.ID_HSCV = pi.ID_O
						 inner join
						 (SELECT * FROM (SELECT * FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")." temp ORDER BY temp.ID_PL
						 DESC) temp1 GROUP BY temp1.ID_PI) pl on pl.ID_PI = pi.ID_PI
						 inner join WF_TRANSITIONS tr on tr.ID_T = pl.ID_T
						 inner join QTHT_USERS u on u.ID_U = pl.ID_U_RECEIVE
						 inner join QTHT_EMPLOYEES emp on emp.ID_EMP=u.ID_EMP
						 inner join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP
					where
						 tr.ISLAST <> 1 and dep.ID_DEP = ? and pl.DATESEND > '".date('Y-m-d',$begindate-86400*(7*2-1))." 23:59:59"."'
				";
				
				$hscv = $db->query($sql,$user->ID_DEP)->fetchAll();
				$r = "";
				foreach($hscv as $itemhscv){
					if(QLVBDHCommon::getTreHan($itemhscv["DATESEND"],$itemhscv['HANXULY'])>0){
						$r .= $itemhscv['UNAME'].": ".$itemhscv['NAME']."\n";
					}
				}
				$db->insert(QLVBDHCommon::Table("LCT2_DEPARTMENT_HSCV"),array("NOIDUNG"=>$r,"WEEK"=>$week,"ID_DEP"=>$user->ID_DEP));
				return $r;
			}
		}else{
			return $hscv['NOIDUNG'];
		}
	}
    public static function phanPhoiLCTPersonal($data){
        global $db;
        $arrId = explode(",",$data['IDU']);       
        $arrPhong = explode(",",$data['PHONG']);
        $ngay = implode("-",array_reverse(explode("/",$data['NGAY'])));
        $thoigian = "" ;
        if($data['THOIGIAN'] != ""){
            $thoigian = $data['THOIGIAN'].' : ';
        }
        
        for($y=0;$y<count($arrId);$y++){
            $sql = sprintf("select MAX(ORDERS) as M
                from ".QLVBDHCommon::Table("LCT2_PERSONAL")."
                where NGAY = '%s' and BUOI = %d AND ID_U = %d",$ngay,$data['BUOI'],$arrId[$y]);
            $result = $db->query($sql)->fetch();
            $max = (int)$result['M']++;
            
            if(trim($data['NOIDUNG'])!="" && $arrId[$y] != '' ){
                $db->insert(QLVBDHCommon::Table("LCT2_PERSONAL"),
                    array(
                        "NOIDUNG"=>$thoigian.$data['NOIDUNG'],
                        "THANHPHAN"=>$data['CHUTRI'] . "," . $data['THANHPHAN'],
                        "DIADIEM"=>$data['DIADIEM'],
                        "BUOI"=>$data['BUOI'],
                        "ORDERS"=>$max,
                        "NGAY"=>$ngay,
                        "ID_U"=>$arrId[$y],
                        "IS_UPTOCQ"=>null,
                        "GHICHU"=>$data['GHICHU']
                    )
                );
            }
        }
        for($n=0;$n<count($arrPhong);$n++){
            $sql = sprintf("select MAX(ORDERS) as M
                from ".QLVBDHCommon::Table("lct2_department")."
                where NGAY = '%s' and BUOI = %d AND ID_DEP = %d",$ngay,$data['BUOI'],$arrPhong[$n]);
            $result = $db->query($sql)->fetch();
            $max = (int)$result['M']++;
            
            if(trim($data['NOIDUNG'])!="" && trim($arrPhong[$n]) != ''){
                $db->insert(QLVBDHCommon::Table("lct2_department"),
                    array(
                        "NOIDUNG"=>$thoigian.$data['NOIDUNG'],
                        "THANHPHAN"=>$data['CHUTRI'] . "," . $data['THANHPHAN'],
                        "DIADIEM"=>$data['DIADIEM'],
                        "BUOI"=>$data['BUOI'],
                        "ORDERS"=>$max,
                        "NGAY"=>$ngay,
                        "ID_DEP"=>$arrPhong[$n],
                        "ID_U"=>null,
                        "IS_UPTOCQ"=>null,
                        "GHICHU"=>$data['GHICHU']
                    )
                );
            }
        }        
    }
}
