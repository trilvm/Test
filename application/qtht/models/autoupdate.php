<?php
class AutoUpdate{
	static function SelectVersionList($folder){
		$config = Zend_Registry::get('config');
		$url = $config->autoupdate->service;
		$s = file_get_contents($url."/".$folder."/ver.ini");
		return explode(PHP_EOL, $s);
	}

	static function TookVersion($curver,$verlist){
		foreach($verlist as $itemver){
			if($curver<$itemver){
				return $itemver;
			}
		}
		return 0;
	}
	static function TookFiles($curver,$folder){
		$config = Zend_Registry::get('config');
		$url = $config->autoupdate->service;
		$s = file_get_contents($url."/".$folder."/".$curver."/list.ini");
		return explode(PHP_EOL, $s);
	}
	static function UpdateFile($curver,$folder,$file){
		$config = Zend_Registry::get('config');
		$url = $config->autoupdate->service;
		try{
			if(strtolower (substr($file,-8))==".sqlcode"){
				global $db;
				$sql = explode(";", file_get_contents($url."/".$folder."/".$curver."/".$file));
				//var_dump($url."/".$folder."/".$curver."/".$file);
				foreach($sql as $sqlitem){
					//echo $sqlitem;
					if($sqlitem!=""){
						try{
							$db->query($sqlitem);
						}catch(Exception $ex1){
						}
					}
				}
			}else{
				$filetemp = substr($file,0,-4);
				$arrfilename = explode("/",$filetemp);
				$folderpath = "";
				for($i=0;$i<count($arrfilename)-1;$i++){
					$folderpath .= $arrfilename[$i] . "/";
				}
				mkdir("../".$folderpath,777,true);
				file_put_contents("../".$filetemp,file_get_contents($url."/".$folder."/".$curver."/".$file));
			}
		}catch(Exception $ex){
		}
	}
}