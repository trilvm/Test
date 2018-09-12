<?php

/**
 * mail
 *  
 * @author nguyennd
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';
require_once 'mailclient/models/Email_messages.php';

class mail extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'amail_message';
	static function ToTree($data,$id_parent,$name_tree,&$html,$sel){                        
        $isFirst = false;
        
        foreach($data as $row){
        	if($row["ID_EF_PARENT"]==$id_parent){
        		if(!$isFirst){
        			$isFirst=true;
        			if($id_parent==1)
                        $html .= "<ul id=" . $name_tree . ">";
                            else
                                $html .= "<ul>";
        		}
        		if($row['ID_EF']==$sel){
                	$html .= "<li class='mail_folder_open'>";
                	$html .= "<img src=/images/open.gif border=0 align=left ><span class=mail_open onclick='folder_click(".$row["ID_EF"].");return false;' >".$row["NAME"].
					($row["CODE"]=="inbox" && mail::SelectUnread_Inbox() > 0 ? ( " <font color=#000>[".mail::SelectUnread_Inbox()."]</font>" ) :"").	
					"</span>";
        		}else{
        			 $html .= "<li>";
        			 $html .= "<a href='#' onclick='folder_click(".$row["ID_EF"].");return false;' >".$row["NAME"].
					($row["CODE"]=="inbox" && mail::SelectUnread_Inbox() > 0 ? ( " <font color=#FFF>[".mail::SelectUnread_Inbox()."]</font>" ) :"").	
					"</a>";
        		}
				
        	    mail::ToTree($data,$row["ID_EF"],$name_tree,&$html,$sel);
        	    $html .= "</li>";
        	}
        }
        if($isFirst)
            $html .= "</ul>";
        return $html;
    }
    /**
     * $param:
     * 		IDFOLDER
     * 		ID_U
     * 		SENDER
     * 		SEARCH
     * 		FROMDATE yyyy-mm-dd
     * 		TODATE yyyy-mm-dd
     * 		
     */
    static function Count($param){
    	global $db;
    	$user = Zend_Registry::get("auth")->getIdentity();
		
		$arrparam = array();
    	$where = "(1=1)";
    	$innerjoin = "";
    	$orderby = "";
    	
    	if($param['SEARCH']!=""){
    		Common_DBUtils::repairTableBeforeFulltextSearch("EMAIL_MESSAGES");
    		$where .= " AND match(em.SUBJECT) against (? IN BOOLEAN MODE)";
    		$arrparam[] = $param['SEARCH'];
    	}
    	if($param['IDFOLDER'] > 1){
    		$innerjoin .= " INNER JOIN EMAIL_FOLDER f on f.CODE = em.FOLDER_CODE";
    		$where .= " AND f.ID_EF = ?";
    		$arrparam[] = $param['IDFOLDER'];
    	}
    	
    	$sql = "
    		SELECT
    			count(*) as CNT
    		FROM
    			EMAIL_MESSAGES em
    			$innerjoin
    		WHERE
    			em.ID_U= $user->ID_U and $where
    		$orderby
    	";
    	$r = $db->query($sql,$arrparam);
    	$cnt = $r->fetch();
    	return $cnt['CNT'];
    }
    
	static function SelectUnread_Inbox(){
		
		global $db;
    	$user = Zend_Registry::get("auth")->getIdentity();
		$sql = "
    		SELECT
    			count(*) as DEM
    		FROM
    			EMAIL_MESSAGES em
    			INNER JOIN EMAIL_FOLDER f on f.CODE = em.FOLDER_CODE
    		WHERE
    			em.ID_U= $user->ID_U and IS_READ = 0 and f.CODE='inbox'
    		
    	";
    	$r = $db->query($sql,$arrparam);
    	$re = $r->fetch();
		return (int)$re["DEM"];
	}
	
	static function selectUnreadMails(){
		$db = Zend_Db_Table::getDefaultAdapter();
    	$user = Zend_Registry::get("auth")->getIdentity();
		$sql = "
    		SELECT
    			*
    		FROM
    			EMAIL_MESSAGES em
    			INNER JOIN EMAIL_FOLDER f on f.CODE = em.FOLDER_CODE
    		WHERE
    			em.ID_U= ? and IS_READ = 0 and f.CODE='inbox'
    		
    	";
    	try{
			$r = $db->query($sql,array($user->ID_U,));
    		$re = $r->fetchAll();
			return $re;
		}catch(Exception $ex){
			return array();
		}
		
	}

	static function SelectAll($param,$offset,$limit,$order){
    	global $db;
    	$user = Zend_Registry::get("auth")->getIdentity();
		
		$arrparam = array();
    	$where = "(1=1)";
    	$innerjoin = "";
    	$orderby = "";
    	
    	if($param['SEARCH']!=""){
    		Common_DBUtils::repairTableBeforeFulltextSearch("EMAIL_MESSAGES");
    		$where .= " AND match(em.SUBJECT) against (? IN BOOLEAN MODE)";
    		$arrparam[] = $param['SEARCH'];
    	}
    	if($param['IDFOLDER'] > 1){
    		$innerjoin .= " INNER JOIN EMAIL_FOLDER f on f.CODE = em.FOLDER_CODE";
    		$where .= " AND f.ID_EF = ?";
    		$arrparam[] = $param['IDFOLDER'];
    	}
    	
    	$sql = "
    		SELECT
    			*
    		FROM
    			EMAIL_MESSAGES em
    			
				$innerjoin
    		WHERE
    			em.ID_U= $user->ID_U and $where
    		ORDER BY
    			em.TIME DESC
    		LIMIT
    			$offset,$limit
    		$orderby
    	";
    	$r = $db->query($sql,$arrparam);
    	return $r->fetchAll();
    }
    static function Unread($id,$idu){
    	global $db;
    	$db->update("EMAIL_MESSAGES",array("IS_READ"=>0),"ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
    }
	static function Read($id,$idu){
    	global $db;
    	$db->update("EMAIL_MESSAGES",array("IS_READ"=>1),"ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
		//echo "ID_EM in (".implode(",",$id).") AND ID_U=".$idu;exit;
    }
	static function Mark($id,$idu){
    	global $db;
    	$db->update("EMAIL_MESSAGES",array("IS_MARK"=>1),"ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
		
    }
	static function Unmark($id,$idu){
    	global $db;
    	$db->update("EMAIL_MESSAGES",array("IS_MARK"=>0),"ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
    }
	
	static function DeleteMessge($ids,$idu){
		//global $db;
    	//var_dump($id);exit;
		//$db->delete("EMAIL_MESSAGES","ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
		foreach($ids as $id){
			Email_messages::deleteMessage($id);
		}
		
	}
	static function SentToTrash ($id,$idu){
		global $db;
		$db->update("EMAIL_MESSAGES",array("FOLDER_CODE"=>"trash"),"ID_EM in (".implode(",",$id).") AND ID_U=".$idu);
	}
	static function getNameFolderById($id_folder){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select NAME from `email_folder` where `ID_EF`=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_folder));
			$re = $qr->fetch();
			return $re["NAME"];
		}catch(Exception $ex){
			return "";
		}
		
	}

	static function getCodeFolderById($id_folder){
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = "
			select CODE from `email_folder` where `ID_EF`=?
		";
		try{
			$qr = $dbAdapter->query($sql,array($id_folder));
			$re = $qr->fetch();
			return $re["CODE"];
		}catch(Exception $ex){
			return "";
		}
		
	}
}
