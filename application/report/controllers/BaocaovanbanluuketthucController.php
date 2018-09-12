<?php

    /**
     * User: HOANGNM
     * Date: 2016-07-08
     */
    #region require_once
    require_once 'Zend/Controller/Action.php';
    require_once 'report/models/BaocaovanbanluuketthucModel.php';
    require_once 'report/models/ThongkevanbandiModel.php';
    #endregion

    class Report_BaocaovanbanluuketthucController extends Zend_Controller_Action
    {
        public function init()
        {
            $this->view->title = "Báo cáo văn bản lưu kết thúc";
        }

        public function indexAction()
        {

        }

        public function reportAction()
        {
            global $auth;
            $this->_helper->layout->disableLayout();
            $config = Zend_Registry::get('config');
            $params = (object)$this->getRequest()->getParams();

            // setting date
            // year
            $year = $params->year;
            if ( (int)$year < 2013 || (int)$year > QLVBDHCommon::getYear() ) {
                $year = QLVBDHCommon::getYear();
            }
            // from date
            if ( $params->fromdate == NULL ) {
                $params->fromdate = '1/1';
            }
            // todate
            if ( $params->todate == NULL ) {
                $params->todate = '31/12';
            }

            //model
            $model = new BaocaovanbanluuketthucModel($year);
            if((int)$params->thongke == 0){
                $listItems = ThongkevanbandiModel::getPhongBan($params->sel_pb);
            }else{
                $listItems = ThongkevanbandiModel::getCaNhan($params->sel_dep_canhan,$params->sel_canhan);
            }

            if ( (int)$params->TYPE_LUUKT == 0 ) {
                $dataResult = $model->selectAll($params);
            } else {
                $dataResult = $model->selectAllByLoaiSave($params);
            }

            if((int)$params->thongke == 0){
                $key = 'ID_DEP';
            }else{
                $key = 'ID_U';
            }
            $dataResult = $this->groupDataArray($dataResult, $key);
            
            // view
            $this->view->params = $params;
            $this->view->config = $config;
            $this->view->datetxt = "Từ ngày " . $params->fromdate . "/" . $year . " đến ngày " . $params->todate . '/' . $year;
            $this->view->data = $dataResult;
            $this->view->dataGroup = $listItems;
            switch ( (int)$params->is_print ) {
                case 1:
                    $this->renderScript('baocaovanbanluuketthuc/print.phtml');
                    break;
                default:
                    $this->renderScript("baocaovanbanluuketthuc/reportview.phtml");
                    break;
            }
        }

        public function exportexcelAction()
        {
            global $auth;
            $this->_helper->layout->disableLayout();
            $config = Zend_Registry::get('config');
            $params = (object)$this->getRequest()->getParams();

            // setting date
            // year
            $year = $params->year;
            if ( (int)$year < 2013 || (int)$year > QLVBDHCommon::getYear() ) {
                $year = QLVBDHCommon::getYear();
            }
            // from date
            if ( $params->fromdate == NULL ) {
                $params->fromdate = '1/1';
            }
            // todate
            if ( $params->todate == NULL ) {
                $params->todate = '31/12';
            }

            //model
            $model = new BaocaovanbanluuketthucModel($year);
            if((int)$params->thongke == 0){
                $listItems = ThongkevanbandiModel::getPhongBan($params->sel_pb);
            }else{
                $listItems = ThongkevanbandiModel::getCaNhan($params->sel_dep_canhan,$params->sel_canhan);
            }

            if ( (int)$params->TYPE_LUUKT == 0 ) {
                $dataResult = $model->selectAll($params);
            } else {
                $dataResult = $model->selectAllByLoaiSave($params);
            }

            if((int)$params->thongke == 0){
                $key = 'ID_DEP';
            }else{
                $key = 'ID_U';
            }
            $dataResult = $this->groupDataArray($dataResult, $key);

            // view
            $this->view->params = $params;
            $this->view->config = $config;
            $this->view->datetxt = "Từ ngày " . $params->fromdate . "/" . $year . " đến ngày " . $params->todate . '/' . $year;
            $this->view->data = $dataResult;
            $this->view->dataGroup = $listItems;

            header("Content-Type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: attachment; filename=baocaovanbanluuketthuc_". date('dmY') .".xls;");
            header("Pragma: no-cache");
            header("Expires: 0");
        }

        function groupDataArray(array $array, $key_select)
        {
            $result = array();
            foreach($array as $value){
                $result[$value[$key_select]][] = $value;
            }

            return $result;
        }
    }