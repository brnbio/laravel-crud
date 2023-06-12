<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
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
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the model file should be created'],
            ['type', null, InputOption::VALUE_REQUIRED, 'Manually specify the controller stub file to use'],
        ];
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        if ($type = $this->option('type')) {
            return $this->resolveStubPath('controller.' . $type . '.stub');
        }

        return 'controller.stub';
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
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        $replace = parent::getReplaceItems($name);
        $namespace = $this->rootNamespace() . 'Http\Requests\\';
        $storeRequestClass = Str::plural($replace['model']) . '\StoreRequest';
        $updateRequestClass = Str::plural($replace['model']) . '\UpdateRequest';

        return array_merge($replace, [
            'storeRequest'            => $storeRequestClass,
            'updateRequest'           => $updateRequestClass,
            'namespacedStoreRequest'  => $namespace . $storeRequestClass,
            'namespacedUpdateRequest' => $namespace . $updateRequestClass
        ]);
    }
}
