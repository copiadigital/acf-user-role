<?php

namespace AcfUserRole\Providers;

class RolePermissionServiceProvider implements Provider
{
    private $adminBarNodes;
    
    public function __construct()
    {
        add_filter('admin_init', [$this, 'acf_user_role_admin_init']);
        add_action('acf/init', [$this, 'acf_user_role_options']);
        add_action('acf/init', [$this, 'acf_user_role_fields']);
        add_action('admin_bar_menu', [$this, 'acf_user_role_admin_bar'], 999);
        add_action('plugins_loaded', [$this, 'acf_user_role_after_plugin_loaded']);
        add_filter('acf/load_field/name=user_role_admin_menu', [$this, 'acf_load_user_role_admin_menu']);
        // add_filter('acf/load_field/name=user_role_admin_sub_menu', array($this, 'acf_load_user_role_admin_sub_menu'));
        add_filter('acf/prepare_field/name=user_role_admin_bar', [$this, 'acf_load_user_role_admin_bar']);
        add_filter('admin_head', [$this, 'acf_user_role_disable_yoast_taxonomy_metabox']);
    }
    
    public function register()
    {
       //
    }

    public function acf_user_role_after_plugin_loaded() {
        if (!class_exists('acf') && !class_exists('acf_pro') && !function_exists('acf_add_options_page')) {
            deactivate_plugins('acf-user-role/acf-user-role.php');
            add_action( 'admin_notices', function() {
                $class = 'notice notice-error';
                $message = __( 'ACF Class or ACF Pro not found!', 'acf-user-role' );

                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            } );
        }
    }

    public function acf_user_role_admin_init() {
        global $wp_roles, $submenu, $menu;

        // get current user
        $user = wp_get_current_user();

        if(get_field('user_roles', 'option')) {
            foreach(get_field('user_roles', 'option') as $role) {
                $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));

                // check if user role exist
                if($wp_roles->is_role($role_name_plain)) {
                    // add role
                    add_role( $role_name_plain, $role_name_plain);
                }

                $getCurrentRole = get_role($role_name_plain);
                if($role['user_role_permission']) {
                    foreach($role['user_role_permission'] as $capabilities) {
                        // add capabilities
                        if($capabilities || !empty($capabilities)) {
                            $getCurrentRole->add_cap($capabilities, true);
                        }
                    }
                    $removeCurrentRolePermission = array_diff(array_keys($getCurrentRole->capabilities), $role['user_role_permission']);
                    foreach($removeCurrentRolePermission as $removeCap) {
                        // remove cabalities
                        $getCurrentRole->remove_cap($removeCap);
                    }
                } else {
                    // if there is no selected then it will remove all capabilities
                    $getCaps = array_keys($getCurrentRole->capabilities);
                    foreach($getCaps as $cap) {
                        $getCurrentRole->remove_cap($cap);
                    }
                }

                
                if(in_array( 'aur_' . $role['user_role_name'], (array) $user->roles )) {
                    // hide admin menu
                    if($role['user_role_admin_menu']) {
                    // if(array_intersect( $allowed_roles, (array) $user->roles )[1]) {
                        // remove admin menu pages
                        $acfAdminMenuIntersect = array_intersect($role['user_role_admin_menu'], $this->getAdminMenuList($menu));
                        foreach($acfAdminMenuIntersect as $adminItem) {
                            remove_menu_page( $adminItem );
                        }
                    }

                    // hide yoast metabox in posts page
                    if($role['user_role_others_yoast_metabox']) {
                        // Remove page analysis columns from post lists, also SEO status on post editor
                        add_filter( 'wpseo_use_page_analysis', '__return_false' );
                        // Remove Yoast meta boxes
                        add_action( 'add_meta_boxes', [$this, 'acf_user_role_disable_yoast_posts_metabox'], 100000 );
                    }
                }
            }   
        }

        // remove role based on acf repeater
        $remove_role_list = array_diff($this->getWpUserRoleList($wp_roles), $this->getAcfUserRoleList());
        foreach($remove_role_list as $role_list) {
            remove_role($role_list);
        }
    }

    public function getAdminMenuList($menu) {
        $getAdminMenuArray = [];
        foreach($menu as $item) {
            if($item[0] !== '' && $item[4] !== 'wp-menu-separator') {
                $getAdminMenuArray[] = $item[2];
            }
        }

        return $getAdminMenuArray;
    }

    public function getAcfUserRoleList() {
        $acf_user_role_array = [];
        if(get_field('user_roles', 'option')) {
            foreach(get_field('user_roles', 'option') as $role) {
                $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));
                $acf_user_role_array[] = $role_name_plain;
            }
        }
        return $acf_user_role_array;
    }

    public function getWpUserRoleList($wp_roles) {
        $all_roles_array = [];
        $all_roles = $wp_roles->roles;
        foreach($all_roles as $role) {
            if(substr( $role['name'], 0, 3 ) === 'aur') {
                // get only the roles that start with aur
                $all_roles_array[] = $role['name'];
            }
        }
        // var_dump($all_roles['aur_qwerty']);
        return $all_roles_array;
    }

    public function acf_user_role_admin_bar( $wp_admin_bar ) {
        global $wp_roles;

        // pass wp_admin_bar->get_nodes data
        $this->adminBarNodes = $wp_admin_bar->get_nodes();

        // get current user
        $user = wp_get_current_user();

        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if(get_field('user_roles', 'option')) {
            foreach(get_field('user_roles', 'option') as $role) {
                $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));
                
                if($role['user_role_admin_bar']) {
                    if(in_array( 'aur_' . $role['user_role_name'], (array) $user->roles )) {
                        // remove admin bar
                        $acfAdminbarIntersect = array_intersect($role['user_role_admin_bar'], array_keys($this->adminBarNodes));
                        foreach($acfAdminbarIntersect as $adminItem) {
                            $wp_admin_bar->remove_menu($adminItem);
                        }
                    }
                }
            }
        }
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

    public function acf_load_user_role_admin_menu($field) {
        global $menu;

        $field['choices'] = array_combine($this->getAdminMenuList($menu), $this->getAdminMenuList($menu));
        return $field;
    }

    public function acf_load_user_role_admin_bar($field) {
        $field['choices'] = array_combine(array_keys($this->adminBarNodes), array_keys($this->adminBarNodes));
        return $field;
    }

    // public function acf_load_user_role_admin_sub_menu($field) {
    //     global $submenu, $wp_admin_bar;
    //     // dd($wp_admin_bar);
    //     $getAdminSubMenuArray = [];
    //     foreach($submenu as $key => $item) {
    //         if($key !== '') {
    //             // $getAdminSubMenuArray[] = $item[2];
    //             // unset($item);
    //         }
    //     }

    //     // dd($getAdminSubMenuArray);
    //     // $field['choices'] = array_combine($this->getAdminMenuList($menu), $this->getAdminMenuList($menu));
    //     return $field;
    // }

    public function acf_user_role_disable_yoast_posts_metabox() {
        remove_meta_box( 'wpseo_meta', '', 'normal' );
    }

    public function acf_user_role_disable_yoast_taxonomy_metabox() {
        global $wp_roles, $pagenow;

        // get current user
        $user = wp_get_current_user();

        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if (is_admin()) {
            if($pagenow === 'term.php') {
                if(get_field('user_roles', 'option')) {
                    foreach(get_field('user_roles', 'option') as $role) {
                        $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));

                        if($role['user_role_others_yoast_metabox']) {
                            if(in_array( 'aur_' . $role['user_role_name'], (array) $user->roles )) {
                                // hide yoast metabox in posts page
                                echo '<style type="text/css" id="acf-user-role-yoast-taxonomy-metabox-hide">#wpseo_meta { display: none }</style>';
                            }
                        }
                    }
                }
            }
        }  
    }

    public function acf_user_role_fields() {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        $getCapabilities = array_keys(get_role( 'administrator' )->capabilities);

        acf_add_local_field_group(array(
            'key'   => 'group_user_role_settings',
            'title' => 'User Role Settings',
            'fields'    => array (),
            'position'  => 'acf_after_title',
            'menu_order'    => 0,
            'label_placement'   => 'top',
            'style' => 'default',
            'active'    => true,
            'description'   => '',
            'location'  => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator'  => '==',
                        'value' => 'acf-options-user-role-settings',
                    ),
                ),
            ),
        ));
    
            acf_add_local_field(array(
                'key'   => 'field_user_role_settings_user_roles',
                'label' => 'User Roles',
                'name'  => 'user_roles',
                'type'  => 'repeater',
                'layout'    => 'block',
                'parent'    => 'group_user_role_settings',
                'required'  => 0,
                'min'   => '',
                'max'   => '',
                'button_label'  => 'Add Role',
                'sub_field' => array(),
            ));
    
                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_name',
                    'label'        => 'Role Name',
                    'instructions' => 'Dont use spacing. If you want spacing then use underscore (_)',
                    'name'         => 'user_role_name',
                    'type'         => 'text',
                    'required'     => 1,
                    'parent'       => 'field_user_role_settings_user_roles',
                ));
    
                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_permission_tab',
                    'label'        => 'Role Permission',
                    'name'         => '',
                    'type'         => 'tab',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));
    
                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_permission',
                    'label'        => 'Role Permission',
                    'name'         => 'user_role_permission',
                    'type'         => 'checkbox',
                    'required'     => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'choices' => array_combine($getCapabilities, $getCapabilities),
                    'allow_custom' => 0,
                    'save_custom' => 0,
                    'default_value' => [],
                    'layout' => 'vertical',
                    'toggle' => 0,
                    'return_format' => 'value',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));
    
                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_admin_menu_tab',
                    'label'        => 'Hide Admin Menu',
                    'name'         => '',
                    'type'         => 'tab',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_admin_menu',
                    'label'        => 'Hide Admin Menu',
                    'name'         => 'user_role_admin_menu',
                    'type'         => 'checkbox',
                    'required'     => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'choices' => [],
                    'allow_custom' => 0,
                    'save_custom' => 0,
                    'default_value' => [],
                    'layout' => 'vertical',
                    'toggle' => 0,
                    'return_format' => 'value',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                // acf_add_local_field(array(
                //     'key'          => 'field_user_role_settings_user_roles_user_role_admin_sub_menu_tab',
                //     'label'        => 'Hide Admin Sub Menu',
                //     'name'         => '',
                //     'type'         => 'tab',
                //     'parent'       => 'field_user_role_settings_user_roles',
                // ));

                // acf_add_local_field(array(
                //     'key'          => 'field_user_role_settings_user_roles_user_role_admin_sub_menu',
                //     'label'        => 'Hide Admin Sub Menu',
                //     'name'         => 'user_role_admin_sub_menu',
                //     'type'         => 'checkbox',
                //     'required'     => 0,
                //     'wrapper' => [
                //         'width' => '',
                //         'class' => '',
                //         'id' => '',
                //     ],
                //     'choices' => [],
                //     'allow_custom' => 0,
                //     'save_custom' => 0,
                //     'default_value' => [],
                //     'layout' => 'vertical',
                //     'toggle' => 0,
                //     'return_format' => 'value',
                //     'parent'       => 'field_user_role_settings_user_roles',
                // ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_admin_bar_tab',
                    'label'        => 'Hide Admin Bar',
                    'name'         => '',
                    'type'         => 'tab',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_admin_bar',
                    'label'        => 'Hide Admin Bar',
                    'name'         => 'user_role_admin_bar',
                    'type'         => 'checkbox',
                    'required'     => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'choices' => [],
                    'allow_custom' => 0,
                    'save_custom' => 0,
                    'default_value' => [],
                    'layout' => 'vertical',
                    'toggle' => 0,
                    'return_format' => 'value',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_others_tab',
                    'label'        => 'Others',
                    'name'         => '',
                    'type'         => 'tab',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_others_yoast_metabox_bar',
                    'label'        => 'Disable Yoast Metabox',
                    'name'         => 'user_role_others_yoast_metabox',
                    'type'         => 'true_false',
                    'required'     => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));
    }
}
