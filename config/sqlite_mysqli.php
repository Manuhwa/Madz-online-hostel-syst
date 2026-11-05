<?php
/**
 * Minimal mysqli-compatible wrapper over SQLite so this app can run
 * without a MySQL server installed.
 */

class SqliteMysqliConnection
{
    public $db;
    public $connect_error = '';
    public $error = '';
    public $insert_id = 0;
    public $affected_rows = 0;

    public function __construct($path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->db = new SQLite3($path);
        $this->db->exec('PRAGMA foreign_keys = ON');
        $this->db->exec('PRAGMA journal_mode = WAL');
    }
}

class SqliteMysqliResult
{
    public $rows = [];
    public $idx = 0;
    public $num_rows = 0;

    public function __construct($rows = [])
    {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }
}

class SqliteMysqliStmt
{
    public $conn;
    public $sql;
    public $bound = [];
    public $types = '';
    public $sqliteStmt;
    public $rows = [];
    public $rowIndex = 0;
    public $resultRefs = [];
    public $insert_id = 0;
    public $error = '';
}

function sqlite_mysqli_translate($sql)
{
    $trimmed = ltrim($sql);

    if (preg_match("/^SHOW\s+TABLES\s+LIKE\s+'([^']+)'/i", $trimmed, $m)) {
        return "SELECT name FROM sqlite_master WHERE type='table' AND name LIKE " . sqlite_mysqli_quote($m[1]);
    }
    if (preg_match("/^SHOW\s+TABLES/i", $trimmed)) {
        return "SELECT name FROM sqlite_master WHERE type='table'";
    }
    if (preg_match("/^DESCRIBE\s+(\w+)/i", $trimmed, $m) || preg_match("/^DESC\s+(\w+)/i", $trimmed, $m)) {
        return "PRAGMA table_info(" . $m[1] . ")";
    }
    if (preg_match("/^SET\s+FOREIGN_KEY_CHECKS/i", $trimmed)) {
        $on = preg_match("/=\s*1/", $trimmed);
        return $on ? "PRAGMA foreign_keys = ON" : "PRAGMA foreign_keys = OFF";
    }

    $sql = preg_replace('/\bENUM\s*\([^)]*\)/i', 'TEXT', $sql);
    $sql = preg_replace('/\bINT\s+PRIMARY\s+KEY\s+AUTO_INCREMENT/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
    $sql = preg_replace('/\bAUTO_INCREMENT\b/i', 'AUTOINCREMENT', $sql);
    $sql = preg_replace('/UNIQUE KEY\s+\w+\s*/i', 'UNIQUE ', $sql);
    $sql = preg_replace('/\bUNSIGNED\b/i', '', $sql);
    $sql = preg_replace('/\bENGINE\s*=\s*\w+/i', '', $sql);
    $sql = preg_replace('/\bCHARACTER SET\s+\w+/i', '', $sql);
    $sql = preg_replace('/\bCOLLATE\s+\w+/i', '', $sql);
    $sql = preg_replace('/ADD COLUMN IF NOT EXISTS/i', 'ADD COLUMN', $sql);
    return $sql;
}

function sqlite_mysqli_quote($value)
{
    return "'" . SQLite3::escapeString($value) . "'";
}

function sqlite_mysqli_is_select($sql)
{
    return (bool) preg_match('/^(SELECT|PRAGMA|SHOW|DESCRIBE|DESC|WITH)\b/i', ltrim($sql));
}

function sqlite_mysqli_fetch_all($result)
{
    $rows = [];
    if ($result === false || $result === true) {
        return $rows;
    }
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (isset($row['name']) && !isset($row['Field']) && array_key_exists('type', $row)) {
            $row['Field'] = $row['name'];
            $row['Type'] = $row['type'];
        }
        if (isset($row['name']) && !isset($row['Tables_in_hostel_management'])) {
            $keys = array_keys($row);
            if ($keys === ['name']) {
                $row['Tables_in_hostel_management'] = $row['name'];
            }
        }
        $rows[] = $row;
    }
    return $rows;
}

function mysqli_connect($host = '', $user = '', $password = '', $dbname = '', $port = 3306, $socket = '')
{
    global $SQLITE_DB_PATH;
    try {
        $conn = new SqliteMysqliConnection($SQLITE_DB_PATH);
        $GLOBALS['__mysqli_last'] = $conn;
        return $conn;
    } catch (Exception $e) {
        $conn = new SqliteMysqliConnection(':memory:');
        $conn->connect_error = $e->getMessage();
        $GLOBALS['__mysqli_last'] = $conn;
        return false;
    }
}

function mysqli_connect_error()
{
    $conn = $GLOBALS['__mysqli_last'] ?? null;
    return $conn ? $conn->connect_error : 'Connection failed';
}

function mysqli_select_db($conn, $dbname)
{
    return true;
}

function mysqli_query($conn, $sql)
{
    $conn->error = '';
    $translated = sqlite_mysqli_translate($sql);
    try {
        if (sqlite_mysqli_is_select($sql) || sqlite_mysqli_is_select($translated)) {
            $result = $conn->db->query($translated);
            if ($result === false) {
                $conn->error = $conn->db->lastErrorMsg();
                return false;
            }
            $rows = sqlite_mysqli_fetch_all($result);
            return new SqliteMysqliResult($rows);
        }
        $conn->db->exec($translated);
        $conn->affected_rows = $conn->db->changes();
        $conn->insert_id = $conn->db->lastInsertRowID();
        return true;
    } catch (Exception $e) {
        $conn->error = $e->getMessage();
        return false;
    }
}

function mysqli_error($conn)
{
    return $conn->error;
}

function mysqli_prepare($conn, $sql)
{
    $stmt = new SqliteMysqliStmt();
    $stmt->conn = $conn;
    $stmt->sql = sqlite_mysqli_translate($sql);
    return $stmt;
}

function mysqli_stmt_bind_param($stmt, $types, &...$vars)
{
    $stmt->types = $types;
    $stmt->bound = [];
    foreach ($vars as $i => &$value) {
        $stmt->bound[$i] = &$value;
    }
    return true;
}

function sqlite_mysqli_bind_and_prepare($stmt)
{
    $sqliteStmt = $stmt->conn->db->prepare($stmt->sql);
    if ($sqliteStmt === false) {
        $stmt->error = $stmt->conn->db->lastErrorMsg();
        $stmt->conn->error = $stmt->error;
        return false;
    }
    foreach ($stmt->bound as $i => $value) {
        $typeChar = $stmt->types[$i] ?? 's';
        $sqliteType = SQLITE3_TEXT;
        if ($typeChar === 'i') {
            $sqliteType = SQLITE3_INTEGER;
        } elseif ($typeChar === 'd') {
            $sqliteType = SQLITE3_FLOAT;
        } elseif ($typeChar === 'b') {
            $sqliteType = SQLITE3_BLOB;
        }
        $sqliteStmt->bindValue($i + 1, $value, $sqliteType);
    }
    $stmt->sqliteStmt = $sqliteStmt;
    return true;
}

function mysqli_stmt_execute($stmt)
{
    $stmt->error = '';
    if (!sqlite_mysqli_bind_and_prepare($stmt)) {
        return false;
    }
    try {
        $result = $stmt->sqliteStmt->execute();
        $stmt->conn->insert_id = $stmt->conn->db->lastInsertRowID();
        $stmt->insert_id = $stmt->conn->insert_id;
        $stmt->conn->affected_rows = $stmt->conn->db->changes();
        if (sqlite_mysqli_is_select($stmt->sql) && $result instanceof SQLite3Result) {
            $stmt->rows = sqlite_mysqli_fetch_all($result);
            $stmt->rowIndex = 0;
        } else {
            $stmt->rows = [];
            $stmt->rowIndex = 0;
            if ($result instanceof SQLite3Result) {
                $result->finalize();
            }
        }
        return true;
    } catch (Exception $e) {
        $stmt->error = $e->getMessage();
        $stmt->conn->error = $stmt->error;
        return false;
    }
}

function mysqli_stmt_get_result($stmt)
{
    return new SqliteMysqliResult($stmt->rows);
}

function mysqli_stmt_store_result($stmt)
{
    return true;
}

function mysqli_stmt_num_rows($stmt)
{
    return count($stmt->rows);
}

function mysqli_stmt_bind_result($stmt, &...$vars)
{
    $stmt->resultRefs = [];
    foreach ($vars as $i => &$value) {
        $stmt->resultRefs[$i] = &$value;
    }
    return true;
}

function mysqli_stmt_fetch($stmt)
{
    if ($stmt->rowIndex >= count($stmt->rows)) {
        return false;
    }
    $row = array_values($stmt->rows[$stmt->rowIndex++]);
    foreach ($stmt->resultRefs as $i => &$ref) {
        $ref = $row[$i] ?? null;
    }
    return true;
}

function mysqli_stmt_close($stmt)
{
    if ($stmt->sqliteStmt) {
        $stmt->sqliteStmt->close();
    }
    return true;
}

function mysqli_fetch_assoc($result)
{
    if (!$result instanceof SqliteMysqliResult) {
        return null;
    }
    if ($result->idx >= count($result->rows)) {
        return null;
    }
    return $result->rows[$result->idx++];
}

function mysqli_num_rows($result)
{
    if ($result instanceof SqliteMysqliResult) {
        return $result->num_rows;
    }
    return 0;
}

function mysqli_insert_id($conn)
{
    return $conn->insert_id;
}

function mysqli_affected_rows($conn)
{
    return $conn->affected_rows;
}

function mysqli_real_escape_string($conn, $string)
{
    return SQLite3::escapeString($string);
}

function mysqli_close($conn)
{
    if ($conn && $conn->db) {
        $conn->db->close();
    }
    return true;
}

function mysqli_begin_transaction($conn)
{
    return $conn->db->exec('BEGIN');
}

function mysqli_commit($conn)
{
    return $conn->db->exec('COMMIT');
}

function mysqli_rollback($conn)
{
    return $conn->db->exec('ROLLBACK');
}
