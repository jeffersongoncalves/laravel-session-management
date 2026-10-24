<?php

namespace JeffersonGoncalves\SessionManagement\Tests;

use JeffersonGoncalves\SessionManagement\SessionManagementServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SessionManagementServiceProvider::class,
        ];
    }
}
