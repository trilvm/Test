<?php
class Unitech_Validate_NoExistItemOnTableVBDi extends Zend_Validate_Abstract
{
	const EXIST = 'Exist';
	const DATABASEERROR = 'DBError';
	const CONSTRUCTERROR='ConstructError';
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
    protected $_primaryColumnId;
    
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
     * Returns the tableName option
     *
     * @return String
     */
    public function getPrimaryColumnId()
    {
        return $this->_primaryColumnId;
    }

    /**
     * Sets the tableName option
     *
     * @param  String $name     
     *  
     *
     */
    public function setPrimaryColumnId($name)
    {
    	if (null == $name) 
    	{
            require_once 'Unitech/Validate/Exception.php';
            throw new Unitech_Validate_Exception("Truyền tên không hợp lệ");
        }
        else        
        {
            $this->_primaryColumnId = $name;
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
     * @param  string $primaryColumnId
     * @return void
     */
    public function __construct($columnName,$tableName,$primaryColumnId)
    {
        $this->setColumnName($columnName);
        $this->setTableName($tableName);
        $this->setPrimaryColumnId($primaryColumnId);
        
    }
	protected $_messageTemplates = array(
		self::EXIST => 'Giá trị này đã tồn tại',
		self::DATABASEERROR =>'Lỗi truy cập Cơ sở dữ liệu',
		self::CONSTRUCTERROR=>'Lỗi khởi tạo'
	);
	public function isValid($value, $context = null)
	{
		global $db;
		$value = (string) $value;		
		$this->_setValue($value);		
		$where='';
		if(isset($context['ID_CQ']) && $context['ID_CQ']!='')	$where="AND ID_CQ = ".$context['ID_CQ'];
		if(isset($context['NGAYBANHANH']) && $context['NGAYBANHANH']!='')   $where.=" AND YEAR(NGAYBANHANH) = ".QLVBDHCommon::getYear();
		if(isset($context['ID_LVB']) && $context['ID_LVB']!='')   $where.=" AND ID_LVB = ".$context['ID_LVB'];
		if (isset($context['id']) && $context['id']!='' )
		{
			$r = $db->query("
					SELECT
						".$this->getTableName().".*
					FROM				
						".$this->getTableName()."
					WHERE
						".$this->getColumnName()."=? AND
						".$this->getPrimaryColumnId()." <> ?".$where,array($value,$context['id']));
				
		}
		else 
		{
			$r = $db->query("
						SELECT
							".$this->getTableName().".*
						FROM				
							".$this->getTableName()."
						WHERE
							".$this->getColumnName()."=?".$where,array($value));		
		}
		try 
		{
			$listData = $r->fetchAll();
			
		}
		catch(Exception $e2)
		{
			$this->_error(self::DATABASEERROR);
			echo $e2;
			return false;
		}			
		if($listData!=null)
		{
			$this->_error(self::EXIST);
			return false;
		}		
		return true;		
	}
} 
?>