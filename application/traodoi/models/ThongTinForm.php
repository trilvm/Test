<?php
/*
 * ThongTinForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class ThongTinForm extends Zend_Form
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
            'name'  => 'thongtinForm', 
        	'method'=>'post',
        	        	       	      	
        ));
        Zend_Dojo::enableForm($this);   
        $year=QLVBDHCommon::getYear();
        
        $tieudeText = new Zend_Form_Element_Text('tieude');
        $tieudeText->setRequired(true)
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'100','size'=>'86'))
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
					'options' => array(1, 100)
				),									
			 )
		)
		->setDecorators(array('ViewHelper'));	   
        
		
		
        $nguoinhanTextarea = new Zend_Form_Element_TextArea('nguoinhan');
        $nguoinhanTextarea->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:448px;height:40px','id'=>'nguoinhan'))
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
					'options' => array(1, 3000)
				),					
			)
		)	
		->addValidator("nguoiNhan")	
		->setDecorators(array('ViewHelper'));
		
		$nguoinhan_ccTextarea = new Zend_Form_Element_TextArea('nguoinhan_cc');
        $nguoinhan_ccTextarea->setRequired(false)
        ->addFilter('StripTags')
        ->addFilter('StringTrim') 
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('style'=>'width:448px;height:40px','id'=>'nguoinhan_cc'))
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
					'options' => array(1, 1024)
				),					
			)
		)	
		->addValidator("nguoiNhan")	
		->setDecorators(array('ViewHelper'));
		
		$this->addElement($tieudeText);
		$this->addElement($nguoinhanTextarea);
		$this->addElement($nguoinhan_ccTextarea);
		    	
	}
}