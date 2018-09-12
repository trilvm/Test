<?php

require_once 'Common/ConvertFileUtils.php';
require_once 'hscv/models/FileTransfer.php';
require_once 'Common/Convert.php';

class Vanban {

    var $_id_dk;
    var $_folder;
    var $_id_object;
    var $_maso;
    var $_nam;
    var $_thang;
    var $_mime;
    var $_filename;
    var $_type;
    var $_content;
    var $_user;
    var $_id_identify;
    var $_time_update;
    var $_pathFile;

}

class Taphscv_dinhkemModel extends Zend_Db_Table {

    public  $_name = 'hscvdt_dinhkem_taphoso';
    public $_id_p = 0;

    static function insertFileDinhKem($oldmaso) {
        //Lay cac bien toan cuc
        $con = Zend_Registry::get('config');
        $au = Zend_Registry::get('auth');
        $year = QLVBDHCommon::getYear();
        $model = new filedinhkemModel($year);
        $max_size = $con->file->maxsize;
        $temp_path = $model->getTempPath();
        $filepath = $temp_path . DIRECTORY_SEPARATOR . $_FILES['uploadedfile']['name'];
        
        if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $filepath)) {
            return array("", "", "");
        } else {
            if ($oldmaso != "" && $_FILES['uploadedfile']['name'] != "") {
                unlink($con->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $oldmaso);
            }
            $filename = $_FILES['uploadedfile']['name'];
            $mime = $_FILES['uploadedfile']['type'];
            $maso = $filename . date("Y-m-d H:i:s");
            $maso = md5($maso);
            $newlocation = $con->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $maso;
            rename($filepath, $newlocation);
            return array($maso, $mime, $filename);
        }
    }

    public function GetDinhKemById($id) {
        $sql = "SELECT * FROM $this->_name WHERE ID_DINHKEM=" . $id;
        $result = $this->getDefaultAdapter()->query($sql);
        return $result->fetch();
    }

    public function GetVanbanByIdTaphoso($id) {
        $sql = "SELECT * FROM $this->_name WHERE ID_TAPHOSO=" . $id;
        $result = $this->getDefaultAdapter()->query($sql);
        return $result->fetchAll();
    }

    function getTempPath() {
        $con = Zend_Registry::get('config');
        $root = $con->file->root_dir;
        $temp_path = $con->file->temp_path;
        if (!file_exists($root))
            mkdir($root);
        if (!file_exists($temp_path))
            mkdir($temp_path);
        return $temp_path;
    }

}
