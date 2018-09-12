<?php
class QLVBDHButton {
	static $button = array();
	static function AddButton($name,$action,$class,$script){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array($name,$action,$class,$script);
	}
	static function EnableAddNew($action,$label="Thêm mới"){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array($label,$action,"AddNewButton","AddNewButtonClick();");
	}
	static function EnableDelete($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xoá",$action,"DeleteButton","DeleteButtonClick();");
	}
	static function EnableVbdCoquan($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem văn bản đến toàn cơ quan",$action,"VbdCoquanButton","VbdCoquanButtonClick();");
	}
	static function EnableVbdChuyen($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem văn bản đến được chuyển đến",$action,"VbdChuyenButton","VbdChuyenButtonClick();");
	}
	static function EnableVbdiCoquan($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem văn bản đi toàn cơ quan",$action,"VbdCoquanButton","VbdCoquanButtonClick();");
	}
	static function EnableVbdiChuyen($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem văn bản đi được chuyển đến",$action,"VbdChuyenButton","VbdChuyenButtonClick();");
	}
	static function EnableHelp($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Trợ giúp",$action,"HelpButton","HelpButtonClick();");
	}
	static function EnableCheckAll($child){
		return "<input type=checkbox name=DELALL onclick=\"SelectAll(this,'".$child."')\">";
	}
        static function EnableDongBo($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Đồng bộ",$action,"DongBoButton","DongBoButtonClick();");
	}
	//Bottom
	static function EnableSave($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Lưu",$action,"SaveButton","SaveButtonClick();");
	}
	static function EnableBack($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Trở lại",$action,"BackButton","BackButtonClick();");
	}
        static function EnableSaveLienThong($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Phát hành qua mạng",$action,"SaveButton","SaveLienThongButtonClick();");
	}
	static function DrawButton(){
		$html = "";
		$html .= "<ul>";
		for($i=0;$i<count(QLVBDHButton::$button);$i++){
			if(QLVBDHButton::$button[$i][3]==""){
				$html .= "<li class='icon-".QLVBDHButton::$button[$i][2]."' ".($i==count(QLVBDHButton::$button)-1?"style='border:none'":""). " onClick='".QLVBDHButton::$button[$i][1]."'><a title='".QLVBDHButton::$button[$i][0]."'>".QLVBDHButton::$button[$i][0]."</a></li>";
			}else{
				$html .= "<li class='icon-".QLVBDHButton::$button[$i][2]."' ".($i==count(QLVBDHButton::$button)-1?"style='border:none'":"")." onclick= '".QLVBDHButton::$button[$i][3]."'><a href='#'  title='".QLVBDHButton::$button[$i][0]."'>".QLVBDHButton::$button[$i][0]."</a></li>";
			}
		}
		$html .= "</ul>";
		return $html;
	}
    //vuld 6/8/2018 add xem ds nv toan cq
    static function EnableNhiemvuXulyToancoquan($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem danh sách nhiệm vụ toàn cơ quan cần xử lý",$action,"VbdChuyenButton","NhiemvuXulyToancoquanClick();");
	}
    static function EnableNhiemvuXulyTheogioi($action){
		QLVBDHButton::$button[count(QLVBDHButton::$button)] = array("Xem danh sách nhiệm vụ cần xử lý",$action,"VbdCoquanButton","NhiemvuXulyTheogioiClick();");
	}
    //vuld end
}
?>
