<?php
/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class fk_groups_actionsModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'fk_groups_actions';
    var $_id_g = 0;
    var $_id_mod = 0;
    var $_search = "";
    
    public function SelectAll($offset,$limit,$order){
        // Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
                
        $arrwhere[] = $this->_id_g;
        
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and a.name like ?";
        }
        
        // Build phần limit
        $strlimit = "";
        if($limit>0){
            $strlimit = " LIMIT $offset,$limit";
        }
        
        // Build order
        $strorder = "";
        if($order>0){
            $strorder = " ORDER BY $order";
        }
        else 
        {
            $strorder = " ORDER BY a.id_mod";
        }
        
        $sql = "
            SELECT
                a.id_act, a.id_mod , a.name as ACTION_NAME, m.name AS MODULE_NAME, SUM( g_a.id_g =? ) AS isChecked
            FROM
                qtht_actions a
            LEFT JOIN 
                fk_groups_actions g_a ON g_a.id_act = a.id_act
             LEFT JOIN 
                qtht_modules m ON m.id_mod = a.id_mod                
            WHERE
                $strwhere
            GROUP BY 
                   a.id_act, a.id_mod     
            $strorder
            $strlimit
        ";
        // Thực hiện query
        $result = $this->getDefaultAdapter()->query($sql,$arrwhere);
        return $result->fetchAll();
    }    
    /**
     * Chuyển dữ liệu tới combobox
     */
    public function Count(){
        // Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
            
        $arrwhere[] = $this->_id_g;
        
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and a.name like ?";
        }        
        
        $sql = "
            SELECT
                a. * , SUM( g_a.id_g =? ) AS isChecked
            FROM
                qtht_actions a
            LEFT JOIN 
                fk_groups_actions g_a ON g_a.id_act = a.id_act
            WHERE
                $strwhere
            GROUP BY 
                   a.id_act, a.id_mod                   
        ";        
        // Thực hiện query
        return $this->getDefaultAdapter()->query($sql,$arrwhere)->rowCount();
    }
	public function SelectAllByIDG($idg){
		$r = $this->getDefaultAdapter()->query("
			SELECT
				t.SEL,ac.DESCRIPTION as NAME,m.ID_MOD,m.NAME as MODNAME,ac.ID_ACT,gm.NAME as GMODNAME,gm.ID_GMOD
			FROM
			(SELECT
			    a.ID_ACT,
			    (a.ID_G=u.ID_G) as SEL
			FROM
				(
					SELECT
						ua.ID_G,ac.NAME,ac.ID_ACT
					FROM
						QTHT_ACTIONS ac
						left join (
							SELECT 
								ID_G,ID_ACT
							from
								`fk_groups_actions`
							where ID_G=?) ua on ac.ID_ACT = ua.ID_ACT
				) a
				left join (
					SELECT
						ID_G
					FROM
						`qtht_groups`
					WHERE
						ID_G=?
			) u on a.ID_G = u.ID_G) t
			inner join QTHT_ACTIONS ac on t.ID_ACT = ac.ID_ACT
			inner join QTHT_MODULES m on m.ID_MOD = ac.ID_MOD
			inner join QTHT_GROUPMODULES gm on gm.ID_GMOD = m.ID_GMOD
			WHERE
				ac.ISPUBLIC = 0 or ac.ISPUBLIC is null
			ORDER BY
				gm.ID_GMOD,m.ID_MOD,m.NAME,ac.NAME
		",array($idg,$idg));
		return $r->fetchAll();	
	}
}
