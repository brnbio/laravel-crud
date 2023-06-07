<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Illuminate\Console\GeneratorCommand as Command;

/**
 * Class GeneratorCommand
 *
 * @package Brnbio\LaravelCrud
 */
abstract class GeneratorCommand extends Command
{
    /**
     * @param $stub
     * @return string
     */
    protected function resolveStubPath($stub): string
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        if (file_exists($customPath)) {
            return $customPath;
        }

        return __DIR__ . $stub;
    }

    /**
     * @return string
     */
    protected function rootNamespace(): string
    {
        if ($this->hasOption('namespace') && !empty($customNamespace = $this->option('namespace'))) {
            return explode('\\', $customNamespace)[0];
        }

        return parent::rootNamespace();
    }
}
