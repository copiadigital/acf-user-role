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

require_once __DIR__.'/../../../../vendor/autoload.php';

$clover = new AcfUserRole\Providers\AcfUserRoleServiceProvider;
$clover->register();

add_action('init', [$clover, 'boot']);