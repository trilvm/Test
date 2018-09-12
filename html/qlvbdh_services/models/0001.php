<?php
require_once '../../db/common.php';


    function GetService($function_name,$param) {
		switch ($function_name) {
			
			case "Login":
				return GetUserName($param);
				break;
			case "Logout":
				return LogoutFunction($param);
				break;
			case "ThoiGian":
				return LayGioHeThong();
				break;
			case "LayNgayGioPhutGiay":
				return LayNgayGioPhutGiay();
				break;
			// Thong bao ban den(chuyen de biet) chi lay 1 lan
			case "VBDmoi":
				return VBDmoiFunction($param);
				break;
			// Kiem tra da xem hay chua
			case "VBDmoiDaLayVeChuaXem":
				return VBDmoiDaLayVeChuaXemFunction($param);
				break;
			// Thong bao co van ban di(chuyen de biet) chi lay 1 lan
			case "VBDImoi":
				return VBDImoiFunction($param);
			break;	
			case "VBDImoiDaLayVeChuaXem":
				return VBDImoiDaLayVeChuaXemFunction($param);
			break;			
			//Thong bao co lich cong tac chi lay 1 lan 
			case "AlarmLCT":
				return AlarmLCTFunction($param);
				break;
			case "UpdateAlarmLCT":
				return UpdateAlarmLCTFunction($param);
				break;
			case "NNAlarmLCT":
				return NNAlarmLCTFunction($param);
				break;	
			case "HuyLCT":
				return HuyLCTFunction($param);
			case "KiemTraThoiGianConLai":
				return KiemTraThoiGianConLaiFunction($param);
			// co cong viec moi den(xu ly van ban den,xu ly cong viec)
			//Thong bao co HSCV moi(can xu ly) chi lay 1 lan 
			case "HSCVmoi":
				return HSCVmoiFunction($param);
				break;	
			case "Ngaynghi":
				return NgaynghiFunction($param);
				break;	
			case "LoadChuDeTraoDoi":
				return LoadChuDeTraoDoiFunction($param);
			case "DongBoNhanTraodoi":
				return DongBoNhanTraodoiFunction($param);
			case "DongBoGuiTraodoi":
				return DongBoGuiTraodoiFunction($param);
			case "DSGroup":
				return DSGroupFunction($param);
			case "DSPhong":
				return DSPhongFunction($param);
			case "DSUsers":
				return DSUsersFunction($param);
			case "InsertMessage":
				return InsertMessageFunction($param);
			case "NhanNNMessage":
				return NhanNNMessageFunction($param);
			case "GuiNNMessage":
				return GuiNNMessageFunction($param);
			case "ThoiGuiNNMessage":
				return ThoiGuiNNMessageFunction($param);
			case "ThoiNhanNNMessage":
				return ThoiNhanNNMessageFunction($param);
			case "Upload":
				return UploadFunction($param);
			case "InsertSessionUpload":
				return InsertSessionUploadFunction($param);
			case "NoiFile":
				return NoiFileFunction($param);
			case "DSUsersForChat":
				return DSUsersForChatFunction($param);
			case "DeQuiCallMenuPhong":
				return DeQuiCallMenuPhongFunction($param);
			case "PingTrangThaiUser":
				return pingTrangThaiUserFunction($param);
			case "CheckTrangThaiUser":
				return checkTrangThaiUserFunction($param);
			case "GetCurentUser":
				return GetCurentUserFunction($param);
			case "GetRoom":
				return GetRoomFunction($param);
			case "Sendmessage":
				return SendmessageFunction($param);
			case "Sendmessagegroup":
				return SendmessagegroupFunction($param);
			case "SendFile":
				return SendFileFunction($param);
			case "BeginSendFile":
				return BeginSendFileFunction($param);
			case "SendFileGroup":
				return SendFileGroupFunction($param);
			case "BeginSendFileGroup":
				return BeginSendFileGroupFunction($param);
			case "GetTotalPart":
				return GetTotalPartFunction($param);
			case "BeginDownFile":
				return BeginDownFileFunction($param);
			case "CheckAcceptGet":
				return CheckAcceptGetFunction($param);
			case "CheckCancelFile":
				return CheckCancelFileFunction($param);
			case "GetCancelFiles":
				return GetCancelFilesFunction($param);
			case "CheckStatustFile":
				return CheckStatustFileFunction($param);
			case "DeletePart":
				return DeletePartFunction($param);
			case "DeleteFileCancel":
				return DeleteFileCancelFunction($param);
			case "Updatevisible":
				return UpdatevisibleFunction($param);
			case "Getmessage":
				return GetmessageFunction($param);
			case "GetmessageForGroup":
				return GetmessageForGroupFunction($param);
			case "GetGroup":
				return GetGroupFunction($param);
			case "GetGroupMember":
				return GetGroupMemberFunction($param);
			case "AddMemberToGroup":
				return AddMemberToGroupFunction($param);
			case "ConfirmInitGroup":
				return ConfirmInitGroupFunction($param);
			case "ConfirmUpdateGroup":
				return ConfirmUpdateGroupFunction($param);
			case "CheckAvatarAndFeelingMessageUser":
				return CheckAvatarAndFeelingMessageUserFunction($param);
			case "SetAvatar":
				return SetAvatarFunction($param);
			case "GetAvatar":
				return GetAvatarFunction($param);
			case "Updatefeelingmessage":
				return UpdatefeelingmessageFunction($param);
			case "ConfirmDownCompleted":
				return ConfirmDownCompletedFunction($param);
			case "Typing":
				return TypingFunction($param);
			case "TypingGroup":
				return TypingGroupFunction($param);
			case "CheckMemberTypingGroup":
				return CheckMemberTypingGroupFunction($param);
			case "Congviengiaotrehan":
					return DSCVGiaoTreHanFunction($param);
			default:
				return "the service name not found";
		}
    }
	
	function TypingGroupFunction($param)
	{
		$param=explode("~",$param);
		$id_room=$param[0];
		$users_typing= $param[1];
		$option_type=$param[2];
		if($option_type=="UP")
		{
			$sqlTypingGroup=sprintf(
			"update des_group set IS_TYPING='Y' where GROUP_NAME='%s' and GROUP_MEMBER='%s' and IS_TYPING!='Y'",
			mysql_real_escape_string($id_room),
			mysql_real_escape_string($users_typing)
			);
			$rsUserTyping = query($sqlTypingGroup);
		}
		else if($option_type=="CA")
		{
			$sqlCancelTypingGroup=sprintf(
			"update des_group set IS_TYPING='N' where GROUP_NAME='%s' and GROUP_MEMBER='%s' and IS_TYPING!='N'",
			mysql_real_escape_string($id_room),
			mysql_real_escape_string($users_typing)
			);
			$rssqlCancelTypingGroup = query($sqlCancelTypingGroup);
		}
		return "";
	}
	
	function CheckMemberTypingGroupFunction($param) {
		
		$param=explode("~",$param);
		$id_room=$param[0];
		$users_typing= str_replace("|"," or GROUP_MEMBER= ",$param[1]);
		$where_clause=" and ( GROUP_MEMBER=".$users_typing.")";
		$sqlCheckUserTyping=sprintf(
					"select GROUP_MEMBER from des_group where GROUP_NAME='%s' and IS_TYPING='Y'".$where_clause,
					 mysql_real_escape_string($id_room)
					);
		//return $sqlCheckUserTyping;
		$rowsUserTyping = query($sqlCheckUserTyping);
		$rs_users_typing="";
		while ($row = mysql_fetch_array($rowsUserTyping)) 
		{
			$rs_users_typing=$row[0].'~'.$rs_users_typing;
		}
		return substr($rs_users_typing,0,-1);
	
    }
	
	function TypingFunction($param)
	{
		$param=explode("~",$param);
		$id_room=$param[0];
		$user_typing=$param[1];
		$option_type=$param[2];
		
		$checkUser=sprintf(
				"select USER1 from des_room where ID_R='%s' and USER1='%s'",
				 mysql_real_escape_string($id_room),
				 mysql_real_escape_string($user_typing)
				);
		$rowUser = query($checkUser);
		if(mysql_num_rows($rowUser)>=1)
		{
			$fieldTyping="USER1TYPING";
			$fieldCheckTyping="USER2TYPING";
		}
		else
		{
			$fieldTyping="USER2TYPING";
			$fieldCheckTyping="USER1TYPING";
		}
		
		if($option_type=="CA"||$option_type=="UP")
		{
			if($option_type=="CA")
			{
				$action_update="N";
			}
			else if($option_type=="UP")
			{
				$action_update="Y";
			}
			$where_clause=" and ".$fieldTyping."!='".$action_update."'";
			$sqlUpdateTyping = sprintf(
				"update des_room set ".$fieldTyping."='%s' where ID_R='%s' ".$where_clause,
				 mysql_real_escape_string($action_update),
				 mysql_real_escape_string($id_room)
				);	
				//return $sqlUpdateTyping;
			return	$rows = query($sqlUpdateTyping);
		}
		else if($option_type=="CH")
		{
			$checkUserTyping=sprintf(
					"select ".$fieldCheckTyping." from des_room where ID_R='%s'",
					 mysql_real_escape_string($id_room)
					);
			$rowUserTyping = query($checkUserTyping);
			
			while ($UserTyping = mysql_fetch_array($rowUserTyping)) 
			{
				return $UserTyping[0];
			}
		}
	}
	//chat
	function ConfirmDownCompletedFunction($param) {
		$param=explode("~",$param);
		$ID_SEND_FILE=$param[0];
		$TOTAL_MEMBER=$param[1];
		$sql = sprintf(
				"select STATUST_FILE from des_send_file where ID_SEND_FILE='%s' and STT_PART=1",
				 mysql_real_escape_string($ID_SEND_FILE)
				);
		$rows = query($sql);
		while ($STT_MEMBER_DOWN = mysql_fetch_array($rows)) {
				$STT_MEMBER_DOWN=$STT_MEMBER_DOWN[0];
		}
		if($STT_MEMBER_DOWN==$TOTAL_MEMBER)
		{
			DeletePartFunction($ID_SEND_FILE);
		}
		else
		{
			$STT_MEMBER_DOWN++;
			$sql = sprintf(
				"Update STATUST_FILE set STATUST_FILE='%s' where ID_SEND_FILE='%s' and STT_PART=1",
				 mysql_real_escape_string($STT_MEMBER_DOWN),
				 mysql_real_escape_string($ID_SEND_FILE)
				);
			query($sql);
		}
    }
	
	 function AddMemberToGroupFunction($param) {
		$param=explode("~",$param);
		
		$BOSS_GROUP=$param[0];
		$GROUP_NAME=$param[1];
		$GROUP_MEMBER=$param[2];
		$members=explode("|",$GROUP_MEMBER);
		$TITLE=base64_decode($param[3]);
		$sqlDeleteGroup= sprintf(
			"delete from des_group where GROUP_NAME='%s'",
			mysql_real_escape_string($GROUP_NAME)
			);
		query($sqlDeleteGroup);
		
		foreach($members as $member)
		{
			//if($BOSS_GROUP==$member)
			{
				//$is_boss='Y';
			}
			//else
			{
				//$is_boss='N';
			}
			$sqlnewgroup = sprintf(
			"insert into des_group(GROUP_NAME,IS_BOSS_GROUP,GROUP_MEMBER,IS_UPDATE_GROUP,TITLE_GROUP) value('%s','%s','%s','Y','%s') ",
			mysql_real_escape_string($GROUP_NAME),
			mysql_real_escape_string($BOSS_GROUP),
			mysql_real_escape_string($member),
			mysql_real_escape_string($TITLE)
			);
			query($sqlnewgroup);
		}
		//return $GROUP_NAME;
    }
	
	function ConfirmUpdateGroupFunction($param) {
		$param=explode("~",$param);
		//$groupName=str_replace("GROUPNODE","",$param[0]);
		$groupName=$param[0];
		$user_member=$param[1];
		$sql = sprintf(
				"Update des_group set IS_UPDATE_GROUP='N' where GROUP_NAME ='%s' and GROUP_MEMBER ='%s'",
				 mysql_real_escape_string($groupName),
				 mysql_real_escape_string($user_member)
				);
		
		$rows = query($sql);
    }
	function GetGroupFunction($param)
	{
		$sql = sprintf(
			"Select DGR.* from des_group  DGR 
			where DGR.GROUP_MEMBER='%s' 
			and DGR.IS_UPDATE_GROUP='Y' 
			group by DGR.GROUP_NAME",
			mysql_real_escape_string($param)
			);
		
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	function GetGroupMemberFunction($param)
	{
		//$groupName=str_replace("GROUPNODE","",$param);
		$groupName=$param;
		$sql = sprintf(
			"Select DGR.* from des_group DGR where DGR.GROUP_NAME='%s'",
			mysql_real_escape_string($groupName)
			);
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	
	
	function CheckAcceptGetFunction($param)
	{
			$sql = sprintf(
				"Select IS_DOWN from des_send_file where ID_SEND_FILE ='%s' and STT_PART=1",
				 mysql_real_escape_string($param)
				);
			$rows = query($sql);
			while ($IS_DOWN = mysql_fetch_array($rows)) {
				$IS_DOWN=$IS_DOWN[0];
			}
			return $IS_DOWN;
	}
	function DeletePartFunction($param)
	{
			$sql = sprintf(
				"Delete from des_send_file where ID_SEND_FILE ='%s' and IS_DOWN='%s'",
				 mysql_real_escape_string($param),mysql_real_escape_string('Y')
				);
			$rows = query($sql);
	}
	function DeleteFileCancelFunction($param)
	{
			$sql = sprintf(
				"Delete from des_send_file where ID_SEND_FILE ='%s'",
				 mysql_real_escape_string($param)
				);
			$rows = query($sql);
			
			$sql = sprintf(
				"Delete from des_message where ID_SEND_FILE ='%s'",
				 mysql_real_escape_string($param)
				);
			$rows = query($sql);
			
	}
	
	function GetTotalPartFunction($param)
	{
			$sql = sprintf(
				"SELECT DESM.TOTALPART FROM des_message DESM
				where DESM.ID_SEND_FILE='%s'",
				mysql_real_escape_string($param)
				);
			$rows = query($sql);	
			while ($rowsContent = mysql_fetch_array($rows)) {
				$Content=$rowsContent[0];
			}	
			return $Content;
	}
	function BeginDownFileFunction($param)
	{
		$param=explode("~",$param);
		$Content=CheckCancelFileFunction($param[0]);
		$Content=explode("~",$Content);
		if($Content[0]=='cancel')
		{
			return $Content[0];
		}
		else
		{
				$sqlSelectContent = sprintf(
				"SELECT DESSF.CONTENT FROM des_send_file DESSF
				where DESSF.ID_SEND_FILE='%s' and DESSF.IS_DOWN='%s' and DESSF.STT_PART ='%s'",
				 mysql_real_escape_string($param[0])
				,mysql_real_escape_string('N')
				,mysql_real_escape_string($param[1])
				);
				
				$data = query($sqlSelectContent);
				while ($rowsContent = mysql_fetch_array($data)) {
				
					$Content=$rowsContent[0];
					if(count($Content)>0)
					{
						$sqlUpdate=sprintf("
						update des_send_file DESSF 
						set  DESSF.IS_DOWN='%s' 
						where DESSF.ID_SEND_FILE='%s' and DESSF.STT_PART ='%s'
						"
						,mysql_real_escape_string('Y')
						,mysql_real_escape_string($param[0])
						,mysql_real_escape_string($param[1])
						);
						query($sqlUpdate);
						
						$sqlUpdatePercent = sprintf("Update des_message set STTPARTDOWNLOADED ='%s' where ID_SEND_FILE='%s'" ,
											mysql_real_escape_string($param[1]),
											mysql_real_escape_string($param[0])
										);
						query($sqlUpdatePercent); 
					}
					else
					{
						$Content ='wait';
					}
				}
			return $Content;
		}
		
	}
    //SendFileGroup
    function SendFileGroupFunction($param) {
       $param=explode("~",$param);
	   $username=$param[0];
	  
       if (Common::CheckUserExist($username) == 1)
	   {
            //Message co cac field sau:
            //TOUSER,MESSAGE
			$touser=$param[1];
			$roomid=$param[2];
			$tenfile= base64_decode($param[3]);
			$id_send_file= $param[4];
			$STT=$param[5];
			$totalpart=$param[6];
			$isgroup=$param[7];
			$members=explode("|",$touser);
			foreach($members as $member)
			{
					$sql = sprintf("INSERT INTO DES_MESSAGE(FROMUSER,TOUSER,ROOMID,MESSAGE,IS_SEND_FILE,ID_SEND_FILE,STT,TOTALPART,IS_GROUP)VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s')",
                            mysql_real_escape_string($username),
                            mysql_real_escape_string($member),
                            mysql_real_escape_string($roomid),
							mysql_real_escape_string(''),
                            mysql_real_escape_string($tenfile),
							mysql_real_escape_string($id_send_file),
							mysql_real_escape_string($STT),
							mysql_real_escape_string($totalpart),
							mysql_real_escape_string($isgroup)
					);
					query($sql);
			}
           return mysql_insert_id();
			
            //return  $sql;
			
		   // if(query($sql)==1)
			{
				//return mysql_insert_id();
			}
			//else
			{
				//return -2;
			} 
			
		} else {
            return -2;
        }
    }
	 //SendFile
    function SendFileFunction($param) {
       $param=explode("~",$param);
	   $username=$param[0];
	  
       if (Common::CheckUserExist($username) == 1)
	   {
            //Message co cac field sau:
            //TOUSER,MESSAGE
			$touser=$param[1];
			//return $touser;
			$roomid=$param[2];
			$tenfile= base64_decode($param[3]);
			$id_send_file= $param[4];
			$STT=$param[5];
			$totalpart=$param[6];
			$isgroup=$param[7];
			$sql = sprintf("INSERT INTO DES_MESSAGE(FROMUSER,TOUSER,ROOMID,MESSAGE,IS_SEND_FILE,ID_SEND_FILE,STT,TOTALPART,IS_GROUP)VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s')",
                            mysql_real_escape_string($username),
                            mysql_real_escape_string($touser),
                            mysql_real_escape_string($roomid),
							mysql_real_escape_string(''),
                            mysql_real_escape_string($tenfile),
							mysql_real_escape_string($id_send_file),
							mysql_real_escape_string($STT),
							mysql_real_escape_string($totalpart),
							mysql_real_escape_string($isgroup)
					);
			
            //return  $sql;
			
		    if(query($sql)==1){
				return mysql_insert_id();
			}else {
				return -2;
			} ;
			
		} else {
            return -2;
        }
    }
	function BeginSendFileFunction($param)
	{
		$param=explode("~",$param);
		$ID_SEND_FILE=$param[0];
		$CONTENT=$param[1];
		$STT_PART=$param[2];
		$sql = sprintf("INSERT INTO des_send_file(ID_SEND_FILE,CONTENT,STT_PART)VALUES('%s','%s','%s')",
                            mysql_real_escape_string($ID_SEND_FILE),
                           	mysql_real_escape_string($CONTENT),
							mysql_real_escape_string($STT_PART)
						);
		return query($sql);			
	}
	
	//Get Cancel Files
	function GetCancelFilesFunction($param)
	{
		$param=str_replace('~',' DESM.ID_SEND_FILE= ',$param);
		$param=str_replace('|',' or ',$param);
		$param=substr($param,0,strlen($param)-3);
		$sqlCheckCancel= sprintf(
				"SELECT DESM.ID_SEND_FILE,DESM.WHO_CANCEL FROM des_message DESM
				where DESM.STATUST_FILE='cancel' and (".$param.")
				group by DESM.ID_SEND_FILE"
				);
				
		$reCancel = query($sqlCheckCancel);
		//return $sqlCheckCancel;
		//return  mysql_num_rows($reCancel);
		if(mysql_num_rows($reCancel)>=1)
		{
			while ($rowsContent = mysql_fetch_array($reCancel)) {
				$Content=$Content."~".$rowsContent[0]."|".$rowsContent[1];
			}
			return substr($Content,1);
		}
		else
		{
			return '0';
		}
		
	}
	//CheckCancelFile
	function CheckCancelFileFunction($param)
	{
		$sqlCheckCancel= sprintf(
				"SELECT DESM.STATUST_FILE,DESM.WHO_CANCEL FROM des_message DESM
				 where DESM.ID_SEND_FILE='%s'",
				 mysql_real_escape_string($param)
				);
		$reCancel = query($sqlCheckCancel);
		while ($rowsContent = mysql_fetch_array($reCancel)) {
			$Content=$rowsContent[0];
			$Who=$rowsContent[1];
		}
		return $Content."~".$Who;
	}
	//CheckCancelFile group
	function CheckCancelFileGroupFunction($id,$user)
	{
		$sqlCheckCancel= sprintf(
				"SELECT DESM.STATUST_FILE,DESM.WHO_CANCEL FROM des_message DESM
				 where DESM.ID_SEND_FILE='%s' and DESM.TOUSER='%s'",
				 mysql_real_escape_string($id)
				 , mysql_real_escape_string($user)
				);
		$reCancel = query($sqlCheckCancel);
		while ($rowsContent = mysql_fetch_array($reCancel)) {
			$Content=$rowsContent[0];
			$Who=$rowsContent[1];
		}
		return $Content."~".$Who;
	}
	//CheckStatustFile 
	function CheckStatustFileFunction($param)
	{
		$param=explode("~",$param);
		$Content=CheckCancelFileFunction($param[0]);
		if($param[1]=='one')
		{
			$Content=CheckCancelFileFunction($param[0]);
		}else
		{
			$Content=CheckCancelFileGroupFunction($param[0],$param[1]);
		}
		$Content=explode("~",$Content);
		if($Content[0]=='cancel')
		{
			return $Content[1];
			
		}else
		{
			$sql = sprintf("
			SELECT
				DESMESS.STTPARTDOWNLOADED
			FROM
				des_message  DESMESS
				where DESMESS.ID_SEND_FILE='%s'
			", 
				mysql_real_escape_string($param[0])
			);
			$data = query($sql);
			while ($rows = mysql_fetch_array($data)) {
				$row=$rows[0];
			}
			return $row;
		}
	}
	//phuong an 2
	function CheckStatustFileFunction2($param)
	{
		$param=explode("~",$param);
		$ID_SEND_FILE=$param[0];
		$STT_PART=$param[1];
		$Content=CheckCancelFileFunction($ID_SEND_FILE);
		
		if($Content=='cancel')
		{
			return $Content;
	
		}else
		{
			$sql = sprintf("
			SELECT
				DESF.IS_DOWN
			FROM
				des_send_file   DESF
				where DESF.ID_SEND_FILE='%s' AND DESF.STT_PART ='%s'
			", 
				mysql_real_escape_string($ID_SEND_FILE),
				mysql_real_escape_string($STT_PART)
			);
			$data = query($sql);
			while ($rows = mysql_fetch_array($data)) {
				$row=$rows[0];
			}
			return $row;
		}
		
			
		
	
	}
	function SetAvatarFunction($param)
	{
		$param=explode("~",$param);
		  $sql = sprintf("
			Update DES_LOGIN set fullAvatar ='%s',avatar ='%s' where USER ='%s'
			",
			mysql_real_escape_string($param[0]),
			mysql_real_escape_string($param[0]),
			mysql_real_escape_string($param[1])
			);
		$data = query($sql);
        return $data;
	}
	function GetAvatarFunction($param)
	{
		  $sql = sprintf("
			SELECT
				DESLOGIN.USER USERNAME,
				DESLOGIN.IDLE,
				DESLOGIN.visible,
				DESLOGIN.avatar,
				DESLOGIN.fullAvatar,
				DESLOGIN.feelingsMessage
			FROM
				DES_LOGIN DESLOGIN
				
		");
        $data = query($sql);
        return base64_encode(Common::SerializeData($data));
	}
	//check cac trang thai danh sach user
	function CheckAvatarAndFeelingMessageUserFunction($param)
	{
		  $sql = sprintf("
			SELECT
				DESLOGIN.USER USERNAME,
				DESLOGIN.IDLE,
				DESLOGIN.visible,
				DESLOGIN.avatar,
				DESLOGIN.fullAvatar,
				DESLOGIN.feelingsMessage
			FROM
				DES_LOGIN DESLOGIN
				
		");
        $data = query($sql);
        return base64_encode(Common::SerializeData($data));
	}
	//UpdatefeelingmessageFunction
	 function UpdatefeelingmessageFunction($param) {
		$param=explode("~",$param);
		$feelingsMessage=base64_decode($param[0]);
		$idUser= $param[1];
        $sql = sprintf("
						Update des_login set feelingsMessage='%s' where USER='%s'",
                        mysql_real_escape_string($feelingsMessage),
						mysql_real_escape_string($idUser)
					   );
        $data = query($sql);
        return $data;
    }
	//UpdatevisibleFunction
	 function UpdatevisibleFunction($param) {
		$param=explode("~",$param);
		$visible=$param[0];
		$idUser= $param[1];
        $sql = sprintf("
						Update des_login set visible='%s' where USER='%s'",
                        mysql_real_escape_string($visible),
						mysql_real_escape_string($idUser)
					   );
        $data = query($sql);
        return $data;
    }
	//
	function GetCurentUserFunction($param)
	{
		$sql = sprintf("
				SELECT 
					DISTINCT(U.ID_U),
					CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NAME,
					DLOG.visible,
					DLOG.avatar,
					DLOG.fullAvatar,
					DLOG.feelingsMessage,
					DLOG.USER
				FROM QTHT_USERS U
					 
					 LEFT JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					 
					 LEFT JOIN des_login DLOG ON U.USERNAME = DLOG.USER
					 
					 where U.USERNAME = '%s' 
					
 					 ORDER BY NAME ",
					 mysql_real_escape_string($param)
						);
			$rows = query($sql);
			return base64_encode(Common::SerializeData($rows));
	}
	//Get Roomid
  
	 function GetRoomFunction($param) {
	 $param=explode("~",$param);
        $sql = sprintf("SELECT ID_R FROM DES_ROOM WHERE ISPRIVATE = 1 AND ((USER1='%s' AND USER2 = '%s') OR (USER1='%s' AND USER2 = '%s'))",
                        mysql_real_escape_string($param[0])
                        , mysql_real_escape_string($param[1])
                        , mysql_real_escape_string($param[1])
                        , mysql_real_escape_string($param[0])
        );
        $r = query($sql);
        if (mysql_num_rows($r) == 1) {
            $row = mysql_fetch_assoc($r);
			
			$sqlresettyping="update DES_ROOM set USER1TYPING='N',USER2TYPING='N' where ID_R=".$row["ID_R"];
			query($sqlresettyping);
            return $row["ID_R"];
        } else {
            $sql = sprintf("INSERT INTO DES_ROOM(USER1,USER2,ISPRIVATE,NAME) VALUES('%s','%s','%s','%s')",
                            mysql_real_escape_string($param[0])
                            , mysql_real_escape_string($param[1])
                            , 1, ""
            );
            query($sql);
            $sql = "SELECT LAST_INSERT_ID() as ROOMID";
            $r = query($sql);
            $row = mysql_fetch_assoc($r);
            return $row["ROOMID"];
        }
    }
	
	
    //Sendmessage
    function SendmessageFunction($param) {
       $param=explode("~",$param);
	   $username=$param[0];
	  
       if (Common::CheckUserExist($username) == 1)
	   {
            //Message co cac field sau:
            //TOUSER,MESSAGE
			$touser=$param[1];
			$roomid=$param[2];
			$message=base64_decode($param[3]);
			$STT=$param[4];
            $sql = sprintf("INSERT INTO DES_MESSAGE(FROMUSER,TOUSER,ROOMID,MESSAGE,STT,ID_SEND_FILE)VALUES('%s','%s','%s','%s','%s','')",
                            mysql_real_escape_string($username),
                            mysql_real_escape_string($touser),
                            mysql_real_escape_string($roomid),
                            mysql_real_escape_string($message),
							mysql_real_escape_string($STT)
            );
            //return  $sql;
            return  query($sql);
        } else {
            return -2;
        }
    }
	//Sendmessagegroup
    function SendmessagegroupFunction($param) {
       $param=explode("~",$param);
	   $username=$param[0];
	  
       if (Common::CheckUserExist($username) == 1)
	   {
            //Message co cac field sau:
            //TOUSER,MESSAGE
			$touser=$param[1];
			$roomid=$param[2];
			$message=base64_decode($param[3]);
			$STT=$param[4];
			$members=explode("|",$touser);
			 //return $members;
			foreach($members as $member)
			{
				$sql = sprintf("INSERT INTO DES_MESSAGE(FROMUSER,TOUSER,ROOMID,MESSAGE,STT,ID_SEND_FILE,IS_GROUP)VALUES('%s','%s','%s','%s','%s','','Y')",
                            mysql_real_escape_string($username),
                            mysql_real_escape_string($member),
                            mysql_real_escape_string($roomid),
                            mysql_real_escape_string($message),
							mysql_real_escape_string($STT)
				 );
				query($sql);
			}
		
			//return  $sql;
			
           
        } else {
            return -2;
        }
    }
	 //Getmessage
    function GetmessageFunction($param) {
       $sql = sprintf("SELECT tam.* FROM(SELECT * FROM DES_MESSAGE WHERE TOUSER='%s' AND ISGET=0 AND ISFILE=0 AND TOUSER!=FROMUSER order by STT)tam order by tam.ROOMID",
                        mysql_real_escape_string($param));
        $data = query($sql);
        return base64_encode(Common::SerializeDataAndUpdateIsGet($data, "DES_MESSAGE", "ID_DM"));
    }
	 //Getmessage group
    function GetmessageForGroupFunction($param) {
	
       $sql = sprintf("SELECT tam.* FROM(SELECT * FROM DES_MESSAGE WHERE TOUSER='%s' AND ISGET=0 AND ISFILE=0 order by STT)tam order by tam.ROOMID",
                        mysql_real_escape_string($param));
        $data = query($sql);
        return base64_encode(Common::SerializeDataAndUpdateIsGet($data, "DES_MESSAGE", "ID_DM"));
		
	}
	//
	function DSUsersForChatFunction($param)
	{
		
			if($param!='')
			{
				$whereClause=" where DEP.ID_DEP = '%s' ";
			}
			
			$sql = sprintf("
				SELECT 
					DISTINCT(U.ID_U),
					U.ID_EMP,
					CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NAME,
					U.USERNAME,
					DESLOGIN.USER as ONLINE,
					DESLOGIN.visible,
					DESLOGIN.avatar,
					DESLOGIN.feelingsMessage,
					E.ID_DEP,
					DESLOGIN.IDLE
				FROM QTHT_USERS U
					 LEFT JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					 LEFT JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = E.ID_DEP
					 LEFT JOIN des_login DESLOGIN ON U.USERNAME=DESLOGIN.USER
					 AND DESLOGIN.`LASTACTIVITY` > ADDDATE(CURRENT_TIMESTAMP(), INTERVAL -10 SECOND)
					
					 ".$whereClause." 
					
					 ORDER BY NAME ",
					 mysql_real_escape_string($param)
						);
			$rows = query($sql);
			return base64_encode(Common::SerializeData($rows));
	}
	function pingTrangThaiUserFunction($param)
	{
		$param=explode("~",$param);
		$query="update des_login set LASTACTIVITY='%s',IDLE='%s' where USER ='%s'";
		$sql = sprintf($query,mysql_real_escape_string(date("Y-m-d H:i:s")),mysql_real_escape_string($param[0]),mysql_real_escape_string($param[1]));
		$rows = query($sql);
	}
	
	function checkTrangThaiUserFunction($param)
	{
		  $sql = sprintf("
			SELECT
				U.USERNAME,
				DESLOGIN.USER as ONLINE,
				DESLOGIN.IDLE,
				DESLOGIN.visible,
				DESLOGIN.avatar,
				DESLOGIN.fullAvatar,
				DESLOGIN.feelingsMessage
			FROM
				QTHT_USERS U
				LEFT JOIN DES_LOGIN DESLOGIN on DESLOGIN.`USER` = U.`USERNAME` 
				AND DESLOGIN.`LASTACTIVITY` > ADDDATE(CURRENT_TIMESTAMP(), INTERVAL -30 SECOND)
		");
        $data = query($sql);
        return base64_encode(Common::SerializeData($data));
	}
	function LogoutFunction($param)
	{
		$query="delete from des_login  where USER ='%s'";
		$sql = sprintf($query,mysql_real_escape_string($param));
		$rows = query($sql);
	}
	function DeQuiCallMenuPhongFunction($param)
	{
		if($param=='0')
		{
			$query="SELECT dep.* from qtht_departments dep where dep.ID_DEP_PARENT is null";
		}else
		{
			$query="SELECT dep.* from qtht_departments dep
						where dep.ID_DEP_PARENT = '%s'
						";
		}
		$sql = sprintf($query,mysql_real_escape_string($param));
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	function GetUserName($param){
		$param=explode("~",$param);
		$sql = sprintf("SELECT 
							u.ID_EMP,CONCAT(emp.FIRSTNAME,' ',emp.LASTNAME) as userName
							FROM QTHT_USERS u 
							inner join `qtht_employees` emp on emp.`ID_EMP`=u.`ID_EMP`
							where u.USERNAME = '%s' AND u.PASSWORD = '%s'",
			mysql_real_escape_string($param[0]),$param[1]);

		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	function UploadFunction($param)
	{
		$param=explode("~",$param);
		$duongDanFileConfig=base64_decode($param[0]);
		$duongDanLuuFileTam=explode("=",LayThuMucUpload($duongDanFileConfig,'file.temp_path'));
		$duongDanLuuFileTam=$duongDanFileConfig;
		$contentFile = base64_decode($param[1]);
		$tenFile = base64_decode($param[2]);
		$partIndex=$param[3];
		$loaiFile =base64_decode($param[4]);
		$numberOfFiles=$param[5];
		$profifeTam=$param[6];
		$pathMakeDir=trim($duongDanLuuFileTam).'\\'.$profifeTam;
		mkdir($pathMakeDir,0777);
		$duongDanLuuFile=$pathMakeDir.'\\'.trim($tenFile.$partIndex.$loaiFile);
		$fh = fopen($duongDanLuuFile, 'w') or die("can't open file");
		fwrite($fh, $contentFile);
		fclose($fh);
		return $duongDanLuuFile;
	}
	function NoiFileFunction($param)
	{	
		
		$param=explode("~",$param);
		$duongDanFileConfig=base64_decode($param[0]);
		$profifeTam=$param[1];
		$duongDanLuuFileTam=explode("=",LayThuMucUpload($duongDanFileConfig,'file.temp_path'));
		$duongDanLuuFileTam=$duongDanFileConfig.$profifeTam;
		
		$contentJoin="";
		$tenFile = base64_decode($param[2]);
		$loaiFile =base64_decode($param[3]);
		$yearPath=	$param[4];
		$monthPath=	$param[5];
		$maso=md5($tenFile.$loaiFile.$curentDateTime); 
		if ($handle = opendir($duongDanLuuFileTam)) 
		{
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != "..") 
				{
					$fileArray[$i]=$file;
					$i++;
				}
			}
			sort($fileArray,'SORT_STRING');
			$pathGetContent=str_replace('\\','/',$duongDanLuuFileTam).'/';
			foreach($fileArray as $file)
			{
				$contentJoin=$contentJoin.file_get_contents($pathGetContent.$file) ;
				unlink($duongDanLuuFileTam.'\\'.$file);
			}
			$duongDanLuuFile=explode("=",LayThuMucUpload($duongDanFileConfig,'file.root_dir'));
			$duongDanLuuFile=$duongDanFileConfig.$yearPath;
			if(!is_dir($duongDanLuuFile))
			{
				mkdir($duongDanLuuFile);
				
			}
			$duongDanLuuFile=$duongDanLuuFile.'\\'.$monthPath;
			if(!is_dir($duongDanLuuFile))
			{
				mkdir($duongDanLuuFile);
			}
			$fh = fopen($duongDanLuuFile.'\\'.$maso, 'w') or die("can't open file");
			fwrite($fh, $contentJoin);
			fclose($fh);
			closedir($handle);
			rmdir(substr($pathGetContent,0, -1));
			return $duongDanLuuFile.'~'.$maso;
		}
	}
	function InsertSessionUploadFunction($param)
	{
		$param=explode("~",$param);
		$duongDanLuuFile=$param[0];
		$maso=$param[1];
		$yearPath=$param[2];
		$monthPath=$param[3];
		$mime=$param[4];
		$tenFile=base64_decode($param[5]).base64_decode($param[6]);
		$type=$param[7];
		$idUser= Common::GetID_U($param[8]);
		$curentDateTime=$param[9];
		$id_thongtin = $param[10];
		
		$sql = sprintf("insert into gen_filedinhkem_".date("Y")."(FOLDER,ID_OBJECT,MASO,NAM,THANG,MIME,FILENAME,TYPE,USER,TIME_UPDATE) values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			mysql_real_escape_string($duongDanLuuFile),
			mysql_real_escape_string($id_thongtin),
			mysql_real_escape_string($maso),
			mysql_real_escape_string($yearPath),
			mysql_real_escape_string($monthPath),
			mysql_real_escape_string($mime),
			mysql_real_escape_string($tenFile),
			mysql_real_escape_string($type),
			mysql_real_escape_string($idUser),
			mysql_real_escape_string($curentDateTime)
		);
		$rows = query($sql);
	}
	function LayThuMucUpload($path,$findme)
	{
		$page = join("",file($path));
		$kw = explode("\n", $page);
		for($i=0;$i<count($kw);$i++){
			$mystring=$kw[$i];
			$pos = strpos($mystring, $findme);
			if($pos===false){}
			else{
				return $mystring;
			}
			;
		}
	}
	function LayGioHeThong(){
		return Common::GetDateTime();
	}
	function LayNgayGioPhutGiay()
	{
		return date("Y-m-d H:i");
	}
	function DSGroupFunction($param)
	{
		$sql = sprintf("
		SELECT
			G.ID_G,G.NAME 
		FROM 
			QTHT_GROUPS G
		ORDER BY G.NAME
		");
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	
	function DSPhongFunction($param)
	{
		$sql = sprintf("
		SELECT
			DEP.ID_DEP,DEP.NAME
		FROM 
			QTHT_DEPARTMENTS DEP
		ORDER BY DEP.NAME
		");
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	
	function DSUsersFunction($param)
	{
		$param=explode("~",$param);
		
		 if($param[0]=='-1' && $param[1]=='-1')
		{
			$sql = sprintf("
			 SELECT DISTINCT(U.ID_U),
					   U.ID_EMP,
					   CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NAME,U.USERNAME
				FROM QTHT_USERS U
					 INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U = U.ID_U
					 INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					 INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = E.ID_DEP
					 ORDER BY NAME
					 "
						);
			$rows = query($sql);
			return base64_encode(Common::SerializeData($rows));
		}

		if($param[0]!='-1' && $param[1]!='-1')
		{
			$whereClause=" where UG.ID_G = '%s' and DEP.ID_DEP = '%s'";
		}
		else if($param[0]!='-1' || $param[1]!='-1')
		{
			$whereClause=" where UG.ID_G = '%s' or DEP.ID_DEP = '%s'";
		}
		
		$sql = sprintf("
		 SELECT DISTINCT(U.ID_U),
                   U.ID_EMP,
                   CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NAME ,U.USERNAME
            FROM QTHT_USERS U
                 INNER JOIN FK_USERS_GROUPS UG ON UG.ID_U = U.ID_U
                 INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
                 INNER JOIN QTHT_DEPARTMENTS DEP ON DEP.ID_DEP = E.ID_DEP".
				 $whereClause." ORDER BY NAME"
		,mysql_real_escape_string($param[0]),mysql_real_escape_string($param[1]));
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
		
	}
	function NhanNNMessageFunction($param)
	{
		 $idUser=  Common::GetID_U($param);
		 $sql = sprintf("
			select TTN.id_nhan,TTN.ngaygui,TT.noidung,TT.id_thongtin,TT.nguoitao,US.ID_EMP, CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NGUOIGUI,TT.tieude
            from td_nhan_".date("Y")." TTN 
            left join td_thongtin_".date("Y")." TT on TTN.id_thongtin=TT.id_thongtin
            left join QTHT_USERS US on TT.nguoitao=US.ID_U
            left join QTHT_EMPLOYEES E on US.ID_EMP=E.ID_EMP
            WHERE TTN.nguoinhan ='%s'  AND TTN.danhan=0 AND TTN.is_alert=0 AND TTN.ngaynhan is null
		",mysql_real_escape_string($idUser));
		$rows = query($sql);
		if(count($rows)>0)
		{
		 $sql = sprintf("
			update td_nhan_".date("Y")." set is_alert=1 where nguoinhan='%s' and is_alert=0
		",mysql_real_escape_string($idUser));
		 query($sql);
		}
		return base64_encode(Common::SerializeData($rows));
	}
	function DongBoNhanTraodoiFunction($param)
	{
		 $idUser=  Common::GetID_U($param);
		 $sql = sprintf("
			select TTN.id_nhan
            from td_nhan_".date("Y")." TTN 
            WHERE TTN.nguoinhan ='%s'  AND TTN.is_alert=1  AND TTN.ngaynhan is not null
		",mysql_real_escape_string($idUser));
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	
	function GuiNNMessageFunction($param)
	{
		 $idUser=  Common::GetID_U($param);
		 $sql = sprintf("
		 select tam.id_thongtin,tam.ngaytao,tam.noidung,GROUP_CONCAT(tam.NGUOIGUI) as goichoNN FROM
         (
            select TTN.id_nhan,TT.ngaytao,TT.noidung,TT.id_thongtin,TT.nguoitao,US.ID_EMP, CONCAT(E.FIRSTNAME, ' ', E.LASTNAME) as NGUOIGUI
            from td_thongtin_".date("Y")." TT 
            left join td_nhan_".date("Y")." TTN on TT.id_thongtin=TTN.id_thongtin
            left join QTHT_USERS US on TTN.nguoinhan=US.ID_U
            left join QTHT_EMPLOYEES E on US.ID_EMP=E.ID_EMP
            WHERE TT.nguoitao ='%s'  AND TT.is_alert=0  AND TT.hienthi=1
         ) tam group by tam.id_thongtin
		",mysql_real_escape_string($idUser));
		$rows = query($sql);
		if(count($rows)>0)
		{
		 $sql = sprintf("
			update td_thongtin_".date("Y")." set is_alert=1 where nguoitao='%s' and is_alert=0
		",mysql_real_escape_string($idUser));
		query($sql);
		}
		
		
		return base64_encode(Common::SerializeData($rows));
	}
	function DongBoGuiTraodoiFunction($param)
	{
		 $idUser=  Common::GetID_U($param);
		 $sql = sprintf("
			select TT.id_thongtin
            from td_thongtin_".date("Y")." TT 
            WHERE TT.nguoitao ='%s'  AND TT.is_alert=1  AND TT.hienthi=0
		",mysql_real_escape_string($idUser));
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	function LoadChuDeTraoDoiFunction($param)
	{
		$sql = sprintf("SELECT cd.* FROM td_chude cd where cd.trangthai=1");
		$rows = query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	function InsertMessageFunction($param)
	{
		$param=explode("~",$param);
		$usNguoiGoi=$param[0];
		$idUser=  Common::GetID_U($usNguoiGoi);
		$tieuDe=base64_decode($param[1]);
		$noidung=base64_decode($param[2]);
		$idChuDe=$param[3];
		if($idChuDe=='-1')
		{
			$idChuDe='0';
		}
		$sql = sprintf("insert into td_thongtin_".date("Y")."(id_chude,nguoitao,tieude,noidung,ngaytao) values('%s','%s','%s','%s','%s')",
			mysql_real_escape_string($idChuDe),
			mysql_real_escape_string($idUser),
			mysql_real_escape_string($tieuDe),
			mysql_real_escape_string($noidung),
			date("Y-m-d H:i:s")	
		);
		$rows = query($sql);
		$id_Thongtin = mysql_insert_id();
		
		$sql = sprintf("SELECT tt.id_thongtin FROM td_thongtin_".date("Y")." tt WHERE tt.nguoitao='%s' order by id_thongtin desc limit 1 ",mysql_real_escape_string($idUser));
        $rs = query($sql);
        if(mysql_num_rows($rs)==1){
        	$id_thongtin = mysql_fetch_assoc($rs);
        }
		
		if(count($param[4])>0)
		{
			$chuoiIdUserNhan=$param[4];
			$UserNhan=explode(",",$chuoiIdUserNhan);
			updateNguoiNhanTraoDoi($UserNhan,'k',$id_thongtin['id_thongtin']);
			
			if(count($param[5])>0)
			{
				$chuoiIdUserNhanCC=$param[5];
				$UserNhanCC=explode(",",$chuoiIdUserNhanCC);
				updateNguoiNhanTraoDoi($UserNhanCC,'c',$id_thongtin['id_thongtin']);
			}
		}

		return base64_encode($id_Thongtin);
	}
	function updateNguoiNhanTraoDoi($idUsers,$is_cc,$id_thongtin)
	{
		if($is_cc=='k')
		{
			foreach($idUsers as $idUser)
			{
				$id_nguoinhan=  Common::GetID_U($idUser);
				$sql = sprintf("insert into td_nhan_".date("Y")."(id_thongtin,ngaygui,nguoinhan) values('%s','%s','%s')",
				mysql_real_escape_string($id_thongtin),
				date("Y-m-d H:i:s"),
				mysql_real_escape_string($id_nguoinhan)
				);
				$rows = query($sql);
			}
		}else if($is_cc=='c')
		{
			foreach($idUsers as $idUser)
			{
				$id_nguoinhan=  Common::GetID_U($idUser);
				$sql = sprintf("insert into td_nhan_".date("Y")."(id_thongtin,ngaygui,nguoinhan,is_cc) values('%s','%s','%s','%s')",
				mysql_real_escape_string($id_thongtin),
				date("Y-m-d H:i:s"),
				mysql_real_escape_string($id_nguoinhan),
				mysql_real_escape_string('1')
				);
				$rows = query($sql);
			}
		}
		
	}
	

    function VBDmoiFunction($param){

        $sql = sprintf("
			SELECT
			dlc.NGAYCHUYEN,dlc.ID_VBD, d.TRICHYEU, CONCAT(emp1.FIRSTNAME,' ',emp1.LASTNAME) as NGUOICHUYEN,d.SOKYHIEU,d.NGAYBANHANH
			FROM 
			VBD_DONGLUANCHUYEN_".date("Y")." dlc 
			inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
			inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
			inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U 			
			inner join VBD_VANBANDEN_".date("Y")." d on dlc.ID_VBD=d.ID_VBD
		WHERE u.USERNAME ='%s' AND dlc.IS_ALERT =1 AND dlc.DA_XEM=0 ORDER BY dlc.NGAYCHUYEN DESC 
		",mysql_real_escape_string($param));
		$rows = query($sql);

		$sql = sprintf("
			update VBD_DONGLUANCHUYEN_".date("Y")." vbd SET vbd.IS_ALERT =2 where vbd.IS_ALERT =1 and vbd.NGUOINHAN in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param));	
		query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	 function VBDmoiDaLayVeChuaXemFunction($param){

        $sql = sprintf("
			SELECT
			dlc.NGAYCHUYEN,dlc.ID_VBD as ID_MESSAGE
			FROM 
			VBD_DONGLUANCHUYEN_".date("Y")." dlc 
			inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
			inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
			inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U 			
			inner join VBD_VANBANDEN_".date("Y")." d on dlc.ID_VBD=d.ID_VBD
		WHERE u.USERNAME ='%s' AND dlc.IS_ALERT =2 AND dlc.DA_XEM=1 ORDER BY dlc.NGAYCHUYEN DESC 
		",mysql_real_escape_string($param));
		$rows = query($sql);

		return base64_encode(Common::SerializeData($rows));
	}
	
	
	
	 function VBDImoiFunction($param){
		
        $sql = sprintf("
			SELECT 
				dlc.NGAYCHUYEN,dlc.ID_VBDI,d.TRICHYEU,CONCAT(emp1.FIRSTNAME,' ',emp1.LASTNAME) as NGUOICHUYEN,d.SOKYHIEU,d.NGAYBANHANH
			FROM
				VBDI_DONGLUANCHUYEN_".date("Y")." dlc
				inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
				inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
				inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U
				inner join VBDI_VANBANDI_".date("Y")." d on dlc.ID_VBDI=d.ID_VBDI
			WHERE u.USERNAME ='%s' AND dlc.IS_ALERT =1  AND dlc.DA_XEM=0  ORDER BY dlc.NGAYCHUYEN
		",mysql_real_escape_string($param));
		$row = query($sql);
	    $sql = sprintf("
			update VBDI_DONGLUANCHUYEN_".date("Y")." vbdi SET vbdi.IS_ALERT =2 where vbdi.IS_ALERT =1 and vbdi.NGUOINHAN in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param));	
		query($sql);
		
		
		return   base64_encode(Common::SerializeData($row));
	}

	 function VBDImoiDaLayVeChuaXemFunction($param){
		
        $sql = sprintf("
			SELECT 
				dlc.NGAYCHUYEN,dlc.ID_VBDI as ID_MESSAGE
			FROM
				VBDI_DONGLUANCHUYEN_".date("Y")." dlc
				inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
				inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
				inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U
				inner join VBDI_VANBANDI_".date("Y")." d on dlc.ID_VBDI=d.ID_VBDI
			WHERE u.USERNAME ='%s' AND dlc.IS_ALERT =2 AND dlc.DA_XEM=1 ORDER BY dlc.NGAYCHUYEN DESC
		",mysql_real_escape_string($param));
		$row = query($sql);
	  	return   base64_encode(Common::SerializeData($row));
	}

	function AlarmLCTFunction($param)
	{	
		//AND lct.NHACNHO - INTERVAL lct.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."'
		// lct.NHACNHO - INTERVAL lct.BEFORE*60 SECOND < '".date("Y-m-d H:i:s")."' AND
		$sql = sprintf("
			SELECT
				lct.ID_LCT_P, lct.NOIDUNG, lct.DIADIEM,lct.NHACNHO,lct.NGAY,lct.GHICHU,lct.BEFORE
			FROM
				LCT2_PERSONAL_".date("Y")." lct
			inner join QTHT_USERS u on lct.ID_U = u.ID_U
			inner join QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP
		WHERE
			lct.NHACNHO IS NOT NULL 
			
			AND				
			
			lct.IS_ALERT=1 and  u.USERNAME ='%s'  ORDER BY lct.NGAY DESC
		",mysql_real_escape_string($param));
		 $row = query($sql);  
		 $sql = sprintf("
			 update LCT2_PERSONAL_".date("Y")." lct SET lct.IS_ALERT=0 where  
			 lct.NHACNHO IS NOT NULL and
			
			 lct.ID_U in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param));
		 query($sql);	

		return   base64_encode(Common::SerializeData($row));
	}
	function UpdateAlarmLCTFunction($param)
	{			
		$param=explode("~",$param);
	    $sql = sprintf("
			 update LCT2_PERSONAL_".date("Y")." lct SET lct.IS_ALERT=0 where  lct.ID_LCT_P='%s' and
			 lct.ID_U in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param[0]),mysql_real_escape_string($param[1]));		
	   return query($sql);	
		
	}
	function HuyLCTFunction($param)
	{
		$param=explode("~",$param);
		$idLCTs=explode(",",$param[0]);
		foreach($idLCTs as $idLCT)
		{
			 $sql = sprintf("
			update LCT2_PERSONAL_".date("Y")." lct SET lct.IS_ALERT=0 where  lct.ID_LCT_P='%s' and
			 lct.ID_U in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($idLCT),mysql_real_escape_string($param[1]));	
		query($sql);
		}
	}
	function KiemTraThoiGianConLaiFunction($param)
	{
		$sql = sprintf("
			SELECT
				lct.NHACNHO,lct.BEFORE
			FROM
				LCT2_PERSONAL_".date("Y")." lct
			WHERE
			lct.ID_LCT_P='%s'
		",mysql_real_escape_string($param));
		$row = query($sql);  
		return   base64_encode(Common::SerializeData($row));
	}
	

	

	// ho so cong viec moi cho xu ly(chua update)
    function HSCVmoiFunction($param){
        $sql = sprintf("
				select
				   hscv.ID_HSCV,
				   hscv.`NAME`,
				   hscv.NGAY_KT,
				   wfl.ID_U_RECEIVE,
				   wfl.ID_U_SEND,
				   class.ALIAS,
				   wfl.DATEEND,
				   wfl.DATESEND,
				   wfl.IS_ALERT,
				   wfl.HANXULY,
				   CONCAT(emp1.FIRSTNAME, ' ', emp1.LASTNAME) as NGUOICHUYEN
				   FROM hscv_hosocongviec_".date("Y")." hscv
				INNER JOIN wf_processitems_".date("Y")." wfitem ON hscv.ID_PI = wfitem.ID_PI
				INNER JOIN wf_processlogs_".date("Y")." wfl ON wfl.ID_PI = wfitem.ID_PI
				INNER JOIN WF_PROCESSES wfp1 ON wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class ON class.ID_C = wfp1.ID_C
				INNER JOIN QTHT_USERS u1 ON wfl.ID_U_SEND = u1.ID_U
				INNER JOIN `qtht_employees` emp1 ON emp1.`ID_EMP` = u1.`ID_EMP`
				INNER JOIN QTHT_USERS u on wfl.ID_U_RECEIVE = u.ID_U 
				WHERE (1 = 1) AND
					  (IS_THEODOI <> 1) AND
					  (hscv.IS_CHOXULY = 0 OR
					  hscv.IS_CHOXULY is NULL) AND 
					  IS_DXCHOXL <> 1 AND
					  hscv.ID_THUMUC = 1 AND
					  u.USERNAME ='%s' 
					  AND wfl.IS_ALERT =1
				ORDER BY wfl.ID_PL DESC",mysql_real_escape_string($param));
		$row = query($sql);
		$sql = sprintf("
			update WF_PROCESSLOGS_".date("Y")." wfl SET wfl.IS_ALERT =0 where wfl.IS_ALERT =1 and wfl.ID_U_RECEIVE in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param));	
		query($sql);
		
		return   base64_encode(Common::SerializeData($row));
	}
	


	 function NgaynghiFunction($param){
        $sql = sprintf("
			SELECT
			  *
			FROM
			gen_nonworkingdates");
		$rows = query($sql);		
		return base64_encode(Common::SerializeData($rows));
	}
        
        
        function DSCVGiaoTreHanFunction($username){
        
        $sql=sprintf("select ID_U FROM qtht_users where USERNAME='%s'", mysql_real_escape_string($username));
        $row = query($sql);
        while($row= mysql_fetch_array($row))
			{
			  $ID_U= $row['ID_U'];
			}
        $sql = sprintf("
				select
				   hscv.ID_HSCV,
				   hscv.`NAME`,
				   hscv.NGAY_KT,
				   wfl.ID_U_RECEIVE,
				   wfl.ID_U_SEND,
				   class.ALIAS,
				   wfl.DATEEND,
				   wfl.DATESEND,
				   wfl.IS_ALERT,
				   wfl.HANXULY,
				   CONCAT(emp1.FIRSTNAME, ' ', emp1.LASTNAME) as NGUOICHUYEN
				   FROM hscv_hosocongviec_".date("Y")." hscv
				INNER JOIN wf_processitems_".date("Y")." wfitem ON hscv.ID_PI = wfitem.ID_PI
				INNER JOIN (
				SELECT * FROM (select * from wf_processlogs_".date("Y")." wflog1
                                                where wflog1.ID_U_RECEIVE=%d and TRE >0
				ORDER BY wflog1.`ID_PL` DESC) wflog 
				GROUP by wflog.`ID_PI`) wfl ON wfl.ID_PI = wfitem.ID_PI
				INNER JOIN WF_PROCESSES wfp1 ON wfp1.ID_P = wfitem.ID_P
				INNER JOIN WF_CLASSES class ON class.ID_C = wfp1.ID_C
				INNER JOIN QTHT_USERS u1 ON wfl.ID_U_SEND = u1.ID_U
				INNER JOIN `qtht_employees` emp1 ON emp1.`ID_EMP` = u1.`ID_EMP`
				INNER JOIN QTHT_USERS u on wfl.ID_U_RECEIVE = u.ID_U 
				WHERE (1 = 1) AND
					  (IS_THEODOI <> 1) AND
					  (hscv.IS_CHOXULY = 0 OR
					  hscv.IS_CHOXULY is NULL) AND 
					  IS_DXCHOXL <> 1 AND
					  hscv.ID_THUMUC = 1 
				ORDER BY wfl.ID_PL DESC",($ID_U));
		$row = query($sql);
		return   base64_encode(Common::SerializeData($row));
	}
	
?>