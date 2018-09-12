<?php
class Unitech_Validate_MaSoVanBanHSMC extends Zend_Validate_Abstract
{
	const NOT_VALID = 'notValid';
	protected $_messageTemplates = array(
		self::NOT_VALID => 'Mã số không hợp lệ'
	);	
	/**
	 * Hàm khởi tạo
	 *
	 * @param string $fieldname
	 */
	public function __construct()
    {
        
        
    }
    /**
     * Valid ngày
     *
     * @param String $value
     * @param Context $context
     * @return Boolean
     */
	public function isValid($value, $context = null)
	{
		$value = (string) $value;
		if($value!=null)
		{		
			$mstinhthanh=substr(0,2);
			$mscoquan=substr(2,3);
			$loai=substr(5,1);
			$nam=substr(6,2);
			$maloai=substr(8,2);
			$dokhan=substr(10,1);
			$somat=substr(11,1);
			$soden=substr(12,4);
			$this->_error(self::NOT_VALID);
			return true;
		}
		else 
		{
			return true;
		}
	}
} 
