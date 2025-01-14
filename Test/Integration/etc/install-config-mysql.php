<?php
/** @codingStandardsIgnoreFile */
return [
    'db-host' => getenv('TEST_DB_HOST') ?: 'mysql',
    'db-user' => getenv('TEST_DB_USER') ?: 'root',
    'db-password' => getenv('TEST_DB_PASSWORD')?: 'root',
    'db-name' => getenv('TEST_DB_NAME') ?: 'magento_integration_tests',
    'db-prefix' => '',
    'backend-frontname' => 'backend',
    'search-engine' => getenv('TEST_SEARCH_ENGINE') ?: getenv('SEARCH_ENGINE') ?: 'elasticsearch7',
    'elasticsearch-host' => getenv('TEST_ELASTICSEARCH_HOST') ?: getenv('ELASTICSEARCH_HOST') ?: 'elasticsearch',
    'elasticsearch-port' => getenv('TEST_ELASTICSEARCH_PORT') ?: getenv('ELASTICSEARCH_PORT') ?: 9200,
    'admin-user' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
    'admin-password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
    'admin-email' => \Magento\TestFramework\Bootstrap::ADMIN_EMAIL,
    'admin-firstname' => \Magento\TestFramework\Bootstrap::ADMIN_FIRSTNAME,
    'admin-lastname' => \Magento\TestFramework\Bootstrap::ADMIN_LASTNAME,
    'amqp-host' => getenv('TEST_AMQP_HOST') ?: getenv('AMQP_HOST') ?: 'rabbitmq',
    'amqp-port' => getenv('TEST_AMQP_PORT') ?: getenv('AMQP_PORT') ?: '5672',
    'amqp-user' => getenv('TEST_AMQP_USER') ?: getenv('AMQP_USER') ?: 'guest',
    'amqp-password' => getenv('TEST_AMQP_PASSWORD') ?: getenv('AMQP_PASSWORD') ?: 'guest',
    'consumers-wait-for-messages' => getenv('TEST_CONSUMERS_WAIT_FOR_MESSAGES') ?: getenv('CONSUMERS_WAIT_FOR_MESSAGES') ?: '0',
];
