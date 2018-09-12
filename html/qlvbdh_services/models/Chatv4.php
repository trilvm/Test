<?php

class Chat {
    /*   Hàm chứng thực người dùng
      Auth($session)
      ^.^   Dev : HUNGDH   ^.^
      Parameters: id_sesssion
     */

    public static function Auth($session) {

        // select các thông tin từ bảng DES_SESSION điều kiện  SESSION_ID= với giá trị session get về được
        $sql = sprintf("SELECT USERNAME,SESSION_ID,LAST_DATE FROM DES_SESSION where SESSION_ID = '%s' ", mysql_real_escape_string($session));
        $result = @mysql_query($sql);
        $row = @mysql_fetch_array($result);
        $today = date("Y-m-d H:i:s");
        // kiểm tra giờ hiện tại có lớn hơn giờ trong bảng có quá 5 phút hay ko?
        $minutes = (strtotime($today) - strtotime($row[2])) / 60;
        if ($minutes <= 5) {
            //Nếu bé hơn 5 phút thì cho cập nhật lại LAST_DATE và trả lại username,session_id
            $sql = sprintf("UPDATE DES_SESSION set LAST_DATE= CURRENT_TIMESTAMP() where SESSION_ID='%s'", mysql_real_escape_string($session));
            $result = query($sql);
            if ($result)
                return $row;
            else
                return 1;
        }
        else {
            return 0;
        }
    }

	public static function AuthByUsername($username,$password){
		// select các thông tin từ bảng DES_SESSION điều kiện  SESSION_ID= với giá trị session get về được
        $sql = sprintf("SELECT USERNAME FROM QTHT_USERS where USERNAME = '%s' AND PASSWORD= '%s'"
		, mysql_real_escape_string($username)
		, mysql_real_escape_string($password)
		);
        $result = @mysql_query($sql);
        $row = @mysql_fetch_array($result);
        return $row;
	}

    /*
      ^.^   Dev : HUNGDH   ^.^
      Login LoginDes($username,$password)
      parames  Gồm 2 tham số là username và password

     */

    public static function Login($username, $password) {

        //Select thông tin với điều kiện username và password
        $sql = sprintf("select qt.USERNAME as USER_QT, dl.USERNAME as USERS , ds.USERNAME 
                        from qtht_users qt
                        Left Join des_login dl on qt.USERNAME= dl.USERNAME
                        Left Join des_session ds on qt.USERNAME=ds.USERNAME
                        where
                        qt.USERNAME='%s' and qt.PASSWORD='%s' and qt.ACTIVE=1", mysql_real_escape_string($username), mysql_real_escape_string(($password))
        );
        $result = query($sql);
        //Nếu đăng nhập thành công
        if (mysql_num_rows($result) > 0) {
            $sid = md5(rand(1, 1000000000) . $username . md5(time()));
            $row = mysql_fetch_array($result);
            //Nếu người này chưa có tài khoản trong des_login thì tạo mới
            if ($row['USERS'] == null) {
                $sql = sprintf("INSERT INTO des_login (USERNAME,LASTACTIVITY) VALUES('%s',CURRENT_TIMESTAMP())", mysql_real_escape_string($username));
                $result1 = query($sql);
            }
            //Không thì cập nhật LASTACTIVITY vào bảng des_login
            else {
                $sql = sprintf("UPDATE des_login SET LASTACTIVITY=CURRENT_TIMESTAMP() , VISIBLE=0 WHERE USERNAME='%s' ", mysql_real_escape_string($username));
                $result1 = query($sql);
            }
            //Nếu người này chưa có tài khoản trong des_session thì tạo mới
            if ($row['USERNAME'] == NULL) {
                $sql = sprintf("INSERT into des_session  (USERNAME, SESSION_ID, IS_FIRST_LOGIN ,LAST_DATE)
                               values ('%s','%s',1,CURRENT_TIMESTAMP())", mysql_real_escape_string($username), mysql_real_escape_string($sid));
                $result2 = query($sql);
            }
            //Không thì cập nhật LAST_DATE vào bảng des_session
            else {
                $sql = sprintf("UPDATE des_session set SESSION_ID='%s' , LAST_DATE= CURRENT_TIMESTAMP() 
                              where USERNAME='%s'", mysql_real_escape_string($sid), mysql_real_escape_string($username));
                $result2 = query($sql);
            }
            if ($result1 && $result2)
                return $sid;
            else
                return 0;
        }
        else
            return 0;
    }

    /*
      ^.^   Dev : HUNGDH   ^.^
      Hàm GetRoom($parameters)
      $parames  là chuỗi được quy định   : ID_ROOM hoặc Tên bạn chát~ User

     */

    public static function GetRoom($parameters) {
        $params = Common::Deseriallize($parameters);
        $thamso1 = ($params[0]);
        $user = $params[1];
        $sql = sprintf(" select ds.ID_RU, dr.ISPRIVATE, dr.ID_R, dr.NAME,dr.USER1,dr.USER2, ds.USER 
                        from des_room dr
                        Left Join des_roomuser ds on dr.ID_R= ds.ID_R
                        where (dr.USER2='%s' and dr.USER1= '%s') or (dr.USER2='%s' and dr.USER1= '%s') or dr.ID_R=%d", mysql_real_escape_string($thamso1), mysql_real_escape_string($user), mysql_real_escape_string($user), mysql_real_escape_string($thamso1), mysql_real_escape_string($thamso1)
        );
        $result1 = query($sql);

        //Nếu chưa có room nào nên tạo mới room và trả lại id_room mới tạo
        if (mysql_num_rows($result1) == 0) {
            $nameroom = $thamso1 . '-' . $user;
            $sql = sprintf("insert into des_room (NAME,ISPRIVATE,USER1,USER2) values ('%s',1,'%s','%s')", mysql_real_escape_string($nameroom), mysql_real_escape_string($user), mysql_real_escape_string($thamso1));
            $result = query($sql);


            if ($result)
                return ('7' . '~' . 'ID_RU' . '~' . 'ISPRIVATE' . '~' . 'ID_R' . '~' . 'NAME' . '~' . 'USER1' . '~' . 'USER2' . '~' . 'USER' . '~' . '0' . '~' . '1' . '~' . mysql_insert_id() . '~' . $nameroom . '~' . $user . '~' . $thamso1 . '~' . ' ');
        }
        //Nếu đã có room thì trả lại danh sách ID_ROM, NAME,danh sách bạn bè
        else {
            //$array=array_unique($row); 

            return (Common::SerializeData($result1));
        }
    }

    /*
      ^.^   Dev : HUNGDH   ^.^
      Hàm SendMessage($parameters)
      $parames  là chuỗi encode được quy định   : (ID_Room)~base64(message)~base64(user)
     */

    public static function SendMessage($parameters) {
        //Xuất tham số ra mảng
        $params = Common::Deseriallize($parameters);
        $id_r = ($params[0]);
        $mess = base64_decode ($params[1]);
        $user = base64_decode ($params[2]);

        //Kiểm tra xem ID_ROOM gởi lên là PRIVATE hay PUBLIC
        $sql = sprintf("SELECT dr.USER1, dr.USER2,du.`USER`
                           FROM des_room dr 
                           Left JOIN des_roomuser du  on dr.ID_R=du.ID_R
                           WHERE  dr.ID_R= %d ", $id_r);
        $result = query($sql); 
        //Nếu Room này là PRIVATE thì lấy tên bạn chat trong bảng des_room
        if (mysql_num_rows($result) == 1) {
            $row = mysql_fetch_assoc($result);

            $array[] = ($row['USER2'] == $user ? $row['USER1'] : $row['USER2']);
        }
        //Nếu Room này PUBLIC thì lấy danh sách bạn chat trong bảng des_roomuser
        else {
            $sql1 = sprintf("select USER
                                from des_roomuser where USER='%s' and ID_R=%d", $user, $id_r);
            $result1 = query($sql1);
            if (mysql_num_rows($result1) > 0) {
                // // $i=-1;
                while ($row = mysql_fetch_assoc($result)) {
                    //   $i++;
                    //   $array[$i]=$row['USER']; 
                    $array[] = $row['USER']; 
                }
            } 
           }
            // Insert thông điệp tới bạn chat
            foreach ($array as $value) {

                $sql = sprintf("insert into des_message (FROMUSER,TOUSER,MESSAGE,DATESEND,ISGET,ROOMID) 
                                       values ('%s','%s','%s',CURRENT_TIMESTAMP(),%d,%d)", mysql_real_escape_string($user), mysql_real_escape_string($value), mysql_real_escape_string($mess), ($user == $value ? 1 : 0), mysql_real_escape_string($id_r));
                $result1 = query($sql);
            }
            if ($result1) {
                return 1;
            } else {
                return 0;
            }
        
    }

    /*
      ^.^   Dev : HUNGDH   ^.^
      Hàm ReceiveMessage()
      $parames  là chuỗi  được quy định   : USER

     */

    public static function ReceiveMessage($parameters) {
        // Chứng thật , nếu session này quá 5p thì trả về 0
        $user = ($parameters);
        // Chọn tất cả tin nhắn được gởi tới mình điều kiện là isget là 1
        $sql = sprintf("select ID_DM,FROMUSER,MESSAGE,ROOMID,DATESEND,NAME from des_message,des_room where TOUSER='%s' and ISGET=0 and des_message.ROOMID=des_room.ID_R ORDER BY DATESEND", mysql_real_escape_string($user));
        $result = query($sql);
        return Common::SerializeDataAndDelete($result, 'des_message', 'ID_DM');
    }

    /*
      ^.^   Dev : HUNGDH   ^.^
      Hàm AddUserToRoom($parameters)
      $parames  là chuỗi encode được quy định   : user~idroom~banchat1~banchat2~banchat3...

     */

    public static function AddUserToRoom($parameters) {
        $array = Common::Deseriallize($parameters);
        $user = $array[0];
        $id_r = $array[1];
        $tenroom = $user;
        $count = count($array);
        $arr_user = array();

        for ($a = 2; $a < count($array); $a++) {
            $tenroom = $tenroom . '-' . $array[$a];
            $arr_user[] = $array[$a];
        }

        $sql = sprintf("SELECT USER,USER2,ISPRIVATE from des_room dr
                        LEFT JOIN des_roomuser du on dr.ID_R=du.ID_R
                        WHERE dr.ID_R=%d", $id_r);
        $result = query($sql);
        // Nếu phòng này là private   
        if (mysql_num_rows($result) == 1) {
            $row = mysql_fetch_assoc($result);
            if ($row['USER2'] != '') {
                $arr_user[] = $row['USER2'];
                //$tenroom = $tenroom . '-' . $row['USER2'];
            }

            $arr_user[] = $user;
			$arr_user = array_unique($arr_user);
			$tenroom = implode(" - ",$arr_user);
            //tạo 1 phòng mới 
            $sql = sprintf("insert into des_room (NAME,ISPRIVATE,USER1) values ('%s',0,'%s')", mysql_real_escape_string($tenroom), mysql_real_escape_string($user)
            );
            $result = query($sql);
            if ($result)
                $id_r = mysql_insert_id();
        }
        else {
            //Nếu phòng này là public thì đưa hết danh sách USER select được vào danh sách bạn bè
            $count = $count - 1;
            while ($row = mysql_fetch_array($result)) {
                $count++;
                $arr_user[] = $row['USER'];
            }

            //Xóa tất cả bạn bè trong nhóm này
            $sql = sprintf("delete from des_roomuser where ID_R=%d", mysql_real_escape_string($id_r));
            query($sql);
        }
        $arr_user = array_unique($arr_user);

        //Chèn danh sách bạn chat vào trong ID_ROOM truyền vào bảng des_roomuser   
        for ($i = 0; $i < count($arr_user); $i++) {
            if ($arr_user[$i] != '') {
                $sql = sprintf("insert des_roomuser (ID_R,USER) values (%d,'%s')", ($id_r), mysql_real_escape_string($arr_user[$i]));
                $result = query($sql);
            }
        }

        if ($result)
            return $id_r;
        else
            return 0;
    }

    /*
      ^.^   Dev : HUNGDH   ^.^
      Hàm RemoveUser($parameters)
      $parames  là chuỗi encode được quy định   : idroom~tenbanchat~(user)

     */

    public static function RemoveUser($parameters) {
        $array = Common::Deseriallize($parameters);
        $user = $array[2];

        $sql = sprintf("select dr.ISPRIVATE, dr.USER1,dr.USER2, du.USER,count(du.USER) as count
                        from des_room dr
                        left join des_roomuser du on dr.ID_R=du.ID_R
                        where dr.ID_R=%d and dr.ISPRIVATE=0
                        Group by dr.ID_R
                        ", mysql_real_escape_string($array[0]));
        $result = query($sql);
        $row = mysql_fetch_assoc($result);
        //Nếu người đang chat là chủ room thì cho phép thực hiện kick bạn chat
        if (($row['USER1'] == $user || $array[1] == $user)) {

            //Nếu phòng này là public thì delete người này ra khỏi phòng chat des_roomuser
            $sql = sprintf("delete from des_roomuser where ID_R=%d and USER='%s'", mysql_real_escape_string($array[0]), mysql_real_escape_string($array[1])
            );
            $result = query($sql);
            if ($result) {
                if ($row['count'] == 1) {
                    $sql = sprintf("delete from des_roomuser where ID_R=%d", $array[0]);
                    query($sql);
                }
                return 1;
            } else {
                return 0;
            }
        }
        else
            return 0;
    }

    public function ReceiveUserInformation($user) {//cuongnc
        $sql = sprintf("
		SELECT *
		FROM des_login 
		WHERE USERNAME = '%s'
		", mysql_real_escape_string($user));
        $result = mysql_query($sql);
        return Common::SerializeData($result);
    }

    public function ReceiveAllUser() {//cuongnc
        $sql = sprintf("
			SELECT qtht_users.USERNAME,Concat(FIRSTNAME,' ',LASTNAME) AS FULLNAME, LASTACTIVITY, VISIBLE, UPDATE_AVATAR_DATE, `STATUS`, UPDATE_STATUS_DATE, qtht_employees.ID_DEP
			FROM qtht_users INNER JOIN qtht_employees 
					ON qtht_users.ID_EMP = qtht_employees.ID_EMP
				INNER JOIN  qtht_departments
					ON qtht_departments.ID_DEP = qtht_employees.ID_DEP  
				LEFT JOIN des_login 
					ON des_login.USERNAME = qtht_users.USERNAME
			WHERE qtht_users.ACTIVE = 1
		");
        $result = mysql_query($sql);
        return Common::SerializeData($result);
    }

    public function SendInformation($parameters) {//cuongnc
        $params = (Common::Deseriallize($parameters));
        /*
         * $params[0]: visible
         * $params[1]: fullAvatar
         * $params[2]: avatar
         * $params[3]: status
         * $params[4]: typing
         * $params[5]: room id
         * $params[6]: user
         * visible = 1: Ẩn
         * visible = 2: Bận
         * visible = 3: Ra ngoài
         * visible = 0: trực tuyến
         */


        if (!empty($params[2])) {//cuongnc
            $sql = sprintf("
				UPDATE des_login 
				SET FULL_AVATAR = '%s',AVATAR = '%s', UPDATE_AVATAR_DATE = '%s'
				WHERE USERNAME = '%s'
			", ($params[1]), ($params[2]), date("Y-m-d H:i:s"), mysql_real_escape_string($params[6]));

            if (query($sql))
                return 1;
        }
        if (!empty($params[3])) {
            $sql = sprintf("
				UPDATE des_login 
				SET STATUS = '%s', UPDATE_STATUS_DATE = '%s'
				WHERE USERNAME = '%s'
			", mysql_real_escape_string(base64_decode($params[3])), date("Y-m-d H:i:s"), mysql_real_escape_string($params[6]));
            if (query($sql))
                return 1;
        }

        if (!empty($params[4]) && !empty($params[5])) {
            $sql = sprintf("
				UPDATE des_login 
				SET TYPING = %d, ROOM_ID = %d
				WHERE USERNAME = '%s'
			", $params[4], $params[5], mysql_real_escape_string($params[6]));
            if (query($sql))
                return 1;
        }

        if (!empty($params[0])) {
            $sql = sprintf("
				UPDATE des_login 
				SET VISIBLE = %d
				WHERE USERNAME = '%s'
			", $params[0], mysql_real_escape_string($params[6]));
            if (query($sql))
                return 1;
        }
    }

    public function ReceiveUserOnline() {//cuongnc
        $sql = sprintf("
			SELECT ds.USERNAME,dl.STATUS,dl.VISIBLE,dl.LASTACTIVITY, CURRENT_TIMESTAMP()-dl.LASTACTIVITY as IDLETIME
			FROM des_session ds 
                        inner join des_login dl on ds.USERNAME=dl.USERNAME
			WHERE LAST_DATE >= '%s' and SESSION_ID <> ''
			
		", mysql_real_escape_string(date('Y-m-d H:i:s', time() - 5 * 60)));

        $result = mysql_query($sql);
        return Common::SerializeData($result);
    }

    //VuiNNT
    /*
     * @input:  $part: tên file sẽ nhận và part thứ mấy
     * @output: $data: dữ liệu trong $part của file, nếu đọc hết file thì trả về null
     */
    public static function ReceiveFile($parameters) {
        $params = Common::Deseriallize($parameters);
        $file_name = $params[0];
        $part = $params[1];
        $dir = PATH_FILE_UPLOAD;
        $dir = $dir . 'Chat/';
        $data = '';
        $fp = fopen($dir . $file_name, 'r');
        fseek($fp, $part * 32767);
        $data = fread($fp, 32767);
        return $data;
    }

    //VuiNNT
    /*
     * @input: $id_file là tên file gởi
     * @output: trạng thái - Cập nhật trạng thái của file là đã được nhận
     * 					 - Nếu tất cả các file đã được nhận thì xóa file vật lý
     */
    public static function EndSendFile($parameters) {
        $params = Common::Deseriallize($parameters);
        $id_file = $params[0];
        $to_user = $params[1];

        $dir = PATH_FILE_UPLOAD;
        $dir = $dir . 'Chat/';
        if (!is_dir($dir))
            mkdir($dir, 0700);

        $status = 0;
        $sql = sprintf("
			DELETE FROM DES_FILE  WHERE ID_FILE = '%s' AND (TO_USER = '%s' OR FROM_USER='%s')", mysql_real_escape_string($id_file), mysql_real_escape_string($to_user),
			mysql_real_escape_string($to_user)
        );
		//	return $sql;
        query($sql);
        $sql = sprintf("
			SELECT ISGET FROM DES_FILE WHERE ID_FILE = '%s'", mysql_real_escape_string($id_file)
        );
        $row = query($sql);
        if (mysql_num_rows($row) == 0) {
            unlink($dir . $id_file);
            $status = 1;
        }
        return 1;
    }

    //VuiNNT
    /*
     * @input: không có
     * @output: trả về tên của tên của những người chưa nhận file
     */
    public static function ReceiveRequestFile($parameter) {
        $sql = sprintf("
			SELECT * FROM DES_FILE WHERE ISGET = 0 and TO_USER='%s'", mysql_real_escape_string($parameter));
        $row = query($sql);
        return Common::SerializeDataAndUpdateIsGet($row, 'DES_FILE', 'ID');
    }

    //PhuongNV
    /*
     * @input:
     * @output: 
     */

    public static function SendFile($parameter) {

        $params = Common::Deseriallize($parameter);
        $maSoFile = base64_decode($params[0]);
        $content = base64_decode($params[1]);

        $path = PATH_FILE_UPLOAD;
        $path = $path . 'Chat';
        if (!is_dir($path))
            mkdir($path, 0700);
        if ($maSoFile != "") {
            $data = ($content);
            file_put_contents($path . '/' . $maSoFile, $data, FILE_APPEND | LOCK_EX);
        } else {
            $maSoFileMd5 = md5(rand(1, 1000000000) . time());
            $data = ($content);
            file_put_contents($path . '/' . $maSoFileMd5, $data, FILE_APPEND | LOCK_EX);
            $maSoFile = $maSoFileMd5;
        }

        return $maSoFile;
    }

    //PhuongNV
    /*
     * @input: id_r~maSoFile~content   
     * @output: 
     */
    public static function BeginSendFile($parameters) {
        //Xuất tham số ra mảng

        $params = Common::Deseriallize($parameters);
        $id_r = ($params[0]);
        $maSoFile = base64_decode($params[1]);
        $Filename = base64_decode($params[2]);
        $part = ($params[3]);
        $user = ($params[4]);



        //Kiểm tra xem ID_ROOM gởi lên là PRIVATE hay PUBLIC
        $sql = sprintf("SELECT dr.USER2,dr.USER1,du.`USER`
                           FROM des_room dr 
                           Left JOIN des_roomuser du  on dr.ID_R=du.ID_R
                           WHERE  dr.ID_R= %d  ", $id_r);
        $result = query($sql);
        //Nếu Room này là PRIVATE thì lấy tên bạn chat trong bảng des_room
        $array = array();
        if (mysql_num_rows($result) == 1) {
            $row = mysql_fetch_assoc($result);
            $array[] = ($row['USER2'] == $user ? $row['USER1'] : $row['USER2']);
        }
        //Nếu Room này PUBLIC thì lấy danh sách bạn chat trong bảng des_roomuser
        else {
            while ($row = mysql_fetch_assoc($result)) {

                $array[] = $row['USER'];
            }
        }
        // LUU DATABASE
        foreach ($array as $value) {
            if ($value != $user) {
                $sql = sprintf("insert into des_file (ID_FILE,FROM_USER,TO_USER,ISGET,FILE_NAME,PART,ID_ROOM,DATE_SEND) 
                                   values ('%s','%s','%s',0,'%s','%s',%d,'%s')", mysql_real_escape_string($maSoFile), mysql_real_escape_string($user), mysql_real_escape_string($value), mysql_real_escape_string($Filename), mysql_real_escape_string($part), mysql_real_escape_string($id_r), date('Y-m-d H:i:s')
                );
                query($sql);
            }
        }
        return 1;
    }

    //PhuongNV
    /*
     * @input: Là chuỗi  được quy định masofile~encode(filename)~user
     * @output: 
     */
    public static function AcceptSendFile($parameters) {

        $params = Common::Deseriallize($parameters);
        $maSoFile = ($params[0]);
        $filename = base64_decode($params[1]);
        $user = ($params[2]);

        $sql = sprintf("
	    	UPDATE des_file SET ISGET=1 WHERE TO_USER = '%s' AND ID_FILE = '%s' and FILE_NAME='%s'", mysql_real_escape_string($user), mysql_real_escape_string($maSoFile), mysql_real_escape_string($filename)
        );
        //$xml = $sql;
        query($sql);
        return 1;
    }

    public static function GetDepartments() {
        $sql = sprintf("select * from qtht_departments");
        $result = query($sql);
        return Common::SerializeData($result);
    }

    public static function Logout($sid) {
        $sql = sprintf("update des_session set SESSION_ID='' where SESSION_ID='%s'", mysql_real_escape_string($sid));
        $result = query($sql);
        if ($result)
            return 1;
        else
            return 0;
    }

    public static function GetGroupPublic($user) {
        $sql = sprintf("SELECT dr.ID_R,dro.NAME, GROUP_CONCAT(dr.`USER` SEPARATOR '-') as USER
                        FROM des_room dro
                        INNER JOIN des_roomuser dr on dr.ID_R=dro.ID_R
                        WHERE dro.ISPRIVATE=0 AND dr.`USER` = '".$user."'
                        GROUP BY dr.ID_R");

        $result = query($sql);
        return Common::SerializeData($result);
    }

    //$parameter ID_ROOM ~ base64_encode(tênroom)
    public static function ChangeNameRoom($parameters) {
        $params = Common::Deseriallize($parameters);
        $idroom = $params[0];
        $tenroom = base64_decode($params[1]);

        $sql = sprintf("UPDATE des_room dr set dr.`NAME`='%s' WHERE  dr.ID_R=%d", mysql_real_escape_string($tenroom), $idroom);

        $result = query($sql);
        if ($result)
            return 1;
        else
            return 0;
    }

	public static function BaoTri(){
		$sql = "truncate table des_message";
		query($sql);

		$sql = "select ID_FILE FROM DES_FILE";
		$result = query($sql);

		$path = PATH_FILE_UPLOAD;
        $path = $path . 'Chat';
		while ($row = mysql_fetch_assoc($result)) {
			try{
				unlink($path."/".$row["ID_FILE"]);
			}catch(Exception $ex){
			}
        }

		$sql = "truncate table des_file";
		query($sql);
		return 1;
	}
	public static function UpdateIDLE($user){
		$sql = sprintf("UPDATE des_login SET LASTACTIVITY=CURRENT_TIMESTAMP() WHERE USERNAME='%s' ", mysql_real_escape_string($user));
        query($sql);
	}
	public static function VBDmoi($user){
		$sql = sprintf("
			SELECT
			dlc.NGAYCHUYEN,dlc.ID_VBD, d.TRICHYEU, CONCAT(emp1.FIRSTNAME,' ',emp1.LASTNAME) as NGUOICHUYEN,d.SOKYHIEU,d.NGAYBANHANH
			FROM 
			VBD_DONGLUANCHUYEN_".date("Y")." dlc 
			inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
			inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
			inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U 			
			inner join VBD_VANBANDEN_".date("Y")." d on dlc.ID_VBD=d.ID_VBD
		WHERE u.USERNAME ='%s' AND dlc.DA_XEM=0 
		GROUP BY d.ID_VBD ORDER BY dlc.NGAYCHUYEN DESC
		",mysql_real_escape_string($user));
		$rows = query($sql);

		$sql = sprintf("
			update VBD_DONGLUANCHUYEN_".date("Y")." vbd SET vbd.IS_ALERT =2 where vbd.IS_ALERT =1 and vbd.NGUOINHAN in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($user));	
		query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
	public static function VBDImoi($user){
		$sql = sprintf("
			SELECT 
				dlc.NGAYCHUYEN,dlc.ID_VBDI,d.TRICHYEU,CONCAT(emp1.FIRSTNAME,' ',emp1.LASTNAME) as NGUOICHUYEN,d.SOKYHIEU,d.NGAYBANHANH
			FROM
				VBDI_DONGLUANCHUYEN_".date("Y")." dlc
				inner join QTHT_USERS u1 on dlc.NGUOICHUYEN = u1.ID_U
				inner join `qtht_employees` emp1 on emp1.`ID_EMP`=u1.`ID_EMP`
				inner join QTHT_USERS u on dlc.NGUOINHAN = u.ID_U
				inner join VBDI_VANBANDI_".date("Y")." d on dlc.ID_VBDI=d.ID_VBDI
			WHERE u.USERNAME ='%s' AND dlc.DA_XEM=0  ORDER BY dlc.NGAYCHUYEN DESC
		",mysql_real_escape_string($user));
		$row = query($sql);
	    $sql = sprintf("
			update VBDI_DONGLUANCHUYEN_".date("Y")." vbdi SET vbdi.IS_ALERT =2 where vbdi.IS_ALERT =1 and vbdi.NGUOINHAN in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($user));	
		query($sql);
		
		return   base64_encode(Common::SerializeData($row));
	}

	public static function HSCVmoi($user){
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
					  wfitem.ID_U = u.ID_U AND
					  u.USERNAME ='%s' 
				ORDER BY wfl.ID_PL DESC",mysql_real_escape_string($user));
		//return $sql;
		$row = query($sql);
		$sql = sprintf("
			update WF_PROCESSLOGS_".date("Y")." wfl SET wfl.IS_ALERT =0 where wfl.IS_ALERT =1 and wfl.ID_U_RECEIVE in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($user));	
		query($sql);
		
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
	function UpdateAlarmLCTFunction($user,$param)
	{			
	    $sql = sprintf("
			 update LCT2_PERSONAL_".date("Y")." lct SET lct.IS_ALERT=0 where  lct.ID_LCT_P='%s' and
			 lct.ID_U in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($param),mysql_real_escape_string($user));		
	   return query($sql);	
		
	}
	function HuyLCTFunction($user,$param)
	{
		$param=explode("~",$param);
		foreach($param as $idLCT)
		{
			 $sql = sprintf("
			update LCT2_PERSONAL_".date("Y")." lct SET lct.IS_ALERT=0 where  lct.ID_LCT_P='%s' and
			 lct.ID_U in (select u1.ID_U from  QTHT_USERS u1 where u1.USERNAME ='%s')
		",mysql_real_escape_string($idLCT),mysql_real_escape_string($user));	
		query($sql);
		}
	}
	function DongBoNhanTraodoiFunction($user)
	{
		 $idUser=  Common::GetID_U($user);
		 $sql = sprintf("
			select TT.ID_THONGTIN,TT.TIEUDE,TTN.NGAYGUI,CONCAT(e.FIRSTNAME,' ',e.LASTNAME) AS NGUOICHUYEN
            from td_thongtin_".date("Y")." TT 
			INNER JOIN td_nhan_".date("Y")." TTN  ON TT.ID_THONGTIN = TTN.ID_THONGTIN
			INNER JOIN qtht_users u ON TT.NGUOITAO = u.ID_U
			INNER JOIN qtht_employees e ON u.ID_EMP = e.ID_EMP
			LEFT JOIN td_chude cd ON cd.id_chude = tt.id_chude
            WHERE TTN.nguoinhan ='%s' AND TTN.ngaynhan is null AND TTN.danhan = 0 AND (tt.id_chude = cd.id_chude OR tt.id_chude is null OR tt.id_chude=0)
		",mysql_real_escape_string($idUser));
		$rows = query($sql);
		$sql = sprintf("
			update td_nhan_".date("Y")." set is_alert=0
            WHERE nguoinhan ='%s'  AND is_alert=1
		",mysql_real_escape_string($idUser));
		query($sql);
		return base64_encode(Common::SerializeData($rows));
	}
}