<table width=100%>
<?php 
	$userbp = WFEngine::GetAccessUserFromTransitionNoGroup($this->wf_id_t);
	$xltrans = WFEngine::GetAllNextTransitionsByTransition($this->wf_id_t);
	//var_dump($xltrans);
?>
<tr id='tr_cxl' >
	<td>
		<table width="100%">
			<tr>
				<td valign="top" nowrap="nowrap"><font color="Blue">Hành động</font></td>
				<td width="100%">
					<select name=wf_id_t onchange="
					arr_user = new Array();
				    arr_user_temp = new Array();
				    arr1 = new Array();
				    ShowArr('listuser',arr1,arr_user);
				    FastReload();
					">
						<?php foreach($xltrans as $itemutr){?>
						<option value='<?=$itemutr["ID_T"]?>'><?=$itemutr["NAME"]?></option>
						<?php } ?>
					</select>
				</td>
			</tr>			
			<tr>
				<td valign="top" nowrap="nowrap"><font color="Blue">Người bút phê</font></td>
				<td width="100%">
					<select name=ID_U_BP>
						<?php foreach($userbp as $itemubp){?>
						<option value='<?=$itemubp["ID_U"]?>'><?=$itemubp["NAME"]?></option>
						<?php } ?>
					</select>
				</td>
			</tr>

			<tr>
				<td valign="top" nowrap="nowrap"><font color="Blue">Nội dung bút phê</font></td>
				<td width="100%">
					<textarea rows="2" style="width:99%" name=NOIDUNG_BP></textarea>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>