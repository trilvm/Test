<?php

/**
 * @author thangtba
 * @version
 */
include_once 'taphscv/models/Taphscv_dinhkemModel.php';
include_once 'hscv/models/filedinhkemModel.php';
include_once 'auth/models/ResourceUserModel.php';
require_once 'taphscv/models/Taphscv_TaphosocongviecModel.php';

class Taphscv_dinhkemController extends Zend_Controller_Action {

    var $getParams;
    var $dinhkemModel;
    var $config;
    public function init() {
        $this->_helper->layout->disablelayout();
        $this->getParams = $this->getRequest()->getParams();

        //instance model dinhkem
        $this->dinhkemModel = new Taphscv_dinhkemModel();
        $this->config = Zend_Registry::get('config');
    }

    public function listAction() {
        
        $id = $this->getParams['idtaphoso'];
        $this->view->idtaphoso = $id;
        $this->view->data = $this->dinhkemModel->GetVanbanByIdTaphoso($id);
    }

    public function inputAction() {
       
        $id = $this->getParams['idtaphoso'];
        $idvb = $this->getParams['idvb'];
        
        $this->view->IdFrame = $this->getParams['IdFrame'];
        echo $this->getParams['IdFrame'];
        $this->view->idtaphoso = $id;
        global $db;
        if ($idvb > 0) {
            $r = $db->query("select * from hscvdt_vanban where ID_VB=$idvb")->fetch();
        $this->view->r = $r;
        }       
        
    }

    public function saveAction() {
        
        global $db;
        $iddk = $this->getParams['iddk'];
        $idvb = $this->getParams['id_vb'];
        $idtaphoso = $this->getParams['idtaphoso'];        
        $sokyhieu = $this->getParams['sokyhieu'];
        $ngaybanhanh = QLVBDHCommon::doDateViet2Standard($this->getParams['ngaybanhanh']);
        $coquanbanhanh = $this->getParams['coquanbanhanh'];
        $trichyeu = $this->getParams['trichyeu'];
        $filename = $_FILES['uploadedfile']['name'];
        $mime = $_FILES['uploadedfile']['type'];
        $maso = $filename . date("Y-m-d H:i:s");
        $maso = md5($maso); 
        //them moi
        if ($iddk == 0) {
            //insert zo dinhkem
            $db->insert('hscvdt_dinhkem_taphoso', array(
                'ID_TAPHOSO' => $idtaphoso,
                'FILENAME' => $filename,
                'FILECODE' => $maso,
                'FILEMIME' => $mime
            ));
            $taphosoMdl = new Taphscv_TaphosocongviecModel();
            $id_object = $taphosoMdl->getPrimarykeyWhenInsert("ID_DINHKEM", 'hscvdt_dinhkem_taphoso');
            
            //insert zo vanban
            $db->insert('hscvdt_vanban', array(
                'ID_TAPHOSO' => $idtaphoso,
                'SOKYHIEU' => $sokyhieu,
                'ID_OBJECT' => $id_object,
                'NGAYBANHANH' => $ngaybanhanh,
                'COQUANBANHANH' => $coquanbanhanh,
                'TRICHYEU' => $trichyeu,
                'LOAI' => 3,
                'NAM' => QLVBDHCommon::getYear()
            ));
            $this->dinhkemModel->insertFileDinhKem($oldmaso);
        }
        //cap nhat
        else {
            //capnhat zo dinhkem	
			if($filename!=""){
			$db->delete($this->dinhkemModel->_name, "ID_DINHKEM=".$iddk);
			$db->insert('hscvdt_dinhkem_taphoso', array(
                'ID_TAPHOSO' => $idtaphoso,
                'FILENAME' => $filename,
                'FILECODE' => $maso,
                'FILEMIME' => $mime
            ));
			$taphosoMdl = new Taphscv_TaphosocongviecModel();
            $id_object = $taphosoMdl->getPrimarykeyWhenInsert("ID_DINHKEM", 'hscvdt_dinhkem_taphoso');      
		
            //capnhat zo vanban
            $db->update('hscvdt_vanban', array(                
                'SOKYHIEU' => $sokyhieu, 
				 'ID_OBJECT' => $id_object,
                'NGAYBANHANH' => $ngaybanhanh,
                'COQUANBANHANH' => $coquanbanhanh,
				'TRICHYEU' => $trichyeu
            ), "ID_VB=".$idvb); 
            $this->dinhkemModel->insertFileDinhKem($oldmaso);
			}else{
				$db->update('hscvdt_vanban', array(                
                'SOKYHIEU' => $sokyhieu,				
                'NGAYBANHANH' => $ngaybanhanh,
                'COQUANBANHANH' => $coquanbanhanh,
				'TRICHYEU' => $trichyeu
            ), "ID_VB=".$idvb);

			}
        }
        echo "<script>window.parent.SwapDiv(".$idtaphoso.",'/taphscv/taphscv/vblist/idtaphoso/".$idtaphoso."',1);</script>";
        exit;
    }

    public function downloadAction() {
        $id = $this->getParams['id'];
        $data = $this->dinhkemModel->find($id)->current();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type:" . $data->FILEMIME);
        header('Content-Disposition: attachment; filename="' . $data->FILENAME . '"');
        header("Content-Description: Excel output");
        echo file_get_contents($this->config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $data->FILECODE);
        exit;
    }

    public function deleteAction() {
        $id = (int) $this->getParams['idtaphoso'];
        $this->view->idtaphoso = $id;
        //exit;
        $iddk = (int) $this->getParams['iddinhkem'];

        //xóa file trong thư mục
        $data = $this->dinhkemModel->find($iddk)->current();
        unlink($this->config->file->root_dir . DIRECTORY_SEPARATOR . "taphscv" . DIRECTORY_SEPARATOR . $data->FILECODE);

        //xóa file trong db
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->delete($this->dinhkemModel->_name, "ID_DINHKEM=" . $iddk);


        //
        echo "window.parent.loadDivFromUrl('groupcontent" . $id . "','/taphscv/dinhkem/list/idtaphoso/" . $id . "',1);";
        exit;
    }

}
