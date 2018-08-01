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
    public const ARGUMENT_NAME = 'name';
    public const OPTION_ACTIONS = 'actions';
    public const OPTION_MODULE = 'module';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        crud:controller
            {' . self::ARGUMENT_NAME . ' : Name of the controller (without the `Controller` suffix).}
            {--' . self::OPTION_ACTIONS . '= : The comma separated list of actions to generate (index|view|add|edit|delete).}            
            {--' . self::OPTION_MODULE . '= : Module to generate controller into.}            
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a controller';

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
     * @return void
     */
    public function handle()
    {
        $model = $this->getModel();
        $module = $this->getModule();

        $file = view()
            ->make('laravel-crud::controller', [
                'model' => $model,
                'controller' => $this->getController(),
                'filename' => $this->getController() . '.php',
                'actions' => $this->getActions(),
                'namespace' => $this->getNamespace(),
                'module' => $module,
                'modulePrefix' => $module ? strtolower($module) . '::' : '',
                'use' => [
                    'App\Http\Controllers\Controller',
                    'Illuminate\Http\RedirectResponse',
                    'Illuminate\Support\Facades\Input',
                    'Illuminate\View\View',
                    ($module ?: 'App') . '\Model\\' . $model,
                ],
                'var' => strtolower(
                    Str::snake($this->getModel())
                ),
                'vars' => strtolower(
                    Str::snake(
                        Str::plural($this->getModel())
                    )
                ),
            ]);

        file_put_contents(app_path('Http/Controllers') . DIRECTORY_SEPARATOR . $this->getController() . '.php', "<?php\n\n" . $file->render());
        $this->line('Controller ' . $this->getController() . ' successfully created.');
    }

    /**
     * @return string
     */
    private function getModel(): string
    {
        return ucfirst(
            Str::camel(
                Str::singular(
                    $this->argument(self::ARGUMENT_NAME)
                )
            )
        );
    }

    /**
     * @return string
     */
    private function getController(): string
    {
        return ucfirst(
            Str::camel(
                Str::plural(
                    $this->argument(self::ARGUMENT_NAME)
                )
            )
        ) . 'Controller';
    }

    /**
     * @return array
     */
    private function getActions(): array
    {
        $actions = ['index', 'view', 'add', 'edit', 'delete'];
        if ($this->option('actions')) {
            return array_intersect(
                $actions,
                explode(',', $this->option(self::OPTION_ACTIONS))
            );
        }

        return $actions;
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        $module = $this->getModule();
        if ($module) {
            return $this->getModule() . '\\Http\\Controllers';
        }

        return 'App\Http\Controllers';
    }

    /**
     * @return null|string
     */
    private function getModule(): ?string
    {
        $option = $this->option(self::OPTION_MODULE);
        if ($option) {
            return ucfirst($option);
        }

        return null;
    }
}
