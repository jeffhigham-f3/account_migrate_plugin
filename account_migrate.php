<?php
/**
 * @package Account Migrate
 */
/*
Plugin Name: Account Migration
Plugin URI: https://github.com/jeffhigham-f3/account_migrate
Description: Migrate a user account from an external database into Wordpress. Install the plugin, configure the database information under settings, and Users will seamlessly migrate upon login. Supports migrating plain-text passwords (yuck), passwords created PHP <a href='https://www.php.net/manual/en/function.password-hash.php'>password_hash</a>, or your own custom PHP functions to verify passwords. 
Version: 1.0.0
Author: Jeff Higham
Author URI: https://github.com/jeffhigham-f3
License: GPLv3
Text Domain: f3software
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	print 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'ACCT_MIGRATE_DEBUG', false );
define( 'ACCT_MIGRATE_VERSION', '0.0.1' );
define( 'ACCT_MIGRATE_MINIMUM_WP_VERSION', '4.0' );
define( 'ACCT_MIGRATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ACCT_MIGRATE_PLUGIN_DIR . 'functions.php' );

function account_migrate_add_settings_page() {
    add_options_page( 'Account Migration', 'Account Migration', 'manage_options', 'account-migrate-plugin', 'account_migrate_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'account_migrate_add_settings_page' );

function account_migrate_render_plugin_settings_page() {
    ?>
    <h2>Account Migration Plugin</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'account_migrate_options' );
        do_settings_sections( 'account_migrate' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Options' ); ?>" />
    </form>
    <?php
}

function account_migrate_register_settings() {
    register_setting( 'account_migrate_options', 'account_migrate_options', 'account_migrate_options_validate' );
    add_settings_section( 'account_migrate', 'Database Settings', 'account_migrate_section_text', 'account_migrate' );
    add_settings_field( 'account_migrate_database_name', 'Database Name', 'account_migrate_database_name', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_host', 'Database Host', 'account_migrate_database_host', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_username', 'Database Username', 'account_migrate_database_username', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_password', 'Database Password', 'account_migrate_database_password', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_table', 'Database Table', 'account_migrate_database_table', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_user_column', 'Database UserColumn', 'account_migrate_database_user_column', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_password_column', 'Database Password Column', 'account_migrate_database_password_column', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_user_role', 'Wordpress User Role', 'account_migrate_user_role', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_password_algorithm', 'Database Password Algorithm', 'account_migrate_database_password_algorithm', 'account_migrate', 'account_migrate' );
    add_settings_field( 'account_migrate_database_custom_validator', 'Custom Validator Function', 'account_migrate_database_custom_validator', 'account_migrate', 'account_migrate' );

}
add_action( 'admin_init', 'account_migrate_register_settings' );

function account_migrate_section_text() {
    print '<p>Here you can set all of the Account Migration plugin options.</p>';
}

function account_migrate_database_host() {
    $options = get_option( 'account_migrate_options' );
    $options['database_host'] = ($options['database_host'] == '') ? 'localhost' : $options['database_host'];
    print "<input id='account_migrate_database_host' name='account_migrate_options[database_host]' type='text' value='". esc_attr( $options['database_host'] ) ."' />";
}

function account_migrate_database_name() {
    $options = get_option( 'account_migrate_options' );
    $options['database_name'] = ($options['database_name'] == '') ? 'database' : $options['database_name'];
    print "<input id='account_migrate_database_name' name='account_migrate_options[database_name]' type='text' value='". esc_attr( $options['database_name'] ) ."' />";
}

function account_migrate_database_username() {
    $options = get_option( 'account_migrate_options' );
    $options['database_username'] = ($options['database_username'] == '') ? 'root' : $options['database_username'];
    print "<input id='account_migrate_database_username' name='account_migrate_options[database_username]' type='text' value='". esc_attr( $options['database_username'] ) ."' />";
}

function account_migrate_database_password() {
    $options = get_option( 'account_migrate_options' );
    print "<input id='account_migrate_database_password' name='account_migrate_options[database_password]' type='password' value='". esc_attr( $options['database_password'] ) ."' />";
}

function account_migrate_database_table() {
    $options = get_option( 'account_migrate_options' );
    print "<input id='account_migrate_database_table' name='account_migrate_options[database_table]' type='text' value='". esc_attr( $options['database_table'] ) ."' />";
}

function account_migrate_database_user_column() {
    $options = get_option( 'account_migrate_options' );
    print "<input id='account_migrate_database_user_column' name='account_migrate_options[database_user_column]' type='text' value='". esc_attr( $options['database_user_column'] ) ."' />";
}

function account_migrate_database_password_column() {
    $options = get_option( 'account_migrate_options' );
    print "<input id='account_migrate_database_password_column' name='account_migrate_options[database_password_column]' type='text' value='". esc_attr( $options['database_password_column'] ) ."' />";
}

function account_migrate_database_password_algorithm() {
    $options = get_option( 'account_migrate_options' );
    $options['password_algorithm'] = ($options['password_algorithm'] == '') ? 'PLAIN_TEXT' : $options['password_algorithm'];

    account_migrate_log( print_r($options['password_algorithm'],1) );


    print "<select id='account_migrate_database_password_algorithm' name='account_migrate_options[password_algorithm]'>";
    print "<option value='PASSWORD_VERIFY' ". selected( $options['password_algorithm'], 'PASSWORD_VERIFY' ) .">PASSWORD VERIFY (PHP)</option>";
    print "<option value='PLAIN_TEXT' ". selected( $options['password_algorithm'], 'PLAIN_TEXT' ) .">PLAIN TEXT</option>";
    print "<option value='CUSTOM_VALIDATOR_FUNCTION' ". selected( $options['password_algorithm'], 'CUSTOM_VALIDATOR_FUNCTION' ) .">CUSTOM VALIDATOR FUNCTION</option>";
    print "</select>";
    print ' <a target="_blank" href="https://www.php.net/manual/en/function.password-hash.php">More Info</a>';
}

function account_migrate_database_custom_validator() {
    $options = get_option( 'account_migrate_options' );
    $options['password_validator'] = ($options['password_validator'] == '') ? file_get_contents(ACCT_MIGRATE_PLUGIN_DIR . "/sample_validator.php") : $options['password_validator'];
    $disabled = ($options['password_algorithm'] == 'CUSTOM_VALIDATOR_FUNCTION') ? '' : 'disabled';
    print "<textarea ". $disabled ."  rows='20' cols='80' id='account_migrate_password_validator' name='account_migrate_options[password_validator]'>". $options['password_validator'] ."</textarea>";
}

function account_migrate_user_role() {
    $options = get_option( 'account_migrate_options' );
    $roles = wp_roles();
    print "<select id='account_migrate_user_role' name='account_migrate_options[user_role]'>";
    foreach( wp_roles()->roles as $role => $roleObj ){
        print "<option value='". $role ."' ". selected( $options['user_role'], $role ) .">". $roleObj['name'] ."</option>";
    }
    print "</select>";
}

function account_migrate_options_validate( $input ) {
    return $input;
}