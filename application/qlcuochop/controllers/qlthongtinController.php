<?php
require_once 'hscv/models/filedinhkemModel.php';
require_once 'qlcuochop/models/quanlycuochopModel.php';
require_once 'hscv/models/gen_tempModel.php';
class qlcuochop_qlthongtinController extends Zend_Controller_Action {


    public function init(){

        $this->getParams=$this->getRequest()->getParams();
        $this->model = new quanlycuochopModel();
        $this->view->title = "Quản lý cuộc họp";
    }

    public function indexAction()
    {
        global $auth;
        $user = $auth->getIdentity();
        $this->view->ID_U= $user->ID_U;

        $this->view->subtitle  ="Danh sách";
        $model=new quanlycuochopModel();

        $ten= $this->getParams['NAME'];
        $phamvi= $this->getParams['phamvi'];
        $ID_CUOCHOP = $this->getParams['ID_CUOCHOP'];
        
        $ngaybd = implode("-",array_reverse(explode("/",$this->getParams['fromdate'])));
       
        $ngaykt= implode("-",array_reverse(explode("/",$this->getParams['todate'])));

        //if($user->ID_U ==12  || $user->ID_U == 113  || $user->ID_U == 146  || $user->ID_U == 179  || $user->ID_U == 114){
        QLVBDHButton::EnableAddNew("/qlcuochop/qlthongtin/input");
        QLVBDHButton::EnableDelete("/qlcuochop/qlthongtin/delete");
        //}
        
        $params = $this->_request->getParams();
       // $year = QLVBDHCommon::getYear();
        $config = Zend_Registry::get('config');
        $page= $this->_request->getParam('page');
        $limit= $this->_request->getParam('limit1');        
        $param= $this->_request->getParams();
        if($limit==0 || $limit=="")$limit=$config->limit;
        if($page==0 || $page=="")$page=1;        
        $NAME = $this->_request->getParam("NAME");
        $this->view->NAME = $NAME;
        $parameter = array('NAME'=>$ten,'fromdate'=>$ngaybd,'todate'=>$ngaykt,'phamvi'=>$phamvi);
        //var_dump($parameter); exit;
        $offset=($page - 1) * $limit;
        $order = "ID_CUOCHOP DESC";
        
        $data = $model->SelectAll($parameter,$offset,$limit,$order,$user->ID_U);
        $this->view->data=$data;
        //var_dump($data); exit;
        $this->view->ID_CUOCHOP=$ID_CUOCHOP;
      
        $n_rows = $model->count($parameter,$user->ID_U);
        $this->view->showPage = QLVBDHCommon::Paginator($n_rows,5,$limit,"frm",$page) ;
        $this->view->page = $page;
        $this->view->limit = $limit;
    }    

    public function inputAction()
    {
        $year = QLVBDHCommon::getYear();
        if(!$this->getParams['is_ajax']){
            $id = $this->getParams['ID_CUOCHOP'];
            $sel_dep = $parameter["ID_DEP"];
            $this->qlch = new quanlycuochopModel();

			/* Trường hợp cập nhật */
            if($id>0){
                $this->view->subtitle  ="Cập nhật cuộc họp";
                $data=$this->qlch->find($id)->current();
				//var_dump($data);exit;
                $data_cv = $this->qlch->GetCONGVIECBYIDCUOCHOP($id);
                $this->view->data=$data;
                $this->view->data_cv= $data_cv;
                $this->view->id=$id;
            }
			/* Trường hợp thêm mới */
			else{
                $tempTbl = new gen_tempModel();
                $idObjectTCQ = $tempTbl->getIdTemp();
                $idObjectNCQ = $tempTbl->getIdTemp();
                $this->view->idObject=$idObjectTCQ;
                $this->view->idObjectNCQ=$idObjectNCQ;
            }

            $this->view->subtitle  ="Tạo cuộc họp";
            $this->view->phamvi = $this->getParams['PHAMVI'];   
            $this->view->sel_dep = (int)$parameter["ID_DEP"];
            $this->view->dep = $this->qlch->GetDeparment();
            $this->view->year = $year;
			
            QLVBDHButton::EnableSave("/qlcuochop/qlthongtin/save");
            QLVBDHButton::EnableBack("/qlcuochop/qlthongtin/index");
        }
		/* Đoạn bên dưới sử dụng cho ajax */
		else{
           require_once 'Common/ajax.php';
           $this->_helper->Layout->disableLayout();
		   $varName = $this->getParams['varName'];
           $tempTbl = new gen_tempModel();
           $idObject = $tempTbl->getIdTemp();
           ajax::ship($varName,$idObject);
           ajax::ship('year',$year);
           exit;  
        }
    }    

    public function savengoaicoquanAction(){
        $db=  Zend_Db_Table::getDefaultAdapter();
        $model= new quanlycuochopModel();

        $id=(int)$this->getParams['id'];
        //var_dump($id); exit;
        $ten=$this->getParams['ten'];
        $phamvi=$this->getParams['phamvi'];
		$HTML_nguoithamdu = $this->getParams['HTML_nguoithamdu'];
                $HTML_nguoithamdu2 = $this->getParams['HTML_nguoithamdu2'];
                //var_dump($HTML_nguoithamdu2);exit;
        $dateReceive = implode("-",array_reverse(explode("/",$this->getParams["BEGINDATE2"])));
        $gio=$this->getParams['gio2'];
        $diadiem=$this->getParams['diadiem2']==""? null:$this->getParams['diadiem2'];
        $nguoichutri = $this->getParams['nguoichutri2']==""?null:$this->getParams['nguoichutri2'];
        //$nguoithamgia = $this->getParams['nguoithamdu'];
        $noidung = $this->getParams['noidung'];
        $idObject_CH = $this->getParams['idobject_ch_ncq'];
        
        $list_dstd = $this->getParams['list_dstd'];
        $dstd = implode(",", $list_dstd);
        $dstd = $dstd.'~'.$HTML_nguoithamdu;     
                
        $list_dstd2 = $this->getParams['list_dstd2'];
        $dstd2 = implode(",", $list_dstd2);
        $ids_nguoithamgia = $dstd2.'~'.$HTML_nguoithamdu2;
        //var_dump($dstd2); exit;        

        global $auth;
        $user = $auth->getIdentity();
        $id_nguoitao = $user->ID_U;
        $idFile=$this->getParams['idFile'];
        //var_dump($idFile); exit;
        if(count($idFile)>0){
            (int)$check_bb = 1;
        }  else if(count($idFile)== 0){
            (int)$check_bb = 0;
        }
        //neu them moi thi lay id nay
        $model->savengoaicoquan((int)$id,$ten,$phamvi,$dateReceive,$gio,$diadiem,$nguoichutri,$noidung,(int)$id_nguoitao,$dstd,$idObject_CH, $check_bb,$ids_nguoithamgia);
        $this->_redirect('qlcuochop/qlthongtin/index');
    }

    public function savetrongcoquanAction(){
        $db =  Zend_Db_Table::getDefaultAdapter();
        $model = new quanlycuochopModel();
        
        $id=(int)$this->getParams['id'];
        //var_dump($id); exit;
        $ten=$this->getParams['ten'];
        $HTML_nguoithamgia=$this->getParams['HTML_nguoithamgia'];
        //var_dump($HTML_nguoithamgia);exit;
        
        $phamvi=$this->getParams['phamvi'];
        $dateReceive = implode("-",array_reverse(explode("/",$this->getParams["BEGINDATE"])));
        $gio=$this->getParams['gio'];
        $diadiem=$this->getParams['diadiem'];
//        $bophan=$this->getParams['GDEP_NGUOICHUTRI'];
//        $phong=$this->getParams['DEP_NGUOICHUTRI'];
        $nguoichutri=$this->getParams['NGUOICHUTRI'];       
        $nguoithamgia=$this->getParams['nguoithamgia'];
        $thuky=$this->getParams['THUKY'];
        $thuky2 = $this->getParams['thuky2'] == ""? null :$this->getParams['thuky2'];
        //var_dump($thukyN);exit;
        $ld_thuchien = $this->getParams['LDTHUCHIEN'];
        
        $list_dstg = $this->getParams['list_dstg'];
        $dstg = implode(",", $list_dstg);
		$dstg = $dstg.'~'.$HTML_nguoithamgia;
        //var_dump($ds); exit;

        global $auth;
        $user = $auth->getIdentity();
        $id_nguoitao = $user->ID_U;
        $nguoi_ncq = $this->getParams['dsncq'];
        $idObject_CH = $this->getParams['idobject_ch'];
        $idFile=$this->getParams['idFile'];
        //echo count($idFile); exit;
        if(count($idFile)>0){
            $check_bb = 1;
        }  else if(count($idFile)== 0){
            $check_bb = 0;
        }
        $is_finish = 0;
        //neu them moi thi lay id nay
        $id_cuochop = $model->save((int)$id,$ten,$phamvi,$dateReceive,$gio,$diadiem,$nguoichutri,$nguoithamgia,$thuky,(int)$id_nguoitao,$nguoi_ncq,$dstg,$idObject_CH,$check_bb, $ld_thuchien,$thuky2);

        $nguoixuly=$this->getParams['NGUOI'];
        $id_object=$this->getParams['id_object'];
		
        $list_idCV =$this->getParams['IS_CVOLD'];
        
        $congviec=$this->getParams['NOIDUNG'];
       
        $gioxuly=$this->getParams['VAOLUC'];
        $ngayxuly1 = $this->getParams["DATE"];
        (int)$is_finish=$this->getParams["IS_FINISH"]== null ? 0 :$this->getParams["IS_FINISH"];
        //var_dump($is_finish); exit;
       
        $arridCV = $this->getParams['DEL'];
         //var_dump($arridCV);exit;
        if($arridCV){
        $db->delete("ql_cuochop_congviec","ID_CONGVIEC IN (".implode(',',$arridCV).")");
        }
        for($i=0;$i<count($nguoixuly);$i++)
        {
           $ngayxuly = implode("-",array_reverse(explode("/",$ngayxuly1[$i])));
            //echo (int)$id_congviec[$i]['ID_CONGVIEC'];exit;
           $idcv = $list_idCV[$i];
           $dataCV = array(
                'NGUOIXULY' => $nguoixuly[$i],
                'CONGVIEC' => $congviec[$i],
                'NGAYXULY' => $ngayxuly,
                'VAOLUC' => $gioxuly[$i],
                'ID_CUOCHOP' => $id_cuochop,
                'ID_OBJECT' => $id_object[$i],
                //'IS_FINISH'=>$is_finish);
                );
           $model->NewCV($id,$idcv,$dataCV);
        }
		///exit;
        //var_dump($dataCV);exit;
        $this->_redirect('qlcuochop/qlthongtin/index');
    }

    public function detailchAction() {
        $this->_helper->Layout->disableLayout();

        $id_ch = $this->getParams['ID_CUOCHOP'];
        $data_cuochop = $this->model->find($id_ch)->current();

        $this->view->data_cuochop = $data_cuochop;        
    }

    public function detailcvAction() {
         $this->_helper->Layout->disableLayout();
        $submit_CV = $this->getParams['submit_CV'];
		if(!$submit_CV){
			$id_ch = $this->getParams['ID_CUOCHOP'];
			$data_cuochop = $this->model->find($id_ch)->current();
			$this->view->data_cuochop = $data_cuochop;
			$tempTbl = new gen_tempModel();
			$idObject = $tempTbl->getIdTemp();
			$this->view->idObject=$idObject;
			global $auth;
			$user = $auth->getIdentity();
			$this->view->ID_U= $user->ID_U;

			$db=  Zend_Db_Table::getDefaultAdapter();
			$model= new quanlycuochopModel();

			$data=array('IS_FINISH'=>1);
			// end finish process
			$this->_helper->Layout->disableLayout();
			$id_ch = $this->getParams['ID_CUOCHOP'];

		   /*------------------- xử lý file đính kèm biên bản-------------------*/

                        
			$id_vb = $this->getParams['ID_CUOCHOP'];
			$this->view->idvb = $id_vb;
			$vb = $db->query("SELECT * FROM ql_cuochop WHERE ID_CUOCHOP =?", $id_vb)->fetch();
			$sqldk = "
					SELECT
							*
					FROM GEN_FILEDINHKEM_2011
					WHERE
							 ID_OBJECT = ?
						";
			$this->view->datadk = $db->query($sqldk, $vb['ID_DINHKEM'])->fetchAll();
			$qr=$db->query($sqldk, $vb['ID_DINHKEM'])->fetchAll();
			$this->view->nam = $vb['NAM'];
			$this->view->maso=$qr['MASO'];
			
			$id_nguoitao = $this->getParams['ID_NGUOITAO'];
			$IdFrame = $this->getParams['iframe'];
			$db = $this->model->getDefaultAdapter();
			$sql = "select * from ql_cuochop_congviec where ID_CUOCHOP=$id_ch";
			//echo $sql;exit;
			$data_congviec = $db->query($sql)->fetchAll();
			//var_dump($data_congviec);
			$this->view->data_congviec = $data_congviec;
			$this->view->id_nguoitao = $id_nguoitao;
			$this->view->IdFrame = $IdFrame;
		}else{
			/* Cập nhật công việc hoàn thành */
			$idObject = $this->getParams['idObject'];
			$idFile = $this->getParams['idFile'];
			$id_congviec = $this->getParams['id_congviec'];
			$noidung_hoanthanh = $this->getParams['noidung_hoanthanh'];
			$iframeSRC = $this->getParams['iframeSRC'];
			if($noidung_hoanthanh || $idFile){
				$model = new quanlycuochopModel();
				$dataCV = array(
                'ID_DINHKEM' => $idObject,
                'NOIDUNGHOANTHANH' => $noidung_hoanthanh,
                'IS_FINISH'=> 1);
			    $model->UpdateCV($id_congviec,$dataCV);
			}
			echo "<script>window.location.href='".$iframeSRC."';</script>";
		}
    }

    public function detaildkAction(){
         $this->_helper->Layout->disableLayout();
         global $db;

        $id_vb = $this->getParams['ID_CUOCHOP'];
        $this->view->idvb = $id_vb;
        $vb = $db->query("SELECT * FROM ql_cuochop WHERE ID_CUOCHOP =?", $id_vb)->fetch();
        //var_dump($vb);exit;
        $sqldk = "
                SELECT
                        *
                FROM GEN_FILEDINHKEM_2011
                WHERE
                         ID_OBJECT = ?                        
                    ";        
        $this->view->datadk = $db->query($sqldk, $vb['ID_DINHKEM'])->fetchAll();
        $qr=$db->query($sqldk, $vb['ID_DINHKEM'])->fetchAll();
        //var_dump($db->query($sqldk, $vb['ID_DINHKEM'])->fetchAll());
        $this->view->nam = $vb['NAM'];
        $this->view->maso=$qr['MASO'];
    }

    function downloadAction(){
		$date = getdate();
		$year = QLVBDHCommon::getYear();
		if(!$year)
			$year = $date['year'];
		$maso = $this->_request->getParam('maso');
		$model = new filedinhkemModel($year);
		$file = $model->getFileByMaso($maso);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header( "Content-type:".$file->_mime );
		header( 'Content-Disposition: attachment; filename="'.$file->_filename.'"' );

		echo file_get_contents($file->_pathFile);

		exit;
	}


    public function deleteAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $ids = $this->getParams['DEL'];
        //$where = "ID_CUOCHOP IN (".implode(',',$ids).")";
        try{
            foreach($ids as $id){
                $db->delete("ql_cuochop","ID_CUOCHOP=".(int)$id);
                $db->delete("ql_cuochop_congviec","ID_CUOCHOP=".(int)$id);
            }
        } catch (Exception  $ex)
        {
            die ($ex->__toString());
        }
        $this->_redirect("/qlcuochop/qlthongtin/index");

    }
}