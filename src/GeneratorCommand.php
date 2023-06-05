<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Console\GeneratorCommand as Command;

/**
 * Class GeneratorCommand
 *
 * @package Brnbio\LaravelCrud
 */
abstract class GeneratorCommand extends Command
{
    use HasOptionAttributes;
}
