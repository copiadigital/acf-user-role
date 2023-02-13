<?php
/**
* Plugin Name:  ACF User Role
* Text Domain:  acf-user-role
* Description:  The last user role you'll ever need
* Version:      1.0.0
* Author:       Copia Digital
* Author URI:   https://www.copiadigital.com/
* License:      MIT License
*/

$autoload_path = __DIR__.'/vendor/autoload.php';
if ( file_exists( $autoload_path ) ) {
    require_once( $autoload_path );
}

$clover = new AcfUserRole\Providers\AcfUserRoleServiceProvider;
$clover->register();

add_action('init', [$clover, 'boot']);

add_action('plugins_loaded', function() {
    if (!class_exists('acf') && !class_exists('acf_pro') && !function_exists('acf_add_options_page')) {
        deactivate_plugins('acf-user-role/acf-user-role.php');
        add_action( 'admin_notices', function() {
            $class = 'notice notice-error';
            $message = __( 'ACF Class or ACF Pro not found!', 'acf-user-role' );
  
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        } );
    }
});