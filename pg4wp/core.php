<?php
/**
 * @package PostgreSQL_For_Wordpress
 * @author    Hawk__, www.hawkix.net
 * @version $Id$
 */

/**
 * This file does all the initialisation tasks
 */

// Logs are put in the pg4wp directory
define('PG4WP_LOG', PG4WP_ROOT . '/logs/');
// Check if the logs directory is needed and exists or create it if possible
if (
    (PG4WP_DEBUG || PG4WP_LOG_ERRORS)
    && !file_exists(PG4WP_LOG)
    && is_writable(dirname(PG4WP_LOG))
) {
    if (!mkdir($concurrentDirectory = PG4WP_LOG) && !is_dir($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
}

// Load the driver defined in 'db.php'
require_once(PG4WP_ROOT . '/driver_' . DB_DRIVER . '.php');

// This loads up the wpdb class applying appropriate changes to it
$replaces = [
    'define( ' => '// define( ',
    'class wpdb' => 'class wpdb2',
    'new wpdb' => 'new wpdb2',
    'mysql_' => 'wpsql_',
    '<?php' => '',
    '?>' => '',
];
// Ensure class uses the replaced mysql_ functions rather than mysqli_
define('WP_USE_EXT_MYSQL', true);
$wpdb_file = ABSPATH . '/wp-includes/class-wpdb.php';
if (!file_exists($wpdb_file)) {
    // Pre-6.1
    $wpdb_file = ABSPATH . '/wp-includes/wp-db.php';
}
eval(str_replace(array_keys($replaces), array_values($replaces), file_get_contents($wpdb_file)));

// Create wpdb object if not already done
if (!isset($wpdb) && defined('DB_USER')) {
    $wpdb = new wpdb2(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}
