<?php
/**
 * Plugin Name: Group2
 * Description: Insecure Design
 * Author: Group 2
 */

function Main() {
    if(!is_page('group2') || post_password_required()) {
        return;
    }
    
    echo '<h1>Order Page</h1>';
    echo '<form method="GET">';
    echo 'Enter your Name: <input type="text" name="names" required><br>';
    echo 'Enter your Email: <input type="email" name="email" required><br>';
    echo 'Enter Temporary Password: <input type="text" name="passwd" required><br>';
    echo 'Quantity (Between 1 and 5): <input type="number" name="quantity" min="1" required><br>';
    echo '<input type="hidden" name="userid" value="'.get_current_user_id().'">';
    echo '<input type="submit" name="submit" value="Place Order">';
    echo '</form>';
    
    if(isset($_GET['submit'])) {
        global $wpdb;
        $queryString = "SELECT user_email FROM wp_users WHERE ID={$_GET['userid']}";
        $results = $wpdb->get_row($queryString, ARRAY_N);
        echo '<h4>Order Successfully Placed</h4>';
        echo "You are logged in as: $results[0]<br>";
    }
}

add_action('wp_footer', 'Main');
