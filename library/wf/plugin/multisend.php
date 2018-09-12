<?php
if(count($user)>1){
?>
<div class='required clearfix'>
	<label>Nhóm</label>
	<select name=wf_selg id=wf_selg onchange='FillComboBy2Combo(this,document.getElementById("wf_seldep"),document.getElementById("wf_selu"),wf_arr_user,wf_nextuser);'>
		<option value=-1>--Mặc định--</option>
<?php
	for($i=0;$i<count($group);$i++){
?>
		<option value="<?=$group[$i]["ID_G"]?>"><?=$group[$i]["NAME"]?></option>
<?php
	}
?>
	</select> <input type=button value="Chọn nhóm" onclick="doSelAllG()" style="width:100px">
</div>
<div class='required clearfix'>
	<label>Phòng</label>
	<select name=wf_seldep id=wf_seldep onchange='FillComboBy2Combo(document.getElementById("wf_selg"),this,document.getElementById("wf_selu"),wf_arr_user,wf_nextuser);'>
		<option value=-1>--Mặc định--</option>
<?php
	for($i=0;$i<count($dep);$i++){
?>
		<option value="<?=$dep[$i]["ID_DEP"]?>"><?=$dep[$i]["NAME"]?></option>
<?php
	}
?>
	</select> 
	<input type=button value="Chọn phòng" onclick="doSelAllDep()" style="width:100px">
</div>
<div class='required clearfix'>
	<label>Người</label>
	<select name=wf_selu id=wf_selu>
	</select> <input type=button value="Chọn người" onclick="doSelAllU()" style="width:100px">
</div>
<input type=hidden name=wf_nexttransition value='<?=$transition_id?>'>
<div id=wf_sel_all>
	<table class=adminlist>
		<thead>
			<tr>
				<th nowrap>STT</th>
				<th nowrap>Kiểu</th>
				<th nowrap>Tên</th>
				<th width=100%>Nội dung công việc</th>
				<th nowrap>Hạn xử lý</th>
				<th nowrap>&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan=6></th>
			</tr>
		</tfoot>
		<tbody></tbody>
	</table>
</div>
<script>
	var wf_arr_dep = new Array();
<?php
	foreach($dep as $depitem){
?>
		wf_arr_dep[wf_arr_dep.length] = new Array();
		wf_arr_dep[wf_arr_dep.length-1][0] = '<?=$depitem['ID_DEP']?>';
		wf_arr_dep[wf_arr_dep.length-1][1] = '<?=$depitem['ID_U_DAIDIEN']?>';
<?php
	}
?>
	var wf_nextuser = new Array();
	var wf_nextdep = new Array();
	var wf_nextg = new Array();
	var wf_arr_user = new Array();
<?php
	for($i=0;$i<count($user);$i++){
?>
	wf_arr_user[<?=$i?>] = new Array('<?=$user[$i]['ID_G']?>','<?=$user[$i]['ID_DEP']?>','<?=$user[$i]['ID_U']?>','<?=$user[$i]['NAME']?>');
<?php
	}
?>
	FillComboBy2Combo(document.getElementById("wf_selg"),document.getElementById("wf_seldep"),document.getElementById("wf_selu"),wf_arr_user,new Array);
	function doSelAllG(){
		var obj = document.getElementById("wf_selg");
		if(obj.selectedIndex>0){
			objsel = obj.options[obj.selectedIndex];
			wf_nextg[wf_nextg.length] = new Array();
			wf_nextg[wf_nextg.length-1][0] = objsel.value;
			wf_nextg[wf_nextg.length-1][1] = objsel.text;
			wf_nextg[wf_nextg.length-1][2] = "";
			wf_nextg[wf_nextg.length-1][3] = "<?=$hanxuly?>";
			obj.removeChild(obj.options[obj.selectedIndex]);
			WF_DrawGrid();
		}
	}
	function doSelAllU(){
		var obj = document.getElementById("wf_selu");
		if(obj.selectedIndex>-1){
			objsel = obj.options[obj.selectedIndex];
			wf_nextuser[wf_nextuser.length] = new Array();
			wf_nextuser[wf_nextuser.length-1][0] = objsel.value;
			wf_nextuser[wf_nextuser.length-1][1] = objsel.text;
			wf_nextuser[wf_nextuser.length-1][2] = "";
			wf_nextuser[wf_nextuser.length-1][3] = "<?=$hanxuly?>";
			obj.removeChild(obj.options[obj.selectedIndex]);
			WF_DrawGrid();
		}
	}
	function doSelAllDep(){
		var obj = document.getElementById("wf_seldep");
		var ok=false;
		for(var i=0;i<wf_arr_dep.length;i++){
			if(wf_arr_dep[i][0]==obj.value){
				if(wf_arr_dep[i][1]>0){
					ok=true;
					break;
				}
			}
		}
		if(ok){
			if(obj.selectedIndex>0){
				objsel = obj.options[obj.selectedIndex];
				wf_nextdep[wf_nextdep.length] = new Array();
				wf_nextdep[wf_nextdep.length-1][0] = objsel.value;
				wf_nextdep[wf_nextdep.length-1][1] = objsel.text;
				wf_nextdep[wf_nextdep.length-1][2] = "";
				wf_nextdep[wf_nextdep.length-1][3] = "<?=$hanxuly?>";
				obj.removeChild(obj.options[obj.selectedIndex]);
				WF_DrawGrid();
			}
		}else{
			alert("Phòng được chọn chưa có người đại diện.");
		}
	}
	function doAddDep(id,text){
		
	}
	function WF_DrawGrid(){
		var html="";
		html += "<table class=adminlist id=alllist>";
			html += "<thead>";
				html += "<tr>";
					html += "<th norwap>STT</th>";
					html += "<th nowrap>Kiểu</th>";
					html += "<th nowrap>Tên</th>";
					html += "<th width=100%>Nội dung xử lý</th>";
					html += "<th nowrap>Hạn xử lý</th>";
					html += "<th nowrap>&nbsp;</th>";
				html += "</tr>";
			html += "</thead>";
			html += "<tfoot>";
				html += "<tr>";
					html += "<th colspan=6></th>";
				html += "</tr>";
			html += "</tfoot>";
			html += "<tbody>";
		stt=0;
		for(var i=0;i<wf_nextg.length;i++){
				stt++;
				html += "<tr id='g"+i+"'>";
					html += "<td nowrap>"+stt+"</td>";
					html += "<td nowrap>Nhóm</td>";
					html += "<td nowrap>"+wf_nextg[i][1]+"</td>";
					html += "<td width=100%><textarea name='wf_name_g[]' style='width:95%' rows=3 onchange='wf_nextg["+i+"][2] = this.value;'>"+wf_nextg[i][2]+"</textarea></td>";
					html += "<td nowrap><input type=textbox onkeypress='return isNumberKey(event)' name='wf_hanxuly_g[]' size=3 maxlength=3 value='"+wf_nextg[i][3]+"' onchange='wf_nextg["+i+"][3] = this.value;'></td>";
					html += "<td nowrap><a href='#' onclick=\"AddComboxItem(document.frm.wf_selg,wf_nextg["+i+"][0],wf_nextg["+i+"][1]);RemoveArrItem(wf_nextg,"+wf_nextg[i][0]+");RemoveElement(document.getElementById('g"+i+"'));QuickReload();return false;\">Xoá</a><input type=hidden name='wf_nextg[]' value='"+wf_nextg[i][0]+"'></td>";
				html += "</tr>";
		}
		for(var i=0;i<wf_nextdep.length;i++){
				stt++;
				html += "<tr id='d"+i+"'>";
					html += "<td nowrap>"+stt+"</td>";
					html += "<td nowrap>Phòng</td>";
					html += "<td nowrap>"+wf_nextdep[i][1]+"</td>";
					html += "<td width=100%><textarea name='wf_name_dep[]' style='width:95%' rows=3 onchange='wf_nextdep["+i+"][2] = this.value;'>"+wf_nextdep[i][2]+"</textarea></td>";
					html += "<td nowrap><input type=textbox onkeypress='return isNumberKey(event)' name='wf_hanxuly_dep[]' size=3 maxlength=3 value='"+wf_nextdep[i][3]+"' onchange='wf_nextdep["+i+"][3] = this.value;'></td>";
					html += "<td nowrap><a href='#' onclick=\"AddComboxItem(document.frm.wf_seldep,wf_nextdep["+i+"][0],wf_nextdep["+i+"][1]);RemoveArrItem(wf_nextdep,"+wf_nextdep[i][0]+");RemoveElement(document.getElementById('d"+i+"'));QuickReload();return false;\">Xoá</a><input type=hidden name='wf_nextdep[]' value='"+wf_nextdep[i][0]+"'></td>";
				html += "</tr>";
		}
		for(var i=0;i<wf_nextuser.length;i++){
				stt++;
				html += "<tr id='u"+i+"'>";
					html += "<td nowrap>"+stt+"</td>";
					html += "<td nowrap>Nhân viên</td>";
					html += "<td nowrap>"+wf_nextuser[i][1]+"</td>";
					html += "<td width=100%><textarea name='wf_name_user[]' style='width:95%' rows=3 onchange='wf_nextuser["+i+"][2] = this.value;'>"+wf_nextuser[i][2]+"</textarea></td>";
					html += "<td nowrap><input type=textbox onkeypress='return isNumberKey(event)' name='wf_hanxuly_user[]' size=3 maxlength=3 value='"+wf_nextuser[i][3]+"' onchange='wf_nextuser["+i+"][3] = this.value;'></td>";
					html += "<td nowrap><a href='#' onclick=\"RemoveArrItem(wf_nextuser,"+wf_nextuser[i][0]+");RemoveElement(document.getElementById('u"+i+"'));QuickReload();return false;\">Xoá</a><input type=hidden name='wf_nextuser[]' value='"+wf_nextuser[i][0]+"'></td>";
				html += "</tr>";
		}
			html += "</tbody>";
		html += "</table>";
		//alert(html);
		document.getElementById("wf_sel_all").innerHTML = html;
		if(window.resize)resize();
	}
	function QuickReload(){
		if(window.resize)resize();
		FillComboBy2Combo(document.getElementById("wf_selg"),document.getElementById("wf_seldep"),document.getElementById("wf_selu"),wf_arr_user,wf_nextuser);
	}
</script>
<?php
}else{
?>
	<input type=hidden name=wf_nexttransition value='<?=$transition_id?>'>
	<input type=hidden name='wf_nextuser' value='<?=$user[0]['ID_U']?>'>
<?php
}
?>