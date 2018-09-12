<?php
require_once 'qtht/models/CoquanModel.php';
require_once 'qtht/models/LoaiVanBanModel.php';
require_once 'report/models/Ad_XulyvanbandenModel.php';
class Report_XulyvanbandenController extends Zend_Controller_Action{
		function init(){
		}

		function indexAction(){
			$this->view->title = "Báo cáo xử lý văn bản đến";
			//$this->view->subtitle = "Xử lý văn bản đến";
		}

		function reportviewAction(){
			$this->_helper->layout->disableLayout();
			
			
			$param = $this->_request->getParams();

			$config = Zend_Registry::get("config");
			
			//tham so du lieu
			
			$type = $param['sel_lcv'];//loai cong viec
			if(!$type || $type==0)
				$type = 1;
			$fromdate = $param["fromdate"];
			if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";		
			$id_pbs = $param["sel_pb"];
			$this->view->type = $type;
			$this->view->todate = $todate;
			$this->view->fromdate = $fromdate;		
			
			$arrparam = array();
                        
			$arrparam["type_date"] = (int)$param["type_date"];
			$this->view->type_date = $arrparam["type_date"];

			$arrparam["sel_lvb"] = $param["sel_lvb"];
			$arrparam["op_lvb"] = (int)$param["op_lvb"];
			
			$this->view->sel_lvb = $param["sel_lvb"];
			$this->view->op_lvb = (int)$param["op_lvb"];

			$arrparam["sel_cqbh"] = $param["sel_cqbh"];
			$arrparam["op_cqbh"] = (int)$param["op_cqbh"];
			
			$this->view->sel_cqbh = $param["sel_cqbh"];
			$this->view->op_cqbh = $param["op_cqbh"];
			$this->view->sel_svb = $param['sel_svb'];
			$arrparam["sel_trangthai"] = (int)$param["sel_trangthai"];
			$arrparam["op_trangthai"] = (int)$param["op_trangthai"];
			$arrparam["hskxly"] = $param["hskxly"]; 
			$arrparam["sel_svb"] = $param['sel_svb']; 
			$arrayidu=$param["ID_U"];
			$arraydep=$param["ID_DEP"];	
			$this->view->arrayidu = $arrayidu;
			$this->view->arraydep = $arraydep;
			$this->view->sokh = $param["sokh"];	
			$this->view->coquanbh = $param["coquanbh"];	
			$this->view->trichyeu = $param["trichyeu"];	
			$this->view->nguoixl = $param["nguoixl"];	
			$this->view->trangthai = $param["trangthai"];	
			$this->view->ketquaxl = $param["ketquaxl"];	
			$this->view->soden = $param["soden"];	
			$this->view->ngayden = $param["ngayden"];
			$this->view->trehantheo = $param["trehantheo"];
                        
                        $nguoixl1 = $param["nguoixl"];
                        $SORTTYPE = $param['SORTTYPE'];
//                        var_dump($param);
                        
			$this->view->nguoiky = $param["nguoiky"];	
			$this->view->soto = $param["soto"];	
			$this->view->domat = $param["domat"];	
			$this->view->dokhan = $param["dokhan"];	
                        $this->view->xulyvanban=count($param["sokh"]) +count($param["coquanbh"]) +count($param["trichyeu"])+count($param["soden"])+count($param["ngayden"])+count($param["nguoiky"])+ count($param["soto"])+count($param["domat"])+count($param["dokhan"])+count($nguoixl1);
			$this->view->tinhhinhxl= count($param["nguoixl"]) +count($param["trangthai"])+count($param["ketquaxl"]);
			$this->view->hskxly = $param["hskxly"];			
			$this->view->sel_trangthai = (int)$param["sel_trangthai"];
			$this->view->op_trangthai = $arrparam["op_trangthai"];

			$this->view->title="Báo cáo xử lý văn bản đến từ ngày $fromdate đến ngày $todate";
			
			//tham so phan trang
			$page = $param['page'];
			$limit = $param['limit1'];
			if($limit==0 || $limit=="")$limit=$config->limit;
			if($page==0 || $page=="")$page=1;
			$this->view->page = $page;
			$this->view->limit = $limit;				
			$arr_thongke = Ad_XulyvanbandenModel::thongke($type,$todate,$fromdate,$arrparam,$arrayidu,$arraydep);
			$this->view->arr_thongke = $arr_thongke;			
			$count = $arr_thongke["dem"];
			//var_dump($count);exit;
			//Ad_XulyvanbandenModel::getCountReportData($type,$todate,$fromdate,$arrparam);
			
			//echo $count; exit;
			$this->view->showPage = QLVBDHCommon::PaginatorWithParentSubmit($count,10,$limit,"frm",$page,"/report/Xulyvanbanden/reportview","reportview");
			$this->view->data = Ad_XulyvanbandenModel::getReportData($type,$todate,$fromdate,$arrparam,($page-1)*$limit,$limit,$arrayidu,$arraydep);
//                        var_dump($this->view->data1);
			$is_in = $param['is_in'];
			if($is_in){
				global $config;
				$this->view->config = $config;
				if($fromdate == "")
					$fromdate = "1/1";
				$todate = $param['todate'];
				if($todate == "")
					$todate = "31/12";
				$this->view->thu="Từ ngày"." ".$fromdate." "."đến ngày"." ".$todate;
				$this->renderScript("xulyvanbanden/reportview_in.phtml");
				
			}
			//$this->view->arr_thongke = $arr_thongke;
			//var_dump($arr_thongke);
			//exit;
			//var_dump($this->view->data);
		}
		function reportviewexcelAction() {
			$this->_helper->layout->disableLayout();
			$param = $this->_request->getParams();
			
			 if($param['h_isexel'] == 1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=baocaovanbanden.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
			 }		
			
			$config = Zend_Registry::get("config");
			$this->view->config = $config;
			$type = $param['sel_lcv'];//loai cong viec
			if(!$type || $type==0)
				$type = 1;
			$fromdate = $param["fromdate"];
			if($fromdate == "")
				$fromdate = "1/1";
			$todate = $param['todate'];
			if($todate == "")
				$todate = "31/12";
			$this->view->thu="TỪ NGÀY"." ".$fromdate." "."ĐẾN NGÀY"." ".$todate;
			$id_pbs = $param["sel_pb"];
			$this->view->type = $type;
			$this->view->todate = $todate;
			$this->view->fromdate = $fromdate;		
			
			$arrparam = array();			
			
			$arrparam["type_date"] = (int)$param["type_date"];
			$this->view->type_date = $arrparam["type_date"];

			$arrparam["sel_lvb"] = $param["sel_lvb"];
			$arrparam["op_lvb"] = (int)$param["op_lvb"];
			
			$this->view->sel_lvb = $param["sel_lvb"];
			$this->view->op_lvb = (int)$param["op_lvb"];

			$arrparam["sel_cqbh"] = $param["sel_cqbh"];
			$arrparam["op_cqbh"] = (int)$param["op_cqbh"];
			
			$this->view->sel_cqbh = $param["sel_cqbh"];
			$this->view->op_cqbh = $param["op_cqbh"];
			$arrparam["hskxly"] = $param["hskxly"];
			$arrayidu=$param["ID_U"];
			$arraydep=$param["ID_DEP"];
			$this->view->sokh = $param["sokh"];	
			$this->view->coquanbh = $param["coquanbh"];	
			$this->view->trichyeu = $param["trichyeu"];	
			$this->view->nguoixl = $param["nguoixl"];	
			$this->view->trangthai = $param["trangthai"];	
			$this->view->ketquaxl = $param["ketquaxl"];	
			$this->view->soden = $param["soden"];	
			$this->view->ngayden = $param["ngayden"];	
				
			$this->view->nguoiky = $param["nguoiky"];	
			$this->view->soto = $param["soto"];	
			$this->view->domat = $param["domat"];	
			$this->view->dokhan = $param["dokhan"];	
			$this->view->xulyvanban=count($param["sokh"]) +count($param["coquanbh"]) +count($param["trichyeu"])+count($param["soden"])+count($param["ngayden"])+count($param["nguoiky"])+ count($param["soto"])+count($param["domat"])+count($param["dokhan"]);
			$this->view->tinhhinhxl= count($param["nguoixl"]) +count($param["trangthai"])+count($param["ketquaxl"]);
            
			$arrparam["sel_trangthai"] = (int)$param["sel_trangthai"];
			$arrparam["op_trangthai"] = (int)$param["op_trangthai"];
			

			$this->view->sel_trangthai = (int)$param["sel_trangthai"];
			$this->view->op_trangthai = $arrparam["op_trangthai"];            			
			$this->view->title="Báo cáo xử lý văn bản đến từ ngày $fromdate đến ngày $todate";
			$this->view->xuat=(int)$param["xuat"];
			//tham so phan trang
			/*$page = $param['page'];
			$limit = $param['limit1'];
			if($limit==0 || $limit=="")$limit=$config->limit;
			if($page==0 || $page=="")$page=1;
			$this->view->page = $page;
			$this->view->limit = $limit;*/
			
			//echo "111111122222"; 
			
			$arr_thongke = Ad_XulyvanbandenModel::thongke($type,$todate,$fromdate,$arrparam,$arrayidu,$arraydep);
			$this->view->arr_thongke = $arr_thongke;
			//var_dump($arr_thongke);
			$count = $arr_thongke["dem"];
			//Ad_XulyvanbandenModel::getCountReportData($type,$todate,$fromdate,$arrparam);
			
			//echo $count; exit;
			//$this->view->showPage = QLVBDHCommon::PaginatorWithAction($count,10,$limit,"frm",$page,"/report/Xulyvanbanden/reportview");
			$this->view->data = Ad_XulyvanbandenModel::getReportData($type,$todate,$fromdate,$arrparam,0,$count,$arrayidu,$arraydep);
			
			//var_dump($this->view->data);exit;
		
			

		}
		
}