<?php
require_once 'auth/models/ResourceUserModel.php';
class chuyennoiboModel {

	

	function getIDCQ($id_dep,&$id_depcha){
		$db =  Zend_Db_Table::getDefaultAdapter();		
		$sql = "
			select * from qtht_departments  			
			where ID_DEP = ?			
		";		
		$qr = $db->query($sql,array($id_dep));
		$re =  $qr->fetch();	
        if($re['ID_DEP_PARENT']==1){
			$id_depcha=$re['ID_CQ'];
		}else{
			chuyennoiboModel::getIDCQ($re['ID_DEP_PARENT'],&$id_depcha);
		}
		
		return $id_depcha;
		
	}
	
	function luuChuyennoibocobutphe($id_vbd,$nguoichuyen,$arr_cq,$noidung){
		$db = Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$sql = " select ID_DEP from qtht_departments where ID_CQ = ? AND ACTIVE=1 ";
		foreach($arr_cq as $cq){
			$qr = $db->query($sql,array($cq));
			$dtphong = $qr->fetch();
			$paramcnb = array (
					"ID_VBD" => $id_vbd,
					"NGUOICHUYEN"=>$nguoichuyen  , 
					"CQCHUYEN" => (int)$user->ID_CQ ,
					
					"CQNHAN" => $cq,
					"PHONGNHAN"=>$dtphong["ID_DEP"],
					"NGAYGUI"=> date("Y-m-d H:i:s"),
					"NOIDUNG" =>$noidung
				);
			$db->insert(QLVBDHCommon::Table("vbd_luanchuyennoibo"),$paramcnb);
			chuyennoiboModel::savevbdentotempcobutphe($id_vbd,(int)$user->ID_CQ,$nguoichuyen,$noidung,(int)$cq,date("Y-m-d H:i:s"),QLVBDHCommon::getYear(),$noidung,$user->ID_U,date("Y-m-d H:i:s"));
		}
	}
	
	function luuChuyennoibo($id_vbd,$nguoichuyen,$arr_cq,$noidung){
		$db = Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$sql = " select ID_DEP from qtht_departments where ID_CQ = ? AND ACTIVE=1 ";
		foreach($arr_cq as $cq){
			$qr = $db->query($sql,array($cq));
			$dtphong = $qr->fetch();
			$paramcnb = array (
					"ID_VBD" => $id_vbd,
					"NGUOICHUYEN"=>$nguoichuyen  , 
					"CQCHUYEN" => (int)$user->ID_CQ ,
					
					"CQNHAN" => $cq,
					"PHONGNHAN"=>$dtphong["ID_DEP"],
					"NGAYGUI"=> date("Y-m-d H:i:s"),
					"NOIDUNG" =>$noidung
				);
			$db->insert(QLVBDHCommon::Table("vbd_luanchuyennoibo"),$paramcnb);
			chuyennoiboModel::savevbdentotemp($id_vbd,(int)$user->ID_CQ,$nguoichuyen,$noidung,(int)$cq,date("Y-m-d H:i:s"),QLVBDHCommon::getYear());
		}
	}

	
	function getLuanchuyennoibo($id_vbd){
		$sql = "
			select lcnb.* , CONCAT(emp.FIRSTNAME ,' ' , emp.LASTNAME) as NAMENC , dep.NAME as NAMEDEPN
			
			from ".  QLVBDHCommon::Table("vbd_luanchuyennoibo"). " lcnb
			inner join qtht_users u on lcnb.NGUOICHUYEN = u.ID_U
			inner join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			inner join qtht_departments dep on lcnb.PHONGNHAN = dep.ID_DEP
			where lcnb.ID_VBD = ? ORDER BY lcnb.NGAYGUI DESC
		";
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$qr = $db->query($sql,array((int)$id_vbd));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	
	function vbdi_getLuanchuyennoibo($id_vbdi){
		$sql = "
			select lcnb.* , CONCAT(emp.FIRSTNAME ,' ' , emp.LASTNAME) as NAMENC , dep.NAME as NAMEDEPN
			
			from ".  QLVBDHCommon::Table("vbdi_luanchuyennoibo"). " lcnb
			left join qtht_users u on lcnb.NGUOICHUYEN = u.ID_U
			left join qtht_employees emp on u.ID_EMP = emp.ID_EMP
			left join qtht_departments dep on lcnb.PHONGNHAN = dep.ID_DEP
			where lcnb.ID_VBDI = ? ORDER BY lcnb.NGAYGUI DESC
		";
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$qr = $db->query($sql,array((int)$id_vbdi));
			return $qr->fetchAll();
		}catch(Exception $ex){
			return array();
		}
	}
	
	function getDataCQNoibo(){
		$db = Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		chuyennoiboModel::getIDCQ($user->ID_DEP,&$id_depcha);
		$sql = "
			select distinct cq.* from 
			qtht_departments dep
			inner join vb_coquan cq on dep.ID_CQ = cq.ID_CQ
			where 
			dep.ID_CQ > 0 AND dep.ACTIVE=1
		";		
		try{
			$qr = $db->query($sql);
			return $qr->fetchAll();
		}catch(Exception $ex){
		
		}
		
	}
	// tra ve id cua van ban trong ban tam
	
	
	function savevbdentotemp($id_vbd,$cqchuyen,$nguoichuyen,$noidungchuyen,$cqnhan,$ngaygui,$year){
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$sql = " select  vbd.* , butphe.NGUOIKY as NGUOIBUTPHE , butphe.NOIDUNG as ND_BUTPHE , butphe.NGAYBP as NGAYBUTPHE from " . QLVBDHCommon::Table("vbd_vanbanden") . "   vbd
		left join " . QLVBDHCommon::Table("hscv_butphe") . " butphe on   vbd.ID_VBD = butphe.ID_VBD
		where vbd.ID_VBD = ?   ";
		
		//lay noi dung but phe
		

		$qr = $db->query($sql,array($id_vbd));
		$data_vbden = $qr->fetch();
		//var_dump($data_vbden); exit;
		
		
		$sql = "
			insert into vbd_nhannoibo (
				ID_VBD,
				ID_LVVB,
				ID_CQ,
				ID_LVB,
				SOKYHIEU,
				NGAYBANHANH,
				COQUANBANHANH_TEXT,
				TRICHYEU,
				SOBAN,
				SOTO,
				DOMAT,
				DOKHAN,
				NGUOIKY,
				SOKYHIEU_IN,
				CQ_CHUYEN,
				NGUOICHUYEN,
				NOIDUNGCHUYEN,
				CQ_NHAN,
				NGAYGUI,
				YEAR,
				NOIDUNG_BUTPHE,
				NGUOIBUTPHE,
				NGAYBUTPHE
				
			)

			values ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )
		";
		
		


		$params = array (
			$data_vbden["ID_VBD"],
			$data_vbden["ID_LVVB"],
			$data_vbden["ID_CQ"],
			$data_vbden["ID_LVB"],
			$data_vbden["SOKYHIEU"],
			$data_vbden["NGAYBANHANH"],
			$data_vbden["COQUANBANHANH_TEXT"],
			$data_vbden["TRICHYEU"],
			$data_vbden["SOBAN"],
			$data_vbden["SOTO"],
			$data_vbden["DOMAT"],
			$data_vbden["DOKHAN"],
			$data_vbden["NGUOIKY"],
			$data_vbden["SOKYHIEU_IN"],
			$cqchuyen,
			$nguoichuyen,
			$noidungchuyen,
			$cqnhan,
			$ngaygui,
			$year,
			$data_vbden["ND_BUTPHE"],
			$data_vbden["NGUOIBUTPHE"],
			$data_vbden["NGAYBUTPHE"]
			
		
		);
		

		try{
			$stm = $db->prepare($sql);
			$stm->execute($params);
			//lay id vua mới insert 
			$sql = "select MAX(ID_VBDNB) as ID_VBDNB from vbd_nhannoibo";
			$stm = $db->query($sql);
			$dtid = $stm->fetch();
			$idvbdnb = $dtid["ID_VBDNB"];
			//lay file dinh kem
			filedinhkemModel::copyFile($year,$id_vbd,$idvbdnb,3,15);
			/*
			$sql = " select * from ". QLVBDHCommon::Table("gen_filedinhkem")." where ID_OBJECT = ? and TYPE = 3 ";
			$qr =  $db->query($sql,array($id_vbd));
			$re = $qr->fetchAll();
			foreach($re as $item ){
				$item["ID_OBJECT"] = $idvbdnb ;
				$item["TYPE"] = 15 ;
				$item["USER"] = $user->ID_U ;
				unset($item["ID_DK"]);
				//$db->insert(QLVBDHCommon::Table("gen_filedinhkem"),$item);
				//$idf = $db->lastInsertId();
				//$item["MASO"] = md5($idf.$item["FILENAME"].$item["TIME_UPDATE"]); 
				$db->insert(QLVBDHCommon::Table("gen_filedinhkem"),$item);
			}
			*/
			return 1;
		}catch(Exception $ex){
			
			return 0;
		}


	}

	
	function savevbdentotempcobutphe($id_vbd,$cqchuyen,$nguoichuyen,$noidungchuyen,$cqnhan,$ngaygui,$year,$noidungbutphe,$nguoibutphe,$ngaybutphe){
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$sql = " select  vbd.* from " . QLVBDHCommon::Table("vbd_vanbanden") . "   vbd
		
		where vbd.ID_VBD = ?   ";
		
		//lay noi dung but phe
		

		$qr = $db->query($sql,array($id_vbd));
		$data_vbden = $qr->fetch();
		//var_dump($data_vbden); exit;
		
		
		$sql = "
			insert into vbd_nhannoibo (
				ID_VBD,
				ID_LVVB,
				ID_CQ,
				ID_LVB,
				SOKYHIEU,
				NGAYBANHANH,
				COQUANBANHANH_TEXT,
				TRICHYEU,
				SOBAN,
				SOTO,
				DOMAT,
				DOKHAN,
				NGUOIKY,
				SOKYHIEU_IN,
				CQ_CHUYEN,
				NGUOICHUYEN,
				NOIDUNGCHUYEN,
				CQ_NHAN,
				NGAYGUI,
				YEAR,
				NOIDUNG_BUTPHE,
				NGUOIBUTPHE,
				NGAYBUTPHE
				
			)

			values ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )
		";
		
		


		$params = array (
			$data_vbden["ID_VBD"],
			$data_vbden["ID_LVVB"],
			$data_vbden["ID_CQ"],
			$data_vbden["ID_LVB"],
			$data_vbden["SOKYHIEU"],
			$data_vbden["NGAYBANHANH"],
			$data_vbden["COQUANBANHANH_TEXT"],
			$data_vbden["TRICHYEU"],
			$data_vbden["SOBAN"],
			$data_vbden["SOTO"],
			$data_vbden["DOMAT"],
			$data_vbden["DOKHAN"],
			$data_vbden["NGUOIKY"],
			$data_vbden["SOKYHIEU_IN"],
			$cqchuyen,
			$nguoichuyen,
			$noidungchuyen,
			$cqnhan,
			$ngaygui,
			$year,
			$noidungbutphe,
			$nguoibutphe,
			$ngaybutphe
			
		
		);
		

		try{
			$stm = $db->prepare($sql);
			$stm->execute($params);
			//lay id vua mới insert 
			$sql = "select MAX(ID_VBDNB) as ID_VBDNB from vbd_nhannoibo where ID_VBD = ? ";
			$stm = $db->query($sql,array($id_vbd));
			$dtid = $stm->fetch();
			$idvbdnb = $dtid["ID_VBDNB"];
			//lay file dinh kem
			filedinhkemModel::copyFile($year,$id_vbd,$idvbdnb,3,15);
			/*
			$sql = " select * from ". QLVBDHCommon::Table("gen_filedinhkem")." where ID_OBJECT = ? and TYPE = 3 ";
			$qr =  $db->query($sql,array($id_vbd));
			$re = $qr->fetchAll();
			foreach($re as $item ){
				$item["ID_OBJECT"] = $idvbdnb ;
				$item["TYPE"] = 15 ;
				$item["USER"] = $user->ID_U ;
				unset($item["ID_DK"]);
				$db->insert(QLVBDHCommon::Table("gen_filedinhkem"),$item);
				//$idf = $db->lastInsertId();
				//$item["MASO"] = md5($idf.$item["FILENAME"].$item["TIME_UPDATE"]); 
				//$db->update(QLVBDHCommon::Table("gen_filedinhkem"),$item,"ID_DK=".$idf);
			}
			*/
			return 1;
		}catch(Exception $ex){
			
			return 0;
		}


	}
	function getInfovbnhannoibo($id_vbdnb){
		
		$sql = "
			select * from vbd_nhannoibo 
			where ID_VBDNB = ?
		";
		
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$qr = $db->query($sql,array((int)$id_vbdnb));
			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}
	
	function getInfonamevbnhannoibo($id_vbdnb){
		
		$sql = "
			select vbnb.* , lvb.NAME as LVBNAME , cq.NAME as NAMECQCHUYEN from vbd_nhannoibo vbnb 
			left join vb_loaivanban lvb on vbnb.ID_LVB = lvb.ID_LVB
			left join vb_coquan cq on vbnb.CQ_CHUYEN = cq.ID_CQ
			where ID_VBDNB = ?
		";
		
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$qr = $db->query($sql,array((int)$id_vbdnb));
			return $qr->fetch();
		}catch(Exception $ex){
			return array();
		}
	}


	function vbdi_luuchuyennoibo($id_vbdi,$nguoichuyen,$arr_cq,$noidung){
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$sql = " select ID_DEP from qtht_departments where ID_CQ = ? AND ACTIVE=1 ";
		foreach($arr_cq as $cq){
			$qr = $db->query($sql,array($cq));
			$dtphong = $qr->fetch();
			$paramcnb = array (
					"ID_VBDI" => $id_vbdi,
					"NGUOICHUYEN"=>$nguoichuyen  , 
					"CQCHUYEN" => (int)$user->ID_CQ ,
					"CQNHAN" => $cq,
					"PHONGNHAN"=>$dtphong["ID_DEP"],
					"NGAYGUI"=> date("Y-m-d H:i:s"),
					"NOIDUNG" =>$noidung
				);
			$db->insert(QLVBDHCommon::Table("vbdi_luanchuyennoibo"),$paramcnb);
			chuyennoiboModel::savevbditotemp($id_vbdi,(int)$user->ID_CQ,$nguoichuyen,$noidung,(int)$cq,date("Y-m-d H:i:s"),QLVBDHCommon::getYear());
		}
	
	}

	function savevbditotemp($id_vbdi,$cqchuyen,$nguoichuyen,$noidungchuyen,$cqnhan,$ngaygui,$year){
		
		$db = Zend_Db_Table::getDefaultAdapter();
		//lay co quan ban hanh
		
		
		$sql = " select CONCAT(emp.FIRSTNAME,' ', emp.LASTNAME ) as NAME_NGUOIKY , cquan.NAME as CQBANHANH_TEXT,vbdi.* from " . QLVBDHCommon::Table("vbdi_vanbandi") . " vbdi 
		LEFT JOIN qtht_users u on vbdi.NGUOIKY = u.ID_U
		LEFT JOIN qtht_employees emp on u.ID_EMP = emp.ID_EMP
		LEFT JOIN vb_coquan cquan on vbdi.ID_CQ = cquan.ID_CQ 
		where ID_VBDI = ?   ";
		
		

		$qr = $db->query($sql,array($id_vbdi));
		$data_vbden = $qr->fetch();
		$cqbanhanh_text = $data_vbden["COQUANBANHANH_TEXT"];

		if($cqbanhanh_text == "")
			$cqbanhanh_text = $data_vbden["CQBANHANH_TEXT"];
		$sql = "
			insert into vbd_nhannoibo (
				ID_VBDI,
				ID_LVVB,
				ID_CQ,
				ID_LVB,
				SOKYHIEU,
				NGAYBANHANH,
				COQUANBANHANH_TEXT,
				TRICHYEU,
				SOBAN,
				SOTO,
				DOMAT,
				DOKHAN,
				NGUOIKY,
				SOKYHIEU_IN,
				CQ_CHUYEN,
				NGUOICHUYEN,
				NOIDUNGCHUYEN,
				CQ_NHAN,
				NGAYGUI,
				YEAR
			
			)

			values ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )
		";
		
		


		$params = array (
			$data_vbden["ID_VBDI"],
			$data_vbden["ID_LVVB"],
			$data_vbden["ID_CQ"],
			$data_vbden["ID_LVB"],
			$data_vbden["SOKYHIEU"],
			$data_vbden["NGAYBANHANH"],
			$cqbanhanh_text,
			$data_vbden["TRICHYEU"],
			$data_vbden["SOBAN"],
			$data_vbden["SOTO"],
			$data_vbden["DOMAT"],
			$data_vbden["DOKHAN"],
			$data_vbden["NAME_NGUOIKY"],
			$data_vbden["SOKYHIEU_IN"],
			$cqchuyen,
			$nguoichuyen,
			$noidungchuyen,
			$cqnhan,
			$ngaygui,
			$year
		
		
		);

		
		try{
			$stm = $db->prepare($sql);
			$stm->execute($params);

			$sql = "select MAX(ID_VBDNB) as ID_VBDNB from vbd_nhannoibo where ID_VBDI = ? ";
			$stm = $db->query($sql,array($id_vbdi));
			$dtid = $stm->fetch();
			$idvbdnb = $dtid["ID_VBDNB"];
			//lay file dinh kem
			filedinhkemModel::copyFile($year,$id_vbdi,$idvbdnb,5,15);
			/*
			$sql = " select * from ". QLVBDHCommon::Table("gen_filedinhkem")." where ID_OBJECT = ? and TYPE = 5 ";
			$qr =  $db->query($sql,array($id_vbdi));
			$re = $qr->fetchAll();
			foreach($re as $item ){
				$item["ID_OBJECT"] = $idvbdnb ;
				$item["TYPE"] = 15 ;
				$item["USER"] = $user->ID_U ;
				unset($item["ID_DK"]);
				$db->insert(QLVBDHCommon::Table("gen_filedinhkem"),$item);
			}*/
			return 1;
		}catch(Exception $ex){
			return 0;
		}

	}
	

	function countvbnoibo($parameter,$offset,$limit,$order){
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
			//Check cơ quan
		
		$where = "(1=1)";
		$join = "";
		$select = "";
		$param = array();
		if($parameter['ID_CQ']>0 && $parameter['ID_CQ']!=""){
			$where .= " and vbd.ID_CQ = ?";
			$param[] = $parameter['ID_CQ'];
		}
		else if($parameter['COQUANBANHANH_TEXT']!="") 
		{
			$where .= " and vbd.COQUANBANHANH_TEXT = ?";
			$param[] = $parameter['COQUANBANHANH_TEXT'];
		}
		//check sổ văn bản
	    if($parameter['ID_SVB']>0 && $parameter['ID_SVB']!=""){
			$where .= " and vbd.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		
	    //Check ngày đến bd
		if($parameter['NGAYDEN_BD']!=""){
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:01";
			$where .= " and vbd.NGAYDEN >= ?";
			$param[] = $ngayden_bd;
		}
		//Check ngày đến kt
		if($parameter['NGAYDEN_KT']!=""){
			$ngayden_kt = $parameter['NGAYDEN_KT']." 23:59:59";
			$where .= " and vbd.NGAYDEN <= ?";
			$param[] = $ngayden_kt;
		}
	    //Check ngày ban hành bd
		if($parameter['NGAYBANHANH_BD']!=""){
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}
		//Check ngày ban hành kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}


		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$sokyhieu_in = (int)$parameter['SOKYHIEU'];
			if($sokyhieu_in >0){
				$where .= " and (vbd.SOKYHIEU = ? OR vbd.SOKYHIEU_IN = ?)";
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbd.SOKYHIEU = ? ";
				$param[] = $parameter['SOKYHIEU'];
				
			}
		}
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}
		//Fulltext
		if($parameter['TRICHYEU']!=""){
			
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_NHANNOIBO'));
				$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
				$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				if($isseeall){
					$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INNAME` DESC";
				}
			
		}
		chuyennoiboModel::getIDCQ($user->ID_DEP,&$id_depcha);
		$param[] = $id_depcha;
		$sql = "
			select count(*) as  DEM from vbd_nhannoibo vbd 
			
			where $where and CQ_NHAN = ?
		";

		$qr = $db->query($sql,$param);
		$re =  $qr->fetch();
		return $re["DEM"];
	}

	function selectAllvbnoibo($parameter,$offset,$limit,$order){
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
			//Check cơ quan
		
		$where = "(1=1)";
		$join = "";
		$select = "";
		$param = array();
		if($parameter['ID_CQ']>0 && $parameter['ID_CQ']!=""){
			$where .= " and vbd.ID_CQ = ?";
			$param[] = $parameter['ID_CQ'];
		}
		else if($parameter['COQUANBANHANH_TEXT']!="") 
		{
			$where .= " and vbd.COQUANBANHANH_TEXT = ?";
			$param[] = $parameter['COQUANBANHANH_TEXT'];
		}
		//check sổ văn bản
	    if($parameter['ID_SVB']>0 && $parameter['ID_SVB']!=""){
			$where .= " and vbd.ID_SVB = ?";
			$param[] = $parameter['ID_SVB'];
		}
		
		//check loại văn bản
		if($parameter['ID_LVB']>0 && $parameter['ID_LVB']!=""){
			$where .= " and vbd.ID_LVB = ?";
			$param[] = $parameter['ID_LVB'];
		}
		
	    //Check ngày đến bd
		if($parameter['NGAYDEN_BD']!=""){
			$ngayden_bd = $parameter['NGAYDEN_BD']." 00:00:01";
			$where .= " and vbd.NGAYDEN >= ?";
			$param[] = $ngayden_bd;
		}
		//Check ngày đến kt
		if($parameter['NGAYDEN_KT']!=""){
			$ngayden_kt = $parameter['NGAYDEN_KT']." 23:59:59";
			$where .= " and vbd.NGAYDEN <= ?";
			$param[] = $ngayden_kt;
		}
	    //Check ngày ban hành bd
		if($parameter['NGAYBANHANH_BD']!=""){
			$ngaybanhanh_bd = $parameter['NGAYBANHANH_BD']." 00:00:01";
			$where .= " and vbd.NGAYBANHANH >= ?";
			$param[] = $ngaybanhanh_bd;
		}
		//Check ngày ban hành kt
		if($parameter['NGAYBANHANH_KT']!=""){
			$ngaybanhanh_kt = $parameter['NGAYBANHANH_KT']." 23:59:59";
			$where .= " and vbd.NGAYBANHANH <= ?";
			$param[] = $ngaybanhanh_kt;
		}


		//check so ky hieu
		if($parameter['SOKYHIEU']!=""){
			$sokyhieu_in = (int)$parameter['SOKYHIEU'];
			if($sokyhieu_in >0){
				$where .= " and (vbd.SOKYHIEU = ? OR vbd.SOKYHIEU_IN = ?)";
				$param[] = $parameter['SOKYHIEU'];
				$param[] = $sokyhieu_in;
			}else if($sokyhieu_in == 0) {
				$where .= " and vbd.SOKYHIEU = ? ";
				$param[] = $parameter['SOKYHIEU'];
				
			}
		}
		$strlimit = "";
		if($limit>0){
			$strlimit = " LIMIT $offset,$limit";
		}
		if($order!=""){
			$order = "ORDER BY $order";
		}
		//Fulltext
		if($parameter['TRICHYEU']!=""){
			
				Common_DBUtils::repairTableBeforeFulltextSearch(QLVBDHCommon::Table('VBD_NHANNOIBO'));
				$select .=",match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') AS `INNAME`";
				$where .= " ".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?"or":"and")." match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."' IN BOOLEAN MODE)".(($parameter['INFILE'] == 1 && $parameter['INNAME']==1)?")":"")." ";
				if($isseeall){
					$order .= " ,match(vbd.TRICHYEU) against ('".str_replace("\\","\\\\",str_replace("'","''",$parameter['TRICHYEU']))."') DESC";
				}else{
					$order .= " ,`INNAME` DESC";
				}
			
		}
		
		chuyennoiboModel::getIDCQ($user->ID_DEP,&$id_depcha);
		$param[] = $id_depcha;
		$sql = "
			select * from vbd_nhannoibo vbd 
			
			where $where and CQ_NHAN = ?
			ORDER BY NGAYGUI DESC
			$strlimit
			
		";
		//echo $sql.$id_depcha;
		$qr = $db->query($sql,$param);
		$re =  $qr->fetchAll();
		return $re;
	}

	function getListAlert(){
		$db =  Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		$sql = "
			select count(*) as DEM  from vbd_nhannoibo vbd 
			
			where CQ_NHAN = ?			
		";
		chuyennoiboModel::getIDCQ($user->ID_DEP,&$id_depcha);		
		$qr = $db->query($sql,array($id_depcha));
		$re =  $qr->fetch();
		return $re["DEM"];
	}
	function getUserOnCQ(){
		$db =  Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Registry::get('auth');
		$user = $auth->getIdentity();
		/*
		$sql = " select  u.ID_U from qtht_users u 
				 inner join qtht_employees emp on u.ID_EMP = emp.ID_EMP
				 inner join qtht_departments dep on emp.ID_DEP = dep.ID_DEP
				 where ID_CQ = ?
		";*/
		$sql = "
			select u.ID_U from qtht_users u 
		";
		//$qr = $db->query($sql,array($user->ID_CQ));
		$qr = $db->query($sql);
		$re =  $qr->fetchAll();
		return $re;
	}

}