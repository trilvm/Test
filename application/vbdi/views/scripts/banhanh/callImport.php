 	<?php
header("Content-Type: text/xml; charset=utf-8");
include('nusoap.php');
  $query = isset($HTTP_RAW_POST_DATA)? $HTTP_RAW_POST_DATA : 0;
  $sp = new soap_parser($query, 'UTF-8', 'POST');
  $c = new soapclient('http://www.danangcity.gov.vn/serviceVBPQ/Service');
  $c->call('importDocument', array('text'=>$text));
echo $c->responseData;
  ?>

