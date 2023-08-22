<?php

return [
    'adapter' => \Nos\DbAdapters\ClickHouseDbClientAdapter::class,
    'host' => 'clickhouse',
    'port' => 8123,
    'database' => 'test',
    'username' => 'default',
    'password' => '',
    'dir' => __DIR__ . '/../database/migrations',
    'history_file' => __DIR__ . '/../database/clickhouse_migrations.log',
    'bindings' => ['redpanda:9092' => '{KAFKA_BROKER_LIST}', 'local_' => '{APP_ENV}']
];
