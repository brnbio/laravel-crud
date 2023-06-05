<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateMigrationCommand
 *
 * @package Brnbio\LaravelCrud\Console\Commands
 */
class GenerateModelCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'generate:model';

    /**
     * @var string
     */
    protected $description = 'Create a new eloquent model class';

    /**
     * @var string
     */
    protected $type = 'Model';

    /**
     * @return false|void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        if ($this->option('all')) {
            $this->input->setOption('factory', true);
            $this->input->setOption('seed', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('policy', true);
            $this->input->setOption('resource', true);
        }

        if ($this->option('factory')) {
            $this->createFactory();
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('seed')) {
            $this->createSeeder();
        }

        if ($this->option('controller') || $this->option('resource') || $this->option('api')) {
            $this->createController();
        }

        if ($this->option('policy')) {
            $this->createPolicy();
        }
    }

    /**
     * @return void
     */
    protected function createFactory()
    {
        $factory = Str::studly($this->argument('name'));

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
            '--fullpath' => true,
        ]);
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $this->call('make:seeder', [
            'name' => "{$seeder}Seeder",
        ]);
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
            '--api' => $this->option('api'),
            '--requests' => $this->option('requests') || $this->option('all'),
        ]));
    }

    /**
     * Create a policy file for the model.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $policy = Str::studly(class_basename($this->argument('name')));

        $this->call('make:policy', [
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        if ($this->option('pivot')) {
            return $this->resolveStubPath('model.pivot.stub');
        }

        if ($this->option('morph-pivot')) {
            return $this->resolveStubPath('model.morph-pivot.stub');
        }

        return $this->resolveStubPath('model.stub');
    }

    /**
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub): string
    {
        $customPath = '/stubs/' . $this->laravel->basePath(trim($stub, '/'));

        return file_exists($customPath) ? $customPath : __DIR__ . '/stubs/' . $stub;
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

    /**
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Models';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, seeder, factory, policy, resource controller, and form request classes for the model'],
            ['api', null, InputOption::VALUE_NONE, 'Indicates if the generated controller should be an API resource controller'],
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['morph-pivot', null, InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom polymorphic intermediate table model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the model file should be created'],
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
            ['policy', null, InputOption::VALUE_NONE, 'Create a new policy for the model'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Create new form request classes and use them in the resource controller'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder for the model'],
            ['table', 't', InputOption::VALUE_OPTIONAL, 'The table to be created'],
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

        $stub = str_replace('{{ properties }}', $this->buildProperties(), $stub);
        $stub = str_replace(['{{ table }}', '{{ tableName }}'], $this->option('table') ?: '', $stub);
        $stub = str_replace('{{ attributes }}', $this->buildAttributes(), $stub);
        $stub = str_replace('{{ fillable }}', $this->buildFillable(), $stub);

        return $this
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * @return string
     */
    protected function buildProperties(): string
    {
        $properties = [];

        foreach ($this->getAttributes() as $attribute) {
            $properties[] = ' * @property ' . $attribute['type'] . ($attribute['nullable'] ? '|null' : '') . ' $' . $attribute['name'];
        }

        return implode(PHP_EOL, $properties);
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $attributes = [];
        foreach (explode(',', $this->option('attributes') ?? '') as $attribute) {
            $attributes[] = $this->buildAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * @param mixed $attribute
     * @return array
     */
    protected function buildAttribute(string $attribute): array
    {
        $attribute = explode(':', $attribute);

        return [
            'name' => $attribute[0],
            'type' => $this->getType($attribute[1] ?? 'string'),
            'nullable' => $attribute[2] ?? false,
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getType(string $type): string
    {
        return match ($type) {
            'date', 'datetime', 'time' => 'Carbon',
            default => 'string',
        };
    }

    /**
     * @return string
     */
    private function buildAttributes(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributes[] = 'public const ATTRIBUTE_' . strtoupper($attribute['name']) . ' = \'' . $attribute['name'] . '\';';
        }

        return implode(PHP_EOL . "\t", $attributes);
    }

    /**
     * @return string
     */
    private function buildFillable(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributes[] = 'self::ATTRIBUTE_' . strtoupper($attribute['name']) . ',';
        }

        return implode(PHP_EOL . "\t\t", $attributes);
    }
}
