<?php
/*
 * BanHanhForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class BanHanhForm extends Zend_Form
{
	/**
     * Danh sach loai van ban
     * 
     * @return array
     */
	function optionLoaiVbs()
 	{
		$loaimodel = new LoaiVanBanModel();				
		$dataResult = $loaimodel->fetchAll()->toArray();
		$counter=0;
		$arrReturn=array();	
		$arrReturn+=array('0'=>'--------------Chọn loại--------------');
		$CountForNumberOfArray=count($dataResult);
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_LVB'];
						$name=$subArray['NAME'];
						$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		return $arrReturn;
	}
	/**
     * Danh sach co quan
     * 
     * @return array
     */
	function optionCoQuans()
 	{
		QLVBDHCommon::GetTree(&$dataResult,"VB_COQUAN","ID_CQ","ID_CQ_PARENT",1,1);			
		$counter=0;
		$arrReturn=array();	
		//$arrReturn+=array('0'=>'--------------Chọn cơ quan--------------');
		$CountForNumberOfArray=count($dataResult);
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_CQ'];
						$name= str_repeat("--",$subArray["LEVEL"]).($subArray["LEVEL"]>1?"-> ":"").$subArray["NAME"];
						if($subArray["ISSYSTEMCQ"] ==1)$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach linh vuc van ban
     * 
     * @return array
     */
	function optionLinhVucVbs()
 	{
		$linhvucvbmodel = new LinhVucVanBanModel();				
		$dataResult = $linhvucvbmodel->fetchAll()->toArray();
       	$counter=0;
       	$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('0'=>'--------------Chọn Lĩnh vực--------------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_LVVB'];
						$name=$subArray['NAME'];
						$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach so van ban
     * 
     * @return array
     */
	function optionSoVbs()
 	{
		$sovbmodel = new SoVanBanModel();				
		$dataResult = $sovbmodel->fetchAll("TYPE=2")->toArray();
       	$counter=0;
       	$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('0'=>'--------------Chọn Sổ văn bản--------------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_SVB'];
						$name=$subArray['NAME'];
						$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach Nguoi tao
     * 
     * @return array
     */
	function optionNguoiTaos()
 	{
 		$nguoitaoModel=new UsersModel();
 		$dataResult =$nguoitaoModel->selectAllUsersJoinEmployees();
		$counter=0;
		$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('0'=>'-------Chọn người-------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_U'];
						$name=$subArray['NAME'].'('.$subArray['USERNAME'].')';
						$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Get list departments
     * 
     * @return array
     */
	function optionDeps()
 	{
 		QLVBDHCommon::GetTree(&$dataResult,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
       	$counter=0;
       	$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('1'=>'------------------Root-----------------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_dep=$subArray['ID_DEP'];
						$name= str_repeat("--",$subArray["LEVEL"]).($subArray["LEVEL"]>1?"-> ":"").$subArray["NAME"];
						$arrReturn+=array($id_dep=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		return $arrReturn;
	}
	/**
     * Danh sach Nguoi ky
     * 
     * @return array
     */
	function optionNguoiKys()
 	{
		$nguoikyModel=new UsersModel();
 		$dataResult =$nguoikyModel->selectAllUsersJoinEmployees();
       	$counter=0;
       	$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('0'=>'-------Chọn người-------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_U'];
						$name=$subArray['NAME'].'('.$subArray['USERNAME'].')';
						$arrReturn+=array($id_act=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach Co Quan Ban Hanh
     * 
     * @return array
     */
	function optionCoQuanBanHanhs()
 	{
		QLVBDHCommon::GetTree(&$dataResult,"VB_COQUAN","ID_CQ","ID_CQ_PARENT",1,1);	
       	$counter=0;
       	$CountForNumberOfArray=count($dataResult);
		$arrReturn=array();	
		$arrReturn+=array('0'=>'----------Cơ quan ban hành----------');
		if($dataResult!=null)		
		{
			while ( $counter < $CountForNumberOfArray) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_CQ'];
						$name=$subArray['NAME'];
						$arrReturn+=array($name=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach do khan
     * 
     * @return array
     */
	function optionDoKhans()
 	{
		$arrReturn=array();	
		$arrReturn+=array('0'=>'--Chọn cấp--');
		$arrReturn+=array('1'=>'Bình thường');
		$arrReturn+=array('2'=>'Khẩn');
		$arrReturn+=array('3'=>'Tối khẩn');
		return $arrReturn;
	}
		/**
     * Danh sach do mat
     * 
     * @return array
     */
	function optionDoMats()
 	{
		$arrReturn=array();	
		$arrReturn+=array('0'=>'--Chọn cấp--');
		$arrReturn+=array('1'=>'Bình thường');
		$arrReturn+=array('2'=>'Mật');
		$arrReturn+=array('3'=>'Tuyệt mật');
		return $arrReturn;
		
	}
	/**
     * Form initialization
     * 
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);     
        $this ->setAttribs(array(
            'name'  => 'banHanhForm', 
        	'method'=>'post',
        	        	       	      	
        ));
        $year=QLVBDHCommon::getYear();
        $trichyeu = new Zend_Form_Element_Textarea('TRICHYEU');
        $trichyeu->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array("cols"=>"150","rows"=>"3"))
        ->addFilter('StringTrim')
        ->addValidators(
             array
             (
				array
				(
					'validator' => 'NotEmpty',
					'breakChainOnFailure' => true,					
				),				
				array
				(
					'validator' => 'stringLength',
					'options' => array(1, 250)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));
        
        $loaiVbList = new Zend_Form_Element_Select('ID_LVB');
        $loaiVbList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionLoaiVbs())
        ->setOptions(array('style'=>'width:190px','id'=>'id_ID_LVB'))
        ->setOptions(array('OnChange'=>'getSodi(this);return checkData(document.getElementById("id_SOKYHIEU").value);'))
        ->setDecorators(array('ViewHelper')); 
        
        $coQuanList = new Zend_Form_Element_Select('ID_CQ');
        $coQuanList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionCoQuans())
        ->setOptions(array('style'=>'width:190px','id'=>'id_ID_CQ'))
        ->setOptions(array('OnChange'=>'getSodi();return checkData(document.getElementById("id_SOKYHIEU").value);'))
        ->setDecorators(array('ViewHelper')); 
        
        $linhVucVbList = new Zend_Form_Element_Select('ID_LVVB');
        $linhVucVbList->setRequired(false)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionLinhVucVbs())
        ->setOptions(array('style'=>'width:190px'))
        ->setDecorators(array('ViewHelper')); 
        
        $soVbList = new Zend_Form_Element_Select('ID_SVB');
        $soVbList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionSoVbs())
        ->setOptions(array('style'=>'width:190px','id'=>'id_ID_SVB'))
		 ->setOptions(array('OnChange'=>'getSodi(this);'))
        ->setDecorators(array('ViewHelper')); 
        
        $nguoiTaoList = new Zend_Form_Element_Select('NGUOITAO');
        $nguoiTaoList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionNguoiTaos())
        ->setOptions(array('style'=>'width:170px','name'=>'NGUOITAO','id'=>'NGUOITAO'))
        ->setDecorators(array('ViewHelper')); 
        
        $nguoiKyList = new Zend_Form_Element_Select('NGUOIKY');
        $nguoiKyList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionNguoiKys())
        ->setOptions(array('style'=>'width:170px','name'=>'NGUOIKY','id'=>'NGUOIKY'))
        ->setDecorators(array('ViewHelper')); 
        
        $coQuanBanHanhList = new Zend_Form_Element_Select('COQUANBANHANH_TEXT');
        $coQuanBanHanhList->setRequired(true)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addMultiOptions($this->optionCoQuanBanHanhs())
        ->setOptions(array('style'=>'width:190px'))
        ->setOptions(array('OnChange'=>'getSodi();'))
		->setDecorators(array('ViewHelper')); 
        
        $soKyHieuText = new Zend_Form_Element_Text('SOKYHIEU');
        $soKyHieuText->setRequired(true)        
        ->addFilter('StringTrim')    
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:150px','id'=>'id_SOKYHIEU', 'onChange'=>'return checkData(this.value);'))
        ->setOptions(array('maxlength'=>'100','size'=>'100'))
        ->setDecorators(array('ViewHelper'));
        
        $soBanText = new Zend_Form_Element_Text('SOBAN');
        $soBanText->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
        $soToText = new Zend_Form_Element_Text('SOTO');
        $soToText->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
        $soDi = new Zend_Form_Element_Text('SODI');
        $soDi->setRequired(true)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')
		 ->addValidators(
             array
             (
				array
				(
					'validator' => 'noExistItemOnTableVBDi',
					'options' => array('SODI','VBDI_VANBANDI_'.$year,'ID_VBDI')),					
				)
			)
        ->setDecorators(array('ViewHelper'))
		->setOptions(array('id'=>'id_SODI','OnChange'=>' return false;'));
        
        $doMatList = new Zend_Form_Element_Select('DOMAT');
        $doMatList ->addMultiOptions($this->optionDoMats())
        ->setOptions(array('style'=>'width:100px'))
        ->setDecorators(array('ViewHelper')); 
        
        $doKhanList = new Zend_Form_Element_Select('DOKHAN');
        $doKhanList ->addMultiOptions($this->optionDoKhans())
        ->setOptions(array('style'=>'width:100px'))
        ->setDecorators(array('ViewHelper')); 
              
        $noiDenText = new Zend_Form_Element_Text('NOIDEN');
        $noiDenText->setRequired(false)        
        ->addFilter('StringTrim')    
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
        ->setDecorators(array('ViewHelper'));
        
      	$ngayBanHanhDate = new Zend_Dojo_Form_Element_DateTextBox('NGAYBANHANH');
        $ngayBanHanhDate->setRequired(false)
        ->setOptions(array('maxlength'=>'8','size'=>'8','style'=>'width:100px'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')  
        ->setPromptMessage('Phải nhập đúng định dạng')
        ->setInvalidMessage('Nhập sai định dạng')          
        ->setDecorators(
		     	array(     		   
		     		'DijitElement',     	
		     		
		     	));  
		
		$this->addElement($trichyeu);
		$this->addElement($loaiVbList);
		$this->addElement($coQuanList);
		$this->addElement($linhVucVbList);
		$this->addElement($soVbList);
		$this->addElement($nguoiTaoList);		
		$this->addElement($nguoiKyList);
		//$this->addElement($coQuanBanHanhList);
		$this->addElement($soKyHieuText);
		$this->addElement($soBanText);
		$this->addElement($soToText);
		$this->addElement($doMatList);
		$this->addElement($doKhanList);
		$this->addElement($noiDenText);
		$this->addElement($soDi);
		$this->addElement($ngayBanHanhDate);
		
     	
	}
}