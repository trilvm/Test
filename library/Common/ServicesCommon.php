<?php

class ServicesCommon {

    public static function Deseriallize($string) {
        $r = array();
        $r = explode("~", $string);
        for ($i = 0; $i < count($r); $i++) {
            $r[$i] = str_replace("&split;", "~", $r[$i]);
            $r[$i] = str_replace("&amp;", "&", $r[$i]);
        }
        return $r;
    }

    public static function SerializeString($s) {
        $s = str_replace("&", "&amp;", $s);
        $s = str_replace("~", "&split;", $s);
        return $s;
    }

    public static function SerializeData($object) {
        $data = "";
        $fields = mysql_num_fields($object);
        $data .= $fields;
        for ($i = 0; $i < $fields; $i++) {
            $fieldname = mysql_field_name($object, $i);
            $data .= "~" . $fieldname;
        }
        while ($row = mysql_fetch_assoc($object)) {
            for ($i = 0; $i < $fields; $i++) {
                $fieldname = mysql_field_name($object, $i);
                $data .= "~" . Common::SerializeString($row[$fieldname]);
            }
        }
        return $data;
    }

    public static function SerializeDataAndUpdateIsGet($object, $table, $idfield) {
        $data = "";
        $fields = mysql_num_fields($object);
        $data .= $fields;
        for ($i = 0; $i < $fields; $i++) {
            $fieldname = mysql_field_name($object, $i);
            $data .= "~" . $fieldname;
        }
        while ($row = mysql_fetch_assoc($object)) {
            for ($i = 0; $i < $fields; $i++) {
                $fieldname = mysql_field_name($object, $i);
                $data .= "~" . Common::SerializeString($row[$fieldname]);
            }
            query("UPDATE $table SET ISGET=1 WHERE $idfield = " . $row[$idfield]);
        }
        return $data;
    }

    public static function isAdministrators($username, $password) {
        $sid = "0";
        $sql = sprintf("SELECT * FROM qtht_users  u
		inner join fk_users_groups fk on u.ID_U = fk.ID_U
		inner join qtht_groups g on fk.ID_G = g.ID_G
		where USERNAME = '%s' and PASSWORD = '%s' and g.CODE = 'NQT'  ",
                        mysql_real_escape_string($username), md5(mysql_real_escape_string($password)));

        $user = query($sql);
        if (mysql_num_rows($user) == 1) {
            return true;
        }
        return false;
    }

    public static function SerializeDataXML($object) {
        $data = "<DATA>";
        $fields = mysql_num_fields($object);
        while ($row = mysql_fetch_assoc($object)) {
            $data.="<ITEM>";
            for ($i = 0; $i < $fields; $i++) {
                //$fieldname  = mysql_field_name($object, $i);
                //$data .= "~".Common::SerializeString($row[$fieldname]);
                $fieldname = mysql_field_name($object, $i);
                $data.="<" . $fieldname . ">";
                $data.=base64_encode($row[$fieldname]);
                $data.="</" . $fieldname . ">";
            }
            $data.="</ITEM>";
        }
        $data .= "</DATA>";
        return $data;
    }

    public static function SerializeDataForMysqlAssoc($data) {

        $string = '' . count($data);
        foreach ($data[0] as $key => $value) {
            $string .= '~' . $key;
        }
        foreach ($data as $key => $value) {
            foreach ($value as $key => $value) {
                $string .= '~' . $value;
            }
        }
        return $string;
    }

    static function DeseriallizeToArray($string) {
        $r = array();
        $r = explode("~", $string);
        for ($i = 0; $i < count($r); $i++) {
            $r[$i] = str_replace("&split;", "~", $r[$i]);
            $r[$i] = str_replace("&amp;", "&", $r[$i]);
        }
        $array_result = array();
        $colnum = $r[0]; //so cot
        $incol = 1;
        $countr = count($r);

        $num_object = (int) ((int) $countr / (int) $colnum);

        for ($i = 1; $i < $num_object; $i++) {
            $temp = array();
            for ($j = 0; $j < $colnum; $j++) {
                $temp["" . $r[$j + 1] . ""] = $r[$i * $colnum + $j + 1];
            }
            $array_result[$i - 1] = $temp;
        }
        return $array_result;
    }
}