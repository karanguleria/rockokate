<?php
/*
Template Name: Custom Registration
*/
get_header(); ?>
<?php
if (isset($_POST['register'])) 
{
  $username = sanitize_user($_POST['username']);
  $email = sanitize_email($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];  
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


    if (empty($errors)) {

        if(!username_exists($username) &&  !email_exists($email)){
            // Add user to WordPress database
            

            $userdata = array(
                'user_pass'	 => $password,
                'user_login' => $email,
                'user_nicename' => $last_name.''.$first_name,
                'user_email' => $email,
                'display_name' => $last_name.''.$first_name,
                'first_name' => $first_name, 
                'last_name' => $last_name,
                'role' => 'customer',  
            );

            $InsertedId = wp_insert_user( $userdata ) ;

            // Generate activation key
            $activation_key = md5($InsertedId . time());

            // Update user meta with activation key
            update_user_meta($InsertedId, 'activation_key', $activation_key);

            // Set user status to inactive
            update_user_meta($InsertedId, 'status', 'inactive');

            // Send activation email
            $activation_link = home_url() . '/activate-account/?key=' . $activation_key;
            $to = $email;
            $subject = 'Activate your account';
            $message = 'Please click the following link to activate your account: ' . home_url() . '/activate-account/?key=' . $activation_key;
            wp_mail($to, $subject, $message);

            $success =  'User registered successfully. Please check your email to activate your account.('.$activation_link.')';


            // Redirect user to login page
            //wp_redirect(wp_login_url());
            //exit;
        }
    }
}
?>
<div class="div_mainsec sec_register">
    <?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php
    if(!empty($success)){
        echo $success;
    }
    ?>
    <form id="registration-form" method="post" action="">
    <div class="wp-25">
        <div class="form-group">
            <input type="text" name="username" id="username" class="form-control" placeholder="Username">
        </div>
    </div>
    <div class="wp-37">
        <div class="form-group">
            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First name">
        </div>
    </div>
    <div class="wp-37">
        <div class="form-group">
            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last name">
        </div>
    </div>
    <div class="wp-50">
        <div class="form-group">
            <input type="email" name="email" id="email" class="form-control" placeholder="Email">
        </div>
    </div>
    <div class="wp-50">
        <div class="form-group">
        <input class="form-control" name="phone_number" type="text" value="" placeholder="Phone">
        </div>
    </div>
    <div class="wp-50">
        <div class="form-group">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password">
        </div>
    </div>
    <div class="wp-50">
        <div class="form-group">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password"/>
        </div>
    </div>
    
    <div class="wp-50">
        <div class="form-group">
            <input class="form-control" name="groomer_name" type="text" value="" placeholder="Groomer's Name">
        </div>
    </div>
    <div class="wp-50">
        <div class="form-group">
            <input class="form-control" name="business_name" type="text" value="" placeholder="Business Name">
        </div>
    </div>
    <div class="wp-100">
        <div class="form-group">
            <input class="form-control" name="user_address" type="text" value="" placeholder="Address">
        </div>
    </div>

    <div class="wp-50">
        <div class="form-group">
            <input class="form-control" name="user_city" type="text" value="" placeholder="City">
        </div>
    </div>

    <div class="wp-25">
        <div class="form-group">
            <input class="form-control" name="user_state" type="text" value="" placeholder="State">
        </div>
    </div>

    <div class="wp-25">
        <div class="form-group">
            <input class="form-control" name="user_zipcode" type="text" value="" placeholder="Zipcode">
        </div>
    </div>
    <div class="wp-100">
        <div class="form-group">
            <input class="form-control" name="certification_number" type="text" value="" placeholder="License / Certification">
        </div>
    </div>
    <div class="wp-100">
        <div class="form-group">
        <button type="submit" name="register" class="btn btn-primary">Register</button>
        </div>
    </div>
    </form>
</div>
<style>
body .div_mainsec {
    margin-top: 60px;
    margin-bottom: 60px;
}

</style>
<?php  get_footer(); ?>