<?php
function listid($var)
{
    // returns whether the input integer is odd
    return($var["Subject"]["id"]);
}

$a = array(
0=>array("Subject"=>array("id"=>19)),
1=>array("Subject"=>array("id"=>20)),
2=>array("Subject"=>array("id"=>21)),
3=>array("Subject"=>array("id"=>22)),

);

var_dump($a);

$b = array_map("listid",$a);
var_dump($b);