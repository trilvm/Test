<?php

$domain = $_SERVER["HTTP_HOST"];
switch ($domain) {
	 default :
		 define('IMAGE_PATH','');
		 define('APP_NAME','APPLICATION');
		 break;
}
