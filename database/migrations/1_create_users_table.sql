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
