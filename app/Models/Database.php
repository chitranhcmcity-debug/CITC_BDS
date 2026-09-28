<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class Database
{
    private ?\PDOStatement $stmt = null;

    private string $lastQuery = '';

    private static ?PDO $sharedConnection = null;

    private function getPdo()
    {
        return DB::connection()->getPdo();
    }

    public function query(string $sql): void
    {
        $this->lastQuery = $sql;
        $this->stmt = $this->getPdo()->prepare($sql);
    }

    public function bind(string $param, mixed $value, ?int $type = null): void
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool
    {
        try {
            return $this->stmt->execute();
        } catch (\PDOException $e) {
            Log::error('Database Execute Error: '.$e->getMessage().' | SQL: '.$this->lastQuery);

            return false;
        }
    }

    public function resultSet(): array
    {
        $this->execute();

        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single(): object|false
    {
        $this->execute();
        $res = $this->stmt->fetch(PDO::FETCH_OBJ);

        return $res ?: false;
    }

    public function singleColumn(): mixed
    {
        $this->execute();

        return $this->stmt->fetchColumn();
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->getPdo()->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        DB::beginTransaction();

        return true;
    }

    public function commit(): bool
    {
        DB::commit();

        return true;
    }

    public function rollBack(): bool
    {
        DB::rollBack();

        return true;
    }

    public function inTransaction(): bool
    {
        return DB::transactionLevel() > 0;
    }
}
