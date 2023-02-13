<?php

namespace AcfUserRole\Providers;

class AcfUserRoleServiceProvider implements Provider
{
    protected function providers()
    {
        return [
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
