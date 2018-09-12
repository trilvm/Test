<?php
require_once 'config.php';
require_once 'db/connection.php';
if($_POST["MASOHOSO"]!=""){
	$sql = sprintf("
		SELECT * FROM WEB_MOTCUA_TRACUU WHERE MASOHOSO='%s' ORDER BY ID_TRACUU DESC
		",
		mysql_real_escape_string($_POST["MASOHOSO"])
	);
	$row = query($sql);
	$TENTOCHUCCANHAN = "";
	$TRANGTHAI = "";
	$GHICHU = "";
	$TENHOSO = "";
	while($item = mysql_fetch_assoc($row)){
		$TENTOCHUCCANHAN = $item["TENTOCHUCCANHAN"];
		$TENHOSO = $item["TENHOSO"];
		$TRANGTHAI = $item["TRANGTHAI"];
		$GHICHU = nl2br(htmlspecialchars($item["GHICHU"]));
		break;
	}
	$tt = array("","Đang xử lý","Chờ bổ sung hồ sơ","Đã giải quyết xong");
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<form method=post>
<table>
	<tr>
		<td style="color: blue">Mã số hồ sơ</td>
		<td><input type=text size=20 name=MASOHOSO value="<?=$_POST["MASOHOSO"]?>"> <input type=submit value="Xem hồ sơ"></td>
	</tr>
<?php if($TRANGTHAI>0){ ?>
	<tr>
		<td style="color: blue">Hồ sơ: </td>
		<td><?=$TENHOSO?></td>
	</tr>
	<tr>
		<td style="color: blue">Trạng thái: </td>
		<td><?=$tt[$TRANGTHAI]?></td>
	</tr>
	<tr>
		<td style="color: blue">Ghi chú: </td>
		<td><?=$GHICHU?></td>
	</tr>
<?php } ?>
</table>
</form>
</body>
</html>