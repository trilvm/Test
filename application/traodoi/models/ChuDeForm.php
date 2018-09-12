<?php
/*
 * chudeForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class ChuDeForm extends Zend_Form
{
	/**
     * Form initialization
     * 
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);     
        $this ->setAttribs(array(
            'name'  => 'chudeForm', 
        	'method'=>'post',
        	        	       	      	
        ));
      	$tenchude = new Zend_Form_Element_Text('ten_chude');
        $tenchude->setRequired(true)
        ->setDecorators(array('ViewHelper'))
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'50','size'=>'50'))
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
					'options' => array(1, 50)),					
				)
			);       
   		$trangthai = new Zend_Form_Element_CheckBox('trangthai');
		$trangthai->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));		
		
		
		
		$this->addElement($tenchude);
		$this->addElement($trangthai);     	
		
	}
}