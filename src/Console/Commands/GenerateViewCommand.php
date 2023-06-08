<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateViewCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateViewCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'generate:view';

    /**
     * @var string
     */
    protected $description = 'Create a new view template';

    /**
     * @var string
     */
    protected $type = 'View';

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the view even if already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a view for the given model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the view file should be created'],
            ['type', null, InputOption::VALUE_OPTIONAL, 'Manually specify the view stub file to use'],
        ];
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $stub = 'view.stub';
        if ($type = $this->option('type')) {
            $stub = 'view.' . $type . '.stub';
        }

        return $this->resolveStubPath($stub);
    }

    /**
     * @param $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return implode('/', [
            base_path($this->option('path') ?: 'resources/views'),
            str_replace('\\', '/', $name) . '.vue',
        ]);
    }
}
