<?php
class EmployeesForm extends Zend_Form
{
	function optionDeps()
 	{
 		//$departments = new DepartmentsModel();				
       	//$dataResult = $departments->GetAllDeps();
       	 
       	QLVBDHCommon::GetTree(&$dataResult,"QTHT_DEPARTMENTS","ID_DEP","ACTIVE=1 AND ID_DEP_PARENT",1,1);
       	$counter=0;
		$arrReturn=array();	
		$arrReturn+=array(''=>'--Root--');
		if($dataResult!=null)		
		{
			while ( $counter < count($dataResult)) 
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
    public function __construct($options = null)
    {
        parent::__construct($options);       
       
        $this->setAttribs(array(
            'name'  => 'employeesForm', 
        	'method'=>'post',      	
        ));
		$firstname = new Zend_Form_Element_Text('FIRSTNAME');
        $firstname->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
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
					'options' => array(1, 20)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));
       
              
        $lastname = new Zend_Form_Element_Text('LASTNAME');
        $lastname->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
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
					'options' => array(1,30)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));  ;
      
                 
        $birthdate = new Zend_Dojo_Form_Element_DateTextBox('BIRTHDATE');
        $birthdate->setInvalidMessage('Nhập ngày sai định dạng ngày/Tháng/năm')
        ->setPromptMessage('Phải nhập ngày theo định dạng ngày/Tháng/năm') 
        ->setRequired(false)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setDatePattern('d/M/yyyy')
        ->addFilter('StringTrim')
        ->addFilter('StripTags')
        ->addValidator('MyDate')        
        ->setDecorators(
		     	array(     		   
		     		'DijitElement',     	
		     		'ContentPane',
		     	));;
          
          
       
		$comboBox = new Zend_Form_Element_Select('ID_DEP');	    
	    $comboBox->addMultiOptions($this->optionDeps())
	     ->setValue('blue')
	     ->setOrder(0)	 
	     ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
	     ->setOptions(array('style'=>'width:190px'))         
	     ->setDecorators(array('ViewHelper'));  ;
		$this->addElement($firstname);
		$this->addElement($lastname);
		$this->addElement($birthdate);
		$this->addElement($comboBox);
		
		$newButton=new Zend_Form_Element_Submit('submit');
		$newButton->setLabel('Thêm mới')
		->setOrder(4)
		;
     	$this->addElement($newButton);
     	
		
             
    }
}