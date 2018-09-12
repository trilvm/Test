<?php
/**
 * @name WF API + ENGINE
 * Các function dành riêng cho WF. Mọi function đều là static.
 *
 * @author Nếu các đoạn code dưới đây chạy tốt thì nó được viết bởi Nguyennd. Nếu không: không biết ai viết mà tệ quá.
 * @deprecated 23/09/2009
 * @filesource wfengine.php
 * @access static
 */
require_once('qtht/models/UsersModel.php');
require_once 'qtht/models/DepartmentsModel.php';
require_once "giaoviec/models/giaoviecservice.php";
class WFEngine{
	
	static $WFTYPE_USER = 1;
	static $WFTYPE_GROUP = 2;
	static $WFTYPE_DEP = 3;
	
	/**
	 * Tạo process item.
	 * Sau khi khởi tạo một process item thì hệ thống cũng tự động gọi hàm sendnextuser
	 * Yêu cầu: Process phải có 1 transition là isfirst và 1 activity hành động (thêm mới, add)
	 * Cách sử dụng:
	 * 		$arr = CreateProcess("TNXLVBD",1234,"Cong việc a",$user_id);
	 * @param string $process_alias
	 * @param number $object_id
	 * @param string $name
	 * @return int
	 */
	static function CreateProcess($process_alias,$object_id,$name,$user_create,$user_receive,$name1,$hanxuly){
		try{
			global $db;
			//Get and check process alias
			$r = $db->query("
				select 
					p.ID_P,p.ID_C,c.ALIAS 
				from 
					wf_processes p inner join wf_classes c on p.ID_C=c.ID_C 
				where p.ALIAS=?",array($process_alias));
			if($r->rowCount()==0){
				return -1001;
			}
			$process = $r->fetch();
			$r->closeCursor();
			
			//Check name
			if(trim($name)=="" || strlen($name)>1024){
				return -1004;
			}
			//Lấy AID đầu tiên của process
			$r = $db->query("select ID_P,ID_T,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ISFIRST=1 and ID_P=?",array($process['ID_P']));
			$activity = $r->fetch();
			$r->closeCursor();
			//kiem tra quyen truy cap BEGIN ACTIVITY doi voi nguoi gui
			if(WFEngine::HaveAccessableActivity($activity["ID_A_BEGIN"],$user_create)){
				//insert vào process item
				if($user_receive==0 || $user_receive==""){
					
					//Kiem tra quyen truy cap END ACTIVITY với người gui
					if(WFEngine::HaveAccessableActivity($activity["ID_A_END"],$user_create)){
						
						$r = $db->insert(QLVBDHCommon::Table("wf_processitems"),array("NAME"=>$name,"ID_O"=>$object_id,"ID_A"=>$activity["ID_A_END"],"ID_P"=>$process["ID_P"],"ID_U"=>$user_create,"LASTCHANGE"=>date("Y-m-d H:i:s")));
						$r = $db->lastInsertId();
						
						//ghi Log tao process
						WFEngine::WriteLog(
							$r,
							$activity["ID_T"],
							$user_create,
							0,
							$activity["ID_A_BEGIN"],
							$activity["ID_A_END"],
							$activity["ID_P"],
							0,
							0
						);
					}else{
						return -1003;
					}
				}else{
					//Kiem tra quyen truy cap voi ID_A_END cuar nguoi nhan
					if(WFEngine::HaveAccessableActivity($activity["ID_A_END"],$user_receive)){
					//phuongpt - update hạn xử lý table wf_processitems
						$hanxuly = $hanxuly==""?0:$hanxuly;
						$enddate = QLVBDHCommon::addDate(time(),($hanxuly==0?999:$hanxuly));
						
						$r = $db->insert(QLVBDHCommon::Table("wf_processitems"),array("NAME"=>$name,"ID_O"=>$object_id,"ID_A"=>$activity["ID_A_END"],"ID_P"=>$process["ID_P"],"ID_U"=>$user_receive,"LASTCHANGE"=>date("Y-m-d H:i:s")));
						$r = $db->lastInsertId();
						if($r > 0)
						{
							$db->update(QLVBDHCommon::Table("wf_processitems"),array("DATEEND" => date("Y-m-d H:i:s",$enddate)),"ID_PI=".$r);
						}
						//ghi Log tao process
						WFEngine::WriteLog(
							$r,
							$activity["ID_T"],
							$user_create,
							0,
							$activity["ID_A_BEGIN"],
							$activity["ID_A_END"],
							$activity["ID_P"],
							0,
							0,
							"Thêm mới",
							0
						);
						
						WFEngine::WriteLog(
							$r,
							$activity["ID_T"],
							$user_create,
							$user_receive,
							$activity["ID_A_BEGIN"],
							$activity["ID_A_END"],
							$activity["ID_P"],
							0,
							0,
							$name1,
							$hanxuly
						);
					}else{
						return -1003;
					}
				}
			}else{
				return -1002;
			}
			return $r;
		}catch(Exception $ex){
			echo $ex->__toString();
			return -0001;
		}
	}
	/**
	 * Tạo process item.
	 * Sau khi khởi tạo một process item thì hệ thống cũng tự động gọi hàm sendnextuser
	 * Yêu cầu: Process phải có 1 transition là isfirst và 1 activity hành động (thêm mới, add)
	 * Cách sử dụng:
	 * 		$arr = CreateProcess("TNXLVBD",1234,"Cong việc a",$user_id);
	 * @param string $process_alias
	 * @param number $object_id
	 * @param string $name
	 * @return int
	 */
	static function CreateProcess2($process_alias,$object_id,$name,$user_create,$id_receive,$type){
		try{
			global $db;
			//Get and check process alias
			$r = $db->query("
				select 
					p.ID_P,p.ID_C,c.ALIAS 
				from 
					wf_processes p inner join wf_classes c on p.ID_C=c.ID_C 
				where p.ALIAS=?",array($process_alias));
			if($r->rowCount()==0){
				return -1001;
			}
			$process = $r->fetch();
			$r->closeCursor();
			
			//Check name
			if(trim($name)=="" || strlen($name)>128){
				return -1004;
			}
			//Lấy AID đầu tiên của process
			$r = $db->query("select ID_P,ID_T,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ISFIRST=1 and ID_P=?",array($process['ID_P']));
			$activity = $r->fetch();
			$r->closeCursor();
			//kiem tra quyen truy cap BEGIN ACTIVITY doi voi nguoi gui
			if(WFEngine::HaveAccessableActivity($activity["ID_A_BEGIN"],$user_create)){
				//Kiem tra quyen truy cap voi ID_A_END cua nguoi nhan
				$ok = false;
				if($type==WFEngine::$WFTYPE_USER){
					$ok = WFEngine::HaveAccessableActivity($activity["ID_A_END"],$id_receive);
				}else if($type==WFEngine::$WFTYPE_DEP){
					$ok = WFEngine::HaveAccessableActivityDep($activity["ID_A_END"],$id_receive);
				}else if($type==WFEngine::$WFTYPE_GROUP){
					$ok = WFEngine::HaveAccessableActivityGroup($activity["ID_A_END"],$id_receive);
				}
				if($ok){
					$r = $db->insert(
						QLVBDHCommon::Table("wf_processitems"),
						array("NAME"=>$name,
						"ID_O"=>$object_id,
						"ID_A"=>$activity["ID_A_END"],
						"ID_P"=>$process["ID_P"],
						"ID_U"=>$type==WFEngine::$WFTYPE_USER?$id_receive:0,
						"ID_G"=>$type==WFEngine::$WFTYPE_GROUP?$id_receive:0,
						"ID_DEP"=>$type==WFEngine::$WFTYPE_DEP?$id_receive:0,
						"LASTCHANGE"=>date("Y-m-d H:i:s")));
					$r = $db->lastInsertId();
					
					//ghi Log tao process
					WFEngine::WriteLog(
						$r,
						$activity["ID_T"],
						$user_create,
						0,
						$activity["ID_A_BEGIN"],
						$activity["ID_A_END"],
						$activity["ID_P"],
						0,
						0,
						0,
						0
					);
					WFEngine::WriteLog(
						$r,
						$activity["ID_T"],
						$user_create,
						$type==WFEngine::$WFTYPE_USER?$id_receive:0,
						$activity["ID_A_BEGIN"],
						$activity["ID_A_END"],
						$activity["ID_P"],
						$type==WFEngine::$WFTYPE_GROUP?$id_receive:0,
						$type==WFEngine::$WFTYPE_DEP?$id_receive:0,
						0,
						0
					);
				}else{
					return -1003;
				}
			}else{
				return -1002;
			}
			return $r;
		}catch(Exception $ex){
			$ex->__toString();
			return -0001;
		}
	}
	/**
	 * Lấy danh sách các Transition từ 1 process item id
	 *
	 * @param int $process_item_id
	 * @param int $user_id
	 * @return array
	 */
	static function GetNextTransitions($process_item_id,$user_id){
		global $db;
		$r=$db->query("
		SELECT TR.ID_T,TR.NAME,TP.LINK, TR.ID_A_END,ISLAST from
			WF_TRANSITIONS TR
			inner join (
				SELECT AC.ID_A FROM
					FK_USERS_GROUPS G
					INNER JOIN QTHT_USERS U ON U.ID_U = G.ID_U
					INNER JOIN WF_ACTIVITYACCESSES AC ON G.ID_G = AC.ID_G
				WHERE U.ID_U = ?
				UNION
				SELECT AC.ID_A FROM
					QTHT_DEPARTMENTS DEP
					INNER JOIN QTHT_EMPLOYEES EMP ON EMP.ID_DEP = DEP.ID_DEP
					INNER JOIN QTHT_USERS U ON U.ID_EMP = EMP.ID_EMP
					INNER JOIN WF_ACTIVITYACCESSES AC ON DEP.ID_DEP = AC.ID_DEP
				WHERE U.ID_U = ?
				UNION
				SELECT AC.ID_A FROM
					WF_ACTIVITYACCESSES AC
				WHERE AC.ID_U = ?
			) T on TR.ID_A_BEGIN=T.ID_A
			inner join wf_transitionpools TP on tp.ID_TP=TR.ID_TP
			inner join ".QLVBDHCommon::Table("wf_processitems")." PR on pr.ID_A=tr.ID_A_BEGIN
		WHERE
		     PR.ID_PI=?
		",array($user_id,$user_id,$user_id,$process_item_id));	
		
			return $r->fetchAll();
		
	}
	
	static function GetNextTransitions_nb($user_id,$id_a){
		global $db;
		$r=$db->query("
		SELECT TR.ID_T,TR.NAME,TP.LINK, TR.ID_A_END,ISLAST from
			WF_TRANSITIONS TR
			inner join (
				SELECT AC.ID_A FROM
					FK_USERS_GROUPS G
					INNER JOIN QTHT_USERS U ON U.ID_U = G.ID_U
					INNER JOIN WF_ACTIVITYACCESSES AC ON G.ID_G = AC.ID_G
				WHERE U.ID_U = ?
				UNION
				SELECT AC.ID_A FROM
					QTHT_DEPARTMENTS DEP
					INNER JOIN QTHT_EMPLOYEES EMP ON EMP.ID_DEP = DEP.ID_DEP
					INNER JOIN QTHT_USERS U ON U.ID_EMP = EMP.ID_EMP
					INNER JOIN WF_ACTIVITYACCESSES AC ON DEP.ID_DEP = AC.ID_DEP
				WHERE U.ID_U = ?
				UNION
				SELECT AC.ID_A FROM
					WF_ACTIVITYACCESSES AC
				WHERE AC.ID_U = ?
			) T on TR.ID_A_BEGIN=T.ID_A
			inner join wf_transitionpools TP on tp.ID_TP=TR.ID_TP			
		WHERE
		      tr.ID_A_BEGIN=?
		",array($user_id,$user_id,$user_id,$id_a));	
		
			return $r->fetchAll();
		
	}

	static function GetNextTransitionsByTransition($transition_id){
		global $db;
		
		$r=$db->query("
			SELECT 
				tr1.* 
			FROM
				WF_TRANSITIONS TR
				INNER JOIN WF_TRANSITIONS TR1 on tr.`ID_A_END` = tr1.`ID_A_BEGIN`
			WHERE
				tr.`ID_T` = ?
			",
			array($transition_id)
		);
		return $r->fetch();
	}
	static function GetAllNextTransitionsByTransition($transition_id){
		global $db;
		
		$r=$db->query("
			SELECT 
				tr1.*,TP.ALIAS
			FROM
				WF_TRANSITIONS TR
				INNER JOIN WF_TRANSITIONS TR1 on tr.`ID_A_END` = tr1.`ID_A_BEGIN`
				INNER JOIN WF_TRANSITIONPOOLS TP on tr1.ID_TP = TP.ID_TP
			WHERE
				tr.`ID_T` = ?
			",
			array($transition_id)
		);
		return $r->fetchAll();
	}
	/**
	 * Kiểm tra quyền truy cập đến một process item của một user
	 * Ngoài ra nó có có chức năng check xem process item có tồn tại không
	 *
	 * @param int $process_item_id
	 * @param int $user_id
	 * @return bool true:có quyền
	 * 				flase: không có quyền
	 */
	static function HaveAccessableProcessItem($process_item_id,$user_id){
		global $db;
		
		$r = $db->query("
			SELECT U.ID_U FROM
				QTHT_DEPARTMENTS DEP
				INNER JOIN QTHT_EMPLOYEES EMP ON EMP.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = EMP.ID_EMP
				INNER JOIN WF_ACTIVITYACCESSES AC ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN ".QLVBDHCommon::Table("WF_PROCESSITEMS")." P ON P.ID_A = AC.ID_A
			WHERE U.ID_U = ? AND P.ID_PI=?
			UNION
			SELECT U.ID_U FROM
				FK_USERS_GROUPS G
				INNER JOIN QTHT_USERS U ON U.ID_U = G.ID_U
				INNER JOIN WF_ACTIVITYACCESSES AC ON G.ID_G = AC.ID_G
				INNER JOIN ".QLVBDHCommon::Table("WF_PROCESSITEMS")." P ON P.ID_A = AC.ID_A
			WHERE U.ID_U = ? AND P.ID_PI=?
			UNION
			SELECT AC.ID_U FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN ".QLVBDHCommon::Table("WF_PROCESSITEMS")." P ON P.ID_A = AC.ID_A
			WHERE AC.ID_U = ? AND P.ID_PI=? AND P.ID_U=?
		",array($user_id,$process_item_id,$user_id,$process_item_id,$user_id,$process_item_id,$user_id));
		if($r->rowCount()==0){
			return false;
		}
		
		return true;
	}
	static function HaveAccessableProcess($process_id,$user_id){
		global $db;
		return true;
	}
	/**
	 * Kiểm tra quyền truy cập đến một activity của một user
	 * Ngoài ra còn sử dụng để biết 1 lần sendnextuser có hợp lệ phái người nhận ko. 
	 * 
	 * @param int $activity_id
	 * @param int $user_id
	 * @return bool true:có quyền
	 * 				flase: không có quyền
	 */
	static function HaveAccessableActivity($activity_id,$user_id){
		global $db;
		
		$r = $db->query("
			SELECT U.ID_U FROM
				FK_USERS_GROUPS G
				INNER JOIN QTHT_USERS U ON U.ID_U = G.ID_U
				INNER JOIN WF_ACTIVITYACCESSES AC ON G.ID_G = AC.ID_G
				WHERE U.ID_U=? AND AC.ID_A=?
			UNION
			SELECT U.ID_U FROM
				QTHT_DEPARTMENTS DEP
				INNER JOIN QTHT_EMPLOYEES EMP ON EMP.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = EMP.ID_EMP
				INNER JOIN WF_ACTIVITYACCESSES AC ON DEP.ID_DEP = AC.ID_DEP
				WHERE U.ID_U=? AND AC.ID_A=?
			UNION
			SELECT AC.ID_U FROM
				WF_ACTIVITYACCESSES AC
			WHERE AC.ID_U=? AND AC.ID_A=?
		",array($user_id,$activity_id,$user_id,$activity_id,$user_id,$activity_id));
		/*echo "
			SELECT U.ID_U FROM
				FK_USERS_GROUPS G
				INNER JOIN QTHT_USERS U ON U.ID_U = G.ID_U
				INNER JOIN WF_ACTIVITYACCESSES AC ON G.ID_G = AC.ID_G
				WHERE U.ID_U=$user_id AND AC.ID_A=$activity_id
			UNION
			SELECT U.ID_U FROM
				QTHT_DEPARTMENTS DEP
				INNER JOIN QTHT_EMPLOYEES EMP ON EMP.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = EMP.ID_EMP
				INNER JOIN WF_ACTIVITYACCESSES AC ON DEP.ID_DEP = AC.ID_DEP
				WHERE U.ID_U=$user_id AND AC.ID_A=$activity_id
			UNION
			SELECT AC.ID_U FROM
				WF_ACTIVITYACCESSES AC
			WHERE AC.ID_U=$user_id AND AC.ID_A=$activity_id
		";*/
		if($r->rowCount()==0){
			return false;
		}
		return true;
	}
	/**
	 * Kiểm tra quyền truy cập đến một activity của một user
	 * Ngoài ra còn sử dụng để biết 1 lần sendnextuser có hợp lệ phái người nhận ko. 
	 * 
	 * @param int $activity_id
	 * @param int $user_id
	 * @return bool true:có quyền
	 * 				flase: không có quyền
	 */
	static function HaveAccessableActivityGroup($activity_id,$group_id){
		global $db;
		//echo $activity_id.'-'.$group_id;exit;
		$r = $db->query("
			SELECT ID_G FROM
				WF_ACTIVITYACCESSES
				WHERE ID_G=? AND ID_A=?
		",array($group_id,$activity_id));
		if($r->rowCount()==0){
			return false;
		}
		return true;
	}
	/**
	 * Kiểm tra quyền truy cập đến một activity của một user
	 * Ngoài ra còn sử dụng để biết 1 lần sendnextuser có hợp lệ phái người nhận ko. 
	 * 
	 * @param int $activity_id
	 * @param int $user_id
	 * @return bool true:có quyền
	 * 				flase: không có quyền
	 */
	static function HaveAccessableActivityDep($activity_id,$dep_id){
		global $db;
		
		$r = $db->query("
			SELECT ID_DEP FROM
				WF_ACTIVITYACCESSES
				WHERE ID_DEP=? AND ID_A=?
		",array($dep_id,$activity_id));
		if($r->rowCount()==0){
			return false;
		}
		return true;
	}
	/**
	 * Kiểm tra xem từ 1 process item khi send có đúng theo quy trình không
	 * 
	 * @param int $process_item_id
	 * @param int $next_transition_id
	 */
	static function HaveSendAble($process_item_id,$next_transition_id){
		global $db;
		
		$r = $db->query("
			SELECT* FROM
				".QLVBDHCommon::Table("wf_processitems")." PR
				INNER JOIN `WF_TRANSITIONS` TR ON PR.`ID_A`=TR.`ID_A_BEGIN`
			WHERE 
				PR.`ID_PI`=? AND TR.`ID_T`=?
		",array($process_item_id,$next_transition_id));
		if($r->rowCount()>0)return true;
		return false;
	}
/**
	 * Kiểm tra xem từ 1 process item khi send có đúng theo quy trình không
	 * 
	 * @param int $process_item_id
	 * @param int $next_transition_id
	 */
	static function HaveSendAbleByTransition($next_transition_id,$user_receive){
		global $db;
		
		$r = $db->query("select ID_P,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ID_T=?",array($next_transition_id));
		$transition = $r->fetch();
		if(WFEngine::HaveAccessableActivity($transition["ID_A_END"],$user_receive)){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * Chuyển process item cho người khác
	 *
	 * @param int $process_item_id
	 * @param int $next_transition_id
	 * @param int $user_send
	 * @param int $user_receive
	 * @return bool true: chuyển thành công
	 * 				false: chuyển không thành công (do hack);
	 */
	static function SendNextUser($process_item_id,$next_transition_id,$user_send,$user_receive,$noidung,$hanxuly){
		try{
			global $db;
			
			$enddate = QLVBDHCommon::addDate(time(),($hanxuly==0?999:$hanxuly));
			//Check quyen truy cap process_item_id
			if(WFEngine::HaveAccessableProcessItem($process_item_id,$user_send)){
				//Lay activity tiep theo
				$r = $db->query("select ID_P,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ID_T=?",array($next_transition_id));
				$transition = $r->fetch();
				
				//Kiem tra ton tai transition
				if($r->rowCount()==1){
					$r->closeCursor();
					//Kiem tra tinh hop le cua quy trinh
					if(WFEngine::HaveSendAble($process_item_id,$next_transition_id)){
						//Kiem tra quyen truy cap activity cua nguoi nhan
						if(WFEngine::HaveAccessableActivity($transition["ID_A_END"],$user_receive)){
							$r = $db->update(QLVBDHCommon::Table("wf_processitems"),array("ID_A"=>$transition["ID_A_END"],"ID_U"=>$user_receive,"LASTCHANGE"=>date("Y-m-d H:i:s"),"DATEEND"=>date("Y-m-d H:i:s",$enddate)),"ID_PI=".$process_item_id);
							WFEngine::WriteLog(
								$process_item_id,
								$next_transition_id,
								$user_send,
								$user_receive,
								$transition["ID_A_BEGIN"],
								$transition["ID_A_END"],
								$transition["ID_P"],
								0,
								0,
								$noidung,
								$hanxuly
							);
						}else{
							return -1003;
						}
					}else{
						return -1007;
					}
				}		
			}else{
				return -1006;
			}
			return 1;
		}catch(Exception $ex){
			return -1;
		}
	}
/**
	 * Chuyển process item cho người khác
	 *
	 * @param int $process_item_id
	 * @param int $next_transition_id
	 * @param int $user_send
	 * @param int $user_receive
	 * @return bool true: chuyển thành công
	 * 				false: chuyển không thành công (do hack);
	 */
	static function SendNextUserByObjectId($idobject,$next_transition_id,$user_send,$user_receive,$noidung,$hanxuly){
		try{
			global $db;
			$enddate = QLVBDHCommon::addDate(time(),($hanxuly==0?999:$hanxuly));
			$sql="
				SELECT 
					ID_PI 
				FROM
					".QLVBDHCommon::Table("WF_PROCESSITEMS")."
				WHERE
					ID_O=?
			";
			
			$r = $db->query($sql,array($idobject));
			$process = $r->fetch();
			$process_item_id = $process["ID_PI"];
			
			//Check quyen truy cap process_item_id
			if(WFEngine::HaveAccessableProcessItem($process_item_id,$user_send)){
				//Lay activity tiep theo
				$r = $db->query("select ID_P,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ID_T=?",array($next_transition_id));
				$transition = $r->fetch();
				
				//Kiem tra ton tai transition
				if($r->rowCount()==1){
					$r->closeCursor();
					//Kiem tra tinh hop le cua quy trinh
					if(WFEngine::HaveSendAble($process_item_id,$next_transition_id)){
						//Kiem tra quyen truy cap activity cua nguoi nhan
						if(WFEngine::HaveAccessableActivity($transition["ID_A_END"],$user_receive)){
							$r = $db->update(QLVBDHCommon::Table("wf_processitems"),array("ID_A"=>$transition["ID_A_END"],"ID_U"=>$user_receive,"LASTCHANGE"=>date("Y-m-d H:i:s"),"DATEEND"=>date("Y-m-d H:i:s",$enddate)),"ID_PI=".$process_item_id);
							WFEngine::WriteLog(
								$process_item_id,
								$next_transition_id,
								$user_send,
								$user_receive,
								$transition["ID_A_BEGIN"],
								$transition["ID_A_END"],
								$transition["ID_P"],
								0,
								0,
								$noidung,
								$hanxuly
							);
						}else{
							return -1003;
						}
					}else{
						return -1007;
					}
				}		
			}else{
				return -1006;
			}
			return 1;
		}catch(Exception $ex){
			echo $ex->__toString();
			return -1;
		}
	}
	static function SendNextUserByObjectId2($idobject,$next_transition_id,$user_send,$id_receive,$type,$noidung,$hanxuly,$sms,$email){
		try{
			global $db;
			$enddate = QLVBDHCommon::addDate(time(),($hanxuly==0?999:$hanxuly));
			$sql="
				SELECT 
					ID_PI 
				FROM
					".QLVBDHCommon::Table("WF_PROCESSITEMS")."
				WHERE
					ID_O=?
			";
			
			$r = $db->query($sql,array($idobject));
			$process = $r->fetch();
			$process_item_id = $process["ID_PI"];
			//kiem tra nguoi dai dien
			if($type==WFEngine::$WFTYPE_GROUP){
                            
				$sql = "SELECT ID_U_DAIDIEN FROM QTHT_GROUPS WHERE ID_G=? AND ID_U_DAIDIEN>0";
				$r = $db ->query($sql,$id_receive);
				if($r->rowCount()==1){
					$g = $r->fetch();
					$id_receive = $g['ID_U_DAIDIEN'];
					$type = WFEngine::$WFTYPE_USER;
				}
			}else if($type==WFEngine::$WFTYPE_DEP){
				$sql = "SELECT ID_U_DAIDIEN FROM QTHT_DEPARTMENTS WHERE ID_DEP=? AND ID_U_DAIDIEN>0";
				$r = $db ->query($sql,$id_receive);
				if($r->rowCount()==1){
					$dep = $r->fetch();
					$id_receive = $dep['ID_U_DAIDIEN'];
					$type = WFEngine::$WFTYPE_USER;
				}
			}
			//Check quyen truy cap process_item_id
			if(WFEngine::HaveAccessableProcessItem($process_item_id,$user_send)){
				//Lay activity tiep theo
				$r = $db->query("select ID_P,ID_A_BEGIN,ID_A_END from WF_TRANSITIONS where ID_T=?",array($next_transition_id));
				$transition = $r->fetch();
//                                echo '<pre>';
//				print_r($transition);exit;
				//Kiem tra ton tai transition
				if($r->rowCount()==1){
					$r->closeCursor();
					//Kiem tra tinh hop le cua quy trinh
					if(WFEngine::HaveSendAble($process_item_id,$next_transition_id)){
						//Kiem tra quyen truy cap activity cua nguoi nhan
						$ok = false;
						if($type==WFEngine::$WFTYPE_USER){
							$ok = WFEngine::HaveAccessableActivity($transition["ID_A_END"],$id_receive);
						}else if($type==WFEngine::$WFTYPE_DEP){
							$ok = WFEngine::HaveAccessableActivityDep($transition["ID_A_END"],$id_receive);
						}else if($type==WFEngine::$WFTYPE_GROUP){
							$ok = WFEngine::HaveAccessableActivityGroup($transition["ID_A_END"],$id_receive);
						}
                                               //echo $ok = WFEngine::HaveAccessableActivityGroup($transition["ID_A_END"],$id_receive);exit;
						if($ok){
							$r = $db->update(
								QLVBDHCommon::Table("wf_processitems"),
								array(
									"ID_A"=>$transition["ID_A_END"],
									"ID_U"=>$type==WFEngine::$WFTYPE_USER?$id_receive:0,
									"ID_G"=>$type==WFEngine::$WFTYPE_GROUP?$id_receive:0,
									"ID_DEP"=>$type==WFEngine::$WFTYPE_DEP?$id_receive:0,
									"LASTCHANGE"=>date("Y-m-d H:i:s"),
									"DATEEND"=>date("Y-m-d H:i:s",$enddate)
								),
								"ID_PI=".$process_item_id
							);
							WFEngine::WriteLog(
								$process_item_id,
								$next_transition_id,
								$user_send,
								$type==WFEngine::$WFTYPE_USER?$id_receive:0,
								$transition["ID_A_BEGIN"],
								$transition["ID_A_END"],
								$transition["ID_P"],
								$type==WFEngine::$WFTYPE_GROUP?$id_receive:0,
								$type==WFEngine::$WFTYPE_DEP?$id_receive:0,
								$noidung,
								$hanxuly,
								$sms,
								$email,
                                                                $idobject
							);
						}else{
							return -1003;
						}
					}else{
						return -1007;
					}
				}		
			}else{
				return -1006;
			}
			return 1;
		}catch(Exception $ex){
			echo $ex->__toString();
			return -1;
		}
	}
	/**
	 * Lấy id của log khởi nguồn mọi rắc rối
	 */
	static function GetStartLogIdByEndAt($process_item_id,$transition_id){
		//Lấy END_AT tu transition_id
		global $db;
		$sql = "SELECT * FROM WF_TRANSITIONS WHERE END_AT=?";
		$r=$db->query($sql,$transition_id);
		if($r->rowCount()>=1){
			$result = $r->fetchAll();
			$logid = array();
			foreach($result as $item){
				$transitionid[] = $item['ID_T'];
			}
			$sql = "SELECT ID_PL,HANXULY,DATESEND,ID_U_RECEIVE FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")." WHERE ID_T in (".implode(",",$transitionid).") AND ID_PI = ?";
			$r = $db->query($sql,$process_item_id);
			return $r->fetchAll();
		}else{
			return 0;
		}
	}
/**
	 * Lấy id của log khởi nguồn mọi rắc rối
	 */
	static function GetStartLogIdByProcessItem($process_item_id){
		//Lấy END_AT tu transition_id
		global $db;
		$sql = "SELECT 
			tr.ID_T 
		FROM 
			WF_TRANSITIONS tr
			inner join ".QLVBDHCommon::Table("WF_PROCESSITEMS")." p on p.ID_P = tr.ID_P
		WHERE
			p.ID_PI = ? AND
			tr.END_AT > 0";
		$r=$db->query($sql,$process_item_id);
		if($r->rowCount()>=1){
			$result = $r->fetchAll();
			$logid = array();
			foreach($result as $item){
				$transitionid[] = $item['ID_T'];
			}
			$sql = "SELECT ID_PL,HANXULY,DATESEND,ID_U_RECEIVE,ID_U_SEND,ID_G_RECEIVE,ID_DEP_RECEIVE FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")." WHERE ID_T in (".implode(",",$transitionid).") AND ID_PI = ? AND TRE IS NULL ";
			$r = $db->query($sql,$process_item_id);
			return $r->fetchAll();
		}else{
			return 0;
		}
	}
/**
	 * Lấy id của log khởi nguồn mọi rắc rối
	 */
	static function CheckUpdateTre($transition_id){
		//Lấy END_AT tu transition_id
		global $db;
		$sql = "SELECT * FROM WF_TRANSITIONS WHERE END_AT > 0 AND ID_T = ?";
		$r=$db->query($sql,$transition_id);
		if($r->rowCount()>=1){
			return 0;
		}else{
			return 1;
		}
	}
	/**
	 * Cập nhật Log
	 *  
	 * @param int $process_item_id
	 * @param int $user_send
	 * @param int $user_receive
	 */
	static function WriteLog($process_item_id,$transition_id,$user_send,$user_receive,$activity_begin,$activity_end,$process,$group_receive,$dep_receive,$noidung,$hanxuly,$sms,$email){
		global $db;
		global $auth;
		$user = $auth->getIdentity();
		//tìm dòng luân chuyển trước
		$lastpl = WFEngine::GetStartLogIdByEndAt($process_item_id,$transition_id);
		$tre_last = 0;
		if(is_array($lastpl)){
			foreach($lastpl as $item){
				if($item['DATESEND']!="" && $item['HANXULY']>0){
					$tre = QLVBDHCommon::getTreHan($item['DATESEND'],$item['HANXULY']);
					$tre_last = $tre;
					$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>$tre),"ID_PL=".$item['ID_PL']);
				}else if($item['HANXULY']==0){
					$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>0),"ID_PL=".$item['ID_PL']);
				}
			}
			$lastpl = WFEngine::GetCurrentTransitionInfoByIdProcess($process_item_id);
			if(WFEngine::CheckUpdateTre($lastpl['ID_T'])==1){
				if($lastpl['DATESEND']!="" && $lastpl['HANXULY']>0){
					$tre = QLVBDHCommon::getTreHan($lastpl['DATESEND'],$lastpl['HANXULY']);
					$tre_last = $tre;
					$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>$tre),"ID_PL=".$lastpl['ID_PL']);
				}
			}
		}else{
			$lastpl = WFEngine::GetCurrentTransitionInfoByIdProcess($process_item_id);
			if(WFEngine::CheckUpdateTre($lastpl['ID_T'])==1){
				if($lastpl['DATESEND']!="" && $lastpl['HANXULY']>0){
					$tre = QLVBDHCommon::getTreHan($lastpl['DATESEND'],$lastpl['HANXULY']);
					$tre_last = $tre;
					$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>$tre),"ID_PL=".$lastpl['ID_PL']);
				}
			}
		}
		$hanxuly = $hanxuly==""?0:$hanxuly;
		//insert vào log
		$enddate = QLVBDHCommon::addDate(time(),($hanxuly==0?999:$hanxuly));
		$db->insert(QLVBDHCommon::Table("wf_processlogs"),array(
			"ID_U_SEND"=>$user_send,
			"ID_U_RECEIVE"=>$user_receive,
			"ID_PI"=>$process_item_id,
			"ID_T"=>$transition_id,
			"DATESEND"=>date("Y-m-d H:i:s"),
			"ID_A_BEGIN"=>$activity_begin,
			"ID_A_END"=>$activity_end,
			"ID_P"=>$process,
			"ID_G_RECEIVE"=>$group_receive,
			"ID_DEP_RECEIVE"=>$dep_receive,
			"NOIDUNG"=>$noidung,
			"HANXULY"=>$hanxuly,
			"SMS"=>$sms,
			"EMAIL"=>$email,
			"DATEEND"=>date("Y-m-d H:i:s",$enddate),
			"ID_U_EXECUTE"=>$user->ID_U
		));
                
        // exit;
		//chdeck finish

		/* Cap nhat cac bang ho so cong viec dang xu ly, da xu ly, tre */

		$ref = $db->query('select ID_O from '.QLVBDHCommon::Table('wf_processitems'). '  where ID_PI = ?', array($process_item_id));
		$ref = $ref->fetch();
		$id_hscv = $ref["ID_O"];
		
		$sql = "select ID_VBDEN from ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." where ID_HSCV = ?";
		$vbden = $db->query($sql,array($id_hscv))->fetch();
                
		// kiem tra hscv da co trong ho so dang xu ly chua
		$ref = $db->query('select count(*) as DEM from '.QLVBDHCommon::Table('hscv_dangxuly'). '  where ID_HSCV = ?', array($id_hscv));
		$ref = $ref->fetch();
		$count = $ref["DEM"];
		if((int)$count >0){
			//cap nhat lai nguoi nhan
			$db->update(QLVBDHCommon::Table("hscv_dangxuly"),array('ID_U'=>$user_receive,
																	'DATE_RECEIVE' => date("Y-m-d H:i:s"),
																	'DATEEND' => date("Y-m-d H:i:s",$enddate)	
			),"ID_HSCV=".$id_hscv);
		}else{
			$db->insert(QLVBDHCommon::Table("hscv_dangxuly"),array(
				'ID_HSCV'=> $id_hscv,
				'ID_U'=> $user_receive,
				'DATE_RECEIVE' => date("Y-m-d H:i:s"),
				'DATEEND'=>date("Y-m-d H:i:s",$enddate)
			));
		}
		
		
		//xoa trong bang da xu ly ma nguoi nhan co ho so cong viec
		$db->delete(QLVBDHCommon::Table('hscv_daxuly'),' ID_HSCV = '. $id_hscv . ' and ID_U = '.$user_receive);
		
	
		//them moi ho so da xu ly nguoi chuyen
		$ref = $db->query('select count(*) as DEM from '.QLVBDHCommon::Table('hscv_daxuly'). '  where ID_HSCV = ? and ID_U =? ',array($id_hscv,$user_send));
		$ref = $ref->fetch();
		if($ref["DEM"] > 0){
			
			$db->update(QLVBDHCommon::Table("hscv_daxuly"),array(
			'ID_U'=>$user_send,
			'DATE_RECEIVE'=>date("Y-m-d H:i:s")	
			),
				
			"ID_HSCV=".$id_hscv);
			
		}else{
			$db->insert(QLVBDHCommon::Table("hscv_daxuly"),array(
				'ID_HSCV'=>$id_hscv,
				'ID_U'=> $user_send,
				'DATE_RECEIVE' => date("Y-m-d H:i:s")
				
			));
		}

		if($tre_last != 0){
			
			$ref = $db->query('select *  from '.QLVBDHCommon::Table('hscv_tre'). '  where ID_HSCV = ?  and ID_U = ? ',array($id_hscv,$user_send));
			$ref = $ref->fetch();
			//$count = count($ref);
			//var_dump($ref) ; exit;//$count;
			if($ref){
				$db->update(QLVBDHCommon::Table("hscv_tre"),array(
					'TRE' => 	( $ref["TRE"]+ $tre_last),
					'TRECOUNT' => ( $ref["TRECOUNT"]+ 1)
				),"ID_HSCV = ". $id_hscv." and ID_U = ".$user_send );
			}else{
				$db->insert(QLVBDHCommon::Table("hscv_tre"),array(
					'ID_HSCV'=>$id_hscv,
					'ID_U'=> $user_send,
					'DATE_RECEIVE' => date("Y-m-d H:i:s"),
					'TRE'=>$tre_last,
					'TRECOUNT'=>1	
				));
			}
			
		}
		
		/* ket thuc cap nhat cac bang ho so cong viec */

		if(WFEngine::IsFinishTransition($transition_id)){
			$db->update(QLVBDHCommon::Table("WF_PROCESSITEMS"),array("IS_FINISH"=>1),"ID_PI=".$process_item_id);
		}
		if($user_receive!=0){
			$r = $db->query("SELECT ID_O,NAME FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")." WHERE ID_PI=?",$process_item_id);
			$r = $r->fetch();
			
			QLVBDHCommon::SendMessage(
				$user_send,
				$user_receive,
				$r['NAME'],
				"hscv/hscv/list/idhscv/".$r["ID_O"]."#hscv".$r["ID_O"]
			);
		}

		// Cap nhat cho van ban den
		// Truong hợp begindeadline
		$sql = "SELECT  * FROM WF_TRANSITIONS WHERE ID_T = ?";
		$r = $db->query($sql,$transition_id)->fetch();
		if($r["BEGINDEADLINE"]==1){
			// Lay ID_VBD
			$sql="SELECT fkhscv.ID_VBDEN as ID_VBD,lvb.HANXULY,vbdens.HANXULYTOANBO,vbdens.NGAYHETHAN
			,vbdens.NGAYCHUYENLIENTHONG,vbdens.NHOMCVVBD,vbdens.MASOLIENTHONG,hscv.HANXULY_GIAOVIEC,hscv.TYPEHANXULY
					FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")." wfp
					INNER JOIN ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv ON wfp.ID_O = hscv.ID_HSCV
					INNER JOIN ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv ON fkhscv.ID_HSCV = hscv.ID_HSCV
                                        INNER JOIN ".QLVBDHCommon::Table("vbd_vanbanden")." vbdens ON fkhscv.ID_VBDEN= vbdens.ID_VBD
                                        INNER JOIN vb_loaivanban lvb ON vbdens.ID_LVB= lvb.ID_LVB
					WHERE
						wfp.ID_PI = ?
			";
			$rvbd = $db->query($sql,$process_item_id)->fetch();
			// Update vanbanden NGAYHETHAN,HANXULYTOANBO,TRE,IS_FINISH
			if((int)$rvbd['HANXULYTOANBO'] && $rvbd['MASOLIENTHONG'] !="" ){				
				if($rvbd['NGAYHETHAN'] < '".date("Y-m-d")."'){
					$tre = QLVBDHCommon::getTreHan($rvbd['NGAYCHUYENLIENTHONG'],$rvbd['HANXULYTOANBO']);
				}else{
					$tre=0;
				}				
				$sql = "UPDATE ".QLVBDHCommon::Table("VBD_VANBANDEN")." SET					
					TRE=$tre,
					IS_FINISH=0,
					NGAYBATDAU='".$rvbd['NGAYCHUYENLIENTHONG']."'
					WHERE ID_VBD = ?
				";
				$db->query($sql,array($rvbd["ID_VBD"]));				
				
			}else{
				if($rvbd['NHOMCVVBD']==1){
					$hanxulytoanbo=(int)$rvbd['HANXULY_GIAOVIEC'];
					if((int)$hanxulytoanbo){
						$ngayhethan = date("Y-m-d H:i:s", QLVBDHCommon::addDate(time(),$hanxulytoanbo));
						$sql = "UPDATE ".QLVBDHCommon::Table("VBD_VANBANDEN")." SET
						NGAYHETHAN = ?,
						HANXULYTOANBO=$hanxulytoanbo,
						TRE=0,
						IS_FINISH=0,
						NGAYBATDAU='".date("Y-m-d H:i:s")."'
						WHERE ID_VBD = ?
						";
						$db->query($sql,array($ngayhethan,$rvbd["ID_VBD"]));
					}
				}else{
					$hanxulytoanbo=(int)$rvbd['HANXULY'];
					if((int)$hanxulytoanbo){
						$ngayhethan = date("Y-m-d H:i:s", QLVBDHCommon::addDate(time(),$hanxulytoanbo));
						$sql = "UPDATE ".QLVBDHCommon::Table("VBD_VANBANDEN")." SET
						NGAYHETHAN = ?,
						HANXULYTOANBO=$hanxulytoanbo,
						TRE=0,
						IS_FINISH=0,
						NGAYBATDAU='".date("Y-m-d H:i:s")."'
						WHERE ID_VBD = ?
						";
						$db->query($sql,array($ngayhethan,$rvbd["ID_VBD"]));
					}
				}
				
			}
            
		}else if($r["ENDDEADLINE"]==1){
			// Lay ID_VBD
			$sql="SELECT vbd.*
					FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")." wfp
					INNER JOIN ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv ON wfp.ID_O = hscv.ID_HSCV
					INNER JOIN ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv ON fkhscv.ID_HSCV = hscv.ID_HSCV
					INNER JOIN ".QLVBDHCommon::Table("VBD_VANBANDEN")." vbd ON fkhscv.ID_VBDEN = vbd.ID_VBD
					WHERE
						wfp.ID_PI = ?
			";
			$rvbd = $db->query($sql,$process_item_id)->fetch();
			// Update vanbanden NGAYHETHAN,HANXULYTOANBO,TRE,IS_FINISH
			$sql = "UPDATE ".QLVBDHCommon::Table("VBD_VANBANDEN")." SET
					TRE=".QLVBDHCommon::getTreHan($rvbd['NGAYBATDAU'],7).",
					IS_FINISH=1,
					ID_U_TRINHLDVP='".$user_send."'
					WHERE ID_VBD = ? AND IS_FINISH = 0
			";
			$db->query($sql,$rvbd["ID_VBD"]);
		}else{
		// Lay ID_VBD
			$sql="SELECT fkhscv.ID_VBDEN as ID_VBD,lvb.HANXULY,vbdens.HANXULYTOANBO,vbdens.NGAYHETHAN
			,vbdens.NGAYCHUYENLIENTHONG,vbdens.NHOMCVVBD,vbdens.MASOLIENTHONG,hscv.HANXULY_GIAOVIEC,hscv.TYPEHANXULY
					FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")." wfp
					INNER JOIN ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv ON wfp.ID_O = hscv.ID_HSCV
					INNER JOIN ".QLVBDHCommon::Table("vbd_fk_vbden_hscvs")." fkhscv ON fkhscv.ID_HSCV = hscv.ID_HSCV
                                        INNER JOIN ".QLVBDHCommon::Table("vbd_vanbanden")." vbdens ON fkhscv.ID_VBDEN= vbdens.ID_VBD
                                        INNER JOIN vb_loaivanban lvb ON vbdens.ID_LVB= lvb.ID_LVB
					WHERE
						wfp.ID_PI = ?
			";
			$rvbd = $db->query($sql,$process_item_id)->fetch();
			if($rvbd['NHOMCVVBD']==1){
				if($rvbd['HANXULYTOANBO'] < $rvbd['HANXULY_GIAOVIEC']){
					$hanxulytoanbo=(int)$rvbd['HANXULY_GIAOVIEC'];
					if((int)$hanxulytoanbo){
						$ngayhethan = date("Y-m-d H:i:s", QLVBDHCommon::addDate(time(),$hanxulytoanbo));
						$sql = "UPDATE ".QLVBDHCommon::Table("VBD_VANBANDEN")." SET
						NGAYHETHAN = ?,
						HANXULYTOANBO=$hanxulytoanbo,
						TRE=0,
						IS_FINISH=0,
						NGAYBATDAU='".date("Y-m-d H:i:s")."'
						WHERE ID_VBD = ?
						";
						$db->query($sql,array($ngayhethan,$rvbd["ID_VBD"]));
					}					
				}				
				
			}
		
		}
                    /////////////////////////////////////////////////////
                    $sqlCheckLienThong="select ID_VBLTCP,MASOLIENTHONG from ".QLVBDHCommon::Table("vbd_vanbanden")." where ID_VBD = ?";
                    $vbdenlienthong = $db->query($sqlCheckLienThong,array($vbden["ID_VBDEN"]))->fetch();
                    if($vbdenlienthong["MASOLIENTHONG"]!=0 && $vbdenlienthong["MASOLIENTHONG"]!= null && $vbdenlienthong["ID_VBLTCP"]!= null && $vbdenlienthong["ID_VBLTCP"]!= 0){
                        if(WFEngine::IsFinishTransition($transition_id)){
                                $status='06';//Đã hoàn thành
                                $Description= 'Đã hoàn thành';
                        }else{
                                $status='05';//Đang xử lý
                                $Description= 'Đang xử lý';
                        }
                        $config = Zend_Registry::get('config');
                        global $auth;        
                        $user = $auth->getIdentity();
                        $code=UsersModel::getsokyhieucqnbbyidu($user->ID_CQ);
                        $client = new SoapClient($config->service->lienthong->uri);
                        $sessionlt = $client->__call('Login', array(
                            'madonvi' => $code,
                            'password' => $config->service->lienthong->password));
                        $dataltvpcp = array(
                                'session'=>$sessionlt,
                                'ID_VBLTCP' => $vbdenlienthong["ID_VBLTCP"],
                                'Timestamp' => date('Y-m-d H:i:s'),
                                'status' => $status,
                                'Description'=> $Description,
                                'Staff' => $user->FULLNAME,
                                'Department' => DepartmentsModel::getNameById($user->ID_DEP)
                              );
                        $params = array(
                            'session' => $sessionlt,
                            'service_code' => 'VPCP',
                            'service_name' => 'QLVBDHCapNhatTrangThaiVPCP',
                            'parameter' => base64_encode(json_encode($dataltvpcp))
                        );
                        $client->__call('Execute', $params); 
                }
		return true;
	}
	static function IsMulti($transition_id){
		global $db;
		$r = $db->query("
		SELECT
			tr.MULTI 
		FROM 
			WF_TRANSITIONS tr
			inner join WF_TRANSITIONPOOLS tp on tr.ID_TP=tp.ID_TP
			inner join WF_PROCESSES p on p.ID_P = tr.ID_P
			inner join WF_CLASSES c on p.ID_C = c.ID_C
		WHERE
			tr.MULTI = 1 AND
			c.HAVEMULTI = 1 AND
			tp.HAVEMULTI = 1 AND
			ID_T=?
		",array($transition_id));
		if($r->rowCount()==1)return true;
		return false;
	}
	static function IsCheckMulti($transitionpool_id){
		global $db;
		$r = $db->query("
		SELECT
			1 as a 
		FROM 
			WF_TRANSITIONPOOLS tp
			inner join WF_CLASSES c on c.ID_C = tp.ID_C
		WHERE
			c.HAVEMULTI = 1 AND
			tp.HAVEMULTI = 1 AND
			ID_TP=?
		",$transitionpool_id);
		if($r->rowCount()==1)return true;
		return false;
	}
	
	
	
	/**
	 * Tạo form send next user.
	 * 
	 * @param int $transition_id
	 * @return string $html
	 */
	static function FormSend($transition_id,$forceone = false,$onlydep=false){
		global $db;
		//Lay han xu ly mac dinh
		$r = $db->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$transition_id);
		$hanxuly = $r->fetch();
		$hanxuly = $hanxuly['HANXULY'];
		//Lấy danh sách các group
		$r = $db->query("
		SELECT
			G.ID_G,G.NAME 
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
		WHERE
			TR.ID_T=? AND G.ACTIVE = 1
		ORDER BY NAME
		",array($transition_id));
		$group = $r->fetchAll();
		$r->closeCursor();
		
		//Lấy danh sách các phòng
		$r = $db->query("
		SELECT
			DEP.ID_DEP,DEP.NAME,DEP.ID_U_DAIDIEN
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_DEPARTMENTS DEP ON AC.ID_DEP=DEP.ID_DEP
		WHERE
			TR.ID_T=? AND DEP.ACTIVE = 1
		ORDER BY NAME
		",array($transition_id));
		$dep = $r->fetchAll();
		$r->closeCursor();
		
		//Lay danh sách các user
		$r = $db->query("
			SELECT
				'-1' as ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				UG.ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U=U.ID_U
			WHERE
				TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				G.ID_G,-1 AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_G=G.ID_G
				INNER JOIN QTHT_USERS U ON U.ID_U = UG.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE
				TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				-1 AS ID_G,'-1' AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=? AND U.ACTIVE = 1
			ORDER BY ORDERS,NAME
		",array($transition_id,$transition_id,$transition_id,$transition_id));
		$user = $r->fetchAll();
		$r->closeCursor();
		if(WFEngine::IsMulti($transition_id) && $forceone==false){
			global $auth;
			$user = $auth->getIdentity();
			require_once 'plugin/multisend.php';
		}else{
			if(count($user)>1){
				if($onlydep){
					global $auth;
					$html.="  <select style='display:none' name=wf_selg id=wf_selg onchange='FillComboBy2Combo(this,document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));showallnhom(this);'>";
					$html.="  	<option value=-1>--Chọn nhóm--</option>";
					for($i=0;$i<count($group);$i++){
						$html.=" <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
					}
					$html.="  </select>";

					$html.="  <select name=wf_seldep id=wf_seldep onchange='FillComboBy2Combo(document.getElementById(\"wf_selg\"),this,document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));showallphong(this);'>";
					$html.="  	<option value=-1>--Chọn phòng--</option>";
					for($i=0;$i<count($dep);$i++){
						$html.=" <option value=".$dep[$i]["ID_DEP"].">".$dep[$i]["NAME"]."</option>";
					}
					$html.="  </select>";
					$html.="  <select name=wf_nextuser id=wf_nextuser onchange='wf_changeuser(this)'>";
					$html.="  </select>";
					$html.="<script>";
					$html .= "function wf_changeuser(obj){
						action=2;
						getvalue('auth','user','checksmsemail','id_u='+obj.value);
					};";
					$html.="	var wf_arr_user = new Array();";
					for($i=0;$i<count($user);$i++){
						$html.="wf_arr_user[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
					}
					if($usercurrent->ID_DEP){
					$html.="	
						document.getElementById(\"wf_seldep\").value=-1;
						FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);

						if(document.getElementById(\"wf_nextuser\").options.length==0){
							
							document.getElementById(\"wf_seldep\").value='".$usercurrent->ID_DEP."';
							FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
						}
						
						
						";
						
					}else{
						$html.="document.getElementById(\"wf_seldep\").value=-1;";
					}
					$html.="document.getElementById(\"wf_seldep\").value='".$auth->getIdentity()->ID_DEP."';";
					$html.="FillComboBy2ComboWithSel(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user,null,'".$auth->getIdentity()->ID_U."');wf_changeuser(document.getElementById(\"wf_nextuser\"));showallphong(document.getElementById(\"wf_seldep\"));";
					$html.="document.getElementById(\"wf_nextuser\").value='".$auth->getIdentity()->ID_U."';";
					$html.="</script>";
				}else{
					global $auth;
					$usercurrent = $auth->getIdentity();
					//Xuat du lieu ra html
					$html = "";
					$html.="<div id=chonnh class='required clearfix'>";
					$html.="  <label>Nhóm</label>";
					$html.="  <select name=wf_selg id=wf_selg onchange='FillComboBy2Combo(this,document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));showallnhom(this);'>";
					$html.="  	<option value=-1>--Chọn nhóm--</option>";
					for($i=0;$i<count($group);$i++){
						$html.=" <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
					}
					$html.="  </select>";
									$html.="<div id=groallok style=\"display:none;\"><input id=checkgro name=groallok type=checkbox value=1 onchange=\"hiddennguoi(this);\"><span> Gửi cả nhóm</span></div>";
					$html.="</div>";
									$html.="<div class=clr></div>";
					$html.="<div id=chonp class='required clearfix'>";
					$html.="  <label>Phòng</label>";
					$html.="  <select name=wf_seldep id=wf_seldep onchange='FillComboBy2Combo(document.getElementById(\"wf_selg\"),this,document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));showallphong(this);'>";
					$html.="  	<option value=-1>--Chọn phòng--</option>";
					for($i=0;$i<count($dep);$i++){
						$html.=" <option value=".$dep[$i]["ID_DEP"].">".$dep[$i]["NAME"]."</option>";
					}
					$html.="  </select>";
									$html.="<div id=depallok style=\"display:none;\"><input id=checkdep name=depallok type=checkbox value=1 onchange=\"hiddennguoi(this);\"><span> Gửi cả phòng</span></div>";
					$html.="</div>";
									$html.="<div class=clr></div>";
					$html.="<div id=chonng class='required clearfix'>";
					$html.="  <label>Người</label>";
					$html.="  <select name=wf_nextuser id=wf_nextuser onchange='wf_changeuser(this)'>";
					$html.="  </select>";
					$html.="</div>";
									$html.="<div class=clr></div>";
					$html.="<div class='required clearfix' id=wfnoidung>";
					$html.="  <label>Nội dung</label>";
					$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";
					$html.="  </textarea>";
					$html.="</div>";
									$html.="<div class=clr></div>";
					$html.="<div class='required clearfix'>";
					$html.="  <label>Hạn xử lý</label>";
					$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
					//$html.="  <input size=3 maxlength=3 name=wf_hanxuly_user onkeypress='return isNumberKey(event)' id=wf_hanxuly_user value='".$hanxuly."'>";
					$html.="</div>";
					$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
									$html.="<div class=clr></div>";
					$html.="<div class='required clearfix' id='wf_smsemail'>";
					$html.="  <label>Nhắc nhở</label>";
					$html.="  <span id=wf_sms> SMS  &nbsp; <input type='checkbox' value=1 name=wf_sms> </span> <span id=wf_email> EMAIL &nbsp;<input type='checkbox' value=1 name=wf_email> </span>";
					$html.="</div>";
					$html.="<script>";
					$html .= "function wf_changeuser(obj){
						action=2;
						getvalue('auth','user','checksmsemail','id_u='+obj.value);
					};";
					$html.="	var wf_arr_user = new Array();";
					for($i=0;$i<count($user);$i++){
						$html.="wf_arr_user[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
					}
					if($usercurrent->ID_DEP){
					$html.="	
						document.getElementById(\"wf_seldep\").value=-1;
						FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);

						if(document.getElementById(\"wf_nextuser\").options.length==0){
							
							document.getElementById(\"wf_seldep\").value='".$usercurrent->ID_DEP."';
							FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
						}
						
						
						";
						
					}else{
						$html.="document.getElementById(\"wf_seldep\").value=-1;";
					}

					$html.="</script>";
				}
			}else{
				$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
				$html.="<input type=hidden name='wf_nextuser' value='".$user[0]['ID_U']."'>";
                                $html.="<div class=clr></div>";
				$html.="<div class='required clearfix'><label>Người</label>".$user[0]['NAME']."</div>";
                                $html.="<div class=clr></div>";
				$html.="<div class='required clearfix' id=wfnoidung>";
				$html.="  <label>Nội dung</label>";
				$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";
				$html.="  </textarea>";
				$html.="</div>";
                                $html.="<div class=clr></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Hạn xử lý</label>";
				$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
				//$html.="  <input size=4 name=wf_hanxuly_user id=wf_hanxuly_user value='".$hanxuly."'>";
				$html.="</div>";
			}
			return $html;
		}
	}
        
   
   // formseng chuyen lai

   static function FormSendchuyenlai($transition_id,$id_hscv){
		global $db;
		//Lay han xu ly mac dinh
		$r = $db->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$transition_id);
		$hanxuly = $r->fetch();
		$hanxuly = $hanxuly['HANXULY'];
		//Lấy danh sách các group
		$r = $db->query("
		SELECT
			G.ID_G,G.NAME 
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
		WHERE
			TR.ID_T=?
		ORDER BY NAME
		",array($transition_id));
		$group = $r->fetchAll();
		$r->closeCursor();
		
		//Lấy danh sách các phòng
		$r = $db->query("
		SELECT
			DEP.ID_DEP,DEP.NAME,DEP.ID_U_DAIDIEN
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_DEPARTMENTS DEP ON AC.ID_DEP=DEP.ID_DEP
		WHERE
			TR.ID_T=?
		ORDER BY NAME
		",array($transition_id));
		$dep = $r->fetchAll();
		$r->closeCursor();
		
		//Lay danh sách các user
		$r = $db->query("
			SELECT
				'-1' as ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=?
			UNION ALL
			SELECT
				UG.ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U=U.ID_U
			WHERE
				TR.ID_T=?
			UNION ALL
			SELECT
				G.ID_G,-1 AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_G=G.ID_G
				INNER JOIN QTHT_USERS U ON U.ID_U = UG.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE
				TR.ID_T=?
			UNION ALL
			SELECT
				-1 AS ID_G,'-1' AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=?
			ORDER BY NAME
		",array($transition_id,$transition_id,$transition_id,$transition_id));
		$user = $r->fetchAll();
		$r->closeCursor();

		//Lấy noi dung nguoi chuyen sau cung
		$r = $db->query("
			select wfl.NOIDUNG from ".QLVBDHCommon::Table('hscv_hosocongviec')." hscv
			 inner join ".QLVBDHCommon::Table('wf_processlogs')." wfl on hscv.`ID_PI`=wfl.`ID_PI`
			  where hscv.`ID_HSCV`=? order by wfl.`ID_PL` DESC limit 0,1
		",array($id_hscv));
		$noidung = $r->fetch();
		$r->closeCursor();

		if(WFEngine::IsMulti($transition_id) && $forceone==false){
			global $auth;
			$user = $auth->getIdentity();
			require_once 'plugin/multisend.php';
		}else{
			if(count($user)>1){
				global $auth;
				$usercurrent = $auth->getIdentity();
				//Xuat du lieu ra html
				$html = "";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Nhóm</label>";
				$html.="  <select name=wf_selg id=wf_selg onchange='FillComboBy2Combo(this,document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));'>";
				$html.="  	<option value=-1>--Chọn nhóm--</option>";
				for($i=0;$i<count($group);$i++){
					$html.=" <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
				}
				$html.="  </select>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Phòng</label>";
				$html.="  <select name=wf_seldep id=wf_seldep onchange='FillComboBy2Combo(document.getElementById(\"wf_selg\"),this,document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));'>";
				$html.="  	<option value=-1>--Chọn phòng--</option>";
				for($i=0;$i<count($dep);$i++){
					$html.=" <option value=".$dep[$i]["ID_DEP"].">".$dep[$i]["NAME"]."</option>";
				}
				$html.="  </select>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Người</label>";
				$html.="  <select name=wf_nextuser id=wf_nextuser onchange='wf_changeuser(this)'>";
				$html.="  </select>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Nội dung</label>";
				$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";
				$html.=$noidung['NOIDUNG'];
				$html.="  </textarea>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Hạn xử lý</label>";
				$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
				//$html.="  <input size=3 maxlength=3 name=wf_hanxuly_user onkeypress='return isNumberKey(event)' id=wf_hanxuly_user value='".$hanxuly."'>";
				$html.="</div>";
				$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix' id='wf_smsemail'>";
				$html.="  <label>Nhắc nhở</label>";
				$html.="  <span id=wf_sms> SMS  &nbsp; <input type='checkbox' value=1 name=wf_sms> </span> <span id=wf_email> EMAIL &nbsp;<input type='checkbox' value=1 name=wf_email> </span>";
				$html.="</div>";
				$html.="<script>";
				$html .= "function wf_changeuser(obj){
					action=2;
					getvalue('auth','user','checksmsemail','id_u='+obj.value);
				};";
				$html.="	var wf_arr_user = new Array();";
				for($i=0;$i<count($user);$i++){
					$html.="wf_arr_user[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				}
				if($usercurrent->ID_DEP){
				$html.="	
					document.getElementById(\"wf_seldep\").value=-1;
					FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);

					if(document.getElementById(\"wf_nextuser\").options.length==0){
						
						document.getElementById(\"wf_seldep\").value='".$usercurrent->ID_DEP."';
						FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
					}
					
					
					";
					
				}else{
					$html.="document.getElementById(\"wf_seldep\").value=-1;";
				}

				$html.="</script>";
			}else{
				$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
				$html.="<input type=hidden name='wf_nextuser' value='".$user[0]['ID_U']."'>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'><label>Người</label>".$user[0]['NAME']."</div>";
				$html.="<div class='clr'></div>";
                                $html.="<div class='required clearfix'>";
				$html.="  <label>Nội dung</label>";
				$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";				
				$html.="  </textarea>";
				$html.="</div>";"<div class='clr'></div>";
				$html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Hạn xử lý</label>";
				$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
				//$html.="  <input size=4 name=wf_hanxuly_user id=wf_hanxuly_user value='".$hanxuly."'>";
				$html.="</div>";
			}
			return $html;
		}
	}


	static function FormSendDefaultRetranfer($transition_id,$id_hscv,$forceone = false){
		global $db;
		
		//Lay han xu ly mac dinh
		$r = $db->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$transition_id);
		$hanxuly = $r->fetch();
		$hanxuly = $hanxuly['HANXULY'];
		
		//Lấy danh sách các group
		$r = $db->query("
		SELECT
			G.ID_G,G.NAME 
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
		WHERE
			TR.ID_T=?
		ORDER BY NAME
		",array($transition_id));
		$group = $r->fetchAll();
		$r->closeCursor();


		//Lấy danh sách các phòng
		$r = $db->query("
		SELECT
			DEP.ID_DEP,DEP.NAME,DEP.ID_U_DAIDIEN
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_DEPARTMENTS DEP ON AC.ID_DEP=DEP.ID_DEP
		WHERE
			TR.ID_T=?
		ORDER BY NAME
		",array($transition_id));
		$dep = $r->fetchAll();
		$r->closeCursor();
		
		//Lay danh sách các user
		$r = $db->query("
			SELECT
				'-1' as ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=? AND u.ACTIVE=1
			
			UNION 
			SELECT
				-1 as ID_G,-1 AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_GROUPS G ON AC.ID_G=G.ID_G
				INNER JOIN FK_USERS_GROUPS UG ON UG.ID_G=G.ID_G
				INNER JOIN QTHT_USERS U ON U.ID_U = UG.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE
				TR.ID_T=? AND u.ACTIVE=1
			UNION 
			SELECT
				-1 AS ID_G,'-1' AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=? AND u.ACTIVE=1
			ORDER BY NAME
		",array($transition_id,$transition_id,$transition_id));		
		$user = $r->fetchAll();		
		$r->closeCursor();
		if(WFEngine::IsMulti($transition_id) && $forceone==false){
			global $auth;
			$user = $auth->getIdentity();
			require_once 'plugin/multisend.php';
		}else{
			if(count($user)>1){
				global $auth;
				$usercurrent = $auth->getIdentity();
				//Xuat du lieu ra html
				$html = "";
				
				$html .="";
				$html.="<div class='required clearfix'>";
				
				
				$html.="<input type=hidden name=wf_selg id=wf_selg value=-1>";
				$html.="</div>";
				
				$html.="<div class='required clearfix'>";
				$html.="  <label>Phòng</label>";
				$html.="  <select name=wf_seldep id=wf_seldep onchange='FillComboBy2Combo(document.getElementById(\"wf_selg\"),this,document.getElementById(\"wf_nextuser\"),wf_arr_user);wf_changeuser(document.getElementById(\"wf_nextuser\"));'>";
				$html.="  	<option value=-1>--Chọn phòng--</option>";
				for($i=0;$i<count($dep);$i++){
					$html.=" <option value=".$dep[$i]["ID_DEP"].">".$dep[$i]["NAME"]."</option>";
				}
				$html.="  </select>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Người</label>";
				$html.="  <select name=wf_nextuser id=wf_nextuser onchange='wf_changeuser(this)'>";
				$html.="  </select>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Ý kiến</label>";
				$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";
				$html.="  </textarea>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Hạn xử lý</label>";
				$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
				//$html.="  <input size=3 maxlength=3 name=wf_hanxuly_user onkeypress='return isNumberKey(event)' id=wf_hanxuly_user value='".$hanxuly."'>";
				$html.="</div>";
				$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix' id='wf_smsemail'>";
				$html.="  <label>Nhắc nhở</label>";
				$html.="  <span id=wf_sms> SMS  &nbsp; <input type='checkbox' value=1 name=wf_sms> </span> <span id=wf_email> EMAIL &nbsp;<input type='checkbox' value=1 name=wf_email> </span>";
				$html.="</div>";
				$html.="<script>";
				$html .= "function wf_changeuser(obj){
					//action=2;
					//getvalue('auth','user','checksmsemail','id_u='+obj.value);
				};";
				$html.="	var wf_arr_user = new Array();";
				for($i=0;$i<count($user);$i++){
					$html.="wf_arr_user[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				}
				$sql = "
					select wfitem.*, CONCAT(emp.FIRSTNAME, ' ' , emp.LASTNAME) as NAME 
					from
					(
						select distinct wfl.ID_U_SEND from
						".QLVBDHCommon::Table('wf_processlogs')." wfl
						inner join ".QLVBDHCommon::Table('wf_processitems')." wfi on wfl.ID_PI = wfi.ID_PI
						where wfi.ID_O = ?
						ORDER BY wfl.ID_PL DESC
					)wfitem
					inner join qtht_users u on wfitem.ID_U_SEND = u.ID_U
					
					inner join qtht_employees emp on u.ID_EMP = emp.ID_EMP
					
					
				";
				try{
					$qr = $db->query($sql,array((int)$id_hscv));
					$old_wfl = $qr->fetch();
				}catch(Exception $ex){
					
				}

				//echo $id_hscv;
				//var_dump($old_wfl);
				//kiem tra xem id_u có quyền không được chuyển không
				$ln = 0;
				if(count($old_wfl)){
					foreach($user as $it_u){
						if($old_wfl["ID_U_SEND"]== $it_u["ID_U"]){
							$ln =1;
							break;
						}
					}
				}

				if($ln==1){
					
					
					$html.="	var wf_arr_user_df = new Array();";
					
						$html.="wf_arr_user_df[0] = new Array('"."-1"."','"."-1"."','".$old_wfl['ID_U_SEND']."','".$old_wfl['NAME']."');";
					
					$html.="document.getElementById(\"wf_seldep\").value=-1;";	
					$html.="
					FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user_df);
					
				
					
					";
				}

				else{
					
					if(count($dep)==0){
						$html.="	
								document.getElementById(\"wf_seldep\").value=-1;
								FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
					
						
								";
					}else{
						if($usercurrent->ID_DEP){
						$html.="	
							document.getElementById(\"wf_seldep\").value=-1;
							FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);

							if(document.getElementById(\"wf_nextuser\").options.length==0){
								
								document.getElementById(\"wf_seldep\").value='".$usercurrent->ID_DEP."';
								FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
							}
							
							
							";
							
						}else{
							$html.="	
								document.getElementById(\"wf_seldep\").value=-1;
								FillComboBy2Combo(document.getElementById(\"wf_selg\"),document.getElementById(\"wf_seldep\"),document.getElementById(\"wf_nextuser\"),wf_arr_user);
						
					
						
								";
						}
					}
				}

				$html.="</script>";
			}else{
				$html.="<input type=hidden name=wf_nexttransition value='".$transition_id."'>";
				$html.="<input type=hidden name='wf_nextuser' value='".$user[0]['ID_U']."'>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'><label>Người</label>".$user[0]['NAME']."</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Nội dung</label>";
				$html.="  <textarea name=wf_name_user id=wf_name_user cols=100 rows=3>";
				$html.="  </textarea>";
				$html.="</div>";
                                $html.="<div class='clr'></div>";
				$html.="<div class='required clearfix'>";
				$html.="  <label>Hạn xử lý</label>";
				$html .= QLVBDHCommon::createInputHanxuly("wf_hanxuly_user","wf_hanxuly_user",$hanxuly);
				//$html.="  <input size=4 name=wf_hanxuly_user id=wf_hanxuly_user value='".$hanxuly."'>";
				$html.="</div>";
			}
			return $html;
		}
	}
	/**
	 * Lấy menu xử lý. Menu trả về có dạng <ul><li>
	 * Class menu cha: parentmenu
	 * Class menu con: childmemu
	 * 
	 * @param int $user_id
	 * @return string $html
	 */
	static function GetMainMenu($user_id){
		global $db;
		
		$r = $db->query("
		SELECT 
			PR.ALIAS, PR.ID_P, PR.NAME as PR_NAME, A.`ID_A`,A.`NAME`,AP.`LINK`,AP.ORDERS as ORDERS_AP,PR.ORDERS as ORDERS_PR
		FROM
			`WF_PROCESSES` PR
			INNER JOIN `WF_ACTIVITIES` A ON A.`ID_P`=PR.`ID_P`
			INNER JOIN `WF_ACTIVITYACCESSES` AC ON A.`ID_A`= AC.`ID_A`
			INNER JOIN QTHT_USERS U ON U.`ID_U` = AC.`ID_U`
			INNER JOIN `WF_ACTIVITYPOOLS` AP ON AP.`IP_AP` = A.`ID_AP`
		WHERE 
			U.`ID_U`=?
		UNION
		SELECT 
			PR.ALIAS, PR.ID_P, PR.NAME as PR_NAME, A.`ID_A`,A.`NAME`,AP.`LINK`,AP.ORDERS as ORDERS_AP,PR.ORDERS as ORDERS_PR
		FROM
			`WF_PROCESSES` PR
			INNER JOIN `WF_ACTIVITIES` A ON A.`ID_P`=PR.`ID_P`
			INNER JOIN `WF_ACTIVITYACCESSES` AC ON A.`ID_A`= AC.`ID_A`
			INNER JOIN `WF_ACTIVITYPOOLS` AP ON AP.`IP_AP` = A.`ID_AP`
			INNER JOIN `QTHT_GROUPS` G ON G.`ID_G` = AC.`ID_G`
			INNER JOIN `FK_USERS_GROUPS` UG ON UG.`ID_G`=G.`ID_G`
		WHERE 
			UG.`ID_U`=?
		ORDER BY
			ORDERS_PR,ORDERS_AP
		",array($user_id,$user_id));
		$process = $r->fetchAll();
		$r->closeCursor();
		
		$html="";
		if($r->rowCount()>0){
			$currentprocess = 0;
			$html.="<ul><li class=parentmenu>".$process[0]["PR_NAME"]."<ul>";		
			for($i=0;$i<$r->rowCount();$i++){
				if($currentprocess!=$process[$i]["ID_P"] && $currentprocess>0){
					$html.="</ul></li></ul><ul><li>".$process[$i]["PR_NAME"]."<ul>";
					$currentprocess = $process[$i]["ID_P"];
				}else{
					$create = WFEngine::GetCreateProcessButton($process[$i]["ALIAS"],$user_id);
					if(count($create)>0){
						$html.="<li class=childmenu><a href='".$create["LINK"]."/wf_id_t/".$create["ID_T"]."'>".$create["NAME"]."</a></li>";
					}else{
						$html.="<li class=childmenu><a href='".$process[$i]["LINK"]."?wf_id_p=".$process[$i]["ID_P"]."&wf_id_a=".$process[$i]["ID_A"]."'>".$process[$i]["NAME"]."</a></li>";
					}
					$currentprocess = $process[$i]["ID_P"];
				}
			}
			$html.="</ul></li></ul>";
		}
		return $html;
	}
	
	/**
	 * Tạo button ho việc khởi tạo process
	 * @param int $process_id
	 * @param int $activity_id
	 * @param int $user_id
	 * @return array $arr
	 */
	static function GetCreateProcessButton($process_alias,$user_id){
		global $db;
		
		//Get transition co begin activity = null
		$r = $db->query("
			select
				ID_T,tp.ID_TP,LINK,tp.NAME 
			from 
				WF_TRANSITIONS t 
				inner join WF_TRANSITIONPOOLS tp on t.ID_TP=tp.ID_TP 
			where 
				ISFIRST=1 and tp.ALIAS=?",array($process_alias));
		$transition = $r->fetch();
		$r->closeCursor();
		$arr = array();
		if($r->rowCount()>0){
		//xuat ra html 
			$arr = array("ID_T"=>$transition["ID_T"],"LINK"=>$transition["LINK"],"NAME"=>$transition["NAME"]);
		}
		return $arr;
	}
	/**
	 * Tạo button ho việc khởi tạo process
	 * @param int $process_id
	 * @param int $activity_id
	 * @param int $user_id
	 * @return array $arr
	 */
	static function GetCreateProcessButtonFromLoaiCV($loaicv,$user_id){
		global $db;
		
		//Get transition co begin activity = null
		$r = $db->query("
			select
				ID_T,tp.ID_TP,LINK,tp.NAME,t.ID_A_BEGIN
			from 
				WF_TRANSITIONS t 
				inner join WF_TRANSITIONPOOLS tp on t.ID_TP=tp.ID_TP
				inner join WF_PROCESSES p on p.ID_P = t.ID_P
				inner join HSCV_LOAIHOSOCONGVIEC lcv on p.ALIAS = lcv.MASOQUYTRINH
			where 
				ISFIRST=1 and lcv.ID_LOAIHSCV=?",array($loaicv));
		$transition = $r->fetch();
		$r->closeCursor();
		$arr = array();
		if($r->rowCount()>0){
			if(WFEngine::HaveAccessableActivity($transition["ID_A_BEGIN"],$user_id)){
				$arr = array("ID_T"=>$transition["ID_T"],"LINK"=>$transition["LINK"],"NAME"=>$transition["NAME"]);
			}
		}
		return $arr;
	}
	/**
	 * 
	 */
	static function GetActivityFromLoaiCV($loaicv,$user_id){
		global $db;
		
		//check loai hscv
		$param = array();
		$where = "";
		if($loaicv>0){
			$where = "and lcv.ID_LOAIHSCV=?";
			$param[] = $loaicv;
		}
		//Get transition co begin activity = null
		$r = $db->query("
			select
				distinct a.ID_A,a.NAME as NAME_AP,ap.ORDERS as ORDERS_AP,p.ID_P,p.NAME as NAME_P,p.ORDERS as ORDERS_P
			from 
				WF_ACTIVITIES a
				inner join WF_ACTIVITYPOOLS ap on a.ID_AP = ap.IP_AP
				inner join WF_PROCESSES p on p.ID_P = a.ID_P
				inner join HSCV_LOAIHOSOCONGVIEC lcv on p.ALIAS = lcv.MASOQUYTRINH
				inner join WF_TRANSITIONS tp on tp.ID_A_END=a.ID_A
			where
				true
				$where
			ORDER BY
				ORDERS_P,ORDERS_AP
			",$param);
		$activity = $r->fetchAll();
		$r->closeCursor();
		$arr = array();
		if($r->rowCount()>0){
			for($i=0;$i<$r->rowCount();$i++){
				if(WFEngine::HaveAccessableActivity($activity[$i]["ID_A"],$user_id)){
					$arr[] = array($activity[$i]["ID_P"],$activity[$i]["NAME_P"],$activity[$i]["ID_A"],$activity[$i]["NAME_AP"]);
				}
			}
		}
		return $arr;
	}
	static function GetLoaiCongViecFromUser($user_id){
		global $db;
		
		//Get transition co begin activity = null
		$r = $db->query("
			select
				lcv.ID_LOAIHSCV,p.NAME,p.ID_P
			from 
				WF_PROCESSES p
					inner join HSCV_LOAIHOSOCONGVIEC lcv on p.ALIAS = lcv.MASOQUYTRINH
			",array());
		$process = $r->fetchAll();
		$r->closeCursor();
		$arr = array();
		if($r->rowCount()>0){
			for($i=0;$i<count($process);$i++){
				if(WFEngine::HaveAccessableProcess($process[$i]["ID_P"],$user_id)){
					$arr[] = array("ID_LOAIHSCV"=>$process[$i]["ID_LOAIHSCV"],"NAME"=>$process[$i]["NAME"]);
				}
			}
		}
		return $arr;
	}
	/**
	 * 
	 */
	static function ToCombo($arr,$sel){
		$html = "";
		$idp=0;
		for($i=0;$i<count($arr);$i++){
			if($arr[$i][0]!=$idp){
				if($idp>0){
					$html .= "</optgroup>";
				}
				$html .= "<optgroup label='".htmlspecialchars($arr[$i][1])."'>";
				$idp = $arr[$i][0];
				//kiểm tra xem có phải activity cuối không
				if($arr[$i][2]!=$sel){
					$html .= "<option value=".$arr[$i][2].">".htmlspecialchars($arr[$i][3])."</option>";
				}else{
					$html .= "<option value=".$arr[$i][2]." selected>".htmlspecialchars($arr[$i][3])."</option>";
				}
			}else{
				//kiểm tra xem có phải activity cuối không
				if($arr[$i][2]!=$sel){
					$html .= "<option value=".$arr[$i][2].">".htmlspecialchars($arr[$i][3])."</option>";
				}else{
					$html .= "<option value=".$arr[$i][2]." selected>".htmlspecialchars($arr[$i][3])."</option>";
				}
			}
		}
		$html .= "</optgroup>";
		return $html;
	}
	static function GetCurrentTransitionInfoByIdHscv($idhscv){
		global $db;
		
		//Lấy thông tin lần chuyển cuối cùng
		$sql = "
			SELECT
				concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,unc.ID_U as ID_U_NC,
				concat(empnk.FIRSTNAME,' ',empnk.LASTNAME) as EMPNKNAME,unk.ID_U as ID_U_NK,
				g.NAME as GROUPNAME,
				dep.NAME as DEPNAME,
				pl.DATESEND,
				pl.HANXULY,
				pl.ID_PL,
				pl.TRE,
				pl.ID_PI,
				pl.ID_T,
				ac.NAME as AC_NAME,
				tr.ISLAST,
				hscv.IS_THEODOI,
				hscv.IS_CHOXULY
			FROM
				".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv
				inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on hscv.ID_PI = pl.ID_PI
				inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
				inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
				left join QTHT_USERS unk on unk.ID_U = pl.ID_U_RECEIVE
				left join QTHT_EMPLOYEES empnk on unk.ID_EMP = empnk.ID_EMP
				left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
				inner join WF_ACTIVITIES ac on ac.ID_A = pl.ID_A_END
				inner join WF_TRANSITIONS tr on tr.ID_T = pl.ID_T
			WHERE
				hscv.ID_HSCV = ?
			ORDER BY
				pl.ID_PL DESC
		";
		//echo $sql;
		$r = $db->query($sql,array($idhscv));
		return $r->fetch();
	}
	static function GetCurrentTransitionInfoByIdProcess($idprocess){
		global $db;
		
		//Lấy thông tin lần chuyển cuối cùng
		$sql = "
			SELECT
				concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,unc.ID_U as ID_U_NC,
				concat(empnk.FIRSTNAME,' ',empnk.LASTNAME) as EMPNKNAME,unk.ID_U as ID_U_NK,
				g.NAME as GROUPNAME,
				dep.NAME as DEPNAME,
				pl.DATESEND,
				pl.HANXULY,
				pl.ID_PL,
				pl.ID_T
			FROM
				".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
				inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
				inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
				left join QTHT_USERS unk on unk.ID_U = pl.ID_U_RECEIVE
				left join QTHT_EMPLOYEES empnk on unk.ID_EMP = empnk.ID_EMP
				left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
			WHERE
				pl.ID_PI = ?
			ORDER BY
				pl.ID_PL DESC
		";
		$r = $db->query($sql,array($idprocess));
		return $r->fetch();
	}
	static function GetProcessLogByObjectId($objectid){
		 global $db;
		 $sql= "
		 	SELECT
		 		unc.ID_U as ID_U_NC,
		 		unn.ID_U as ID_U_NN,
				unc.USERNAME as USERNAME_NC,
				unn.USERNAME as USERNAME_NN,
				pl.ID_U_EXECUTE,
		 		concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,
				concat(empnn.FIRSTNAME,' ',empnn.LASTNAME) as EMPNNNAME,
				concat(empex.FIRSTNAME,' ',empex.LASTNAME) as EMPEXNAME,
				g.NAME as GROUPNAME,
				dep.NAME as DEPNAME,
				tr.NAME,
				pl.DATESEND,
				pl.HANXULY,
				pl.TRE,
				pl.NOIDUNG,
				hscv.`NAME` as HSCV_NAME,
				hscv.`EXTRA`,
				hscv.IS_THEODOI,
				hscv.IS_CHOXULY,
				pl.ID_PL,
				cd.NAME as CHUCDANH
		 	FROM
		 		".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
		 		inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on pl.ID_PI = hscv.ID_PI
		 		inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
				inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
				left join QTHT_USERS unn on unn.ID_U = pl.ID_U_RECEIVE
				left join QTHT_EMPLOYEES empnn on unn.ID_EMP = empnn.ID_EMP
				left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
				left join QTHT_USERS uex on uex.ID_U = pl.ID_U_EXECUTE
				left join QTHT_EMPLOYEES empex on uex.ID_EMP = empex.ID_EMP
				inner join WF_TRANSITIONS tr on tr.ID_T = pl.ID_T
				inner join WF_TRANSITIONPOOLS tp on tr.ID_TP = tp.ID_TP
				left join qtht_chucdanh cd ON cd.ID_CD = empnn.ID_CD
			WHERE
				hscv.ID_HSCV = ?
			ORDER BY ID_PL
			LIMIT 1,999
		 ";
		 $r = $db->query($sql,array($objectid));
		return $r->fetchAll();
	}
	
	static function RollBack($idhscv,$user_id,$nodelete=false){
		global $db;
		//Lấy log cuối cùng
		$sql="SELECT pl.*
		FROM
			".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
			INNER JOIN (SELECT max(ID_PL) as ID_PL FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")." GROUP BY ID_PI) t on t.ID_PL = pl.ID_PL
			INNER JOIN ".QLVBDHCommon::Table("WF_PROCESSITEMS")." p on pl.ID_PI = p.ID_PI
		WHERE
			ID_U_SEND = ? AND p.ID_O = ?
		";
		$r = $db->query($sql,array($user_id,$idhscv));

		if($r->rowCount()==1){
			$lastlog = $r->fetch();
			
			///return 0;
			if($lastlog['ISSPLIT']!=1 || $nodelete==true){
				$id_a_begin = $lastlog['ID_A_BEGIN'];
				$id_a_end = $lastlog['ID_A_END'];
				$id_p = $lastlog['ID_P'];
				$id_pi = $lastlog['ID_PI'];
				$id_pl = $lastlog['ID_PL'];
				$id_t = $lastlog['ID_T'];
				//update tre lai
				$lastpl = WFEngine::GetStartLogIdByEndAt($id_pi,$id_t);
				if(is_array($lastpl)){
					foreach($lastpl as $item){
						$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>NULL),"ID_PL=".$item['ID_PL']);
					}
				}
				//Xoa log
				$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PL=".$id_pl);
                                //xoa butphe
                                $db->delete(QLVBDHCommon::Table("HSCV_BUTPHE"),"NGUOICHUYEN=".$user_id);
				//Cap nhat lai process item
				$db->update(QLVBDHCommon::Table("WF_PROCESSITEMS"),array("ID_A"=>$id_a_begin,"ID_U"=>$user_id,"LASTCHANGE"=>date("Y-m-d H:i:s")),"ID_PI=".$id_pi);
				return 1;
			}else{
				//xoa toan bo log
				$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PI=".$lastlog['ID_PI']);
				//xoa item
				$db->delete(QLVBDHCommon::Table("WF_PROCESSITEMS"),"ID_PI=".$lastlog['ID_PI']);
				//xoa hscv
				$db->delete(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),"ID_PI=".$lastlog['ID_PI']);
				return 2;
			}
		}else{
			return 0;
		}
	}
	static function CopyProcess($object_id,$name,$idreceive,$type,$nhiemvus=array(),$wf_nexttransition,$usersend,$wf_hanxuly_user,$sms,$email,$tablefk,$filedfk,$idfk){
		global $db;
             //   var_dump($nhiemvus);exit;
		//copy hscv cu
		$sql = "
			SELECT
				*
			FROM ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")."
			WHERE
				ID_HSCV = ?
		";
		$r = $db->query($sql,$object_id);
		$hscvold = $r->fetch();
		//Lay thong tin nguoi nhan
		$extra = "";
		if($type==WFEngine::$WFTYPE_USER){
			$sql="SELECT concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP WHERE u.ID_U = ?";
			$r = $db->query($sql,$idreceive);
			$u = $r->fetch();
			$extra = $u['FULLNAME'];
		}else if($type==WFEngine::$WFTYPE_DEP){
			$sql="SELECT NAME FROM QTHT_DEPARTMENTS WHERE ID_DEP = ?";
			$r = $db->query($sql,$idreceive);
			$d = $r->fetch();
			$extra = $d['NAME'];
		}else if($type==WFEngine::$WFTYPE_GROUP){
			$sql="SELECT NAME FROM QTHT_GROUPS WHERE ID_G = ?";
			$r = $db->query($sql,$idreceive);
			$d = $r->fetch();
			$extra = $d['NAME'];
		}
                if(count($nhiemvus) > 0)
                {
                    foreach($nhiemvus as $k=>$nhiemvu)
                        {
                       // var_dump($nhiemvus[$k]);exit;
                        $db->insert(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("ID_THUMUC"=>$hscvold['ID_THUMUC'],"ID_LOAIHSCV"=>$hscvold['ID_LOAIHSCV'],"NAME"=>$hscvold['NAME'],"EXTRA"=>$extra,"NGAY_BD"=>$hscvold['NGAY_BD'],"NGAY_KT"=>$hscvold['NGAY_KT'],"MACONGVIEC"=>$nhiemvus[$k]));
                        $idhscv = $db->lastInsertId(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"));
                        //copy process
                        $sql = "
                                SELECT
                                        *
                                FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")."
                                WHERE
                                        ID_PI = ?
                        ";
                        $r = $db->query($sql,$hscvold['ID_PI']);
                        $processold = $r->fetch();
                        $db->insert(QLVBDHCommon::Table("WF_PROCESSITEMS"),array(
                                "NAME"=>$name,
                                "ID_O"=>$idhscv,
                                "ID_A"=>$processold['ID_A'],
                                "ID_P"=>$processold['ID_P'],
                                "ID_U"=>$processold['ID_U'],
                                "LASTCHANGE"=>$processold['LASTCHANGE'],
                                "ID_G"=>$processold['ID_G'],
                                "ID_DEP"=>$processold['ID_DEP']
                        ));
                        $idpi = $db->lastInsertId(QLVBDHCommon::Table("WF_PROCESSITEMS"));
                        //cap nhat lai hscv
                        $db->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("ID_PI"=>$idpi),"ID_HSCV=".$idhscv);

                        //copy log
                        $sql = "
                                SELECT
                                        *
                                FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")."
                                WHERE
                                        ID_PI = ?
                                ORDER BY ID_PL
                        ";
                        $r = $db->query($sql,$processold['ID_PI']);
                        $logold = $r->fetchAll();
                        foreach($logold as $logitem){
                                $db->insert(QLVBDHCommon::Table("WF_PROCESSLOGS"),array(
                                        "ID_U_SEND"=>$logitem['ID_U_SEND'],
                                        "ID_U_RECEIVE"=>$logitem['ID_U_RECEIVE'],
                                        "ID_PI"=>$idpi,
                                        "ID_T"=>$logitem['ID_T'],
                                        "DATESEND"=>$logitem['DATESEND'],
                                        "ID_A_BEGIN"=>$logitem['ID_A_BEGIN'],
                                        "ID_A_END"=>$logitem['ID_A_END'],
                                        "ID_P"=>$logitem['ID_P'],
                                        "HANXULY"=>$logitem['HANXULY'],
                                        "TRE"=>0,
                                        "NOIDUNG"=>$logitem['NOIDUNG'],
                                        "ID_G_RECEIVE"=>$logitem['ID_G_RECEIVE'],
                                        "ID_DEP_RECEIVE"=>$logitem['ID_DEP_RECEIVE']
                                ));
                        }
                    WFEngine::SendNextUserByObjectId2($idhscv,$wf_nexttransition,$usersend,$idreceive,WFEngine::$WFTYPE_USER,$name,$wf_hanxuly_user,$sms,$email);
                    WFEngine::UpdateAfterCopy($idhscv);
                    $db->insert(QLVBDHCommon::Table($tablefk),array($filedfk=>$idfk,"ID_HSCV"=>$idhscv));
                    $configlienthong = new Zend_Config_Ini('../application/config.ini', 'general');
                    $madonvi = $configlienthong->service->lienthong->username;
                    $password = $configlienthong->service->lienthong->password;
                    $giaoviecservice = new GiaoViecService();
                    $model = new UsersModel();
                    $nguoinhan = $model->getName($idreceive);
                    $user_dep = UsersModel::getUserDepId($idreceive);
                    $token = $giaoviecservice->login($madonvi,md5($password),"");
                    $giaoviecservice->createNhatKy(
                        $token
                        ,$nhiemvus[$k]
                        ,$idreceive
                        ,$nguoinhan['TENNGUOITAO']
                        ,0
                        ,'Bút phê chuyển xử lý'
                        ,$user_dep['NAME']
                    );
                    }
                }else {
                $db->insert(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("ID_THUMUC"=>$hscvold['ID_THUMUC'],"ID_LOAIHSCV"=>$hscvold['ID_LOAIHSCV'],"NAME"=>$hscvold['NAME'],"EXTRA"=>$extra,"NGAY_BD"=>$hscvold['NGAY_BD'],"NGAY_KT"=>$hscvold['NGAY_KT']));
		$idhscv = $db->lastInsertId(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"));
		//copy process
		$sql = "
			SELECT
				*
			FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")."
			WHERE
				ID_PI = ?
		";
		$r = $db->query($sql,$hscvold['ID_PI']);
		$processold = $r->fetch();
		$db->insert(QLVBDHCommon::Table("WF_PROCESSITEMS"),array(
			"NAME"=>$name,
			"ID_O"=>$idhscv,
			"ID_A"=>$processold['ID_A'],
			"ID_P"=>$processold['ID_P'],
			"ID_U"=>$processold['ID_U'],
			"LASTCHANGE"=>$processold['LASTCHANGE'],
			"ID_G"=>$processold['ID_G'],
			"ID_DEP"=>$processold['ID_DEP']
		));
		$idpi = $db->lastInsertId(QLVBDHCommon::Table("WF_PROCESSITEMS"));
		//cap nhat lai hscv
		$db->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("ID_PI"=>$idpi),"ID_HSCV=".$idhscv);
		
		//copy log
		$sql = "
			SELECT
				*
			FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")."
			WHERE
				ID_PI = ?
			ORDER BY ID_PL
		";
		$r = $db->query($sql,$processold['ID_PI']);
		$logold = $r->fetchAll();
		foreach($logold as $logitem){
			$db->insert(QLVBDHCommon::Table("WF_PROCESSLOGS"),array(
				"ID_U_SEND"=>$logitem['ID_U_SEND'],
				"ID_U_RECEIVE"=>$logitem['ID_U_RECEIVE'],
				"ID_PI"=>$idpi,
				"ID_T"=>$logitem['ID_T'],
				"DATESEND"=>$logitem['DATESEND'],
				"ID_A_BEGIN"=>$logitem['ID_A_BEGIN'],
				"ID_A_END"=>$logitem['ID_A_END'],
				"ID_P"=>$logitem['ID_P'],
				"HANXULY"=>$logitem['HANXULY'],
				"TRE"=>0,
				"NOIDUNG"=>$logitem['NOIDUNG'],
				"ID_G_RECEIVE"=>$logitem['ID_G_RECEIVE'],
				"ID_DEP_RECEIVE"=>$logitem['ID_DEP_RECEIVE']
			));
		}
		return $idhscv;
                }
        }
	static function UpdateAfterCopy($idhscv){
		global $db;
		//Lay process item tu hscv
		$sql = "
			SELECT * FROM ".QLVBDHCommon::Table("WF_PROCESSITEMS")." WHERE ID_O = ?
		";
		$r = $db->query($sql,$idhscv);
		$pi = $r->fetch();
		$db->update(QLVBDHCommon::Table("WF_PROCESSLOGS"),array("ISSPLIT"=>1),"ID_PI=".$pi['ID_PI']);
	}
	static function UpdateExtra($idhscv,$idreceive,$type){
		global $db;
		$extra = "";
		if($type==WFEngine::$WFTYPE_USER){
			$sql="SELECT concat(emp.FIRSTNAME,' ',emp.LASTNAME) as FULLNAME FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP WHERE u.ID_U = ?";
			$r = $db->query($sql,$idreceive);
			$u = $r->fetch();
			$extra = $u['FULLNAME'];
		}else if($type==WFEngine::$WFTYPE_DEP){
			$sql="SELECT NAME FROM QTHT_DEPARTMENTS WHERE ID_DEP = ?";
			$r = $db->query($sql,$idreceive);
			$d = $r->fetch();
			$extra = $d['NAME'];
		}else if($type==WFEngine::$WFTYPE_GROUP){
			$sql="SELECT NAME FROM QTHT_GROUPS WHERE ID_G = ?";
			$r = $db->query($sql,$idreceive);
			$d = $r->fetch();
			$extra = $d['NAME'];
		}
		$db->update(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),array("EXTRA"=>$extra),"ID_HSCV=".$idhscv);
	}
	static function IsFinishTransition($transition_id){
		global $db;
		//lay activity end
		$sql = "SELECT * FROM WF_TRANSITIONS WHERE ID_T = ? AND ISLAST=1";
		$r = $db->query($sql,$transition_id);
		if($r->rowCount()==0)return false;
		return true;
	}
	static function IsVaoSoTransition($transition_id){
		global $db;
		//lay activity end
		$sql = "SELECT * FROM WF_TRANSITIONS WHERE ID_T = ? AND IS_VAOSO=1";
		$r = $db->query($sql,$transition_id);
		if($r->rowCount()==0)return false;
		return true;
		//check xem co di tiep duoc hok
	}
	static function GetHanXuLy($transition_id){
		global $db;
		$r = $db->query("SELECT HANXULY FROM WF_TRANSITIONS WHERE ID_T=?",$transition_id);
		$hanxuly = $r->fetch();
		$hanxuly = $hanxuly['HANXULY'];
		return $hanxuly;
	}
	static function GetAccessUserFromTransition($transition_id){
		global $db;
		$r = $db->query("
			SELECT
				0 as TYPE, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_BEGIN
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				0 as TYPE, '-1' AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_BEGIN
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				1 as TYPE, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=? AND U.ACTIVE = 1
			UNION ALL
			SELECT
				1 as TYPE, '-1' AS ID_DEP, U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.ORDERS
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=? AND U.ACTIVE = 1
			ORDER BY ORDERS,NAME 
		",array($transition_id,$transition_id,$transition_id,$transition_id));
		$user = $r->fetchAll();
		return $user;
	}

	static function GetAccessUserFromTransition_nb($transition_id){
		global $db;
		$r = $db->query(" SELECT * FROM 
			(SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE TR.ID_T=?
			UNION
			SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				TR.ID_T=?
			UNION
			SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN fk_users_groups fkg on fkg.ID_G = AC.ID_G
				INNER JOIN QTHT_USERS U ON U.ID_U = fkg.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE
				TR.ID_T=?
			ORDER BY NAME ) unb
			GROUP BY unb.ID_U
			ORDER BY NAME
		",array($transition_id,$transition_id,$transition_id));
		$user = $r->fetchAll();
		return $user;
	}

	static function GetAccessUserFromTransitionNoGroup($transition_id){
		global $db;
		$r = $db->query("
			SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_USERS U ON AC.ID_U=U.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE U.ACTIVE = 1 AND TR.ID_T=?
			UNION
			SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = AC.ID_DEP
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_DEP = DEP.ID_DEP
				INNER JOIN QTHT_USERS U ON U.ID_EMP = E.ID_EMP
			WHERE
				U.ACTIVE = 1 AND TR.ID_T=?
			UNION
			SELECT
				U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
			FROM
				WF_ACTIVITYACCESSES AC
				INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
				INNER JOIN fk_users_groups fkg on fkg.ID_G = AC.ID_G
				INNER JOIN QTHT_USERS U ON U.ID_U = fkg.ID_U
				INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
			WHERE
				U.ACTIVE = 1 AND TR.ID_T=?
			ORDER BY NAME
		",array($transition_id,$transition_id,$transition_id));
		$user = $r->fetchAll();
		return $user;
	}
	static function GetAccessDepFromTransition($transition_id){
		global $db;
		$r = $db->query("
		SELECT
			0 as TYPE, DEP.ID_DEP,DEP.NAME,DEP.ID_U_DAIDIEN
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_BEGIN
			INNER JOIN QTHT_DEPARTMENTS DEP ON AC.ID_DEP=DEP.ID_DEP
		WHERE
			TR.ID_T=? AND DEP.ACTIVE = 1
		UNION ALL
		SELECT
			1 as TYPE, DEP.ID_DEP,DEP.NAME,DEP.ID_U_DAIDIEN
		FROM 
			WF_ACTIVITYACCESSES AC
			INNER JOIN WF_TRANSITIONS TR ON AC.ID_A=TR.ID_A_END
			INNER JOIN QTHT_DEPARTMENTS DEP ON AC.ID_DEP=DEP.ID_DEP
		WHERE
			TR.ID_T=? AND DEP.ACTIVE = 1
		ORDER BY ID_DEP,NAME
		",array($transition_id,$transition_id));
		$dep = $r->fetchAll();
		return $dep;
	}
	static function Test($x){
		if($x==1) return true;
		return false;
	}
	static function CanChuyenLaiForVTBP($object_id){
		global $db;
		$r = $db->query("
		SELECT
			count(*) as CNT
		FROM 
			".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
			inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI
		WHERE
			pi.ID_O = ?
		",array($object_id));
		$dep = $r->fetch();
		if($dep["CNT"]==3)return true;
		return false;
	}
	static function CanBP($object_id){
		global $db;
		$r = $db->query("
		SELECT
			count(*) as CNT
		FROM 
			".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
			inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI
		WHERE
			pi.ID_O = ?
		",array($object_id));
		$dep = $r->fetch();
		if($dep["CNT"]==2)return true;
		return false;
	}
	static function GetBPTransition($object_id){
		global $db;
		$r = $db->query("
		SELECT
			pl.*
		FROM 
			".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
			inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI
		WHERE
			pi.ID_O = ?
		ORDER BY ID_PL
		",array($object_id));
		$dep = $r->fetch();
		$dep = $r->fetch();
		return $dep["ID_T"];
	}
	static function GetBPInfo($object_id){
		global $db;
		$r = $db->query("
		SELECT
			pl.*
		FROM 
			".QLVBDHCommon::Table("WF_PROCESSITEMS")." pi
			inner join ".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl on pi.ID_PI = pl.ID_PI
		WHERE
			pi.ID_O = ?
		ORDER BY ID_PL
		",array($object_id));
		$dep = $r->fetch();
		$dep = $r->fetch();
		return $dep;
	}
	static function ChangeUBP($object_id,$idu){
		global $db;
		$db->update(QLVBDHCommon::Table("WF_PROCESSITEMS"),array("ID_U"=>$idu),"ID_O=".$object_id);
		$bpinfo = WFEngine::GetBPInfo($object_id);
		$db->update(QLVBDHCommon::Table("WF_PROCESSLOGS"),array("ID_U_RECEIVE"=>$idu),"ID_PL=".$bpinfo["ID_PL"]);
	}
	static function GetClassNameFromObjectId($object_id){
		global $db;
		$r = $db->query("
			SELECT c.ALIAS FROM
				".QLVBDHCommon::Table("WF_PROCESSITEMS")." wfp
				INNER JOIN WF_PROCESSES p on wfp.ID_P = p.ID_P
				INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
			WHERE
				wfp.ID_O = ?
		",$object_id);
		$r = $r->fetch();
		return $r['ALIAS'];
	}
	static function GetBeginTransition($ALIAS, $ID_DEP){
		global $db;
		$r = $db->query("
			SELECT tr.ID_T FROM
				WF_PROCESSES p
				INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
				INNER JOIN WF_TRANSITIONS tr on tr.ID_P = p.ID_P
			WHERE
				c.ALIAS = ? AND
				p.ID_DEP = ? AND
				tr.ISFIRST = 1
		",array($ALIAS, $ID_DEP));
		$r = $r->fetch();
		if($r['ID_T']>0){
			return $r['ID_T'];
		}else{
			$r = $db->query("
				SELECT tr.ID_T FROM
					WF_PROCESSES p
					INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
					INNER JOIN WF_TRANSITIONS tr on tr.ID_P = p.ID_P
				WHERE
					c.ALIAS = ? AND
					(p.ID_DEP is null or p.ID_DEP=0) AND
					tr.ISFIRST = 1
			",array($ALIAS));
			$r = $r->fetch();
			return $r['ID_T'];
		}
	}
	static function GetAllBeginTransition($ALIAS, $ID_DEP){
		global $db;
		$r = $db->query("
			SELECT tr.ID_A_BEGIN FROM
				WF_PROCESSES p
				INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
				INNER JOIN WF_TRANSITIONS tr on tr.ID_P = p.ID_P
			WHERE
				c.ALIAS = ? AND
				p.ID_DEP = ? AND
				tr.ISFIRST = 1
		",array($ALIAS, $ID_DEP));
		$r = $r->fetch();
		if($r['ID_T']>0){
			$ida = $r['ID_A_BEGIN'];
		}else{
			$r = $db->query("
				SELECT tr.ID_A_BEGIN FROM
					WF_PROCESSES p
					INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
					INNER JOIN WF_TRANSITIONS tr on tr.ID_P = p.ID_P
				WHERE
					c.ALIAS = ? AND
					(p.ID_DEP is null or p.ID_DEP=0) AND
					tr.ISFIRST = 1
			",array($ALIAS));
			$r = $r->fetch();
			$ida = $r['ID_A_BEGIN'];
		}
		$sql = "SELECT wt.*,wp.ALIAS FROM WF_TRANSITIONS wt INNER JOIN WF_TRANSITIONPOOLS wp ON wt.ID_TP = wp.ID_TP  WHERE ID_A_BEGIN = ?";
		return $db->query($sql,array($ida))->fetchAll();
	}
	static function GetIdLoaiHSCVFromIdT($idt){
		global $db;
		$r = $db->query("
			SELECT lhs.ID_LOAIHSCV FROM
				WF_PROCESSES p
				INNER JOIN HSCV_LOAIHOSOCONGVIEC lhs on lhs.MASOQUYTRINH = p.ALIAS
				INNER JOIN WF_TRANSITIONS tr on tr.ID_P = p.ID_P
			WHERE
				tr.ID_T = ?
		",array($idt));
		$r = $r->fetch();
		return $r['ID_LOAIHSCV'];
	}
        
        static function GetProcessLogByObjectId2($objectid){
		 global $db;
		 $sql= "
		 	SELECT
		 		unc.ID_U as ID_U_NC,
		 		unn.ID_U as ID_U_NN,
				pl.ID_U_EXECUTE,
				unc.USERNAME as USERNAME_NC,
				unn.USERNAME as USERNAME_NN,
		 		concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,
				concat(empnn.FIRSTNAME,' ',empnn.LASTNAME) as EMPNNNAME,
				concat(empex.FIRSTNAME,' ',empex.LASTNAME) as EMPEXNAME,
				g.NAME as GROUPNAME,
				dep.NAME as DEPNAME,
				tr.NAME,
				pl.DATESEND,
				pl.HANXULY,
				pl.TRE,
				pl.NOIDUNG,
				hscv.`NAME` as HSCV_NAME,
				hscv.`EXTRA`,
				hscv.IS_THEODOI,
				hscv.IS_CHOXULY,
				pl.ID_PL,
				cd.NAME as CHUCDANH
		 	FROM
		 		".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
		 		inner join ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." hscv on pl.ID_PI = hscv.ID_PI
		 		inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
				inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
				left join QTHT_USERS unn on unn.ID_U = pl.ID_U_RECEIVE
				left join QTHT_EMPLOYEES empnn on unn.ID_EMP = empnn.ID_EMP
				left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
				left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
				left join QTHT_USERS uex on uex.ID_U = pl.ID_U_EXECUTE
				left join QTHT_EMPLOYEES empex on uex.ID_EMP = empex.ID_EMP
				inner join WF_TRANSITIONS tr on tr.ID_T = pl.ID_T
				inner join WF_TRANSITIONPOOLS tp on tr.ID_TP = tp.ID_TP
				left join qtht_chucdanh cd ON cd.ID_CD = empnn.ID_CD
			WHERE
				hscv.ID_HSCV in (?)
			ORDER BY ID_PL
			LIMIT 1,999
		 ";
		 $r = $db->query($sql, explode(',', $objectid));
		return $r->fetchAll();
	}
	static function GetClassNameFromProcessId($process_id){
		global $db;
		$r = $db->query("
			SELECT c.ALIAS FROM
				WF_PROCESSES p
				INNER JOIN WF_CLASSES c on p.ID_C = c.ID_C
			WHERE
				p.ID_P = ?
		",$process_id);
		$r = $r->fetch();
		return $r['ALIAS'];
	}
        
        static function getUserXulicuoiVBDen($id_hscv)
	{
            global $db;
            $nguoiXuLyChinh=array();
            $r = $db->query("
                    SELECT hscv.ID_HSCV,dt.ID_DUTHAO,dt.NGUOISOAN,
                        concat(emp.FIRSTNAME,' ',emp.LASTNAME) as NAME_NGUOISOAN,
                        pi.IS_FINISH
                    FROM ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
                    join ".QLVBDHCommon::Table("wf_processitems")." pi on pi.ID_PI= hscv.ID_PI
                    join wf_processes p on p.ID_P= pi.ID_P
                    join wf_classes c on c.ID_C = p.ID_C
                    left join ".QLVBDHCommon::Table("hscv_duthao")." dt on hscv.ID_HSCV =dt.ID_HSCV
                    left join QTHT_USERS u on u.ID_U = dt.NGUOISOAN
                    left join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
                    WHERE c.ALIAS='VBD' and hscv.ID_HSCV = ? ;
            ",$id_hscv);
            $dataCheckDuThao = $r->fetch();
            //kiem tra van ban co du thao
            if((int)$dataCheckDuThao['ID_DUTHAO']>0){
                //nguoi xu li chinh la nguoi tao du thao 
                $nguoiXuLyChinh['ID_U']=$dataCheckDuThao['NGUOISOAN'];
                $nguoiXuLyChinh['NAME']=$dataCheckDuThao['NAME_NGUOISOAN'];                
                $nguoiXuLyChinh['STATE']=0;//DUTHAO
            }else{
                //nguoc lai ko co thi lay nguoi cuoi theo dong luan chuyen
                $r = $db->query("
                    SELECT 
                            hscv.ID_HSCV,
                            concat(empnc.FIRSTNAME,' ',empnc.LASTNAME) as EMPNCNAME,unc.ID_U as ID_U_NC,
                            concat(empnk.FIRSTNAME,' ',empnk.LASTNAME) as EMPNKNAME,unk.ID_U as ID_U_NK,
                            g.NAME as GROUPNAME,
                            dep.NAME as DEPNAME
                    FROM
                            ".QLVBDHCommon::Table("hscv_hosocongviec")." hscv
                            inner join ".QLVBDHCommon::Table("wf_processlogs")." pl on hscv.ID_PI = pl.ID_PI
                            inner join QTHT_USERS unc on unc.ID_U = pl.ID_U_SEND
                            inner join QTHT_EMPLOYEES empnc on unc.ID_EMP = empnc.ID_EMP
                            left join QTHT_USERS unk on unk.ID_U = pl.ID_U_RECEIVE
                            left join QTHT_EMPLOYEES empnk on unk.ID_EMP = empnk.ID_EMP
                            left join QTHT_GROUPS g on g.ID_G = pl.ID_G_RECEIVE
                            left join QTHT_DEPARTMENTS dep on dep.ID_DEP = pl.ID_DEP_RECEIVE
                            WHERE hscv.ID_HSCV= ?
                    ORDER BY
                            pl.ID_PL DESC;
                ",$id_hscv);
                $dataDongLuanChuyen = $r->fetch();
                
                $nguoiXuLyChinh['ID_U']=$dataDongLuanChuyen['ID_U_NK'];
                $nguoiXuLyChinh['NAME']=$dataDongLuanChuyen['EMPNKNAME'];
                $nguoiXuLyChinh['STATE']=1;//KHAC
            }
            $nguoiXuLyChinh['IS_FINISH']=$dataCheckDuThao['IS_FINISH'];
            return $nguoiXuLyChinh;
	}
        static function RollBackXuLy($idhscv,$user_id,$nodelete=false){
		global $db;
		//Lấy log cuối cùng
		$sql="SELECT pl.*
		FROM
			".QLVBDHCommon::Table("WF_PROCESSLOGS")." pl
			INNER JOIN (SELECT max(ID_PL) as ID_PL FROM ".QLVBDHCommon::Table("WF_PROCESSLOGS")." GROUP BY ID_PI) t on t.ID_PL = pl.ID_PL
			INNER JOIN ".QLVBDHCommon::Table("WF_PROCESSITEMS")." p on pl.ID_PI = p.ID_PI
		WHERE
			ID_U_RECEIVE = ? AND p.ID_O = ?
		";
                
		$r = $db->query($sql,array($user_id,$idhscv));
		if($r->rowCount()==1){
			$lastlog = $r->fetch();
			///return 0;
			if($lastlog['ISSPLIT']!=1 || $nodelete==true){
				$id_a_begin = $lastlog['ID_A_BEGIN'];
				$id_a_end = $lastlog['ID_A_END'];
				$id_p = $lastlog['ID_P'];
				$id_pi = $lastlog['ID_PI'];
				$id_pl = $lastlog['ID_PL'];
				$id_t = $lastlog['ID_T'];
				//update tre lai
				$lastpl = WFEngine::GetStartLogIdByEndAt($id_pi,$id_t);
				if(is_array($lastpl)){
					foreach($lastpl as $item){
						$db->update(QLVBDHCommon::Table("wf_processlogs"),array("TRE"=>NULL),"ID_PL=".$item['ID_PL']);
					}
				}
				//Xoa log
				$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PL=".$id_pl);
				//xoa butphe
				//$db->delete(QLVBDHCommon::Table("HSCV_BUTPHE"),"NGUOICHUYEN=".$user_id);
				//Cap nhat lai process item
				$db->update(QLVBDHCommon::Table("WF_PROCESSITEMS"),array("ID_A"=>$id_a_begin,"ID_U"=>$lastlog['ID_U_SEND'],"LASTCHANGE"=>date("Y-m-d H:i:s")),"ID_PI=".$id_pi);
				return 1;
			}else{
				//xoa toan bo log
				$db->delete(QLVBDHCommon::Table("WF_PROCESSLOGS"),"ID_PI=".$lastlog['ID_PI']);
				//xoa item
				$db->delete(QLVBDHCommon::Table("WF_PROCESSITEMS"),"ID_PI=".$lastlog['ID_PI']);
				//xoa hscv
				$db->delete(QLVBDHCommon::Table("HSCV_HOSOCONGVIEC"),"ID_PI=".$lastlog['ID_PI']);
				return 2;
			}
		}else{
			return 0;
		}
	}
}