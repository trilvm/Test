
function setHeaderDanhmucdonviIndex(){
    var arr=new Array();
    arr[0]="add";
    arr[1]="delete";
    setHeader(ERR_12_08,arr);
}
function DeleteButtonClick(){
	var ln = 0;
	var arr = document.getElementsByName('DEL[]');
	for(var i = 0 ; i < arr.length ;i++ ){
		if(arr[i].checked == true){
			ln = 1;
		}
	}
	if(ln == 1){
		var func_ok = "deleteAction()";
		var func_cancel = "";
		displayConfirm(ERR_12_02,"","",func_ok,func_cancel);
		
	}else{
		alert(ERR_12_01);
	}
}

function deleteAction(){
    if (history && history.pushState) {
        document.frm.action='/qtht/danhmucdonvi/delete';
        document.frm.method='post';
        history.pushState({action:"jxBack"}, ERR_12_13, '/qtht/danhmucdonvi/index');
        submitForm(document.frm,'');
    }else{
        document.frm.action='/qtht/danhmucdonvi/delete';
        document.frm.method='post';
        document.frm.submit();
    }
}
function AddNewButtonClick(){
    if (history && history.pushState) {
      jx.loadInto('div-content');
      jx.request('/qtht/danhmucdonvi/input','POST');
      history.pushState({action:"jxBack"}, ERR_12_13, '/qtht/danhmucdonvi/input');
      
    }else{
        document.frm.action="/qtht/danhmucdonvi/input";
        document.frm.submit();
    }
}
function ItemClick(id){
    if (history && history.pushState) {
      jx.loadInto('div-content');
      jx.setParameter('idCoQuan',id);
      jx.request('/qtht/danhmucdonvi/input','POST');
      history.pushState({action:"jxBack"}, ERR_12_14, '/qtht/danhmucdonvi/input');
    }else{
        document.frm.action="/qtht/danhmucdonvi/input/idCoQuan/"+id;
        document.frm.submit();
    }
}