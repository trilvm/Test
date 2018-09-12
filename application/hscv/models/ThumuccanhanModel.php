<?
class ThumuccanhanModel {
	static function saveThuMuc($id_tmcn,$name,$id_parent){
		//lay thong tin user tren session
		//echo "dddd";
		$user = Zend_Registry::get('auth')->getIdentity();
		//db adapter
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$arraydata = array();
		if($id_tmcn > 0){
			//truong hop cap nhat
			$sql = " update hscv_thumuccanhan Set NAME = ? , ID_PARENT = ? , ID_U =? where ID_TMCN = ? ";
			$arraydata = array($name,$id_parent,$user->ID_U,$id_tmcn);
		}else{
			//truong hop them moi
			$sql = " insert into hscv_thumuccanhan (NAME,ID_PARENT,ID_U) values (?,?,?) ";
			$arraydata = array($name,$id_parent,$user->ID_U);
		}
		
		//try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute($arraydata);
			return $dbAdapter->lastInsertId();
		//}catch(Exception $ex){
			return 0;
		//}
		
	}
	
	


	static function toComboThuMucPrivate(){
		//$folder ;
		//QLVBDHCommon::GetTree(&$folder,"HSCV_THUMUCCANHAN","ID_TMCN","ID_PARENT",1,1);
		//return $folder;
		$user = Zend_Registry::get('auth')->getIdentity();
		$lists = array();
		$params = array (
			'ID_U' => $user->ID_U
		);
		QLVBDHCommon::GetTreeWithCase(&$lists,"HSCV_THUMUCCANHAN","ID_TMCN","ID_PARENT",1,1,$params);
		return $lists;
	}

	static function getAllByIdU($id_u){
		
		/*$sql = "
			select * from hscv_thumuccanhan where ID_U = ?
		";
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_u));
			return $qr->fetchAll();
		} catch (Exception $ex){
			return array();
		}*/
		$lists = array();
		$params = array (
			'ID_U' => $id_u	
		);
		QLVBDHCommon::GetTreeWithCase(&$lists,"HSCV_THUMUCCANHAN","ID_TMCN","ID_PARENT",1,1,$params);
		return $lists;
	}

	static function getThumucById($id_tm, $id_u){
		
		if(!$id_tm || $id_tm < 1)
			return array();
		$sql = "
			select * from hscv_thumuccanhan where ID_U = ? and ID_TMCN = ?
		";
		
		try{
			$dbAdapter = Zend_Db_Table::getDefaultAdapter();
			$qr = $dbAdapter->query($sql,array($id_u,$id_tm));
			return $qr->fetch();
		} catch (Exception $ex){
			return array();
		}
	
	}

	static function insertHscvIntoTMCN($id_hscv,$id_tmcn){
		
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		
		//Kiem tra user co quyen luu ho so vao thu muc ca nhan hay khong
		$sql = " select count(*) as DEM from hscv_thumuccanhan  where ID_TMCN = ? and ID_U = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_tmcn,$user->ID_U));
			$re = $qr->fetch();
			$count = $re["DEM"];
			//echo $count;
			if($count <= 0)
				return -1;
		}catch(Exception $ex){
			return 0;
		}
		//kiem tra xem ho so da co trong thu muc nay chua

		$sql = " select count(*) as DEM from ".QLVBDHCommon::Table("hscv_fk_tmcn")."  where ID_TMCN = ? and ID_OBJECT = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_tmcn,$id_hscv));
			$re = $qr->fetch();
			$count = $re["DEM"];
			if($count > 0)
				return -2;
		}catch(Exception $ex){
			return 0;
		}
		
		//them moi hso vao thu muc ca nhan
		$sql = "  insert into ". QLVBDHCommon::Table("hscv_fk_tmcn") ." ( ID_OBJECT , ID_TMCN , TYPE )  values ( ? , ? , ?) ";
		$type = 1;
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_hscv,$id_tmcn,$type));
		}catch(Exception $ex){
			return 0;
		}
		return 1;
	}

	static function getTMCNByHSCV($id_hscv){
	
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select tmcn.* from ".QLVBDHCommon::Table("hscv_fk_tmcn")." fk
		inner join hscv_thumuccanhan  tmcn on fk.ID_TMCN = tmcn.ID_TMCN
		where fk.ID_OBJECT = ? and tmcn.ID_U = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_hscv,$user->ID_U));
			$re = $qr->fetchAll();
			return $re;
		}catch(Exception $ex){
			return array();
		}
	}

	static function removeHscv($id_hscv,$id_tmcn){
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		//kiem tra thu muc co phai thuoc user dang su dung chuong trinh hay khong
		$sql = " select count(*) as DEM from hscv_thumuccanhan  where ID_TMCN = ? and ID_U = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_tmcn,$user->ID_U));
			$re = $qr->fetch();
			$count = $re["DEM"];
			
			if($count <= 0)
				return -1;
		}catch(Exception $ex){
			return 0;
		}
		//kiem tra thu muc do co thu muc con hay khong
		/*$sql = " select count(*) as DEM from hscv_thumuccanhan  where ID_PARENT = ? and ID_U = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_tmcn,$user->ID_U));
			$re = $qr->fetch();
			$count = $re["DEM"];
			
			if($count <= 0)
				return -2;
		}catch(Exception $ex){
			return 0;
		}*/
		
		//Xoa ho so cong viec khoi thu muc
		$sql = "  delete from ". QLVBDHCommon::Table("hscv_fk_tmcn") ."  where ID_OBJECT = ? and ID_TMCN = ? and TYPE = ? ";
		$type = 1;
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_hscv,$id_tmcn,$type));
		}catch(Exception $ex){
			return 0;
		}
		return 1;
	}

	static function ToTree($data,$id_parent,$name_tree,&$html,$sel){                        
        $isFirst = false;
        foreach($data as $row){
        	if($row["ID_PARENT"]==$id_parent){
        		if(!$isFirst){
        			$isFirst=true;
        			if($id_parent==0)
                        $html .= "<ul ref='open' id=" . $name_tree . " class=treeview>";
                            else
                                $html .= "<ul ref='open'>";
        		}
                $html .= "<li>";
                
                	if($row['ID_TMCN']!=$sel){
                		$html .= "<a href='/hscv/thumuccanhan/list/id_thumuc/".$row["ID_TMCN"]."'>".$row["NAME"]."</a>";
                	}else{
                		$html .= "<a href='/hscv/thumuccanhan/list/id_thumuc/".$row["ID_TMCN"]."'><b>".$row["NAME"]."</b></a>";
                	}
               
        	    ThumuccanhanModel::ToTree($data,$row["ID_TMCN"],$name_tree,&$html,$sel);
        	    $html .= "</li>";
        	}
        }
        if($isFirst)
            $html .= "</ul>";
        return $html;
    }

	static function getListHSCV($id_tmcn,$offset,$limit){
		
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		
		$sql = " select hscv.* from " .QLVBDHCommon::Table("hscv_hosocongviec"). " hscv   
		inner join ".QLVBDHCommon::Table("hscv_fk_tmcn")." fk on hscv.ID_HSCV = fk.ID_OBJECT
		inner join hscv_thumuccanhan  tmcn on fk.ID_TMCN = tmcn.ID_TMCN
		where  tmcn.ID_U = ? and fk.ID_TMCN = ?    $strlimit" ;
		try{
			$qr = $dbAdapter->query($sql, array($user->ID_U,$id_tmcn));
			$re = $qr->fetchAll();
			return $re;
		}catch(Exception $ex){
			return array();
		}
	}
	static function countListHSCV($id_tmcn){
		
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$sql = " select count(*) as DEM from " .QLVBDHCommon::Table("hscv_hosocongviec"). " hscv   
		inner join ".QLVBDHCommon::Table("hscv_fk_tmcn")." fk on hscv.ID_HSCV = fk.ID_OBJECT
		inner join hscv_thumuccanhan  tmcn on fk.ID_TMCN = tmcn.ID_TMCN
		where  tmcn.ID_U = ? and fk.ID_TMCN = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($user->ID_U,$id_tmcn));
			$re = $qr->fetch();
			return $re["DEM"];
		}catch(Exception $ex){
			return 0;
		}
	}

	static function deleteThumuc($id_tmcn){
		$user = Zend_Registry::get('auth')->getIdentity();
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	
		//kiem tra thu muc do co thu muc con hay khong
		$sql = " select count(*) as DEM from hscv_thumuccanhan  where ID_PARENT = ? and ID_U = ?" ;
		try{
			$qr = $dbAdapter->query($sql, array($id_tmcn,$user->ID_U));
			$re = $qr->fetch();
			$count = $re["DEM"];
			
			if($count > 0)
				return -1;
		}catch(Exception $ex){
			return 0;
		}
		
		//Xoa ho so cong viec khoi thu muc
		$sql = "  delete from ". hscv_thumuccanhan ."  where ID_TMCN = ? and ID_U = ?  ";
		$type = 1;
		try{
			$stm = $dbAdapter->prepare($sql);
			$stm->execute(array($id_tmcn,$user->ID_U));
		}catch(Exception $ex){
			return 0;
		}
		return 1;
	}

}