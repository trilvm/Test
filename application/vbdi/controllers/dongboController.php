<?php

/**
 * BanHanhController
 * 
 * @author truongvc
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/VanBanDuThaoModel.php';
require_once 'vbdi/models/VanBanDiModel.php';
require_once 'qtht/models/MenusModel.php';
require_once 'qtht/models/UsersModel.php';
require_once 'qtht/models/DepartmentsModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
require_once 'qtht/models/CoQuanModel.php';
require_once 'qtht/models/LinhVucVanBanModel.php';
require_once 'qtht/models/SoVanBanModel.php';
require_once 'vbdi/models/vbdi_dongluanchuyenModel.php';
require_once 'vbdi/models/BanHanhForm.php';
//require_once 'vbdi/models/dongboModel.php';
// hieuvt
require_once 'hscv/models/hosocongviecModel.php';
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';

require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once 'config/vbdi.php';
// end hieuvt
class vbdi {
	var $_id_lvb;
	var $_id_cq; 
	var $_domat;
	var $_dokhan;
	var $_sodi; 
}

class Vbdi_dongboController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	
	public function indexAction() 
	{
		$this->view->title = "Danh sách văn bản Đi";
		//$this->view->subtitle="Danh sách";
		QLVBDHButton::EnableAddNew("/vbdi/dongbo/input");
		QLVBDHButton::EnableDelete("/vbdi/dongbo/delete");
		//Doc du lieu de hien thi len view
		$config = Zend_Registry::get('config');
		$page = $this->_request->getParam('page');
		$year =  QLVBDHCommon::getYear();
		$limit = $this->_request->getParam('limit');
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$filter_object = $this->_request->getParam('filter_object');
		$model=new VanBanDiModel($year);
		$this->view->filter_object = $filter_object; 
		$search = $this->_request->getParam("search");
		$this->view->search = $search;
		$order = 'SODI DESC';
		$this->view->data = $model->SelectAll(($page-1)*$limit,$limit,$search,$filter_object,$order);	
		$n_rows = $model->count($search,$filter_object);
		$this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frmListBanHanhs",$page) ;
		$this->view->limit=$limit;
		$this->view->page=$page;
	}

	function callimportAction ()
	{   // echo  base64_encode(file_get_contents("C:\\Upload\\2010\\4\\1198bb84af488dceb890c97f5bcfa6fb"));exit;  
		$params = $this->_request->getParams();		
	       // echo $params["id"];exit;
			$this->_helper->layout->disableLayout();
			$year =  QLVBDHCommon::getYear();
			require_once('nusoap/nusoap.php');			
			$client = new nusoap_client("http://www.danangcity.gov.vn/serviceVBCDDH/ServiceVBCDDH_DN?WSDL",true);
			$client->soap_defencoding = 'utf-8';			
			$model = new VanBanDiModel($year);
			$data =$model->getvbdi($params["id"]);			
			foreach($data as $row){					
			$array_import_lvb=array("param_kyhieu"=>$row["KH_LOAI"]);
            $array_import_lvvb=array("param_kyhieu"=>$row["KH_LINHVUC"]);
			$array_import_cq=array("param_kyhieu"=>$row["KH_COQUAN"]);
			$lvb =  $client->call('getIdDocTypeByShortName',$array_import_lvb);			
			$lvvb =  $client->call('getIdDocScopeByShortName',$array_import_lvvb);			
			$cq =  $client->call('getIdOfficePublishmentByShortName',$array_import_cq);			
			$data_file =$model->getfile($params["id"]);
		   foreach($data_file as $item){
			$file = $item["FOLDER"].'\\'.$item["MASO"];	
			//echo $file;exit;
			$data_content= file_get_contents($file);
			//echo $data_content;exit;
			$array_import = array($row["SOKYHIEU"],$row["TRICHYEU"],QLVBDHCommon::MysqlDateToVnDate($row['NGAYBANHANH']),"","",$data_content,$row["NGUOIKY"],$lvb,$lvvb,$cq); 			
			$result_import =  $client->call('importDocument',$array_import);
			if($result_import > 0){
            $where_id="ID_VBDI=".$params["id"];
		    $model->getDefaultAdapter()->update(QLVBDHCommon::Table('vbdi_vanbandi'),array(
               "ID_DONGBO"=>$result_import,
               "IS_DONGBO"=>1             
		  ),$where_id);			            			
              $array_attach = array("p_iOfficialDocID"=>$result_import,"p_strFileName"=>$item["FILENAME"],"p_strFileType"=>$item["MIME"],"p_iFileSize"=>strlen($data_content),"p_strFileContent"=> base64_encode($data_content)); 
						
			$result_attach =  $client->call('attachAttachmentFile',$array_attach);
			if($result_attach)
			print_r($result_attach);
			else echo -5;exit;
			exit; 
			}else{
				echo -4;exit;
			}			
		   }
		   }
	        //$array_import = array("221-STTTT","Sở Thông Tin Truyền Thông","01/04/2010","01/04/2010","","thunghiem","thunghiem",696,425,794); 
			//echo = $client->getError();
			//$result =  $client_import->call('importDocument',$array_import);
			//$array_import=array("param_kyhieu"=>"NĐ");
			//$result =  $client_import->call('getAllDocTypeFormatJSON',$array_import);		   
			//print_r($result);
			//echo $client_import->responseData;
			//echo "dd";
			//exit; 
	}
	function callimportthuhoiAction ()
	{   // echo  base64_encode(file_get_contents("C:\\Upload\\2010\\4\\1198bb84af488dceb890c97f5bcfa6fb"));exit;  
		$params = $this->_request->getParams();
	      // echo $params["id"];exit;
			$this->_helper->layout->disableLayout();
			$year =  QLVBDHCommon::getYear();
			require_once('nusoap/nusoap.php');			
			$client = new nusoap_client("http://www.danangcity.gov.vn/serviceVBCDDH/ServiceVBCDDH_DN?WSDL",true);
			$client->soap_defencoding = 'utf-8';			
			$model = new VanBanDiModel($year);
			$array_import= array("idDoc"=>$params["id"]);
			$result_import =  $client->call('deleteDocumentById',$array_import);            
			$where_id="ID_DONGBO=".$params["id"];
	        $model->getDefaultAdapter()->update(QLVBDHCommon::Table('vbdi_vanbandi'),array(               
              "IS_DONGBO"=>0             
		  ),$where_id);           	
			print_r($result_import);
			exit;	        
	}
	/**
	 * ban hanh van ban
	 *
	 */
	function saveAction()
	{
		global $db;
		//lay thong tin user luu tren session
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		//Lay du lieu tu client
		$formData = $this->_request->getPost();	
		$id=(int)$this->_request->getParam("id",0);
    	$id_duthao=(int)$this->_request->getParam("idduthao",0);
    	$year = QLVBDHCommon::getYear();
    	
    	/* Tao ma so cho van ban*/
    	$vbdi = new vbdi();
        $vbdi->_id_lvb = $formData['ID_LVB'];
        $vbdi->_id_cq = $formData['ID_CQ'];
        $vbdi->_sodi = $formData['SODI'];
        $vbdi->_domat = $formData['DOMAT'];
        $vbdi->_dokhan = $formData['DOKHAN'];
        $MASOVANBAN = "";
        $MASOVANBAN = Common_Maso::getMaSo(2,$vbdi);
        /*Ket thuc tao ma so cho van ban*/
    	/* Lay du lieu cu cua van ban de tinh so di */
        $old_data = array();
		$arr_col = array();
		$len_arr = 0;
		if($id > 0) //truong hop cap nhat
		{
			$old_data = VanBanDiModel::getDetail($id,$year);
			$arr_col = Common_Sovanban::getColumNameGroup(2);
			$len_arr = count($arr_col);	
		}
        
        /* Kiem tra so di hop le*/
		$sodi = trim($formData['SODI']);
		$array = array();
		$array["ID_CQ"] = $formData['ID_CQ'];
		$array["ID_LVB"] = $formData['ID_LVB'];
		$array["ID_SVB"] =$formData['ID_SVB'];
		$sql_getsodi= Common_Sovanban::checkExistsSodiSQL($year,$array,$sodi);
		$vbdi = new VanBanDiModel($year);		        	                	                
        //echo $sql_getsodi;
		//exit;
		/* Lock bang vbdi_vanbandi lai */
		$sql = 'LOCK TABLE vbdi_vanbandi_'.$year .' WRITE';
		
		/* bat dau dong bo van ban di*/
		//$conn = $vbdi->getDefaultAdapter()->getConnection()->exec($sql);
		/* bat dau dong bo van ban di*/
		
		$is_checked_up = 0;
		if($id > 0){ //truong hop cap nhat
			$old_sodi = $old_data["SODI"];
			$old_cq = $old_data["ID_CQ"];
			$aff_old = 0;
			$col_name = "";
			foreach ($arr_col as $itcol){
				switch ($itcol)
				{
					case 'ID_SVB':
						$aff_old = $old_data["ID_SVB"];
						$col_name='ID_SVB';
					break;
					case 'ID_LVB':
						$aff_old = $old_data["ID_LVB"];
						$col_name='ID_LVB';
					break;
					default:
					break; 
				}
			}
			if($old_cq == $formData["ID_CQ"] && $aff_old == $formData[$col_name]){
				if($sodi == $old_sodi)
					$is_checked_up = 1;
			}
			
		}
		if($is_checked_up == 0){
			$sodi_re = $vbdi->getDefaultAdapter()->getConnection()->query($sql_getsodi);
			$cur_sodi_row = $sodi_re->fetch();
			$cur_sodi = $cur_sodi_row['DEM'];
			if($cur_sodi > 0){
			$vbdi->getDefaultAdapter()->closeConnection();
			echo 2;
			exit;
		}
		}
		
		/*Ket thuc kiem tra so di*/
        
    	//if($sodi <= $cur_sodi && $is_checked_up == 0){
			//loi xay ra khi cap nhat du lieu	
    		/* ket thuc dong bo van ban di*/
			//$vbdi->getDefaultAdapter()->closeConnection();
			//echo 2;
    		//exit; //ket thuc tai day
		//}
		
		//Loc du lieu nhan duoc
		if($formData['NGAYBANHANH']!=null)
			//doi thanh format cua csdl
			$ngaybanhanh=implode("-",array_reverse(explode("/",$formData['NGAYBANHANH'])));
		
		$data = array(
	                    'ID_LVB' 		=> $formData['ID_LVB'],
	                    'ID_HSCV' 		=> (trim($formData['idhscv']) == ""?null:$formData['idhscv']),
	                	'ID_CQ' 	=> $formData['ID_CQ'],
	                	'NGUOITAO' 		=> $formData['NGUOITAO'],
	                	'NGUOIKY' 		=> $formData['NGUOIKY'],
	                	'ID_LVVB' 	=> $formData['ID_LVVB'] ,
	                	'ID_SVB'	=> $formData['ID_SVB'],
	                	'SOKYHIEU'=> $formData['SOKYHIEU'],
	                	'TRICHYEU'		=> $formData['TRICHYEU'],
	                	'SODI' 	=> (trim($formData['SODI']) == ""?0:$formData['SODI']),
	                	'SOBAN' 	=> (trim($formData['SOBAN']) == ""?0:$formData['SOBAN']),
	                	'SOTO' 	=> (trim($formData['SOTO']) == ""?0:$formData['SOTO']),	 	 	                    
	                	'DOKHAN' 	=> $formData['DOKHAN'],	 
	                	'DOMAT' 	=> $formData['DOMAT'],
	                	'NGAYTAO'  => $today,
						'NGUOISOAN'  => $formData['NGUOISOAN'],
	                	'NGAYBANHANH' 	=> $ngaybanhanh,	 	 	                     	 
	                	'MASOVANBAN' => $MASOVANBAN,
	                	'SODI_IN' 	=> (trim($formData['SODI_IN']) == ""?0:$formData['SODI_IN']),
	                	'SOKYHIEU_IN' 	=> (trim($formData['SOKYHIEU_IN']) == ""?0:$formData['SOKYHIEU_IN'])
	                );
		
		
		
		
		//neu khong co loi
		if($id>0){
        	try{
				$where="ID_VBDI=".$id;
	        	$vbdi->update($data,$where);	
        	}catch(Exception $ex){
        		/* ket thuc dong bo van ban di*/
				$vbdi->getDefaultAdapter()->closeConnection();
        		echo 0;
        		exit; //ket thuc tai day
        	}
		}
        else 
        {
        	try{
        		$id=$vbdi->insert($data);
				if($formData['idhscv']>0){
					$usermodel = new UsersModel();
					$nguoikyname = $usermodel->getName($formData['NGUOIKY']);
					//echo $nguoikyname;exit;
					$db->insert(QLVBDHCommon::Table("MOTCUA_KETQUA"),array("ID_HSCV"=>$formData['idhscv'],"NGAYKY"=>$ngaybanhanh,"NGUOIKY"=>$nguoikyname["TENNGUOITAO"],"SOKYHIEU"=>$formData['SOKYHIEU']));
				}
        	}catch (Exception $ex){
        		/* ket thuc dong bo van ban di*/
				//$vbdi->getDefaultAdapter()->closeConnection();
				//echo $ex->__toString();
        		echo 0;
        		exit; //ket thuc tai day
        	}
			//$id = $vbdi->getDefaultAdapter()->insert('vbdi_vanbandi_2009',$data);
			//cap nhat trang thai cua du thao
			
			
        }
		
        /* ket thuc dong bo van ban di*/
		$vbdi->getDefaultAdapter()->closeConnection();
		
		//if($id == 0){
			$vbduthao = new VanBanDuThaoModel($year);
			$id_hscv = trim($formData['idhscv']) == ""?null:$formData['idhscv'];
			if($id_hscv >0){
				$vbduthao->updateTrangthaiByIdDuthao($id_duthao,2);
				if($formData['THUMUC']>0){
					$hscv = new hosocongviecModel(QLVBDHCommon::getYear());
					$hscv->update(array("ID_THUMUC"=>$formData['THUMUC']),"ID_HSCV=".$id_hscv);
				}
			}
		//}
		
		
		/* Luu, cap nhat chuyen de biet*/
		
		
		$lc = new vbdi_dongluanchuyenModel($year);
        if($id > 0){
        	$arr_old_keep = $formData['ID_OLD'];
		    
        	$old_lcs = vbdi_dongluanchuyenModel::way($year,$id);
			
		    $old_delete = array() ;
		    foreach ($old_lcs as $old_lc){
		    	$ln = 1;
				
		    	foreach ($arr_old_keep as $lc_keep){
					if($old_lc["ID_VBDI_DLC"] == $lc_keep)
						$ln = 0;
				}
				if($ln == 1)
					array_push($old_delete,$old_lc["ID_VBDI_DLC"]);
		    		
			}
		    
			//var_dump($old_delete); 
			foreach ($old_delete as $itdel){
			try 
			{
					$where = 'ID_VBDI_DLC = ' . $itdel;
		          	$lc->delete($where);
					
			}
			catch(Exception $er){
				//echo $er->__toString();	
			};	
		    }
		   
        }
		
		//Thêm mới vào dòng luân chuyển
		try 
		{
			
			$arr_user_re =$formData['ID_U']; 
			if(!is_array($arr_user_re))
				$arr_user_re = array();
			$lc->send($id,$arr_user_re,$formData['NOIDUNG'],$user->ID_U);
			//echo $arru[$counter];
			
		}
		catch(Exception $er)
		{
		}
		
		/* Ket thuc Luu, cap nhat chuyen de biet*/
		
		/*File dinh kem */
        $filedinhkemModel = new filedinhkemModel($year);
		$arrayIdFile=$formData['idFile'];
        for($i=0;$i<count($arrayIdFile);$i++)
	    {   
    		$filedinhkemModel->update(array("ID_OBJECT"=>$id,"TYPE"=>5),"MASO='".$arrayIdFile[$i]."'");				  
		}
		
		/*Ket thuc File dinh kem */
		echo 1;
		exit;
		
       		
	}
	function inputAction()
	{
		//Thêm các button
		QLVBDHButton::EnableSave("vbdi/banhanh/input");
		QLVBDHButton::EnableBack("vbdi/banhanh/list");
		//lay tham so
		$param = $this->_request->getParams();
		$year = QLVBDHCommon::getYear();
		$id = $param["id"];

		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$this->view->name_u = UsersModel::getEmloyeeNameByIdUser($user->ID_U) ;
		$dep_u = UsersModel::getUserDepId($user->ID_U);
		$this->view->id_dep_u = $dep_u["ID_DEP"];
		if($id > 0){
		//truong hop cap nhat
			$this->view->title = "Cập nhật văn bản Đi";
			//$this->view->subtitle="Cập nhật";
			//Lay thong tin ve van ban di	
			$this->view->data = VanBanDiModel::getDetail($id,$year);
			$this->view->idhscv = $this->view->data["ID_HSCV"];
			$dongluanchuyen_vbdi = new vbdi_dongluanchuyenModel($year);
    		//$array_chuyendebiet=$dongluanchuyen_vbdi->findAllNguoiNhan($id);
    		$array_chuyendebiet = $dongluanchuyen_vbdi->way($year,$id);
			if(count($array_chuyendebiet)>0)
			{
				//var_dump($array_chuyendebiet);
				$this->view->data_chuyendebiet=$array_chuyendebiet;
			}
		}else{
		//truong hop them moi
			$this->view->title = " Thêm mới văn bản Đi";
			//$this->view->subtitle="Thêm mới";
			//Tao mot so gia tri mac dinh
			$data = array();
			$data["DOKHAN"] = 1;
			$data["DOMAT"] = 1;
			//$data["ID_CQ"] = ;
			$id_duthao = $param["idduthao"];
			if($id_duthao>0)
    		{
	    		$this->view->title = "Ban hành văn bản dự thảo";
	    		//$this->view->subtitle ="Ban hành";
	     		$duthaoModel = new VanBanDuThaoModel($year);
	    		$getDuthao=$duthaoModel->fetchRow("ID_DUTHAO=".$id_duthao);
	     		if($getDuthao!=null)
	     		{
	     			$this->view->idduthao=$getDuthao->ID_DUTHAO;
					$this->view->idhscv=$getDuthao->ID_HSCV;
					$data["TRICHYEU"]=$getDuthao->TENDUTHAO;
					$data["ID_LVB"]=$getDuthao->ID_LVB;
					$r = $duthaoModel->getAdapter()->query("SELECT * FROM VB_SOVANBAN WHERE ID_LVB=? AND TYPE=2",$getDuthao->ID_LVB);
					
					$svb = $r->fetch();
					$data["ID_SVB"]=$svb["ID_SVB"];			
					$data["NGUOIKY"]=$getDuthao->NGUOIKY;
					$data["NGUOISOAN"]=$getDuthao->NGUOISOAN;
					$array_lcdb = array();
					$i = 0;
					if($data["NGUOITAO"] > 0){
						//$array_lcdb[$i] = array();
						//$array_lcdb[$i]["NG"] = $user->ID_U;
						//$array_lcdb[$i]["EMPNN"] = UsersModel::getEmloyeeNameByIdUser($data["NGUOITAO"]);
						//$i++;
					}
					if($data["NGUOIKY"] > 0){
						$array_lcdb[$i] = array();
						$array_lcdb[$i]["NGUOINHAN"] = $data["NGUOIKY"];
						$array_lcdb[$i]["EMPNN"] = UsersModel::getEmloyeeNameByIdUser($data["NGUOIKY"]);
						$dep_data = UsersModel::getUserDepId($data["NGUOIKY"]);
						$array_lcdb[$i]["DEP_NN"] = $dep_data["ID_DEP"];
						
					}
					$this->view->cdb_macdinh=$array_lcdb;
					
	     		}
	     		$this->view->id_duthao  = $id_duthao;
	     		//check xem co the luu thu muc duoc khong
	     		$getDuthao=$duthaoModel->fetchAll("ID_HSCV=".$getDuthao->ID_HSCV." AND TRANGTHAI<>2");
	     		$this->view->isluuthumuc = 0;
	     		if(count($getDuthao)==1){
	     			$this->view->isluuthumuc = 1;
	     			$thumuc = array();
					QLVBDHCommon::GetTree(&$thumuc,"HSCV_THUMUC","ID_THUMUC","ID_THUMUC_CHA",1,1);
					$this->view->thumuc = $thumuc;
	     		}
    		}
			
			$this->view->data = $data;
			
		}
		$this->view->id = $id;
		$svb = new SoVanBanModel();
		$this->view->svb = $svb->fetchAll();   
		$lvb = new LoaiVanBanModel();
		$this->view->lvb = $lvb->fetchAll();
		$r = $lvb->getDefaultAdapter()->query("SELECT u.ID_U,dep.KYHIEU_PB FROM QTHT_USERS u INNER JOIN QTHT_EMPLOYEES emp on u.ID_EMP = emp.ID_EMP INNER JOIN QTHT_DEPARTMENTS dep on emp.ID_DEP = dep.ID_DEP");
		$this->view->ns = $r->fetchAll();
		$r = $lvb->getDefaultAdapter()->query("SELECT vb.ID_CQ,vb.KYHIEU,vb.NAME,svb.ID_SVB FROM VB_COQUAN vb left join vb_sovanban svb on vb.ID_CQ=svb.ID_CQ WHERE ISSYSTEMCQ=1");
		$this->view->cq = $r->fetchAll();      	
	}
	
	
	
	private function checkSokyhieu($sokyhieu,$id_cq,$id_lvb){
		$year=QLVBDHCommon::getYear();
		$vbdiModel=new VanBanDiModel($year);
		$where="";
		if($id_cq!="") $where.=" AND ID_CQ=".$id_cq;
        if($id_lvb!="") $where.=" AND ID_LVB=".$id_lvb;
        $id=$checkData['id'];
        if($sokyhieu!=null)
		{
			try 
			{
				if($id>0)
				{
					$tempData=$vbdiModel->fetchRow("SOKYHIEU='".$sokyhieu."' AND ID_VBDI <> ".$id.$where);
				}				
				else 
				{
					$tempData=$vbdiModel->fetchRow("SOKYHIEU='".$sokyhieu."'".$where);
				}
					
				if($tempData!=null)
					return 1;
				else 
					return 2;
			}
			catch (Exception $e2)
			{
				return 3;
			}
		}
		else 
		{
			return 0;
		}		
	}
	
	private function checkSodi($sodi,$id_cq,$id_lvb,$id_svb){
		$arr_data = 
		$sodi_cu = Common_Sovanban::getCurrentSodi();
		
	}
	
	/**
	 * Kiểm tra trạng thái số ký hiệu
	 *
	 */
	
	function checkAction()
    {
    	$this->_helper->layout->disablelayout();
    	$checkData =  $this->getRequest()->getParams();
    	//$year=QLVBDHCommon::getYear();
        //$vbdiModel=new VanBanDiModel($year);
		$sodi = $checkData['sodi'];
        $sokyhieu = $checkData['sokyhieu'];
        $id_cq=$checkData['id_cq'];
        $id_lvb=$checkData['id_lvb'];
		$id_svb=$checkData['id_svb'];
		$re = $this->checkSodi($sodi,$id_cq,$id_lvb,$id_svb);
		echo $re;
		exit;
    }
    /**
     * delete action
     *
     */
    function deleteAction()
    {
    	$this->view->title = "Xóa văn bản đi";
    	$year=QLVBDHCommon::getYear();
    	//add button
    	QLVBDHButton::AddButton("Danh sách","/vbdi/banhanh/","","",2);
	    //Get messages
        $this->view->deleteError = '';
        //list Id cannot delete
        $adderror='';
    	if($this->_request->isPost())
		{
			$idarray = $this->_request->getParam('DEL');
			
			if(count($idarray)<=0)
			{
				$this->view->deleteError="Không có mục nào để tiến hành xóa( xin vui lòng chọn một mục!)";
			}
			$counter=0;
			while ( $counter < count($idarray )) 
			{
				
				if ($idarray[$counter] > 0) 
				{
					try 
					{
						$delLoai = new VanBanDiModel($year);
	                	$where = 'ID_VBDI = ' . $idarray[$counter];
	                	$delLoai->delete($where);
						
					}
					catch(Exception $er){ $adderror=$adderror.$idarray[$counter].' ; ';};
				}
				$counter++;
			}
			//already delete some or all items
			if($counter>0)
			{
				if($counter==count($idarray ))	
				{
					$this->view->deleteError="Xóa thành công các mục đã chọn";	
					$this->_redirect('/vbdi/banhanh/');				
				}
				else 
				{
					$this->view->deleteError="Không xóa được mục đã chọn";
				}
			}
			if($adderror!='')
			{
				$this->view->deleteError="Xóa không thành công các mục với id= ".$adderror;
			}
		}
	}
	
//hieuvt	
   /**
	 * Xem list văn bản đi
	 */
	public function listAction(){
		$this->view->start = (float) array_sum(explode(' ',microtime())); 
		global $auth;
		$user = $auth->getIdentity();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$config = Zend_Registry::get('config');
		
		$realyear = QLVBDHCommon::getYear();
		//tinh chỉnh param
		$ID_VBDI 	= $param['ID_VBDI'];
		$TRICHYEU 	= $param['TRICHYEU'];
		$ID_CQ 		= $param['ID_CQBH'];
		//$ID_CQBH 	= $param['ID_CQBH'];
		$ID_SVB 	= $param['ID_SVB'];
		$ID_LVVB 	= $param['ID_LVVB'];
		$ID_LVB 	= $param['ID_LVB'];
		$COQUANBANHANH_TEXT = $param['COQUANBANHANH_TEXT']==MSG11001017 ?'':$param['COQUANBANHANH_TEXT'];
		$DOMAT 		= $param['DOMAT'];
		$DOKHAN 	= $param['DOKHAN'];	
		$SOKYHIEU 	= $param["SOKYHIEU"];
		$SODI 		= $param["SODI"];
		$IS_SEE_ALL = $param["IS_SEE_ALL"];
		if($param['NGAYBANHANH_BD']!=""){
			$ngaytao_bd = $param['NGAYBANHANH_BD']."/".QLVBDHCommon::getYear();
			$ngaytao_bd = implode("-",array_reverse(explode("/",$ngaytao_bd)));
		}
		if($param['NGAYBANHANH_KT']!=""){
			$ngaytao_kt = $param['NGAYBANHANH_KT']."/".QLVBDHCommon::getYear();
			$ngaytao_kt = implode("-",array_reverse(explode("/",$ngaytao_kt)));
		}		
		
		$page = $param['page'];
		$limit = $param['limit1'];
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
		$scope = array();
		if($param['SCOPE']){
			$scope = $param['SCOPE'];
		}
		
		$parameter = array(
			"ID_VBDI"=>$ID_VBDI,
		    "TRICHYEU"=>$TRICHYEU,
		    "ID_CQ"=>$ID_CQ,
		    //"ID_CQBH"=>$ID_CQBH,
			"ID_SVB"=>$ID_SVB,
			"ID_LVVB"=>$ID_LVVB,
			"ID_LVB"=>$ID_LVB,
			"SODI"=>$SODI,
			"COQUANBANHANH_TEXT"=>$COQUANBANHANH_TEXT,
			"DOMAT"=>$DOMAT,
			"DOKHAN"=>$DOKHAN,	
			"NGAYBANHANH_BD"=>$ngaytao_bd,
			"NGAYBANHANH_KT"=>$ngaytao_kt,
			"SOKYHIEU" => $SOKYHIEU,
			"ID_U"=>$user->ID_U,
			"IS_SEE_ALL"=>$IS_SEE_ALL,
			"SCOPE"=>$scope
		);
		
		//Tạo đối tượng
		$hscv = new hosocongviecModel();
		$hscvcount = $hscv->Count_vbdi($parameter);
		
		//Lấy dữ liệu
		$this->view->data = $hscv->SelectAll_vbdi($parameter,($page-1)*$limit,$limit,"NGAYBANHANH DESC");		
		//$this->view->ID_VBDI = 
		$this->view->realyear 		= $realyear;
		$this->view->TRICHYEU 		= $TRICHYEU;
		$this->view->ID_CQBH 		= $ID_CQ;
		//$this->view->ID_CQBH 		= $ID_CQBH;
		$this->view->ID_SVB 		= $ID_SVB;
		$this->view->ID_LVVB 		= $ID_LVVB;
		$this->view->ID_LVB 		= $ID_LVB;
		$this->view->SOKYHIEU 		= $SOKYHIEU;
		$this->view->SODI 			= $SODI;
		$this->view->COQUANBANHANH_TEXT = $COQUANBANHANH_TEXT;
		$this->view->DOMAT 			= $DOMAT;
		$this->view->DOKHAN 		= $DOKHAN;		
		$this->view->NGAYBANHANH_BD 	= $param['NGAYBANHANH_BD'];
		$this->view->NGAYBANHANH_KT 	= $param['NGAYBANHANH_KT'];
		$this->view->SCOPE = $scope;
		$this->view->user = $user;
		$this->view->vbdi = new VanBanDiModel(QLVBDHCommon::getYear());
		$this->view->IS_SEE_ALL = $IS_SEE_ALL;
		//page
		$this->view->title = "Danh sách Văn bản đi";
		//$this->view->subtitle = "danh sách";
		
		$this->view->page = $page;
		$this->view->limit = $limit;
		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($hscvcount,10,$limit,"frm",$page,"/vbdi/banhanh/list");
		$act = ResourceUserModel::getActionByUrl("vbdi","banhanh","input");
		//var_dump($act);
		if(ResourceUserModel::isAcionAlowed($user->USERNAME,$act[0])){
			QLVBDHButton::EnableAddNew("/vbdi/banhanh/input");
		}
		$this->view->arr_idnews = vbdi_dongluanchuyenModel::getIdVbdiChuaXemByIdUser($realyear,$user->ID_U);
		$this->view->year = $realyear;
	}
// end hieuvt
//trunglv
	/**
		@author : trunglv
		@des: Ham ajax tai server nhan du lieu la so di , loai van ban, so van ban , co quan ban hanh
		Ham tra ve so di : Neu tham so type = 2
		Ham tra ve ket qua : Neu tham so type = 1	
	*/
	function getsodiAction(){
		$params = $this->_request->getParams();
		$type = $params["type"];
		$year = QLVBDHCommon::getYear();
		$is_update = $params["is_update"];
		$id = $params["id"];
		if($id >0){
			//lay cac truon hop cu trong csdl
			$old_data = VanBanDiModel::getDetail($id,$year);
			$arr_col = Common_Sovanban::getColumNameGroup(2);
			$len_arr = count($arr_col);	
			$old_sodi = $old_data["SODI"];
			$old_cq = $old_data["ID_CQ"];
			$aff_old = 0;
			$col_name = "";
			foreach ($arr_col as $itcol){
			switch ($itcol)
			{
				case 'ID_SVB':
					$aff_old = $old_data["ID_SVB"];
					$col_name='id_svb';
					break;
				case 'ID_LVB':
					$aff_old = $old_data["ID_LVB"];
					$col_name='id_lvb';
					break;
				default:
					break; 
			}
			if($old_cq == $params["id_cq"] && $aff_old == $params[$col_name] )
			{
				
				echo -2;//truong hop giu nguyen so di
				exit;
			}
		}
		}
		if($type == 2){
			//truong hop muon lay so di
			$year=QLVBDHCommon::getYear();
			$array = array();
			$array["ID_CQ"] = $params["id_cq"];
			$array["ID_LVB"] = $params["id_lvb"];
			$array["ID_SVB"] = $params["id_svb"];
			$result = Common_Sovanban::getCurrentSodi($year,$array);
			if($result=="")
				echo 0;
			else
				echo $result;
			
		}else if ($type == 1){
			//truong hop nguoi dung tu nhap so di khi da nhap cac truong khac nhu co quan ban hanh , loai van ban , ....
			$year=QLVBDHCommon::getYear();
			$array = array();
			$array["ID_CQ"] = $params["id_cq"];
			$array["ID_LVB"] = $params["id_lvb"];
			$array["ID_SVB"] = $params["id_svb"];
			$arr_col = Common_Sovanban::getColumNameGroup(2);
			foreach($arr_col as $item){
				if($array[$item] == 0 ) {
					echo -1;
					exit;
				}
			}
			$result = Common_Sovanban::getCurrentSodi($year,$array);
			if($result=="")
				echo 0;
			else{
				//if($result > $params["sodi"] ) echo 1;
				//else echo 0;
				echo $result;
			}
		}
		exit;
		
	}
	function detailAction(){
		$params =  $this->_request->getParams();
		$id_vbdi = $params['id_vbdi'];
		$year = $params['year'];
		$this->view->data = VanBanDiModel::getDetail($id_vbdi,$year);
	}
	/**
	Lay danh sach dong luan chuyen, chuyen de biet
	*/
	
	public function wayAction(){
    	$this->_helper->layout->disableLayout();
		
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$id_vbdi = $param["id"];
		$year = $param["year"];
		$this->view->type = $type;
		$this->view->year = $year;
		$this->view->idobject = $id_vbdi;
		$this->view->way = vbdi_dongluanchuyenModel::way($year,$id_vbdi);
		//kiem tra dieu kien truy cap
		$isreadonly = $this->_request->getParam('isreadonly');
		if(!$isreadonly)
			$isreadonly = 0;
		
    }
    
    function sendAction(){
		$this->_helper->layout->disableLayout();
		//Lấy parameter
		$param = $this->getRequest()->getParams();
		$id= $param["id"];
		$year = $param["year"];
		$this->view->id = $id;
		$this->view->year = $year;
		
	}
	function savesendAction(){
		global $auth;
		$user = $auth->getIdentity();
		
		$param = $this->getRequest()->getParams();
		$id = $param["id"];
		$year = $param["year"];
		$lc = new vbdi_dongluanchuyenModel($year);
		$lc->send($id,$param['ID_U'],$param['NOIDUNG'],$user->ID_U);
		echo "<script>window.parent.loadDivFromUrl('groupcontent".$id."','/vbdi/banhanh/way/id/".$id."/year/".$year."',1);</script>";
		exit;
	}
	
	function updatedadocAction(){
		$params = $this->_request->getParams();
		$id_vbdi = $params['id_vbdi'];
		$id_u = $params['id_u'];
		$year = $params['year'];
		echo vbdi_dongluanchuyenModel::updateDaDoc($year,$id_vbdi,$id_u);
		exit;
	}
	
	function vbdenlienquanAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		$id_vbdi = $params["id"];
		$year = QLVBDHCommon::getYear();
		$this->view->data = VanBanDiModel::getListVanbandenByIdVbdi($id_vbdi,$year);
	}
	function getsvbbycoquanbhAction(){
		$this->_helper->layout->disableLayout();
		$params = $this->_request->getParams();
		//$arr_svb = SoVanBanModel::getDataByCQ(QLVBDHCommon::getYear(),$params["COQUANBH"],1);
		$arr_svb = SoVanBanModel::selectWithDep(1);
		echo json_encode($arr_svb);
		exit;
	}
//end trunglv
}
