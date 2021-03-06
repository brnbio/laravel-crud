<?php

/**
 * CrudServiceProvider.php
 *
 * @copyright   brnb.io (info@brnb.io)
 * @author      Frank Heider <info@brnb.io>
 * @since       2018-07-20
 */

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Illuminate\Support\ServiceProvider;

/**
 * Class CrudServiceProvider
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud
 */
class CrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap laravel crud services
     *
     * @return void
     */
    public function boot(): void
    {
        // -- add commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Brnbio\LaravelCrud\Console\Commands\CrudModel::class,
                \Brnbio\LaravelCrud\Console\Commands\CrudController::class,
                \Brnbio\LaravelCrud\Console\Commands\CrudAll::class,
            ]);
        }

        $this->loadViewsFrom( __DIR__ . '/Templates', 'laravel-crud');
    }

    /**
     * Register laravel crud services
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__. ' /../config/laravel-crud.php', 'laravel-crud');
    }
}
