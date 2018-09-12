<?php
class Unitech_Validate_ExistItemOnTable extends Zend_Validate_Abstract
{
	const NOTEXIST = 'NotExist';
	const DATABASEERROR = 'DBError';
	/**
    * Name of Table
    *
    * @var String
    */
    protected $_tableName;
    
    /**
    * Name of Column
    *
    * @var String
    */
    protected $_coLumnName;
    
    /**
    * array discription of table data
    *
    * @var String
    */
    protected $_arrayTable;
    
    /**
     * Returns the tableName option
     *
     * @return String
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * Sets the tableName option
     *
     * @param  String $name     
     *  
     *
     */
    public function setTableName($name)
    {
    	if (null == $name) 
    	{
            require_once 'Unitech/Validate/Exception.php';
            throw new Unitech_Validate_Exception("Truyền tên không hợp lệ");
        }
        else        
        {
            $this->_tableName = $name;
        }
        return $this;
    }     
    /**
    * Returns the columnName option
    *
    * @return String
    */
    public function getColumnName()
    {
        return $this->_coLumnName;
    }

    /**
     * Sets the columnName option
     *
     * @param  String $name     
     *  
     *
     */
    public function setColumnName($name)
    {
    	if (null == $name) 
    	{
            require_once 'Unitech/Validate/Exception.php';
            throw new Unitech_Validate_Exception("Truyền tên không hợp lệ");
        }
        else 
        {
            $this->_coLumnName = $name;
        }
        return $this;
    }      
     /**
     * Sets validator options
     *
     * @param  string $tableName
     * @param  string $columnName
     * @param  string $columnValue
     * @return void
     */
    public function __construct($columnName,$tableName)
    {
        //$this->setArrayTable($arrayName);
        $this->setColumnName($columnName);
        $this->setTableName($tableName);
        
    }
	protected $_messageTemplates = array(
		self::NOTEXIST => 'Giá trị này không tồn tại',
		self::DATABASEERROR =>'Lỗi truy cập Cơ sở dữ liệu'
	);
	public function isValid($value)
	{
		global $db;
		$value = (string) $value;		
		$this->_setValue($value);
		try 
		{
			$r = $db->query("
					SELECT
						".$this->getTableName().".*
					FROM				
						".$this->getTableName()."
					WHERE
						".$this->getColumnName()."=?",array($value));
			
			$listData = $r->fetchAll();
		}
		catch(Exception $e2)
		{
			$this->_error(self::DATABASEERROR);
			return false;
		}
		if($listData==null)
		{
			$this->_error(self::NOTEXIST);
			return false;
		}
		return true;
	}
} 
?>