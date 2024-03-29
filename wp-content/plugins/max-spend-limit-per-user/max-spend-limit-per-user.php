<?php
/*
  Plugin Name: Monthly Spend Limit
  Description: Limits user spending on WooCommerce based on maximum spend amount.
  Version: 1.0.0
  Author: Karan Singh
  Author URI: himekaranguleria@Gmail.com
  License: GPLv2 or later
 */

// Add custom fields to user profile
function cwd_woo_max_spend_limit_add_user_profile_fields($user) {
    $maximum_spend_amount = get_user_meta($user->ID, 'maximum_spend_amount', true);
    $spend_limit_period = get_user_meta($user->ID, 'spend_limit_period', true);
    ?>
    <h3><?php esc_html_e('Max Spend Limit', 'woo-max-spend-limit'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="maximum_spend_amount"><?php esc_html_e('Maximum Spend Amount', 'woo-max-spend-limit'); ?></label></th>
            <td>
                <input type="number" name="maximum_spend_amount" id="maximum_spend_amount" value="<?php echo esc_attr($maximum_spend_amount); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e('Enter the maximum spend amount for this user.', 'woo-max-spend-limit'); ?></p>
            </td>
        </tr>
        <tr> 
            <th><label for="spend_limit_period"><?php esc_html_e('Has Max Spend Limit', 'woo-max-spend-limit'); ?></label></th>
            <td>
                <?php $checked = ''; // Initialize $checked to an empty string ?>

                <?php
                if (isset($spend_limit_period)) {
                    if ($spend_limit_period == 1) {
                        $checked = 'checked';
                    }
                }
                ?>
                <input type="checkbox" name="spend_limit_period" id="spend_limit_period" class="regular-text" <?php echo $checked; ?> />

                <p class="description"><?php esc_html_e('Enable disbale Spend Limit.', 'woo-max-spend-limit'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('show_user_profile', 'cwd_woo_max_spend_limit_add_user_profile_fields');
add_action('edit_user_profile', 'cwd_woo_max_spend_limit_add_user_profile_fields');

// Save custom field data
function cwd_woo_max_spend_limit_save_user_profile_fields($user_id) {
    if (current_user_can('edit_user', $user_id)) {
        $maximum_spend_amount = isset($_POST['maximum_spend_amount']) ? sanitize_text_field($_POST['maximum_spend_amount']) : '';
        $spend_limit_period = isset($_POST['spend_limit_period']) == "on" ? 1 : 0;
        update_user_meta($user_id, 'maximum_spend_amount', $maximum_spend_amount);
        update_user_meta($user_id, 'spend_limit_period', $spend_limit_period);
    }
}

add_action('personal_options_update', 'cwd_woo_max_spend_limit_save_user_profile_fields');
add_action('edit_user_profile_update', 'cwd_woo_max_spend_limit_save_user_profile_fields');

// Check user total spend within date limit
function cwd_woo_max_spend_limit_check_user_total_spend() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $maximum_spend_amount = get_user_meta($user_id, 'maximum_spend_amount', true);
        $spend_limit_period_enable_disable = get_user_meta($user_id, 'spend_limit_period', true);

        if (isset($spend_limit_period_enable_disable)) {
            if ($spend_limit_period_enable_disable == 1) {
                $spend_limit_period = date('t');
                if ($maximum_spend_amount !== '' && $spend_limit_period > 0) {
                    // Calculate the date and time limit based on the spend limit period
                    $current_datetime = current_time('mysql');
                    $date_limit = date('Y-m-d H:i:s', strtotime('-' . $spend_limit_period . ' days', strtotime($current_datetime)));
                    $total_spend = 0;

                    // Query the orders within the date and time limit
                    $args = array(
                        'customer_id' => $user_id,
                        'date_created' => '>=' . $date_limit,
                        // You can remove the orders status as per your requirments
                        'status' => array('completed', 'processing', 'on-hold')
                    );
                    $orders = wc_get_orders($args);

                    // Calculate the total spend for the orders within the date and time limit
                    foreach ($orders as $order) {
                        $order_datetime = $order->get_date_created()->date('Y-m-d H:i:s');
                        if (strtotime($order_datetime) >= strtotime($date_limit)) {
                            $total_spend += floatval($order->get_total());
                        }
                    }

                    $limit = floatval($maximum_spend_amount);
                    global $woocommerce;
                    $cart_amount = $woocommerce->cart->total;
                    if ($total_spend >= $limit) {
                        wc_clear_notices(); // Clear any existing notices
                        $message = sprintf(__('You have reached or surpassed the maximum spend limit allowed of %s for this month. '
                                        . ' Contact Kurt Dennis or Ashley Jalkson to request a budget increase.', 'woo-max-spend-limit'), wc_price($maximum_spend_amount));
                        $error_message = '<ul class="woocommerce-error" style="border: 5px solid red; border-radius: 15px;"><li>' . $message . '</li></ul>';
                        wc_add_notice($error_message, 'error'); // Display the notice
                        // Display maximum spend amount
                        $message = sprintf(__('Your Maximum Montly Spend Amount: %s', 'woo-max-spend-limit'), wc_price($maximum_spend_amount));
                        wc_add_notice($message, 'notice');

                        // Calculate the time remaining till reset the cap
                        $reset_date = date('Y-m-d H:i:s', strtotime('+' . $spend_limit_period . ' days', strtotime($date_limit)));
                        $time_diff = strtotime($reset_date) - strtotime($current_datetime);
                        $days_remaining = floor($time_diff / (60 * 60 * 24));
                        $hours_remaining = floor(($time_diff % (60 * 60 * 24)) / (60 * 60));

                        // Remove specific elements from the checkout page
                        add_action('woocommerce_checkout_order_review', 'cwd_woo_max_spend_limit_remove_checkout_elements', 1);
                    } else if ($cart_amount >= $limit) {
                        wc_clear_notices(); // Clear any existing notices
                        $message = sprintf(__('You will surpass the maximum spend limit allowed of %s for this month.'
                                        . ' Contact Kurt Dennis or Ashley Jalkson to request a budget increase.', 'woo-max-spend-limit'), wc_price($maximum_spend_amount));
                        $error_message = '<ul class="woocommerce-error" style="border: 5px solid red; border-radius: 15px;"><li>' . $message . '</li></ul>';
                        wc_add_notice($error_message, 'error'); // Display the notice
                        // Display maximum spend amount
                        $message = sprintf(__('Your Maximum Montly Spend Amount: %s', 'woo-max-spend-limit'), wc_price($maximum_spend_amount));
                        wc_add_notice($message, 'notice');

                        // Remove specific elements from the checkout page
                        add_action('woocommerce_checkout_order_review', 'cwd_woo_max_spend_limit_remove_checkout_elements', 1);
                    }
                }
            }
        }
    }
}

// Remove checkout elements and apply CSS hide option
function cwd_woo_max_spend_limit_remove_checkout_elements() {
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
    echo '<style>#customer_details, div#order_review { display: none; } h3#order_review_heading { display: none; }</style>';
}

// Check user total spend at checkout
add_action('woocommerce_checkout_before_order_review', 'cwd_woo_max_spend_limit_check_user_total_spend');
