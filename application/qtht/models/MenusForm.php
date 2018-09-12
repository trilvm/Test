<?php
/*
 * MenusForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class MenusForm extends Zend_Form
{
	/**
     * Get list menus
     * 
     * @return array
     */
	 function optionMenus($idCurrentMenu)
 	 {
 		$menus = new MenusModel();				
       	$dataResult = $menus->GetAllMenus(); 
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
						$id_menu=$subArray['ID_MNU'];
						$name=$subArray['NAME'];
						if($id_menu!=$idCurrentMenu)
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
     * Get list actions
     * 
     * @return array
     */
	function optionActions()
 	{
		$menus = new MenusModel();				
       	$dataResult = $menus->GetAllActions(); 
       	$counter=0;
		$arrReturn=array();	
		$arrReturn+=array(''=>'--Chọn Action--');
		if($dataResult!=null)		
		{
			while ( $counter < count($dataResult)) 
			{
				if ($dataResult[$counter] > 0) 
				{
					$subArray=$dataResult[$counter];				
					try 
					{
						$id_act=$subArray['ID_ACT'];
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
     * Form initialization
     * 
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);     
        $this ->setAttribs(array(
            'name'  => 'menusForm', 
        	'method'=>'post',
        	        	       	      	
        ));
      	$menuName = new Zend_Form_Element_Text('NAME');
        $menuName->setRequired(true)
        ->setDecorators(array('ViewHelper'))
        ->addFilter('StripTags')
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
			);     
	           
        $menuScript = new Zend_Form_Element_Text('SCRIPT');
        $menuScript->setRequired(false)        
        ->addFilter('StringTrim')    
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
        ->setDecorators(array('ViewHelper'));
        
        $menuIcon = new Zend_Form_Element_Text('ICON');
        $menuIcon->setRequired(false)
        ->addFilter('StringTrim')  
        ->setDecorators(array('ViewHelper'));  
        
   
        $menuUrl = new Zend_Form_Element_Text('URL');
        $menuUrl->setRequired(false)
        ->addFilter('StripTags')
        ->setOptions(array('maxlength'=>'100','size'=>'50'))
        ->addFilter('StringTrim')       
        ->setDecorators(array('ViewHelper'));
      
        $menuAction = new Zend_Form_Element_Select('ACTID');
        $menuAction ->addMultiOptions($this->optionActions())
        ->setOptions(array('style'=>'width:190px')) 
        ->addValidators
         	(
         	array
             (
            	array
				(
					'validator' => 'ExistItemOnTable',
					'options' => array('ACTID','QTHT_ACTIONS')
				)
			 )
			)        
        ->setDecorators(array('ViewHelper'));          
       
		$comboBox = new Zend_Form_Element_Select('ID_MNU_PARENT');	    
	    $comboBox->addMultiOptions($this->optionMenus($options['idCurrentMenu']))	  
	    ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
	    ->setOptions(array('style'=>'width:190px'))   
	    ->setOrder(0)	
	    ->addValidators
         	(
         	array
             (
            	array
				(
					'validator' => 'ExistItemOnTable',
					'options' => array('ID_MNU','QTHT_MENUS')
				)
			 )
			) 
	    ->setDecorators(array('ViewHelper'));
	    
     	$checkIsLast = new Zend_Form_Element_CheckBox('ISLASTMENU');
		$checkIsLast->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));
		
		
		$checkPop = new Zend_Form_Element_CheckBox('POPUP');
		$checkPop->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));
		
		$widthPop = new Zend_Form_Element_Text('WIDTH');
        $widthPop->setRequired(false)
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->addFilter('StringTrim')    
        ->addValidator('Int')    
        ->setDecorators(array('ViewHelper'));
        
		$heightPop = new Zend_Form_Element_Text('HEIGHT');
        $heightPop->setRequired(false)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addValidator('Int')  
        ->addFilter('StringTrim')        
        ->setDecorators(array('ViewHelper'));  
            
		$topPop = new Zend_Form_Element_Text('TOP');
        $topPop->setRequired(false)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addValidator('Int')  
        ->addFilter('StringTrim')        
        ->setDecorators(array('ViewHelper'));
        
		$xleftPop = new Zend_Form_Element_Text('XLEFT');
        $xleftPop->setRequired(false)
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'10','size'=>'10'))
        ->addValidator('Int')  
        ->addFilter('StringTrim')        
        ->setDecorators(array('ViewHelper'));
        
		$isCenterPop = new Zend_Form_Element_CheckBox('ISCENTER');
		$isCenterPop->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));
		
		$this->addElement($menuName);
		$this->addElement($menuScript);
		$this->addElement($menuIcon);
		$this->addElement($menuUrl);
		//$this->addElement($menuAction);		
		$this->addElement($comboBox);
		$this->addElement($checkIsLast);
		$this->addElement($checkPop);
		$this->addElement($widthPop);
		$this->addElement($heightPop);
		$this->addElement($topPop);
		$this->addElement($xleftPop);
		$this->addElement($isCenterPop);
		$id=new Zend_Form_Element_Hidden('ID_MNU');
		$this->addElement($id);
     	
	}
}