<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle\Model;
 
class WhmcsConnection extends MultipleDbConnection {
    
    
    public function getAllAdmins($whmcs = null){
        $query = "SELECT * FROM tbladmins WHERE firstname = ?";
        if(is_null($whmcs)){
            return $this->execMultipleQuery($query, array('Tomas'), QueryReturnType::ARRAY_RESULT);
        }
        return $this->execQuery($query, array(), $whmcs);
    }
    
}
 