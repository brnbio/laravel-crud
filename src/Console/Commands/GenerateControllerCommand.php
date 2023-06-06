<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateControllerCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateControllerCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'generate:controller';

    /**
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * @var string
     */
    protected $type = 'Controller';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $stub = null;

        if ($type = $this->option('type')) {
            $stub = 'controller.' . $type . '.stub';
        } elseif ($this->option('parent')) {
            $stub = $this->option('singleton')
                ? 'controller.nested.singleton.stub'
                : 'controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = 'controller.model.stub';
        } elseif ($this->option('invokable')) {
            $stub = 'controller.invokable.stub';
        } elseif ($this->option('singleton')) {
            $stub = 'controller.singleton.stub';
        } elseif ($this->option('resource')) {
            $stub = 'controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = 'controller.api.stub';
        } elseif ($this->option('api') && !is_null($stub) && !$this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub ??= 'controller.plain.stub';

        return $this->resolveStubPath('/stubs/' . $stub);
    }

    /**
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath) ? $customPath : __DIR__ . $stub;
    }

    /**
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Controllers';
    }

    /**
     * @param $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if ($this->option('creatable')) {
            $replace['abort(404);'] = '//';
        }

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    protected function buildParentReplacements(): array
    {
        $parentModelClass = $this->parseModel($this->option('parent'));

        if (!class_exists($parentModelClass) &&
            $this->components->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
            $this->call('make:model', ['name' => $parentModelClass]);
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        if (!class_exists($modelClass) && $this->components->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
            $this->call('make:model', ['name' => $modelClass]);
        }

        $replace = $this->buildFormRequestReplacements($replace, $modelClass);

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
            '{{ modelVariablePlural }}' => lcfirst(Str::plural(class_basename($modelClass))),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param string $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @param string $modelClass
     * @return array
     */
    protected function buildFormRequestReplacements(array $replace, $modelClass)
    {
        [$namespace, $storeRequestClass, $updateRequestClass] = [
            'Illuminate\\Http', 'Request', 'Request',
        ];

        if ($this->option('requests')) {
            $namespace = 'App\\Http\\Requests';

            [$storeRequestClass, $updateRequestClass] = $this->generateFormRequests(
                $modelClass, $storeRequestClass, $updateRequestClass
            );
        }

        $namespacedRequests = $namespace . '\\' . $storeRequestClass . ';';

        if ($storeRequestClass !== $updateRequestClass) {
            $namespacedRequests .= PHP_EOL . 'use ' . $namespace . '\\' . $updateRequestClass . ';';
        }

        return array_merge($replace, [
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace . '\\' . $storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace . '\\' . $storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace . '\\' . $updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace . '\\' . $updateRequestClass,
            '{{ namespacedRequests }}' => $namespacedRequests,
            '{{namespacedRequests}}' => $namespacedRequests,
        ]);
    }

    /**
     * Generate the form requests for the given model and classes.
     *
     * @param string $modelClass
     * @param string $storeRequestClass
     * @param string $updateRequestClass
     * @return array
     */
    protected function generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass)
    {
        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        $this->call('make:request', [
            'name' => $storeRequestClass,
        ]);

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        $this->call('make:request', [
            'name' => $updateRequestClass,
        ]);

        return [$storeRequestClass, $updateRequestClass];
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller'],
            ['creatable', null, InputOption::VALUE_NONE, 'Indicate that a singleton resource should be creatable'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the model file should be created'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Generate FormRequest classes for store and update'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class'],
            ['singleton', 's', InputOption::VALUE_NONE, 'Generate a singleton resource controller class'],
            ['type', null, InputOption::VALUE_REQUIRED, 'Manually specify the controller stub file to use'],
        ];
    }

    /**
     * @return string
     */
    protected function rootNamespace(): string
    {
        if (!empty($customNamespace = $this->option('namespace'))) {
            return explode('\\', $customNamespace)[0];
        }

        return parent::rootNamespace();
    }
}
