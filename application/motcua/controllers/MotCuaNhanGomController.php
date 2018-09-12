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
require_once 'qtht/models/UsersModel.php';
require_once 'motcua/models/MotCuaNhanGomModel.php';
require_once 'motcua/models/ThuTucModel.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/File/Transfer/Adapter/Http.php';
require_once 'Zend/File/Transfer.php';
require_once 'Common/AjaxEngine.php';
/**
 * PhoiHopController dung de quan tri so nguoi phoi hop voi mot HSCV cu the
 * 
 * @author truongvc
 * @version 1.0
 */
class Motcua_MotCuaNhanGomController extends AjaxEngine 
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
		// TODO Auto-generated PhoiHopController::indexAction() default action
		$this->returnResultHTML($this);
	}
	/*
	 * Ham ajax tra ket qua duoi dang html ve cho client  
	 */
	public function echoHTML()
	{
		$year = QLVBDHCommon::getYear();	
		$idhoso = (int)$this->_request->getParam('idhoso', 0); 
		$id_loai_change=(int)$this->_request->getParam('idloai', 0);		
		$tenthutuc = $this->_request->getParam('TenThuTuc'); 
		$data_input=$this->_request->getParam('DEL'); 	
		$check=$this->_request->getParam('d_tt'); 	
		$thutuc_array = $this->_request->getParam('DEL_THUTUC');
		$nhangom_array = $this->_request->getParam('DEL_NHANGOM');
		$gethiddenThutuc=$this->_request->getParam('hiddenThuTuc');
		$return=$this->_request->getParam('return');
		$arrayHiddenThuTuc=split(",",$gethiddenThutuc);
		if(count($nhangom_array)>0 && $return==null ) $this->dispatch("deleteAction");
		//lay id_session		
		$id_session=session_id();
		$motcuanhangomModel = new MotCuaNhanGomModel($year);	
		$thutuc = new ThuTucModel();  
		$thutuc_them = new MotCuaNhanGomModel($year);  
		$this->view->idhoso= $idhoso;
		$this->view->year= $year;
		$this->view->id_loaihoso=$id_loai_change;	
		$this->view->d_tt=$check;
		if($idhoso>0)	 
		{
		 	$this->view->data_nhangom=$thutuc_them->fetchAll("ID_HOSO=".$idhoso,"TEN_THUTUC DESC");
		 	
		}
		$where="1=1";	
		if($id_loai_change>0)
		{
			$where.=" AND ID_LOAIHOSO=".$id_loai_change;			
			if($idhoso>0)
			{
				foreach ($this->view->data_nhangom as $item)
				{
					$where.=" AND TENTHUTUC <> '".$item->TEN_THUTUC."'";
				}
			}
			$this->view->data_thutuc=$thutuc->fetchAll($where); 
		}
		//Build your where in 
		
		if(count($thutuc_array)>0 && $check=='thutuc' && $id_loai_change>0 )
		{
			$counter=0;
			while ( $counter < count($thutuc_array )) 
			{
				if ($thutuc_array[$counter] > 0) 
				{
					$where .= ' AND ID_THUTUC <> ' . $thutuc_array[$counter];       
					$hiddenThutuc.=$thutuc_array[$counter].",";   		
				}
				$counter++;
			}
			
			$this->view->hiddenThuTuc=$hiddenThutuc.$gethiddenThutuc;
			$this->view->data_thutuc=$thutuc->fetchAll($where);						
		}
		if($hiddenThutuc=="") $this->view->hiddenThuTuc=$gethiddenThutuc;
		if(count($arrayHiddenThuTuc)>1)
		{
			for($j=0;$j<count($arrayHiddenThuTuc);$j++)
			{
				if($arrayHiddenThuTuc[$j]!="")
				$where.=' AND ID_THUTUC <> ' . $arrayHiddenThuTuc[$j];
			}
			$this->view->data_thutuc=$thutuc->fetchAll($where);		
		}			
		
		$this->view->data_input=array();		
		if($idhoso>=0 && $tenthutuc!='')		
		{
			try 
			{
				$data = array(
				            'TEN_THUTUC' 		=> $tenthutuc,
				        	'ID_SESSION' 	=> $id_session,
				        	'ID_HOSO'=>$idhoso,			        
		       				 );
			    
			    $motcuanhangomModel->insert($data);			
			}
			catch (Exception $ma)
			{}
		}
		$this->view->data_input = $motcuanhangomModel->fetchAll("ID_SESSION='".$id_session."'");
	}
	/*
	 * Ham ajax tra ket qua duoi dang html ve cho client  
	 */
	public function addAction()
	{
		$idhoso = $this->_request->getParam('idhoso');	
		$id_loaihoso = $this->_request->getParam('idloai');	
		$year = QLVBDHCommon::getYear();		
		$gethiddenThutuc=$this->_request->getParam('hiddenThuTuc');
		$d_tt=$this->_request->getParam('d_tt');
		
		$this->view->idhoso= $idhoso;
		$this->view->id_loaihoso= $id_loaihoso;
		$this->view->year= $year;
		$this->view->d_tt= $d_tt;
		$this->view->hiddenThuTuc= $gethiddenThutuc;
		
	}
	/*
	 * Ham delete ket qua  
	 */
	public function deleteAction()
	{
		$del_nhangom_array = $this->_request->getParam('DEL_NHANGOM');
		$idhoso = $this->_request->getParam('idhoso');	
		$id_loaihoso = $this->_request->getParam('idloai');	
		$gethiddenThutuc=$this->_request->getParam('hiddenThuTuc');
		$this->_request->setParam('hiddenThuTuc',$gethiddenThutuc);
		$this->_request->setParam('idhoso',$idhoso);
		$this->_request->setParam('idloai',$id_loaihoso);
		$this->_request->setParam('return',"returnFromTest");
		$year=QLVBDHCommon::getYear();		
		//Xoa ho so nhan gom
		$counter=0;
		while ( $counter < count($del_nhangom_array )) 
		{
			if ($del_nhangom_array[$counter] > 0) 
			{
				try 
				{
					$nhangomModel=new MotCuaNhanGomModel($year);
					$where = 'ID_TAILIEU_NHAN = ' . $del_nhangom_array[$counter];
                	$nhangomModel->delete($where);				
					
				}
				catch(Exception $er){};
			}
			$counter++;
		}
		$this->dispatch("indexAction");
	}
	/**
	 * Ham tao text hoac text area cho mot doi tuong text, voi chieu dai quy dinh de xuong dong
	 *
	 * @param String $stringText
	 * @param int $length
	 * @return text or textarea HTML element
	 */
	public function createWrapText($stringText,$length,$color)
	{

		$html='<textarea name="tenthutuc[]" style="width:98%;color:'.$color.'" rows="'.(ceil(strlen($stringText)/$length)+1).'">'.$stringText.'</textarea>';
		echo $html;
	}
	
}

