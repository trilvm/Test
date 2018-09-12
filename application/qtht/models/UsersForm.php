<?php
/**
 * UsersForm : validate,filter, populate data from controller
 *  
 * @author truongvc
 * @version 1.0
 */
class UsersForm extends Zend_Form
{
	public $listNames=array();
	/**
     * Get list menus
     * 
     * @return array
     */
	 function optionEmployees($option=null)
 	 {
 		$employees = new EmployeesModel();				
       	$dataResult = $employees->GetAllFreeEmployees($option); 
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
						$id_emp=$subArray['ID_EMP'];
						$firstname=$subArray['FIRSTNAME'];
						$lastname=$subArray['LASTNAME'];
						$arrReturn+=array($id_emp=>$firstname.' '.$lastname);
						
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
        $this ->setAttribs(array(
            'name'  => 'usersForm', 
        	'method'=>'post',));
       
			
    	$username = new Zend_Form_Element_Text('USERNAME');
        $username->setRequired(true)        
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'40','size'=>'50'))
        ->addFilter('StripTags')
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
					'validator' => 'Alnum',					
				),
				array
				(
					'validator' => 'StringLength',
					'options' => array(5, 40)
				),									
				array
				(
					'validator' => 'noExistItemOnTable',
					'options' => array('USERNAME','QTHT_USERS','ID_U')
				)									
			)
			)
	   ->setDecorators(array('ViewHelper')) ;       
       $password = new Zend_Form_Element_Password('PASSWORD');
       $password->setRequired(true) 
       ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate') 
       ->setOptions(array('maxlength'=>'40','size'=>'50'))    
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
					'validator' => 'Alnum',					
				),
				array
				(
					'validator' => 'StringLength',
					'options' => array(5, 40)),					
				)				
			 )
       ->setDecorators(array('ViewHelper'));
	   $rpassword = new Zend_Form_Element_Password('RPASSWORD');
       $rpassword->setRequired(true)
       ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate') 
       ->setOptions(array('maxlength'=>'40','size'=>'50'))   
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
					'validator' => 'Alnum',					
				),
				array
				(
					'validator' => 'StringLength',
					'options' => array(5, 40)),					
				)				
			 )
		->addValidator('PasswordConfirmation')  
		->setDecorators(array('ViewHelper'));
		   
      	$checkactive = new Zend_Form_Element_CheckBox('ACTIVE');
		$checkactive->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));
		
		$listEmps = new Zend_Form_Element_Select('ID_EMP');
        $listEmps ->addMultiOptions($this->optionEmployees($options['idCurrentUser']))
        ->setOptions(array('style'=>'width:190px'))
        ->setDecorators(array('ViewHelper')); 
        
		//Add element to form
		$this->addElement($username);
		$this->addElement($password);
		$this->addElement($rpassword);
		$this->addElement($listEmps);
		$this->addElement($checkactive);	
		
		$newButton=new Zend_Form_Element_Submit('submit');
		$newButton->setLabel('Thêm mới')
		->setOrder(5)
		->setDecorators(array('ViewHelper'));
     	
     	//$this->addElement($newButton);
     	
     	
	}	
}