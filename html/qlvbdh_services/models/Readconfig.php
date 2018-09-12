<?php

class Config
{

    public static function ReadConfig($id_donvi)
    {    
        $query=sprintf("select name,value from info_config ");
        $result=@mysql_query($query);    
        return Common::SerializeData($result);   
    }
}
?>
