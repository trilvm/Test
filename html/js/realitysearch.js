/*!
 * realitysearch v1.0
 * Copyright 2013 - Phan Thiet - Binh Thuan 
 * author : TRAN QUOC BAO
 * baotq@unitech.com
 */
var realitysearch = new realitysearch()
function realitysearch(){
	var err_exec=""
	this.params=""
	var arrANSI = new Array(57);	
	arrANSI['á'] = "as";
	arrANSI['à'] = "af";
	arrANSI['ả'] = "ar";
	arrANSI['ã'] = "ax";
	arrANSI['ạ'] = "aj";	
	arrANSI['ă'] = "aw";	
	arrANSI['ắ'] = "aws";
	arrANSI['ằ'] = "awf";
	arrANSI['ẳ'] = "awr";
	arrANSI['ẵ'] = "awx";
	arrANSI['ặ'] = "awj";	
	arrANSI['â'] = "aa";
	arrANSI['ầ'] = "awf";
	arrANSI['ấ'] = "aas";
	arrANSI['ẩ'] = "aar";
	arrANSI['ẫ'] = "aax";
	arrANSI['ậ'] = "aaj";	
	arrANSI['đ'] = "dd";	
	arrANSI['ê'] = "ee";
	arrANSI['ề'] = "eef";
	arrANSI['ế'] = "ees";
	arrANSI['ể'] = "eer";
	arrANSI['ễ'] = "eex";
	arrANSI['ệ'] = "eej";	
	arrANSI['ỉ'] = "iir";
	arrANSI['ì'] = "iif";
	arrANSI['í'] = "iis";
	arrANSI['ị'] = "iij";
	arrANSI['ĩ'] = "iix";	
	arrANSI['ó'] = "as";
	arrANSI['ò'] = "af";
	arrANSI['ỏ'] = "ar";
	arrANSI['õ'] = "ax";
	arrANSI['ạ'] = "aj";	
	arrANSI['ú'] = "us";
	arrANSI['ù'] = "uf";
	arrANSI['ủ'] = "ur";
	arrANSI['ũ'] = "ux";
	arrANSI['ụ'] = "uj";	
	arrANSI['ô'] = "oo";
	arrANSI['ồ'] = "oof";
	arrANSI['ố'] = "oos";
	arrANSI['ổ'] = "oor";
	arrANSI['ỗ'] = "oox";
	arrANSI['ộ'] = "ooj";	
	arrANSI['ơ'] = "ow";
	arrANSI['ờ'] = "owf";
	arrANSI['ớ'] = "ows";
	arrANSI['ở'] = "owr";
	arrANSI['ỡ'] = "owx";
	arrANSI['ợ'] = "owj";	
	arrANSI['ư'] = "uw";
	arrANSI['ừ'] = "uwf";
	arrANSI['ứ'] = "uws";
	arrANSI['ử'] = "uwr";
	arrANSI['ữ'] = "uwx";
	arrANSI['ự'] = "uwj";
	
	this.getKeyword=rl_setKeyword
	function rl_setKeyword(keyword){
		keyword = keyword.toLowerCase();
		var arrKey = keyword.split("");
		var newtext = "";
		for(var i = 0 ; i < arrKey.length; i++ ){
			if(arrANSI[arrKey[i]] != undefined){
				newtext += arrANSI[arrKey[i]];
			}else{
				newtext += arrKey[i];
			}			
		}
		return newtext;
	}
}