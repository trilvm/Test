/*!
 * jx - JavaScript X v1.4
 * Copyright 2011, Baotq 
 * baotq@unitech.com
 */
var jx=new Jx()
var jx_jsList=new Array()
function Jx(){
var xmlhttp
var result
var fn_exec=""
var fn_response=""
var id_loaddiv=""
var id_loadiframe=""
var err_exec=""
this.params=""
this.loadDiv=jx_loadDiv
this.loadIframe=jx_loadIrame
this.getResponse=jx_getHTMLResponse
this.setParameter=jx_setParameter
this.success=jx_success
this.error=jx_error
this.request=jx_requestServer
this.stop=jx_stop
function jx_setParameter(key,parameter){
this.params+="&"+key+"="+parameter}
function jx_success(sfn){
fn_exec=sfn}
function jx_loadDiv(iddiv){
id_loaddiv=iddiv}
function jx_loadIrame(idiframe){
id_loadiframe=idiframe}
function jx_getHTMLResponse(sfn){
fn_response=sfn}
function jx_error(efn){
err_exec=efn}
function jx_requestServer(target,method){
xmlhttp=GetXmlHttpObject()
if(xmlhttp==false){
alert("Browser does not support HTTP Request")
return}
xmlhttp.onreadystatechange=stateChanged
xmlhttp.open(method,target,true)
xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded")
xmlhttp.send(this.params)
this.params=""}

function GetXmlHttpObject(){
if(window.XMLHttpRequest){
return new XMLHttpRequest()}
if(window.ActiveXObject){
return new ActiveXObject("Microsoft.XMLHTTP")}
if(window.ActiveXObject){
return new ActiveXObject("Msxml2.XMLHTTP")}
return false}


function stateChanged(){

if(xmlhttp.readyState==4&&xmlhttp.status==200){
	result=xmlhttp.responseText
	if(fn_response !=""){
		eval(fn_response)(result)
		fn_response=""
	}
	else if(id_loaddiv != ""){
	doLoadDiv(result);
	}
	else if(id_loadiframe != ""){
		var iFrameBody = document.getElementById(id_loadiframe).contentWindow.document.body
		iFrameBody.innerHTML = result
		id_loadiframe=""
	}
	else{
		if(document.getElementById("Jx_DJSPlace") == null)
		{
			createDJSPlace();
		}
		domVariable(result)
	}
	if(fn_exec !=""){
		eval(fn_exec)()
		fn_exec=""
	}
}


if(xmlhttp.readyState==4&&xmlhttp.status==500){
jx_stop()
if(err_exec !=""){
eval(err_exec)()
}
else{
alert("500 Internal Server Error")}}

}

function doLoadDiv(html){
	var div = document.getElementById(id_loaddiv)
	div.innerHTML = html	
	var x = div.getElementsByTagName("script")
	var jsList_temp=new Array()
	for(var i=0;i<x.length;i++)
	{
		if(x[i].text == "")
		{
			if(x[i]["type"] == "text/javascript" && x[i]["src"] != ""){			
				var isInclude = 0;				
				for(var j = 0;j<jx_jsList.length;j++){					
					if(jx_jsList[j] == x[i]["src"]){
					isInclude = 1;
					break;
					}
				}
				if(isInclude == 0){
				jsList_temp[i] = x[i]["src"];
				}
			}			
		}
	}
	jx_loadJs(jsList_temp,0);	
}
function jx_loadJs(arrURL,count)
{	
	if(count<arrURL.length){
	var html_doc = document.getElementsByTagName('head')[0];
    js = document.createElement('script');
    js.setAttribute('type', 'text/javascript');
    js.setAttribute('src', arrURL[count]);
    html_doc.appendChild(js);
	
		js.onreadystatechange = function () {
			if (js.readyState == 'complete') {
			}
		}
		js.onload = function () {
		   var jsListLenght = jx_jsList.length;
		   jx_jsList[jsListLenght + 1] = arrURL[count];
		   if(count<arrURL.length){
				count++;
				jx_loadJs(arrURL,count);
		   }	  
		}
	}else{
		var div = document.getElementById(id_loaddiv)
		var x = div.getElementsByTagName("script")
		for(var i=0;i<x.length;i++)
		{
			if(x[i].text != "")
			{
				eval(x[i].text)				
			}
		}
		id_loaddiv=""
	}    
}

function jx_stop(){
xmlhttp.abort()}


function domVariable(string){
string=string.substring(string.length-2,0)
var ni=document.getElementById("Jx_DJSPlace")
if(ni == null)
{
alert("LỖI :\n KHÔNG TÌM THẤY [Jx_DJSPlace] ")
}else{
ni.innerHTML=""
}
var arr=string.split("&&")
var script=document.createElement("script")
var varjs=""
for(var i=0;i<arr.length;i++){
var arrv=arr[i].split(">")
var vname=arrv[0].split(" ")
switch(vname[0]){
case '01':
varjs+="var "+vname[1]+" = '"+arrv[1]+"';\n"
break
case '02':
varjs+=DeseriallizeToArray(vname[1],arrv[1])+"\n"
break
case '03':
varjs+=ToArray(vname[1],arrv[1])+"\n"
break
case '04':
varjs+=ToBoolean(vname[1],arrv[1])+"\n"
break
case '05':
varjs+="var "+vname[1]+" = "+arrv[1]+";\n"
break
default :
document.writeln(string);
break}}
script.text=varjs
ni.appendChild(script)}


function DeseriallizeToArray(varname,service){
var arrs=service.split("~")
var head=(parseInt(arrs.length)- (parseInt(arrs[0]) +1))/parseInt(arrs[0])
var stringArr="var "+varname+" = new Array("+head+");\n"
var d=1
var ii=0
for(ii;ii<head;ii++){
stringArr+=varname+"["+ii+"] = new Array("+arrs[0]+");\n"
for(var j=0;j<arrs[0];j++){
stringArr+=varname+"["+ii+"]['"+arrs[j+1]+"'] = '"+arrs[d+ii+parseInt(arrs[0])]+"';\n"
if(j<(parseInt(arrs[0])-1)){
d++}}}
return stringArr}

function ToArray(varname,service){
var arrs=service.split("~")
var stringArr="var "+varname+" = new Array();\n"
for(var i=0;i<arrs[0].length;i++){
var par=arrs[i].split(":")
stringArr+=varname+"['"+par[0]+"'] ='"+par[1]+"';\n"}
return stringArr}

function ToBoolean(varname,service){
var varBoolean=""
if(service==''){
varBoolean+="var "+varname+" = false;\n"}
if(service==true){
varBoolean+="var "+varname+" = true;\n"}
return varBoolean}

function createDJSPlace(){
var _body=document.getElementsByTagName('body')[0]
var DSPlace=document.createElement('p')
DSPlace.setAttribute("id","Jx_DJSPlace")
DSPlace.setAttribute("style","display:none")
_body.insertBefore(DSPlace,_body.firstChild)
}}