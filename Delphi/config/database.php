<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // Konversi dari MyStock connection (MyModul.dfm)
        'stock' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_STOCK_HOST', 'svrajc\sql2008r2'),
            'port' => env('DB_STOCK_PORT', '1433'),
            'database' => env('DB_STOCK_DATABASE', 'Dbsuryajawara'),
            'username' => env('DB_STOCK_USERNAME', 'sa'),
            'password' => env('DB_STOCK_PASSWORD', 'anekajc1a9'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => [
                PDO::ATTR_TIMEOUT => 120,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        // Konversi dari DBTransfer connection (MyModul.dfm)
        'transfer' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_TRANSFER_HOST', 'svrajc\sql2008r2'),
            'port' => env('DB_TRANSFER_PORT', '1433'),
            'database' => env('DB_TRANSFER_DATABASE', 'Dbsuryajawara'),
            'username' => env('DB_TRANSFER_USERNAME', 'sa'),
            'password' => env('DB_TRANSFER_PASSWORD', 'anekajc1a9'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        // Konversi dari MyStockMkt connection (MyModul.dfm)
        'marketing' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_MARKETING_HOST', 'svrajc\sql2008r2'),
            'port' => env('DB_MARKETING_PORT', '1433'),
            'database' => env('DB_MARKETING_DATABASE', 'Dbsuryajawara'),
            'username' => env('DB_MARKETING_USERNAME', 'sa'),
            'password' => env('DB_MARKETING_PASSWORD', 'anekajc1a9'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => [
                PDO::ATTR_TIMEOUT => 120,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        // Konversi dari QuCariGL connection (MyModul.dfm)
        'gl' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_GL_HOST', 'svrajc\sql2008r2'),
            'port' => env('DB_GL_PORT', '1433'),
            'database' => env('DB_GL_DATABASE', 'Dbsuryajawara'),
            'username' => env('DB_GL_USERNAME', 'sa'),
            'password' => env('DB_GL_PASSWORD', 'anekajc1a9'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'encrypt' => env('DB_ENCRYPT', 'yes'),
            'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
]; 