<?php
/**
 * @name DanhMucCoQuanController
 * @author trunglv
 * @package qtht
 * @version 1.0
 */
require_once ('Zend/Controller/Action.php');
require_once 'qtht/models/CoQuanModel.php';
require_once 'Common/common.php';
require_once 'Common/button.php';
require_once 'Common/ValidateInputData.php';
require_once 'config/qtht.php';
require_once 'nusoap/nusoap.php';

class Qtht_DanhMucCoQuanController extends Zend_Controller_Action {

	var $coquanTable; // doi tuong bang co quan
        var $iscode=true;
        var $codehere=true;
	
	/**
	 * Ham khoi tao du lieu cho controller
	 */
	public function init()
	{
		$this->coquanTable = new CoQuanModel();
		$this->view->title = "Danh Mục Cơ Quan";
	}
	
	/**
	 * Ham xu ly cho action xem danh sach bang co quan
	 */
	
	public function indexAction()
	{
		$this->view->data = array();
		QLVBDHButton::EnableAddNew("/qtht/DanhMucCoQuan/input");
		QLVBDHButton::EnableDelete("/qtht/DanhMucCoQuan/delete");
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
		$search = "%".$search."%";
		QLVBDHCommon::GetTreeByName($search,'NAME',&$this->view->data,"vb_coquan","ID_CQ","ID_CQ_PARENT",1,1);
        
        $synchronousStatus = $this->_request->getParam("synchronousStatus");
        if(isset($synchronousStatus)){
            $this->view->synchronousStatus = $synchronousStatus;
            $this->view->update = $this->_request->getParam("update");
            $this->view->insert = $this->_request->getParam("insert");
        }else{
            $this->view->synchronousStatus = 3;
        }
	}
	
	/**
	 * Ham xu ly cho action them moi hoac cap nhat thong tin co quan
	 *  
	 */
	
	public function saveAction()
	{
		//$this->view->data = array();//$this->coquanTable->fetchAll();
		QLVBDHCommon::GetTree(&$this->view->data,"vb_coquan","ID_CQ","ID_CQ_PARENT",1,1);
		
		
		if($this->_request->isPost())
		{
			//Lay du lieu post len tu form nhap lieu
		 	$phamvi = $this->_request->getParam("phamvi");
			if(!$phamvi)
				$phamvi = 0;
			$kyhieu = $this->_request->getParam("kyhieu");	
		 	$name = $this->_request->getParam("name");
			$id = $this->_request->getParam("idCoQuan");
			$parent = $this->_request->getParam("choiceCQCha");
			$capcq = $this->_request->getParam("choiceCapCQ");
			$code = $this->_request->getParam("code");
			$email = $this->_request->getParam("email");
			//Kiem tra du lieu hop le
			$this->checkInputData($name,$phamvi,$parent,$capcq) ;
			if($id>1){
				//Truong hop cap nhat du lieu
				$where = 'ID_CQ='.$id;
				try{
					$this->coquanTable->update(array('NAME'=>$name,'CODE'=>$code ,'ISSYSTEMCQ'=>$phamvi , "ID_CQ_PARENT" =>$parent,"CAPCQ"=>$capcq,"EMAIL"=>$email,"KYHIEU"=>$kyhieu),$where);
					$this->_redirect('/qtht/DanhMucCoQuan');
				}catch (Exception $e){
					//cap nhat co so du lieu khong thanh cong
					$this->_redirect('/default/error/error?control=DanhMucCoQuan&mod=qtht&id=ERR11013008');
				}	
			
			}else{
				//Truong hop them moi du lieu
				$arr_newdata = array("NAME"=> $name , "ISSYSTEMCQ" => $phamvi , 'CODE'=>$code,"ID_CQ_PARENT" =>$parent ,"CAPCQ"=>$capcq,"KYHIEU"=>$kyhieu);
				try{
					$this->coquanTable->insert($arr_newdata);
				}catch (Exception $ex){
					//Loi khong the them co quan vao csdl
					$this->_redirect('/default/error/error?control=DanhMucCoQuan&mod=qtht&id=ERR11013009');
				}
			}
			//chuyen den trang xem danh sach danh muc co quan
			$this->_redirect('/qtht/DanhMucCoQuan');
		}else {
			//chuyen den trang nhap lieu
			$this->renderScript("DanhMucCoQuan/InputData.phtml");
		}
	}
	
	/**
	 * Ham xu ly cho action xoa mot co quan
	 */
	public function deleteAction()
	{
		if($this->_request->isPost())
		{	
			// Lay id cua cac co quan can xoa
			$idarray = $this->_request->getParam('DEL');
			
			//Thuc hien xoa cac co quan
			$where = 'ID_CQ in ('.implode(',',$idarray).')';
			
			try{
				if(!$this->coquanTable->delete($where)) 
				{
					//Khong the xoa
					$this->_redirect('/default/error/error?control=DanhMucCoQuan&mod=qtht&id=ERR11013010');
				}
				
			}catch (Exception  $e)
			{	
				$this->_redirect('/default/error/error?control=DanhMucCoQuan&mod=qtht&id=ERR11013010');
				//Xoa co quan that bai
			}
			//chuyen den trang xem danh sach danh muc co quan
			$this->_redirect('/qtht/DanhMucCoQuan');
		}
		else 
		{
			$this->_redirect('/qtht/DanhMucCoQuan');
		}
	}
	
	/**
	 * Ham xu ly cho action hien thi form nhap lieu cho nguoi dung
	 */
	public function inputAction()
	{
		
		$this->view->data = array();
		QLVBDHCommon::GetTree(&$this->view->data,"vb_coquan","ID_CQ","ID_CQ_PARENT",1,1);
		QLVBDHButton::EnableSave("/qtht/DanhMucCoQuan/save");
		QLVBDHButton::AddButton("Trở lại","","BackButton","BackButtonClick();");
		$this->view->page = $this->_request->getParam('page');
		$this->view->limit = $this->_request->getParam('limit');
		$this->view->filter_object = $this->_request->getParam('filter_object');
		$this->view->search = $this->_request->getParam("search");	
		if($this->_request->isGet())
		{
			// Lay id cua co quan can cap nhat
			$idCapNhat = $this->_request->getParam('idCoQuan');
			if($idCapNhat>0){
				//Truong hop cap nhat du lieu
				//Kiem tra id cua co quan co ton tai hay khong
				$rowcn = $this->coquanTable->find($idCapNhat);		
				if($rowcn->count() == 0){
						//Khong tim thay record trong csdl
						$this->_redirect('/default/error/error?control=DanhMucCoQuan&mod=qtht&id=ERR11013008');
				}
				else
				{   $this->view->kyhieu = $rowcn->current()->KYHIEU;
					$this->view->namebefore = $rowcn->current()->NAME;
					$this->view->codebefore = $rowcn->current()->CODE;
					$this->view->isnoibo = $rowcn->current()->ISSYSTEMCQ;
					$this->view->ID_CQ_PARENT = $rowcn->current()->ID_CQ_PARENT;
					$this->view->id= $idCapNhat;
					$this->view->cap =$rowcn->current()->CAPCQ;	
					$this->view->emailbefore =$rowcn->current()->EMAIL;	
					
				}
				
				$this->view->title="Cập nhật cơ quan";
				$this->view->action="capnhat";
			}
			else{
				$this->view->title="Thêm mới cơ quan";
				$this->view->action="themmoi";
				//truong hop them moi
			}
			//Hien trang nhap lieu 
			$this->renderScript("DanhMucCoQuan/InputData.phtml");
			
			
		}else{
			$this->_redirect('/qtht/DanhMucCoQuan');
		}
			
	}
	
	private function checkInputData($name,$phamvi,$parent,$capcq){
		$strurl='/default/error/error?control=DanhMucCoQuan&mod=qtht&id=';
		$strerr = "";
		$strerr .= ValidateInputData::validateInput('text128_re',$name,"ERR11013001").",";
		$strerr .= ValidateInputData::validateInput('boolean',$phamvi,"ERR11013006").",";
		$strerr .= ValidateInputData::validateInput('int',$parent,"ERR11013007").",";
		if(strlen($strerr)!=3){
			$this->_redirect($strurl.$strerr);
		}
		return true;
	}

    public function dongbocoquanAction(){
       
        $config     = new Zend_Config_Ini('../application/config.ini','general',true);
        $arrLogin   =  array(
            "madonvi" => $config->service->lienthong->username,
            "password" => $config->service->lienthong->password
        );
        
        $client     = new SoapClient($config->service->lienthong->uri);
        $session    = $client->__call('Login',$arrLogin);
       
        $param = array(
            'session'  =>  $session,
            'service_code' => 'DONGBODANHMUC',
            'service_name' => 'DV',
            'parameter' => 'DV'
        );
        
        $result=$client->__call('Execute', $param);
        
        $rs = ServicesCommon::DeseriallizeToArray(base64_decode($result));
        
        $datadb = $this->coquanTable->getAllCoQuan();
        $count = count($rs);
        $status = 0;
        try{
            $updateCount = 0;
            $insertCount = 0;
            for($i = 0 ;$i < $count;$i++){
                if(in_array($rs[$i]['MADONVI'],$datadb))
                {
                    if($rs[$i]['NAME'] !=''){
                        $where = 'CODE="'.$rs[$i]['MADONVI'].'"';
                        $idupdate=$this->coquanTable->update(array(
                            'NAME'=>$rs[$i]['NAME'],
                            'LIENTHONG'=>1,
                            'NHOM'=>$rs[$i]['NHOMDONVI'],
                            'CODE_PARENT'=>$rs[$i]['CODE_PARENT'],
                            'IS_DVTT'=>(int)$rs[$i]['IS_DVTT'],
                        ),$where);
                        if(count($idupdate)>0){
                            $updateCount++;
                        }
                    }
                }else{
                    
                    if($rs[$i]['NAME'] !=''){
                        $idinsert=$this->coquanTable->insert(
                            array(
                                "NAME"=> $rs[$i]['NAME'],
                                "ISSYSTEMCQ" => 0,
                                "CAPCQ"=>1,
                                "EMAIL"=> $rs[$i]['EMAIL'],
                                "CODE"=> $rs[$i]['MADONVI'],
                                "MADONVI_SERVICE"=> $rs[$i]['MADONVI'],
                                "ID_DV_SERVICE"=> $rs[$i]['ID_DONVI'],
                                "LIENTHONG"=> 1,
                                "ID_CQ_PARENT"=> 1,
                                "ID_DV_PARENT_SERVICE"=>$rs[$i]['ID_DV_PARENT'],
                                'NHOM'=>$rs[$i]['NHOMDONVI'],
                                'CODE_PARENT'=>$rs[$i]['CODE_PARENT'],
                                'IS_DVTT'=>(int)$rs[$i]['IS_DVTT'],
                            )
                        );
                        if(count($idinsert)>0){
                             $insertCount++;
                        }
                    }
                }
            }
            $status = 1;
        }catch (Exception  $ex){
            $status = 2;
            echo $ex->__toString();exit;
        }
        $this->_redirect('/qtht/DanhMucCoQuan/index/synchronousStatus/'.$status.'/update/'.$updateCount.'/insert/'.$insertCount);
    }
}


