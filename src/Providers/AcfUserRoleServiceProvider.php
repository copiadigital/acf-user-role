<?php

namespace AcfUserRole\Providers;

class AcfUserRoleServiceProvider implements Provider
{
    protected function providers()
    {
        return [
            OptionsServiceProvider::class,
            FieldsServiceProvider::class,
            RolePermissionServiceProvider::class,
        ];
    }

    public function register()
    {
        foreach ($this->providers() as $service) {
            (new $service)->register();
        }
    }

    public function boot()
    {
        //
    }
}
