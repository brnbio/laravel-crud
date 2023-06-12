<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud;

use Illuminate\Console\GeneratorCommand as Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

/**
 * Class GeneratorCommand
 *
 * @package Brnbio\LaravelCrud
 */
abstract class GeneratorCommand extends Command
{
    use Macroable;

    /**
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        $stub = '/stubs/' . $stub;
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
            return explode('\\', $customNamespace)[0] . '\\';
        }

        return parent::rootNamespace();
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $modelClass = $this->qualifyModel(
            $this->hasOption('model') ? $this->option('model') : $name
        );

        return [
            'namespace'           => $this->getNamespace($name),
            'rootNamespace'       => $this->rootNamespace(),
            'class'               => $class,
            'namespacedModel'     => $modelClass,
            'model'               => class_basename($modelClass),
            'modelVariable'       => lcfirst(class_basename($modelClass)),
            'modelVariablePlural' => lcfirst(Str::plural(class_basename($modelClass))),
        ];
    }

    /**
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replace(string $stub, string $name): string
    {
        $items = $this->getReplaceItems($name);
        if (self::hasMacro('updateReplace')) {
            $items = array_merge(
                $items,
                self::updateReplace($items, $this->arguments(), $this->options())
            );
        }

        foreach ($items as $search => $replace) {
            $stub = str_replace('{{ ' . $search . ' }}', $replace, $stub);
        }

        return $stub;
    }

    /**
     * @param $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replace($stub, $name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        if ($path = $this->option('path')) {
            return $path . '/app/' . str_replace('\\', '/', $name) . '.php';
        }

        return parent::getPath($name);
    }
}
