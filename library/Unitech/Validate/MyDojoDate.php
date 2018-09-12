<?php
class Unitech_Validate_MyDoJoDate extends Zend_Validate_Abstract
{
	const NOT_VALID = 'notValid';
	/**
    * Name of Dojo Date Field 
    *
    * @var String
    */
    protected $_nameField;
    /**
     * Returns the Field Name
     *
     * @return String
     */
    public function getFieldName()
    {
        return $this->_nameField;
    }

    /**
     * Sets the Field Name option
     *
     * @param  String $fieldname     
     *  
     *
     */
    public function setFieldName($fieldname)
    {
    	if (null == $fieldname) 
    	{
            require_once 'Unitech/Validate/Exception.php';
            throw new Unitech_Validate_Exception("Truyền tên không hợp lệ");
        }
        else        
        {
            $this->_nameField = $fieldname;
        }
        return $this;
    }     

	protected $_messageTemplates = array(
		self::NOT_VALID => 'Định dạng ngày không hợp lệ'
	);
	/**
	 * Hàm khởi tạo
	 *
	 * @param string $fieldname
	 */
	public function __construct($fieldname)
    {
        $this->setFieldName($fieldname);
        
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
		$this->_setValue($value);
		$name_displayed=$this->getFieldName().'_displayed_';
	
		if (is_array($context)) 
		{
			
			if (isset($context[$this->getFieldName()]))
			{
				if(Zend_Date::isDate($context[$this->getFieldName().'_displayed_'],'dd/MM/yyyy'))
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
