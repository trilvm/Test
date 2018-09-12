<?php
require_once 'Common/pdfConvert.php';
class ConvertFileUtils {
	
	static function getExePathToParseWord(){
		$config = Zend_Registry::get('config');
		$os = $config->cgi->os;
		if($os == 'win'){
			return $config->cgi->parse_word_exe_win;
		}
		
	}
	static function convertWordToUTF8Text($path_word,$path_txt){
		$path_exe = ConvertFileUtils::getExePathToParseWord();
		$cmd = $path_exe . ' ' . $path_word . ' -m utf-8 > ' . $path_txt;
		//echo $cmd;
		exec($cmd); 
	}
	static function convertPDFToText($filename){
		//echo "trung khung";
		//$filename = $HTTP_GET_VARS['filepath'];
		$terms = "";//explode(',', strtolower($HTTP_GET_VARS['search']));
		$all = 1;//$HTTP_GET_VARS['all'];
		$start_pg = 0;//= $HTTP_GET_VARS['start_pg'];
		$end_pg = 2147483647;//= $HTTP_GET_VARS['end_pg'];
		//if($end_pg < $start_pg) $end_pg = $start_pg;
		//if($end_pg == '') $end_pg = 2147483647;
		echo $filename;
		$pdf = new pdf_parser;
		$pdf->open($filename);
		
		
		
		/*$pg = 0;
		foreach($pdf->pages as $index => $page)
		{
		 	if(!$terms) break;
			if($pg > $end_pg) break;
			if($pg >= $start_pg)
			{
				list($dict, $data) = $pdf->read_page_contents($page);
		  		$filter = extract_type($dict, 'Filter');
		 		 // only know how to deflate (for text streams that's really the only filter)
		  		if($filter == 'FlateDecode' || $filer == '' || die(''))
		   		{
		   			$texts = extract_text($filter == 'FlateDecode' ? inflate($data) : $data);
			   		// extract_text returns a array of strings
			  		// each string is a line of text or just some isolated words on the page
			  		// (the page number, for instance)
			   		$total_len = 0;
		   			foreach($texts as $line){
		   			 	echo $line;
		   			}
		   
		  		}
		 	}	
		 	$pg++;
		 }*/
		
		 	
		 $pdf->close();
		 	
	}
	
	
}

?>
