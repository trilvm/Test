<?php
class TinhThanhForm extends Zend_Form
{
	public function __construct($options = null)
    {
        parent::__construct($options);       
       
        $this->setAttribs(array(
            'name'  => 'employeesForm', 
        	'method'=>'post',      	
        ));
		$code = new Zend_Form_Element_Text('CODE');
        $code->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'2','size'=>'2'))
        ->addFilter('StringTrim')
        ->addValidator("Int")
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
					'options' => array(1, 2)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));
       
              
        $tentinhthanh = new Zend_Form_Element_Text('TEN_TINHTHANH');
        $tentinhthanh->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'70','size'=>'50'))
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
					'options' => array(1,70)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));  ;            
       
        $islocal = new Zend_Form_Element_CheckBox('ISLOCAL');
		$islocal->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));
		
		$this->addElement($code);
		$this->addElement($tentinhthanh);
		$this->addElement($islocal);
    }
}