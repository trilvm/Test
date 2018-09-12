<?php
/*
 * HoSoMotCuaForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */require_once 'motcua_ub/models/motcua_hosoModel.php';
class motcua_hosoForm extends Zend_Form
{
	/**
     * Get list 'loai's
     * 
     * @return array
     */
	  function optionLoais($idlvmc)
 	 {
 		$loaiModel = new LoaiModel();				
       	$dataResult = $loaiModel->GetAllLoais($idlvmc); 
       	$counter=0;
		$arrReturn=array('0'=>'---Chọn loại---');			
		if($dataResult!=null)		
		{
			while ( $counter < count($dataResult)) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_menu=$subArray['ID_LOAIHOSO'];
						$name=$subArray['TENLOAI'];						
						$arrReturn+=array($id_menu=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	/**
     * Danh sach Nguoi Nhan
     * 
     * @return array
     */
	function optionNguoiNhans()
 	{
 		$nguoitaoModel=new UsersModel();
 		$dataResult =$nguoitaoModel->selectAllUsersJoinEmployees();
		$counter=0;
		$arrReturn=array();	
		$arrReturn+=array(''=>'-------Chọn người-------');
		if($dataResult!=null)		
		{
			while ( $counter < count($dataResult)) 
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
	 * Ham khoi tao
	 *
	 * @param unknown_type $options
	 */
	public function __construct($options = null)
    {
    	parent::__construct($options);       
        $this->setAttribs(array(
            'name'  => 'loaiHoSoMotCuaForm', 
        	'method'=>'post',      	
        ));
        Zend_Dojo::enableForm($this);
       $comboBoxLoais = new Zend_Form_Element_Select('ID_LOAIHOSO');	    
		$comboBoxLoais->addMultiOptions($this->optionLoais($options["ID_LV_MC"]))	  
	    ->setOptions(array('style'=>'width:98%','id'=>'id_ID_LOAIHOSO','OnChange'=>'document.frmAddMotCua.inputSubmit.value="";document.frmAddMotCua.submit();'))   
	    ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
	    ->setOrder(0)
	    ->addValidators(
             array
             (
				array
				(
					'validator' => 'existItemOnTable',
					'options' => array('ID_LOAIHOSO','MOTCUA_LOAI_HOSO')),					
				)
			) 	
	    ->setDecorators(array('ViewHelper')); 
	    
        $mahosoText = new Zend_Form_Element_Text('MAHOSO');
        /*$mahosoText->setRequired(true)
        ->setOptions(array('maxlength'=>'24','size'=>'24'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
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
				'options' => array(16, 16)),					
			)
		)  
		->addValidator('MaSoVanBanHSMC')        
        ->setDecorators(array('ViewHelper'));*/
        
        $tentochucText = new Zend_Form_Element_Text('TENTOCHUCCANHAN');
        $tentochucText->setRequired(true)
        ->setOptions(array('maxlength'=>'100','size'=>'30','style'=>'','OnChange'=>'UpdateTrichYeu();'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
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
				'options' => array(1, 100)),					
			)
		)          
        ->setDecorators(array('ViewHelper'));
        
        $emailText = new Zend_Form_Element_Text('EMAIL');
        $emailText->setRequired(false)
        ->setOptions(array('maxlength'=>'50','size'=>'20'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('EmailAddress')    
        ->setDecorators(array('ViewHelper'));
        
        $dienthoaiText = new Zend_Form_Element_Text('DIENTHOAI');
        $dienthoaiText->setRequired(false)
        ->setOptions(array('maxlength'=>'20','size'=>'20'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $diachiText = new Zend_Form_Element_Text('DIACHI');
        $diachiText->setRequired(true)
        ->setOptions(array('maxlength'=>'100','size'=>'30','id'=>'DIACHI','style'=>''))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
       
        $nhanlucText =new Zend_Form_Element_Text('NHAN_LUC');
        //$nhanlucText = new Zend_Form_Element_Text('NHAN_LUC');
        $nhanlucText->setRequired(false)
        ->setOptions(array('maxlength'=>'8','style'=>'width:50px'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $nhanlailucText =new Zend_Form_Element_Text('NHANLAI_LUC');
        //$nhanlucText = new Zend_Form_Element_Text('NHAN_LUC');
        $nhanlailucText->setRequired(false)
        ->setOptions(array('maxlength'=>'8','style'=>'width:50px'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $nhanngayText = new Zend_Dojo_Form_Element_DateTextBox('NHAN_NGAY');
        //$nhanngayText = new Zend_Form_Element_Text('NHAN_NGAY');
        $nhanngayText->setRequired(false)
        ->setOptions(array('maxlength'=>'8','size'=>'8','style'=>'width:100px','OnChange'=>'UpdateNgayNhan();'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')  
        ->setPromptMessage('Phải nhập đúng định dạng')
        ->setInvalidMessage('Nhập sai định dạng')     
        ->addValidators(
        	array
            (
				array
				(
					'validator' => 'myDojoDate',
					'options' => array('NHAN_NGAY')
				),					
				
			) 	
		)
        ->setDecorators(
		     	array(     		   
		     		'DijitElement',     	
		     		
		     	));  
        //->setDecorators(array('ViewHelper'));
        
        $lephiText = new Zend_Form_Element_Text('LEPHI');
        $lephiText->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10','disabled'=>'disabled','id'=>'id_LEPHI'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
        $soText = new Zend_Form_Element_Text('SO');
        $soText->setRequired(false)
        ->setOptions(array('maxlength'=>'5','size'=>'5','id'=>'id_SO'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')
        ->addValidator('Int')
        ->setValue(motcua_hosoModel::getNextSo())
        ->setDecorators(array('ViewHelper'));
        
        $trichyeuText =new Zend_Form_Element_Textarea('TRICHYEU');
        $trichyeuText->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:98%','rows'=>'2')) 
         ->addValidators(
         array
         (
			array
			(
				'validator' => 'stringLength',
				'options' => array(1, 500)),					
			)
		)   
        ->setDecorators(array('ViewHelper')); 
		
		$config = Zend_Registry::get('config');
        $sokyehieuText = new Zend_Form_Element_Text('SOKYHIEU_CHAR');
        $sokyehieuText->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10','id'=>'id_SOKYHIEU_CHAR'))
		->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')
        ->setValue($config->sys_info->kyhieutiepnhan)
		->setDecorators(array('ViewHelper'));
		
		
		$chuthichText =new Zend_Form_Element_Textarea('CHUTHICH');
        $chuthichText->setRequired(false)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')
        ->setOptions(array('style'=>'width:98%','rows'=>'2')) 
        ->addValidators(
         array
         (
			array
			(
				'validator' => 'stringLength',
				'options' => array(1, 1024)),					
			)
		)          
        ->setDecorators(array('ViewHelper'));   
        $idHscvHidden=new Zend_Form_Element_Hidden('ID_HSCV');
        $idHscvHidden->setRequired(false)
		->setDecorators(array('ViewHelper'));
			
		 
 		$this->addElement($comboBoxLoais);
        $this->addElement($mahosoText);
        $this->addElement($tentochucText);
		$this->addElement($emailText);
		$this->addElement($dienthoaiText);
		$this->addElement($diachiText);
		$this->addElement($nhanlucText);
		$this->addElement($nhanlailucText);
		//$this->addElement($nhanngayText);
		$this->addElement($lephiText);
		$this->addElement($soText);
		$this->addElement($trichyeuText);		
		$this->addElement($chuthichText);		
		$this->addElement($idHscvHidden);	
		$this->addElement($sokyehieuText);
	}
}