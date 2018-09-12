<?php
class ProcessMessage {
    public static function ExecuteFunction($service_code,$function_name,$parameter) {
		
		$filename = "../../models/".$service_code.'.php';
		//return getcwd();
		
		if (file_exists($filename)){			
			include($filename);			
			return GetService($function_name,$parameter);
		}else{
			return "The service code is wrong";
		}
	}
}?>