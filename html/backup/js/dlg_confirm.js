// Detect if the browser is IE or not.
// If it is not IE, we assume that the browser is NS.
var IE = document.all?true:false

// If NS -- that is, !IE -- then set up for mouse capture
if (!IE) document.captureEvents(Event.MOUSEMOVE)

// Set-up to use getMouseXY function onMouseMove
document.onmousemove = getMouseXY;

// Temporary variables to hold mouse x-y pos.s
var tempX = 0
var tempY = 0

// Main function to retrieve mouse x-y pos.s

function getMouseXY(e) {
 
  return true
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


	var IS_YES = 0;
	var IS_CANCEL = 0;
	var IS_SELECTED = 0;
	var func_yes = '';
	var func_cancel = '';
	function displayConfirm(text,value_yes,value_cancel,pa_func_yes,pa_func_cancel){
		if(!value_yes)
		value_yes = "Có";
		if(!value_cancel)
		value_cancel = "Không";
		var content_confirm =  document.getElementById('content_confirm');
		content_confirm.innerHTML = text;
		 var DLG_YES_BUTTON = document.getElementById("DLG_YES_BUTTON");
		 DLG_YES_BUTTON.value = value_yes;
		  var DLG_CANCEL_BUTTON = document.getElementById("DLG_CANCEL_BUTTON");
		 DLG_CANCEL_BUTTON.value = value_cancel;
		 overlay("DISPLAY");
		 IS_SELECTED = 0;
		 func_yes = pa_func_yes ;
		 func_cancel = pa_func_cancel  ;

	}
	function overlay(str) {
		var scrolly = getScrollY();
		var el = document.getElementById("confirm_dlg");
		el.style.visibility = (el.style.visibility == "visible") ? "hidden" : "visible";
		el.style.margin = ( scrolly)/2 + "px" + "  auto"; 
		//alert(el.style.margin);
		 var DLG_YES_BUTTON = document.getElementById("DLG_YES_BUTTON");
		
		 if(str == "YES"){
			 IS_YES = 1;
			 IS_CANCEL = 0;
			 eval(func_yes);
			
		 }
			
		if(str == "CAMCEL"){
			 IS_YES = 0;
			 IS_CANCEL = 1;
			  eval(func_cancel);
			  
		}
		IS_SELECTED = 1;
		 return 1;
	}
