<?php

/**
 * CrudAll.php
 *
 * @copyright   OEMUS MEDIA AG (https://oemus.com)
 * @author      Frank Heider <f.heider@oemus-media.de>
 * @since       23.07.2018
 */

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class CrudAll
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud\Console\Commands
 */
class CrudAll extends Command
{
    public const ARGUMENT_MODEL = 'model';
    public const OPTION_TABLE = 'table';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:all
        {model : Name of the model}
        {--table= : Table}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create model, controller and views';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = ucfirst(
            Str::singular(
                $this->argument(self::ARGUMENT_MODEL)
            )
        );
        $controller = Str::plural($model);

        $this->call('crud:model', [
            'model' => $model,
            '--table' => $this->option('table'),
        ]);
        $this->call('crud:controller', [
            'controller' => $controller,
        ]);
    }
}
