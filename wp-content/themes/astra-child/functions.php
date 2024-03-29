<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */
/**
 * Define Constants
 */
define('CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0');

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

    wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all');
}

add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);

function wp_add_ob_start_callback($buffer) {
    return $buffer;
}

add_action('init', 'wp_add_ob_start');

function wp_add_ob_start() {
    ob_start("wp_add_ob_start_callback");
}

add_action('wp_footer', 'wp_flush_ob_end');

function wp_flush_ob_end() {
    ob_end_flush();
}

add_shortcode('Registration_User', 'user_registration_form');

function user_registration_form($atts) {
    ob_start();
    if (isset($_POST['register'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone_number = $_POST['phone_number'];
        //$groomer_name = $_POST['groomer_name'];

        $errors = array();
        $success = '';

        if (username_exists($username) || email_exists($email)) {

            $errors[] = 'Username or email already exists';
        }


        if (empty($username)) {
            $errors[] = 'Username is required.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }


        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        if ($password != $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        if (empty($phone_number)) {
            $errors[] = 'Phone is required.';
        }


        if (empty($errors)) {

            if (!username_exists($username) && !email_exists($email)) {
                // Add user to WordPress database


                $userdata = array(
                    'user_pass' => $password,
                    'user_login' => $username,
                    'user_nicename' => $last_name . '' . $first_name,
                    'user_email' => $email,
                    'display_name' => $last_name . '' . $first_name,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role' => 'customer',
                );

                $InsertedId = wp_insert_user($userdata);

                //update_user_meta( $InsertedId, 'groomer_name', $_POST['groomer_name']);
                update_user_meta($InsertedId, 'business_name', $_POST['business_name']);
                update_user_meta($InsertedId, 'user_address', $_POST['user_address']);
                update_user_meta($InsertedId, 'user_city', $_POST['user_city']);
                update_user_meta($InsertedId, 'user_state', $_POST['user_state']);
                update_user_meta($InsertedId, 'user_zipcode', $_POST['user_zipcode']);
                update_user_meta($InsertedId, 'certification_number', $_POST['certification_number']);

                update_user_meta($InsertedId, 'billing_address_1', $_POST['user_address']);
                update_user_meta($InsertedId, 'billing_city', $_POST['user_city']);
                update_user_meta($InsertedId, 'billing_state', $_POST['user_state']);
                update_user_meta($InsertedId, 'billing_postcode', $_POST['user_zipcode']);

                update_user_meta($InsertedId, 'shipping_address_1', $_POST['user_address']);
                update_user_meta($InsertedId, 'shipping_city', $_POST['user_city']);
                update_user_meta($InsertedId, 'shipping_postcode', $_POST['user_state']);
                update_user_meta($InsertedId, 'shipping_postcode', $_POST['user_zipcode']);

                // Generate activation key
                $activation_key = md5($InsertedId . time());

                // Update user meta with activation key
                update_user_meta($InsertedId, 'activation_key', $activation_key);

                // Set user status to inactive
                update_user_meta($InsertedId, 'status', 'inactive');

                // Send activation email
                $activation_link = home_url() . '/activate-account/?key=' . $activation_key;

                $from = 'info@rockokate.wpengine.com';
                $to = $email;
                $to_admin = get_option('admin_email');

                $subject = 'Activate your account';
                $subject_admin = 'New user Information';
                $sender = 'From:' . get_option('blogname') . ' <' . $from . '>' . "\r\n";

                $activation_links = home_url() . '/activate-account/?key=' . $activation_key;

                $messagebody = '<div style="max-width: 560px; padding: 20px; background: #eee; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #666;">
			<div style="color: #444444; font-weight: normal;">
			<div style="text-align: center; font-weight: 600; font-size: 26px; padding: 10px 0; border-bottom: solid 3px #eeeeee;"><img src="https://rockokate.wpengine.com/wp-content/uploads/2023/03/rockokate_mail_logo.png" alt="" width="180" style="height:auto;" /></div>			
			</div>
			<div style="padding: 0 30px 30px 30px; border-bottom: 3px solid #eeeeee;">
			<div style="padding: 30px 0; font-size: 20px; text-align: center; line-height: 40px;">Thank you for registering!<span style="display: block;">Please click the following link to activate your account.</span></div>
			<div style="padding: 10px 0 50px 0; text-align: center;"><a style="background: #555555; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 3px; letter-spacing: 0.3px;" href="' . $activation_links . '">Activate your Account</a></div>			
			</div>			
			</div>';

                $admin_msg_body = '<!DOCTYPE html>
            <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <meta name="x-apple-disable-message-reformatting">
                <title></title>	
                <style>
                    table, td, div, h1, p {font-family: Arial, sans-serif;}
                </style>
            </head>
            <body style="margin:0;padding:0;">
                <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
                    <tr>
                        <td align="center" style="padding:0;">
                            <table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
                                <tr>
                                    <td align="center" style="padding:20px 0 30px 0;background:#fff;">
                                        <img src="https://rockokate.wpengine.com/wp-content/uploads/2023/03/rockokate_mail_logo.png" alt="" width="120" style="height:auto;display:block;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" style="padding:5px 30px 5px 30px;background:#fff;">Hello admin,</td>
                                </tr>
                                <tr>
                                    <td style="padding:20px 30px 42px 30px;">
                                        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">								
                                            <tr>
                                                <td style="padding:0;">										
                                          <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                          
                                             <tr>
                                                <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">Name</th>
                                                <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $first_name . ' ' . $last_name . '</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">Email address</th>
                                                <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $email . '</td>
                                            </tr>                                            
                                         <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">Business Name</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['business_name'] . '</td>
                                          </tr>
                                          <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">Address</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['user_address'] . '</td>
                                          </tr>
                                          <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">City</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['user_city'] . '</td>
                                          </tr>
                                          <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">State</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['user_state'] . '</td>
                                          </tr>
                                          <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">Zipcode</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['user_zipcode'] . '</td>
                                          </tr>
                                          <tr>
                                          <th style="padding: 12px; text-align: left; border: 1px solid #eee;" width="44%">License / Certification</th>
                                          <td style="padding: 12px; text-align: left; border: 1px solid #eee;">' . $_POST['certification_number'] . '</td>
                                          </tr>
                                          </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:30px;background:#1B75B9;">
                                        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                                            <tr>
                                                <td style="padding:0;width:50%;" align="center"></td>									
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

                $headers[] = 'MIME-Version: 1.0' . "\r\n";
                $headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers[] = "X-Mailer: PHP \r\n";
                $headers[] = $sender;

                $mail = wp_mail($to, $subject, $messagebody, $headers);
                $admin_mail = wp_mail($to_admin, $subject_admin, $admin_msg_body, $headers);

                $success = '<p class="succ_email">' . $email . '</p><p class="succMsg">User registered successfully. Please check your email to activate your account. You may need to check your junk or spam folder for activation email.</p>';

                $_POST = array();

                // Redirect user to login page
                //wp_redirect(site_url('/login'));
                //exit;
            }
        }
    }
    ?>
    <div class="div_mainsec sec_register">
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger wp_Error">
                <ul class="woocommerce-error">
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php
        if (!empty($success)) {
            echo '<div class="div_success_msg" style="margin-bottom: 25px;">' . $success . '</div>';
        }
        ?>
        <form id="registration-form" method="post" action="" class="form_Register">
            <div class="div_username_fiels">
                <div class="wp-25">
                    <div class="form-group">
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" value="<?php if (isset($_POST['username'])) echo $_POST['username']; ?>">
                    </div>
                </div>
                <div class="wp-37">
                    <div class="form-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" value="<?php if (isset($_POST['password'])) echo $_POST['password']; ?>">
                    </div>
                </div>
                <div class="wp-37">
                    <div class="form-group">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" value="<?php if (isset($_POST['confirm_password'])) echo $_POST['confirm_password']; ?>"/>
                    </div>
                </div>
            </div>
            <div class="wp-50 div_email_100">
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>">
                </div>
            </div>
            <div class="wp-50" style="display: none;">
                <div class="form-group">
                    <input class="form-control" name="groomer_name" type="text" placeholder="Groomer's Name"
                           value="<?php if (isset($_POST['groomer_name'])) echo $_POST['groomer_name']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First name" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last name" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input class="form-control" name="business_name" type="text" placeholder="Business Name"
                           value="<?php if (isset($_POST['business_name'])) echo $_POST['business_name']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input class="form-control" name="user_address" type="text" placeholder="Address"
                           value="<?php if (isset($_POST['user_address'])) echo $_POST['user_address']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input class="form-control" name="user_city" type="text" placeholder="City"
                           value="<?php if (isset($_POST['user_city'])) echo $_POST['user_city']; ?>">
                </div>
            </div>

            <div class="wp-25">
                <div class="form-group">
                    <input class="form-control" name="user_state" type="text" placeholder="State"
                           value="<?php if (isset($_POST['user_state'])) echo $_POST['user_state']; ?>">
                </div>
            </div>

            <div class="wp-25">
                <div class="form-group">
                    <input class="form-control" name="user_zipcode" type="text" placeholder="Zipcode"
                           value="<?php if (isset($_POST['user_zipcode'])) echo $_POST['user_zipcode']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input class="form-control" name="certification_number" type="text" placeholder="License / Certification"
                           value="<?php if (isset($_POST['certification_number'])) echo $_POST['certification_number']; ?>">
                </div>
            </div>
            <div class="wp-50">
                <div class="form-group">
                    <input class="form-control" name="phone_number" type="text" value="" placeholder="Phone"
                           value="<?php if (isset($_POST['phone_number'])) echo $_POST['phone_number']; ?>">
                </div>
            </div>
            <div class="wp-100">
                <div class="form-group">
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    $html = ob_get_clean();
    return $html;
}

add_shortcode('Activate_Account', 'user_active_account');

function user_active_account() {
    ob_start();
    if (isset($_GET['key'])) {
        $activation_key = $_GET['key'];
        $user_id = get_users(array(
            'meta_key' => 'activation_key',
            'meta_value' => $activation_key,
            'fields' => 'ID'
        ));

        if ($user_id[0]) {
            // Activate user account
            update_user_meta($user_id[0], 'status', 'active');

            // Redirect user to login page
            wp_redirect(home_url('/my-account/'));
            exit;
        } else {
            echo 'Invalid activation key';
        }
    }

    $html = ob_get_clean();
    return $html;
}

add_shortcode('User_Login', 'user_login_form');

function user_login_form() {

    global $current_user;
    if ($current_user->ID > 0) {
        wp_redirect(site_url('/shop'));
        exit;
    } else {
        ob_start();
        if (isset($_POST['log']) && isset($_POST['pwd'])) {

            $username = $_POST['log'];
            $pwd = $_POST['pwd'];
            $errors = array();
            $success_login = '';
            $failmessage = '';

            if (is_email($username)) {
                if (email_exists($username)) {
                    $get_by = 'email';
                } else {
                    $errors[] = "There are no users registered with this email address.";
                }
            } elseif (validate_username($username)) {
                if (username_exists($username)) {
                    $get_by = 'login';
                } else {
                    $errors[] = "There are no users registered with this email address.";
                }
            }

            $user = get_user_by($get_by, $username);
            $user_status = get_user_meta($user->ID, 'status', true);
            if ($user_status != 'active') {
                $errors[] = "Please active your account. please check email";
            }


            if (empty($errors)) {

                $creds = array();
                $creds['user_login'] = $user->user_login;
                $creds['user_password'] = $pwd;
                $creds['remember'] = true;
                $user_details = wp_signon($creds, false);
                $success_login = '';
                if (!is_wp_error($user_details)) {
                    $success_login = 'successfull';
                    $shop_page_url = get_permalink(wc_get_page_id('shop'));
                    wp_redirect(site_url('/shop'));
                    exit;
                } else {
                    //$failmessage = "Login fail please try again";
                }
            } else {
                //$failmessage = "Login fail please try again";
            }
        }
        ?>
        <div class="div_mainsec loginMain_sec">
            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>   
            <h2>Login</h2>
            <form name="loginform" id="loginform" action="" method="post">
                <p>
                    <label for="user_login">Username or Email<br>
                        <input type="text" name="log" id="user_login" class="input" value="" size="20"></label>
                </p>
                <p>
                    <label for="user_pass">Password<br>
                        <input type="password" name="pwd" id="user_pass" class="input" value="" size="20"></label>
                </p>
                <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever"> Remember Me</label></p>

                <?php wp_nonce_field('wp-login.php'); ?>
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Log In">
            </form>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }
}

function wp_redirect_page_template() {

    if (is_page('activate-account')) {
        if (is_user_logged_in()) {
            $shop_page_url = get_permalink(wc_get_page_id('shop'));
            wp_redirect(site_url('/shop'));

            exit();
        }
    }
}

add_action('wp', 'wp_redirect_page_template');

function check_user_meta_data($user) {
    $st = get_user_meta($user->ID, 'status', true);
    $data = get_userdata($user->ID);
    $user_roles = $data->roles;
    $user_role = array_shift($user_roles);
    if ($user_role == 'customer') {
        if ($st != 'active') {
            $user = new WP_Error('denied', __("ERROR: Your account is not active.Please check email and active your account"));
        }
    }
    return $user;
}

add_action('wp_authenticate_user', 'check_user_meta_data', 10, 2);

//add_filter( 'authenticate', 'custom_login_check', 10, 3 );
function custom_login_check($user, $username, $password) {
    $get_by = '';

    if (email_exists($username)) {
        $get_by = 'email';
    }
    if (username_exists($username)) {
        $get_by = 'login';
    }
    $user = get_user_by($get_by, $username);
    $user_roles = $user->roles;
    $user_role = array_shift($user_roles);

    //  echo '<pre>';print_r($user);echo '</pre>';

    $st = get_user_meta($user->ID, 'status', true);

    // Check if user status is active
    if ($user_role == 'customer') {
        if ($st != 'active') {


            // $error = new WP_Error();
            // $error->add( 'custom_error', __( 'Your account is inactive.', 'textdomain' ) );
            //return $error;

            $error = new WP_Error();
            $user = new WP_Error('authentication_failed', __('Your account is inactive.'));
            return $error;
            die;
        }
    }

    return $user;
}

// Apply role-based pricing adjustments
function custom_role_based_pricing($price, $product) {
    $user = wp_get_current_user();
    $user_roles = $user->roles;
    // Check if user has a specific role
    if (in_array('corporate', $user_roles)) {
        // Apply discount for wholesale customers
        $price *= 0.9; // 10% discount
    } elseif (in_array('franchise', $user_roles)) {
        // Apply markup for VIP customers
        $price *= 0.8; // 20% markup
    }

    return $price;
}

add_filter('woocommerce_product_get_price', 'custom_role_based_pricing', 10, 2);
add_filter('woocommerce_product_variation_get_price', 'custom_role_based_pricing', 10, 2);

function custom_products_shortcode_handler($atts) {
    // Default attributes
    $atts = shortcode_atts(array(
        'tag' => 'all-users', // Default tag slug
        'exclude_tag' => '', // Tag slug to exclude
            ), $atts, 'products_by_tag');

    // Get products based on tag
    $products_args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(),
    );

    // Include products with the specified tag
    if (!empty($atts['tag'])) {
        $products_args['tax_query'][] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $atts['tag'],
            'operator' => 'IN',
        );
    }

    // Exclude products with a specific tag
    if (!empty($atts['exclude_tag'])) {
        $products_args['tax_query'][] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $atts['exclude_tag'],
            'operator' => 'NOT IN',
        );
    }

    // Query products
    $products_query = new WP_Query($products_args);

    // Output products
    ob_start();
    if ($products_query->have_posts()) {
        woocommerce_product_loop_start();
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }
        woocommerce_product_loop_end();
    } else {
        echo '<p>No products found</p>';
    }
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('products', 'custom_products_shortcode_handler');
/*

  add_action( 'woocommerce_register_form', 'custom_user_role_dropdown' );

  function custom_user_role_dropdown() {
  $roles = [
  "franchise"=> "franchise",
  "corporate"=>"corporate",
  "customer"=>"customer"
  ];

  ?>
  <p class="form-row form-row-wide">
  <label for="user_role"><?php _e( 'Select User Role:', 'woocommerce' ); ?></label>
  <select name="user_role" id="user_role">
  <?php foreach ($roles as $role_key => $role_data) : ?>
  <option value="<?php echo esc_attr($role_key); ?>">
  <?php echo ucfirst($role_data); ?>
  </option>
  <?php endforeach; ?>
  </select>
  </p>
  <?php
  }

  add_action( 'woocommerce_created_customer', 'save_user_role_on_registration', 10, 2 );

  function save_user_role_on_registration( $customer_id, $new_customer_data ) {
  if ( isset( $_POST['user_role'] ) ) {
  $user = new WP_User( $customer_id );
  $user->set_role( $_POST['user_role'] );
  }
  }

 */
add_action('woocommerce_register_form', 'custom_company_name_field');

function custom_company_name_field() {
    ?>
    <p class="form-row form-row-wide">
        <label for="company_name"><?php _e('Company Name:', 'woocommerce'); ?></label>
        <input type="text" class="input-text" name="company_name" id="company_name" placeholder="<?php esc_attr_e('Your Company Name', 'woocommerce'); ?>" />
    </p>
    <?php
}

// Save company name on registration
add_action('woocommerce_created_customer', 'save_company_name_on_registration', 10, 2);

function save_company_name_on_registration($customer_id, $new_customer_data) {
    if (isset($_POST['company_name'])) {
        $company_name = sanitize_text_field($_POST['company_name']);

        // Update both billing and shipping company fields
        update_user_meta($customer_id, 'shipping_company', $company_name);
        update_user_meta($customer_id, 'billing_company', $company_name);
        update_user_meta($customer_id, 'status', 'active');
    }
}

// Add login logout button on header
function my_custom_navigation_login($nav = null) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $logout_url = wp_logout_url(home_url());
        $user_id = get_current_user_id();
        $maximum_spend_amount = get_user_meta($user_id, 'maximum_spend_amount', true);
        $cart_url = wc_get_cart_url(); // Assuming WooCommerce is used

        $dropdown = '<li class="logged-in-user">
                  Hi, ' . $user->display_name . '  
                  <ul class="dropdown">
                    <li><a href="' . admin_url('profile.php') . '">My Profile</a></li>';
        $spend_limit_period_enable_disable = get_user_meta($user_id, 'spend_limit_period', true);
        if (isset($spend_limit_period_enable_disable)) {
            if ($spend_limit_period_enable_disable == 1) {
                $dropdown .= '<li>Monthly Budget Allowance : $' . $maximum_spend_amount . ' </li>';
            }
        }
        $dropdown .= '<li><a href="' . $logout_url . '">Logout</a></li>
                  </ul>
                </li>';
        $dropdown .= '<li class="cart-icon"><a href="' . $cart_url . '"><i class="fas fa-shopping-cart"></i></a></li>';
    } else {
        $login_url = site_url('/my-account/', 'https');
        $dropdown = '<li class="login"><a href="' . $login_url . '">Login</a></li>';
    }

    if ($nav) {
        $nav .= $dropdown;
        return $nav;
    } else {
        echo $dropdown;
    }
}

add_filter('wp_nav_menu_items', 'my_custom_navigation_login');

// Disable plugin update
function my_filter_plugin_updates($value) {
    if (isset($value->response['max-spend-limit-per-user/max-spend-limit-per-user.php'])) {
        unset($value->response['max-spend-limit-per-user/max-spend-limit-per-user.php']);
    }
    return $value;
}

add_filter('site_transient_update_plugins', 'my_filter_plugin_updates');

function show_monthly_budget_allowance() {
    $user_id = get_current_user_id();
    $maximum_spend_amount = get_user_meta($user_id, 'maximum_spend_amount', true);
    $spend_limit_period_enable_disable = get_user_meta($user_id, 'spend_limit_period', true);
    if (isset($spend_limit_period_enable_disable)) {
        if ($spend_limit_period_enable_disable == 1) {
            echo '<p class="monthly-budget-allowance">Monthly Budget Allowance : $' . $maximum_spend_amount . '</p>';
        }
    }
}

add_action('woocommerce_before_my_account', 'show_monthly_budget_allowance');
