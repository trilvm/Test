<?php
class Common_VanBanDuThao{
	static function insertVanBanDuThaoDiv($iddiv,$idHSCV,$year){
		
		echo '<a href="#" onclick=loadDivFromUrl("'.$iddiv.'","/hscv/VanBanDuThao/index/year/'.$year.'/iddivParent/'.$iddiv.'/idHSCV/'.$idHSCV.'",1)>Văn bản dự thảo</a>';
	}
}
?>