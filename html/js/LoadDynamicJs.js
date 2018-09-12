function loadJsSrc(doc,src,idjs){
	var headID = doc.getElementsByTagName("head")[0];         
	var newScript = doc.createElement('script');
	newScript.type = 'text/javascript';
	newScript.src = src;
	headID.appendChild(newScript);
}

function loadJsStatement(doc,divid,state){
	
}