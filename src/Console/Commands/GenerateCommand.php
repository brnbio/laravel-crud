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
    protected $signature = 'code:generate {name}
        { --table= : The table to be created }
        { --attributes= : The attributes of the model }
        { --namespace= : The root namespace }
        { --path= : Root path of the files }
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

        $this->call('code:generate:model', [
            'name'         => $model,
            '--migration'  => true,
            '--attributes' => $this->option('attributes'),
            '--table'      => $this->option('table'),
        ]);

        foreach (['store', 'update'] as $action) {
            $requestClass = $modelPlural . '\\' . ucfirst($action) . 'Request';
            $this->call('code:generate:request', [
                'name'         => $requestClass,
                '--model'      => $model,
                '--attributes' => $this->option('attributes'),
            ]);
        }

        foreach (['create', 'read', 'update', 'delete'] as $action) {
            $this->call('code:generate:controller', [
                'name'    => $modelPlural . '/' . ucfirst($action) . 'Controller',
                '--model' => $model,
                '--type'  => $action,
            ]);
        }

        foreach (['index', 'details', 'create', 'update'] as $action) {
            $path = 'resources/js/views';
            if ($this->option('path')) {
                $path = $this->option('path') . '/' . $path;
            }
            $this->call('code:generate:view', [
                'name'         => strtolower($modelPlural) . '/' . $action,
                '--attributes' => $this->option('attributes'),
                '--model'      => $model,
                '--type'       => $action,
                '--path'       => $path,
            ]);
        }
    }

    /**
     * @param string $command
     * @param array $arguments
     * @return int
     */
    public function call($command, array $arguments = []): int
    {
        $arguments = array_merge([
            '--namespace' => $this->option('namespace'),
            '--path'      => $this->option('path'),
            '--force'     => $this->option('force')
        ], $arguments);

        return parent::call($command, $arguments);
    }
}
