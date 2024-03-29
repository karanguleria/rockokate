<?php
/**
 * Plugin Name: Custom WooCommerce Order Starting Number
 * Plugin URI:  # (Optional, plugin website URL)
 * Description: Changes the starting order number in WooCommerce.
 * Version:     1.0.0
 * Author:      Karan Singh Guleriya
 * Author URI:  # (Optional, your website URL)
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

global $wpdb;

// Assuming default table prefix for WooCommerce orders
$table_name = 'wp_posts';

function change_order_starting_number() {
  $qry = $wpdb->query("ALTER TABLE $table_name AUTO_INCREMENT = 2000;");

  if ($qry) {
    echo "Order starting number changed successfully."; // Display message only on plugin activation
  } else {
    echo "Error changing order starting number."; // Display message only on plugin activation
  }
}

register_activation_hook( __FILE__, 'change_order_starting_number' );
 