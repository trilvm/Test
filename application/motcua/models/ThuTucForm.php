<?php
/*
 * ThuTucForm
 * @copyright  2009 Unitech
 * @license    
 * @version    
 * @link       
 * @since      
 * @deprecated 
 * @author Võ Chí Trường (truongvc@unitech.vn)
 */
class ThuTucForm extends Zend_Form
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
	 * Instance of specific form...
	 *
	 * @param  $options
	 */
	public function __construct($options = null)
    {
        parent::__construct($options);       
       
        $this->setAttribs(array(
            'name'  => 'loaiHsForm', 
        	'method'=>'post',      	
        ));       
       	
        $tenThuTuc = new Zend_Form_Element_Text('TENTHUTUC');
        $tenThuTuc->setRequired(true)
        ->addFilter('StripTags')
        ->addPrefixPath('Unitech_Validate', 'Unitech/Validate/', 'validate')
        ->setOptions(array('maxlength'=>'1024','size'=>'200'))
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
					'options' => array(1, 50000)),					
				)
			) 
        ->setDecorators(array('ViewHelper'));
       
		$comboBoxLoais = new Zend_Form_Element_Select('ID_LOAIHOSO');	    
		$comboBoxLoais->addMultiOptions($this->optionLoais())	  
	    ->setOptions(array('style'=>'width:600px'))   
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
   		$checkactive = new Zend_Form_Element_CheckBox('ACTIVE');
		$checkactive->setCheckedValue('1')
		->setUncheckedValue('0')		
		->setDecorators(array('ViewHelper'));

        $this->addElement($tenThuTuc);
        $this->addElement($comboBoxLoais);
		$this->addElement($checkactive);
		
		
	}
}