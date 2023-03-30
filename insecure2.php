<?php
/**
 * Plugin Name: Wordpress Vulnerable Plugin
 * Description: Created for E-Commerce Security. DO NOT DEPLOY ON PRODUCTION SYSTEMS.
 * Author: Group 2
 */

function secretCookieSetterandChecker() {
    if (isset($_POST['secret'])) {
        $expirationTime = time() + (86400 * 30); // 30 days
        setcookie("vulnerable_page_secretSuccessCount", '0', $expirationTime, "/");
        setcookie("vulnerable_page_secret", secretHash($_POST['secret']), $expirationTime, "/");
    } else if (isset($_POST['secretCheck'])) {
        $expirationTime = time() + (86400 * 30); // 30 days
        if (secretHash($_POST['secretCheck']) == $_COOKIE["vulnerable_page_secret"]) {
            $secretSuccessCount = $_COOKIE["vulnerable_page_secretSuccessCount"] + 1;
            setcookie("vulnerable_page_secretSuccessCount", $secretSuccessCount, $expirationTime, "/");
        }
    }
}
add_action('init', 'secretCookieSetterandChecker');

function sqlInjectionForm() {
    if (isset($_POST['email'])) {
        global $wpdb;
        $wpdb->show_errors(true);
        $queryString = "SELECT user_login FROM wp_users WHERE user_email = '{$_POST['email']}'";
        echo "Your Query: " . stripslashes($queryString);
        $results = $wpdb->get_results(stripslashes($queryString));
        echo '<br> Output: ';
        foreach ($results as $result) {
            echo $result->user_login . ", ";
        }
    }
    ?>
    <h3>SQL INJECTION</h3>
    Forgot your login username? Enter your email below to retrieve your username
    <form method="POST">
        <label for="email">Email:</label><br>
        <input type="text" id="email" name="email"><br>
        <input type="submit" value="Submit">
    </form>
    <br>Solution: x' or 1=1 or user_email='x
    <?php
}
add_action('wp_footer', 'sqlInjectionForm');

function cryptoFailure() {
    if (isset($_POST['secret'])) {
        echo 'Output: ' . secretHash($_POST['secret'], false);
        echo '<br>Solution: Paste the output to https://crackstation.net/';
    }
    ?>
    <h3>CRYPTO FAILURE</h3>
    Please enter a secret word/phrase<br>
    <form method="POST">
        <input type="text" id="secret" name="secret"><br>
        <input type="submit" value="Submit">
    </form>
    <?php
}
add_action('wp_footer', 'cryptoFailure');

function brokenAccessControl() {
    if(!is_page('vulnerable_page') || post_password_required()) {
        return;
    }

    echo '<h3>Broken Access Control</h3>';
    echo 'Please click the button to retrieve your account email<br>';
    echo '<form method = "GET">';
    wp_nonce_field('retrieve_email', 'security');
    echo '<input type="hidden" id="userid" name="userid" value="'.get_current_user_id().'"<br>';
    echo '<input type="hidden" id="page_id" name="page_id" value="'.get_the_ID().'"<br>';
    echo '<input type="submit" value="Submit">';
    echo '</form>';

    if(isset($_GET['userid']) && wp_verify_nonce($_GET['security'], 'retrieve_email')) {
        global $wpdb;
        $queryString = $wpdb->prepare("SELECT user_email FROM wp_users WHERE ID=%d", $_GET['userid']);
        $results = $wpdb->get_var($queryString);
        echo 'Output: ' . $results;
    }

    echo '<br>Solution: Check the URL (change the userid)';
}
add_action('wp_footer', 'brokenAccessControl');

function loggingAndMonitoringFailure() {
    if(!is_page('vulnerable_page') || post_password_required()) {
        return;
    }

    echo '<h3>Security Logging and Monitoring Failures</h3>';
    echo 'Make sure you have saved a "secret" to this page on the Crypto Failure module first!<br>';
    echo 'Please enter the secret you entered in the Crypto Failure module<br>';
    echo '<form method="POST">';
    wp_nonce_field('check_secret', 'security');
    echo '<input type="text" id="secretCheck" name="secretCheck"><br>';
    echo '<input type="submit" value="Submit">';
    echo '</form>';

    if(isset($_POST['secretCheck']) && wp_verify_nonce($_POST['security'], 'check_secret')) {
        $secret = get_option('vulnerable_page_secret');
        $count = get_option('vulnerable_page_secretSuccessCount', 0);
        $count++;

        if(secretHash($_POST['secretCheck']) == $secret) {
            echo 'Correct<br>';
            echo 'Successful Entries: ' . $count;
            update_option('vulnerable_page_secretSuccessCount', $count);
        }
        else {
            echo 'Incorrect<br>';
        }
    }
}
add_action('wp_footer', 'loggingAndMonitoringFailure');

function secretHash($secret) {
    return md5($secret);
}

?>
