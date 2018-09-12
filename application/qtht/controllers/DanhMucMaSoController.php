<?php

require_once ('Zend/Controller/Action.php');
require_once 'qtht/models/gen_masoModel.php';
class Qtht_DanhMucMaSoController extends Zend_Controller_Action {
	function init(){
		$this->view->title = "Quản lý mã số";
	}
	
	function indexAction(){
		QLVBDHButton::EnableAddNew("/qtht/DanhMucMaSo/input");
		QLVBDHButton::EnableDelete("/qtht/DanhMucMaSo/delete");
		$model = new gen_masoModel();
		$this->view->data = $model->getDetail();
		//$this->view->subtitle = "Danh sách";	
	}
	
	function inputAction(){
		//Tao phan button
		QLVBDHButton::EnableSave("/qtht/DanhMucMaSo/save");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
                $this->view->title = "Nhập liệu mã số";
		//lay tham so id
		$id = $this->_request->getParam('id');
		if(!$id) $id = 0;
		$this->view->id = $id;
		if($id > 0){ //truong hop cap nhat
			//lay du lieu trong database
			$model = new gen_masoModel();
			$re = $model->find($id);
			$re = $re->current();	
			$this->view->ten_b = $re->TEN;
			$this->view->loai_b = $re->LOAI;
			$this->view->kieu_b = $re->KIEU;
			
			//var_dump($this->view->kieu_b);
			$this->view->length_b = $re->LENGTH;
			$this->view->order_b = $re->ORDER;
			$this->view->field_b = $re->FIELD;
			$this->view->table_b = $re->TABLE;
			$this->view->value_b = $re->VALUE;
			$this->view->php_func_b = $re->PHP_FUNC;
		}

		
	}
	
	function deleteAction(){
		if($this->_request->isPost())
		{
			$model = new gen_masoModel();
			//Lay id cac van ban can xoa
			$idarray = $this->_request->getParam('DEL');
			//var_dump($idarray);
			//thuc hien xoa cac loai van ban duoc chon
			$where = 'ID_MASO in ('.implode(',',$idarray).')'; 
			try{
				if(!$model->delete($where)){
					$this->_redirect('/default/error/error?control=DanhMucloaivanban&mod=qtht&id=ERR11005008');
				}
			}catch (Exception $ex)
			{
				//Loi trong qua trinh xoa
				$this->_redirect('/default/error/error?control=DanhMucloaivanban&mod=qtht&id=ERR11005008');
			}
			
		}
		//chuyen den trang xem danh sach cac loai van ban
		$this->_redirect('/qtht/DanhMucMaso');
		exit;
	}
	
	function saveAction(){
//lay thong tin ve truong ma so muon cap nhat hay them moi
		$param = $this->_request->getParams();
//lay cac tham so
		$ten = $param["ten"];
		$loai = $param["loai"];
		$kieu = $param["kieu"];
		$length = $param["length"];
		$order = $param["order"];
		$gen_maso = new gen_maso();
		$gen_maso->_ten = $ten;
		$gen_maso->_loai= $loai;
		$gen_maso->_kieu = $kieu;
		$gen_maso->_length = $length;
		$gen_maso->_order = $order;
//default value
		$gen_maso->_is_php_func = 0;
//phan loai loai ma ho so 
		$table ="";
		$arr_field = array();
		switch ($loai){
			case 1:
				$gen_maso->_table = 'vbd_vanbanden';
				break;			
			case 2:
				$gen_maso->_table = 'vbdi_vanbandi';
				break;			
			case 3:
				$gen_maso->_table = 'motcua_hoso';
				break;			
			default:
				$gen_maso->_table = 'hscv_taphoso';
				break;
		}
//phan loai theo kieu
		switch($kieu){
			case 1: // nam
				break;
			case 2: // ma co quan noi bo
				break;
			case 3: // ma tinh thanh noi bo
				break;
			case 4: // so co dinh
				$gen_maso->_value = $param['value'];
				break;
			case 5: // so khong co dinh
				$gen_maso->_is_php_func = $param['is_php_func'];
				$gen_maso->_is_php_func = 1;
				break;
			case 6: // chuoi co dinh
				$gen_maso->_value = $param['value'];
				break;
			case 7 : // chuoi khong co dinh
				$gen_maso->_is_php_func = $param['is_php_func'];
				$gen_maso->_is_php_func = 1;
				break;
			case 8 : //truong co trong csdl	
				$gen_maso->_field = $param['field'];
				break;
            case 9: // nam ban hanh
			     break;
		    case 10: // ngay ban hanh
			     break;
			 case 11: // thang ban hanh
			     break;
			default:
				break;
		}
		$model = new gen_masoModel();
		$id = $this->_request->getParam('id');
		if(!$id) $id = 0;
		$gen_maso->_id_maso = $id;
		if($id > 0){//truong hop cap nhat
			$model->updateOne($gen_maso);
		}else{ //truong hop them moi
			$model->insertOne($gen_maso);
		}	
		
		
		$this->_redirect('/qtht/DanhMucMaSo/index');
	}
}

?>
