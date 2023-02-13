<?php

namespace AcfUserRole\Providers;

class OptionsServiceProvider implements Provider
{
    public function __construct()
    {
        add_action('acf/init', [$this, 'acf_user_role_options']);
    }
    
    public function register()
    {
       //
    }

    public function acf_user_role_options() {
        if( function_exists('acf_add_options_page') ) {
        
            acf_add_options_page(array(
                'page_title' 	=> 'User Role Settings',
                'menu_title'	=> 'User Role Settings',
                'menu_slug' 	=> 'acf-options-user-role-settings',
                'capability'	=> 'edit_theme_options',
                'redirect'		=> false,
                'icon_url' => 'dashicons-admin-generic',
            ));
    
        }
    }
}
