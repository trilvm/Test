<?php
/*
 * LoaiForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class LoaiForm extends Zend_Form
{
	/**
     * Get list 'loai's
     * 
     * @return array
     */
	 function optionLoais()
 	 {
 		
		$loaiModel = new LoaiModel();				
       	$dataResult = $loaiModel->GetAllLoaiHscv(); 
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
						$id_loaihscv=$subArray['ID_LOAIHSCV'];
						$name=$subArray['NAME'];						
						$arrReturn+=array($id_loaihscv=>$name);
						
					}
					catch(Exception $er){ };
				}
				$counter++;
			}
		}	
		
		return $arrReturn;
	}
	public function __construct($options = null)
    {
    	
        parent::__construct($options);       
       
        $this->setAttribs(array(
            'name'  => 'loaiHsForm', 
        	'method'=>'post',      	
        ));
        $code = new Zend_Form_Element_Text('CODE');
        $code->setRequired(true)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
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
					'options' => array(1, 10)),					
				),
				array
				(
					'validator' => 'noExistItemOnTable',
					'options' => array('CODE','MOTCUA_LOAI_HOSO','ID_LOAIHOSO')
				)
				
			) 
        ->setDecorators(array('ViewHelper'));
        
		$tenloai = new Zend_Form_Element_Text('TENLOAI');
        $tenloai->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'300','size'=>'50','style'=>'width:500px'))
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
					'options' => array(1, 300)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));
       
        $soNgayXuLy = new Zend_Form_Element_Text('SONGAYXULY');
        $soNgayXuLy->setRequired(true)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
              
        $lePhi = new Zend_Form_Element_Text('LEPHI');
        $lePhi->setRequired(true)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
        $chuThich =new Zend_Form_Element_Textarea('CHUTHICH');
        $chuThich->setRequired(false)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:500px','rows'=>'5')) 
        ->setDecorators(array('ViewHelper'))
        ->addValidators(
             array
             (
				array
				(
					'validator' => 'stringLength',
					'options' => array(1, 1000)
				),					
				
			 ) 
		);  
		
		$comboBoxLoais = new Zend_Form_Element_Select('ID_LOAIHSCV');	    
		$comboBoxLoais->addMultiOptions($this->optionLoais())	  
	    ->setOptions(array('style'=>'width:300px'))   
	    ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
	    ->setOrder(0)
	    ->addValidators(
             array
             (
				array
				(
					'validator' => 'existItemOnTable',
					'options' => array('ID_LOAIHSCV','HSCV_LOAIHOSOCONGVIEC')),					
				)
			) 	
	    ->setDecorators(array('ViewHelper'));  
	    
        $this->addElement($code);
        $this->addElement($tenloai);
		$this->addElement($soNgayXuLy);
		$this->addElement($lePhi);
		$this->addElement($chuThich);
		$this->addElement($comboBoxLoais);
		
	}
}