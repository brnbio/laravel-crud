<?php

declare(strict_types=1);

namespace Brnbio\LaravelCrud\Console\Commands;

use Brnbio\LaravelCrud\GeneratorCommand;
use Brnbio\LaravelCrud\Traits\HasOptionAttributes;
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
    use HasOptionAttributes;

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

        if ($this->option('factory')) {
//            $this->createFactory();
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('seed')) {
//            $this->createSeeder();
        }
    }

    /**
     * TODO:
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
     * @return void
     */
    protected function createMigration(): void
    {
        $table = $this->option('table');

        $this->call('generate:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
            '--attributes' => $this->option('attributes')
        ]);
    }

    /**
     * TODO:
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
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/model.stub');
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
            ['attributes', null, InputOption::VALUE_OPTIONAL, 'The attributes of the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The root namespace for the model'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the model file should be created'],
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
            $properties[] = ' * @property ' . $attribute['attributeType'] . ($attribute['nullable'] ? '|null' : '') . ' $' . $attribute['name'];
            if ($attribute['type'] === 'foreignId') {
                $foreign = str($attribute['name'])->replace('_id', '')->camel()->__toString();
                $properties[] = ' * @property ' . ucfirst($foreign) . ($attribute['nullable'] ? '|null' : '') . ' $' . $foreign;
            }
        }

        return implode(PHP_EOL, $properties);
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
        $fillable = [];
        foreach ($this->getAttributes() as $attribute) {
            $fillable[] = 'self::ATTRIBUTE_' . strtoupper($attribute['name']) . ',';
        }

        return implode(PHP_EOL . "\t\t", $fillable);
    }
}
