<?php

function account_migrate_log($text) {
    $options = get_option('account_migrate_options');
    if( $options['debug']  == true ){
        $date = new DateTime();
        $date = $date->format("Y:m:d h:i:s");
        error_log($date ." ". $text . "\n", 3, ACCT_MIGRATE_PLUGIN_DIR. "/debug.log");
    }
}

function account_migrate_action($username, $password){

    $options = get_option('account_migrate_options');
    $mysqli = new mysqli($options['database_host'], $options['database_username'],  $options['database_password'], $options['database_name']);
    account_migrate_log("Connecting to Host: ". $options['database_host'] ." Database: ".$options['database_name'] );
    if ($mysqli->connect_errno) {
        account_migrate_log( "Error: Failed to make a MySQL connection.  Errno: " . $mysqli->connect_errno);
    }
    $query = "select * from ". $options['database_table'] ." where ". $options['database_user_column'] ."='$username'";
    account_migrate_log($query);
    $result = $mysqli->query($query);
    
    if($result->num_rows != 1)
    return;
    
    $dbUser = $result->fetch_assoc();
    account_migrate_log(print_r( array_replace($dbUser, ['password' => '*****']) ,1));

    account_migrate_log("Password algorithm is: ". $options['password_algorithm'] );
    $authenticated = false;
    $dbPassword = $dbUser['password'];
    switch ($options['password_algorithm']) {
        
        case "PASSWORD_VERIFY":
            $authenticated = password_verify($password, $dbPassword);
            break;

        case "PLAIN_TEXT":
            $authenticated = ($dbPassword == $password);
            break;

        case "CUSTOM_VALIDATOR_FUNCTION":
            account_migrate_log("Executing code:\n". print_r($options['password_validator'], true) );
            eval($options['password_validator']);
            if( class_exists('\AccountMigrate\Password') && method_exists('\AccountMigrate\Password','validate') ) {
                $authenticated = \AccountMigrate\Password::validate($password, $dbPassword);
            } else {
                account_migrate_log("Invalid custom validator!"); 
            }
            break;
        
        default: 
            break;
    }
    
    if( $authenticated ) {
        $dbUser['password'] = $password;
        account_migrate_log("Found account for ". $username);
        account_migrate_log( print_r( array_replace($dbUser, ['password' => '*****']), true) );
        account_migrate_create_user($dbUser);
    } else {
        account_migrate_log("Failed to authenticate user ". $username );
    }

    $result->free();
    $mysqli->close();
}


function account_migrate_create_user($user){
    $options = get_option('account_migrate_options');

    $userDetails = Array(
        "user_login" => $user["username"],
        "user_pass" => $user["password"],
        "display_name" => $user["name"],
        "show_admin_bar_front" => false,
        "description" => "Migrated from Database: ". $options['database_name'] .", Table: ". $options['database_table'],
        "role" => wp_roles()->is_role( $options['user_role'] ) ? $options['user_role'] : 'subscriber'
    );
    account_migrate_log("Inserting user with attributes:");
    account_migrate_log( print_r( array_replace($userDetails, ['user_pass' => '*****']) , true) );

    $user_id = wp_insert_user( $userDetails ) ;
    account_migrate_log("New Wordpress user_id is: ". $user_id);

    if ( ! is_wp_error( $user_id ) ) {

        if( is_file(ACCT_MIGRATE_PLUGIN_DIR . "/user_meta.php") ){
            require_once(ACCT_MIGRATE_PLUGIN_DIR . "/user_meta.php");
            account_migrate_create_user_metta($user_id, $user);
            account_migrate_log("Added user metta for ". $user["username"]);
            account_migrate_log( print_r(get_user_meta($user_id), true) );
        }
    }   

}

function account_migrate_preauth_hook($username, $password ) {

    if (!empty($username) && !empty($password)) {
        if ( !username_exists( $username ) ){
            account_migrate_log("Login => Username: " . $username  ." migrating account to Wordpress.");
            account_migrate_action($username, $password);
        } else {
            account_migrate_log("Login => Username: " . $username ." is already a Wordpress user. Merging existing users is not a supported feature." );
        }
    }

  }

  add_action('wp_authenticate', 'account_migrate_preauth_hook', 30, 2);