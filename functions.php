<?php

function account_migrate_log($text) {
    if( ACCT_TRANSFER_DEBUG  == true ){
        $date = new DateTime();
        $date = $date->format("Y:m:d h:i:s");
        error_log($date ." ". $text . "\n", 3, ACCT_TRANSFER_PLUGIN_DIR. "/debug.log");
    }
}


function account_migrate_action($username, $password){

        $mysqli = new mysqli(ACCT_TRANSFER_DB_HOST, ACCT_TRANSFER_DB_USER, ACCT_TRANSFER_DB_PASSWORD, ACCT_TRANSFER_DB_NAME);
        account_migrate_log("Connecting to Host: ". ACCT_TRANSFER_DB_HOST ." Database: ".ACCT_TRANSFER_DB_NAME );

        if ($mysqli->connect_errno) {
            account_migrate_log( "Error: Failed to make a MySQL connection.  Errno: " . $mysqli->connect_errno);
            exit;
        }
        $query = "select * from ". ACCT_TRANSFER_DB_ACCOUNT_TABLE ." where password='$password' AND username='$username'";
        account_migrate_log($query);

        $result = $mysqli->query($query);
        if ($result->num_rows === 1 ) {
            account_migrate_log("Found account for ". $username);
            account_migrate_create_user($username, $password);
        } else {
            account_migrate_log("Failed to authenticate user ". $username );
        }
        $mysqli->close();

}


function account_migrate_create_user($username, $password){
    $userDetails = Array(
        "user_login" => $username,
        "user_pass" => $password,
        "description" => "Migrated from Database: ". ACCT_TRANSFER_DB_NAME .", Table: ". ACCT_TRANSFER_DB_ACCOUNT_TABLE,
    );
    wp_insert_user($userDetails);
}


function account_migrate_preauth_hook($username, $password ) {

    if (!empty($username) && !empty($password)) {
        account_migrate_log("Login => Username: " . $username .", Password: ". $password );
        account_migrate_action($username, $password);
    }

  }
  add_action('wp_authenticate', 'account_migrate_preauth_hook', 30, 2);