<?php
class Unitech_Validate_MyDate extends Zend_Validate_Abstract
{
	const NOT_VALID = 'notValid';

	protected $_messageTemplates = array(
		self::NOT_VALID => 'Định dạng ngày không hợp lệ'
	);
	public function isValid($value, $context = null)
	{
		$value = (string) $value;
		$this->_setValue($value);
		
		if (is_array($context)) 
		{
			
			if (isset($context['BIRTHDATE']))
			{
				if(Zend_Date::isDate($context['BIRTHDATE_displayed_'],'dd/MM/yyyy'))
		        {
		        	return true;	        	
		        }  
		        else 
		        {
		        	$this->_error(self::NOT_VALID);
					return false;
		        	//$this->_setValue($context['BIRTHDATE']);
		        }
			}
		} 		
		$this->_error(self::NOT_VALID);
		return false;
	}
} 
