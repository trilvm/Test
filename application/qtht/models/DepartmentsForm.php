<?php
/*
 * DepartmentsForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class DepartmentsForm extends Zend_Form
{
	/**
     * Get list departments
     * 
     * @return array
     */
	function optionDeps($idCurrentDep)
 	{
 		//$departments = new DepartmentsModel();				
       	//$dataResult = $departments->GetAllDeps();
       	 
       	QLVBDHCommon::GetTree(&$dataResult,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
       	$counter=0;
		$arrReturn=array();	
		$arrReturn+=array('1'=>'--Root--');
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
						if($id_dep!=$idCurrentDep)
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
     * Init form
     * 
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);    
        
        
       
        $this->setAttribs(array(
            'name'  => 'departmentsForm', 
        	'method'=>'post',      	
        ));


        $codedep = new Zend_Form_Element_Text('CODE_DEP');
        $codedep->setRequired(true)
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'2','size'=>'2'))
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
					'options' => array(1, 2)),					
				)
			)
		->addValidators(
             array
             (
				array
				(
					'validator' => 'noExistItemOnTable',
					'options' => array('CODE_DEP','QTHT_DEPARTMENTS','ID_DEP')),
				)
			)
		->setDecorators(array('ViewHelper'));





        $depname = new Zend_Form_Element_Text('NAME');
        $depname->setRequired(true)
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'100','size'=>'40'))
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
					'options' => array(1, 150)),
				)
			)
		->setDecorators(array('ViewHelper'));
        $kihieu_pb = new Zend_Form_Element_Text('KYHIEU_PB');
        $kihieu_pb->setRequired(true)
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'40','size'=>'40'))
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
					'options' => array(1, 40)),
				)
			)
		->setDecorators(array('ViewHelper'));

		$comboBox = new Zend_Form_Element_Select('ID_DEP_PARENT');
	    $comboBox->addMultiOptions($this->optionDeps($options['idCurrentDep']))
	    ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
	    ->setOrder(0)
	    ->addValidators
         	(
         	array
             (
            	array
				(
					'validator' => 'ExistItemOnTable',
					'options' => array('ID_DEP','QTHT_DEPARTMENTS')
				)
			 )
			)
	    ->setOptions(array('style'=>'width:200px'))
	    ->setDecorators(array('ViewHelper'));

		$checkboxVar = new Zend_Form_Element_CheckBox('ACTIVE');
		$checkboxVar->setCheckedValue('1')
		->setUncheckedValue('0')
		->setDecorators(array('ViewHelper'));

		$checkboxVar1 = new Zend_Form_Element_CheckBox('ISLEADER');
		$checkboxVar1->setCheckedValue('1')
		->setUncheckedValue('0')
		->setDecorators(array('ViewHelper'));

            $dep_phone = new Zend_Form_Element_Text('PHONE_DEP');
            $dep_phone->addFilter('StripTags')
            ->setOptions(array('maxlength'=>'12','size'=>'40'))
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
			)
		->setDecorators(array('ViewHelper'));     
           
		$this->addElement($codedep);
		$this->addElement($depname);
		$this->addElement($kihieu_pb);
		$this->addElement($comboBox);
		$this->addElement($checkboxVar);
		$this->addElement($checkboxVar1);
                $this->addElement($dep_phone);




    }
}