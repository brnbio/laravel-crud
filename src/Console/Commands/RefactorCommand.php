<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class RefactorCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class RefactorCommand extends Command
{
    public const TABLES_BLACKLIST = [
        'failed_jobs',
        'media',
        'migrations',
        'password_resets',
        'personal_access_tokens',
    ];

    /**
     * @var string
     */
    protected $signature = 'code:refactor';

    /**
     * @var string
     */
    protected $description = 'Generate all from existing database';

    /**
     * @return void
     * @throws Exception
     */
    #[NoReturn] public function handle(): void
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if (in_array($table, self::TABLES_BLACKLIST, true)) {
                continue;
            }
            $model = str($table)->singular()->title()->toString();
            $this->info('Generating ' . $model . '...');
            $this->call('code:refactor-table', [
                'name'    => $model,
                '--table' => $table,
            ]);
        }
    }
}