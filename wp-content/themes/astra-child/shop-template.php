<?php
/*
Template Name: Shop Template
*/
get_header(); ?>

<div class="shop-page">
    <div class="banner">
        <img src="https://rockokate.wpengine.com/wp-content/uploads/2021/09/Untitled12121-1-1024x487.png">
    </div>
    <div class="product">
<?php 

// Get current user object
$current_user = wp_get_current_user();

// Check if the user has a specific role
if ( in_array( 'corporate', $current_user->roles ) ) {
    // Display products for wholesale customers
    echo do_shortcode('[products tag="corporate"]');
    
} elseif ( in_array( 'franchise', $current_user->roles ) ) {
    // Display products for VIP customers
    echo do_shortcode('[products tag="franchise"]');
} else {
    echo do_shortcode('[products tag="all-users"]');
} ?>
    </div>
        </div>
 <?php
get_footer(); ?>