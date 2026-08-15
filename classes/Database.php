<?php
/**
 * Database Class — PDO Singleton Wrapper
 * Provides prepared-statement helpers, pagination,
 * transaction support, and audit logging.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private array $queryLog = [];

    // ── Constructor ─────────────────────────────────────────────────────────
    private function __construct()
    {
        require_once ROOT_PATH . 'config/database.php';
        try {
            $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    // ── Singleton accessor ──────────────────────────────────────────────────
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO { return $this->pdo; }

    // ── Execute with optional params ────────────────────────────────────────
    public function query(string $sql, array $params = []): PDOStatement
    {
        $start = microtime(true);
        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $this->queryLog[] = [
            'sql'    => $sql,
            'params' => $params,
            'time'   => round((microtime(true) - $start) * 1000, 2) . ' ms',
        ];
        return $stmt;
    }

    // ── Fetch a single row ──────────────────────────────────────────────────
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    // ── Fetch all rows ──────────────────────────────────────────────────────
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    // ── Fetch single column value ────────────────────────────────────────────
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    // ── Generic INSERT ───────────────────────────────────────────────────────
    public function insert(string $table, array $data): int
    {
        $cols        = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO `$table` ($cols) VALUES ($placeholders)", array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    // ── Generic UPDATE ───────────────────────────────────────────────────────
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = $this->query(
            "UPDATE `$table` SET $set WHERE $where",
            array_merge(array_values($data), $whereParams)
        );
        return $stmt->rowCount();
    }

    // ── Generic DELETE ───────────────────────────────────────────────────────
    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->query("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    // ── COUNT helper ─────────────────────────────────────────────────────────
    public function count(string $table, string $where = '1', array $params = []): int
    {
        return (int) $this->fetchColumn("SELECT COUNT(*) FROM `$table` WHERE $where", $params);
    }

    // ── Paginated SELECT ─────────────────────────────────────────────────────
    public function paginate(string $sql, array $params, int $page, int $perPage): array
    {
        $total  = $this->fetchColumn("SELECT COUNT(*) FROM ($sql) AS _t", $params);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
        return [
            'data'         => $rows,
            'total'        => (int) $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
            'from'         => $offset + 1,
            'to'           => min($offset + $perPage, (int) $total),
        ];
    }

    // ── Transactions ─────────────────────────────────────────────────────────
    public function beginTransaction(): void   { $this->pdo->beginTransaction(); }
    public function commit(): void             { $this->pdo->commit(); }
    public function rollBack(): void           { $this->pdo->rollBack(); }
    public function inTransaction(): bool      { return $this->pdo->inTransaction(); }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    // ── Last insert ID ───────────────────────────────────────────────────────
    public function lastInsertId(): int { return (int) $this->pdo->lastInsertId(); }

    // ── Dev query log ────────────────────────────────────────────────────────
    public function getQueryLog(): array { return $this->queryLog; }

    // ── Audit log helper ─────────────────────────────────────────────────────
    public function audit(string $action, string $module, ?int $recordId = null,
                          ?array $old = null, ?array $new = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $this->insert('audit_logs', [
            'user_id'    => $userId,
            'action'     => $action,
            'module'     => $module,
            'table_name' => $module,
            'record_id'  => $recordId,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    // ── Prevent cloning / unserialization ────────────────────────────────────
    private function __clone() {}
    public function __wakeup(): void { throw new \Exception('Cannot unserialize singleton.'); }
}
