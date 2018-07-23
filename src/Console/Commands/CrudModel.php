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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model {model} {--table=} {--namespace=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model';

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
        $model = ucfirst(Str::camel($this->argument('model')));
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

        $modelAttributes = DB::table($tableName)->getConnection()->select('SHOW COLUMNS FROM ' . $tableName);

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

        file_put_contents(app_path('Model') . DIRECTORY_SEPARATOR . $data['filename'], $file->render());
        $this->line('Model ' . $model . ' successfully created.');
    }

    /**
     * @param string $type
     * @return string
     */
    private function getReturnType(string $type): string
    {
        foreach ([
            'int' => 'int',
            'tinyint(1)' => 'bool',
            'datetime' => '\DateTime',
         ] as $key => $returnType) {
            if (strpos($type, $key) !== false) {
                return $returnType;
            }
        }

        return 'string';
    }
}
