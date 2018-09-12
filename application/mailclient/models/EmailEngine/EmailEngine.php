<?php
require_once('mailclient/models/Email_account.php');

 interface  EmailEngine{
	
	public function login();
	
	public function logout();
	
	public function testConnect();
		
	}
	
	

?>