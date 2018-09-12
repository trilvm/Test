function actionChat(option,id)
{
	alert(option+id);
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		 if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			if(option=="cancelsend")
			{
				//actioncancel("placeholdersend"+id);
			}
			
		}
	}
	xmlhttp.open("GET","action_chat.php?option="+option+"&id="+id,true);
	xmlhttp.send();
}

function actioncancel(curentElement)
{
	
	var cell = document.getElementById(curentElement);
	if ( cell.hasChildNodes() )
	{
		while ( cell.childNodes.length >= 1 )
		{
			cell.removeChild( cell.firstChild );       
		} 
		cell.innerText =' đã hủy';
		cell.id="dahuy";
	}
	
}
function removeTyping(curentElement)
{
	var cell = document.getElementById(curentElement);
	if ( cell.hasChildNodes() )
	{
		while ( cell.childNodes.length >= 1 )
		{
			cell.removeChild( cell.firstChild );       
		} 
		cell.innerHTML="";
		cell.id="daremove";
	}
	
}
function actioncancelduringsend(process,confirmcancel)
{
	var cellprocess = document.getElementById(process);
	cellprocess.style.display="none";
	
	var cellconfirmcancel = document.getElementById(confirmcancel);
	cellconfirmcancel.style.display='block';
	cellconfirmcancel.innerText =' đã hủy';
}
function tientrinhprocesssend(phantram,uniqueid)
{
	document.getElementById("send"+uniqueid).style.width=phantram+'px';
	document.getElementById("sendvalue"+uniqueid).innerText=Math.round(phantram/2)+'%';
	if(phantram==200)
	{
		document.getElementById("tdtbhuysend"+uniqueid).removeChild(document.getElementById("tdtbhuysend"+uniqueid).firstChild);
		document.getElementById("tdtbhuysend"+uniqueid).innerText ='Đã gửi';
	}
}

function tientrinhprocessaccept(phantram,uniqueid)
{
	document.getElementById("get"+uniqueid).style.width=phantram+'px';
	document.getElementById("getvalue"+uniqueid).innerText=Math.round(phantram/2)+'%';
	if(phantram==200)
	{
		document.getElementById("tdbthuyget"+uniqueid).removeChild(document.getElementById("tdbthuyget"+uniqueid).firstChild);
		document.getElementById("tdbthuyget"+uniqueid).innerHTML='<input class="bt" type="button" id="openexploder'+uniqueid+'" value="Xem">';
	}
}





/*
function tientrinhprocesssend(phantram,uniqueid,manualPB2)
{
	
	manualPB2.setPercentage('+'+phantram);return false;
	document.observe('dom:loaded', function() {
	manualPB2 = new JS_BRAMUS.jsProgressBar(
		$("'"+uniqueid+"'"),
		0,
		{

			barImage	: Array(
								'images/bramus/percentImage_back4.png',
								'images/bramus/percentImage_back3.png',
								'images/bramus/percentImage_back2.png',
								'images/bramus/percentImage_back1.png'
		),

		onTick : function(pbObj) {

										switch(pbObj.getPercentage()) {
											case 100:
												;
											break;

										}

										return true;
									}
								}
							);
	}, false);
}
*/