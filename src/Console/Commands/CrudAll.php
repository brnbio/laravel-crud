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

/**
 * Class CrudAll
 *
 * @package Brnbio
 * @subpackage Brnbio\LaravelCrud\Console\Commands
 */
class CrudAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:all {model} {--table=}';

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

        $this->call('crud:model', [
            'model' => $this->argument('model'),
            '--table' => $this->option('table'),
        ]);
        $this->call('crud:controller', [
            'controller' => $this->argument('model')
        ]);
    }
}
