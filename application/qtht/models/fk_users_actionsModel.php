<?php
/**
 * ClassModel
 *  
 * @author hieuvt
 * @version 1.0
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once ('Zend/Db/Table.php');

class fk_users_actionsModel extends Zend_Db_Table_Abstract {
    /**
     * The default table name 
     */
    var $_name = 'fk_users_actions';
    var $_id_u = 0;
    var $_id_mod = 0;
    var $_search = "";
    
    /**
     * This function returns all the fk_users_actions
     * in our fk_users_actions database table
     */ 
    public function SelectAll($offset,$limit,$order){
        // Build phần where
        $arrwhere = array();
        $strwhere = "(1=1)";
                
        $arrwhere[] = $this->_id_u;
        
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
                a.id_act, a.id_mod , a.name as ACTION_NAME, m.name AS MODULE_NAME, SUM( u_a.id_u =? ) AS isChecked
            FROM
                qtht_actions a
            LEFT JOIN 
                fk_users_actions u_a ON u_a.id_act = a.id_act
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
            
        $arrwhere[] = $this->_id_u;
        
        if($this->_search != ""){
            $arrwhere[] = "%".$this->_search."%";
            $strwhere .= " and a.name like ?";
        }        
        
        $sql = "
            SELECT
                a.id_act, a.id_mod , a.name as ACTION_NAME, m.name AS MODULE_NAME, SUM( u_a.id_u =? ) AS isChecked
            FROM
                qtht_actions a
            LEFT JOIN 
                fk_users_actions u_a ON u_a.id_act = a.id_act
             LEFT JOIN 
                qtht_modules m ON m.id_mod = a.id_mod                
            WHERE
                $strwhere
            GROUP BY 
                   a.id_act, a.id_mod                
        ";        
        // Thực hiện query
        return $this->getDefaultAdapter()->query($sql,$arrwhere)->rowCount();
    }
	public function SelectAllByIDU($idu){
		$r = $this->getDefaultAdapter()->query("
			SELECT
				t.SEL,ac.DESCRIPTION as NAME,m.ID_MOD,m.NAME as MODNAME,ac.ID_ACT,gm.NAME as GMODNAME,gm.ID_GMOD
			FROM
			(SELECT
			    a.ID_ACT,
			    (a.ID_U=u.ID_U) as SEL
			FROM
				(
					SELECT
						ua.ID_U,ac.NAME,ac.ID_ACT
					FROM
						QTHT_ACTIONS ac
						left join (
							SELECT 
								ID_U,ID_ACT
							from
								`fk_users_actions`
							where ID_U=?) ua on ac.ID_ACT = ua.ID_ACT
				) a
				left join (
					SELECT
						ID_U
					FROM
						`qtht_users`
					WHERE
						ID_U=?
			) u on a.ID_U = u.ID_U) t
			inner join QTHT_ACTIONS ac on t.ID_ACT = ac.ID_ACT
			inner join QTHT_MODULES m on m.ID_MOD = ac.ID_MOD
			inner join QTHT_GROUPMODULES gm on gm.ID_GMOD = m.ID_GMOD
			WHERE
				ac.ISPUBLIC = 0 or ac.ISPUBLIC is null
			ORDER BY
				gm.ID_GMOD,m.ID_MOD,m.NAME,ac.NAME
		",array($idu,$idu));
		return $r->fetchAll();	
	}

	public function SelectAllByNonCheckIDU($idu){
		$r = $this->getDefaultAdapter()->query("
			 SELECT
				t.SEL,ac.DESCRIPTION as NAME,m.ID_MOD,m.NAME as MODNAME,ac.ID_ACT,gm.NAME as GMODNAME,gm.ID_GMOD
			FROM
			(SELECT
			    a.ID_ACT,
			    (a.ID_U=u.ID_U) as SEL
			FROM
				(
					SELECT
						ua.ID_U,ac.NAME,ac.ID_ACT
					FROM
						QTHT_ACTIONS ac
						left join (
							SELECT 
								ID_U,ID_ACT
							from
								`fk_users_actions`
							where ID_U=?) ua on ac.ID_ACT = ua.ID_ACT
				) a
				left join (
					SELECT
						ID_U
					FROM
						`qtht_users`
					WHERE
						ID_U=?
			) u on a.ID_U = u.ID_U) t
			inner join QTHT_ACTIONS ac on t.ID_ACT = ac.ID_ACT
			inner join QTHT_MODULES m on m.ID_MOD = ac.ID_MOD
			inner join QTHT_GROUPMODULES gm on gm.ID_GMOD = m.ID_GMOD
			
			
			WHERE
				( ac.ISPUBLIC = 0 or ac.ISPUBLIC is null ) and ac.ID_ACT not in (
				
					select fk_g.ID_ACT from
						fk_users_groups fk_u_g
						inner join fk_groups_actions fk_g on fk_u_g.ID_G = fk_g.ID_G
						where fk_u_g.ID_U = ?
				)

			ORDER BY
				gm.ID_GMOD,m.ID_MOD,m.NAME,ac.NAME

		",array($idu,$idu,$idu));
		return $r->fetchAll();	
	}
}
