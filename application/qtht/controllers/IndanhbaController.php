<?php
require_once 'Zend/Controller/Action.php';
require_once 'qtht/models/nguoidungModel.php';
require_once 'qtht/models/fk_users_groupsModel.php';
require_once 'qtht/models/fk_users_actionsModel.php';
require_once 'config/qtht.php';
require_once 'qtht/models/DepartmentsModel.php';
class Qtht_IndanhbaController extends Zend_Controller_Action {
    
    public function listAction()
    {
        
                $config = Zend_Registry::get('config');
		$parameter = $this->getRequest()->getParams();
                 if($this->_request->isPost())
                {
                 $limit=  $parameter["limit"];
                }
                else $limit=40;
		$page = $parameter["page"];
		$search = $parameter["search"];
		$this->view->sel_dep = (int)$parameter["ID_DEP"];
                $this->view->group = (int)$parameter["ID_GROUP"];
              //  echo $parameter["ID_DEP"]; echo"<br>";
              //  echo $parameter["ID_GROUP"];
		//Tinh ch?nh parameter
		if($limit==0 || $limit=="")$limit=$config->limit;
		if($page==0 || $page=="")$page=1;
               
		//New cac model
		$this->nguoidung = new nguoidungModel();
		//Kh?i ??ng cac bi?n cho cac model
		$this->nguoidung->_search = $parameter["search"];
		$this->nguoidung->_sel_dep =(int)$parameter["ID_DEP"];
                $this->nguoidung->_group = (int)$parameter["ID_GROUP"];
		//L?y d? li?u chinh
		$rowcount = $this->nguoidung->Count();
                $this->view->data = $this->nguoidung->SelectAll(($page-1)*$limit,$limit,"DEP.ID_DEP, u.ISLEADER DESC, LASTNAME");
               
                if($this->_request->getParam('sapxep'))
                {
                   $this->view->sapxep=$parameter["sapxep"];
                   if($parameter["sapxep"]==1)  $this->view->data = $this->nguoidung->SelectAll(($page-1)*$limit,$limit,"LASTNAME desc");
                   if($parameter["sapxep"]==0)  $this->view->data = $this->nguoidung->SelectAll(($page-1)*$limit,$limit,"LASTNAME asc");
                }
               
                $this->view->dep = $this->nguoidung->GetDeparment();
                $this->view->data_group =$this->nguoidung->ToDepComboNhomNguoidung();
		//Set bi?n cho view
		$this->view->title = "In danh bạ người dùng";
		//$this->view->subtitle = "Danh sách";
		$this->view->limit = $limit;
		$this->view->search = $search;
		$this->view->page = $page;
		$this->view->showPage = QLVBDHCommon::Paginator($rowcount,5,$limit,"frm",$page) ;

		//Enable button
		//QLVBDHButton::EnableDelete("/qtht/danhmucnguoidung/Delete");
		
                 QLVBDHButton::AddButton("Xuất danh bạ ra excel","","Excel","xuatdanhba()");
                 QLVBDHButton::AddButton("In danh bạ","","PrintButton","indanhba()");
                 QLVBDHButton::EnableBack("/qtht/danhmucnguoidung/Input");
    }
    
    public function inAction()
    {      
         $param=$this->_request->getParam('IN');
         $isExcel=$this->_request->getParam('EXCEL');
		 $config = Zend_Registry::get('config');
		 $this->view->config = $config;
         $this->_helper->layout->disableLayout();
         $model_nguoidung= new nguoidungModel();
         //$data= $model_nguoidung->FindAllById2($param);
         
         $db = Zend_Db_Table::getDefaultAdapter();
		//Thực hiện query
           $sql="
               SELECT emp.FIRSTNAME,emp.PHONE,emp.EMAIL,emp.LASTNAME,dep.NAME as DEPNAME,dep.ID_DEP,emp.ID_EMP,emp.ADDRESS,emp.PHONE_EXT,emp.ID_CD,cd.* 
                FROM QTHT_EMPLOYEES emp 
                left join QTHT_CHUCDANH cd on cd.ID_CD = emp.ID_CD 
                left join QTHT_DEPARTMENTS dep on dep.ID_DEP = emp.ID_DEP 
                WHERE   emp.ID_EMP IN  (".implode(',',$param).")
                ORDER BY emp.ID_DEP  "; 
         
          $r = $db->query($sql);  
          $data= $r->fetchAll();
          $this->view->data=$data;
		  if($isExcel == 1){
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=danhba.xls;");
			header("Pragma: no-cache");
			header("Expires: 0");
			$this->renderScript("Indanhba/excel.phtml");
		  }
          
        
    }
}
?>
