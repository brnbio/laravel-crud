<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class GenerateCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class RefactorCommand extends Command
{
    public const COLUMNS_BLACKLIST = [
        'id',
        'uuid',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var string
     */
    protected $signature = 'code:refactor {name} { --table=}';

    /**
     * @var string
     */
    protected $description = 'Generate crud from given table';

    /**
     * @return void
     * @throws Exception
     */
    #[NoReturn] public function handle(): void
    {
        $name = $this->argument('name');
        $table = $this->option('table');

        if (DB::table($table)->exists()) {
            $schema = DB::connection()->getDoctrineSchemaManager();
            $columns = $schema->listTableColumns($table);
            $attributes = collect($columns)
                ->filter(function (Column $column) {
                    return !in_array($column->getName(), self::COLUMNS_BLACKLIST);
                })
                ->map(function (Column $column) {
                    return implode(':', array_filter([
                        $column->getName(),
                        $column->getType()->getName(),
                        $column->getNotnull() ? null : 'true',
                    ]));
                })
                ->implode(',');
            $this->call('code:generate', [
                'name'         => $name,
                '--attributes' => $attributes,
                '--table'      => $table,
            ]);
        }
    }
}
