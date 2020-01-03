<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle\Model;
 
use PDO;
use PDOException;
use DesarrolloHosting\MultipleDbConnectionBundle\Model\Exception\NoConnectionException;
use DesarrolloHosting\MultipleDbConnectionBundle\Model\Exception\InvalidQueryException;
use DesarrolloHosting\MultipleDbConnectionBundle\Model\Exception\InvalidReturnTypeException;
use DesarrolloHosting\MultipleDbConnectionBundle\Model\Exception\FailedConnectionException;
 
abstract class QueryReturnType
{
    const PDO_RESULT = 'PDO';
    const ARRAY_RESULT = 'array';
}
 
class MultipleDbConnection {
    
    private $system_name;
    private $connections_info;
    private $connections;
//    private $logger;
    private $last_statements;
    
    function __construct($system_name, $connections_info) {
        $this->system_name = $system_name;
        $this->connections_info = $connections_info;
//        $this->logger = $logger;
        $this->connections = array();
        $this->last_statements = array();
    }
    
    /**
     * 
     * @return array with all the databases keys configured in config.yml
     */
    function getDatabasesKeys(){
        $return = array();
        foreach ($this->connections_info as $database => $connection_parameters){
            $return[] = $database;
        }
        return $return;
    }
    
    /**
     * 
     * @param string $query
     * @param Array $parameters array wih the parameters to execute the PDO statement
     * @param string $database database key as set in config.yml (in the databases section)
     * @param QueryReturnType:: $return_type [Optional] QueryReturnType::PDO_RESULT (default) or QueryReturnType::ARRAY_RESULT
     * @return [Array || PDOStatement] depending of parameter $return_type, it returns a PDOStatement or an array (where each element is a row of the result of the query)
     * @throws NoConnectionException when the $database specified as a parameter isn't configured in the config.yml
     * @throws FailedConnectionException when the mysqli connection to the database failed with the given parameters
     * @throws Exception when there's an error loading the charset
     * @throws InvalidQueryException when the query isn't valid
     * @throws InvalidReturnTypeException when the return type specified as a parameter isn't QueryReturnType::PDO_RESULT OR QueryReturnType::ARRAY_RESULT
     */
    public function execQuery($query, $parameters, $database, $return_type= QueryReturnType::PDO_RESULT){
        //If there is no connection info for the given database, the an exception is thrown
        if(!isset($this->connections_info[$database])){
            throw new NoConnectionException("No connection info available for database '$this->system_name:$database'");
        }
        //if there is no connection set for the database, then I attempt to create one
        if(!isset($this->connections[$database])){
            $conn_info = $this->connections_info[$database];
            $dsn = 'mysql:host='.$conn_info['host'].';dbname='.$conn_info['dbname'].';charset='.$conn_info['charset'].'';
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try{
                $this->connections[$database] = new PDO($dsn, $conn_info['user'], $conn_info['password'], $opt);
            }catch( PDOException $exception ) {
                //put $exception->getMessage() for more detail
                throw new FailedConnectionException("Failed connection to database '$this->system_name:$database'", (int)$exception->getCode());
            }
            
        }
        $pdo = $this->connections[$database];
        try{
            $stmt = $pdo->prepare($query);
            $stmt->execute($parameters);
        } catch (PDOException $exception) {
            $string_parameters = print_r($parameters, true);
            throw new InvalidQueryException("Invalid query '$query' on database '$this->system_name:$database' with parameters $string_parameters");
        }
        $this->last_statements[$database] = $stmt;
        
        if($return_type == QueryReturnType::PDO_RESULT){
            return $stmt;
        }
        else if($return_type == QueryReturnType::ARRAY_RESULT){
            $rows = array();
            while($row = $stmt->fetch()){
                $rows[] = $row;
            }
            return $rows;
        }
        else{
            throw new InvalidReturnTypeException("Return type '$return_type' not supported. View bundle documentation for more information");
        }
    }
    
    /**
     * 
     * @param string $query
     * @param Array $parameters array wih the parameters to execute the PDO statement
     * @param QueryReturnType:: $return_type [Optional] QueryReturnType::PDO_RESULT (default) or QueryReturnType::ARRAY_RESULT
     * @return Array Eack key of the array is the database name as configured in config.yml. 
     *         Depending of parameter $return_type, each element of the array is a mysql_result or an array (where each element is a row of the result of the query in that database)
     */
    public function execMultipleQuery($query, $parameters, $return_type=QueryReturnType::PDO_RESULT){
        $return = array();
        foreach ($this->connections_info as $conn_name => $conn_info){
            $return[$conn_name] = $this->execQuery($query, $parameters, $conn_name, $return_type);
        }
//        $this->logger->info("Executed query '$query' on all $this->system_name databases");
        return $return;
    }
    
    /**
     * Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. This uses the mysqli_affected_rows() function
     * @param string $database 
     * @return integer number of affected rows. 0 if no connection has been set for the $database.
     */
    public function getAffectedRows($database){
        if(!isset($this->last_statements[$database])){
            return 0;
        }
        return $this->last_statements[$database]->rowCount();
    }
    
    /**
     * Returns the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute. 
     * If the last query wasn't an INSERT or UPDATE statement or if the modified table does not have a column with the AUTO_INCREMENT attribute, 
     * this function will return zero.
     * @param string $database database key as set in config.yml (in the databases section)
     * @return integer with the last affected id (insert or update). 0 if no AUTO_INCREMET attribute, or if the last query wasn't an INSERT or UPDATE
     * @throws NoConnectionException when the $database specified as a parameter isn't configured in the config.yml
     */
    public function getLastInsertId($database){
        if(!isset($this->connections[$database])){
            throw new NoConnectionException("No connection info available for database '$this->system_name:$database'");
        }
        return $this->connections[$database]->lastInsertId();
    }
}