<?php
/*
 * Phoi hop controller
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */

/**
 * GopYController dung de quan tri so nguoi phoi hop voi mot HSCV cu the
 * 
 * @author truongvc
 * @version 1.0
 */
require_once 'vbden/models/vbdenModel.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'hscv/models/filedinhkemModel.php';
class Hscv_QuanLyPhieuTrinhController extends  Zend_Controller_Action 
{
    function deleteptAction() {    
        $params = $this->_request->getParams();
        $this->_helper->layout->disableLayout();
        $db = Zend_Db_Table::getDefaultAdapter();
        if ($params['delete'] == 1) {
            $sql = "DELETE FROM " . QLVBDHCommon::Table("quanlypt") . " WHERE ID=?";
            $db->query($sql, $params['idpt']);
        }
        echo "loadDivFromUrl('groupcontent".$params["idhscv"]."','/hscv/quanlyphieutrinh/list/id/".$params["idhscv"]."/year/0/type/2/iddivParent/groupcontent".$params["idhscv"]."',1);";
		exit;
        
    }
	 function listAction()
        {
            $this->_helper->layout->disableLayout();
            $params=$this->_request->getParams();
            $idhscv=$params['id'];
            global $auth;
            $user = $auth->getIdentity();
            //var_dump($user);exit;
            $db=Zend_Db_Table::getDefaultAdapter();
            $sql="select 
			pt.*,CONCAT(e.FIRSTNAME,' ',e.LASTNAME) as NGUOITAO,f.MASO,f.FILENAME
			from ".QLVBDHCommon::Table("quanlypt")." pt
			inner join qtht_users u on pt.ID_U=u.ID_U
			inner join qtht_employees e on e.ID_EMP=u.ID_EMP
			left join ".QLVBDHCommon::Table("gen_filedinhkem")." f on ID_OBJECT=pt.ID AND TYPE=1004
			where pt.ID_HSCV=?";
            $r=$db->query($sql,array($idhscv));
            $data=$r->fetchAll();
            $this->view->data=$data;
            $this->view->idhscv=$idhscv;
            $this->view->id_u= $user->ID_U;
            $this->view->id_div=$params['iddivParent'];
            
           // var_dump($data);exit;
            
        }
        public function inphieuAction() {
			global $db;
			global $auth;
			$params=$this->_request->getParams();
			$user = $auth->GetIdentity();
			if($params['idpt']==0){
				$sql="INSERT INTO ".QLVBDHCommon::Table("QUANLYPT")."(ID_HSCV,ID_U,TOMTATNOIDUNG) VALUES(?,?,?)";
				$db->query($sql,array($params["idhscv"],$user->ID_U,$params["tenphieutrinh"]));
				$idpt = $db->lastInsertId();
				filedinhkemModel::insertFile($idpt,1,0,0,1004);
			}else{
			$sql="UPDATE ".QLVBDHCommon::Table("QUANLYPT")." SET TOMTATNOIDUNG = ? WHERE ID = ? AND ID_U=?";
				$db->query($sql,array($params["tenphieutrinh"],$params['idpt'],$user->ID_U));
				filedinhkemModel::deleteFileByObject(0,$params['idpt'],1004);
				filedinhkemModel::insertFile($params['idpt'],1,0,0,1004);
			}
			echo "<script>window.parent.loadDivFromUrl('groupcontent".$params["idhscv"]."','/hscv/quanlyphieutrinh/list/id/".$params["idhscv"]."/year/0/type/2/iddivParent/groupcontent".$params["idhscv"]."',1);</script>";
			exit;
		}
    function inputAction(){
		global $db;
		global $auth;
		$user = $auth->GetIdentity();
        $this->_helper->layout->disableLayout();
        $params=$this->_request->getParams();
        $this->view->idhscv=$params['idhscv'];
        $this->view->idpt=$params['idpt'];
		if($params['idpt']>0){
			$sql = "SELECT * FROM ".QLVBDHCommon::Table("QUANLYPT")." WHERE ID = ? AND ID_U = ?";
			$this->view->data=$db->query($sql,array($params["idpt"],$user->ID_U))->fetch();
		}
    }
    function input2Action()
    {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * FROM " . QLVBDHCommon::Table("vbd_fk_vbden_hscvs") . " WHERE ID_HSCV=?";
        $r = $db->query($sql, $params['idhscv']);
        $result = $r->fetchAll();
        $idvbd = $result[0]['ID_VBDEN'];  
        //var_dump($idvbd);exit
        
        $sql = "SELECT * FROM " . QLVBDHCommon::Table("quanlypt") . " WHERE ID=?";
        $r = $db->query($sql, array($params['id']));
        $result = $r->fetchAll();
        //var_dump($result);exit;
        $this->view->ttnd = $result[0]['TOMTATNOIDUNG'];
        $this->view->ykcv = $result[0]['YKIENCHUYENVIEN']; 
        $this->view->ykldp =$result[0]['YKIENLANHDAOPHONG'];
        $this->view->ykldt =$result[0]['YKIENLANHDAOUBND'];
        $this->view->idpt=$result[0]['ID'];
        $this->view->idhscv=$params['idhscv'];
        $this->view->kg= $result[0]['KINHGOI'];
        $this->view->vd = $result[0]['VANDE'];
        $this->view->lq = $result[0]['VBKEMTHEO'];
        $this->view->id_div=$params['id_div'];
        
        
         $hscv = vbdenModel::GetAllHSCV($idvbd); 
        foreach ($hscv as $itemhscv) {
            $arr_id[] = $itemhscv['ID_HSCV'];
        }
        
        foreach ($arr_id as $idhscv) {

            $sendprocess = WFEngine::GetProcessLogByObjectId2($idhscv);
           // array_push($this->view->sendprocess, $sendprocess);
        }
        //var_dump($sendprocess);
        $this->view->sendprocess = $sendprocess;
        
        
    }
	
	
	
}

