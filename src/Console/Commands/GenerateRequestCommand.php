<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
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
    protected $name = 'code:generate:request';

    /**
     * @var string
     */
    protected $description = 'Create a new form request class';

    /**
     * @var string
     */
    protected $type = 'Request';

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
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('request.stub');
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
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        $items = array_merge(parent::getReplaceItems($name), [
            'rules' => $this->replaceRules($name)
        ]);

        if (self::hasMacro('updateReplace')) {
            $items = array_merge(
                $items,
                self::updateReplace($items, $this->arguments(), $this->options())
            );
        }

        return $items;
    }

    /**
     * @param string $stub
     * @return string
     */
    protected function replaceRules(string $stub): string
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
            $attributeRules = implode("',\n                '", $attributeRules);
            $rules[] = sprintf(
                "%s => [\n                '%s',\n            ],",
                $this->option('model') . '::ATTRIBUTE_' . strtoupper($attribute['name']),
                $attributeRules
            );
        }

        return implode("\n            ", $rules);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getRuleType(string $type): string
    {
        return match ($type) {
            'text'     => 'string',
            'datetime' => 'date',
            default    => $type,
        };

    }
}
