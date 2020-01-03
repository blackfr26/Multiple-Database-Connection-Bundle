<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DesarrolloHosting\MultipleDbConnectionBundle\Model\QueryReturnType;
 
class DefaultController extends Controller
{
    public function indexAction()
    {
        $whmcs_connection = $this->get('solicitudes_connection');
        
        $keys = $whmcs_connection->getDatabasesKeys();
        echo "DATABASES<br>";
        echo "<pre>";
        print_r($keys);
        echo "</pre>";
        
//        $admins = $whmcs_connection->execMultipleQuery("SELECT username FROM tbladmins WHERE firstname = :firstname", array(':firstname' => 'Tomas'), QueryReturnType::ARRAY_RESULT);
        $admins = $whmcs_connection->execMultipleQuery("SELECT * FROM CLIENTE LIMIT 1", array(), QueryReturnType::ARRAY_RESULT);
        echo "QUERY RESULT<br>";
        echo "<pre>";
        print_r($admins);
        echo "</pre>";
        
////      This is an example when using QueryReturnType::PDO_RESULT
//        foreach($return as $brand=>$pdo_stmt){
//            echo "     $brand<br>";
//            while($row = $pdo_stmt->fetch()){
//                echo "<pre>";
//                print_r($row);
//                echo "</pre>";
//            }
//        }
        
        
        $affected_rows = $whmcs_connection->getAffectedRows('hosting');
        echo "AFFECTED ROWS HOSTING<br>";
        echo "<pre>";
        print_r($affected_rows);
        echo "</pre>";
//        
        
//        $whmcs_connection->execQuery('INSERT INTO Test (nombre) VALUES (?)', array('Tomas'), 'planeta');
        $last_insert_id = $whmcs_connection->getLastInsertId('planeta');
        echo "LAST INSERT ID PLANETA<br>";
        echo "<pre>";
        print_r($last_insert_id);
        echo "</pre>";
        
//        exit();
        
        return $this->render('DesarrolloHostingMultipleDbConnectionBundle:Default:index.html.twig');
    }
}