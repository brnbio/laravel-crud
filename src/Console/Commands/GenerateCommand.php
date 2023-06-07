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
            '--attributes' => $this->option('attributes'),
            '--table' => $this->option('table'),
            '--force' => $this->option('force')
        ]);

        foreach (['store', 'update'] as $action) {
            $requestClass = $modelPlural . '\\' . ucfirst($action) . 'Request';
            $this->call('generate:request', [
                'name' => $requestClass,
                '--model' => $model,
                '--attributes' => $this->option('attributes'),
                '--force' => $this->option('force')
            ]);
        }

        foreach (['create', 'read', 'update', 'delete'] as $action) {
            $this->call('generate:controller', [
                'name' => $modelPlural . '/' . ucfirst($action) . 'Controller',
                '--model' => $model,
                '--type' => $action,
                '--force' => $this->option('force')
            ]);
        }

        foreach (['index', 'details', 'create', 'update'] as $action) {
            $this->call('generate:view', [
                'name' => strtolower($modelPlural) . '/' . $action,
                '--model' => $model,
                '--type' => $action,
                '--path' => 'resources/js/views',
                '--force' => $this->option('force')
            ]);
        }
    }
}
