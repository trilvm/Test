<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * Author:Tuanpp
 * 
 */

class Backup {
    
    public function SendBackup($parameter) {
        //gửi yêu cầu sao luu
        //tham số truyền vào :  filename~filesize~backupdate~content~folder~is_ok
        $params = Common::Deseriallize($parameter);
        
        $file_name = base64_decode(mysql_real_escape_string($params[0]));
        $file_size = base64_decode(mysql_real_escape_string($params[1]));
        $backup_date = base64_decode(mysql_real_escape_string($params[2]));
        $content = base64_decode(mysql_real_escape_string($params[3]));
        $folder = mysql_real_escape_string($params[4]);
        $is_ok = base64_decode(mysql_real_escape_string($params[5]));

        //build query
        $sql = sprintf("Insert into qtht_backup(FILE_NAME,FILE_SIZE,BACKUP_DATE,CONTENT,FOLDER,IS_OK)
                        VALUES ('%s',%d,'%s','%s','%s',%d)",$file_name,$file_size,$backup_date,$content,$folder,$is_ok);
//        return $sql;
        if(mysql_query($sql))
            return 1;
        else
            return -1;
    }

    public function ReceiveBackupConfig() {
        //lấy thông tin thiết lập sao lưu mới nhất
        //build query
        $sql = sprintf("SELECT `TYPE`, `TIME`, `TYPE_VALUE`,
            `FOLDER`, `CREATE_DATE`
                        FROM qtht_backup_config 
                        ORDER BY CREATE_DATE desc
                        LIMIT 1");
        $result = query($sql);
        if (mysql_num_rows($result)>0){
			// xoa quick
            $ret = base64_encode (Common::SerializeData ($result));
			$dql = "delete from qtht_backup_config where IS_QUICK = 1";
			query($sql);
			return $ret;
        }else{
            return 0;
		}
    }
        
}
?>
