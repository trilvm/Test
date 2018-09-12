<?php

class Common_FileDinhKem {
	static function InsertFileDinhKem($div_id,$idObject,$is_new,$year,$type){
		/*echo '
		<a href="#" onclick="divFileDinhKemId='.'\''.$div_id.'\''.';loadDiv(divFileDinhKemId,'.'\''.'/hscv/file'.'\''.',0,'.$idObject.','.$is_new.','.$year.')">File đính kèm</a>
		';*/
		echo '<a href="#" onclick="divFileDinhKemId='.'\''.$div_id.'\''.';url='.'\''.'/hscv/file?iddiv='.$div_id.'&idObject='.$idObject.'&is_new='.$is_new.'&year='.$year.'&type='.$type.'\''.';loadDivFromUrl(divFileDinhKemId,url,0);return false;">File Đính Kèm</a>';
	}
}

?>
