<?php

namespace AcfUserRole\Providers;

class RolePermissionServiceProvider implements Provider
{
    private $adminBarNodes;
    private $parentMenus = [];
    
    public function __construct()
    {
        add_filter('admin_init', [$this, 'acf_user_role_admin_init']);
        add_action('admin_menu', [$this, 'build_admin_menu_list'], 9999);
        add_action('admin_menu', [$this, 'acf_user_role_admin_menu'], 9999);
        add_action('admin_bar_menu', [$this, 'acf_user_role_admin_bar'], 999);
        add_filter('acf/load_field/key=field_user_role_settings_user_roles_user_role_admin_menu', [$this, 'acf_user_role_load_admin_menu']);
        add_filter('acf/prepare_field/key=field_user_role_settings_user_roles_user_role_admin_bar', [$this, 'acf_user_role_load_admin_bar']);
        add_filter('acf/validate_value/key=field_user_role_settings_user_roles_user_role_name', [$this, 'acf_user_role_unique_role_name_validation'], 20, 4);
        add_filter('admin_head', [$this, 'acf_user_role_disable_yoast_metabox']);
    }

    public function register()
    {
        //
    }

    public function acf_user_role_admin_init() {
        global $wp_roles;

        // get current user
        $user = wp_get_current_user();

        if(is_admin()) {
            if(get_field('user_roles', 'option')) {
                foreach(get_field('user_roles', 'option') as $role) {
                    $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));

                    add_role( $role_name_plain, $role_name_plain);

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
                    
                    if($role['user_role_custom_permission']) {
                        foreach($role['user_role_custom_permission'] as $capabilities) {
                            if($capabilities || !empty($capabilities)) {
                                // add custom permission
                                $getCurrentRole->add_cap($capabilities, true);
                            } else {
                                // remove custom permission
                                $getCurrentRole->remove_cap($capabilities);
                            }
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
    }

    public function build_admin_menu_list() {
        global $menu;
        foreach($menu as $item) {
            if($item[0] !== '' && $item[4] !== 'wp-menu-separator') {
                $this->parentMenus[] = $item[2];
            }
        }
        return $this->parentMenus;
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
        $all_roles = array_keys($wp_roles->role_names);
        foreach($all_roles as $role) {
            if(substr( $role, 0, 3 ) === 'aur') {
                // get only the roles that start with aur
                $all_roles_array[] = $role;
            }
        }
        return $all_roles_array;
    }

    public function acf_user_role_admin_menu( $wp_admin_bar ) {
        global $wp_roles;

        // get current user
        $user = wp_get_current_user();

        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if(is_admin()) {
            if(get_field('user_roles', 'option')) {
                foreach(get_field('user_roles', 'option') as $role) {
                    $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));
                    
                    if($role['user_role_admin_menu']) {
                        if(in_array( 'aur_' . $role['user_role_name'], (array) $user->roles )) {
                            // remove admin menu pages
                            if($this->build_admin_menu_list()) {
                                $acfAdminMenuIntersect = array_intersect($role['user_role_admin_menu'], $this->build_admin_menu_list());
                                if($acfAdminMenuIntersect) {
                                    foreach($acfAdminMenuIntersect as $adminItem) {
                                        remove_menu_page( $adminItem );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function acf_user_role_admin_bar( $wp_admin_bar ) {
        global $wp_roles, $pagenow, $typenow;

        // pass wp_admin_bar->get_nodes data
        $this->adminBarNodes = $wp_admin_bar->get_nodes();

        // get current user
        $user = wp_get_current_user();

        if (!function_exists('acf_add_options_page')) {
            return;
        }
        
        if(class_exists('ACFE')) {
            if(($pagenow === 'post.php' || $pagenow === 'post-new.php') && $typenow === 'acf-field-group') {
                if(get_field('user_roles', 'option')) {
                    foreach(get_field('user_roles', 'option') as $role) {
                        $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['field_user_role_settings_user_roles_user_role_name']));
                        
                        if($role['field_user_role_settings_user_roles_user_role_admin_bar']) {
                            if(in_array( 'aur_' . $role['field_user_role_settings_user_roles_user_role_name'], (array) $user->roles )) {
                                // remove admin bar
                                $acfAdminbarIntersect = array_intersect($role['field_user_role_settings_user_roles_user_role_admin_bar'], array_keys($this->adminBarNodes));
                                foreach($acfAdminbarIntersect as $adminItem) {
                                    $wp_admin_bar->remove_menu($adminItem);
                                }
                            }
                        }
                    }
                }
            } else {
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
        } else {
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
    }

    public function acf_user_role_load_admin_menu($field) {
        if(is_admin()) {
            $field['choices'] = array_combine($this->parentMenus, $this->parentMenus);
        }
        return $field;
    }

    public function acf_user_role_load_admin_bar($field) {
        if(is_admin()) {
            $field['choices'] = array_combine(array_keys($this->adminBarNodes), array_keys($this->adminBarNodes));
        }
        return $field;
    }

    public function acf_user_role_unique_role_name_validation($valid, $value, $field, $input) {
        if (!$valid) {
            return $valid;
        }
        
        // get list of array indexes from $input
        // [ <= this fixes my IDE, it has problems with unmatched brackets
        preg_match_all('/\[([^\]]+)\]/', $input, $matches);
        if (!count($matches[1])) {
            // this should actually never happen
            return $valid;
        }
        $matches = $matches[1];
        
        // walk the acf input to find the repeater and current row      
        $array = $_POST['acf'];
        
        $repeater_key = false;
        $repeater_value = false;
        $row_key = false;
        $row_value = false;
        $field_key = false;
        $field_value = false;
        
        for ($i = 0; $i < count($matches); $i++) {
            if (isset($array[$matches[$i]])) {
                $repeater_key = $row_key;
                $repeater_value = $row_value;
                $row_key = $field_key;
                $row_value = $field_value;
                $field_key = $matches[$i];
                $field_value = $array[$matches[$i]];
                if ($field_key == $field['key']) {
                    break;
                }
                $array = $array[$matches[$i]];
            }
        }
        
        if (!$repeater_key) {
            // this should not happen, but better safe than sorry
            return $valid;
        }
        
        // look for duplicate values in the repeater
        foreach($repeater_value as $index => $row) {
            if($index != $row_key && strtolower($row[$field_key]) == strtolower($value)) {
                // this is a different row with the same value
                $valid = 'This value is not unique';
            }
            if($index == $row_key && preg_match("/[^a-zA-Z0-9_.]/", $row[$field_key])) {
                $valid = 'This value doesnt accept any special character or spacing';
            }
        }
    
        return $valid;
    }

    public function acf_user_role_disable_yoast_posts_metabox() {
        remove_meta_box( 'wpseo_meta', '', 'normal' );
    }

    public function acf_user_role_disable_yoast_metabox() {
        global $wp_roles, $pagenow, $typenow;

        // get current user
        $user = wp_get_current_user();

        if (!function_exists('acf_add_options_page')) {
            return;
        }

        if (is_admin()) {
            if(($pagenow === 'term.php' || $pagenow === 'post.php' || $pagenow === 'post-new.php') && $typenow !== 'acf-field-group') {
                if(get_field('user_roles', 'option')) {
                    foreach(get_field('user_roles', 'option') as $role) {
                        $role_name_plain = 'aur_' . preg_replace("/[^a-zA-Z0-9_.]/", '', strtolower($role['user_role_name']));

                        if($role['user_role_others_yoast_metabox']) {
                            if(in_array( 'aur_' . $role['user_role_name'], (array) $user->roles )) {
                                // remove page analysis columns from post lists, also SEO status on post editor
                                add_filter( 'wpseo_use_page_analysis', '__return_false' );

                                // hide yoast metabox
                                echo '<style type="text/css" id="acf-user-role-yoast-metabox-hide">#wpseo_meta { display: none }</style>';
                            }
                        }
                    }
                }
            }
        }  
    }
}
