<?php

class Database {

    private $connection;
    private $query;
    private $lastQuery;
    private $bind = [];
    private $stmt;
    private $set = [];
    private $error;
    private $transaction = false;

    function __construct($host, $username, $password, $database)
    {
        // $db->select('*')->from('users')->where(['id =' => 1]);
        // $db->delete('users')->where(['id =' => 1]);
        // $db->update('users')->set($data)->where(['id =' => 1]);
        // $db->insert('users')->set($data);
        if(empty($connection)) {
            $this->connect($host, $username, $password, $database);
        }
    }

    private function connect($host, $username, $password, $database)
    {
        $source = 'mysql:host='.$host.';dbname='.$database.';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            $this->connection = new PDO($source, $username, $password, $options);
        } catch (PDOException $ex) {
            $this->error = $ex->getMessage();
        }
    }

    private function run()
    {
        $this->query($this->query);
        if(!empty($this->bind)) {
            foreach($this->bind as $key => $value) {
                $this->bind($key, $value);
            }
        }
        $this->execute();
        if(!$this->transaction) {
            $this->lastQuery = $this->query;
            $this->reset();
        }
    }

    private function execute()
    {
        try {
            $this->stmt->execute();
        } catch(PDOException $ex) {
            $this->error = $ex->getMessage();
        }
    }

    public function reset()
    {
        $this->query = "";
        $this->bind = [];
        $this->set = [];
    }

    public function bind($parameter, $value, $type = NULL)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($parameter, $value, $type);
    }

    public function query($query)
    {
        $this->stmt = $this->connection->prepare($query);
    }

    public function select($select = [])
    {
        $parameters = '';
        if(empty($select) || $select == '*') {
            $parameters = '*';
        } else {
            foreach($select as $parameter) {
                $parameters .= ','.$parameter;
            }
            $parameters = ltrim($parameters, ',');
        }
        $this->query = 'SELECT '. $parameters;
        return $this;
    }

    public function from($from)
    {
        $this->query .= ' FROM '.$from;
        return $this;
    }

    public function leftJoin($joinTable, $on)
    {
        $this->query .= ' LEFT JOIN '.$table. ' ON '.$on;
        return $this;
    }

    public function rightJoin($table, $on)
    {
        $this->query .= ' RIGHT JOIN '.$table. ' ON '.$on;
        return $this;
    }

    public function join($table, $on)
    {
        $this->query .= ' JOIN '.$table. ' ON '.$on;
        return $this;
    }

    public function where($where = [])
    {
        $parameter = key($where);
        $value = current($where);

        $parameter = str_ireplace('=', '', $parameter);
        $parameter = trim($parameter);
        $this->bind[$parameter] = $value;

        if(preg_match('/WHERE/', $this->query)) {
            $this->query .= ' AND '.key($where).' :'.$parameter;
        } else {
            $this->query .= ' WHERE '.key($where).' :'.$parameter;
        }

        // Execute statement if its a delete or update because this is the last method to run
        if(preg_match('/DELETE FROM/', $this->query) || preg_match('/UPDATE/', $this->query)) {
            $this->run();
        }
        return $this;
    }

    public function orderBy($orderBy = [])
    {
        $this->query .= ' ORDER BY '.key($orderBy).' '.strtoupper(current($orderBy));
        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->query .= ' GROUP BY '.$groupBy;
        return $this;
    }

    public function limit($limit)
    {
        $this->query .= ' LIMIT '.$limit;
        return $this;
    }

    public function insert($table)
    {
        $this->query = 'INSERT INTO '.$table;
        return $this;
    }

    public function update($table)
    {
        $this->query = 'UPDATE '.$table;
        return $this;
    }

    public function set($set = [])
    {
        if(preg_match('/UPDATE/', $this->query)) {
            $this->query .= ' SET ';
            if(!empty($set)) {
                foreach($set as $key => $value) {
                    $this->query .= $key.' = :'.$key.',';
                    $this->bind[$key] = $value;
                }
                $this->query = rtrim($this->query, ',');
            }
        }

        // Execute statement if its a insert because its the last method to run
        if(preg_match('/INSERT INTO/', $this->query)) {
            if(!empty($set)) {
                $keys = $set;
                $this->query .= '(';
                foreach($set as $key => $value) {
                    $this->query .= $key.',';
                }
                $this->query = rtrim($this->query, ',');
                $this->query .= ') VALUES (';
                foreach($keys as $key => $value) {
                    $this->query .= ':'.$key.',';
                    $this->bind[$key] = $value;
                }
                $this->query = rtrim($this->query, ',');
                $this->query .= ')';
            }

            $this->run();
        }
        return $this;
    }

    public function delete($table)
    {
        $this->query = 'DELETE FROM '.$table;
        return $this;
    }

    public function fetchAll()
    {
        $this->run();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne()
    {
        $this->run();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function beginTransaction()
    {
        $this->transaction = true;
        return $this->connection->beginTransaction();
    }

    public function commitTransaction()
    {
        return $this->connection->commit();
    }

    public function rollbackTransaction()
    {
        return $this->connection->rollback();
    }

    /* HELPER FUNCTIONS */
    public function insertId()
    {
        $this->connection->lastInsertId();
    }

    public function numRows()
    {
        return $this->stmt->rowCount();
    }

    public function lastError()
    {
        return $this->error;
    }

    public function lastQuery()
    {
        return $this->lastQuery;
    }

}
