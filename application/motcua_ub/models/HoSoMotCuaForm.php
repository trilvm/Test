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
 */
class HoSoMotCuaForm extends Zend_Form
{
	/**
     * Get list 'loai's
     * 
     * @return array
     */
	 function optionLoais()
 	 {
 		$loaiModel = new LoaiModel();				
       	$dataResult = $loaiModel->GetAllLoais(); 
       	$counter=0;
		$arrReturn=array();			
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
        
        $comboBoxLoais = new Zend_Form_Element_Select('ID_LOAIHOSO');	    
		$comboBoxLoais->addMultiOptions($this->optionLoais())	  
	    ->setOptions(array('style'=>'width:190px'))   
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
	    
        /*$mahosoText = new Zend_Form_Element_Text('MAHOSO');
        $mahosoText->setRequired(true)
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
				'options' => array(1, 24)),					
			)
		)          
        ->setDecorators(array('ViewHelper'));
        */
        $tentochucText = new Zend_Form_Element_Text('TENTOCHUCCANHAN');
        $tentochucText->setRequired(true)
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
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
        $diachiText->setRequired(false)
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $nhanlucText = new Zend_Form_Element_Text('NHAN_LUC');
        $nhanlucText->setRequired(false)
        ->setOptions(array('maxlength'=>'8','size'=>'8'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $nhanngayText = new Zend_Form_Element_Text('NHAN_NGAY');
        $nhanngayText->setRequired(false)
        ->setOptions(array('maxlength'=>'8','size'=>'8'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->setDecorators(array('ViewHelper'));
        
        $lephiText = new Zend_Form_Element_Text('LEPHI');
        $lephiText->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
        $trichyeuText =new Zend_Form_Element_Textarea('TRICHYEU');
        $trichyeuText->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:360px','rows'=>'5')) 
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

        $chuthichText =new Zend_Form_Element_Textarea('CHUTHICH');
        $chuthichText->setRequired(false)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')
        ->setOptions(array('style'=>'width:360px','rows'=>'5')) 
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
		$this->addElement($nhanngayText);
		$this->addElement($lephiText);
		$this->addElement($trichyeuText);		
		$this->addElement($chuthichText);		
		$this->addElement($idHscvHidden);	
	}
}