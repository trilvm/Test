<?php
/**
 * @author longtv
 * @version 1.0
 * @example 
 */
require_once 'Zend/Controller/Action.php';
include_once 'qtht/models/LichUB.php';
include_once 'qtht/models/nguoidungModel.php';
include_once 'hscv/models/filedinhkemModel.php';
include_once 'auth/models/ResourceUserModel.php';
//require_once 'motcua/models/TrangthaibiadoModel.php';
class qtht_LichUBController extends Zend_Controller_Action {
	
	/**
	 * Ham xu ly cho action index 
	 *
	 */
	function indexAction(){
		$this->_helper->layout->enableLayout();
			
		global $auth;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		
		//tinh chỉnh param
		$realyear = QLVBDHCommon::getYear();
		$model = new LichUB($realyear);
		
		$this->view->title = "Lịch ủy ban";
		//$this->view->subtitle="Danh sách";
		
		$this->view->data = $model->SelectAll();
		$this->view->lock = 1;
		$actid = ResourceUserModel::getActionByUrl('qtht','LichUB','input');
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$actid[0])){	
			$this->view->lock = 0;
			QLVBDHButton::EnableDelete("/qtht/LichUB/Delete");
			QLVBDHButton::EnableAddNew("/qtht/LichUB/Input");
		}
		
	}
	
	/**
	 * Lưu thông tin lịch khi cập nhật hoặc thêm mới
	 */
	function saveAction(){
		$params = $this->getRequest()->getParams();
		$realyear = QLVBDHCommon::getYear();
		$NGAYLAPLICH=$params["NGAYLAPLICH"];
		$NGAYLAPLICH = trim($NGAYLAPLICH);
		$arr = explode('/',$NGAYLAPLICH);
		$NGAYLAPLICH = date('y-m-d',mktime(null,null,null,$arr[1],$arr[0],$arr[2]));
		if($params["ID_LICHUB"]>0){
			$lichub = new LichUB($realyear);
			$olddata = $lichub->find($params["ID_LICHUB"])->current();
			$re = filedinhkemModel::insertFileLich($olddata->FILE_MASO);
			if($re[0]!=""){
				$lichub->update(
					array(
						"TENLICH"=>$params["NAME"],
						"FILE_MASO"=>$re[0],
						"FILE_NAME"=>$re[2],
						"FILE_MIME"=>$re[1],
						"NGUOITAO"=>$params["NGUOITAO"]
					),
					"ID_LICHUB=".((int)$params["ID_LICHUB"])
				);
			}else{
				$lichub->update(
					array(
						"TENLICH"=>$params["NAME"],
						"NGUOITAO"=>$params["NGUOITAO"]
					),
					"ID_LICHUB=".((int)$params["ID_LICHUB"])
				);
			}
			//echo $re;
		}else{
			$lichub = new LichUB($realyear);
			$re = filedinhkemModel::insertFileLich("");
			$id = $lichub->insert(
				array(
					"TENLICH"=>$params["NAME"],
					"FILE_MASO"=>$re[0],
					"FILE_NAME"=>$re[2],
					"FILE_MIME"=>$re[1],
					"NGAYLAPLICH"=>$NGAYLAPLICH,
					"NGUOITAO"=>$params["NGUOITAO"]
				)
			);
		}
		$this->_redirect("/qtht/LichUB/index");
	}
	/**
	 * Ham hien thi khung nhap lieu cho lịch ủy ban
	 */
	function inputAction(){
		$this->view->title="Nhập liệu lịch ủy ban";
		$realyear = QLVBDHCommon::getYear();
		$auth = Zend_Registry::get('auth');
		$id_u = $auth->getIdentity()->ID_U;
		$model=new nguoidungModel();
		$this->view->NGUOITAO=$model->FindnameById($id_u);
		$params = $this->getRequest()->getParams();

		$d = date("d/m");
		$t = time();
		if($d==""){
			$d=getdate();
		}else{
			$t = strtotime(implode("-",array_reverse(explode("/",$d."/".QLVBDHCommon::getYear()))));
			$d =getdate($t);
		}
		if($d['wday']==0)$d['wday']=7;
		$begindate = $t - ($d['wday']-1)*86400;
		
		//get lich du kien
		$begindatestr = getdate($begindate);
		$begindatestr = $begindatestr['year']."-".$begindatestr['mon']."-".$begindatestr['mday'];
		
		$enddate = $begindate+(7*86400)-10;
		$enddatestr = getdate($enddate);
		$enddatestr = $enddatestr['year']."-".$enddatestr['mon']."-".$enddatestr['mday'];
		$this->view->week = date("YW", $begindate);
		$this->view->begindate = date("d/m",$begindate);
		//Lay lich
		$data = array();
		for($i=1;$i<=7;$i++){
			$currentdate_str = date("Y-m-d",$begindate);
			$curentdate_arr = getdate($begindate);
			$data[$i-1][0] = array($curentdate_arr['wday'],date("d/m",$begindate),$begindate);//Thứ
			$begindate += 86400;
		}
		$this->view->enddate = date("d/m",$begindate-1);
	//	var_dump($this->view->week,$this->view->begindate,$this->view->enddate);
		if($params["id"]>0){
			$lichub = new LichUB($realyear);
			$this->view->title = "Cập nhật lịch ủy ban";
			$this->view->data = $lichub->find($params["id"])->current();
			
		}else{
			$this->view->title = "Thêm mới lịch ủy ban";
			
		}
		QLVBDHButton::EnableSave("/qtht/LichUB/save");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
	}
	public function downloadAction(){
		$realyear = QLVBDHCommon::getYear();
		$con = Zend_Registry::get('config');
		$id = $this->_request->getParam('id');
		$lichub = new LichUB($realyear);
		$data = $lichub->find($id)->current();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$data->FILE_MIME ); 
		header( 'Content-Disposition: attachment; filename="'.$data->FILE_NAME.'"' ); 
		header( "Content-Description: Excel output" );
		echo file_get_contents($con->file->root_dir.DIRECTORY_SEPARATOR."lichub".DIRECTORY_SEPARATOR.$data->FILE_MASO); 
		exit;
	}	
	/**
	 * Xóa hàng theo ID_LICHUB
	 */
	public function deleteAction(){
		$realyear = QLVBDHCommon::getYear();
		$lichub = new LichUB($realyear);
		$id = $this->_request->getParam('DEL');
		try{
			$lichub->delete("ID_LICHUB IN (".implode(",",$id).")");
		}catch(Exception $ex){
			$this->_redirect("/qtht/LichUB/index");
		}
		$this->_redirect("/qtht/LichUB/index");
	}
	
}

?>
