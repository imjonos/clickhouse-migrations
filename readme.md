# Clickhouse migrations

Simple php clickhouse migrations

## Installation

Via Composer

``` bash
$ composer require imjonos/clickhouse-migrations
```

## How to use

1. Copy to the {ROOT_OF_THE_PROJECT}:<br>
   {ROOT_OF_THE_PROJECT}/config/clickhouse-migrations.php - config file <br>
   {ROOT_OF_THE_PROJECT}/database/migrations/ - migrations dir <br>
   {ROOT_OF_THE_PROJECT}/database/clickhouse_migrations.log - migrations log file <br>
   <br>
2. Run <br>

``` bash
$ php ./vendor/bin/clickhouse-migrate [force]
```

Config example: <br>

``` php
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
```

Migration file example:

``` sql
CREATE TABLE IF NOT EXISTS users_queue
(
user_id    UInt64,
name       String,
created_at DATETIME
) ENGINE = Kafka SETTINGS
kafka_broker_list = '{KAFKA_BROKER_LIST}',
kafka_topic_list = '{APP_ENV}users',
kafka_group_name = '{APP_ENV}clickhouse-group-users',
kafka_max_block_size = 1048576,
kafka_format = 'JSONEachRow';

CREATE TABLE IF NOT EXISTS users
(
user_id    UInt64,
name       String,
created_at DATETIME
) ENGINE = ReplacingMergeTree
PRIMARY KEY (user_id)
ORDER BY (user_id);

CREATE MATERIALIZED VIEW users_mv TO users
AS
SELECT *
FROM users_queue;
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## License

license. Please see the [license file](license.md) for more information.
