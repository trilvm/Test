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
require_once 'hscv/models/GopYModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'hscv/models/HoSoCongViecModel.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
/**
 * GopYController dung de quan tri so nguoi phoi hop voi mot HSCV cu the
 * 
 * @author truongvc
 * @version 1.0
 */
class Hscv_GopYController extends AjaxEngine 
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
		// TODO Auto-generated GopYController::indexAction() default action
		$this->returnResultHTML($this);
	}
	/*
	 * Ham ajax tra ket qua duoi dang html ve cho client  
	 */
	protected function echoHTML()
	{
		$idhscv = $this->_request->getParam('idhscv');	
		$id_u = $this->_request->getParam('id_u');	
		$id_phoihop = $this->_request->getParam('id_phoihop');	
		$noidung = $this->_request->getParam('noidung');
		$id_gopy = $this->_request->getParam('id_gopy');
		$year = QLVBDHCommon::getYear();	
		//echo '['.$idhscv.']['.$id_u.']['.$id_phoihop.']';	
		global $auth;
		$user = $auth->getIdentity();
		if($id_gopy!=null)
		{
			try 
			{
				$modelgopy=new GopYModel($year);
				$this->view->gopy=$modelgopy->fetchRow("ID_GOPY=".$id_gopy);
			}
			catch(Exception $re){}
		}
		else
		if($id_u==$user->ID_U && $id_u!=null && $idhscv !=null && $id_phoihop !=null&&$noidung !=null)
		{
				
           	try 
           	{
                $data = array(
                    'id_u' => $id_u,
                    'id_hscv'  => $idhscv,                	
                    'id_phoihop'  => $id_phoihop,
                    'noidung'=>$noidung,   
                );
                $gopy = new GopYModel($year);
                $gopy->insert($data);
                
                
           	}
           	catch(Exception $e2)
           	{
           		echo $e2;
           	}
		}
		$model = new GopYModel($year);
		$this->view->data= $model->findAllGopYs($idhscv,$id_u,$id_phoihop);
		$this->view->idhscv= $idhscv;
		$this->view->id_u= $id_u;
		$this->view->currentUser=$user->ID_U;
		$this->view->id_phoihop= $id_phoihop;
		$modelusers=new UsersModel();
		$this->view->users=$modelusers->fetchRow("ID_U=".$id_u);
	}
	/*
	 * Ham ajax tra ket qua duoi dang html ve cho client  
	 */
	protected function addAction()
	{
		global $auth;
		$user = $auth->getIdentity();
		$idhscv = $this->_request->getParam('idhscv');
		$id_u = $this->_request->getParam('id_u');		
		$this->view->idhscv= $idhscv;
		$year = QLVBDHCommon::getYear();
		$this->view->id_u= $id_u;
		$model = new GopYModel($year);
		$this->view->currentUser=$user->ID_U;
		try 
		{
			$depsResult=array();
			QLVBDHCommon::GetTree(&$depsResult,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
			$this->view->listdeps=$depsResult;
			$this->view->listExistUsers = $model->getRemainUsers($idhscv);
		}
		catch(Exception $mr){echo $mr;}	
		
	}
	/*
	 * Ham edit ket qua  
	 */
	protected function editAction()
	{
		$id_gopy = $this->_request->getParam('id_gopy');
		$idhscv = $this->_request->getParam('idhscv');	
		$id_u = $this->_request->getParam('id_u');	
		$id_phoihop = $this->_request->getParam('id_phoihop');	
		$noidung = $this->_request->getParam('noidung');
		$year = QLVBDHCommon::getYear();	
		global $auth;
		$user = $auth->getIdentity();
        if ($id_gopy !== false && $id_u==$user->ID_U) 
        {
        	try 
        	{
                if ($noidung != '') 
                {
                    $data = array(
	                    'noidung' => $noidung,	                   	                	
	                );
	                
               	 	$where = 'ID_GOPY = ' . $id_gopy;	
               	 	$model = new GopYModel($year);                           
                    $model->update($data, $where);	                    	
                    $this->_redirect('/hscv/gopy/index/idhscv'.$idhscv.'/id_u/'.$id_u.'/id_phoihop'.$id_phoihop.'/year/'.$year);
                  
                } 
                else 
                {
                    
                }
            }
        	catch(Exception $e1){echo $e1;};
        }
        $this->_redirect('/hscv/gopy/index/idhscv'.$idhscv.'/id_u/'.$id_u.'/id_phoihop'.$id_phoihop);
	}
	
}

