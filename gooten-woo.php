<?php

/*
Plugin Name: Gooten Dropshipping for WooCommerce
Plugin URI: https://www.gooten.com/
Description: Print and sell your designs on 100+ products through our international dropshipping network by connecting your WooCommerce store with Gooten.
Author: Gooten
Version: 1.2.1
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    require_once('com_gooten_inc/GootenWC.class.php');
}
