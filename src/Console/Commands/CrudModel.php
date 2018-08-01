<?php

/**
 * CrudServiceProvider.php
 *
 * @copyright   brnb.io (info@brnb.io)
 * @author      Frank Heider <info@brnb.io>
 * @since       2018-07-20
 */

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\Template;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * Class CrudModel
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud\Console\Commands
 */
class CrudModel extends Command
{
    public const ARGUMENT_NAME = 'name';

    public const OPTION_TABLE = 'table';
    public const OPTION_MODULE = 'module';

    public const ATTRIBUTE_TABLE = 'table';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
        {' . self::ARGUMENT_NAME . ' : Name of the model to generate (without the Table suffix).}
        {--' . self::OPTION_TABLE . '= : The table name to use if you have non-conventional table names.}
        {--' . self::OPTION_MODULE . '= : Module to generate model into.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model';

    /**
     * @var array
     */
    protected $use = [
        'Illuminate\Database\Eloquent\Model',
    ];

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
        $model = $this->getModel();
        $fields = $this->getFields($this->getTable());

        $file = view()
            ->make('laravel-crud::model', [
                'model' => $model,
                'filename' => $model . '.php',
                'namespace' => $this->getNamespace(),
                'use' => $this->use,
                'fields' => $fields,
                'attributes' => [
                    self::ATTRIBUTE_TABLE => $this->getTable(),
                ],
            ]);

        file_put_contents(app_path('Model') . DIRECTORY_SEPARATOR . $model . '.php', "<?php\n\n" . $file->render());
        $this->line('Model ' . $model . ' successfully created.');



/*
        $data = [
            'model' => $model,
            'filename' => $model . '.php',
            'table' => $tableName = Str::snake($this->option('table') ?: $model),
            'use' => [
                'Illuminate\Database\Eloquent\Model',
            ],
            'constants' => [],
            'attributes' => [
                'protected $table = \'' . $tableName . '\';',
            ],
            'methods' => [],
        ];



        foreach ($modelAttributes as $item)
        {
            $attribute = Str::camel($item->Field);
            $attributeConstant = strtoupper($item->Field);
            if (!in_array($item->Field, config('laravel-crud.skip.constants'))) {
                $attributeConstant = 'ATTRIBUTE_' . $attributeConstant;
                $data['constants'][] = 'public const ' . $attributeConstant . ' = \'' . $item->Field . '\';';
            }

            // -> belongsTo
            if (strrpos($item->Field, '_id') !== false) {

                $belongsToModel = Str::camel(str_replace('_id', '', $item->Field));

                $data['use'][] = 'App\Model\\' . ucfirst($belongsToModel);
                $data['use'][] = 'Illuminate\Database\Eloquent\Relations\BelongsTo';

                $data['methods'][] = (new Template(
                    'laravel-crud.templates.methods.belongsTo',
                    [
                        'function' => $belongsToModel,
                        'model' => ucfirst($belongsToModel),
                    ]
                ))->render();

                $data['methods'][] = str_replace(
                    '$this->getAttribute(self::' . $attributeConstant . ');',
                    '$this->' . $belongsToModel . ';',
                    (new Template(
                        'laravel-crud.templates.methods.getter',
                        [
                            'type' => ucfirst($belongsToModel),
                            'function' => 'get' . ucfirst($belongsToModel),
                            'attribute' => 'self::' . $attributeConstant,
                            'model' => ucfirst($belongsToModel),
                        ]
                    ))->render()
                );

                $data['methods'][] = (new Template(
                    'laravel-crud.templates.methods.setter',
                    [
                        'type' => 'int',
                        'function' => 'set' . ucfirst($attribute),
                        'attribute' => 'self::' . $attributeConstant,
                        'model' => $model,
                    ]
                ))->render();

            // -- common fields
            } else {

                $type = $this->getReturnType($item->Type);

                if (!in_array($item->Field, config('laravel-crud.skip.getter'))) {
                    $data['methods'][] = (new Template(
                        'laravel-crud.templates.methods.getter',
                        [
                            'type' => $type,
                            'function' => ($type === 'bool' ? 'is' : 'get') . ucfirst($attribute),
                            'attribute' => 'self::' . $attributeConstant,
                        ]
                    ))->render();
                }

                if (!in_array($item->Field, config('laravel-crud.skip.setter'))) {
                    $data['methods'][] = (new Template(
                        'laravel-crud.templates.methods.setter',
                        [
                            'type' => $type,
                            'function' => 'set' . ucfirst($attribute),
                            'attribute' => 'self::' . $attributeConstant,
                            'model' => $model,
                        ]
                    ))->render();
                }
            }
        }

        asort($data['use']);
        $data['use'] = 'use ' . implode(';' . PHP_EOL . 'use ', array_unique($data['use'])) . ';';
        $data['constants'] = implode(PHP_EOL . "\t", $data['constants']);
        $data['attributes'] = implode(PHP_EOL . "\t", $data['attributes']);
        $data['methods'] = implode(PHP_EOL . "\t", $data['methods']);


        $file = new Template('laravel-crud.templates.model', $data);

        */
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
    private function getTable(): string
    {
        if ($this->option(self::OPTION_TABLE)) {
            return $this->option(self::OPTION_TABLE);
        }

        return strtolower(
            Str::snake(
                Str::plural($this->getModel())
            )
        );
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        $module = $this->getModule();
        if ($module) {
            return $this->getModule() . '\\Model';
        }

        return 'App\Model';
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

    /**
     * @string $table
     * @return array
     */
    private function getFields(string $table): array
    {
        $result = [];
        $fields = DB::table($table)->getConnection()->select('SHOW COLUMNS FROM ' . $table);

        foreach ($fields as $item) {
            $result[$item->Field] = [
                'type' => $this->getFieldType($item->Type),
                'nullable' => $item->Null === 'YES',
            ];
        }

        return $result;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getFieldType(string $type): string
    {
        if (strpos($type, 'int(') !== false) {
            return 'int';
        }

        if (strpos($type, 'varchar(') !== false
            || strpos($type, 'text') !== false) {
            return 'string';
        }

        if ($type === 'datetime') {
            $this->use[] = 'Carbon\Carbon';
            return 'Carbon';
        }

        return 'string';
    }
}
