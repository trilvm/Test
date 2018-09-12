<?php

class PhienBan
{
     /*   Hàm cung cập lệnh cập nhật
      ^.^   Dev : HUNGDH   ^.^
      Parameters: ID_VERSION~MASOFILE
     */
    
    public static function CungCapLenhCapNhat()
    {   
       
        $sql=sprintf("select * from version_detail 
                      where IS_UPDATE=0 
                      and ID_VERSION=(select min(ID_VERSION) from  version_detail  where IS_UPDATE=0)
                     ");
        $result=@mysql_query($sql);
        return Common::SerializeData($result);   
    }
     /*   Hàm cập nhật trạng thái
      ^.^   Dev : HUNGDH   ^.^
      Parameters: ID_VERSION~MASOFILE
     */
    public static function CapNhatTrangThaiCapNhat($parameters)
    { 
        $params=  Common::Deseriallize($parameters);
        $id_ver=$params[0];
        $masofile=$params[1];
       // $id_donvi=$params[2];
        $sql=sprintf("UPDATE version_detail 
                      SET IS_UPDATE=1 WHERE ID_VERSION=%s and MASO_FILE='%s' ",  
                      $id_ver,mysql_real_escape_string($masofile)
                    ); 
        $result=@mysql_query($sql);
        if(mysql_affected_rows()) return 1;
        else return 0;
    }
    
     public static function ReadConfig($id_donvi)
    {    
        $query=sprintf("select name,value from info_config ");
        $result=@mysql_query($query);    
        return Common::SerializeData($result);   
    }
    
	public static function CapNhatTrangThaiThatBai($parameters)
    { 
        $params=  Common::Deseriallize($parameters);
        $id_ver=$params[0];
        $masofile=$params[1];
       // $id_donvi=$params[2];
        $sql=sprintf("UPDATE version_detail 
                      SET IS_UPDATE=2 WHERE ID_VERSION=%s and MASO_FILE='%s' ",  
                      $id_ver,mysql_real_escape_string($masofile)
                    ); 
        $result=@mysql_query($sql);
        if(mysql_affected_rows()) return 1;
        else return 0;
    }
  
}
?>
