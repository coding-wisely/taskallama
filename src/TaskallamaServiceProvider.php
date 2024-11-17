<?php

namespace CodingWisely\Taskallama;

use CodingWisely\Taskallama\Services\TaskallamaService;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CodingWisely\Taskallama\Commands\TaskallamaCommand;

class TaskallamaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('taskallama')
            ->hasConfigFile();
    }

}
