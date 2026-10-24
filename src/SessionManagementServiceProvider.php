<?php

namespace JeffersonGoncalves\SessionManagement;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SessionManagementServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-session-management')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
