<?php
/**
 * @package Account Migrate
 */
/*
Plugin Name: Account Migration
Plugin URI: https://f3software.com
Description: Used to migrate an account from a database table to a Wordpress account prior to login.
Version: 0.0.1
Author: Jeff Higham
Author URI: https://github.com/jeffhigham-f3
License: GPLv2 or later
Text Domain: f3software
*/

/*
MIT License

Copyright (c) 2020 Jeff Higham - F3 Software, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'ACCT_TRANSFER_DEBUG', true );
define( 'ACCT_TRANSFER_VERSION', '0.0.1' );
define( 'ACCT_TRANSFER_MINIMUM_WP_VERSION', '4.0' );
define( 'ACCT_TRANSFER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'ACCT_TRANSFER_DB_USER', 'root' );
define( 'ACCT_TRANSFER_DB_PASSWORD', '');
define( 'ACCT_TRANSFER_DB_HOST', 'database' );
define( 'ACCT_TRANSFER_DB_NAME', 'wordpress_development' );
define( 'ACCT_TRANSFER_DB_ACCOUNT_TABLE', 'login');


require_once( ACCT_TRANSFER_PLUGIN_DIR . 'functions.php' );


function dbi_add_settings_page() {
    add_options_page( 'Account Migration', 'Account Migration', 'manage_options', ‘account-migrate-plugin’, 'dbi_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'dbi_add_settings_page' );

function dbi_render_plugin_settings_page() {
    ?>
    <h2>Account Migration Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'account_migrate_plugin_options' );
        do_settings_sections( 'account_migrate_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function dbi_register_settings() {
    register_setting( 'account_migrate_plugin_options', 'account_migrate_plugin_options', 'account_migrate_plugin_options_validate' );
    add_settings_section( 'api_settings', 'API Settings', 'account_migrate_plugin_section_text', 'account_migrate_plugin' );

    add_settings_field( 'account_migrate_plugin_setting_database_name', 'Database Name', 'account_migrate_plugin_setting_database_name', 'account_migrate_plugin', 'api_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_host', 'Database Host', 'account_migrate_plugin_setting_database_host', 'account_migrate_plugin', 'api_settings' );

    add_settings_field( 'account_migrate_plugin_setting_database_username', 'Database Username', 'account_migrate_plugin_setting_database_username', 'account_migrate_plugin', 'api_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_password', 'Database Password', 'account_migrate_plugin_setting_database_password', 'account_migrate_plugin', 'api_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_confirm_password', 'Database Password', 'account_migrate_plugin_setting_database_confirm_password', 'account_migrate_plugin', 'api_settings' );
}
add_action( 'admin_init', 'dbi_register_settings' );

function account_migrate_plugin_section_text() {
    echo '<p>Here you can set all the options for using the API</p>';
}

function account_migrate_plugin_setting_database_host() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_host' name='account_migrate_plugin_options[database_host]' type='text' value='". esc_attr( $options['database_host'] ) ."' />";
}

function account_migrate_plugin_setting_database_name() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_name' name='account_migrate_plugin_options[database_name]' type='text' value='". esc_attr( $options['database_name'] ) ."' />";
}

function account_migrate_plugin_setting_database_username() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_username' name='account_migrate_plugin_options[database_username]' type='text' value='". esc_attr( $options['database_username'] ) ."' />";
}

function account_migrate_plugin_setting_database_password() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_password' name='account_migrate_plugin_options[database_password]' type='password' value='". esc_attr( $options['database_password'] ) ."' />";
}

function account_migrate_plugin_setting_database_confirm_password() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_confirm_password' name='account_migrate_plugin_options[database_confirm_password]' type='password' value='". esc_attr( $options['database_confirm_password'] ) ."' />";
}

function account_migrate_plugin_options_validate( $input ) {
    $newinput['database_name'] = trim( $input['database_name'] );
    return $newinput;
}

