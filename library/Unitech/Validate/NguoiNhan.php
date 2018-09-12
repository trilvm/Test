<?php
class Unitech_Validate_NguoiNhan extends Zend_Validate_Abstract
{
	const NOT_VALID = 'notValid';
	const NOT_VALIDS=  'notValids';
	const ERR_DB	=  'errorDb';
	 /**
     * contain error message
     *
     * @var string
     */
    protected $_errorUsers;
    /**
     * set error message
     *
     */
    public function setErrorUsers($value)
    {
    	$this->_errorUsers=$value;
    }
    /**
     * get error message
     *
     */
    public function getErrorUsers()
    {
    	return $this->_errorUsers;
    }
	protected $_messageTemplates = array(
		self::NOT_VALID => 'Địa chỉ gửi đến không hợp lệ.',
		self::NOT_VALIDS => 'Địa chỉ "%error%" không hợp lệ.',
		self::ERR_DB=>'Lỗi kết nối cơ sở dữ liệu'
	);
	protected $_messageVariables = array(
        'error' => '_errorUsers',
        
    );
	public function isValid($value, $context = null)
	{
		global $db;
		$value = (string) $value;
		$errorUsers="";
		$arrayUsers = split(";",$value);
		$listUsers="";		
		if(count($arrayUsers)>0)
		{
			for($i=0;$i<count($arrayUsers);$i++)
			{
				if(strlen($arrayUsers[$i])>0)
				{
					$listUsers.=trim($arrayUsers[$i]).";";
					try 
					{
						$r = $db->query("
							select
								ID_U
							from				
								QTHT_USERS
							where
								USERNAME=?",array(trim($arrayUsers[$i])));
						$listdata = $r->fetchall();					
						if(count($listdata)==0)
						{
							$errorUsers.=trim($arrayUsers[$i]).",";
						}
					}
					catch(exception $e2)
					{
						$this->_error(self::ERR_DB);
						return false;						
					}		
				}			
			}
		}
		if($errorUsers!="")
		{
			$this->setErrorUsers($errorUsers);
			$this->_error(self::NOT_VALIDS);
			return false;
		}
		else return true;		
	}
} 
