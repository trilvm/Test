<?php
require_once 'Zend/Controller/Action.php';
require_once 'dongbolencong/models/dongbo.php';
class Dongbolencong_dongboController extends Zend_Controller_Action {
	function listAction(){
		$params = $this->getRequest()->getParams();

		$type = $params["type"];

		if($type==""){ $type = 1; }

		$page = $params["page"];
		$page = $page==0?1:$page;
		$limit = $params["limit1"];
		$limit = $limit==0?20:$limit;

		$parameter = array();
		$parameter["SOKYHIEU"] = $params["SOKYHIEU"];
		$parameter["SOKYHIEU_FULL"] = $params["SOKYHIEU_FULL"];

		$this->view->page = $page;
        $this->view->limit = $limit;
        $this->view->SOKYHIEU = $params["SOKYHIEU"];
        $this->view->SOKYHIEU_FULL = $params["SOKYHIEU_FULL"];
        $order="";
		switch($type){
			case 1:
				$this->view->data = DongBo::GetListVanBanChuaDongBo($parameter,$page,$limit,$order);
				$this->view->datacount = DongBo::CountListVanBanChuaDongBo($parameter);
				$this->view->title="Danh sách văn bản chưa đồng bộ";
				break;
			case 2:
				$this->view->data = DongBo::GetListVanBanDaDongBo($parameter,$page,$limit,$order);
				$this->view->datacount = DongBo::CountListVanBanDaDongBo($parameter);
				$this->view->title="Danh sách văn bản đã đồng bộ";
				break;
		}

		$this->view->showPage = QLVBDHCommon::PaginatorWithAction($this->view->datacount, 10, $limit, "frm", $page, "/dongbolencong/dongbo/list/type/".$type);
		$this->view->type = $type;
	}
	function dongboAction(){
		$params = $this->getRequest()->getParams();

		DongBo::DongBo($params["id_vbdi"]);
		exit;
	}
	function thuhoiAction(){
		$params = $this->getRequest()->getParams();

		DongBo::ThuHoi($params["id_vb_portal"]);
		exit;
	}
}