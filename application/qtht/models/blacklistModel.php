<?php

/**
 * newsModel
 *  
 * @author truongvc
 * @version 
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');
class blacklistModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    protected $_name   = 'blacklist';
    public $_search = '';   
  /**
     * Select
     */
    public function SelectAll($offset,$limit,$order){
        //Build where
        $arrwhere = array();
        $strwhere = "(1=1)";
       	if($this->_search != ""){
       		$arrwhere[] = '%'.$this->_search.'%';
            $strwhere .= " and (
            	IP like ?
            	)";
        }          
        //Build limit
        $strlimit = "";
        if($limit>0){
            $strlimit = " LIMIT $offset,$limit";
        }
        
        //Build order
        $strorder = "";
        if($order!=""){
            $strorder = " ORDER BY $order";
        }
        
        //query
        $result = $this->getDefaultAdapter()->query("
            SELECT
                *, date_format(lasttry, '%m/%d/%Y') as lasttry
            FROM
               $this->_name
            WHERE
            $strwhere
			$strorder
            $strlimit
        ",$arrwhere);
        return $result->fetchAll();
    }
    /**
     * rong table
     */
    public function Count()
    {
    	$strwhere = "(1=1)";
    	$arrwhere = array();
        if($this->_search != ""){
       		$arrwhere[] = '%'.$this->_search.'%';
            $strwhere .= " and (
            	IP like ?
            	)";
        }               
        $result = $this->getDefaultAdapter()->query("
        	SELECT count(*) as C 
            FROM
               $this->_name
            WHERE
            $strwhere"
		   ,$arrwhere)->fetch();
        return $result["C"];
    }    
	public static function checkExist($ip)
	{
		try
		{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$result = $dbAdapter->query ("
			SELECT
 				*
			FROM
				blacklist
			WHERE
				IP=?
			",array($ip));
			$data= $result->fetchAll();
            if(count($data)>0)
            {
               return true;
            }
            else
            {
				return false;
			}
		}
		catch (Zend_Exception  $ex)
		{
			return false;
		}
	}
	public static function writeTry()
	{
		$try=Zend_Registry::get ( 'config' )->sys_info->try;
		if(QLVBDHCommon::getTry()>$try)
		{
			$ip=getenv("REMOTE_ADDR");
			$time=new Zend_Db_Expr('NOW()');
			if(!blacklistModel::checkExist(getenv("REMOTE_ADDR")))
			{
				try
				{
					$dbAdapter = Zend_Db_Table::getDefaultAdapter();
					$r = $dbAdapter->prepare("
							INSERT INTO 
									blacklist(IP,lasttry)  
					        VALUES('$ip',$time)");
		    		$r->execute();	
					QLVBDHCommon::clearTry();			
				}
				catch (Zend_Exception  $ex)
				{
					return false;			
				}
			}			
		}
		else
		{
			
		}	
	}		
}