<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Brnbio\LaravelCrud\Console\Commands\GenerateMigrationCommand;
use Brnbio\LaravelCrud\Console\Commands\GenerateModelCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class LaravelCrudServiceProvider
 *
 * @package Brnbio\LaravelCrud
 */
class LaravelCrudServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->app->singleton(GenerateMigrationCommand::class, function ($app) {
            return new GenerateMigrationCommand($app['migration.creator'], $app['composer']);
        });

        $this->commands([
            GenerateModelCommand::class,
            GenerateMigrationCommand::class,
        ]);
    }
}
