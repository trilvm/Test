<?php

/**
 * soanthaoController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once 'hscv/models/filedinhkemModel.php';
require_once 'hscv/models/hosocongviecModel.php';
require_once 'vbdi/models/congviecsoanthaoModel.php';
require_once 'config/vbdi.php';
// Dùng bên listAction
require_once 'hscv/models/loaihosocongviecModel.php';
require_once 'hscv/models/butpheModel.php';
require_once 'vbden/models/vbdenModel.php';
require_once 'vbden/models/vbd_dongluanchuyenModel.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'config/hscv.php';
require_once 'hscv/models/ThuMucModel.php';
require_once 'hscv/models/gen_tempModel.php';

require_once 'taphscv/models/Taphscv_TaphosocongviecModel.php';
class Vbdi_soanthaoController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
        public function indexAction() {
		// TODO Auto-generated soanthaoController::indexAction() default action
		$this->_redirect('/hscv/hscv/list');
	}
	/**
	 * The input action - show edit or new page
	 */
	public function inputAction(){
		// Lấy parameter
		global $auth;
		$user = $auth->getIdentity();
        $parameter = $this->getRequest()->getParams();        
		$this->view->wf_id_t = WFEngine::GetBeginTransition('VBSOANTHAO',$user->ID_DEP);
		$this->view->AllNextTransitions = WFEngine::GetAllNextTransitionsByTransition($this->view->wf_id_t);
		$this->view->id_loaihscv = WFEngine::GetIdLoaiHSCVFromIdT($this->view->wf_id_t);
        $id_hscv = $parameter["id_hscv"];
        $year = QLVBDHCommon::getYear();
        $this->view->year = $year;
        // New các model
        $this->congviecsoanthao = new congviecsoanthaoModel($year);          
        $userModel=new UsersModel();
        
 		// Lấy dữ liệu
        if($id_hscv>0)
        {
            $this->view->id_hscv = $id_hscv;            
            $this->view->nguoitao=$this->view->data->NGUOIYEUCAU;
            $this->view->nguoixuly=$this->view->data->NGUOIXULY;
            $this->view->data = congviecsoanthaoModel::getDetailByHSCV($year,$id_hscv);
        	$this->view->title = "Cập nhật công việc";
            //$this->view->subtitle = "Cập nhật";
        }
        else
        {
            $this->view->title = "Thêm mới công việc";
            //$this->view->subtitle = "Thêm mới";
            $this->view->nguoitao=Zend_Registry::get('auth')->getIdentity()->ID_U;
        }
        
        // Set biến cho view
        $this->view->year = $year;
        $this->view->userdata=$userModel->selectAllUsersJoinEmployees();
        
            
        QLVBDHButton::EnableSave("/vbdi/soanthao/Save");
        QLVBDHButton::EnableBack("/hscv//list");
                
        global $auth;
		$user = $auth->getIdentity();
		$this->view->user = $user;
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		//$this->view->wf_id_t = $this->parameter["wf_id_t"];
		$wf_id_t = $this->view->wf_id_t;
		//Lay danh sach nhan vien va phong ban tham gia vao transaction nay
		$arr_wf_users = WFEngine::GetAccessUserFromTransition($wf_id_t);
		
		$arr_wf_deps = WFEngine::GetAccessDepFromTransition($wf_id_t);
		//var_dump($this->view->arr_wf_deps);
		/* Loc danh sach phong ban*/
		$this->view->arr_deps_se = array();
		$this->view->arr_deps_re = array();
		foreach ($arr_wf_deps as $dep){
			if($dep['TYPE'] == 0)
				array_push($this->view->arr_deps_se,$dep);
			else 
				array_push($this->view->arr_deps_re,$dep);
		}
		
		/* Loc danh sach nhan vien*/
		$this->view->id_dep_cur_user_se = -2;
		$this->view->id_dep_cur_user_re = -2;
		$this->view->arr_users_se = array();
		$this->view->arr_users_re = array();
		foreach ($arr_wf_users as $user_it){
			if($user_it['TYPE'] == 0){
				array_push($this->view->arr_users_se,$user_it);
				if($user_it['ID_U'] == $user->ID_U)
				$this->view->id_dep_cur_user_se = $user_it['ID_DEP'];
			}
			else{ 
				array_push($this->view->arr_users_re,$user_it);
				if($user_it['ID_U'] == $user->ID_U)
				$this->view->id_dep_cur_user_re = $user_it['ID_DEP'];
			}
			
			
		}
		$tempTbl = new gen_tempModel();
		$idObject = $tempTbl->getIdTemp();
		$this->view->ID_HSCVDT=$idObject;
		
	}
    /**
	 * The input action - show edit or new page
	 */
	public function sendAction(){
	    // Lấy parameter
        $parameter = $this->getRequest()->getParams();        
        $id_hscv = $parameter["id_hscv"];
                 
        // New các model
        $this->congviecsoanthao = new congviecsoanthaoModel();        
        
        $this->view->data = $this->congviecsoanthao->findByHscv($id_hscv)->current();
        $this->view->title = MSG11001001;
        //$this->view->subtitle = MSG11001012;

        QLVBDHButton::AddButton("Gửi","","SaveButton","PQClick();");
        QLVBDHButton::EnableBack("/hscv/hscv/list");
        
        global $auth;
		$user = $auth->getIdentity();
		
		//parameter
		$this->parameter = $this->getRequest()->getParams();
		$this->view->wf_id_t = $this->parameter["wf_id_t"];
	}
    /**
     * The save action - insert if item is new or update if if item is exist
     */
    public function saveAction(){

        //Lay cac tham so
    	$parameter = $this->getRequest()->getParams();	
		$parameter['NGUOIXULY'] = $parameter['wf_nextuser'];		
       	 global $auth;
		$user = $auth->getIdentity();
                $year=QLVBDHCommon::getYear();
        $db = Zend_Db_Table::getDefaultAdapter();
      	$congviecsoanthao = new congviecsoanthaoModel($year);
        $filedinhkem =  new filedinhkemModel($year);        
       
        $id_hscv = $parameter["id_hscv"];
        if($parameter["id_hscv"]>0)
        {
        	//truong hop cap nhat
        	//Chua su dung
        	try
        	{
	       		$congviecsoanthao->update(array("NAME"=>$parameter["NAME"],
                                                    //tuan pp thêm độ quan trọng
                                                    "DOQUANTRONG"=>$parameter["DOQUANTRONG"]),"ID_HSCV=$id_hscv");
				$db = Zend_Db_Table::getDefaultAdapter();
				//cap nhat ten cong viec
				$db->update(QLVBDHCommon::Table("hscv_hosocongviec"),array("NAME"=>$parameter["NAME"]),"ID_HSCV=$id_hscv");
				//cap nhat thu muc ho so cong viec
				if($parameter["ID_THUMUC"])
					thumucModel::updateThumucHscv($id_hscv,$parameter["ID_THUMUC"]);
		 	}
        	
        	catch(Exception $ex)
        	{
        		exit;        		
        	}         
        }
        else
        {
            //truong hop them moi
            //tao model ho so cong viec
            $hscv = new hosocongviecModel();
            
            $ngaykt = QLVBDHCommon::addDate(time(),$parameter["HANXULY"]);
            $id_hscv = $hscv->CreateHSCV(
	            $parameter["NAME"],
	            1,
	            $parameter["id_loaihscv"],
	            date("Y-m-d H:i:s"), //ngay bat dau
	            date("Y-m-d H:i:s",$ngaykt), //ngay ket thuc
	            $parameter["NGUOIYEUCAU"],
	            $parameter["NGUOIXULY"],
	        	$parameter["NOIDUNG"], //extend
            	$parameter["HANXULY"],
				$parameter["BEFORE"]*$parameter["MULTIPLEBEFORE"],
				$parameter["SMS"],
				$parameter["EMAIL"],
				$parameter["NGUOIXULY"]
	        );

	        if($id_hscv>0){
				$congviecsoanthao->insert(
					array(
					"NAME"=>$parameter["NAME"],
					"ID_HSCV"=>$id_hscv,
					"NOIDUNG"=>$parameter["NOIDUNG"],
					"GHICHU"=>$parameter["GHICHU"],
					"TRANGTHAI"=>$parameter["TRANGTHAI"],
					"NGUOIYEUCAU"=>$parameter["NGUOIYEUCAU"],
					"NGUOIXULY"=>$parameter["NGUOIXULY"],
					"NGUOITAO"=>$user->ID_U,
					"NGAYTAO"=>date("Y-m-d"),
					"IS_NOIBO"=>$parameter["noibo"],
                                            //tuanpp thêm độ quan trọng,mô tả
                                        "DOQUANTRONG"=>$parameter["DOQUANTRONG"],                                            
                                        "MOTA"=>$parameter["MOTA"]
                                            //tuanpp End - thêm độ quan trọng,mô tả
					)
					
				);
				
				//them người phối hợp
				$id_u_phs = $parameter["ID_U"];
				//var_dump($id_u_phs); exit;
				foreach($id_u_phs as $idu){
					
					try{
						$db->insert(QLVBDHCommon::Table("HSCV_PHOIHOP"),array("ID_U_YC"=>$user->ID_U,"ID_U"=>$idu,"ID_HSCV"=>$id_hscv));
						$r = $db->query("SELECT NAME FROM ".QLVBDHCommon::Table("HSCV_HOSOCONGVIEC")." WHERE ID_HSCV=?",$id_hscv);
						$r = $r->fetch();
						QLVBDHCommon::SendMessage(
							$user->ID_U,
							$idu,
							$r["NAME"],
							"hscv/hscv/list/code/phoihop"
						);
					}catch(Exception $ex){
						echo $ex->__toString(); exit;
					}
				}
				

				if($parameter["ID_THUMUC"])
					thumucModel::updateThumucHscv($id_hscv,$parameter["ID_THUMUC"]);
	            for($i=0;$i<count($parameter["idFile"]);$i++){   
	                $filedinhkem->update(
	                	array(
	                		"ID_OBJECT"=>$id_hscv,
	                		"TYPE"=>1),
	                		"MASO='".$parameter["idFile"][$i]."'"
	                	);
	            }
				if($parameter["chuyenbanhanh"]==1){
					WFEngine::SendNextUserByObjectId($id_hscv,$parameter["wf_nexttransition"],$parameter["NGUOIYEUCAU"],$parameter["NGUOIXULY"],"",0);
				}
			}else{
				
			}
	    }
		
	    //exit;
            //update hscv_duthao set ID_HSCV 
             if($parameter["idHSCV"] >0){               			
                global $db;
                $db->update('hscv_duthao_'.QLVBDHCommon::getYear(),array('ID_HSCV' => $id_hscv), "ID_HSCV=".$parameter['idHSCV']);
			 }
        $this->_redirect("/hscv/hscv/list/code/old");        
    }
    /**
     * The delete action - delete item
     */
    public function deleteAction(){        
        $this->view->parameter =  $this->getRequest()->getParams();
        $parameter = $this->getRequest()->getParams(); 
        // Set Year
        $year = $parameter["year"];
        Zend_Registry::set('year',$year);
        $year = (!isset($year) || $year == '' || $year == 0)?Zend_Date::now()->get(Zend_Date::YEAR):$year;
        
        $this->congviecsoanthao = new congviecsoanthaoModel();
        try{
            $this->congviecsoanthao->delete("ID_VBDI_CVST IN (".implode(",",$this->view->parameter["DEL"]).")");
        }catch(Exception $ex){
        }
        $this->_redirect("/hscv/hscv/list");        
    }
}
