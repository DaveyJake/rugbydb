<?php
/**
 * A usardb-specific implementation.
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
 * from /path/to/usardb/src/Baz/Qux.php:
 *
 *      new \Foo\Bar\Baz\Qux;
 *
 * @package USA_Rugby_Database
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) exit; // phpcs:ignore

// phpcs:disable
spl_autoload_register( function( $class ) {
    // USA_Rugby_Database-specific namespace prefix.
    $prefix = 'Sinergi\\BrowserDetector\\';

    // Base directory for the namespace prefix.
    $base_dir = __DIR__ . '/libs/';

    // Does the class use the namespace prefix?
    $len = strlen( $prefix );

    // No, move to the next registered autoloader.
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    // Get the relative class name.
    $relative_class = substr( $class, $len );

    /*
     * Replace the namespace prefix with the base directory, replace namespace
     * separators with directory separators in the relative class name, append
     * with .php
     */
    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    // If the file exists, require it.
    if ( file_exists( $file ) ) {
        require $file;
    }
} );
