<?php
/*
 * Phoi hop controller
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trư�?ng (truongvc@unitech.vn)
 */
require_once 'hscv/models/PhoiHopModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'hscv/models/HoSoCongViecModel.php';
require_once 'hscv/models/GopYModel.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'vbden/models/vbdenModel.php';
/**
 * PhoiHopController dung de quan tri so nguoi phoi hop voi mot HSCV cu the
 * 
 * @author truongvc
 * @version 1.0
 */
class Hscv_PhoiHopController extends Zend_Controller_Action 
{
	
	/**
	 * Ham khoi tao du lieu cho controller action
	 *
	 */
	function init()
	{
		//disable layout
		$this->_helper->layout->disableLayout();
		
	}
	/**
	 * Ham xu ly index controller , in danh sach file dinh kem 
	 */
	public function indexAction() {
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$idhscv = $param['idhscv'];
		$isreadonly = $param['isreadonly'];
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		$this->view->data = $gopy->SelectAllByIdhscv($idhscv);		
		$this->view->idhscv = $idhscv;
		$this->view->isreadonly = $isreadonly;
		$this->view->havegopy = $gopy->getIDPhoiHopByIdhscv($idhscv,$user->ID_U);
		
	}
	public function addgopyAction()
	{
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();		
		$idhscv = $param['idhscv'];
		$code = $param['code'];
		if($code=="save"){
			$gopy = new GopYModel(QLVBDHCommon::getYear());
			$idphoihop = $gopy->getIDPhoiHopByIdhscv($idhscv,$user->ID_U);
			$r = $gopy->insert(array("NOIDUNG"=>$param['NOIDUNG'],"ID_HSCV"=>$param['idhscv'],"ID_PHOIHOP"=>$idphoihop, 'ID_U' => $user->ID_U));
                        //insert file đính kèm
			$filedinhkemModel = new filedinhkemModel(QLVBDHCommon::getYear());
			$arrayIdFile=$param['idFile'];
			for($i=0;$i<count($arrayIdFile);$i++)
			{   
                            $filedinhkemModel->update(array("ID_OBJECT"=>$r,"TYPE"=>20),"MASO='".$arrayIdFile[$i]."'");				  
			}
			echo "<script>
				window.parent.document.getElementById('phoihop".$idhscv."').style.display='none';
				window.parent.loadDivFromUrl('groupcontent".$idhscv."','/hscv/phoihop/index/idhscv/".$idhscv."',1);
			</script>";exit;
		}
		$this->view->idhscv = $idhscv;
	}
	public function addphanhoiAction()
	{
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();		
		$idgopy = $param['idgopy'];
		$idhscv = $param['idhscv'];
		$code = $param['code'];
		if($code=="save"){		
			$r = $db->insert(QLVBDHCommon::Table("hscv_phoihop_phanhoi"),array("ID_GOPY"=>$idgopy,'ID_U' =>$user->ID_U,"NGAY_PH"=>date("Y-m-d h:i:s"),"NOIDUNG"=>$param['NOIDUNG']));	
			echo "<script>
				window.parent.document.getElementById('phoihop".$idhscv."').style.display='none';
				window.parent.loadDivFromUrl('groupcontent".$idhscv."','/hscv/phoihop/index/idhscv/".$idhscv."',1);
			</script>";exit;
		}
		$this->view->idgopy = $idgopy;
		$this->view->idhscv = $idhscv;		
	}
	public function detailphanhoiAction()
	{
		global $auth;
		global $db;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();		
		$idgopy = $param['idgopy'];
		$idhscv = $param['idhscv'];
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		$this->view->data = $gopy->SelectAllByIdgopy($idgopy);		
		$this->view->idgopy = $idgopy;
		$this->view->idhscv = $idhscv;
	}
	public function viewuserAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		$idhscv = $param['idhscv'];
		$error = $param['error'];
		$this->view->idhscv = $idhscv;
		$this->view->error = $error;
		$this->view->data = $gopy->SelectAllUser($idhscv);
	}
	public function adduserAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$idhscv = $param['idhscv'];
		$idu = $param['ID_U'];
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		try{
			$gopy->getDefaultAdapter()->insert(QLVBDHCommon::Table("HSCV_PHOIHOP"),array("ID_U_YC"=>$user->ID_U,"ID_U"=>$idu,"ID_HSCV"=>$idhscv,"NGAYGUI"=>date("Y-m-d H:i:s")));
			$r = $gopy->getDefaultAdapter()->query("SELECT NAME FROM ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." WHERE ID_HSCV=?",$idhscv);
			$r = $r->fetch();
			QLVBDHCommon::SendMessage(
				$user->ID_U,
				$idu,
				$r["NAME"],
				"hscv/hscv/list/code/phoihop"
			);
			$vbden = new vbdenModel(QLVBDHCommon::getYear());
			$vbden = $vbden->findByHscv($idhscv);
			$lc = new vbd_dongluanchuyenModel(QLVBDHCommon::getYear());
			$lc->send($vbden['ID_VBDEN'],array($idu),"",$user->ID_U,1,"","","",1);
		}catch(Exception $ex){
			echo $ex->__toString();exit;
			$this->_redirect("/hscv/phoihop/viewuser/idhscv/".$idhscv."/error/1");
		}
		$this->_redirect("/hscv/phoihop/viewuser/idhscv/".$idhscv);
	}
	public function deleteAction(){
		global $auth;
		$user = $auth->getIdentity();
		$param = $this->getRequest()->getParams();
		$id = $param['id'];
		$idhscv = $param['idhscv'];
		$gopy = new GopYModel(QLVBDHCommon::getYear());
		$gopy->getDefaultAdapter()->delete(QLVBDHCommon::Table("HSCV_PHOIHOP"),"ID_PHOIHOP=".$id);
		$this->_redirect("/hscv/phoihop/viewuser/idhscv/".$idhscv);
		exit;
	}
}

