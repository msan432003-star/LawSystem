<?php
/**
 * classes/DataAccess.class.php
 * Small wrapper around PDO so pages don't repeat query code.
 * All values go through prepared statements (safe from SQL injection).
 *
 * Usage:
 *   $db = new DataAccess($pdo);
 *   $cases = $db->fetchAll('SELECT * FROM cases WHERE client_id = ?', [$clientId]);
 *   $id    = $db->insert('case_type', ['type_name' => 'Civil', 'description' => '...']);
 *   $db->update('cases', ['status' => 'ongoing'], 'case_id = ?', [$caseId]);
 */
class DataAccess
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ---------- Reading ----------

    /** Run any SELECT and return all rows. */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /** Return the first row, or null if nothing found. */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Return a single value (first column of first row), e.g. a COUNT. */
    public function fetchValue(string $sql, array $params = [])
    {
        $value = $this->run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** Does at least one matching row exist?  exists('login', 'username = ?', ['admin']) */
    public function exists(string $table, string $where, array $params = []): bool
    {
        $table = $this->safeName($table);
        return (int) $this->fetchValue("SELECT COUNT(*) FROM `$table` WHERE $where", $params) > 0;
    }

    /** Count rows.  count('cases', 'status = ?', ['pending'])  */
    public function count(string $table, string $where = '1', array $params = []): int
    {
        $table = $this->safeName($table);
        return (int) $this->fetchValue("SELECT COUNT(*) FROM `$table` WHERE $where", $params);
    }

    // ---------- Writing ----------

    /** Insert a row from an associative array. Returns the new auto-increment id. */
    public function insert(string $table, array $data): int
    {
        $table   = $this->safeName($table);
        $columns = array_map([$this, 'safeName'], array_keys($data));
        $holders = implode(', ', array_fill(0, count($columns), '?'));
        $sql     = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $columns) . '`) VALUES (' . $holders . ')';

        $this->run($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows. $where is a SQL condition with ? placeholders (never put user input in it directly).
     * Returns the number of affected rows.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $table = $this->safeName($table);
        $sets  = [];
        foreach (array_keys($data) as $column) {
            $sets[] = '`' . $this->safeName($column) . '` = ?';
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE ' . $where;

        return $this->run($sql, array_merge(array_values($data), $whereParams))->rowCount();
    }

    /** Delete rows. Returns the number of deleted rows. */
    public function delete(string $table, string $where, array $params = []): int
    {
        $table = $this->safeName($table);
        return $this->run("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    /** Run any other statement (INSERT ... SELECT, custom UPDATE, etc.). */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    // ---------- Transactions (use when several queries must succeed together) ----------

    public function begin(): void    { $this->pdo->beginTransaction(); }
    public function commit(): void   { $this->pdo->commit(); }
    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    // ---------- Internals ----------

    private function run(string $sql, array $params): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Table and column names can't be bound as parameters, so only allow safe characters. */
    private function safeName(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new InvalidArgumentException("Invalid table or column name: $name");
        }
        return $name;
    }
}