<?php
/*
 * PDF highlighting script by Chung Leong (chernyshevsky@hotmail.com)
 * The script opens a specified PDF file, extract the text within, and scan it for specified terms.
 * It then generates a Acrobat highlight file, which tells the Acrobat reader to highlight the terms. 
 * The first page containing highlighted terms will appear first when the reader opens the PDF file.
 * See Adobe Technical Note #5172 for more info.
 * 
 *
 * Usage: Append the following to the URL to your PDF file
 * 
 * #xml=http://<yourserver.net>/pdfhi.php?(parameters...)
 *
 * (e.g. http://www.conradish.com/23.1nunn.pdf#xml=http://www.conradish.com/pdfhi.php?filepath=23.1nunn.pdf&search=china,russia)
 *
 * Parameters:
 *
 * filepath: local path to the PDF file (required)
 * search: a comma delimited list of terms to highlight (required)
 * start_pg: first page to look; zero base (0 -> first page, 89 -> 90th, so on) (optional, 0 if omitted) 
 * end_pg: last page to look (optional; if omitted, set to start_pg if that's provided, otherwise scan every page)
 * all: 1 or 0; highlight all matching word or just the first ones found (optional, highlight only first found by default)
 *
 * Comments:
 *
 * That's a lot of code just to highlight a bunch of lousy words! The parsing code is potentially useful 
 * for extracting info from a PDF file and searching. Just cut and paste. 
 *
 */

function extract_int($s, $name)
{
// extract an integer or a reference from a dictionary
// if /$name appears in a string just before our entry
// then we're screwed
if($buf = strstr($s, "/$name "))
 {
 // see if it's a reference
 if(sscanf($buf, "/$name %d %d R", $obj_num, $gen_num) == 2)
  return "$obj_num $gen_num R";
 else 
  return $obj_num;
 }
}

function extract_type($s, $name = 'Type')
{
if($buf = strstr($s, "/$name "))
 {
 sscanf($buf, "/$name /%s ", $type);
 return $type;
 }
}

function extract_ref($s, $name)
{
if($buf = strstr($s, "/$name "))
 {
 // see if it's a reference
 if(sscanf($buf, "/$name %d %d R", $obj_num, $gen_num) == 2)
  return "$obj_num $gen_num R";
 }
}

function extract_dict_str($s, $name)
{
// extract a string representing a dictionary or a reference
if($buf = strstr($s, "/$name "))
 {
 // see if it's a reference
 if(sscanf($buf, "/$name %d %d R", $obj_num, $gen_num) == 2)
  return "$obj_num $gen_num R";
 else 
  {
  $sp = $prev_sp = strpos($buf, '<<');
  $prev_ep = $sp;
  while($ep = strpos($buf, '>>', $prev_ep + 2))
   {   
   $prev_sp = strpos($buf, '<<', $prev_sp + 2);
   if(!$prev_sp) break;
   $prev_ep = $ep;
   }
  if($ep) return substr($buf, $sp, $ep - $sp + 2);
  }
 }
}

function extract_array_str($s, $name)
{
// extract a string representing a array or a reference
// can't really deal with string arrays, since
// strings might contain unescaped brackets
if($buf = strstr($s, "/$name "))
 {
 // see if it's a reference
 if(sscanf($buf, "/$name %d %d R", $obj_num, $gen_num) == 2)
  return "$obj_num $gen_num R";
 else 
  {
  $sp = $prev_sp = strpos($buf, '[');
  $prev_ep = $sp;
  while($ep = strpos($buf, ']', $prev_ep + 1))
   {   
   $prev_sp = strpos($buf, '[', $prev_sp + 1);
   if(!$prev_sp) break;
   $prev_ep = $ep;
   }
  if($ep) return substr($buf, $sp, $ep - $sp + 1);
  }
 }
}

function extract_ref_array($s, $name)
{
if($a = trim(substr(extract_array_str($s, $name), 1, -1)))
 {
 $elements = array();
 $r = explode('R', $a);
 foreach($r as $e)
  {
  if($e) $elements[] = "$e R";
  }
 return $elements;
 }
}

function is_ref($s)
{
return ($s[strlen($s) - 1] == 'R');
}

class pdf_parser {
var $file;
var $file_path;
var $first_page_offsets;
var $main_offsets;
var $document_offsets;
var $linearized;

// PDF objects:
var $trailer;
var $root;
var $pages;

 function open($file_path)
 {
 echo $this->file_path;
 $this->file_path = $file_path;
 $this->file = fopen($file_path, 'rb') or die('');
 fseek($this->file, -32, SEEK_END);
 // find out where cross reference table starts
 $buf = fread($this->file, 32);
 $startxref = strstr($buf, 'startxref') or die('');
 sscanf($startxref, "startxref %d", $xref_pos);
 // seek to xref table, then read it; trailer follows
 fseek($this->file, $xref_pos);
 $this->document_offsets = $this->read_xref();
 $this->trailer = $this->read_trailer();
 // see if there's another xref table (linearized PDF)
 if($prev_xref_pos = extract_int($this->trailer, 'Prev'))
  {
  $this->first_page_offsets = $this->document_offsets;
  fseek($this->file, $prev_xref_pos);
  $this->main_offsets = $this->read_xref();
  $this->document_offsets = array_merge($this->main_offsets, $this->first_page_offsets);
  $this->linearized = true;
  }
 $this->root = $this->resolve(extract_dict_str($this->trailer, 'Root')) or die('');
 $this->pages = $this->read_page_tree();
 }

 function read_xref()
 {
 $buf = fread($this->file, 32);
 $startxref = strstr($buf, 'xref') or die('');
 // get first index of table, and number of records
 sscanf($startxref, "xref %d %d", $xref_start, $xref_count); 
 // grab the first bits of the table we have already
 $buf = ltrim(substr($buf, strlen("xref $xref_start $xref_count")));
 // read the rest of the table; records are 20 bytes each
 $buf .= fread($this->file, $xref_count * 20 - strlen($buf));
 $offsets = array();
 $table = explode("\r", $buf, $xref_count);
 foreach($table as $i => $record)
  {
  $offsets[$xref_start + $i] = (int) $record;
  }
 return $offsets;
 }

 function read_trailer()
 {
 $buf = fread($this->file, 64);
 $buf = ltrim(substr(strstr($buf, 'trailer'), 7)) or die('');
 while(!($leftover = strstr($buf, '>>'))) $buf .= fread($this->file, 64) or die('');
 // chop off leftover stuff (everything after '>>')
 return strtr(substr($buf, 0, strlen($buf) - strlen($leftover) + 2), "\r\n", "  ");
 }

 function read_obj($ref)
 {
 // ignoring generation number; PDF files with incremental updates are rare
 if($offset = $this->document_offsets[(int) $ref])
  {
  fseek($this->file, $offset);
  $buf = ltrim(substr(strstr(fread($this->file, 64), "obj"), 3));
  while(!($leftover = strstr($buf, 'endobj'))) $buf .= fread($this->file, 64) or die('');
  // chop off 'endobj' and every thing after 
  return trim(strtr(substr($buf, 0, strlen($buf) - strlen($leftover) - 1), "\r\n", "  "));
  } 
 }

 function read_stream_obj($ref)
 {
 if($offset = $this->document_offsets[(int) $ref])
  {
  fseek($this->file, $offset);
  $buf = ltrim(substr(strstr(fread($this->file, 64), "obj"), 3));
  while(!($leftover = strstr($buf, 'stream'))) $buf .= fread($this->file, 64) or die('');
  // chop off 'stream' and every thing after;
  $dict = trim(strtr(substr($buf, 0, strlen($buf) - strlen($leftover) - 2), "\r\n", "  "));
  // get the length of the stream data from the dictionary
  // remember where the file pointer is, since length could be
  // a reference to a number somewhere and resolving the ref would
  // move the file pointer
  $fp = ftell($this->file);
  $stream_len = $this->resolve(extract_int($dict, 'Length'));
  // backtrack to just after 'stream'
  fseek($this->file, $fp - strlen($leftover) + 6, SEEK_SET);
  // 'stream' could be followed by just lf or crlf
  $test_byte = fread($this->file, 1);
  if($test_byte == "\r") { fread($this->file, 1); }
  $data = fread($this->file, $stream_len);
  return array($dict, $data);
  } 
 }

 function read_page_tree()
 {
 $pages = $this->resolve(extract_dict_str($this->root, 'Pages')) or die('');
 return $this->read_pages_obj($pages);
 }

 function read_pages_obj($pages_obj)
 {
 $pages = array();
 $kid_refs = extract_ref_array($pages_obj, 'Kids');
 $kids = $this->resolve($kid_refs) or die('');
 foreach($kids as $index => $kid)
  {
  $kid_type = extract_type($kid);
  if($kid_type == 'Page')
   {
   $pages[$kid_refs[$index]] = $kid;
   }
  else if($kid_type == 'Pages')
   {
   $grandkids = $this->read_pages_obj($kid);
   $pages = array_merge($pages, $grandkids);
   }
  }
 return $pages;
 }

 function read_page_contents($page)
 {
 if($content_ref = extract_ref($page, 'Contents'))
  {
  return $this->read_stream_obj($content_ref);
  }
 else if($contents = extract_ref_array($page, 'Contents'))
  {
  $dict_array = array();
  $data_array = array();
  foreach($contents as $content_ref)
   {
   list($dict, $data) = $this->read_stream_obj($content_ref);
   $dict_array[] = $dict;
   $data_array[] = $data;
   }
  return array($dict_array, $data_array);
  }
 }

 function resolve($value)
 { 
 if(is_array($value))
  {
  $results = array();
  foreach($value as $key => $element)
   {
   $results[$key] = $this->resolve($element);
   }
  return $results;
  }
 else
  {
  if(is_ref($value))
   return $this->read_obj($value);
  return $value;
  }
 }

 function close()
 {
 fclose($this->file);
 }
}

function extract_text($s)
{
// de-escape slashes and parantheses, changing to the latter to
// guillemets to make parsing easier; will change them back at the end
// since guillemets aren't standard chars anyway, this isn't that bad
$s = strtr($s, array('\\\\' => '\\', '\)' => "\xBB", '\(' => "\xAB"));
$start_pos = array();
$end_pos = array();
// find all the text strings
while(($sp = strpos($s, '(', $ep + 1)) && ($ep = strpos($s, ')', $sp + 1)))
 {
 $start_pos[] = $sp;
 $end_pos[] = $ep;
 }
// restore parantheses, convert fancy quotes and emdash to closest ascii equivalents 
// because we don't know what encoding is employed (and finding out is a pain),
// we'll just convert them all; this will mess up european text
$tokens = array();
$s = strtr($s, "\xBB\xAB\xAA\xD2\x93\x8D\xBA\xD3\x94\x8E\xD4\x92\x8F\xD5\x93\x90\xD0\xD1\x97\x84", 
               ")(\"\"\"\"\"\"\"\"''''''----");
foreach($start_pos as $i => $sp)
 {
 $ep = $end_pos[$i];
 $opep = $sp - 1;
 $str = substr($s, $sp + 1, $ep - $sp - 1);
 $ops = substr($s, $opsp, $opep - $opsp + 1);
 // check the op codes preceeding the string; if there's an operation that changes
 // the vertical text position, then the string is not part of the current token
 $new_tok = False;
 if(strpos($ops, 'T*'))
  {
  $new_tok = True;
  }
 else if(($opp = strpos($ops, 'Td')) || ($opp = strpos($ops, 'TD')))
  {
  // the last parameter change the vertical positioning
  $ops = strtr($ops, "\r\n", "  ");
  $oppp = $opp - 2;
  while($ops[$oppp] != ' ') $oppp--;
  $ty = substr($ops, $oppp + 1, $opp - $oppp - 2);
  if($ty != 0)
   {
   $new_tok = True;
   }
  }
 else if($opp = strpos($ops, 'Tm'))
  {
  // the last parameter change the vertical positioning
  $ops = strtr($ops, "\r\n", "  ");
  $oppp = $opp - 2;
  while($ops[$oppp] != ' ') $oppp--;
  $new_tm_f = substr($ops, $oppp + 1, $opp - $oppp - 2);
  if($new_tm_f != $tm_f)
   {
   $new_tok = True;
   $tm_f = $new_tm_f;
   }
  }
 if($new_tok)
  {
  if($cur_tok) $tokens[] = $cur_tok;
  $cur_tok = $str;
  }
 else
  {
  $cur_tok .= $str;
  }
 $opsp = $ep + 1;
 }
	$tokens[] = $cur_tok;
	return $tokens;
}

function inflate($data)
{
if(function_exists('gzinflate'))
 {
 $data = substr($data, 2);
 // dunno why this works; got it to through trial and error
 $data[0] = chr(ord($data[0]) | 0x01);
 return gzinflate($data);
 }
else
 {
 // don't know what the CRC is; gzip will spit out an error
 $header = "\x1F\x8B\x08\x00\x00\x00\x00\x00\x00\x00";
 $file = fopen('.tmp.gz', 'wb') or die('');
 fwrite($file, $header);
 fwrite($file, substr($data, 2));
 fclose($file);
 return `gzip -cdq .tmp.gz`;
 }
}

function isalpha($c)
{
return ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z');
}

function find_terms($s, $terms, $offset)
{
if($offset >= strlen($s))
 {
 return;
 }
$s = strtolower($s);
foreach($terms as $index => $term)
 {
 if(($pos = strpos($s, $term, $offset)) !== false)
  {
  if($pos == 0 || !isalpha($s[$pos - 1]))
   {
   $len = strlen($term);
   if(!isalpha($s[$pos + $len]))
    return array($pos, $len, $index);
   }
  }
 }
}


?>