<?php
class Unitech_Validate_ExistItem extends Zend_Validate_Abstract
{
	const EXIST = 'Exist';

	protected $_messageTemplates = array(
		self::EXIST => 'Giá trị này đã tồn tại'
	);
	public function isValid($value, $context = null)
	{
		
		$value = (string) $value;
		$this->_setValue($value);
		$users = new UsersModel();	
		$listData = $users->GetAllNames();			
		//$listData=$context['listNames'];
		$counter=0;		
		while ( $counter < count($listData)) 
		{
			if ($listData[$counter] > 0) 
			{
				$subArray=$listData[$counter];
				if($value==$subArray['USERNAME'])
				{
					$this->_error(self::EXIST);
					return false;
					
				}
			}
			$counter++;
		}	
		return true;
	}
} 
?>