<?php
/**
 * @author trunglv
 * @version 1.0
 */
require_once ('Zend/Controller/Action.php');
require_once 'hscv/models/vanbanlienquanmodel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/filedinhkemModel.php';
class Hscv_VanbanlienquanController extends Zend_Controller_Action {
	/**
	 * Ham khoi tao cho tat ca cac action
	 *
	 */
	function init(){
		// disable layout
		$this->_helper->layout->disableLayout();
	}
	/**
	 * Action : index 
	 * Hien thi danh sach cac van ban lien quan tuong ung voi ho so cong viec
	 *
	 */
	function indexAction(){
		
		//var_dump($this->_request->getParams());
		$year = QLVBDHCommon::getYear(); //nam
		$iddivParent = $this->_request->getParam('iddivParent'); //id cua the div dung de hien thi
		$model = new VanBanLienQuanModel($year);
		$this->view->year = $year;
		$this->view->iddivParent = $iddivParent;
		$this->view->type = 0;//mac dinh
		$idHSCV = $this->_request->getParam('idHSCV'); //id ho so cong viec
		$this->view->isNoHSCV = 0; 
		//neu id ho so cong viec = 0 thi tao id ho so cong viec tam 
		if($idHSCV ==0 || !$idHSCV){
			$temp = new gen_tempModel();
			$arr = array();
			$idHSCV = $temp->insert($arr);
			$this->view->isNoHSCV = 1;
		}
		$this->view->idHSCV = $idHSCV;
		$this->view->data = $model->getListByIdHSCV($idHSCV,$year);
		//Kiem tra cac truong hop truy xuat den van ban lien quan
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
			$isreadonly = 0;
		$isCapnhat = 1;
		if($this->view->isNoHSCV == 1){
			$isCapnhat = 1;
		}
		else{if(hosocongviecModel::isLuutru($idHSCV,$year) == true || $isreadonly == 1){
			$isCapnhat = 0;
			$this->view->isreadonly = 1;
			}else{$isCapnhat = 1; }
		}
		$this->view->isCapnhat = $isCapnhat;
	}
	/**
	 * action : input
	 * Tim kiem danh sach cac van ban 
	 */
	function inputAction(){
		//Lay cac tham so duoc nhan tu client
		$param = $this->_request->getParams();
		$this->view->type= 0; //mac dinh
		$this->view->year = QLVBDHCommon::getYear();
		$this->view->idHSCV = $this->_request->getParam('idHSCV');
		$this->view->iddivParent = $param['iddivParent'];
		$this->view->isNoHSCV = $param['isNoHSCV'];
		$this->view->idVBLQ = 0;
		
		$this->view->is_sys = $param["issystem"];
		if(!$this->view->is_sys)
			$this->view->is_sys = 0; // neu khong co tham so, mac dinh la van ban ben ngoai he thong
		
		//Neu la phuong thuc post (client goi cac tieu chi tim kiem )
		$this->view->type = (int)$param["choiceLoaiVB"];
		
		if($this->_request->isPost()){
			$trichyeu = $param['searchTrichYeu'];
			$sokyhieu = $param['searchSoKyHieu'];
			
			$year = QLVBDHCommon::getYear();
			$idHSCV = $param['idHSCV'];
			$type = $param["choiceLoaiVB"];
			$s_tieuchi = $param["s_tieuchi"];
			$this->view->type= $type;
			$model = new VanBanLienQuanModel($year);
			//Tra du lieu tim kiem duoc cho lop view
			$this->view->data = $model->findListVanBan($idHSCV,$year,$type,$trichyeu,$sokyhieu,$s_tieuchi);
			$this->view->trichyeu = $trichyeu;
			$this->view->sokyhieu = $sokyhieu;
			$this->view->type = $type;
			if(!$this->view->type)
				$this->view->type = 0;
			$this->view->s_tieuchi = $s_tieuchi;
			if(!$this->view->s_tieuchi)
				$this->view->s_tieuchi = 0;
			//var_dump(); 
			
		}
	}
	/**
	 * action : save
	 * Them moi van ban lien quan vao ho so cong viec
	 *
	 */
	function saveAction(){
		$param = $this->_request->getParams();
		$idvblq = $param['idvblq'];
		if(!$idvblq) $idvblq = 0;
		$idHSCV = $param['idHSCV'];
		$iddivParent = $param['iddivParent'];
		$type = $param['type'];
		$year = QLVBDHCommon::getYear();
		$nosystem = $param['nosystem'];
		$model = new VanBanLienQuanModel($year);
		
		
		if($idvblq == 0){//truong hop them moi
		if($nosystem ==0){ //van ban nam ben trong  he thong
			//lay danh sach cac id van ban muon them 
			//$id_vb = $param['idVB'];
			$arr_idvb = $param['DELidaddvanbanlienquan'.$idHSCV];
			//varr_dump($arr_idvb);
			//exit;
			for($i =0 ;$i<count($arr_idvb);$i++)
				$model->insertOneOnSys($year,$idHSCV,$arr_idvb[$i],$type); // thuc hien them moi
			//Tra ve doan js , cap nhat lai danh sach hien thi cac van ban lien quan ben client
			
			
		}else{ //van ban nam ben ngoai he thong
			
			//var_dump($param);
			//exit;
			$name_vb = $param['txtNameVB'];
			//them moi van ban lien quan
			if(!$name_vb || $name_vb=="")
				$name_vb = "Văn bản liên quan mới"; 
			$data = array(
			'NAME'=>$name_vb,
			'ISSYSTEM' => 0,
			'ID_HSCV' => $idHSCV
			) ;
			$idvblq = $model->insert($data);
			
			//cap nhat file dinh kem
			$filedinhkem = new filedinhkemModel($year);
			for($i=0;$i<count($param["idFile"]);$i++)
			 $filedinhkem->update(array("ID_OBJECT"=>$idvblq,"TYPE"=>4),"MASO='".$param["idFile"][$i]."'");
			//echo "alert('".var_dump($param['idFile'][0])."')";
		
		}
		}else{ //truong hop cap nhat -  cap nhat ten 
			$name_vb = $param['txtNameVB'];
			$where = 'ID_VBLQ='.$idvblq;
			try{
				$data = array(
				'NAME'=>$name_vb,
				) ;
			    $idvblq = $model->update($data,$where);
			}catch(Exception $ex){
				//exit;
			}
			
			echo 'loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanlienquan?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		}
		echo 'window.parent.loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanlienquan?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		exit;
	}
	/**
	 * action : delete
	 * Thuc hien xoa cac van ban lien quan trong ho so cong viec
	 *
	 */
	function deleteAction(){
		//Lay cac tham so ben client goi ve
		$param = $this->_request->getParams();
		$idHSCV = $param['idHSCV'];
		$iddivParent = $param['iddivParent'];
		$year= QLVBDHCommon::getYear();
		$model = new VanBanLienQuanModel($year);
		//lay danh sach cac id cua van ban lien quan muon xoa
		$arr_id = $param['DELidvanbanlienquan'.$idHSCV];
		for($i = 0 ; $i < count($arr_id); $i++){
			$model->deleteOne($arr_id[$i]);
		}
		//Tra ve doan js , cap nhat lai danh sach hien thi cac van ban lien quan ben client
		echo 'loadDivFromUrl("'.$iddivParent.'","/hscv/Vanbanlienquan?idHSCV='.$idHSCV.'&iddivParent='.$iddivParent.'&year='.$year.'",1);';
		exit;
	}
}

?>
