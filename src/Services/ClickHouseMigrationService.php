<?php

namespace Nos\DbAdapters\Services;

use Nos\DbAdapters\Interfaces\DbClientAdapterInterface;
use Throwable;

final readonly class ClickHouseMigrationService
{
    public function __construct(
        private DbClientAdapterInterface $clickHouseDbClientAdapter,
        private string $historyFile,
        private string $dir,
        private array $bindings = []
    ) {
    }

    public function resetDataBase(): void
    {
        file_put_contents($this->historyFile, '');
        $tables = $this->clickHouseDbClientAdapter->showTables();
        foreach ($tables as $table) {
            $this->clickHouseDbClientAdapter->exec('DROP TABLE IF EXISTS ' . $table['name']);
        }
    }

    public function getMigrationList(): array
    {
        $result = [];
        if ($handle = opendir($this->dir)) {
            $executedMigrations = $this->getExecutedMigrations();
            while (false !== ($file = readdir($handle))) {
                $fullPath = $this->dir . '/' . $file;
                if (is_file($fullPath) && !in_array($file, $executedMigrations)) {
                    $result[] = $file;
                }
            }
            closedir($handle);
        }

        sort($result, SORT_NUMERIC);

        return $result;
    }

    protected function getExecutedMigrations(): array
    {
        $result = [];
        if (file_exists($this->historyFile)) {
            $result = file($this->historyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        return $result;
    }

    public function migrate(string $file): bool
    {
        $result = true;
        $sql = $this->getSqlFromMigrationFile($this->dir . '/' . $file);
        $sqlQueries = explode(';', $sql);
        foreach ($sqlQueries as $sqlQuery) {
            $query = $this->prepareSql($sqlQuery);
            if ($query) {
                try {
                    $this->clickHouseDbClientAdapter->exec($query);
                } catch (Throwable $e) {
                    $result = false;
                    echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
                }
            }
        }

        if ($result) {
            $this->saveLastMigration($file);
        }

        return $result;
    }

    protected function getSqlFromMigrationFile(string $file): string
    {
        return file_get_contents($file);
    }

    protected function prepareSql(string $sql): string
    {
        $keys = array_keys($this->bindings);
        $values = array_values($this->bindings);

        return trim(
            str_replace($values, $keys, $sql)
        );
    }

    protected function saveLastMigration(string $name): void
    {
        file_put_contents($this->historyFile, $name . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
