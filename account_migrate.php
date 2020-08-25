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

define( 'ACCT_MIGRATE_DEBUG', true );
define( 'ACCT_MIGRATE_VERSION', '0.0.1' );
define( 'ACCT_MIGRATE_MINIMUM_WP_VERSION', '4.0' );
define( 'ACCT_MIGRATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ACCT_MIGRATE_PLUGIN_DIR . 'functions.php' );

function account_migrate_add_settings_page() {
    add_options_page( 'Account Migration', 'Account Migration', 'manage_options', ‘account-migrate-plugin’, 'account_migrate_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'account_migrate_add_settings_page' );

function account_migrate_render_plugin_settings_page() {
    ?>
    <h2>Account Migration Plugin</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'account_migrate_plugin_options' );
        do_settings_sections( 'account_migrate_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function account_migrate_register_settings() {
    register_setting( 'account_migrate_plugin_options', 'account_migrate_plugin_options', 'account_migrate_plugin_options_validate' );
    add_settings_section( 'account_migrate_settings', 'Database Settings', 'account_migrate_plugin_section_text', 'account_migrate_plugin' );
    add_settings_field( 'account_migrate_plugin_setting_database_name', 'Database Name', 'account_migrate_plugin_setting_database_name', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_host', 'Database Host', 'account_migrate_plugin_setting_database_host', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_username', 'Database Username', 'account_migrate_plugin_setting_database_username', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_password', 'Database Password', 'account_migrate_plugin_setting_database_password', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_table', 'Database Table', 'account_migrate_plugin_setting_database_table', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_user_column', 'Database UserColumn', 'account_migrate_plugin_setting_database_user_column', 'account_migrate_plugin', 'account_migrate_settings' );
    add_settings_field( 'account_migrate_plugin_setting_database_password_column', 'Database Password Column', 'account_migrate_plugin_setting_database_password_column', 'account_migrate_plugin', 'account_migrate_settings' );

}
add_action( 'admin_init', 'account_migrate_register_settings' );

function account_migrate_plugin_section_text() {
    echo '<p>Here you can set all of the Account Migration plugin options.</p>';
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

function account_migrate_plugin_setting_database_table() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_table' name='account_migrate_plugin_options[database_table]' type='text' value='". esc_attr( $options['database_table'] ) ."' />";
}

function account_migrate_plugin_setting_database_user_column() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_user_column' name='account_migrate_plugin_options[database_user_column]' type='text' value='". esc_attr( $options['database_user_column'] ) ."' />";
}

function account_migrate_plugin_setting_database_password_column() {
    $options = get_option( 'account_migrate_plugin_options' );
    echo "<input id='account_migrate_plugin_setting_database_password_column' name='account_migrate_plugin_options[database_password_column]' type='text' value='". esc_attr( $options['database_password_column'] ) ."' />";
}



function account_migrate_plugin_options_validate( $input ) {
    return $input;
}