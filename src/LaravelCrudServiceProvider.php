<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

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
        $this->commands([
            GenerateModelCommand::class,
        ]);
    }
}
