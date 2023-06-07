<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateRequestCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateRequestCommand extends GeneratorCommand
{
    use HasOptionAttributes;

    /**
     * @var string
     */
    protected $name = 'generate:request';

    /**
     * @var string
     */
    protected $description = 'Create a new form request class';

    /**
     * @var string
     */
    protected $type = 'Request';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/request.stub');
    }

    /**
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a form request for the given model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the form request'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the form request file should be created'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the request already exists'],
        ];
    }

    /**
     * @param $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());
        $modelClass = $this->qualifyModel($this->option('model'));
        $stub = str_replace('{{ namespacedModel }}', $modelClass, $stub);

        return $this
            ->replaceRules($stub)
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * @param string $stub
     * @return self
     */
    protected function replaceRules(string &$stub): self
    {
        $rules = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributeRules = [
                $attribute['nullable'] ? 'nullable' : 'required',
                $this->getRuleType($attribute['type']),
            ];
            if ($attribute['type'] === 'string') {
                $attributeRules[] = 'max:255';
            }
            $attributeRules = implode("',\n\t\t\t\t'", $attributeRules);
            $rules[] = sprintf(
                "%s => [\n\t\t\t\t'%s',\n\t\t\t],",
                $this->option('model') . '::ATTRIBUTE_' . strtoupper($attribute['name']),
                $attributeRules
            );
        }
        $stub = str_replace('{{ rules }}', implode("\n            ", $rules), $stub);

        return $this;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getRuleType(string $type): string
    {
        return match ($type) {
            'text' => 'string',
            'datetime' => 'date',
            default => $type,
        };

    }
}
