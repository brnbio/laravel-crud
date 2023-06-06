<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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
     * @return string
     */
    protected function getStub(): string
    {
        $stub = '/stubs/view.stub';
        if ($type = $this->option('type')) {
            $stub = '/stubs/view.' . $type . '.stub';
        }
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath) ? $customPath : __DIR__ . $stub;
    }

    /**
     * @param $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $content = $this->files->get($this->getStub());
        $replace = $this->buildModelReplacements([]);

        return str_replace(array_keys($replace), array_values($replace), $content);
    }


    /**
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        if (empty($this->option('model'))) {
            return $replace;
        }

        $modelClass = $this->qualifyModel($this->option('model'));

        return array_merge($replace, [
            '{{ model }}' => class_basename($modelClass),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{ modelVariablePlural }}' => lcfirst(Str::plural(class_basename($modelClass))),
        ]);
    }

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
