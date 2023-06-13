<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateMigrationCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateFactoryCommand extends GeneratorCommand
{
    use HasOptionAttributes;

    /**
     * @var string
     */
    protected $name = 'generate:factory';

    /**
     * @var string
     */
    protected $description = 'Create a new model factory';

    /**
     * @var string
     */
    protected $type = 'Factory';

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the factory even if already exists'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the factory file should be created'],
        ];
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('factory.stub');
    }

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Database\\Factories';
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getReplaceItems(string $name): array
    {
        $replace = parent::getReplaceItems($name);

        if ($replace['rootNamespace'] === 'App\\') {
            $replace['namespace'] = str_replace('App\\', '', $replace['namespace']);
        }

        return array_merge($replace, [
            'data' => $this->buildData(),
        ]);
    }

    /**
     * @return string
     */
    protected function buildData(): string
    {
        $fields = [];
        foreach ($this->getAttributes() as $attribute) {
            $fields[] = "'{$attribute['name']}' => fake()->" . $this->getFakeMethod($attribute) . ",";
        }

        return implode(PHP_EOL . str_repeat(' ', 12), $fields);
    }

    /**
     * @param array $attribute
     * @return string
     */
    protected function getFakeMethod(array $attribute): string
    {
        if (str_contains($attribute['name'], 'email')) {
            return 'email()';
        }

        if (str_contains($attribute['name'], 'firstname')) {
            return 'firstName()';
        }

        if (str_contains($attribute['name'], 'name')) {
            return 'lastName()';
        }

        return match ($attribute['type']) {
            'boolean' => 'boolean()',
            default   => 'words(asText: true)',
        };
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        if ($this->rootNamespace() === 'App\\') {
            $name = Str::replaceFirst('Database\\Factories\\', '', $name);
        }
        $filename = str_replace('\\', '/', $name) . '.php';

        if ($path = $this->option('path')) {
            return $path . '/app/' . $filename;
        }
        
        return database_path('factories/' . $filename);
    }
}
