<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class GenerateCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateCommand extends Command
{
    use HasOptionAttributes;

    /**
     * @var string
     */
    protected $signature = 'generate {name}
        { --table= : The table to be created }
        { --attributes= : The attributes of the model }
        { --force : Create the files even if they already exists }';

    /**
     * @var string
     */
    protected $description = 'Generate CRUD';

    /**
     * @return void
     */
    public function handle(): void
    {
        $model = $this->argument('name');
        $modelPlural = Str::plural($model);

        $this->call('generate:model', [
            'name' => $model,
            '--migration' => true,
            '--table' => $this->option('table'),
            '--force' => $this->option('force')
        ]);

        foreach (['create', 'read', 'update', 'delete'] as $action) {
            $this->call('generate:controller', [
                'name' => $modelPlural . '/' . ucfirst($action) . 'Controller',
                '--type' => $action,
                '--force' => $this->option('force')
            ]);
        }

        foreach (['index', 'details', 'create', 'update'] as $action) {
            $this->call('generate:view', [
                'name' => strtolower($modelPlural) . '/' . $action,
                '--path' => 'resources/js/views',
                '--type' => $action,
                '--force' => $this->option('force')
            ]);
        }
    }
}
