<?php
/**
 * Standard Model class for abstracting query requests
 */
class zapModel
{
    public $pdo;
    public $order;
    public $table;
    public $group;
    public $limit;
    public $fields;
    public $offset;
    public $database;
    public $condition;
    public $queryType;

    /**
     * Initialize database connection using PDO
     */
    public function __construct()
    {
        $settings = parse_ini_file(ROOT . 'core/settings/application.ini', true);
        $settings = $settings['database'];

        try {
            $this->pdo = new PDO("mysql:host={$settings['hostname']};dbname={$settings['database']}",
                            	 $settings['username'],
                            	 $settings['password']);
        } catch (PDOException $e) {
            echo $e->getMessage();
            echo PDO::errorInfo();
        }
        
        $this->database = $settings['database'];
        $this->table    = str_replace('Model', '', get_class($this));
    }
    
    /**
     * Select specific or all fields
     * @var array $fields array of all fields to be queried
     * @return object
     */
    public function select($fields = array('*'))
    {
        $this->fields    = $fields;
        $this->queryType = 'select';
        return $this;
    }
	
    public function insert($fields = array())
    {
        $this->fields    = $fields;
        $this->queryType = 'insert';
        return $this;
    }
	
    public function update($fields = array())
    {
        $this->fields    = $fields;
        $this->queryType = 'update';
        return $this;
    }
	
    public function delete($condition)
    {
        $this->condition = $condition;
        $this->queryType = 'delete';
        return $this;
    }
	
    public function where($condition)
    {
        $this->condition = $condition;
        return $this;
    }
    
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }
    
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }
	
    public function limit($offset = 0, $limit)
    {
        $this->offset = $offset;
        $this->limit  = $limit;
        return $this;
    }
    
    public function execute()
    {
    	$values = array();

        switch ($this->queryType) {
        case 'insert':
            foreach ($this->fields as $column => $value) {
                $columns[] = $column;
                $values[]  = $value;
                $marker[]  = '?';
            }

            $columns = implode(', ', $columns);
            $marker  = implode(', ', $marker);
            $query   = "INSERT INTO {$this->table} ({$columns}) VALUES ({$marker})";
            break;
        case 'update':
            foreach ($this->fields as $key => $value) {
                $setFields[] = "{$key} = ?";
                $values[]    = $value;
            }

            $setFields = join(', ', $setFields);
            $query     = "UPDATE {$this->table} SET {$setFields}";

            if ($this->condition)
                $query .= " WHERE {$this->condition}";
            break;
        case 'delete':
            $query = "DELETE FROM {$this->table}";
			
            if ($this->condition)
                $query .= " WHERE {$this->condition}";
            break;
        default:
            $fields = implode(', ', $this->fields);
            $query  = "SELECT $fields FROM {$this->table}";
            
            if ($this->condition)
                $query .= " WHERE {$this->condition}";
            
            if ($this->group)
                $query .= " GROUP BY {$this->group}";
            
            if ($this->order)
                $query .= " ORDER BY {$this->order}";
			
            if ($this->limit)
                $query .= " LIMIT {$this->offset}, {$this->limit}";
            break;
        }
        
        $pdo = $this->pdo->prepare($query);

        if ($this->fields) {
            for ($i = 1; $i <= count($values); $i++)
                $pdo->bindParam($i, $values[$i - 1]);
        }
		
        $pdo->execute();
        
        $errors = $pdo->errorInfo();

        if (isset($errors[1])) {
            $errorDetails = array(
                'query' => $query,
                'error' => $errors[2]
            );
            
            echo '<pre>';
            print_r($errorDetails);
            echo '</pre>';
            exit(1);
        }
		
        if ($this->queryType == 'select') {
            $result = array();
            
            while ($row = $pdo->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
			
            return $result;
        } else {
            return true;
        }
    }

    /**
     * Shorthand method for selecting all records of a table
     * @return array
     */
    public function selectAll()
    {
        return $this->select()->execute();
    }
	
    /**
     * Direct query requests
     * @var string $query
     * @return array
     */
    public function query($query)
    {
        $pdo = $this->pdo->prepare($query);
        $pdo->execute();
        
        $errors = $pdo->errorInfo();

        if (isset($errors[1])) {
            $errorDetails = array(
                'query' => $query,
                'error' => $errors[2]
            );
            
            echo '<pre>';
            print_r($errorDetails);
            echo '</pre>';
            exit(1);
        }
        
        $result = array();
        
        while ($row = $pdo->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        
        return $result;
    }
}