MultipleDbConnectionBundle
This bundle allows you generate multiple services, where each one connects to multiple databases. This is meant to use when you have multiple databases with the same structure, so you can execute the same query on all service databases.

Configuration
You must configure the bundle adding this parameters in your parameters.yml file:

multiple_db_connection: services: : databases: : host: dbname: user: password: charset: (default UTF8, not necessary to specify) : host:
dbname:
user:
password: : databases: : host:
dbname:
user:
password: ........... ........... : host:
dbname:
user:
password:

Everything between <...> means it an optional name.
Services
The configuration as shown above will dynamically generate 2 services: _connection and _connection.

Main functions
This services inherit from MultipleDbConnection, so they provide methods to execute a query on every database or a single database.

Examples:
-----------------------------------------------------------------------------------------------------------------
$this->get('<service1>_connection')->execQuery('SELECT * FROM Users WHERE name = :name', array(':name', 'John'), '<db1>', QueryReturnType::ARRAY_RESULT);

This function will execute the query connecting to the database configured in services -> <service1> ->  databases -> <db1>.
It returns an array where each element is a row from the result of the query.
-----------------------------------------------------------------------------------------------------------------
$this->get('<service2>_connection')->execMultipleQuery('SELECT * FROM Users WHERE name = ?', array('John'), QueryReturnType::PDO_RESULT);

This function will execute the query on every database configured in services -> <service2> ->  databases
It return an array where each element follow this structure: the key is <dbx>, and the value is the PDOStatement resulting of executing the query in <dbx>
* Note that QueryReturnType::PDO_RESULT is the default value for the function, so it isn't necesary to specify.
-----------------------------------------------------------------------------------------------------------------
$this->get('<service1>_connection')->getLastInsertId('<db1>');

This method will return the result of the function mysqli_insert_id() using the connection configured in services -> <service1> ->  databases -> <db1>.
-----------------------------------------------------------------------------------------------------------------
$this->get('<service2>_connection')->getAffectedRows('<db2>');

This method will return the result of the function mysqli_affected_rows() using the connection configured in multiple_db_connection -> services -> <service2> ->  databases
-----------------------------------------------------------------------------------------------------------------
$this->get('<service1>_connection')->getDatabasesKeys();

This method returns an array with all the service databases keys as configured in config.yml
-----------------------------------------------------------------------------------------------------------------

* If you wish to have more available functions for your service, you can create a class with name <Service1>Connection (note the capital S) under the directory AppBundle\Model\MultipleDbConnection\.
  This class MUST extend from MultipleDbConnection class available in namespace DesarrolloHosting\MultipleDbConnectionBundle\Model

* For now, the only service that provides additional functions from the ones mentioned before is 'whmcs'. Check WhmcsConnection class documentation for more details.
  If you wish you can extend or overwrite the WhmcsConnection class, since it first looks for the class in your AppBundle and then in this bundle.
Exceptions
The following exception can be throwned by this services:

NoConnectionException -> when the database specified as a parameter in some function isn't configured in the config.yml FailedConnectionException -> when the mysqli connection to the database failed with the given parameters InvalidQueryException -> when the mysqli_result of the query isn't valid InvalidReturnTypeException -> when the return type specified as a parameter isn't QueryReturnType::PDO_RESULT OR QueryReturnType::ARRAY_RESULT

This exceptions are defined in the namespace DesarrolloHosting\MultipleDbConnectionBundle\Model