<?php


namespace Suvarivaza\QB;


use PDO;
use PDOException;

class EasyQueryBuilder
{

    private $pdo;
    protected $where = [];
    protected $sql = '';
    protected $prefix = '';
    protected $type = null;
    protected $data = null;
    protected $error = null;
    protected $count = null;


    /*
     * Create connection in constructor
     * Gets the argument array with database connection configuration
     *
     * @param array $config
     * throw PDOException
     */
    function __construct($config)
    {
        $this->prefix = $config['prefix'];
        try {
            $db_server = $config['host'];
            $db_user = $config['db_user'];
            $db_password = $config['db_password'];
            $db_name = $config['db_name'];
            $charset = $config['charset'];
            $dsn = "mysql:host=$db_server;dbname=$db_name;charset=$charset";
            $options = $config['options'];
            $this->pdo = new PDO($dsn, $db_user, $db_password, $options);


        } catch (PDOException $exception) {
            $this->error = $exception->getMessage();
            die($exception->getMessage());
        }
    }

    /**
     * Select the columns
     *
     * Gets the arguments passed as $db->select('one', 'two')
     * Using it without arguments equals to having '*' as argument
     * Using it with array maps values as column names
     *
     * Examples:
     *    $db->select('title');
     *    // SELECT title
     *
     *    $db->select('title', 'author', 'date');
     *    // SELECT title, author, date
     *
     *    $db->select(['id', 'title']);
     *    // SELECT id, title
     *
     * @param array|string $columns Array or multiple string arguments containing column names
     *
     * @return self
     */
    public function select($columns = null)
    {

        $this->reset();
        $this->type = 'select';

        if (is_array($columns)) {
            $select = $columns;
        } else {
            $select = func_get_args();
        }


        $this->sql .= 'SELECT ';

        if (!empty($select)) {
            $this->sql .= implode(', ', $select) . ' ';
        } else {
            $this->sql .= '* ';
        }

        return $this;
    }


    /**
     * FROM
     * Gets the argument - table name
     *
     * @param string
     *
     * @return self
     */
    public function from($table)
    {

        $this->sql .= "FROM {$this->prefix}{$table} ";
        return $this;
    }

    /**
     * WHERE
     * Used in: SELECT, UPDATE, DELETE
     * Gets the arguments passed as $query->where('column', 'operator', 'value');
     *
     * Examples:
     *    $query->where('name', '=', 'Jacob');
     *    // WHERE 'name' = 'Jacob'
     *
     *    $query->where('id', '>=', '2')
     *    // WHERE 'id' >= '2'
     *
     * @param string
     * $column   The column name
     * $operator supports: '=', '<', '>', '<=', '>='
     * $value string or number
     * @return self
     */
    public function where($column, $operator, $value)
    {

        $this->data[$column] = $value; // add where param in data array for execute()

        $operators = ['=', '<', '>', '<=', '>='];
        if (!in_array($operator, $operators)) die('Operator of this type is not supported!');

        $this->where = array(
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        );

        if (!empty($this->where)) {
            $this->sql .= "WHERE {$column} {$operator}:{$column}";
        }

        if ($this->type === 'update' or $this->type === 'delete') {
            return $this->execute();
        }

        return $this;
    }



    /**
     * INSERT
     * Gets the argument - table name
     *
     * @param string - table name
     *
     * @return self
     */
    public function insert($table)
    {

        $this->reset();
        $this->type = 'insert';

        $this->sql .= 'INSERT INTO' . ' ' . $this->prefix . $table . ' ';

        return $this;

    }


    /**
     * UPDATE
     * Gets the argument - table name
     *
     * @param string - table name
     *
     * @return self
     */
    public function update($table)
    {

        $this->reset();
        $this->type = 'update';

        $this->sql = "UPDATE {$this->prefix}{$table} ";

        return $this;
    }


    /**
     * DELETE
     *
     * @param string - table name
     *
     * @return self
     */
    public function delete($table){
        $this->reset();
        $this->type = 'delete';

        $this->sql = 'DELETE FROM' . ' ' . $this->prefix . $table . ' ';

        return $this;
    }


    /**
     * Used in: INSERT, UPDATE
     * Takes as a parameter an associative array with keys as columns and values as the value for the corresponding column.
     *
     * Example:
     * $query->set([
     *       id => 10,
     *       title => 'Title for id 10' ])
     *
     * @param array $data Array of key-values
     *
     * @return self or result PDO execute()
     */
    function set($data)
    {

        $this->data = $data;

        switch ($this->type) {

            case 'insert':
                $keys = implode(',', array_keys($data));
                $tags = ':' . implode(', :', array_keys($data));
                $this->sql .= "({$keys}) VALUES ({$tags}) ";

                return $this->execute();
                break;

            case 'update':
                $keys = array_keys($data);
                $string = '';
                foreach ($keys as $key) {
                    $string .= $key . '=:' . $key . ',';
                }
                $keys = rtrim($string, ',');
                $this->sql .= "SET {$keys} ";

                break;
        }

        return $this;
    }


    /**
     *
     * execute PDO query
     *
     * @param $fetch string $data_type string
     *
     * @return self
     */
    private function execute($fetch = null, $data_type = null)
    {

        $pdo_fetch_types = [
            'assoc' => PDO::FETCH_ASSOC,
            'obj' => PDO::FETCH_OBJ,
            'both' => PDO::FETCH_BOTH,
            'num' => PDO::FETCH_NUM,
        ];

        if ($data_type) {
            $pdo_fetch_type = $pdo_fetch_types[$data_type];
        } else {
            $pdo_fetch_type = PDO::FETCH_ASSOC;
        }

        try {

            $statement = $this->pdo->prepare($this->sql);
            $result = $statement->execute($this->data);

            if ($this->type === 'select'){
                if ($fetch === 'one') {
                    $result = $statement->fetch($pdo_fetch_type);
                } else {
                    $result = $statement->fetchAll($pdo_fetch_type);
                }
                $this->count = $statement->rowCount();
            }


        } catch (PDOException $exception) {
            $this->error = $exception->getMessage();
            die($exception->getMessage());
        }

        return $result;

    }


    /*
    * get one result
    */
    public function getResult($data_type = null)
    {

        return $this->execute('one', $data_type);

    }

    /*
     * get all results
     */
    public function getResults($data_type = null)
    {

        return $this->execute($data_type);

    }

    /*
     * check for existence
     */
    public function exists()
    {

        $this->execute();
        return $this->count;
    }



    public function getError(){
        return $this->error;
    }


    /**
     * Clearing properties
     *
     * @return self
     */
    public function reset()
    {

        $this->where = [];
        $this->sql = '';
        $this->type = null;
        $this->data = null;
        $this->error = null;
        $this->error = null;
        $this->count = null;

        return $this;
    }


}