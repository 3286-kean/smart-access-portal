<?php
require_once __DIR__ . '/config.php';

class NeonResult
{
    private $result;
    public $num_rows;

    public function __construct($result)
    {
        $this->result = $result;
        $this->num_rows = $result ? $result->num_rows : 0;
    }

    public function fetch_assoc()
    {
        return $this->result ? $this->result->fetch_assoc() : null;
    }

    public function fetch_row()
    {
        return $this->result ? $this->result->fetch_row() : null;
    }
}

class NeonStatement
{
    private $conn;
    private $sql;
    private $params = [];
    public $error = '';
    private $stmt;
    private $result;

    public function __construct($conn, $sql)
    {
        $this->conn = $conn;
        $this->sql = $sql;
    }

    public function bind_param($type, &...$values)
    {
        $this->params = $values;
        return true;
    }

    public function execute()
    {
        $this->stmt = $this->conn->prepare($this->sql);

        if (!$this->stmt) {
            $this->error = $this->conn->error;
            return false;
        }

        if (count($this->params) > 0) {
            $types = '';
            foreach ($this->params as $value) {
                $types .= is_int($value) ? 'i' : (is_double($value) ? 'd' : 's');
            }

            $args = [$types];
            foreach ($this->params as $key => &$value) {
                $args[] = &$value;
            }

            call_user_func_array([$this->stmt, 'bind_param'], $args);
        }

        if (!$this->stmt->execute()) {
            $this->error = $this->stmt->error;
            return false;
        }

        $this->result = $this->stmt->result_metadata() ? $this->stmt->store_result() : true;
        return true;
    }

    public function get_result()
    {
        if (!$this->stmt) {
            return null;
        }

        $result = $this->stmt->get_result();
        return $result ? new NeonResult($result) : null;
    }

    public function store_result()
    {
        return $this->stmt ? $this->stmt->store_result() : true;
    }
}

class NeonConnection
{
    private $conn;
    public $connect_error = '';
    public $error = '';

    public function __construct($dsn = null)
    {
        if ($dsn === null) {
            $dsn = DATABASE_URL;
        }

        $host = defined('fdb1030.awardspace.net') ? DB_HOST : '127.0.0.1';
        $user = defined('4777508_neon') ? DB_USER : 'root';
        $password = defined('@kean!2005#') ? DB_PASSWORD : '';
        $database = defined('4777508_neon') ? DB_NAME : 'student_portal';
        $port = intval(defined('DB_PORT') ? DB_PORT : 3306);

        if ($dsn) {
            $parsed = parse_url($dsn);
            if ($parsed && isset($parsed['scheme']) && in_array($parsed['scheme'], ['mysql', 'mysqli'])) {
                $host = $parsed['host'] ?? $host;
                $port = isset($parsed['port']) ? $parsed['port'] : $port;
                $user = isset($parsed['user']) ? urldecode($parsed['user']) : $user;
                $password = isset($parsed['pass']) ? urldecode($parsed['pass']) : $password;
                $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $database;
            }
        }

        $this->conn = new mysqli($host, $user, $password, $database, $port);

        if ($this->conn->connect_error) {
            $this->connect_error = 'Database connection failed: ' . $this->conn->connect_error;
            $this->error = $this->connect_error;
            return;
        }

        $this->conn->set_charset('utf8mb4');
    }

    public function prepare($sql)
    {
        return new NeonStatement($this->conn, $sql);
    }

    public function query($sql)
    {
        $result = $this->conn->query($sql);
        if ($result === false) {
            $this->error = $this->conn->error;
            return false;
        }

        return new NeonResult($result);
    }

    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}

$conn = new NeonConnection();

if ($conn->connect_error !== '') {
    die($conn->connect_error);
}
