# EasyQueryBuilder



**About**

It is a easy query builder that includes basic methods for working with a database: SELECT, INSERT, UPDATE, DELETE.
If you need a simple query builder to safely work with a database, then this component is what you need.


**Dependencies:**

This Query Builder has no dependencies except PHP 5.6, \MySQLi extension and PDO.


**Safety:**

In all requests used by PDO API and prepared queries.


**Installation**

This is a Composer package. You can install this package with the following command: 
**composer require suvarivaza/easy-query-builder**


**Usage**

```
use Suvarivaza\QB\EasyQueryBuilder;
```

**Connection**

The connection to the database occurs automatically when a new object of the EasyQueryBuilder class is created.
Just pass an array with your database connection data to the EasyQueryBuilder class constructor when creating a new object.

Example:

```
$config = ['driver' => 'mysql', // Db driver
'host' => 'localhost',
'db_name' => 'your-database',
'db_user' => 'root',
'db_password' => 'your-password',
'charset' => 'utf8', // Optional
'prefix' => 'cb_', // Table prefix, optional
'options' => [ // PDO constructor options, optional
PDO::ATTR_TIMEOUT => 5,
PDO::ATTR_EMULATE_PREPARES => false,
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
],
];
```

```
$db = new EasyQueryBuilder($config); // Create a connection
```

**SELECT**

```
$select = $db->select('column_one', 'colume_two')->from('table')->where('username', '=', 'anna');
```

Method SELECT gets the arguments passed as $db->select('one', 'two')
Using it without arguments equals to having '*' as argument
Using it with array maps values as column names

Examples:

```
$db->select();
// SELECT *

$db->select('title');
// SELECT title
     
$db->select('title', 'author', 'date');
// SELECT title, author, date
    
$db->select(['id', 'title']);
// SELECT id, title
```

The **FROM method** takes a table name as a parameter.

The **WHERE method** takes three parameters: key, operator and value.
Gets the arguments passed as $query->where('column', 'operator', 'value')
Used in: SELECT, UPDATE, DELETE
     
Examples:

```
$query->where('name', '=', 'Jacob');
// WHERE 'name' = 'Jacob'
     
$query->where('id', '>=', '2')
// WHERE id >= 2
```
Operator supports: '=', '<', '>', '<=', '>='

Methods **RESULT** and **RESULTS** gets the argument fatch data type.
You can take data in format as supports PDO:

```
'assoc' = PDO::FETCH_ASSOC
'obj' = PDO::FETCH_OBJ
'both' = PDO::FETCH_BOTH
'num' = PDO::FETCH_NUM
```

Examples:

```
$result = $select->getResult('assoc'); // one result as an associative array
$results = $select->getResults('obj'); // all results as an object
```


**INSERT**

```
$db->insert('table')->set([
        'column1' => 'value',
        'column2' => 'value'
    ]);
```

The INSERT method takes a table name as a parameter.

The SET method takes as a parameter an associative array with keys as columns and values as the value for the corresponding column.
Used in: INSERT, UPDATE


**UPDATE**

The UPDATE method takes a table name as a parameter.

```
$db->update('table')->set([
       'column1' => 'value',
       'column2' => 'value'
    ])->where('id', '=', '1');
```

**DELETE**

The DELETE method takes a table name as a parameter.

```
$db->delete('table')->where('id', '=', '1');
```
