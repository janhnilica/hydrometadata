<?php
/**
 * static PDO wrapper for MySQL
 * all methods throw a PDOException
 * @author Jan Hnilica
 */

class Dtb
{
    /**
     * @var PDO connection
     */
    private static $connection;
    
    /**
     * @var array PDO driver settings
     */
    private static $options = array(
    	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    );
    
    /**
     * connects to dtb
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     */
    public static function connect(string $host, string $database, string $user, string $password): void
    {
        if (!isset(self::$connection))
        {
            $dsn = "mysql:host=$host;dbname=$database";
            self::$connection = new PDO($dsn, $user, $password, self::$options);
        }
    }
    
    /**
     * prepares and executes a query and returns a PDO statement
     * @param array $params - the first value is a query, next values are query parameters
     * @return PDOStatement
     */
    private static function executeStatement(array $params): PDOStatement
    {
        $query = array_shift($params);
        $statement = self::$connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }
    
    /**
     * launches a query and returns the number of affected rows
     * for non-selecting queries (INSERT, UPDATE...)
     * @param mixed $query - the query + any number of parameters OR an array (where the query is the first element)
     * @return int - the number of affected rows
     */
    public static function query(mixed $query): int
    {
        if (!is_array($query))
            $query = func_get_args();
        $statement = self::executeStatement($query);
        return $statement->rowCount();
    }
    
    /**
     * inserts a record into the table
     * @param string $table - table name
     * @param array $params - associative array, the array keys correspond to dtb column names
     * @return int - numebr of affected rows
     */
    public static function insert(string $table, array $params = []): int
    {
        $query = "INSERT INTO `$table` ".
            " (`" . implode('`, `', array_keys($params)) . "`)".
            " VALUES (" . str_repeat('?,', count($params) - 1) . "?)";
        return self::query(array_merge(array($query), array_values($params)));
    }
    
    /**
     * updates a record in the table
     * @param string $table - table name
     * @param array $params - associative array, the array keys correspond to dtb column names
     * @param string $condition - e.g. "WHERE `id` = ? AND `name` = ?"
     * @param array $conditionParams - params associated with condition
     * @return int - number of affected rows
     */
    public static function update(string $table, array $params, string $condition, array $conditionParams = []): int
    {
        $query = "UPDATE `$table` SET `" . implode('` = ?, `', array_keys($params)) . "` = ? " . $condition;
        return self::query(array_merge(array($query), array_values($params), $conditionParams));
    }
    
    /**
     * launches a query and returns the first element of the first row of the result
     * @param mixed $query - the query + any number of parameters OR an array (where the query is the first element)
     * @return mixed - a requested value or null (in case of a query on non-existing data)
     */
    public static function getSingleValue(mixed $query): mixed
    {
        if (!is_array($query))
            $query = func_get_args();
        $statement = self::executeStatement($query);
        $res = $statement->fetch(PDO::FETCH_NUM);
        if ($res === false)
            return null;
        else
            return $res[0];
    }
    
    /**
     * launches a query and returns the first row of the result
     * @param mixed $query - the query + any number of parameters OR an array (where the query is the first element)
     * @return array - empty in case of a query on non-existing data
     */
    public static function getOneRow(mixed $query): array
    {
        if (!is_array($query))
            $query = func_get_args();
        $statement = self::executeStatement($query);
        $res = $statement->fetch(PDO::FETCH_ASSOC);
        if ($res === false)
            return [];
        else
            return $res;
    }
    
    /**
     * launches a query and returns a table - all rows of the result (array of associative arrays)
     * @param mixed $query - the query + any number of parameters OR an array (where the query is the first element)
     * @return array - empty in case of a query on non-existing data
     */
    public static function getTable(mixed $query): array
    {
        if (!is_array($query))
            $query = func_get_args();
        $statement = self::executeStatement($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * returns the last inserted id
     * @return string
     */
    public static function getLastId(): string
    {
        return self::$connection->lastInsertId();
    }
}