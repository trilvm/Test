<?php
set_time_limit(0);
function MakePropertyValue($name,$value,$osm){
$oStruct = $osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue");
$oStruct->Name = $name;
$oStruct->Value = $value;
return $oStruct;
}
function word2pdf($doc_url, $output_url){
//Invoke the OpenOffice.org service manager
$osm = new COM("com.sun.star.ServiceManager") or die ("Please be sure that OpenOffice.org is installed.\n");
//Set the application to remain hidden to avoid flashing the document onscreen
$args = array(MakePropertyValue("Hidden",true,$osm));
//Launch the desktop
$oDesktop = $osm->createInstance("com.sun.star.frame.Desktop");
//Load the .doc file, and pass in the "Hidden" property from above
$oWriterDoc = $oDesktop->loadComponentFromURL($doc_url,"_blank", 0, $args);
//Set up the arguments for the PDF output
$export_args = array(MakePropertyValue("FilterName","writer_pdf_Export",$osm));
//Write out the PDF
$oWriterDoc->storeToURL($output_url,$export_args);
$oWriterDoc->close(true);
}
$output_dir = "C:/";
$doc_file = "C:/Untitled 1.doc";
$pdf_file = "DpmR5Reqv1.20.pdf";
$output_file = $output_dir . $pdf_file;
$doc_file = "file:///" . $doc_file;
$output_file = "file:///" . $output_file;
word2pdf($doc_file,$output_file);
?>