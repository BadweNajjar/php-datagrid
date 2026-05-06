<?php
namespace DataGrid\Database;

/**
 * Database\Connection — CodeIgniter 4 connection factory.
 *
 * Returns a CI4 `\CodeIgniter\Database\BaseConnection` instance, ready to
 * be used with `$db->query(...)`. PDO has been removed entirely; supported
 * drivers are limited to CI4's built-in adapters: MySQLi, Postgre, SQLSRV,
 * SQLite3.
 *
 * Usage from a CI4 application:
 * <code>
 *     // Default group:
 *     $db = \DataGrid\Database\Connection::Connect();
 *
 *     // Named group:
 *     $db = \DataGrid\Database\Connection::Connect('analytics');
 *
 *     // Inline overrides:
 *     $db = \DataGrid\Database\Connection::Connect([
 *         'DSN'      => '',
 *         'hostname' => 'localhost',
 *         'database' => 'mydb',
 *         'username' => 'user',
 *         'password' => 'secret',
 *         'DBDriver' => 'MySQLi',
 *     ]);
 * </code>
 *
 * @package DataGrid\Database
 * @since   8.6.0
 */
class Connection
{
    /**
     * Map historical  short driver names onto CI4 adapter names.
     */
    private static $driverMap = [
        'mysql'    => 'MySQLi',
        'mysqli'   => 'MySQLi',
        'pgsql'    => 'Postgre',
        'postgres' => 'Postgre',
        'sqlsrv'   => 'SQLSRV',
        'sqlite'   => 'SQLite3',
        'sqlite3'  => 'SQLite3',
    ];

    /**
     * Get a CI4 database connection.
     *
     * @param mixed $group Group name (string), config array, or null for default.
     * @return \CodeIgniter\Database\BaseConnection
     */
    public static function Connect($group = null)
    {
        if (!class_exists('\\Config\\Database')) {
            throw new \RuntimeException('CodeIgniter 4 Database config not available. DataGrid requires CI4.');
        }
        return \Config\Database::connect($group);
    }

    /**
     * Build a CI4 connection from legacy-style parameters
     * (driver, host, name, user, pass). Used by the back-compat
     * DataGrid::DataSource(driver, host, name, user, pass, sql, ...) call style.
     *
     * @return \CodeIgniter\Database\BaseConnection
     */
    public static function Build($driver, $host, $name, $user, $pass)
    {
        $driverKey = strtolower((string)$driver);
        $ci4Driver = isset(self::$driverMap[$driverKey]) ? self::$driverMap[$driverKey] : 'MySQLi';

        $hostParts = explode(':', $host);
        $hostname  = isset($hostParts[0]) ? $hostParts[0] : $host;
        $port      = isset($hostParts[1]) ? (int)$hostParts[1] : null;

        $config = [
            'DSN'      => '',
            'hostname' => $hostname,
            'username' => $user,
            'password' => $pass,
            'database' => $name,
            'DBDriver' => $ci4Driver,
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
        ];
        if ($port !== null) $config['port'] = $port;

        return self::Connect($config);
    }
}
