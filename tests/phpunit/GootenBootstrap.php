<?php
/**
 * PHPUnit bootstrap file
 *
 * Created by Boro on 12-Oct-16.
 * @package Gooten
 */

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $phpunitProps = parse_ini_file(dirname(__FILE__) . '/phpunit.ini');
    $_tests_dir = $phpunitProps['WP_TEST_ROOT'];
}

require_once $_tests_dir . 'includes/functions.php';

tests_add_filter('muplugins_loaded', function () {
    $plugins_to_activate = array(
        'woocommerce/woocommerce.php',
        'gooten-woocommerce/gooten-woo.php'
    );
    update_option('active_plugins', $plugins_to_activate);
});

require $_tests_dir . 'includes/bootstrap.php';