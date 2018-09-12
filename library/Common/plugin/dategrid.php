<form name=frm method=post action="/lichct/lichct/day/code/<?=$this->code?>/type/<?=$this->type?>">
<div style="width:100%;min-height:30px;">
	<div class="car_nothing">&nbsp;</div>
	<div class="car_sept">&nbsp;</div>
	<div style="float:left">
		Ngày <?=QLVBDHCommon::calendar($this->DATE,"DATE","DATE")?>
<?php 
if($this->type=="personal" && $this->ISLEADER==1){
?>
 của <?=QLVBDHCommon::writeSelectDepartmentUserWithSel("ID_DEP","ID_U",$this->idowner,$this->ID_DEP_CURRENT)?>
<?php 
}else if($this->type=="department"){
?>
<?php 
}
?>
<input type=submit value="Xem">
<?php if($this->type=="corporation" && $edit==true){
?>
<input type=button value="Xuất lịch tuần" onclick="execfunction('lichct','lichct','print','export/yes/code/<?=$this->code?>/type/<?=$this->type?>?date='+document.frm.DATE.value);alert('Xuất lịch công tác tuần cơ quan thành công.');">
<input type=button value="Xem lịch tuần cơ quan" onclick="window.open('/lichct/lichct/viewcq/?date='+document.frm.DATE.value)">
<?php }else{ ?>
<?php } ?>
	</div>
	<div style="float:right">
<?php if($this->code=="week"){?>
	<div class="button2-right">
	<div class="prev">
		<a href="#" onclick="document.frm.DATE.value='<?=$this->preweek?>';document.frm.submit();">Tuần trước</a>
	</div>
	</div>
<?php } ?>
<?php if($this->code=="day"){?>
	<div class="button2-right">
	<div class="prev">
		<a href="#" onclick="document.frm.DATE.value='<?=$this->predate?>';document.frm.submit();">Ngày trước</a>
	</div>
	</div>
<?php } ?>
<?php if($this->code=="day"){?>
	<div class="button2-left">
	<div class="next">
		<a href="#" onclick="document.frm.DATE.value='<?=$this->nextdate?>';document.frm.submit();">Ngày tiếp</a>
	</div>
	</div>
<?php } ?>
<?php if($this->code=="week"){?>
	<div class="button2-left">
	<div class="next">
		<a href="#" onclick="document.frm.DATE.value='<?=$this->nextweek?>';document.frm.submit();">Tuần tiếp</a>
	</div>
	</div>
<?php } ?>
	</div><div class=clr></div>
</div>
</form>
<?php if($notview==false){ ?>
<div style="width:100%;">
	<div class="car_nothing">&nbsp;</div>
<?php
	$arr = array("Chủ nhật","Thứ hai","Thứ ba","Thứ tư","Thứ năm","Thứ sáu","Thứ bảy");
	for($i=0;$i<count($this->date);$i++){
		$d = getdate($this->date[$i]);
		$holiday = 0;
		if(BussinessDateModel::IsNonWorkingDate($d)){
			$holiday = 1;
		}
?>
	<div class="car_sept">&nbsp;</div>
	<div class="car_info" style="text-align:center" id="date<?=date("Ymd",$this->date[$i])?>">
	<?=$holiday==1?"<font color=red>":""?>
	<b>
	<?=$arr[$d['wday']]?>
	<br>
	<?=date("d/m/Y",$this->date[$i])?>
	</b>
	<?=$holiday==1?"</font>":""?>
	</div>
<?php
	}
?>
</div><div class=clr></div>
<div style="width:100%;float:left;min-height:15px;"><?php
	foreach($this->rangedate as $date){
?>
	<div
	<?php if($edit){?>
	ondblclick='Update("<?=$date[$fieldid]?>")'
	<?php } ?>
	class=car_range id="item<?=$date[$fieldid]?>"><?=nl2br(htmlspecialchars($date['CONTENT']))?></div><div class=clr></div>
<?php
	}
?></div>
<?php
	for($i=$beginwork;$i<=$endwork;$i++){
?>
<div style="width:100%;" id="car_main">
	<div title="car_time" class="car_time_<?=$i%2?>"><?=str_pad(floor(($i-1)/2),2,"0",STR_PAD_LEFT).":".($i%2==1?"00":"30")?></div>
<?php
		for($j=0;$j<count($this->date);$j++){
			$d = getdate($this->date[$j]);
			$holiday = "";
			if(BussinessDateModel::IsNonWorkingDate($d)){
				$holiday = "_holiday";
			}
?>
	<div class="car_sept">&nbsp;</div>
	<div
	<?php if($edit){?>
	ondblclick="AddCalendar('<?=$this->date[$j]?>','<?=str_pad(floor(($i-1)/2),2,"0",STR_PAD_LEFT).":".($i%2==1?"00":"30")?>');" 
	<?php } ?>
	<?php if($edit){?>title="Nhấp đúp chuột để thêm mới"<?php } ?> class="car_detail<?=$holiday?>" id="<?=$j+1?><?=str_pad(floor(($i-1)/2),2,"0",STR_PAD_LEFT).($i%2==1?"00":"30")?>"></div>
<?php
		}
?>
</div>
<?php
	}
?>
<div style="position:absolute" id=car_container></div>
<div style="position:absolute;border:1px solid gray;display:none;background:white;z-index:999" id=car_tooltip></div>
<div style="display:none" id="car_button" class="car_button" onmousedown="cancelEvent(event)"></div>
<div style="display:none" id="car_gop_y" class="car_gop_y" onmousedown="cancelEvent(event)"></div>
<script>
	var ArrDate = new Array();
<?php
	foreach($this->date as $itemdate){
?>
		ArrDate[ArrDate.length] = "<?=date("Ymd",$itemdate)?>";
<?php
	}
?>
	var ArrCalendar = new Array();
	ArrCalendar[ArrCalendar.length] = new Array();
<?php
		foreach($this->data as $calendar){
?>
	ArrCalendar[ArrCalendar.length] = new Array();
<?php
			foreach($calendar as $item){
				$begintime = getdate(strtotime($item['BEGINTIME']));
				$endtime = getdate(strtotime($item['ENDTIME']));
?>
				ArrCalendar[ArrCalendar.length-1][ArrCalendar[ArrCalendar.length-1].length] = new Array(
					<?=$item[$fieldid]?>,																						//id
					"<?=str_replace(chr(13).chr(10),'',nl2br(htmlspecialchars($item['CONTENT'])))?>",							//Name
					"<?=str_pad($begintime['hours'],2,"0",STR_PAD_LEFT).($begintime['minutes']>=30?"30":"00")?>",				//Begin range
					"<?=str_pad($endtime['hours'],2,"0",STR_PAD_LEFT).($endtime['minutes']>=30?"30":"00")?>",					//End range
					"<?=str_pad($begintime['hours'],2,"0",STR_PAD_LEFT).str_pad($begintime['minutes'],2,"0",STR_PAD_LEFT)?>",	//real begin time
					"<?=str_pad($endtime['hours'],2,"0",STR_PAD_LEFT).str_pad($endtime['minutes'],2,"0",STR_PAD_LEFT)?>",		//real end time
					"<?=$item[$fieldiscaptren]?>",																				//Da chuyen thanh lich cua phong	
					"<?=str_replace(chr(13).chr(10),'',nl2br(htmlspecialchars($item['GOP_Y'])))?>",								//Gop y
					"<?=str_replace(chr(13).chr(10),'',nl2br(htmlspecialchars($item['THANHPHAN'])))?>",								//Gop y
					"<?=str_replace(chr(13).chr(10),'',nl2br(htmlspecialchars($item['DIADIEM'])))?>"								//Gop y
				);
<?php
			}
		}
?>
	function DrawCalendar(){
		fullwidth = document.getElementById("car_main").offsetWidth;
		DrawGrid(fullwidth);
		container = document.getElementById("car_container");
		container.style.top = "" + getY(document.getElementById("1<?=$beginwork_time?>")) + "px";
		container.style.left = "" + getX(document.getElementById("1<?=$beginwork_time?>")) + "px";
		container.innerHTML = "";
		maxwidth = document.getElementById("1<?=$beginwork_time?>").offsetWidth;
		minheight = document.getElementById("1<?=$beginwork_time?>").offsetHeight;
		for(j=1;j<ArrCalendar.length;j++){
			for(var i=0;i<ArrCalendar[j].length;i++){
				var item = makeItem(ArrCalendar[j][i][0],ArrCalendar[j][i][2],ArrCalendar[j][i][3],maxwidth,minheight,j);
				container.innerHTML = container.innerHTML + "<div <?php if($edit){?>onmousedown='BeginMove(event,this,"+j+","+i+")'<?php } ?> id=item"+ArrCalendar[j][i][0]+" class=car_item_1 style='z-index:20;top:"+item[0]+"px;left:"+item[1]+"px;width:"+item[2]+"px;height:"+item[3]+"px;overflow:hidden'>"+makeDetailItem(ArrCalendar[j][i],item,j,i)+"</div>";
			}
		}
		AddItemEvent();
	}
	function makeDetailItem(object,parentinfo,i,j){
		var html = "";
		var cssleft = "car_item_left";
		var gopycmd = "HideGopY();";
		//Co gop y
		if(object[7]!=""){
			cssleft = "car_item_left_gop_y";
			gopycmd = "HideGopY();ShowGopY("+object[0]+","+i+","+j+");";
		}else if(object[6]==1){
			cssleft = "car_item_left_dachuyen";
		}
		//noi dung hien thi
		var noidung = object[1];
		<?php if($this->code=="day"){?>
		if(object[8]!=""){
			noidung += " <i>(" + object[8] + ")</i>";
		}
		if(object[9]!=""){
			noidung += " <i>(" + object[9] + ")</i>";
		}
		<?php } ?>
		html += "<div <?php if($edit){?>onmousedown='dragWay=0;'<?php } ?> class="+cssleft+" style='width:4px;height:"+(parentinfo[3]-4)+"px'></div>";
		html += "<div onmouseout=\"HideToolTip(event)\" onmousemove='ShowToolTip("+object[0]+","+i+","+j+",event)' onclick='ShowButton("+object[0]+","+i+","+j+");"+gopycmd+"' <?php if($edit){?>ondblclick='Update("+object[0]+")'<?php } ?> onmousedown='cancelEvent(event)' id=itemcontent"+object[0]+" class='car_item_content editlinktip hasTip' style='width:"+(parentinfo[2]-4)+"px;height:"+(parentinfo[3]-4)+"px'>"+noidung+"</div>";
		html += "<div <?php if($edit){?>onmousedown='dragWay=1;'<?php } ?> class=car_item_bottom style='width:"+parentinfo[2]+"px;height:4px;overflow:hidden'></div>";
		return html;
	}
	function getYTime(time){
		return getY(document.getElementById("1"+time))-getY(document.getElementById("car_container"));
	}
	function getHTime(time1,time2){
		return getY(document.getElementById("1"+time2))-getY(document.getElementById("1"+time1))+document.getElementById("1"+time2).offsetHeight-1;
	}
	function getY( oElement )
	{
		var iReturnValue = 0;
		while( oElement != null ) {
			iReturnValue += oElement.offsetTop;
			oElement = oElement.offsetParent;
		}
		return iReturnValue;
	}
	function getX( oElement )
	{
		var iReturnValue = 0;
		while( oElement != null ) {
			iReturnValue += oElement.offsetLeft;
			oElement = oElement.offsetParent;
		}
		return iReturnValue;
	}
	function getRange(time1,time2,arrpos){
		mintime = "<?=$endwork_time?>";
		maxtime = "<?=$beginwork_time?>";
		for(var i=0;i<ArrCalendar[arrpos].length;i++){
			if(time2 >= ArrCalendar[arrpos][i][2] && time2 <= ArrCalendar[arrpos][i][3]){
				if(mintime>=ArrCalendar[arrpos][i][2]){
					mintime=ArrCalendar[arrpos][i][2];
				}
				if(maxtime<=ArrCalendar[arrpos][i][3]){
					maxtime=ArrCalendar[arrpos][i][3];
				}
			}else if(time1 <= ArrCalendar[arrpos][i][2] && time2 >= ArrCalendar[arrpos][i][3]){
				if(mintime>=ArrCalendar[arrpos][i][2]){
					mintime=ArrCalendar[arrpos][i][2];
				}
				if(maxtime<=ArrCalendar[arrpos][i][3]){
					maxtime=ArrCalendar[arrpos][i][3];
				}
			}else if(time1 <= ArrCalendar[arrpos][i][3] && time2 >= ArrCalendar[arrpos][i][3]){
				if(mintime>=ArrCalendar[arrpos][i][2]){
					mintime=ArrCalendar[arrpos][i][2];
				}
				if(maxtime<=ArrCalendar[arrpos][i][3]){
					maxtime=ArrCalendar[arrpos][i][3];
				}
			}
		}
		return new Array(mintime,maxtime);
	}
	function makeItem(id,time1,time2,maxwidth,minheight,arrpos){
		count = 0;
		var range = getRange(time1,time2,arrpos);
		mintime = range[0];
		maxtime = range[1];
		for(var i=0;i<ArrCalendar[arrpos].length;i++){
			if(ArrCalendar[arrpos][i][3] >= mintime && ArrCalendar[arrpos][i][3] <= maxtime){
				count++;
				if(id==ArrCalendar[arrpos][i][0]){
					pos=count;
					minute1 = ArrCalendar[arrpos][i][4];
					minute2 = ArrCalendar[arrpos][i][5];
				}
			}else if(ArrCalendar[arrpos][i][2] <= mintime && ArrCalendar[arrpos][i][3] >= maxtime){
				count++;
				if(id==ArrCalendar[arrpos][i][0]){
					pos=count;
					minute1 = ArrCalendar[arrpos][i][4];
					minute2 = ArrCalendar[arrpos][i][5];
				}
			}else if(ArrCalendar[arrpos][i][2] <= maxtime && ArrCalendar[arrpos][i][3] >= maxtime){
				count++;
				if(id==ArrCalendar[arrpos][i][0]){
					pos=count;
					minute1 = ArrCalendar[arrpos][i][4];
					minute2 = ArrCalendar[arrpos][i][5];
				}
			}
		}
		minute1 = minute1.substr(2,2);
		minute2 = minute2.substr(2,2);
		minute1 = parseInt(minute1)>=30?parseInt(minute1)-30:parseInt(minute1);
		minute2 = parseInt(minute2)>=30?parseInt(minute2)-30:parseInt(minute2);
		_width = maxwidth/count-3;
		_left = maxwidth*(arrpos-1) + (arrpos-1)*5 + maxwidth/count * (pos-1);
		_top = getYTime(time1)+(minute1/30*minheight);
		_height = getHTime(time1,time2)-(minheight - minute2/30*minheight) - (minute1/30*minheight);
		var result = new Array(_top,_left,_width,_height);
		return result;
	}
	function DrawGrid(fullwidth){
		div = document.getElementsByTagName("div");
		for(var i=0;i<div.length;i++){
			if(div[i].className=="car_detail" || div[i].className=="car_info" || div[i].className=="car_detail_holiday"){
				div[i].style.width=""+((fullwidth-90-(ArrCalendar.length*5))/(ArrCalendar.length-1))+"px";
			}
		}
		//xit range cho dung
<?php
	foreach($this->rangedate as $date){
		if(strtotime($date['BEGINTIME'])<$this->date[0]){
			$date['BEGINTIME'] = date("Y-m-d",$this->date[0]);
		}
		if(strtotime($date['ENDTIME']) > $this->date[count($this->date)-1]){
			$date['ENDTIME'] = date("Y-m-d",$this->date[count($this->date)-1]);
		}
		$tempb = $date['BEGINTIME'];
		$tempb = substr($tempb,0,10);
		$tempb = str_replace("-","",$tempb);
		$tempe = $date['ENDTIME'];
		$tempe = substr($tempe,0,10);
		$tempe = str_replace("-","",$tempe);
?>
		document.getElementById("item<?=$date[$fieldid]?>").style.marginLeft = (getX(document.getElementById("date<?=$tempb?>"))-getX(document.getElementById("car_main"))) + "px";
		document.getElementById("item<?=$date[$fieldid]?>").style.marginBottom = "3px";
		document.getElementById("item<?=$date[$fieldid]?>").style.marginTop = "3px";
		document.getElementById("item<?=$date[$fieldid]?>").style.width = (getX(document.getElementById("date<?=$tempe?>")) - getX(document.getElementById("date<?=$tempb?>")) + document.getElementById("date<?=$tempe?>").offsetWidth-2) + "px";
			
<?php
	}
?>
	}
	document.onmousemove = mouseMove;
	document.onmouseup   = mouseUp;
	document.onmousedown = mouseDown;
		
	var dragObject  = null;
	var dragCalendar  = null;
	var dragItem  = null;
	var mouseOffset = null;
	var dragWay = null;
	var dragHeight = null;
	var dragTop = null;
	var dragLeft = null;
	var dragDate = null;
	function getMouseOffset(target, ev){
		ev = ev || window.event;
		
		var docPos    = getPosition(target);
		var mousePos  = mouseCoords(ev);
		return {x:mousePos.x, y:mousePos.y};
	}
	function getScrollY() {
		  var scrOfY = 0;
		  if( typeof( window.pageYOffset ) == 'number' ) {
		    scrOfY = 0;
		  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		    scrOfY = 0;
		  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		    scrOfY = document.documentElement.scrollTop;
		  }
		  //alert(scrOfY);
		  return scrOfY;
		}
	function getPosition(e){
		var left = 0;
		var top  = 0;
	
		while (e.offsetParent){
			left += e.offsetLeft;
			top  += e.offsetTop;
			e     = e.offsetParent;
		}
	
		left += e.offsetLeft;
		top  += e.offsetTop;
	
		return {x:left, y:top};
	}
	
	function mouseMove(ev){
		if(dragObject){
			ev = ev || window.event;
			if(dragWay==0){
				temp = mouseCoords(ev);
				var top = dragTop + (temp.y - mouseOffset.y);
				var left = dragLeft + (temp.x - mouseOffset.x);
				var bottom = top+dragObject.offsetHeight;
				if(bottom>=getY(document.getElementById("1<?=$endwork_time_object?>"))-getY(document.getElementById("car_container"))+document.getElementById("1<?=$endwork_time_object?>").offsetHeight)return false;
				if(top<0){
					top=0;
				}
				dragObject.style.top = top+"px";
				var mouseDate = getDateFromMouse(temp.x);
				if(mouseDate.length==2){
					dragObject.style.left = mouseDate[0]+"px";
					dragDate = mouseDate[1];
				}
			}else if(dragWay==1){
				temp = mouseCoords(ev);
				var height = dragHeight + (temp.y - mouseOffset.y);
				var bottom = dragTop+height;
				if(bottom>=getY(document.getElementById("1<?=$endwork_time_object?>"))-getY(document.getElementById("car_container"))+document.getElementById("1<?=$endwork_time_object?>").offsetHeight)return false;
				
				if(height<50)height=50;
				dragObject.style.height = height+"px";
				/*
				var mouseDate = getDateFromMouse(temp.x);
				if(mouseDate.length==2){
					dragObject.style.left = mouseDate[0]+"px";
					dragDate = mouseDate[1];
				}
				*/
			}
			return false;
		}
	}
	function getDateFromMouse(x){
		for(var i=0;i<ArrDate.length;i++){
			var obj = document.getElementById("date"+ArrDate[i]);
			if(x>=getX(obj) && x<=getX(obj)+obj.offsetWidth){
				//alert(getX(obj));
				return new Array(getX(obj)-getX(document.getElementById("car_container")),i);
			}
		}
		return new Array();
	}
	function mouseDown(){
		HideGopY();
		HideButton();
	}
	function mouseUp(){
		if(dragObject){
			if(dragDate!=dragCalendar-1){
				ArrCalendar[dragDate+1][ArrCalendar[dragDate+1].length] = ArrCalendar[dragCalendar][dragItem];
				ArrCalendar[dragCalendar].splice(dragItem,1);
				dragCalendar = dragDate+1;
				dragItem = ArrCalendar[dragDate+1].length-1;
			}
			if(dragWay==0){
				pos = dragObject.offsetTop/document.getElementById("1<?=$beginwork_time?>").offsetHeight;
				begintime = (pos+<?=$beginwork-1?>)*30+1;
				endtime = begintime + dragObject.offsetHeight/document.getElementById("1<?=$beginwork_time?>").offsetHeight*30-1;
				if(endtime > <?=$endwork*30?>)endtime=<?=$endwork*30-2?>;
				execfunction("lichct","lichct","update","type/<?=$this->type?>/begintime/"+begintime+"/endtime/"+endtime+"/id/"+dragObject.id+"/date/"+ArrDate[dragDate]);
				ArrCalendar[dragCalendar][dragItem][4] = ""+(Math.floor(begintime/60)<10?"0"+Math.floor(begintime/60):""+Math.floor(begintime/60))+Math.floor(begintime%60);
				ArrCalendar[dragCalendar][dragItem][2] = ""+(Math.floor(begintime/60)<10?"0"+Math.floor(begintime/60):""+Math.floor(begintime/60))+(Math.floor(begintime%60)>30?"30":"00");
				ArrCalendar[dragCalendar][dragItem][5] = ""+(Math.floor(endtime/60)<10?"0"+Math.floor(endtime/60):""+Math.floor(endtime/60))+Math.floor(endtime%60);
				ArrCalendar[dragCalendar][dragItem][3] = ""+(Math.floor(endtime/60)<10?"0"+Math.floor(endtime/60):""+Math.floor(endtime/60))+(Math.floor(endtime%60)>30?"30":"00");
				DrawCalendar();
			}else if(dragWay==1){
				pos = dragObject.offsetTop/document.getElementById("1<?=$beginwork_time?>").offsetHeight;
				begintime = (pos+<?=$beginwork-1?>)*30+1;
				endtime = begintime + dragObject.offsetHeight/document.getElementById("1<?=$beginwork_time?>").offsetHeight*30-1;
				if(endtime >= <?=$endwork*30?>)endtime=<?=$endwork*30-2?>;
				execfunction("lichct","lichct","update","type/<?=$this->type?>/begintime/"+begintime+"/endtime/"+endtime+"/id/"+dragObject.id+"/date/"+ArrDate[dragDate]);
				ArrCalendar[dragCalendar][dragItem][5] = ""+(Math.floor(endtime/60)<10?"0"+Math.floor(endtime/60):""+Math.floor(endtime/60))+Math.floor(endtime%60);
				ArrCalendar[dragCalendar][dragItem][3] = ""+(Math.floor(endtime/60)<10?"0"+Math.floor(endtime/60):""+Math.floor(endtime/60))+(Math.floor(endtime%60)>30?"30":"00");
				DrawCalendar();
			}
			dragObject = null;
			mouseOffset = null;
			dragHeight = null;
			dragTop = null;
			dragLeft = null;
			dragDate = null;
			dragCalendar = null;
			dragItem = null;
			HideButton();
			HideGopY();
		}
	}
	function ShowToolTip(item,i,j,ev){
		//if(dragCalendar == null){
			var scrolly = getScrollY();
			ev = ev || window.event;
			temp = mouseCoords(ev);
			tooltip = document.getElementById("car_tooltip");
			obj = document.getElementById("item"+item);
			tooltip.style.top = ((temp.y - 10) + scrolly)+ "px";
			tooltip.style.left = (temp.x + 10) + "px";
			tooltip.style.display = "block";
			tooltip.style.paddingTop = "5px";
			tooltip.innerHTML = "<b>Bắt đầu:</b> " + ArrCalendar[i][j][4].substring(0,2) + ":" + ArrCalendar[i][j][4].substring(2) + "<br><b>Kết thúc:</b> " + ArrCalendar[i][j][5].substring(0,2) + ":" + ArrCalendar[i][j][5].substring(2) + "<br><b>Thành phần:</b> " + ArrCalendar[i][j][8] + "<br>" + "<b>Địa điểm:</b> " + ArrCalendar[i][j][9];
		//}
	}
	function HideToolTip(e){
		tooltip = document.getElementById("car_tooltip");
		tooltip.style.display = "none";
	}
	function makeDraggable(item){
		if(!item) return;
		item.onmousedown = function(ev){
			dragObject  = this;	mouseOffset = mouseCoords(event);	return false;
		}
	}
	function BeginMove(evt,obj,i,j){
		obj.style.zIndex  = "900";
		dragObject  = obj;
		mouseOffset = mouseCoords(evt);
		dragHeight = dragObject.offsetHeight;
		dragTop = dragObject.offsetTop;
		dragLeft = dragObject.offsetLeft;
		dragCalendar  = i;
		dragItem  = j;
		dragDate = i-1;
	}
	function mouseCoords(ev){
		if(ev.pageX || ev.pageY){
			return {x:ev.pageX, y:ev.pageY};
		}
		return {
			x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
			y:ev.clientY + document.body.scrollTop  - document.body.clientTop
		};
	}
	function AddItemEvent(){

	}
	function AddCalendar(day,time){
		document.location.href = "/lichct/lichct/input/day/"+day+"/time/"+time+"/type/<?=$this->type?>/code/<?=$this->code?>";
	}
	function Update(id){
		document.location.href = "/lichct/lichct/input/type/<?=$this->type?>/id/"+id+"/day/<?=$this->date[0]?>/code/<?=$this->code?>";
	}
	function Delete(id){
		if(confirm("Bạn có muốn xoá lịch công tác này không?")){
			execfunction("lichct","lichct","delete","type/<?=$this->type?>/id/"+id);
			ArrCalendar[dragCalendar].splice(dragItem,1);
			DrawCalendar();
		}
	}
	function UpToDep(id){
		<?php
			if($this->type=="personal"){
		?>
		if(confirm("Bạn có muốn đăng ký thành lịch của phòng không?")){
		<?php 
			}else{
		?>
		if(confirm("Bạn có muốn đăng ký thành lịch của cơ quan không?")){
		<?php
			}
		?>
			execfunction("lichct","lichct","uptodep","type/<?=$this->type?>/id/"+id);
			ArrCalendar[dragCalendar][dragItem][6]=1;
			DrawCalendar();
			HideButton();
			HideGopY();
		}
	}
	function ShowGopY(id,i,j){
		gopy = document.getElementById("car_gop_y");
		obj = document.getElementById("item"+id);
		gopy.style.top = (obj.offsetTop + obj.offsetHeight + getY(document.getElementById("car_container"))) + "px";
		gopy.style.left = (obj.offsetLeft + getX(document.getElementById("car_container"))) + "px";
		gopy.style.width = (obj.offsetWidth-2) + "px";
		gopy.style.height = "50px";
		gopy.style.display = "block";
		gopy.style.paddingTop = "5px";
		dragCalendar  = i;
		dragItem  = j;
		<?php if($edit){?>
		gopy.innerHTML = ArrCalendar[i][j][7];
		<?php }else{ ?>
		gopy.innerHTML = "<textarea title='"+id+"_"+i+"_"+j+"' id=NOTE rows=5 style='border:0px;background:transparent;width:100%;margin:0;'>"+ArrCalendar[i][j][7].replace(/[<][b][r][ ][\/][>]/gi,"\n")+"</textarea>";
		document.getElementById("NOTE").focus();
		<?php } ?>
	}
	function ShowButton(id,i,j){
		<?php if($edit==false && $note==false)echo "return;"; ?>
		button = document.getElementById("car_button");
		obj = document.getElementById("item"+id);
		button.style.top = (obj.offsetTop - 30 + getY(document.getElementById("car_container"))) + "px";
		button.style.left = (obj.offsetLeft + getX(document.getElementById("car_container"))) + "px";
		button.style.width = (obj.offsetWidth-2) + "px";
		button.style.height = "22px";
		button.style.display = "block";
		button.style.paddingTop = "5px";
		dragCalendar  = i;
		dragItem  = j;
		var imgup = "<img src='/images/icon_del.gif' onclick='Delete("+id+")' style='cursor:pointer' title='Xoá'>";
		<?php
		if($this->type=="department"){
		?>
		imgup += "<img src='/images/up_icon.gif' onclick='UpToDep("+id+")' style='cursor:pointer' title='Đăng ký lịch cơ quan'>";
		<?php
		}else if($this->type=="personal"){
		?>
		imgup += "<img src='/images/up_icon.gif' onclick='UpToDep("+id+")' style='cursor:pointer' title='Đăng ký lịch phòng'>";
		<?php
		}
		?>
		if(ArrCalendar[i][j][6]==1){
			imgup = "";
		}
		<?php if($edit){?>
		button.innerHTML = "<img src='/images/icon_vanbanlienquan.jpg' onclick='Update("+id+")' style='cursor:pointer' title='Cập nhật'>"+imgup;
		<?php }else if($edit==false && $note==true){ ?>
		button.innerHTML = "<img src='/images/icon_vanbanlienquan.jpg' onclick='ShowGopY("+id+","+i+","+j+")' style='cursor:pointer' title='Ghi chú'>";
		<?php } ?>
	}
	function HideButton(){
		document.getElementById("car_button").style.display="none";
	}
	function HideGopY(){
		<?php if($edit){?>
		document.getElementById("car_gop_y").style.display="none";
		<?php }else{ ?>
		
		if(document.getElementById("NOTE")){
			var arritem = document.getElementById("NOTE").getAttribute("title").split("_");
			if(ArrCalendar[arritem[1]][arritem[2]][7]!=document.getElementById("NOTE").value){
				//alert(document.getElementById("NOTE").value);
				ArrCalendar[arritem[1]][arritem[2]][7] = document.getElementById("NOTE").value;
				execfunction("lichct","lichct","note","type/<?=$this->type?>/id/"+arritem[0]+"/gopy/"+encodeURIComponent(document.getElementById("NOTE").value));
				DrawCalendar();
			}
		}
		document.getElementById("car_gop_y").style.display="none";
		<?php } ?>
	}
	function ViewWeek(){
		window.open('/lichct/lichct/print/excel/no/code/<?=$this->code?>/type/<?=$this->type?>/<?php if($this->type=="personal" && $this->ISLEADER==1){?>ID_U/'+document.frm.ID_U.value+'<?php } ?>?date='+document.frm.DATE.value);
	}
	//DrawCalendar();
	window.addEvent('domready', function(){ DrawCalendar(); });
	window.onresize =function(){DrawCalendar();};
</script>
<?php } else { echo $content; }?>