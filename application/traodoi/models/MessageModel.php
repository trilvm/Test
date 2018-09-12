<?php

class MessageModel extends Zend_Db_Table
{
    protected $_name = 'td_message';
    public $_message_id= 0;
    
    public function getData($chat_id,$message_id,$id_chude)
    {
    	$r = $this->getDefaultAdapter()->query("
    			SELECT message_id, user_name, message, date_format(post_time, '%h:%i') as post_time 
				FROM 
				$this->_name 
				WHERE chat_id = ? AND message_id > ? AND id_chude = ?",array($chat_id,$message_id,$id_chude));
    	return $r->fetchAll();   	
    }

}