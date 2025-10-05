<?php
// Secure Database Singleton using mysqli with prepared statements and utf8mb4
// Enables mysqli exceptions and sets connection attributes.

class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {
        // Turn on mysqli exceptions
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: 'exam';

        $this->conn = new mysqli($host, $user, $pass, $name);
        // charset and collation
        $this->conn->set_charset('utf8mb4');
        $this->conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Best-effort schema hardening for legacy installs
        $this->ensureSchema();
    }

    public static function getInstance(): Database {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }

    // Helper to prepare/execute with typed params
    public function run(string $sql, array $params = []): mysqli_stmt {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            [$types, $values] = $this->buildParamTypes($params);
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->run($sql, $params);
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->run($sql, $params);
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    private function buildParamTypes(array $params): array {
        $types = '';
        $values = [];
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            elseif (is_null($p)) { $types .= 's'; $p = null; }
            else $types .= 's';
            $values[] = $p;
        }
        return [$types, $values];
    }

    private function ensureSchema(): void {
        try {
            // Increase password columns to fit password_hash
            $this->conn->query("ALTER TABLE user MODIFY password VARCHAR(255) NOT NULL");
        } catch (Throwable $e) { /* ignore if already altered or lacks perms */ }
        try {
            $this->conn->query("ALTER TABLE admin MODIFY password VARCHAR(255) NOT NULL");
        } catch (Throwable $e) { /* ignore */ }
        try {
            $this->conn->query("SET SESSION sql_safe_updates = 0");
        } catch (Throwable $e) { /* ignore */ }
    }
}

?>
