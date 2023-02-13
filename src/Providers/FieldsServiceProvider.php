<?php

namespace AcfUserRole\Providers;

class FieldsServiceProvider implements Provider
{
    public function __construct()
    {
        add_action('acf/init', [$this, 'acf_user_role_fields']);
    }
    
    public function register()
    {
       //
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
                'collapsed' => 'field_user_role_settings_user_roles_user_role_name',
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
                    'key'          => 'field_user_role_settings_user_roles_user_role_custom_permission_tab',
                    'label'        => 'Custom Role Permission',
                    'name'         => '',
                    'type'         => 'tab',
                    'parent'       => 'field_user_role_settings_user_roles',
                ));

                acf_add_local_field(array(
                    'key'          => 'field_user_role_settings_user_roles_user_role_custom_permission',
                    'label'        => 'Custom Role Permission',
                    'name'         => 'user_role_custom_permission',
                    'type'         => 'checkbox',
                    'required'     => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'choices' => [],
                    'allow_custom' => 1,
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
