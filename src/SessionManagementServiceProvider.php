<?php

namespace JeffersonGoncalves\SessionManagement;

use JeffersonGoncalves\SessionManagement\Commands\PruneSessionsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SessionManagementServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('session-management')
            ->hasConfigFile()
            ->hasCommand(PruneSessionsCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SessionService::class);
    }
}
