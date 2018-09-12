<?php
define("ENCRYPTION_KEY", "Unitech@;");
require_once '../application/qtht/models/BussinessDateModel.php';
//require_once '../application/auth/models/ResourceUserModel.php';
//require_once '../application/hscv/models/hosocongviecModel.php';

class QLVBDHCommon {
	static function getTreHanNgay($ngaydukien){
			$ngaydukien = strtotime($ngaydukien);
                        $ngayhientai = $ngayhientai = strtotime(date('d-m-Y'));
                        $datediff = $ngaydukien - $ngayhientai;
                        $ngay = floor($datediff / (60*60*24));
                        if($ngay ==0 )
                        {
                        return '<font style="color:blue;">( Hôm nay là ngày cuối ! )</font>';
                        }elseif($ngay > 0)
                        {
                        return '<font style="color:blue;">( Còn '.$ngay.' ngày )</font>';    
                        }elseif($ngay < 0)
                        {
                        return '<font style="color:red;">( Trễ '.abs($ngay).' ngày )</font>';    
                        }    
	}    
	static function makeCQName($string){
		$strings = explode("/",$string,2);
		if(count($strings)==2){
			return $strings[1];
		}
		return $string;
	}
    static function useDlgConfirm() {

        $html = '<div id="confirm_dlg" >';
        $html.= '<div>';
        $html.= '<p id=content_confirm>Content you want the user to see goes here.</p>';
        $html.= '<input type="button" class=input_button style="width:50px;background-color:white" name=YES id=DLG_YES_BUTTON value="Có" onclick=\'overlay("YES")\' >';
        $html.= '<input type="button" class=input_button style="width:50px;background-color:white" name=CANCEL id=DLG_CANCEL_BUTTON value="Không" onclick=\'overlay("CAMCEL")\' >';
        $html.= '</div>';
        $html.= '</div>';
        return $html;
    }

    static function highlightString($string, $words) {
        $string = mb_strtolower($string, "UTF-8");
        $chars = preg_split("/[.,:]/", $string);
        $words = trim(mb_strtolower($words, "UTF-8"));
        $wordsArray = explode(' ', $words);
        $mark = array();
        $i = 0;
        foreach ($chars as $char) {
            foreach ($wordsArray as $word) {
                $mark[$i . "a"] += substr_count($char, $word);
            }
            $i++;
        }
        arsort($mark);
        $r = "";
        $i = 0;
        //var_dump($mark);
        foreach ($mark as $k => $v) {
            if ($v > 0) {
                $r .= "..." . $chars[(int) $k];
                $i++;
            }
            if ($i == 3
                )break;
        }
        return QLVBDHCommon::highlightWords(substr($r, 0, 256), $words, true);
    }

    static function highlightWords($text, $words, $nohighlight=false) {
        if (is_null($colors) || !is_array($colors)) {
            $colors = array('yellow');
        }

        $i = 0;
        /*         * * the maximum key number ** */
        $num_colors = max(array_keys($colors));

        $words = trim($words);
        $the_count = 0;
        $wordsArray = explode(' ', $words);
        foreach ($wordsArray as $word) {
            if (strlen(trim($word)) != 0) {
                $text = str_ireplace($word, '~' . $i . $word . '^' . $i, $text, $count);
                if ($i == $num_colors) {
                    $i = 0;
                } else {
                    $i++;
                }
                $the_count = $count + $the_count;
            }
        }
        //var_dump($colors);
        for ($i = 0; $i < count($colors); $i++) {
            if ($nohighlight) {
                $text = str_replace('~' . ($i), '<b><font color=red>', $text);
                $text = str_replace('^' . ($i), '</font></b>', $text);
            } else {
                $text = str_replace('~' . ($i), '<b><span style="background-color:' . $colors[$i] . '">', $text);
                $text = str_replace('^' . ($i), '</span></b>', $text);
            }
        }
        return $text;
    }
	static function highlightText($text, $words, $nohighlight=false) {
		if (is_null($colors) || !is_array($colors)) {
			$colors = array('yellow');
		}
                if(trim($words)!=""){
                    for ($i = 0; $i < count($colors); $i++) {
                            if ($nohighlight) {
                                    $text = str_replace(mb_strtolower($words,'UTF-8'), '<b><font color=red>'.mb_strtolower($words,'UTF-8').'</font></b>', mb_strtolower($text,'UTF-8'));                
                            } else {
                                    $text = str_replace(mb_strtolower($words,'UTF-8'), '<b><span style="background-color:' . $colors[$i] . '">'.mb_strtolower($words,'UTF-8').'</span></b>', mb_strtolower($text,'UTF-8'));
                            }
                    }
                }
		return $text;
	}

    // ======== 23/6/2016 - update by hoangnm - begin ========
    /**
     * @nội dung chỉnh sửa: Thêm đối số type ( 0 : input[type=text], 1 : textarea)
     */
    static function AutoComplete($data, $fieldid, $fieldname, $controlid, $controltext, $fullcompare, $style, $onokaction, $defaultid, $defaultname, $type=0) {
        $html = "
				<script>
					var DATA_$controlid = new Array();
			";
        foreach ($data as $item) {
            if ($item[$fieldid] == $defaultid)$defaultname =$item[$fieldname];
            if (strtoupper($item[$fieldname]) == strtoupper($defaultname))$defaultid =$item[$fieldid];
            $html.="DATA_" . $controlid . "[DATA_" . $controlid . ".length]=new Array('" . $item[$fieldid] . "','" . str_replace("'", "\\'", $item[$fieldname]) . "');";
        }
        $html.="
				</script>
			";
        if( $type == 0 ){
            $html.="
				<input autocomplete=off onclick='cancelEvent(event)' class=autocombobox value='".htmlspecialchars($defaultname,ENT_QUOTES)."' type=text style='$style' name=$controltext id=$controltext  onkeydown='at_KeyDown(event)' onkeyup='at_Display(event)' onfocus=\"at_Load('$controltext','$controlid',DATA_$controlid," . ($fullcompare == true ? "true" : "false") . ",'$onokaction');\">
				<input type=hidden style='$style' name=$controlid id=$controlid value='$defaultid'>
			";
        }else{
            $html.="
				<textarea rows=\"2\" autocomplete=off onclick='cancelEvent(event)' class=autocombobox style='$style' name=$controltext id=$controltext  onkeydown='at_KeyDown(event)' onkeyup='at_Display(event)' onfocus=\"at_Load('$controltext','$controlid',DATA_$controlid," . ($fullcompare == true ? "true" : "false") . ",'$onokaction');\">".htmlspecialchars($defaultname,ENT_QUOTES)."</textarea>
				<input type=hidden style='$style' name=$controlid id=$controlid value='$defaultid'>
			";
        }
        return $html;
    }
    // ======== 23/6/2016 - update by hoangnm - end ========

    static function Table($tablename) {
        return $tablename . "_" . QLVBDHCommon::getYear();
    }

    static function ShowError($message) {
        $this->_redirect("/demo/cd/list");
    }
    static function GetTree($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level) {
        global $db;
        //echo "SELECT *,$level as LEVEL from $tablename where $fieldidparent=$parentvalue";
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=?", array($parentvalue));
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTree(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }

    static function GetTreeWithWhere_2($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level, $where) {
        global $db;

        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=? AND $where and ACTIVE=1", array($parentvalue));
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTreeWithWhere_2(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1, $where);
        }
    }
	static function GetTreeWithWhere($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level, $where) {
        global $db;

        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=? AND $where", array($parentvalue));
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTreeWithWhere(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1, $where);
        }
    }

    static function GetTreeWithJoinWhere($tree, $tablename, $jointable, $tblid, $jtbleid, $fieldid, $fieldidparent, $parentvalue, $level, $where) {
        global $db;

        $sql = "SELECT *,$level as LEVEL from $tablename $tablename
			inner join $jointable $jointable on $tablename.$tblid = $jointable.$jtbleid
			where $fieldidparent=? AND $where ";

        $r = $db->query($sql, array($parentvalue));
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTreeWithJoinWhere(&$tree, $tablename, $jointable, $tblid, $jtbleid, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1, $where);
        }
    }

    static function GetTreeWithCase($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level, $params) {
        $arr_where = array();
        $arr_param = array($parentvalue);
        $ln = 0;
        foreach ($params as $key => $value) {
            $arr_where[] = " $key= ? ";
            $arr_param[] = $value;
            $ln = 1;
        }
        $where_pr = "";
        if ($ln == 1) {
            $where_pr = " and " . implode(" and ", $arr_where);
        }
        global $db;
        //echo "SELECT *,$level as LEVEL from $tablename where $fieldidparent=$parentvalue";
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=? $where_pr ", $arr_param);
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTree(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }

    static function GetTreeNoChild($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level, $id) {
        global $db;
        //echo "SELECT *,$level as LEVEL from $tablename where $fieldidparent=?";
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=?", array($parentvalue));
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            if ($rows[$i][$fieldid] != $id) {
                $tree[count($tree)] = $rows[$i];
                QLVBDHCommon::GetTreeNoChild(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1, $id);
            }
        }
    }

    static function GetTreeNoChildWithWhere($tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level, $id, $where) {
        global $db;
        //echo "SELECT *,$level as LEVEL from $tablename where $fieldidparent=?";
        //echo "SELECT *,$level as LEVEL from $tablename where $fieldidparent=? AND $where"; exit;
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where $fieldidparent=? AND $where", array($parentvalue));

        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            if ($rows[$i][$fieldid] != $id) {
                $tree[count($tree)] = $rows[$i];
                QLVBDHCommon::GetTreeNoChildWithWhere(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1, $id, $where);
            }
        }
    }

    static function GetTreeByName($value, $fieldname, $tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level) {
        global $db;      
        
        //$value = '%b%';
        $arrparam = array($value, $parentvalue);
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where  $fieldname like ? and $fieldidparent=?", $arrparam);
        $rows = $r->fetchAll();
        $r->closeCursor();	
	//	echo "SELECT *,$level as LEVEL from $tablename where  $fieldname like ? and $fieldidparent=?";var_dump($arrparam);exit;
        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTree(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }
    //phuongnv thêm
    static function GetTreeByName2($value, $fieldname, $tree, $tablename, $fieldid, $fieldidparent, $parentvalue, $level) {
        global $db;   
       // var_dump($parentvalue);exit;
        //$value = '%b%';
        //$arrparam = array($value, $parentvalue);
        $r = $db->query("SELECT *,$level as LEVEL from $tablename where  $fieldname like ?",$value);
        $rows = $r->fetchAll();
        $r->closeCursor();

        for ($i = 0; $i < $r->rowCount(); $i++) {
            $tree[count($tree)] = $rows[$i];
            QLVBDHCommon::GetTree(&$tree, $tablename, $fieldid, $fieldidparent, $rows[$i][$fieldid], $level + 1);
        }
    }
    
    static function Paginator($numrows, $posrows, $valuepage, $formname, $currentpage) {
        $html = "";
        if (floor($numrows / $valuepage) < $posrows) {
            if ($numrows % $valuepage == 0) {
                $posrows = floor($numrows / $valuepage);
            } else {
                $posrows = floor($numrows / $valuepage) + 1;
            }
        }
        $pageCurrent = $currentpage;
        $beginpage = $currentpage - $posrows + 1;
        if ($beginpage <= 0) {
            $beginpage = 1;
        }
        $endpage = $beginpage + $posrows - 1;

        if ($currentpage != 1) {
            $hasFirst = "
					<div class='button2-right'><div class='start'><a href='#' title='Bắt đầu' onclick='javascript: document." . $formname . ".page.value=1; document." . $formname . ".submit();'>Bắt đầu</a></div></div>
					<div class='button2-right'><div class='prev'><a href='#' title='Trước' onclick='javascript: document." . $formname . ".page.value=" . ($currentpage - 1) . "; document." . $formname . ".submit();'>Trước</a></div></div>
				";
        }

        if ($currentpage < $numrows / $valuepage) {
            $hasNext = "
					<div class='button2-left'><div class='next'><a href='#' title='Tiếp' onclick='javascript: document." . $formname . ".page.value=" . ($currentpage + 1) . "; document." . $formname . ".submit();'>Tiếp</a></div></div>
					<div class='button2-left'><div class='end'><a href='#' title='Cuối' onclick='javascript: document." . $formname . ".page.value=" . (ceil($numrows / $valuepage)) . "; document." . $formname . ".submit();'>Cuối</a></div></div>
				";
        }
        $html .= $hasFirst;
        $html .= "<div class='button2-left'><div class='page'>";
        for ($i = $beginpage; $i <= $endpage; $i++) {
            if ($i == $currentpage) {
                $html .= "<span>" . $i . "</span>";
            } else {
                $html .= "<a href='#' onclick='document." . $formname . ".page.value=" . $i . "; document." . $formname . ".submit();'> " . $i . " </a>";
            }
        }
        $html .= "</div></div>";
        $html .= $hasNext;
        return $html;
    }

    static function PaginatorAjax($numrows, $posrows, $valuepage, $formname, $currentpage, $action, $divid) {
        $html = "";
        if (floor($numrows / $valuepage) < $posrows) {
            if ($numrows % $valuepage == 0) {
                $posrows = floor($numrows / $valuepage);
            } else {
                $posrows = floor($numrows / $valuepage) + 1;
            }
        }
        $pageCurrent = $currentpage;
        $beginpage = $currentpage - $posrows + 1;
        if ($beginpage <= 0) {
            $beginpage = 1;
        }
        $endpage = $beginpage + $posrows - 1;

        if ($currentpage != 1) {
            $hasFirst = "
					<div class='button2-right'><div class='start'><a href='#' title='Bắt đầu' onclick=\"
						document." . $formname . ".page.value=1;
						hscv = new AjaxEngine();
						hscv.loadDivFromUrlAndForm('" . $divid . "','" . $action . "',document." . $formname . ");
					\">Bắt đầu</a></div></div>
					<div class='button2-right'><div class='prev'><a href='#' title='Trước' onclick=\"
						document." . $formname . ".page.value=" . ($currentpage - 1) . ";
						hscv = new AjaxEngine();
						hscv.loadDivFromUrlAndForm('" . $divid . "','" . $action . "',document." . $formname . ");
					\">Trước</a></div></div>
				";
        }

        if ($currentpage < $numrows / $valuepage) {
            $hasNext = "
					<div class='button2-left'><div class='next'><a href='#' title='Tiếp' onclick=\"
						document." . $formname . ".page.value=" . ($currentpage + 1) . ";
						hscv = new AjaxEngine();
						hscv.loadDivFromUrlAndForm('" . $divid . "','" . $action . "',document." . $formname . ");
					\">Tiếp</a></div></div>
					<div class='button2-left'><div class='end'><a href='#' title='Cuối' onclick=\"
						document." . $formname . ".page.value=" . (ceil($numrows / $valuepage)) . ";
						hscv = new AjaxEngine();
						hscv.loadDivFromUrlAndForm('" . $divid . "','" . $action . "',document." . $formname . ");
					\">Cuối</a></div></div>
				";
        }
        $html .= $hasFirst;
        $html .= "<div class='button2-left'><div class='page'>";
        for ($i = $beginpage; $i <= $endpage; $i++) {
            if ($i == $currentpage) {
                $html .= "<span><font color=red>" . $i . "</font></span>";
            } else {
                $html .= "<a href='#' onclick=\"
						document." . $formname . ".page.value=" . $i . ";
						hscv = new AjaxEngine();
						hscv.loadDivFromUrlAndForm('" . $divid . "','" . $action . "',document." . $formname . ");\"> " . $i . " </a>";
            }
        }
        $html .= "</div></div>";
        $html .= $hasNext;
        return $html;
    }

    static function PaginatorWithAction($numrows, $posrows, $valuepage, $formname, $currentpage, $action) {
        $html = "";
        if (floor($numrows / $valuepage) < $posrows) {
            if ($numrows % $valuepage == 0) {
                $posrows = floor($numrows / $valuepage);
            } else {
                $posrows = floor($numrows / $valuepage) + 1;
            }
        }
        $pageCurrent = $currentpage;
        $beginpage = $currentpage - $posrows + 1;
        if ($beginpage <= 0) {
            $beginpage = 1;
        }
        $endpage = $beginpage + $posrows - 1;

        if ($currentpage != 1) {
            $hasFirst = "
					<div class='button2-right'><div class='start'><a href='#' title='Bắt đầu' onclick=\"
						document." . $formname . ".page.value=1;
						document." . $formname . ".action='" . $action . "';
						document." . $formname . ".submit();
					\">Bắt đầu</a></div></div>
					<div class='button2-right'><div class='prev'><a href='#' title='Trước' onclick=\"
						document." . $formname . ".page.value=" . ($currentpage - 1) . ";
						document." . $formname . ".action='" . $action . "';
						document." . $formname . ".submit();
					\">Trước</a></div></div>
				";
        }

        if ($currentpage < $numrows / $valuepage) {
            $hasNext = "
					<div class='button2-left'><div class='next'><a href='#' title='Tiếp' onclick=\"
						document." . $formname . ".page.value=" . ($currentpage + 1) . ";
						document." . $formname . ".action='" . $action . "';
						document." . $formname . ".submit();
					\">Tiếp</a></div></div>
					<div class='button2-left'><div class='end'><a href='#' title='Cuối' onclick=\"
						document." . $formname . ".page.value=" . (ceil($numrows / $valuepage)) . ";
						document." . $formname . ".action='" . $action . "';
						document." . $formname . ".submit();
					\">Cuối</a></div></div>
				";
        }
        $html .= $hasFirst;
        $html .= "<div class='button2-left'><div class='page'>";
        for ($i = $beginpage; $i <= $endpage; $i++) {
            if ($i == $currentpage) {
                $html .= "<span>" . $i . "</span>";
            } else {
                $html .= "<a href='#' onclick=\"
						document." . $formname . ".page.value=" . $i . ";
						document." . $formname . ".action='" . $action . "';
						document." . $formname . ".submit();
					\"> " . $i . " </a>";
            }
        }
        $html .= "</div></div>";
        $html .= $hasNext;
        return $html;
    }

    static function checkChild($data, $pos) {
        $curlevel = $data[$pos]['LEVEL'];
        $curactid = $data[$pos]['ACTID'];
        $pos++;
        if ($pos >= count($data)) {
            if ($curactid == 0
                )return false;
            return true;
        }
        while ($curlevel < $data[$pos]['LEVEL']) {
            if ($data[$pos]['ACTID'] != 0
                )return true;
            if ($pos >= count($data)) {
                if ($curactid == 0
                    )return false;
                return true;
            }
            $pos++;
        }
        if ($curactid == 0
            )return false;
        return true;
    }

	static function buildUL_LI($menuData){
		$result = '';

        for ($pos = 0; $pos < count($menuData); $pos++) {
            if ($menuData[$pos + 1]["LEVEL"] > $menuData[$pos]["LEVEL"]) {
                $html = "
						<li>
							<a href=''><span>" . $menuData[$pos]["NAME"] . "</span></a>
							<ul>
					";
                $result .= $html;
            } else if ($menuData[$pos + 1]["LEVEL"] == $menuData[$pos]["LEVEL"]) {
                $html = "
						<li>
							<a href=''><span>" . $menuData[$pos]["NAME"] . "</span></a>
						</li>
					";
                $result .= $html;
            } else {
                $html = "
						<li>
							<a href=''><span>" . $menuData[$pos]["NAME"] . "</span></a>
						</li>
					";

                if ($pos == count($menuData) - 1) {
                    for ($j = 1; $j <= $menuData[$pos]["LEVEL"] - 1; $j++) {
                        $html .= "</ul></li>";
                    }
                } else {
                    for ($j = 1; $j <= $menuData[$pos]["LEVEL"] - $menuData[$pos + 1]["LEVEL"]; $j++) {
                        $html .= "</ul></li>";
                    }
                }
                $result .= $html;
            }
        }
        return $result;
	}
	
    /**
     * Build menu with <li class="node"><a href="">Menu level 1</a><ul></li> formal;
     *
     * @param  array $menuData
     * @return echo menu
     */
    static function buildMenuUL_LI($menuData) {
        $result = '';
        $temp = array();
        for ($pos = 0; $pos < count($menuData); $pos++) {
            if (QLVBDHCommon::checkChild($menuData, $pos)) {
                $temp[] = $menuData[$pos];
            }
        }
        $menuData = $temp;
        for ($pos = 0; $pos < count($menuData); $pos++) {
			if($menuData[$pos + 1]["LEVEL"] <= $menuData[$pos]["LEVEL"]){
				if($menuData[$pos]["SCRIPT"]!=""){
					$action = "onclick='".$menuData[$pos]["SCRIPT"]."'";
				}else{
				$action = "href='" . ($menuData[$pos]["URL"] == "" ? $menuData[$pos]["URL_ACTION"] : $menuData[$pos]["URL"]) . "'";
				}
			}else{
				$action = "";
			}
            if ($menuData[$pos + 1]["LEVEL"] > $menuData[$pos]["LEVEL"]) {
                $html = "
						<li>
							<a $action><span>" . $menuData[$pos]["NAME"] . "</span></a>
							<ul>
					";
                $result .= $html;
            } else if ($menuData[$pos + 1]["LEVEL"] == $menuData[$pos]["LEVEL"]) {
                $html = "
						<li>
							<a $action><span>" . $menuData[$pos]["NAME"] . "</span></a>
						</li>
					";
                $result .= $html;
            } else {
                $html = "
						<li>
							<a $action><span>" . $menuData[$pos]["NAME"] . "</span></a>
						</li>
					";

                if ($pos == count($menuData) - 1) {
                    for ($j = 1; $j <= $menuData[$pos]["LEVEL"] - 1; $j++) {
                        $html .= "</ul></li>";
                    }
                } else {
                    for ($j = 1; $j <= $menuData[$pos]["LEVEL"] - $menuData[$pos + 1]["LEVEL"]; $j++) {
                        $html .= "</ul></li>";
                    }
                }
                $result .= $html;
            }
        }
        return $result;
    }

    static function calendar($value, $name, $id) {
        return '
				<input type="text" size=10 name="' . $name . '" id="' . $id . '" value="' . $value . '" onblur="getMinDate(this);if(typeof(ChangeDate)==\'function\')eval(\'ChangeDate()\');"><img src="/images/calendar.png" align="absmiddle" onclick="document.getElementById(\'' . $id . '\').focus();var dp_cal_' . $id . ' = null;
					dp_cal_' . $id . '  = new Epoch(\'epoch_popup\',\'popup\',document.getElementById(\'' . $id . '\'),false,' . QLVBDHCommon::getYear() . ',\'d/m\');
					dp_cal_' . $id . '.show();HasEvent=true;"></img>
			';
    }

    static function calendarFull($value, $name, $id) {
        return '
				<input type="text" size=10 name="' . $name . '" id="' . $id . '" value="' . $value . '" onblur="getFullDate(this);if(typeof(ChangeDate)==\'function\')eval(\'ChangeDate()\');"><img id="I'.$id.'" src="/images/calendar.png" align="absmiddle" onclick="document.getElementById(\'' . $id . '\').focus();var dp_cal_' . $id . ' = null;
					dp_cal_' . $id . '  = new Epoch(\'epoch_popup\',\'popup\',document.getElementById(\'' . $id . '\'),false,' . QLVBDHCommon::getYear() . ',\'d/m/Y\');
					dp_cal_' . $id . '.show();HasEvent=true;"></img>
			';
    }

    static function calendarFullWithNoEvent($value, $name, $id) {
        return '
				<input type="text" size=10 name="' . $name . '" id="' . $id . '" value="' . $value . '" onblur="getFullDate(this);"><img src="/images/calendar.png" onclick="document.getElementById(\'' . $id . '\').focus();var dp_cal_' . $id . ' = null;
					dp_cal_' . $id . '  = new Epoch(\'epoch_popup\',\'popup\',document.getElementById(\'' . $id . '\'),false,' . QLVBDHCommon::getYear() . ',\'d/m/Y\');
					dp_cal_' . $id . '.show();HasEvent=false;"></img>
			';
    }
	
	static function calendarFullWithEvent($value, $name, $id, $method) {
        return '
				<input type="text" size=10 name="' . $name . '" id="' . $id . '" value="' . $value . '" onchange="' . $method . '"onblur="getFullDate(this);if(typeof(ChangeDate)==\'function\')eval(\'ChangeDate()\');"><img id="I'.$id.'" src="/images/calendar.png" align="absmiddle" onclick="document.getElementById(\'' . $id . '\').focus();var dp_cal_' . $id . ' = null;
					dp_cal_' . $id . '  = new Epoch(\'epoch_popup\',\'popup\',document.getElementById(\'' . $id . '\'),false,' . QLVBDHCommon::getYear() . ',\'d/m/Y\');
					dp_cal_' . $id . '.show();HasEvent=true;"></img>
			';
    }

    /**
     * Get top error message
     *
     * @return String
     */
    public function getTopErrorMessage(Zend_Form_Element $element) {
        $getMessage = $element->getMessages();
        $getError = $element->getErrors();

        if ($getMessage != null && $getError != null) {
            return $getMessage[$getError[0]];
        }
        else
            return '';
    }

    function returnMIMEType($filename) {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        switch (strtolower($fileSuffix[1])) {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/" . strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

            case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";

            case "mov" :
                return "video/quicktime";

            case "zip" :
                return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
                if (function_exists("mime_content_type")) {
                    $fileSuffix = mime_content_type($filename);
                }

                return "unknown/" . trim($fileSuffix[0], ".");
        }
    }

    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeSelectDepartmentUser($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE
					U.ACTIVE=1
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";

        $html.="<select name=$UName id=$UName></select>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeSelectDepartmentMultiUser($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<div style='float:left'><select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select></div>";

        $html.="<div style='float:left'><select name=$UName id=$UName multiple size=5 ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['USERNAME'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    static function writeSelectDepartmentMultiUserH($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<div style='float:left'><select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select><br><select name=$UName id=$UName multiple size=5 ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['USERNAME'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    static function writeSelectDepartmentMultiUserHWithSelDep($DName, $UName, $sel, $func_insert='InsertIntoArr') {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE
					U.ACTIVE=1
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<div style='float:left'><select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            if ($department[$i]["ID_DEP"] == $sel) {
                $html.="        <option value=" . $department[$i]["ID_DEP"] . " selected>" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
            } else {
                $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
            }
        }
        $html.="        </select><br><select name=$UName id=$UName multiple size=5 ondblclick=\"if(typeof($func_insert)=='function')eval('$func_insert()');\"></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['USERNAME'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }
	
	static function writeSelectDepartmentMultiUserHWithGroupSelDep($DName, $UName, $sel, $func_insert) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
		
		$r = $db->query("SELECT ID_G,NAME FROM QTHT_GROUPS");
		$group = $r->fetchAll();
		$r->closeCursor();
		
        $r = $db->query("
				SELECT
					G.ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME,U.ORDERS,E.LASTNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
				UNION ALL
				SELECT
					-1 as ID_G, DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME,U.ORDERS,E.LASTNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				UNION ALL
				SELECT
					G.ID_G, -1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME,U.ORDERS,E.LASTNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
				ORDER BY
					ORDERS, LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();
		$html = "";
        $html .= "<div style='float:left'>";
		$html .= "<select name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user, arr_user);'>";
		$html .= "	<option value=-1>--Chọn nhóm--</option>";
		for($i=0;$i<count($group);$i++){
		$html.="    <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
		}
		$html .= "</select>";
		$html .= "<div></div>";
		$html .= "<select name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        $html.="        	<option value=-1>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            if ($department[$i]["ID_DEP"] == $sel) {
                $html.="        <option value=" . $department[$i]["ID_DEP"] . " selected>" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
            } else {
                $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
            }
        }
        $html.="        </select><br><select name=$UName id=$UName multiple size=5 ondblclick=\"if(typeof($func_insert)=='function')eval('$func_insert()');\"></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_G'] . "','" . $user[$i]['ID_DEP'] . "','" . $user[$i]['USERNAME'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    static function writeSelectDepartmentUserWithSelreport($DName, $UName) {
        global $db;
        $userauth = Zend_Registry::get('auth')->getIdentity();
        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE
					U.ACTIVE=1
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();
        $m = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME AS USERNAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					WHERE u.ID_U= ? and u.ACTIVE=1
							", array($userauth->ID_U));
        $id_dep = $m->fetch();
        $m->closeCursor();
        $html.="    	<div style='float:left'><select name=$DName id=$DName onchange='FillComboByComboExp1(this,document.getElementById(\"$UName\"),$arr_user,arr_user);'>";
        if ($userauth->DEPLEADER == 1) {
            $html.=" <option value=0>--Chọn phòng--</option>";
            for ($i = 0; $i < count($department); $i++) {
                $html.="<option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
            }
        } else {
            for ($i = 0; $i < count($department); $i++) {
                if ($id_dep["ID_DEP"] == $department[$i]["ID_DEP"]) {
                    $html.=" <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
                }
            }
        }
        $html.="        </select></div>";

        $html.="<div style='float:left'><select name=$UName" . "[]" . " id=$UName multiple size=5 ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select></div>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['USERNAME'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp1(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";
        return $html;
    }

    static function getDepList($data, $parent) {
        $result = array();
        foreach ($data as $item) {
            $result[] = $item['ID_DEP'];
        }
        $result[] = $parent;
        return $result;
    }

    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeSelectDepartmentUserWithSel($DName, $UName, $sel, $depparent) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", $depparent, 1);
        $iddep = implode(",", QLVBDHCommon::getDepList($department, $depparent));
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				" . ($depparent > 1 ? "WHERE
					DEP.ID_DEP in ($iddep)" : "") . "
				WHERE
					U.ACTIVE=1
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<select name=$DName id=$DName onchange='FillComboByComboWithSel(this,document.getElementById(\"$UName\"),$arr_user,\"$sel\");'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";

        $html.="<select name=$UName id=$UName>
				
			</select>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        $html.=$arr_user . "[" . "0" . "] = new Array('" . "" . "','" . "0" . "','" . "-- Chọn người --" . "');";
        for ($i = 1; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboWithSel(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,\"$sel\");";
        $html.="</script>";

        return $html;
    }

    static function getFullUserByDepId($return, $iddep) {
        global $db;

        $dep = array();
        $dep[] = 0;
        QLVBDHCommon::getAllDepChild(&$dep, $iddep);
        //lấy danh sách user của phòng
        $r = $db->query("
				SELECT
					$iddep as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE
					DEP.ID_DEP in ($iddep," . implode(",", $dep) . ")
					AND U.ACTIVE=1
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        foreach ($user as $item) {
            $return[] = $item;
        }

        //Lấy các phòng con
        $r = $db->query("
				SELECT
					ID_DEP
				FROM				
					QTHT_DEPARTMENTS
				WHERE
					ID_DEP_PARENT in ($iddep)
			");
        $dep = $r->fetchAll();
        foreach ($dep as $item) {
            //QLVBDHCommon::getFullUserByDepId(&$return,$iddepparent,$item['ID_DEP']);
            QLVBDHCommon::getFullUserByDepId(&$return, $item['ID_DEP']);
        }
    }

    static function getFullUserByDepIdWithNoGroup($return, $iddep) {
        global $db;

        $dep = array();
        $dep[] = 0;
        QLVBDHCommon::getAllDepChild(&$dep, $iddep);
        //lấy danh sách user của phòng
        $r = $db->query("
				SELECT
					-1 as ID_G,$iddep as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME,U.USERNAME, cd.`UU_TIEN`
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE
					DEP.ID_DEP in ($iddep," . implode(",", $dep) . ")
					AND U.ACTIVE=1
				ORDER BY
					cd.UU_TIEN, E.LASTNAME
			");
        $user = $r->fetchAll();
        foreach ($user as $item) {
            $return[] = $item;
        }

        //Lấy các phòng con
        $r = $db->query("
				SELECT
					ID_DEP
				FROM				
					QTHT_DEPARTMENTS
				WHERE
					ID_DEP_PARENT in ($iddep)
			");
        $dep = $r->fetchAll();
        foreach ($dep as $item) {
            //QLVBDHCommon::getFullUserByDepId(&$return,$iddepparent,$item['ID_DEP']);
            QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$return, $item['ID_DEP']);
        }
    }

    static function getAllDepChild($return, $iddep) {
        global $db;
        $r = $db->query("
				SELECT
					ID_DEP
				FROM				
					QTHT_DEPARTMENTS
				WHERE
					ID_DEP_PARENT in ($iddep)
			");
        $dep = $r->fetchAll();
        foreach ($dep as $item) {
            $return[] = $item["ID_DEP"];
            QLVBDHCommon::getAllDepChild(&$return, $item["ID_DEP"]);
        }
    }

    static function writeSelectDepartmentUserWithSelAndAction($DName, $UName, $sel, $depparent, $action) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", $depparent, 1);
        $iddep = implode(",", QLVBDHCommon::getDepList($department, $depparent));
        /*
          $r = $db->query("
          SELECT
          DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
          FROM
          QTHT_USERS U
          INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
          INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
          ".($depparent>1?"WHERE
          DEP.ID_DEP in ($iddep)":"")."
          ORDER BY
          U.ORDERS, E.LASTNAME
          ");
          $user = $r->fetchAll();
          $r->closeCursor();
         */
        $user = Array();
        QLVBDHCommon::getFullUserByDepId(&$user, $depparent, $depparent);

        $html.="    	<select name=$DName id=$DName onchange='FillComboByComboWithSel(this,document.getElementById(\"$UName\"),$arr_user,$sel);'>";
        $html.="        	<option value=1>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";

        $html.="<select name=$UName id=$UName onchange='$action'></select>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboWithSel(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,\"$sel\");";
        $html.="</script>";

        return $html;
    }

    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeMultiSelectDepartmentUser($DName, $UName, $includesmsemail=false) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					ID_G,NAME
				FROM				
					QTHT_GROUPS			
			");
        $group = $r->fetchAll();
        $r->closeCursor();
        $r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				WHERE U.ACTIVE=1

				ORDER BY ORDERS, LASTNAME, NAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user, 1);

        for ($i = 0; $i < count($user); $i++) {
            try {
                if ($user[$i]['PHONE']) {
                    if ($user[$i]['EMAIL']) {
                        $user[$i]['SMSEMAIL'] = 3;
                    } else {
                        $user[$i]['SMSEMAIL'] = 1;
                    }
                } else {
                    if ($user[$i]['EMAIL']) {
                        $user[$i]['SMSEMAIL'] = 2;
                    } else {
                        $user[$i]['SMSEMAIL'] = 0;
                    }
                }
            } catch (Exception $ex) {
                //echo $ex->__tostring();
                $user[$i]['SMSEMAIL'] = 0;
            }
        }

        //check idu
        /* $actsmsid = ResourceUserModel::getActionByUrl('qtht','danhmucnguoidung','sms');
          $actemailid = ResourceUserModel::getActionByUrl('qtht','danhmucnguoidung','email');
          for($i=0;$i<count($user);$i++){
          try{
          if(ResourceUserModel::isAcionAlowed($user[$i]['USERNAME'],$actsmsid[0])){
          if(ResourceUserModel::isAcionAlowed($user[$i]['USERNAME'],$actemailid[0])){
          $user[$i]['SMSEMAIL'] = 3;
          }else{
          $user[$i]['SMSEMAIL'] = 1;
          }
          }else{
          if(ResourceUserModel::isAcionAlowed($user[$i]['USERNAME'],$actemailid[0])){
          $user[$i]['SMSEMAIL'] = 2;
          }else{
          $user[$i]['SMSEMAIL'] = 0;
          }
          }
          }catch(Exception $ex){
          //echo $ex->__tostring();
          $user[$i]['SMSEMAIL'] = 0;
          }
          } */
		$html = "";
        $html.="
				<table>
					<tr><td style='width:50px;'><b>Nhóm</b></td><td>
			";
        $html.="<select name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
        $html.="        	<option value=-1>-- Tất cả --</option>";
        for ($i = 0; $i < count($group); $i++) {
            $html.="        <option value=" . $group[$i]["ID_G"] . ">" . $group[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";
        $html.="</td></tr>
					<tr><td style='width:50px;'><b>Phòng</b></td><td>
			";
        $html.="<select name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
        $html.="        	<option value=-1>-- Tất cả --</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";
        $html.="</td></tr>
					<tr><td style='width:50px;'><b>Người</b></td><td>
			";
        $html.="<select name=$UName id=$UName size=10 multiple ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";
        $html.='
			
			</td>
			</tr>
				</table>
			';
        $html .= "<input type='hidden' name='' id='Hidden_G".$DName."' value='' />";
        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        $html.="	var $arr_user" . "smsemail" . " = new Array();";
        for ($i = 0; $i < count($user); $i++) {
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_G'] . "','" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
            $html.=$arr_user . "smsemail[" . $i . "] = new Array('" . $user[$i]['SMSEMAIL'] . "');";
        }

        $html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        
        $html.="</script>";

        return $html;
    }

    static function writeMultiSelectDepartmentUserOnCorporation($DName, $UName, $includesmsemail=false) {
        global $db;
        global $auth;
        $user = $auth->getIdentity();

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTreeWithWhere(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1, "ID_CQ=" . (int) $user->ID_CQ);
        $r = $db->query("
				SELECT
					ID_G,NAME
				FROM				
					QTHT_GROUPS			
			");
        $group = $r->fetchAll();
        $r->closeCursor();
        $r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
				WHERE U.ACTIVE=1
				
				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE 
					DEP.ID_CQ = ?
					AND U.ACTIVE=1

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE 
					DEP.ID_CQ = ?
					AND U.ACTIVE=1
				ORDER BY ORDERS, LASTNAME, NAME
			", array((int) $user->ID_CQ, (int) $user->ID_CQ));
        $user = $r->fetchAll();
        $r->closeCursor();

        QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user, 1);

        for ($i = 0; $i < count($user); $i++) {
            try {
                if ($user[$i]['PHONE']) {
                    if ($user[$i]['EMAIL']) {
                        $user[$i]['SMSEMAIL'] = 3;
                    } else {
                        $user[$i]['SMSEMAIL'] = 1;
                    }
                } else {
                    if ($user[$i]['EMAIL']) {
                        $user[$i]['SMSEMAIL'] = 2;
                    } else {
                        $user[$i]['SMSEMAIL'] = 0;
                    }
                }
            } catch (Exception $ex) {
                //echo $ex->__tostring();
                $user[$i]['SMSEMAIL'] = 0;
            }
        }



        $html.="
				<table>
					<tr><td style='width:130px;'><b>Nhóm</b></td><td>
			";
        $html.="<select name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
        $html.="        	<option value=-1>-- Tất cả --</option>";
        for ($i = 0; $i < count($group); $i++) {
            $html.="        <option value=" . $group[$i]["ID_G"] . ">" . $group[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";
        $html.="</td></tr>
					<tr><td style='width:130px;'><b>Phòng</b></td><td>
			";
        $html.="<select name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
        $html.="        	<option value=-1>-- Tất cả --</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";
        $html.="</td></tr>
					<tr><td style='width:130px;'><b>Người</b></td><td>
			";
        $html.="<select name=$UName id=$UName size=5 multiple ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";
        $html.='
			
			</td>
			</tr>
				</table>
			';

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        $html.="	var $arr_user" . "smsemail" . " = new Array();";
        for ($i = 0; $i < count($user); $i++) {
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_G'] . "','" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
            $html.=$arr_user . "smsemail[" . $i . "] = new Array('" . $user[$i]['SMSEMAIL'] . "');";
        }

        $html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }

    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeSelectDepartment($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<select name=$DName id=$DName onchange='FillComboByCombo(this,document.getElementById(\"$UName\"),$arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]["LEVEL"]) . ($department[$i]["LEVEL"] > 1 ? "-> " : "") . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="</script>";
        return $html;
    }
    /**
     * Tạo select user từ select department
     *
     * @param int $DName tên combobox chứa danh sách department
     * @param int $UName tên combobox chứa danh sách user thuộc department trên
     * @return html code
     */
    static function writeSelectDepartmentMultiple($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				ORDER BY
					U.ORDERS, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="<table><tr><td style='width:50px;'><b>Phòng</b></td><td>
        <select  multiple='' size='5' name=$DName id=$DName onchange='FillComboByCombo(this,document.getElementById(\"$UName\"),$arr_user);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]["LEVEL"]) . ($department[$i]["LEVEL"] > 1 ? "-> " : "") . $department[$i]["NAME"] . "</option>";
        }
        $html.="</select>
                </td></tr></table>
        ";
        $html .= "<input type='hidden' name='' id='Hidden_G".$DName."' value='' />";
        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="</script>";
        return $html;
    }

    static function TabHscv($num, $loaihscv) {
        global $db;
        $sql = "
				SELECT
					c.ALIAS
				FROM
					HSCV_LOAIHOSOCONGVIEC l
					INNER JOIN WF_PROCESSES p on p.ALIAS = l.MASOQUYTRINH
					INNER JOIN WF_CLASSES c on c.ID_C = p.ID_C
				WHERE
					l.ID_LOAIHSCV = ?
			";
        $r = $db->query($sql, $loaihscv)->fetch();

        if ($r['ALIAS'] == "VBD"
            )$loaihscv = 1;
        if ($r['ALIAS'] == "VBSOANTHAO"
            )$loaihscv = 2;
        if ($r['ALIAS'] == "MOTCUA"
            )$loaihscv = 3;
        if ($loaihscv >= 3
            )$loaihscv = 3;
        $config = Zend_Registry::get('config');
        eval('$tabhscv = $config->HSCV_' . $loaihscv . ';');
        $tabhscv = explode(",", $tabhscv);
        return $tabhscv[$num - 1];
    }

    static function doDateStandard2Viet($standar) {
        //return string time vietnamese (d/m/y h:m:s)
        $date_o = strtotime($standar);
        return date('h', $date_o) ."h". date('m', $date_o) . '\' ' . date('d/m/Y', $date_o);
    }
	static function doDateStandard2VietSimple($standar) {
        //return string time vietnamese (d/m/y h:m:s)
        $date_o = strtotime($standar);
        return date('d/m/Y', $date_o) ." ". date('h', $date_o) . ":" . date('m', $date_o);
    }
    static function doDateViet2Standard($vietdate) {
        //return standard time (y-m-d h:m:s)
        $ngay_tra = trim($vietdate);
        $arr = explode('/', $ngay_tra);
        $ngay_tra = date('Y-m-d', mktime(null, null, null, $arr[1], $arr[0], $arr[2]));
        return $ngay_tra;
    }

    static function getYear() {
        $year = new Zend_Session_Namespace('year');
        if (!isset($year->year)) {
            $d = getdate();
            $db = Zend_Db_Table::getDefaultAdapter();
            $qr = $db->query("select YEAR from qtht_year order by YEAR DESC");
            $re = $qr->fetch();
            if ($re["YEAR"] && $d['year'] > $re["YEAR"]) {

                $year->year = $re["YEAR"];
                //echo $re["YEAR"];
            } else {
                $year->year = $d['year'];
            }
        }
        return $year->year;
    }

    static function MysqlDateToVnDate($mysqldate) {
        if ($mysqldate != "") {
            $d = explode(" ", $mysqldate);
            $d = explode("-", $d[0]);
            
            $mouth = (int)$d[1];
            if($mouth < 3){
            	$mouth = 0 . $mouth;
            }
            return  $d[2] . "/" .  $mouth . "/" . (int) $d[0];
        }
    }
	static function TrimSecondMysqlDate($mysqldate) {
        if ($mysqldate != "") {
            return date("Y-m-d H:i",strtotime($mysqldate));
        }
		return "";
    }

    static function MysqlDateToVnDateNoneZero($mysqldate) {
        if ($mysqldate != "") {
            $d = explode(" ", $mysqldate);
            $d = explode("-", $d[0]);
            
            $mouth = (int)$d[1];
            if($mouth < 3){
            	$mouth = 0 . $mouth;
            }
            return  $d[2] . "/" .  $mouth . "/" . (int) $d[0];
        }
    }

    /**
     * Đếm ngày bị trễ từ ngày đến ngày hiện tại
     * trả về: gio bị trễ
     * Ngày làm 8 giờ
     * Không tính thứ 7 chủ nhật
     * @param $ngay int
     * @param $arrngaynghilam lấy từ database (UTC date)
     */
    static function countdate($ngay, $arrngaynghilam) {
        $tre = 0;
        $curdate = time();
        if ($ngay > $curdate) {
            return 0;
        }
        //tao nagy bat dau
        $begindate = $ngay;
        //$begindate = strtotime($begindate['year']."-".$begindate['mon']."-".$begindate['mday']." 00:00:00");
        $enddate = $curdate;
        //$enddate = strtotime($enddate['year']."-".$enddate['mon']."-".$enddate['mday']." 00:00:00");
        $isbegin = true;
        while (true) {
            //chekc ngay nghi
            if (BussinessDateModel::IsNonWorkingDate(getdate($begindate))) {
                
            } else {
                $hour = date("H", $begindate);
                if ($hour >= 8 && $hour < 12) {
                    $tre++;
                } else if ($hour >= 13 && $hour < 17) {
                    $tre++;
                }
            }
            $begindate += 3600;
            if ($begindate > $enddate
                )break;
        }
        return $tre;
    }

    static function countdatehxl($ngay, $arrngaynghilam) {
        $han = 0;
        $curdate = time();
        if ($ngay < $curdate) {
            return 0;
        }
        //tao nagy bat dau
        $begindate = $curdate;
        //$begindate = strtotime($begindate['year']."-".$begindate['mon']."-".$begindate['mday']." 00:00:00");
        $enddate = $ngay;
        //$enddate = strtotime($enddate['year']."-".$enddate['mon']."-".$enddate['mday']." 00:00:00");
        $isbegin = true;
        while (true) {
            //chekc ngay nghi
            if (BussinessDateModel::IsNonWorkingDate(getdate($begindate))) {
                
            } else {
                $hour = date("H", $begindate);
                if ($hour >= 8 && $hour < 12) {
                    $han++;
                } else if ($hour >= 13 && $hour < 17) {
                    $han++;
                }
            }
            $begindate += 3600;
            if ($begindate > $enddate
                )break;
        }
        return $han;
    }

    static function countdatehxlngay($ngay, $arrngaynghilam) {
        $han = 0;
        $curdate = time();
        if ($ngay < $curdate) {
            return 0;
        }
        //tao nagy bat dau
        $begindate = $curdate;
        //$begindate = strtotime($begindate['year']."-".$begindate['mon']."-".$begindate['mday']." 00:00:00");
        $enddate = $ngay;
        //$enddate = strtotime($enddate['year']."-".$enddate['mon']."-".$enddate['mday']." 00:00:00");
        $isbegin = true;
        while (true) {
            //chekc ngay nghi
            if (BussinessDateModel::IsNonWorkingDate(getdate($begindate))) {
                
            } else {
                $hour = date("H", $begindate);
                if ($hour >= 8 && $hour < 12) {
                    $han++;
                } else if ($hour >= 13 && $hour < 17) {
                    $han++;
                }
            }
            $begindate += 3600;
            if ($begindate > $enddate
                )break;
        }
        return $han = $han % 8 + 1;
    }

    /**
     * Tra ve so gio bi tre (1 ngay co 8 gio)
     */
    static function getTreHan($ngay, $hanxuly) {
        if ($hanxuly == 0 || $hanxuly==999
            )return 0;
        $ngay = strtotime($ngay);
        $freedate = new Zend_Session_Namespace('freedate');
        $free = $freedate->free;
        $delay = QLVBDHCommon::countdate($ngay, $free);
        return ($delay) - ($hanxuly * 8);
    }

    static function getFreeDate() {
        global $db;
        $r = $db->query("SELECT * FROM GEN_LICHNGHILAM");
        $data = $r->fetchAll();
        $result = array();
        foreach ($data as $row) {
            if ($row['ISRANGE'] == 1) {
                $result[] = array(strtotime($row['TUNGAY'] . " 00:00:01"), strtotime($row['DENNGAY'] . " 23:59:59"));
            }
        }
        return $result;
    }

    static function addDateAll($date, $value) {
        $inc = 0;
        $value = $value;
        while (true) {
            $date += 3600 * 24;
            $nocount = 1;
            if (BussinessDateModel::IsNonWorkingDate(getdate($date))) {
                $nocount = 0;
            }
            $inc += $nocount;

            if ($inc >= $value
                )break;
        }
        return $date;
    }

    static function addDate($date, $value) {
		if($value==999){
			return $date + 3600*24*365*3;
		}
        $inc = 0;
        //Đếm ngày từ ngày đến ngày hiện tại
        $value = $value * 8;
        while (true) {
            $date += 3600;
            $nocount = 1;
            if (BussinessDateModel::IsNonWorkingDate(getdate($date))) {
                $nocount = 0;
            }
            $hour = date("H", $date);
            if ($hour >= 8 && $hour < 12) {
                $inc += $nocount;
            } else if ($hour >= 13 && $hour < 17) {
                $inc += $nocount;
            }
            if ($inc >= $value
                )break;
        }
        return $date;
    }

    static function addDategio($date, $value) {
        $inc = 0;
        //Đếm ngày từ ngày đến ngày hiện tại
        while (true) {
            $date += 3600;
            $nocount = 1;
            if (BussinessDateModel::IsNonWorkingDate(getdate($date))) {
                $nocount = 0;
            }
            $hour = date("H", $date);
            if ($hour >= 8 && $hour < 12) {
                $inc += $nocount;
            } else if ($hour >= 13 && $hour < 17) {
                $inc += $nocount;
            }
            if ($inc >= $value
                )break;
        }
        return $date;
    }

    static function trehantostr($tre, $ngay, $hanxuly) {
        if ($tre > 0) {
            $r = " <font color=red><i>Trễ " . (floor($tre / 8) > 0 ? floor($tre / 8) . " ngày " : "") . ($tre % 8 > 0 ? ($tre % 8) . " giờ" : "") . "</i></font>";
        } else if ($tre < 0) {
            /*
              $ngay = strtotime($ngay);
              $ngay = QLVBDHCommon::addDate($ngay,$hanxuly);
              $ngay = date("d/m/Y H:i:s",$ngay);
              $r = " <font color=blue><i>(Gần tới hạn - ".$ngay.")</i></font>";
             */
            $tre = $tre * -1;
            $r = " <font color=blue><i>Còn " . (floor($tre / 8) > 0 ? floor($tre / 8) . " ngày " : "") . ($tre % 8 > 0 ? ($tre % 8) . " giờ" : "") . "</i></font>";
        }
        return $r;
    }

    static function trehantostrwithusername($tre, $ngay, $hanxuly, $username) {
        if ($tre > 0) {
            $r = " <font color=red><i>(Trễ " . (floor($tre / 8) > 0 ? floor($tre / 8) . " ngày " : "") . ($tre % 8 > 0 ? ($tre % 8) . " giờ" : "") . " - " . $username . ")</i></font>";
        } else if ($tre > -8) {
            $ngay = strtotime($ngay);
            $ngay = QLVBDHCommon::addDate($ngay, $hanxuly);
            $ngay = date("d/m/Y H:i:s", $ngay);
            $r = " <font color=blue><i>(Gần tới hạn - " . $ngay . " - " . $username . ")</i></font>";
        }
        return $r;
    }

    static function SendMessage($usend, $ureceive, $content, $link) {
        global $db;
        global $url;
        $db->insert(QLVBDHCommon::Table("GEN_MESSAGE"), array("ID_U_SEND" => $usend, "ID_U_RECEIVE" => $ureceive, "NOIDUNG" => $content, "LINK" => $url . $link, "DATE_SEND" => date("Y-m-d H:i:s")));
    }

    static function UpdateIndex($tablename, $data, $ido) {
        global $db;
        $tablefulltext = $tablename . "_FULLTEXT" . "_" . QLVBDHCommon::getYear();
        $tablefulltextdata = $tablename . "_FULLTEXT" . "_DATA_" . QLVBDHCommon::getYear();
        $keywords = preg_split("/[\s,.!?]*\\\"([^\\\"]+)\\\"[\s,.!?]*|[\s,.!?]+/", $data, 0, PREG_SPLIT_DELIM_CAPTURE);
        $db->delete($tablefulltextdata, "ID_O=" . $ido);
        foreach ($keywords as $key) {
            if ($key != "") {
                //check xem key word co chua
                $sql = "select * from $tablefulltext where DATA = ?";
                $r = $db->query($sql, $key);
                if ($r->rowCount() > 0) {
                    $fulltext = $r->fetch();
                    $db->insert($tablefulltextdata, array("ID_FT" => $fulltext['ID_FT'], "ID_O" => $ido));
                } else {
                    $db->insert($tablefulltext, array("DATA" => $key));
                    $db->insert($tablefulltextdata, array("ID_FT" => $db->lastInsertId($tablefulltext), "ID_O" => $ido));
                }
            }
        }
    }

    static function MakeInString($data) {
        $r = "";
        $keywords = preg_split("/[\s,.!?]*\\\"([^\\\"]+)\\\"[\s,.!?]*|[\s,.!?]+/", $data, 0, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($keywords as $key) {
            if ($key != "") {
                if ($key == "") {
                    $r .= "'" . $key . "'";
                } else {
                    $r .= ",'" . $key . "'";
                }
            }
        }
        return $r;
    }

    static function get_string_between($string, $start, $end) {
        $string = " " . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return "";
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    static function InsertHSMCService($masohoso, $tentochuccanhan, $tenhoso, $trangthai, $ghichu, $dienthoai, $barcode) {
        global $db;
        $auth = Zend_Registry::get('auth');
        $user = $auth->getIdentity(); //lay thong tin cua user trong session
        $r = $db->query("SELECT * from QTHT_DEPARTMENTS where ID_DEP=" . $user->ID_DEP);
        $r = $r->fetch();
        $u = $db->query("SELECT * from MOTCUA_HOSO_" . QLVBDHCommon::getYear() . " where MAHOSO=" . $masohoso);
        $u = $u->fetch();
        //$x = $db->query("SELECT * from MOTCUA_PHUONG where ID_PHUONG=".$u['PHUONG']);
        //$x = $x->fetch();
        $db->insert("SERVICES_MOTCUA_TRACUU", array("MASOHOSO" => $masohoso, "TENTOCHUCCANHAN" => $tentochuccanhan, "TENHOSO" => $tenhoso, "TRANGTHAI" => $trangthai, "GHICHU" => $ghichu, "DIENTHOAI" => $dienthoai, "BARCODE" => substr(md5($masohoso), 0, 6), "PHONG" => $r['NAME'], "NHANLAI_NGAY" => $u['NHANLAI_NGAY']));
    }

    function createTextHanXuLy($value) {
        if ($value < 1 && $value > 0) {
            $value = $value * 8;
            return "" . round($value, 1) . " giờ";
        } else if ($value >= 1) {
            return "" . round($value, 1) . " ngày";
        } else {
            return "";
        }
    }

    function createInputHanxuly($id, $name, $value, $onchange) {
        $type = $value < 1 ? 8 : 1;
        if ($type == 8) {
            $value = round($value * 8, 1);
        } else {
            $value = round($value, 1);
        }
        $html = "";
        $html .= "<input id='" . $id . "' type=text onkeypress='return isNumberKey(event)' name='temp_" . $name . "' size=3 maxlength=3 value='" . $value . "' onchange='document.getElementById(\"real_" . $id . "\").value=this.value/document.frm.type_real_" . $id . ".value;" . $onchange . ";'>";
        $html .= "<input style='display:none' type=text id='real_" . $id . "' name='" . $name . "' value='" . ($value / $type) . "'>";
        $html .= "<input " . ($type == 1 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_1_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=1;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";\" value=1>ngày ";
        $html .= "<input " . ($type == 8 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_8_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=8;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";\" value=8>giờ";
        $html .= "<input style='display:none' type=text id='type_real_" . $id . "' name='type_real_" . $id . "' value='" . $type . "'>";
		$html .="<br><input type=checkbox value=1 name=is_important id=is_important><b>Quan trọng</b>";
        return $html;
    }

	function createInputHanxulyWithChooseDate($id, $name, $value, $onchange="") {
        $type = $value < 1 ? 8 : 1;
        if ($type == 8) {
            $value = round($value * 8, 1);
        } else {
            $value = round($value, 1);
        }
        $html = "";
		$html .= QLVBDHCommon::calendarFullWithEvent("", "hanxuly_date", "hanxuly_date", "ChangeDate");
        $html .= "<input id='" . $id . "' type=text onkeypress='return isNumberKey(event)' name='temp_" . $name . "' size=3 maxlength=3 value='" . $value . "' onchange='document.getElementById(\"real_" . $id . "\").value=this.value/document.frm.type_real_" . $id . ".value;" . $onchange . ";UpdateHanXuLy(0)'>";
        $html .= "<input style='display:none' type=text id='real_" . $id . "' name='" . $name . "' value='" . ($value / $type) . "'>";
        $html .= "<input " . ($type == 1 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_1_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=1;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";UpdateHanXuLy(0)\" value=1>ngày ";
        $html .= "<input " . ($type == 8 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_8_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=8;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";UpdateHanXuLy(0)\" value=8>giờ";
        $html .= "<input style='display:none' type=text id='type_real_" . $id . "' name='type_real_" . $id . "' value='" . $type . "'>";
		$html .="<br><input type=checkbox value=1 name=is_important id=is_important><b>Quan trọng</b>";
        return $html;
    }
    function createInputHanxulyWithChooseDate2($id, $name, $value, $onchange="") {
        $type = $value < 1 ? 8 : 1;
        if ($type == 8) {
            $value = round($value * 8, 1);
        } else {
            $value = round($value, 1);
        }
        $html = "";
		$html .= QLVBDHCommon::calendarFullWithEvent("", "hanxuly_date", "hanxuly_date", "ChangeDate");
        $html .= "<input id='" . $id . "' type=text onkeypress='return isNumberKey(event)' name='temp_" . $name . "' size=3 maxlength=3 value='" . $value . "' onchange='document.getElementById(\"real_" . $id . "\").value=this.value/document.frm.type_real_" . $id . ".value;" . $onchange . ";UpdateHanXuLy(0)'>";
        $html .= "<input style='display:none' type=text id='real_" . $id . "' name='" . $name . "' value='" . ($value / $type) . "'>";
        $html .= "<input " . ($type == 1 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_1_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=1;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";UpdateHanXuLy(0)\" value=1>ngày ";
        $html .= "<input " . ($type == 8 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_8_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=8;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . ";UpdateHanXuLy(0)\" value=8>giờ";
        $html .= "<input style='display:none' type=text id='type_real_" . $id . "' name='type_real_" . $id . "' value='" . $type . "'>";
        return $html;
    }

    static function getDirThuMucTapHSCV($return, $idthumuc) {
        global $db;
        if ($idthumuc > 0) {
            $r = $db->query("
                SELECT
                        ID_THUMUC_PARENT,NAME
                FROM
                        hscvdt_thumuc_taphoso
                WHERE
                        ID_THUMUC = $idthumuc
				");
            $dep = $r->fetchAll();
            foreach ($dep as $item) {
                if ($item["ID_THUMUC_PARENT"] != null) {
                    $return[] = $item["NAME"];
                    QLVBDHCommon::getDirThuMucTapHSCV(&$return, (int) $item["ID_THUMUC_PARENT"]);
                }
            }
        }
    }

	//hungph-->selec user 
            static function writeSingleSelectUser1($DName,$UName,$includesmsemail=false){
			global $db;

			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}

			$html.="";
			$html.="<select style='width:200px;display:none' name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.=" Phòng
			";
			$html.="<select style='width:200px;' name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn phòng--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.=" Ông/Bà
			";
			$html.="<select style='width:200px;' name=$UName id=$UName size=1 ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";

			return $html;
	    }

            static function writeSingleSelectUser2($DName,$UName,$includesmsemail=false){
			global $db;

			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}

			$html.="";
			$html.="<select  style='width:200px;display:none' name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="
			";
			$html.="<select style='width:200px;' name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn phòng--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.=" Ông/Bà
			";
			$html.="<select style='width:200px;' name=$UName id=$UName size=1
                        '<option value=0>--Chọn--</option>'
                        ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";		

			return $html;
	    }
		
		
		 static function writeSingleSelectUser3($DName,$UName,$includesmsemail=false){
			global $db;

			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}

			$html.="";
			$html.="<select style='width:15%;display:none' name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.=" Phòng
			";
			$html.="<select style='width:15%;' name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn phòng--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.=" Ông/Bà
			";
			$html.="<select style='width:15%;' name=$UName id=$UName size=1 
                        '<option value=0>--Chọn--</option>'
                        ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";		

			return $html;
	    }

		static function writeMultiSelectDepartmentUser2($DName,$UName,$includesmsemail=false){
			global $db;
			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL, cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL,cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL,cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				ORDER BY UU_TIEN, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}			

			$html.="
				<table>
					<tr>
                    <td style=';'></td>
                    <td>
			";
			$html.="<select  name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="</td></tr>
					<tr><td style='width:40px;'></td><td>
			";
			$html.="<select name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn toàn cơ quan--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="</td></tr>
					<tr><td style='width:40px;'></td><td>
			";
			$html.="<select name=$UName id=$UName size=5 multiple ondblclick=\"chonNguoithamgia1()\"></select>";
			$html.='
                            <input  type ="button" value="Chọn" name="btChon1" Onclick="chonNguoithamgia1()" style="vertical-align: text-bottom;">
							<input  type ="button" value="Chọn hết" name="btChon1" Onclick="chonhetNguoithamgia1()" style="vertical-align: text-bottom;">
                            
			</td>
			</tr>
				</table>
			';

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";

			return $html;
	    }
		
		static function writeMultiSelectDepartmentUser3($DName,$UName,$includesmsemail=false){
			global $db;

			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL, cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL,cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL,cd.`UU_TIEN`
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					left join `qtht_chucdanh` cd on cd.`ID_CD` = E.`ID_CD`
				WHERE U.ACTIVE=1

				ORDER BY UU_TIEN, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}			

			$html.="
				<table>
					<tr><td style='width:50px;'></td><td>
			";
			$html.="<select  name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="</td></tr>
					<tr><td style='width:50px;'></td><td>
			";
			$html.="<select name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn toàn cơ quan--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="</td></tr>
					<tr><td style='width:50px;'></td><td>
			";
			$html.="<select name=$UName id=$UName size=5 multiple ondblclick=\"chonNguoithamgia2();\"></select>";
			$html.='
                            <input  type ="button" value="Chọn" name="btChon1" Onclick="chonNguoithamgia2()" style="vertical-align: text-bottom;">
							<input  type ="button" value="Chọn hết" name="btChon1" Onclick="chonhetNguoithamgia2()" style="vertical-align: text-bottom;">
                            
			</td>
			</tr>
				</table>
			';

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";

			return $html;
	    }
     public static function convertByte($byte){
        $byte = number_format($byte,null,'.','');
        
        if((int)($byte/1024) == 0){
            return ($byte).' B';
        }else if((int)($byte/(1024*1024))==0){
            return number_format(($byte/1024),2,'.','').' KB';
        }else if((int)($byte/(1024*1024*1024))==0){
            return number_format($byte/(1024*1024),2,'.','').' MB';
        }else if((int)($byte/(1024*1024*1024*1024))==0){
            return number_format($byte/(1024*1024*1024),2,'.','').' GB';
        }else{ 
            return  number_format($byte/(1024*1024*1024*1024),2,'.','').' TB';
        }
    }
	static function getAllNoiLuuTruChildPath($return,$idnoiluutru){
	    	global $db;
			$ArrLoai = array(0=>'Chưa phân loại',1=>'Kho',2=>'Kệ',3=>'Tầng',4=>'Ngăn',5=>'Hộp');
			if($idnoiluutru>0){
				$r = $db->query("
					SELECT
						ID_THUMUCCHA,LOAI,TENTHUMUC
					FROM
						QLLT_NOILUUTRU
					WHERE
						ID_NOILUUTRU = $idnoiluutru
				");
				$dep = $r->fetchAll();
				foreach($dep as $item){
					if($item["ID_THUMUCCHA"]!=null){
						$return[] = $ArrLoai[$item["LOAI"]]." ".$item["TENTHUMUC"];
						QLVBDHCommon::getAllNoiLuuTruChildPath(&$return,(int)$item["ID_THUMUCCHA"]);
					}
				}
			}
	    }
	static function getNoiLuuTruChildById($return,$idnoiluutru,$name){
	
	    	global $db;
			if($idnoiluutru>0){
				$r = $db->query("
					SELECT
						ID_NOILUUTRU,TENTHUMUC
					FROM
						QLLT_NOILUUTRU
					WHERE
						ID_THUMUCCHA = $idnoiluutru
				");
				$dep = $r->fetchAll();
				foreach($dep as $item){
					if($item["TENTHUMUC"]==$name){
						return $return= $item["ID_NOILUUTRU"];
					}else{
						if($item["ID_NOILUUTRU"]!=null){											
							QLVBDHCommon::getNoiLuuTruChildById(&$return,(int)$item["ID_NOILUUTRU"],$name);
						}
					}
				}
			}
	    }
		static function getAllloaitaisanChild($return,$idloaitaisan){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_LoaiThietBi
				FROM				
					qlts_loaitaisan
				WHERE
					ID_LTB_TrucThuoc in ($idloaitaisan)
			");
			$dep = $r->fetchAll();
			foreach($dep as $item){
				$return[] = $item["ID_LoaiThietBi"];
				QLVBDHCommon::getAllloaitaisanChild(&$return,$item["ID_LoaiThietBi"]);
			}
	    }
		
		static function getAllloaitaisanChild_new($return,$idloaitaisan){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_LoaiThietBi
				FROM				
					qlts_loaitaisan
				WHERE
					ID_LoaiThietBi = $idloaitaisan
			");
			$dep = $r->fetchAll();
			
			foreach($dep as $item){
				$return[] = $item["ID_LoaiThietBi"];				
				QLVBDHCommon::getAllloaitaisanChild_new2(&$return,$item["ID_LoaiThietBi"]);				
			}
	    }
		
		static function getAllloaitaisanChild_new2($return,$idloaitaisan){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_LoaiThietBi
				FROM				
					qlts_loaitaisan
				WHERE
					ID_LTB_TrucThuoc = $idloaitaisan
			");
			$dep = $r->fetchAll();
			foreach($dep as $item){
				$return[] = $item["ID_LoaiThietBi"];
				QLVBDHCommon::getAllloaitaisanChild_new2(&$return,$item["ID_LoaiThietBi"]);
			}
	    }
	static function writeSingleSelectUser_taisan($DName,$UName,$includesmsemail=false){
			global $db;
			$arr_user = 'ARR_' . $UName;
			$department = array();
			QLVBDHCommon::GetTree(&$department,"QTHT_DEPARTMENTS","ID_DEP","ID_DEP_PARENT",1,1);
			$r = $db->query("
				SELECT
					ID_G,NAME
				FROM
					QTHT_GROUPS
			");
			$group = $r->fetchAll();
			$r->closeCursor();
			$r = $db->query("
				SELECT
					G.ID_G,DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					G.ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN FK_USERS_GROUPS G ON G.ID_U=U.ID_U

				UNION ALL

				SELECT
					-1 as ID_G,-1 as ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME, U.ORDERS, E.LASTNAME,U.USERNAME,E.PHONE,E.EMAIL
				FROM
					QTHT_USERS U
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
				ORDER BY ORDERS, LASTNAME, NAME
			");
			$user = $r->fetchAll();
			$r->closeCursor();

			QLVBDHCommon::getFullUserByDepIdWithNoGroup(&$user,1);

			for($i=0;$i<count($user);$i++){
				try{
					if($user[$i]['PHONE']){
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 3;
						}else{
							$user[$i]['SMSEMAIL'] = 1;
						}
					}else{
						if($user[$i]['EMAIL']){
							$user[$i]['SMSEMAIL'] = 2;
						}else{
							$user[$i]['SMSEMAIL'] = 0;
						}
					}
				}catch(Exception $ex){
					//echo $ex->__tostring();
					$user[$i]['SMSEMAIL'] = 0;
				}
			}

			$html.="";
			$html.="<select  style='width:15%;display:none' name=G$DName id=G$DName onchange='FillComboBy2Combo(this,document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn nhóm--</option>";
			for($i=0;$i<count($group);$i++){
				$html.="        <option value=".$group[$i]["ID_G"].">".$group[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="";
			$html.="<select style='width:50%;' name=$DName id=$DName onchange='FillComboBy2Combo(document.getElementById(\"G$DName\"),this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
			$html.="        	<option value=-1>--Chọn phòng--</option>";
			for($i=0;$i<count($department);$i++){
				$html.="        <option value=".$department[$i]["ID_DEP"].">".str_repeat("--",$department[$i]['LEVEL']).$department[$i]["NAME"]."</option>";
			}
			$html.="        </select>";
			$html.="";
			$html.="<select style='width:50%;' name=$UName id=$UName size=1
                        '<option value=0>--Chọn--</option>'
                        ondblclick=\"if(typeof(InsertIntoArr)=='function')eval('InsertIntoArr()');\"></select>";

			$html.="<script>";
			$html.="	var $arr_user = new Array();";
			$html.="	var $arr_user"."smsemail"." = new Array();";
			for($i=0;$i<count($user);$i++){
				$html.=$arr_user."[".$i."] = new Array('".$user[$i]['ID_G']."','".$user[$i]['ID_DEP']."','".$user[$i]['ID_U']."','".$user[$i]['NAME']."');";
				$html.=$arr_user."smsemail[".$i."] = new Array('".$user[$i]['SMSEMAIL']."');";
			}

			$html.="	FillComboBy2Combo(document.getElementById(\"G$DName\"),document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
			$html.="</script>";		

			return $html;
	    }

		static function getAllloaitaisandasudung($return){
	    	global $db;
	    	$r = $db->query("SELECT * FROM qlts_loaitaisan 
			WHERE  ID_LoaiThietBi in (select ID_LoaiThietBi from `qlts_thietbi`)");
			$loaitb = $r->fetchAll();
			foreach($loaitb as $item){	
				$return[] = $item["ID_LoaiThietBi"];
				QLVBDHCommon::getAllloaitaisanByChild(&$return,$item["ID_LoaiThietBi"]);
			}
	    }
		static function getAllloaitaisanByChild($return,$idloaitaisan){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_LTB_TrucThuoc
				FROM				
					qlts_loaitaisan
				WHERE
					ID_LoaiThietBi = $idloaitaisan
			");
			$dep = $r->fetchAll();
			foreach($dep as $item){
				if($item["ID_LTB_TrucThuoc"]){
				$return[] = $item["ID_LTB_TrucThuoc"];
				QLVBDHCommon::getAllloaitaisanByChild(&$return,$item["ID_LTB_TrucThuoc"]);
				}
			}
			
	    }
		
		static function getAllnoiluutrudasudung($return){
	    	global $db;
	    	$r = $db->query("SELECT * FROM qllt_noiluutru 
			WHERE  ID_NOILUUTRU in (select ID_NOILUUTRU from `qllt_hoso`)");
			$loaitb = $r->fetchAll();
			foreach($loaitb as $item){
				$return[] = $item["ID_NOILUUTRU"];
				QLVBDHCommon::getAllnoiluutruByChild(&$return,$item["ID_NOILUUTRU"]);
			}
	    }
		static function getAllnoiluutruByChild($return,$idnoiluutru){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_THUMUCCHA
				FROM				
					qllt_noiluutru
				WHERE
					ID_NOILUUTRU = $idnoiluutru
			");
			$dep = $r->fetchAll();
			foreach($dep as $item){
				if($item["ID_THUMUCCHA"]){
				$return[] = $item["ID_THUMUCCHA"];
				QLVBDHCommon::getAllnoiluutruByChild(&$return,$item["ID_THUMUCCHA"]);
				}
			}
			
	    }
		
		
		static function getAllhopsobynoiluutru($return,$idnoiluutru){
	    	global $db;
	    	$r = $db->query("
				SELECT
					ID_NOILUUTRU,LOAI,TENTHUMUC
				FROM				
					qllt_noiluutru
				WHERE
					ID_THUMUCCHA = $idnoiluutru
			");
			$dep = $r->fetchAll();
			
			foreach($dep as $item){
			    if($item["LOAI"]==5){
					$return[] = $item["TENTHUMUC"];	
				}				
				QLVBDHCommon::getAllhopsobynoiluutru(&$return,$item["ID_NOILUUTRU"]);				
			}
	    }

	static function writeSelectDepartmentUser_cvch($DName, $UName) {
        global $db;

        $arr_user = 'ARR_' . $UName;
        $department = array();
        QLVBDHCommon::GetTree(&$department, "QTHT_DEPARTMENTS", "ID_DEP", "ID_DEP_PARENT", 1, 1);		
        $r = $db->query("
				SELECT
					DEP.ID_DEP,U.ID_U,CONCAT(E.FIRSTNAME , ' ' , E.LASTNAME) as NAME
				FROM				
					QTHT_USERS U 
					INNER JOIN QTHT_EMPLOYEES E ON E.ID_EMP = U.ID_EMP
					INNER JOIN QTHT_DEPARTMENTS DEP ON E.ID_DEP=DEP.ID_DEP
				WHERE
					U.ACTIVE = 1
				ORDER BY
					E.`ID_CD`, E.LASTNAME
			");
        $user = $r->fetchAll();
        $r->closeCursor();

        $html.="    	<select name=$DName id=$DName onchange='FillComboByComboExp(this,document.getElementById(\"$UName\"),$arr_user,arr_user_temp);'>";
        $html.="        	<option value=0>--Chọn phòng--</option>";
        for ($i = 0; $i < count($department); $i++) {
            $html.="        <option value=" . $department[$i]["ID_DEP"] . ">" . str_repeat("--", $department[$i]['LEVEL']) . $department[$i]["NAME"] . "</option>";
        }
        $html.="        </select>";

        $html.="<select name=$UName id=$UName></select>";

        $html.="<script>";
        $html.="	var $arr_user = new Array();";
        for ($i = 0; $i < count($user); $i++)
            $html.=$arr_user . "[" . $i . "] = new Array('" . $user[$i]['ID_DEP'] . "','" . $user[$i]['ID_U'] . "','" . $user[$i]['NAME'] . "');";
        $html.="	FillComboByComboExp(document.getElementById(\"$DName\"),document.getElementById(\"$UName\"),$arr_user,null);";
        $html.="</script>";

        return $html;
    }
	function Underline($string,$length){
		$strlength = strlen($string);
		$beginpos = $strlength/2-$length/2;
		$str1 = substr($string,0,$beginpos);
		$str2 = substr($string,$beginpos,$length);
		$str3 = substr($string,$beginpos+$length);
		return $str1 . "<u>" . $str2 ."</u>". $str3;
	}
	function FormatDateVN($string){
		if($string=="")return "";
		$arr = explode("/",$string);
		if((int)$arr[0]<10){
			$arr[0] = "0".(int)$arr[0];
		}else{
			$arr[0] = (int)$arr[0];
		}
		if((int)$arr[1]<3){
			$arr[1] = "0".(int)$arr[1];
		}else{
			$arr[1] = (int)$arr[1];
		}
		if($arr[2]==0){
			$arr[2] = QLVBDHCommon::GetYear();
		}
		return $arr[0]."/".$arr[1]."/".$arr[2];
	}
	function PrintDateVN($string){
		if($string=="")return "";
		$arr = explode("/",$string);
		if((int)$arr[0]<10){
			$arr[0] = "0".(int)$arr[0];
		}else{
			$arr[0] = (int)$arr[0];
		}
		if((int)$arr[1]<3){
			$arr[1] = "0".(int)$arr[1];
		}else{
			$arr[1] = (int)$arr[1];
		}
		if($arr[2]==0){
			$arr[2] = QLVBDHCommon::GetYear();
		}
		return "ngày ".$arr[0]." tháng ".$arr[1]." năm ".$arr[2];
	}
	static function AutoCompleteOnGrid($data,$fieldid,$fieldname,$controlid){
        $html="
            <script>
                var DATA_$controlid = new Array();
        ";
        foreach($data as $item){
            $html.="DATA_".$controlid."[DATA_".$controlid.".length]=new Array('".$item[$fieldid]."','".str_replace("'","\\'",$item[$fieldname])."');";
        }
        $html.="
            </script>
        ";
        return $html;
    }
	function createInputHanxuly2($id, $name, $value, $onchange,$id_t,$islast) {
        $type = $value < 1 ? 8 : 1;
        if ($type == 8) {
            $value = round($value * 8, 1);
        } else {
            $value = round($value, 1);
        }
        $html = "";
        $html = "<input id='" . $id . "' type=text onkeypress='return isNumberKey(event)' name='temp_" . $name . "' size=3 maxlength=3 value='" . $value . "' onchange='Changhan();document.getElementById(\"real_" . $id . "\").value=this.value/document.frm.type_real_" . $id . ".value;" . $onchange . "'>";
        $html .= "<input style='display:none' type=text id='real_" . $id . "' name='" . $name . "' value='" . ($value / $type) . "'>";
        $html .= "<input " . ($type == 1 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_1_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=1;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . "\" value=1>ngày ";
        $html .= "<input " . ($type == 8 ? "checked" : "") . " type=radio name='type_" . $id . "' id='type_8_" . $id . "' onclick=\"document.frm.type_real_" . $id . ".value=8;document.getElementById('real_" . $id . "').value=document.getElementById('" . $id . "').value/this.value;" . $onchange . "\" value=8>giờ";
        $html .= "<input style='display:none' type=text id='type_real_" . $id . "' name='type_real_" . $id . "' value='" . $type . "'>";
		$html .= "<input style='display:none' type=text  name='id_t[]' value='" . $id_t . "'>";
		$html .= "<input style='display:none' type=text  name='islast' value='" . $islast . "'>";		
        return $html;
    }
    static function PaginatorWithParentSubmit($numrows, $posrows, $valuepage, $formname, $currentpage, $action, $target) {
        $html = "";
        if (floor($numrows / $valuepage) < $posrows) {
            if ($numrows % $valuepage == 0) {
                $posrows = floor($numrows / $valuepage);
            } else {
                $posrows = floor($numrows / $valuepage) + 1;
            }
        }
        $pageCurrent = $currentpage;
        $beginpage = $currentpage - $posrows + 1;
        if ($beginpage <= 0) {
            $beginpage = 1;
        }
        $endpage = $beginpage + $posrows - 1;

        if ($currentpage != 1) {
            $hasFirst = "
					<div class='button2-right'><div class='start'><a href='#' title='Bắt đầu' onclick=\"
						window.parent.document." . $formname . ".page.value=1;
						window.parent.document." . $formname . ".action='" . $action . "';
						window.parent.document." . $formname . ".submit();
						window.parent.document." . $formname . ".target='" . $target . "';
					\">Bắt đầu</a></div></div>
					<div class='button2-right'><div class='prev'><a href='#' title='Trước' onclick=\"
						window.parent.document." . $formname . ".page.value=" . ($currentpage - 1) . ";
						window.parent.document." . $formname . ".action='" . $action . "';
						window.parent.document." . $formname . ".submit();
						window.parent.document." . $formname . ".target='" . $target . "';
					\">Trước</a></div></div>
				";
        }

        if ($currentpage < $numrows / $valuepage) {
            $hasNext = "
					<div class='button2-left'><div class='next'><a href='#' title='Tiếp' onclick=\"
						window.parent.document." . $formname . ".page.value=" . ($currentpage + 1) . ";
						window.parent.document." . $formname . ".action='" . $action . "';
						window.parent.document." . $formname . ".submit();
						window.parent.document." . $formname . ".target='" . $target . "';
					\">Tiếp</a></div></div>
					<div class='button2-left'><div class='end'><a href='#' title='Cuối' onclick=\"
						window.parent.document." . $formname . ".page.value=" . (ceil($numrows / $valuepage)) . ";
						window.parent.document." . $formname . ".action='" . $action . "';
						window.parent.document." . $formname . ".submit();
						window.parent.document." . $formname . ".target='" . $target . "';
					\">Cuối</a></div></div>
				";
        }
        $html .= $hasFirst;
        $html .= "<div class='button2-left'><div class='page'>";
        for ($i = $beginpage; $i <= $endpage; $i++) {
            if ($i == $currentpage) {
                $html .= "<span><font color=red>" . $i . "</font></span>";
            } else {
                $html .= "<a href='#' onclick=\"
						window.parent.document." . $formname . ".page.value=" . $i . ";
						window.parent.document." . $formname . ".action='" . $action . "';
						window.parent.document." . $formname . ".submit();
						window.parent.document." . $formname . ".target='" . $target . "';
					\"> " . $i . " </a>";
            }
        }
        $html .= "</div></div>";
        $html .= $hasNext;
        return $html;
    }
    function decrypt($encrypted_string, $encryption_key) {
			$encrypted_string = base64_decode($encrypted_string);
			$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$decrypted_string = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv));
			return substr($decrypted_string,0,strlen($decrypted_string)-32);
		}


}

