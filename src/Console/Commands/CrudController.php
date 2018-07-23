<?php

/**
 * CrudController.php
 *
 * @copyright   brnb.io (info@brnb.io)
 * @author      Frank Heider <info@brnb.io>
 * @since       2018-07-20
 */

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class CrudController
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud\Console\Commands
 */
class CrudController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a controller';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = ucfirst(Str::camel($this->argument('controller')));
        $controller = Str::plural($model) . 'Controller';

        $data = [
            'model' => $model,
            'var' => strtolower(Str::snake($model)),
            'controller' => $controller,
            'filename' => $controller . '.php',
            'use' => [
                'App\Model\\' . $model,
                'Illuminate\Http\RedirectResponse',
                'Illuminate\Support\Facades\Input',
                'Illuminate\View\View',
            ],
        ];

        asort($data['use']);
        $data['use'] = 'use ' . implode(';' . PHP_EOL . 'use ', array_unique($data['use'])) . ';';
        $file = new Template('laravel-crud.templates.controller', $data);

        file_put_contents(app_path('Http/Controllers') . DIRECTORY_SEPARATOR . $data['filename'], $file->render());
        $this->line('Controller ' . $controller . ' successfully created.');
    }
}
